<?php

namespace App\Http\Controllers;

use App\Models\DebtPayment;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        $expenses = Expense::whereDate('date', $date)->latest()->get();

        // 1. Direct cash movements (no invoice, no credit) — use amount_paid to handle MIX correctly
        $directCashMvmt = StockMovement::where('type', 'out')
            ->whereNull('sale_id')->where('is_credit', false)
            ->whereDate('created_at', $date)->whereNotNull('price')->get();
        $cashSalesSyp = $directCashMvmt->where('currency', 'SYP')->sum('amount_paid');
        $cashSalesUsd = $directCashMvmt->where('currency', 'USD')->sum('amount_paid');

        // 2. Direct legacy credit movements: down payment only
        $legacyCreditMovements = StockMovement::where('type', 'out')
            ->whereNull('sale_id')->where('is_credit', true)
            ->whereDate('created_at', $date)->get();
        $cashSalesSyp += $legacyCreditMovements->where('currency', 'SYP')->sum('amount_paid');
        $cashSalesUsd += $legacyCreditMovements->where('currency', 'USD')->sum('amount_paid');

        // 3. Invoice-based non-credit sales (fully paid)
        $cashInvoiceMovements = StockMovement::where('type', 'out')
            ->whereNotNull('sale_id')->where('is_credit', false)
            ->whereDate('created_at', $date)->whereNotNull('price')->get();
        $cashSalesSyp += $cashInvoiceMovements->where('currency', 'SYP')->sum(fn($m) => $m->totalAmount());
        $cashSalesUsd += $cashInvoiceMovements->where('currency', 'USD')->sum(fn($m) => $m->totalAmount());

        // 4. Credit invoices created on this date: initial down payment ONLY
        // Load only SalePayments created on the SAME date to avoid subtracting future payments
        $creditSales = Sale::where('is_credit', true)
            ->whereDate('created_at', $date)
            ->with(['salePayments' => fn($q) => $q->whereDate('created_at', $date)])
            ->get();
        $cashSalesSyp += $creditSales->where('currency', 'SYP')->sum(function ($s) {
            $followup = $s->salePayments->sum(fn($p) => $p->amountInSaleCurrency($s));
            return max(0, (float) $s->amount_paid - $followup);
        });
        $cashSalesUsd += $creditSales->where('currency', 'USD')->sum(function ($s) {
            $followup = $s->salePayments->sum(fn($p) => $p->amountInSaleCurrency($s));
            return max(0, (float) $s->amount_paid - $followup);
        });

        // 5. Follow-up invoice payments received on this date (SalePayment table, by pay_currency)
        $salePaymentsDate = SalePayment::whereDate('created_at', $date)->get();
        $cashSalesSyp += $salePaymentsDate->where('pay_currency', 'SYP')->sum('amount');
        $cashSalesUsd += $salePaymentsDate->where('pay_currency', 'USD')->sum('amount');

        // 6. Debt follow-up payments received on this date (DebtPayment table, by pay_currency)
        $debtPaymentsDate = DebtPayment::whereDate('created_at', $date)
            ->with('movement')->get()
            ->filter(fn($dp) => $dp->movement !== null);
        $cashSalesSyp += $debtPaymentsDate->filter(fn($dp) => $dp->pay_currency === 'SYP')->sum('amount');
        $cashSalesUsd += $debtPaymentsDate->filter(fn($dp) => $dp->pay_currency === 'USD')->sum('amount');

        // exchange_in rows are income (add to drawer), regular expenses are outflows
        $expensesSyp = $expenses->where('currency', 'SYP')->where('type', 'expense')->sum('amount');
        $expensesUsd = $expenses->where('currency', 'USD')->where('type', 'expense')->sum('amount');
        $exchangeInSyp = $expenses->where('currency', 'SYP')->where('type', 'exchange_in')->sum('amount');
        $exchangeInUsd = $expenses->where('currency', 'USD')->where('type', 'exchange_in')->sum('amount');

        // Opening balance stored per day in settings
        $dayKey     = 'opening_' . str_replace('-', '_', $date);
        $openingSyp = (float) Setting::get($dayKey . '_syp', 0);
        $openingUsd = (float) Setting::get($dayKey . '_usd', 0);

        // Last 50 movements for history sidebar
        $recentExpenses = Expense::latest('date')->latest()->take(50)->get()->groupBy(fn($e) => $e->date->format('Y-m-d'));

        return view('expenses.index', compact(
            'expenses', 'date',
            'cashSalesSyp', 'cashSalesUsd',
            'expensesSyp', 'expensesUsd',
            'exchangeInSyp', 'exchangeInUsd',
            'openingSyp', 'openingUsd',
            'recentExpenses'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount'   => 'required|numeric|min:0.01',
            'currency' => 'required|in:SYP,USD',
            'category' => 'required|string|max:100',
            'note'     => 'nullable|string|max:255',
            'date'     => 'required|date',
        ]);

        Expense::create(array_merge($request->only('amount', 'currency', 'category', 'note', 'date'), ['type' => 'expense']));

        $dec = $request->currency === 'SYP' ? 0 : 2;
        return back()->with('success', 'تم تسجيل المصروف: ' . $request->category . ' — ' . number_format($request->amount, $dec) . ($request->currency === 'SYP' ? ' ل.س' : ' $'));
    }

    public function storeExchange(Request $request)
    {
        $request->validate([
            'usd_amount'   => 'required|numeric|min:0.01',
            'exchange_rate'=> 'required|numeric|min:1',
            'date'         => 'required|date',
        ]);

        $usd  = (float) $request->usd_amount;
        $rate = (float) $request->exchange_rate;
        $syp  = round($usd * $rate, 0);
        $date = $request->date;

        // USD leaves the drawer
        Expense::create([
            'date'     => $date,
            'amount'   => $usd,
            'currency' => 'USD',
            'category' => 'تحويل عملة',
            'note'     => 'تحويل ' . number_format($usd, 2) . ' $ → ' . number_format($syp, 0) . ' ل.س (سعر ' . number_format($rate, 0) . ')',
            'type'     => 'expense',
        ]);

        // SYP enters the drawer
        Expense::create([
            'date'     => $date,
            'amount'   => $syp,
            'currency' => 'SYP',
            'category' => 'تحويل عملة',
            'note'     => 'تحويل ' . number_format($usd, 2) . ' $ → ' . number_format($syp, 0) . ' ل.س (سعر ' . number_format($rate, 0) . ')',
            'type'     => 'exchange_in',
        ]);

        return back()->with('success', 'تم التحويل: ' . number_format($usd, 2) . ' $ → ' . number_format($syp, 0) . ' ل.س');
    }

    public function storeExchangeReverse(Request $request)
    {
        $request->validate([
            'syp_amount'      => 'required|numeric|min:1',
            'rev_exchange_rate'=> 'required|numeric|min:1',
            'date'            => 'required|date',
        ]);

        $syp  = (float) $request->syp_amount;
        $rate = (float) $request->rev_exchange_rate;
        $usd  = round($syp / $rate, 2);
        $date = $request->date;

        // SYP leaves the drawer
        Expense::create([
            'date'     => $date,
            'amount'   => $syp,
            'currency' => 'SYP',
            'category' => 'تحويل عملة',
            'note'     => 'تحويل ' . number_format($syp, 0) . ' ل.س → ' . number_format($usd, 2) . ' $ (سعر ' . number_format($rate, 0) . ')',
            'type'     => 'expense',
        ]);

        // USD enters the drawer
        Expense::create([
            'date'     => $date,
            'amount'   => $usd,
            'currency' => 'USD',
            'category' => 'تحويل عملة',
            'note'     => 'تحويل ' . number_format($syp, 0) . ' ل.س → ' . number_format($usd, 2) . ' $ (سعر ' . number_format($rate, 0) . ')',
            'type'     => 'exchange_in',
        ]);

        return back()->with('success', 'تم التحويل: ' . number_format($syp, 0) . ' ل.س → ' . number_format($usd, 2) . ' $');
    }

    public function setOpening(Request $request)
    {
        $request->validate([
            'opening_syp' => 'nullable|numeric|min:0',
            'opening_usd' => 'nullable|numeric|min:0',
            'date'        => 'required|date',
        ]);

        $key = 'opening_' . str_replace('-', '_', $request->date);
        Setting::set($key . '_syp', (float) ($request->opening_syp ?? 0));
        Setting::set($key . '_usd', (float) ($request->opening_usd ?? 0));

        return back()->with('success', 'تم حفظ رصيد الافتتاح');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', 'تم حذف المصروف');
    }
}
