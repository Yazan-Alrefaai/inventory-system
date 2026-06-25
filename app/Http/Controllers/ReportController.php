<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Determine date range
        $preset = $request->input('preset', 'month');
        $from   = $request->input('from');
        $to     = $request->input('to');

        if ($from && $to) {
            $dateFrom = Carbon::parse($from)->startOfDay();
            $dateTo   = Carbon::parse($to)->endOfDay();
            $preset   = 'custom';
        } else {
            [$dateFrom, $dateTo] = match($preset) {
                'today'  => [now()->startOfDay(), now()->endOfDay()],
                'week'   => [now()->startOfWeek(), now()->endOfWeek()],
                'month'  => [now()->startOfMonth(), now()->endOfMonth()],
                'year'   => [now()->startOfYear(), now()->endOfYear()],
                default  => [now()->startOfMonth(), now()->endOfMonth()],
            };
        }

        $type = $request->input('type', '');

        // Base query
        $base = StockMovement::with(['product', 'debtPayments', 'sale'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($type) { $base->where('type', $type); }

        $movements = (clone $base)->latest()->get();

        // Summary totals
        $salesMovements    = $movements->where('type', 'out');
        $purchaseMovements = $movements->where('type', 'in');

        $totalSalesUsd = $salesMovements->sum(fn($m) => $m->totalAmountUsd());
        $totalSalesSyp = $salesMovements->where('currency', 'SYP')->sum(fn($m) => $m->totalAmount());


        $totalPurchasesUsd = $purchaseMovements->sum(fn($m) => ($m->price ?? 0) * $m->qty);
        $totalItemsSold    = $salesMovements->sum('qty');
        $totalItemsBought  = $purchaseMovements->sum('qty');
        $totalTransactions = $movements->count();

        $cashSales   = $salesMovements->where('is_credit', false)->sum(fn($m) => $m->totalAmountUsd());
        $creditSales = $salesMovements->where('is_credit', true)->sum(fn($m) => $m->totalAmountUsd());

        // Profit = revenue - cost (product->price is always stored in USD)
        $totalCostUsd = $salesMovements->sum(function ($m) {
            return round(($m->product->price ?? 0) * $m->qty, 2);
        });
        $totalProfitUsd  = round($totalSalesUsd - $totalCostUsd, 2);
        $profitMarginPct = $totalSalesUsd > 0 ? round($totalProfitUsd / $totalSalesUsd * 100, 1) : 0;

        // Profit breakdown: cash received vs still-unpaid credit
        $cashMovements   = $salesMovements->where('is_credit', false);
        $creditMovements = $salesMovements->where('is_credit', true);

        $cashCostUsd  = $cashMovements->sum(fn($m) => round(($m->product->price ?? 0) * $m->qty, 2));
        $cashSalesUsd = $cashMovements->sum(fn($m) => $m->totalAmountUsd());
        $cashProfitUsd = round($cashSalesUsd - $cashCostUsd, 2);

        // Add collected portion of credit sales:
        //   - Invoice-based (sale_id set): use Sale::amount_paid (updated by follow-up payments)
        //   - Direct movement (no sale_id): use movement::amount_paid
        $directCredit  = $creditMovements->whereNull('sale_id');
        $invoiceCredit = $creditMovements->whereNotNull('sale_id');

        $collectedCreditProfitUsd = 0;

        // Direct credit movements
        foreach ($directCredit as $m) {
            $total = $m->totalAmount();
            if ($total <= 0) { continue; }
            $ratio   = min(1, (float) $m->amount_paid / $total);
            $revenue = $m->totalAmountUsd();
            $cost    = round(($m->product->price ?? 0) * $m->qty, 2);
            $collectedCreditProfitUsd += ($revenue - $cost) * $ratio;
        }

        // Invoice-based credit movements: group per sale to avoid double-counting
        foreach ($invoiceCredit->groupBy('sale_id') as $saleId => $group) {
            $sale = $group->first()->sale;
            if (!$sale) { continue; }
            $saleTotal = $sale->totalAmount();
            if ($saleTotal <= 0) { continue; }
            $ratio   = min(1, (float) $sale->amount_paid / $saleTotal);
            $revenue = $group->sum(fn($m) => $m->totalAmountUsd());
            $cost    = $group->sum(fn($m) => round(($m->product->price ?? 0) * $m->qty, 2));
            $collectedCreditProfitUsd += ($revenue - $cost) * $ratio;
        }

        $cashProfitUsd   = round($cashProfitUsd + $collectedCreditProfitUsd, 2);
        $creditProfitUsd = round($totalProfitUsd - $cashProfitUsd, 2);

        // Profit in SYP (for SYP sales) and USD (for USD sales) separately
        $sypMovements = $salesMovements->where('currency', 'SYP');
        $usdMovements = $salesMovements->where('currency', 'USD');

        $totalProfitSyp = round($sypMovements->sum(function ($m) {
            if (!$m->exchange_rate || $m->exchange_rate <= 0) { return 0; }
            $sell = round($m->price * $m->qty, 2);
            $cost = round(($m->product->price ?? 0) * $m->qty * $m->exchange_rate, 2);
            return $sell - $cost;
        }));

        $totalProfitUsdOnly = round($usdMovements->sum(function ($m) {
            $sell = round($m->price * $m->qty, 2);
            $cost = round(($m->product->price ?? 0) * $m->qty, 2);
            return $sell - $cost;
        }), 2);

        // Chart data — group by day
        $days = collect();
        $cur  = $dateFrom->copy();
        while ($cur->lte($dateTo)) {
            $days->push($cur->format('Y-m-d'));
            $cur->addDay();
        }
        // Cap at 60 days for chart readability
        if ($days->count() > 60) {
            // group by month instead
            $chartMode   = 'month';
            $chartLabels = [];
            $chartSales  = [];
            $chartBuys   = [];
            $cur = $dateFrom->copy()->startOfMonth();
            while ($cur->lte($dateTo)) {
                $label = $cur->format('m/Y');
                $chartLabels[] = $label;
                $chartSales[]  = round($salesMovements->filter(fn($m) => Carbon::parse($m->created_at)->format('m/Y') === $label)->sum(fn($m) => $m->totalAmountUsd()), 2);
                $chartBuys[]   = round($purchaseMovements->filter(fn($m) => Carbon::parse($m->created_at)->format('m/Y') === $label)->sum(fn($m) => ($m->price ?? 0) * $m->qty), 2);
                $cur->addMonth();
            }
        } else {
            $chartMode   = 'day';
            $chartLabels = $days->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray();
            $chartSales  = $days->map(fn($d) => round($salesMovements->filter(fn($m) => Carbon::parse($m->created_at)->format('Y-m-d') === $d)->sum(fn($m) => $m->totalAmountUsd()), 2))->toArray();
            $chartBuys   = $days->map(fn($d) => round($purchaseMovements->filter(fn($m) => Carbon::parse($m->created_at)->format('Y-m-d') === $d)->sum(fn($m) => ($m->price ?? 0) * $m->qty), 2))->toArray();
        }

        // Top products by sales qty
        $topProducts = $salesMovements->groupBy('product_id')
            ->map(fn($g) => [
                'name'  => $g->first()->product->name ?? '(محذوف)',
                'qty'   => $g->sum('qty'),
                'value' => round($g->sum(fn($m) => $m->totalAmountUsd()), 2),
            ])
            ->sortByDesc('qty')
            ->take(5)
            ->values();

        $exportParams = $request->only('from', 'to', 'preset', 'type');

        return view('reports.index', compact(
            'movements', 'preset', 'from', 'to', 'type',
            'dateFrom', 'dateTo',
            'totalSalesUsd', 'totalSalesSyp',
            'totalPurchasesUsd', 'totalItemsSold', 'totalItemsBought',
            'totalTransactions', 'cashSales', 'creditSales',
            'totalCostUsd', 'totalProfitUsd', 'profitMarginPct',
            'cashProfitUsd', 'creditProfitUsd',
            'totalProfitSyp', 'totalProfitUsdOnly',
            'chartLabels', 'chartSales', 'chartBuys', 'chartMode',
            'topProducts', 'exportParams'
        ));
    }

    public function export(Request $request)
    {
        $preset = $request->input('preset', 'month');
        $from   = $request->input('from');
        $to     = $request->input('to');

        if ($from && $to) {
            try {
                $dateFrom = Carbon::parse($from)->startOfDay();
                $dateTo   = Carbon::parse($to)->endOfDay();
            } catch (\Exception) {
                $dateFrom = now()->startOfMonth();
                $dateTo   = now()->endOfMonth();
            }
        } else {
            [$dateFrom, $dateTo] = match($preset) {
                'today' => [now()->startOfDay(), now()->endOfDay()],
                'week'  => [now()->startOfWeek(), now()->endOfWeek()],
                'year'  => [now()->startOfYear(), now()->endOfYear()],
                default => [now()->startOfMonth(), now()->endOfMonth()],
            };
        }

        $type      = $request->input('type', '');
        $query     = StockMovement::with('product.category')->whereBetween('created_at', [$dateFrom, $dateTo]);
        if ($type) { $query->where('type', $type); }
        $movements = $query->latest()->get();

        $filename = 'تقرير-' . $dateFrom->format('Y-m-d') . '_' . $dateTo->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($movements) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens Arabic correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['التاريخ', 'المنتج', 'الصنف', 'النوع', 'الكمية', 'السعر', 'العملة', 'الإجمالي', 'الزبون', 'نقدي/آجل', 'ملاحظة']);
            foreach ($movements as $m) {
                fputcsv($out, [
                    $m->created_at->format('Y-m-d H:i'),
                    $m->product->name ?? '',
                    $m->product->category->name ?? '',
                    $m->type === 'in' ? 'وارد' : 'صادر',
                    $m->qty,
                    number_format($m->price ?? 0, 2, '.', ''),
                    $m->currency,
                    number_format($m->totalAmount(), 2, '.', ''),
                    $m->customer_name ?? '',
                    $m->is_credit ? 'آجل' : 'نقدي',
                    $m->note ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
