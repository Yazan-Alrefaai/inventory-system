<?php

namespace App\Http\Controllers;

use App\Models\DebtPayment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function createIn()
    {
        $products = Product::with('category')->orderBy('name')->get();
        return view('stock.in', compact('products'));
    }

    public function storeIn(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|numeric|min:0.001',
            'price'      => 'nullable|numeric|min:0',
            'note'       => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);
        $product->increment('qty', $request->qty);

        StockMovement::create([
            'product_id' => $request->product_id,
            'type'       => 'in',
            'qty'        => $request->qty,
            'price'      => $request->price ?? $product->price,
            'currency'   => 'USD',
            'note'       => $request->note,
        ]);

        return redirect()->route('stock.in')
            ->with('success', 'تم تسجيل الوارد — أضيف ' . $request->qty . ' ' . $product->unit . ' من ' . $product->name);
    }

    public function createOut()
    {
        $products = Product::with('category')->where('qty', '>', 0)->orderBy('name')->get();
        $usdRate  = (int) Setting::get('usd_rate', 14000);
        return view('stock.out', compact('products', 'usdRate'));
    }

    public function storeOut(Request $request)
    {
        $isMix = $request->input('payment_mode') === 'mix';

        $request->validate([
            'product_id'       => 'required|exists:products,id',
            'qty'              => 'required|numeric|min:0.001',
            'sale_price'       => 'nullable|numeric|min:0',
            'currency'         => 'required|in:USD,SYP',
            'exchange_rate'    => 'required_if:currency,SYP|nullable|numeric|min:1',
            'customer_name'    => 'nullable|string|max:255',
            'is_credit'        => 'nullable|boolean',
            'amount_paid'      => 'nullable|numeric|min:0',
            'mix_usd'          => 'nullable|numeric|min:0',
            'mix_syp'          => 'nullable|numeric|min:0',
            'mix_exchange_rate'=> 'nullable|numeric|min:1',
            'note'             => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($request->qty > $product->qty) {
            return back()
                ->withErrors(['qty' => 'الكمية المطلوبة (' . $request->qty . ') أكبر من المتاح (' . $product->qty . ' ' . $product->unit . ')'])
                ->withInput();
        }

        $currency     = $isMix ? 'USD' : $request->currency;
        $defaultPrice = $product->defaultSellPrice();
        $salePrice    = $request->filled('sale_price') ? (float) $request->sale_price : $defaultPrice;

        if ($currency === 'SYP' && !$request->filled('sale_price') && $request->filled('exchange_rate')) {
            $salePrice = round($defaultPrice * (float) $request->exchange_rate, 0);
        }

        $total    = round($salePrice * $request->qty, 2);
        $isCredit = (bool) $request->is_credit;

        $mixRate = 0.0;
        $mixUsd  = 0.0;
        $mixSyp  = 0.0;

        if ($isMix) {
            $mixRate    = (float) ($request->mix_exchange_rate ?? $request->exchange_rate ?? 14000);
            $mixUsd     = (float) ($request->mix_usd ?? 0);
            $mixSyp     = (float) ($request->mix_syp ?? 0);
            $mixSypAsUsd = $mixRate > 0 ? $mixSyp / $mixRate : 0;
            $mixUsdEq   = $mixUsd + $mixSypAsUsd;
            $amtPaid    = min($mixUsd, $total);
            $remaining  = max(0.0, $total - $mixUsdEq);
        } else {
            $amtPaid   = $isCredit ? round((float) ($request->amount_paid ?? 0), 2) : $total;
            $amtPaid   = min($amtPaid, $total);
            $remaining = round($total - $amtPaid, 2);
        }

        $product->decrement('qty', $request->qty);

        // للدفع المختلط: إذا سُدّد الكامل (أو أكثر) لا نسجّله كدين
        if ($isMix) {
            $isCredit = $remaining > 0.009;
        }

        $movement = StockMovement::create([
            'product_id'    => $request->product_id,
            'type'          => 'out',
            'qty'           => $request->qty,
            'price'         => $salePrice,
            'currency'      => $currency,
            'exchange_rate' => ($currency === 'SYP' || $isMix) ? ($request->mix_exchange_rate ?? $request->exchange_rate) : null,
            'customer_name' => $request->customer_name,
            'amount_paid'   => $amtPaid,
            'is_credit'     => $isCredit,
            'note'          => $request->note,
        ]);

        // إذا دفع بالليرة ضمن الدفع المختلط، نسجّله كـ DebtPayment فوراً
        if ($isMix && isset($mixSyp) && $mixSyp > 0) {
            DebtPayment::create([
                'movement_id'   => $movement->id,
                'amount'        => $mixSyp,
                'pay_currency'  => 'SYP',
                'exchange_rate' => $mixRate,
                'note'          => 'دفع مختلط — الجزء بالليرة',
            ]);
        }

        $sym = $currency === 'SYP' ? 'ل.س' : '$';

        if ($isMix) {
            $parts = [];
            if (($mixUsd ?? 0) > 0) $parts[] = number_format($mixUsd, 2) . ' $';
            if (($mixSyp ?? 0) > 0) $parts[] = number_format($mixSyp, 0) . ' ل.س';
            $paidStr = implode(' + ', $parts) ?: '0';
            if ($remaining > 0.01) {
                $msg = 'تم البيع بدفع مختلط ✓ — دفع: ' . $paidStr . ' — متبقي: ' . number_format($remaining, 2) . ' $';
            } else {
                $msg = 'تم البيع نقداً (مختلط) ✓ — ' . $paidStr;
            }
        } elseif ($isCredit && $remaining > 0) {
            $msg = 'تم البيع بالدين ✓ — ' . ($request->customer_name ?? 'زبون') .
                   ' — دفع: ' . number_format($amtPaid, 0) . ' ' . $sym .
                   ' — متبقي: ' . number_format($remaining, 0) . ' ' . $sym;
        } else {
            $msg = 'تم البيع نقداً ✓ — ' . number_format($total, 0) . ' ' . $sym;
        }

        return redirect()->route('stock.receipt', $movement)->with('success', $msg);
    }

    public function receipt(StockMovement $movement)
    {
        $movement->load('product', 'debtPayments');
        return view('stock.receipt', compact('movement'));
    }

    public function destroy(StockMovement $movement)
    {
        // Invoice movements must be deleted via the Sale page
        if ($movement->sale_id) {
            return back()->with('error', 'هذه الحركة جزء من فاتورة — لحذفها اذهب لصفحة الفاتورة');
        }

        // Reverse the stock change
        if ($movement->type === 'out') {
            $movement->product->increment('qty', $movement->qty);
        } else {
            $movement->product->decrement('qty', $movement->qty);
        }
        $movement->debtPayments()->delete();
        $movement->delete();
        return back()->with('success', 'تم حذف الحركة وإعادة الكمية إلى المخزون');
    }

    public function history(Request $request)
    {
        $query        = StockMovement::with('product.category', 'debtPayments', 'sale');
        $statsQuery   = StockMovement::where('type', 'out')->whereNotNull('price');
        $isFiltered   = $request->filled('type') || $request->filled('date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
            $statsQuery->whereDate('created_at', $request->date);
        }

        $movements = $query->latest()->paginate(30);

        // اليوم دائماً — للكارت الأول
        $todaySales = StockMovement::where('type', 'out')
            ->whereNotNull('price')
            ->whereDate('created_at', today())
            ->get()
            ->sum(fn($m) => $m->totalAmountUsd());

        // الإجمالي يحترم فلتر التاريخ إذا كان مفعّلاً
        $totalSales  = $statsQuery->get()->sum(fn($m) => $m->totalAmountUsd());
        $filterLabel = $request->filled('date')
            ? \Carbon\Carbon::parse($request->date)->format('d/m/Y')
            : 'كل الوقت';

        return view('stock.history', compact('movements', 'totalSales', 'todaySales', 'filterLabel', 'isFiltered'));
    }
}
