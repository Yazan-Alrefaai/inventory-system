<?php

namespace App\Http\Controllers;

use App\Models\DebtPayment;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        // Legacy single-item debts (stock.out page)
        $allMovementDebts = StockMovement::where('type', 'out')
            ->where('is_credit', true)
            ->whereNull('sale_id')
            ->when($search, fn($q) => $q->where('customer_name', 'like', '%' . $search . '%'))
            ->with('product.category', 'debtPayments')
            ->latest()
            ->get();

        $activeMovementDebts = $allMovementDebts->filter(fn($m) => $m->remaining() > 0);
        $paidMovementDebts   = $allMovementDebts->filter(fn($m) => $m->remaining() <= 0);

        // Invoice-based debts (sales system)
        $allSaleDebts = Sale::where('is_credit', true)
            ->when($search, fn($q) => $q->where('customer_name', 'like', '%' . $search . '%'))
            ->with('items.product', 'salePayments')
            ->latest()
            ->get();
        $activeSaleDebts = $allSaleDebts->filter(fn($s) => $s->remaining() > 0);
        $paidSaleDebts   = $allSaleDebts->filter(fn($s) => $s->remaining() <= 0);

        return view('debts.index', compact(
            'activeMovementDebts', 'paidMovementDebts',
            'activeSaleDebts', 'paidSaleDebts', 'search'
        ));
    }

    public function show(StockMovement $movement)
    {
        // Invoice-based debts belong to the Sale page
        if ($movement->sale_id) {
            return redirect()->route('sales.show', $movement->sale_id);
        }
        $movement->load('product', 'debtPayments');
        return view('debts.show', compact('movement'));
    }

    public function pay(Request $request, StockMovement $movement)
    {
        $payCurrency = $request->pay_currency ?? $movement->currency;
        $needsRate   = $payCurrency !== $movement->currency;

        $request->validate([
            'amount'        => 'required|numeric|min:0.01',
            'pay_currency'  => 'required|in:SYP,USD',
            'exchange_rate' => $needsRate ? 'required|numeric|min:1' : 'nullable|numeric|min:1',
            'note'          => 'nullable|string|max:255',
        ]);

        $payCurrency  = $request->pay_currency;
        $exchangeRate = $needsRate ? (float) $request->exchange_rate : null;

        // Build a temporary DebtPayment to check conversion
        $tempDp = new DebtPayment([
            'amount'        => (float) $request->amount,
            'pay_currency'  => $payCurrency,
            'exchange_rate' => $exchangeRate,
        ]);
        $amountInMovCurrency = $tempDp->amountInMovementCurrency($movement);

        DB::transaction(function () use ($movement, $request, $payCurrency, $exchangeRate, $amountInMovCurrency) {
            $fresh     = StockMovement::lockForUpdate()->find($movement->id);
            $fresh->load('debtPayments');
            $remaining = $fresh->remaining();

            if ($remaining <= 0) {
                return;
            }

            if ($amountInMovCurrency > $remaining) {
                $scale  = $remaining / $amountInMovCurrency;
                $capped = round((float) $request->amount * $scale, $payCurrency === 'SYP' ? 0 : 2);
            } else {
                $capped = (float) $request->amount;
            }

            DebtPayment::create([
                'movement_id'   => $fresh->id,
                'amount'        => $capped,
                'pay_currency'  => $payCurrency,
                'exchange_rate' => $exchangeRate,
                'note'          => $request->note,
            ]);
        });

        $movement->load('debtPayments');
        $sym = $movement->currencySymbol();
        $dec = $movement->currency === 'SYP' ? 0 : 2;

        if ($movement->isFullyPaid()) {
            return redirect()->route('debts.index')
                ->with('success', '✅ تم سداد دين ' . ($movement->customer_name ?? 'الزبون') . ' بالكامل!');
        }

        return redirect()->route('debts.show', $movement)
            ->with('success', 'تم تسجيل الدفعة — متبقي: ' . number_format($movement->remaining(), $dec) . ' ' . $sym);
    }
}
