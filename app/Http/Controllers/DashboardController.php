<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DebtPayment;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\Setting;
use App\Models\StockMovement;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts    = Product::count();
        $totalQty         = Product::sum('qty');
        $totalValue       = Product::selectRaw('SUM(qty * price) as total')->value('total') ?? 0;
        $totalSellValue   = Product::where('sell_price', '>', 0)->selectRaw('SUM(qty * sell_price) as total')->value('total') ?? 0;
        $lowStockProducts = Product::whereColumn('qty', '<=', 'min_qty')->with('category')->get();
        $recentMovements  = StockMovement::with('product')->latest()->take(8)->get();

        $totalIn  = StockMovement::where('type', 'in')->whereDate('created_at', today())->sum('qty');
        $totalOut = StockMovement::where('type', 'out')->whereDate('created_at', today())->sum('qty');

        // Load today's out movements once — reused for multiple stats
        $todayOutMvmts = StockMovement::where('type', 'out')
            ->whereNotNull('price')
            ->whereDate('created_at', today())
            ->with('product')
            ->get();

        $todaySalesVal = $todayOutMvmts->sum(fn($m) => $m->totalAmountUsd());
        $todaySalesSyp = $todayOutMvmts->where('currency', 'SYP')->sum(fn($m) => $m->totalAmount());

        // Gross profit today (revenue USD − cost of goods at buy price)
        $todayProfitUsd = $todayOutMvmts->sum(function ($m) {
            $revenue = $m->totalAmountUsd();
            $cost    = $m->product ? round($m->product->price * $m->qty, 2) : 0;
            return $revenue - $cost;
        });

        // Week-over-week sales comparison
        $thisWeekSales = StockMovement::where('type', 'out')->whereNotNull('price')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->get()->sum(fn($m) => $m->totalAmountUsd());

        $lastWeekSales = StockMovement::where('type', 'out')->whereNotNull('price')
            ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->get()->sum(fn($m) => $m->totalAmountUsd());

        $weekChangePct = ($lastWeekSales > 0)
            ? round(($thisWeekSales - $lastWeekSales) / $lastWeekSales * 100, 1)
            : null;

        // Top 5 best-selling products this week
        $topSellerRows = StockMovement::where('type', 'out')
            ->whereBetween('created_at', [now()->startOfWeek(), now()])
            ->selectRaw('product_id, SUM(qty) as total_qty')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();
        $topSellerProducts = Product::findMany($topSellerRows->pluck('product_id'));
        $topSellers = $topSellerRows->map(fn($r) => [
            'product'   => $topSellerProducts->find($r->product_id),
            'total_qty' => (float) $r->total_qty,
        ])->filter(fn($r) => $r['product'] !== null);

        // Dead stock: products in stock with no sales in last 30 days
        $soldInLast30   = StockMovement::where('type', 'out')
            ->where('created_at', '>=', now()->subDays(30))
            ->distinct()->pluck('product_id');
        $deadStockCount = Product::where('qty', '>', 0)
            ->whereNotIn('id', $soldInLast30)
            ->count();
        $deadProducts   = Product::where('qty', '>', 0)
            ->whereNotIn('id', $soldInLast30)
            ->with('category')
            ->orderByDesc('qty')
            ->take(6)
            ->get();

        // Debt counts + totals + oldest
        $movementDebts    = StockMovement::where('type', 'out')->where('is_credit', true)
            ->whereNull('sale_id')->with('debtPayments')->get()->filter(fn($m) => $m->remaining() > 0);
        $saleDebts        = Sale::where('is_credit', true)->get()->filter(fn($s) => $s->remaining() > 0);
        $activeDebtsCount = $movementDebts->count() + $saleDebts->count();
        $totalDebtSyp     = $movementDebts->where('currency', 'SYP')->sum(fn($m) => $m->remaining())
                          + $saleDebts->where('currency', 'SYP')->sum(fn($s) => $s->remaining());
        $totalDebtUsd     = $movementDebts->where('currency', 'USD')->sum(fn($m) => $m->remaining())
                          + $saleDebts->where('currency', 'USD')->sum(fn($s) => $s->remaining());

        // Oldest unpaid debt age in days
        $oldestM = $movementDebts->sortBy('created_at')->first();
        $oldestS = $saleDebts->sortBy('created_at')->first();
        if ($oldestM && $oldestS) {
            $oldest = $oldestM->created_at->lt($oldestS->created_at) ? $oldestM : $oldestS;
        } else {
            $oldest = $oldestM ?? $oldestS;
        }
        $oldestDebtDays = $oldest ? $oldest->created_at->diffInDays(now()) : null;

        // Cash in drawer today
        $drawerKey     = 'opening_' . today()->format('Y_m_d');
        $drawerOpenSyp = (float) Setting::get($drawerKey . '_syp', 0);
        $drawerOpenUsd = (float) Setting::get($drawerKey . '_usd', 0);

        // 1. Cash stock.out movements with no invoice (direct sales, no sale_id)
        $cashOutToday = StockMovement::where('type', 'out')->whereNotNull('price')
            ->whereNull('sale_id')->whereDate('created_at', today())->get();
        // Fully-cash direct movements: use amount_paid (= totalAmount for non-credit, correct for MIX)
        $cashSalesSyp = $cashOutToday->where('currency', 'SYP')->where('is_credit', false)->sum('amount_paid');
        $cashSalesUsd = $cashOutToday->where('currency', 'USD')->where('is_credit', false)->sum('amount_paid');
        // Legacy credit direct movements: down payment
        $cashSalesSyp += $cashOutToday->where('currency', 'SYP')->where('is_credit', true)->sum('amount_paid');
        $cashSalesUsd += $cashOutToday->where('currency', 'USD')->where('is_credit', true)->sum('amount_paid');

        // 2. Invoice-based non-credit sales (fully paid): use totalAmount per currency
        // For mixed payments a SalePayment record exists per currency — skip those (counted in step 4)
        $cashInvoiceMvmt = StockMovement::where('type', 'out')->where('is_credit', false)
            ->whereNotNull('sale_id')->whereDate('created_at', today())
            ->with(['sale' => fn($q) => $q->with(['salePayments' => fn($q2) => $q2->whereDate('created_at', today())])])->get();
        foreach ($cashInvoiceMvmt as $m) {
            if (!$m->sale || $m->sale->salePayments->isNotEmpty()) { continue; }
            if ($m->currency === 'SYP') { $cashSalesSyp += $m->totalAmount(); }
            else                        { $cashSalesUsd += $m->totalAmount(); }
        }

        // 3. Credit invoices created today: initial down payment ONLY (subtract follow-up SalePayments made TODAY)
        $creditInvoices = Sale::where('is_credit', true)->whereDate('created_at', today())
            ->with(['salePayments' => fn($q) => $q->whereDate('created_at', today())])->get();
        $cashSalesSyp  += $creditInvoices->where('currency', 'SYP')->sum(function ($s) {
            $followup = $s->salePayments->sum(fn($p) => $p->amountInSaleCurrency($s));
            return max(0, (float) $s->amount_paid - $followup);
        });
        $cashSalesUsd  += $creditInvoices->where('currency', 'USD')->sum(function ($s) {
            $followup = $s->salePayments->sum(fn($p) => $p->amountInSaleCurrency($s));
            return max(0, (float) $s->amount_paid - $followup);
        });

        // 4. Follow-up invoice payments received today (SalePayment table, by pay_currency)
        $spToday = SalePayment::whereDate('created_at', today())->get();
        $cashSalesSyp += $spToday->where('pay_currency', 'SYP')->sum('amount');
        $cashSalesUsd += $spToday->where('pay_currency', 'USD')->sum('amount');

        // 5. Debt follow-up payments received today (DebtPayment table, by pay_currency)
        $debtPaymentsToday = DebtPayment::whereDate('created_at', today())
            ->with('movement')->get()
            ->filter(fn($dp) => $dp->movement !== null);
        $cashSalesSyp += $debtPaymentsToday->filter(fn($dp) => $dp->pay_currency === 'SYP')->sum('amount');
        $cashSalesUsd += $debtPaymentsToday->filter(fn($dp) => $dp->pay_currency === 'USD')->sum('amount');

        $expSyp    = Expense::whereDate('date', today())->where('currency', 'SYP')->where('type', 'expense')->sum('amount');
        $expUsd    = Expense::whereDate('date', today())->where('currency', 'USD')->where('type', 'expense')->sum('amount');
        $exchInSyp = Expense::whereDate('date', today())->where('currency', 'SYP')->where('type', 'exchange_in')->sum('amount');
        $exchInUsd = Expense::whereDate('date', today())->where('currency', 'USD')->where('type', 'exchange_in')->sum('amount');

        $cashInDrawerSyp = $drawerOpenSyp + $cashSalesSyp + $exchInSyp - $expSyp;
        $cashInDrawerUsd = $drawerOpenUsd + $cashSalesUsd + $exchInUsd - $expUsd;

        // Chart: last 7 days
        $days        = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('Y-m-d'));
        $chartLabels = $days->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toJson();
        $chartIn     = $days->map(fn($d) => StockMovement::where('type','in')->whereDate('created_at',$d)->sum('qty'))->toJson();
        $chartOut    = $days->map(fn($d) => StockMovement::where('type','out')->whereDate('created_at',$d)->sum('qty'))->toJson();
        $chartSales  = $days->map(fn($d) => round((float) StockMovement::where('type','out')->whereNotNull('price')->whereDate('created_at',$d)->get()->sum(fn($m) => $m->totalAmountUsd()), 2))->toJson();

        // Chart: by category
        $categories = Category::with('products')->get();
        $catLabels  = $categories->pluck('name')->toJson();
        $catQty     = $categories->map(fn($c) => $c->products->sum('qty'))->toJson();

        // Quick sale: all products with stock
        $allProducts = Product::where('qty', '>', 0)->with('category')->orderBy('name')->get();

        $usdRate = (int) Setting::get('usd_rate', 14000);

        return view('dashboard', compact(
            'totalProducts','totalQty','totalValue','totalSellValue',
            'todaySalesVal','todaySalesSyp','todayProfitUsd',
            'weekChangePct','thisWeekSales',
            'topSellers','deadStockCount','deadProducts',
            'activeDebtsCount','totalDebtSyp','totalDebtUsd','oldestDebtDays',
            'cashInDrawerSyp','cashInDrawerUsd','drawerOpenSyp','drawerOpenUsd',
            'lowStockProducts','recentMovements','totalIn','totalOut',
            'chartLabels','chartIn','chartOut','chartSales','catLabels','catQty',
            'allProducts','usdRate'
        ));
    }
}
