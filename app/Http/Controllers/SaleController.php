<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('items.product');

        if ($request->filled('search')) {
            $query->where('customer_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('status')) {
            if ($request->status === 'credit') $query->where('is_credit', true);
            if ($request->status === 'cash')   $query->where('is_credit', false);
        }

        $sales = $query->latest()->paginate(30);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::with('category')->where('qty', '>', 0)->orderBy('name')->get();
        $usdRate  = (int) Setting::get('usd_rate', 14000);
        return view('sales.create', compact('products', 'usdRate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'currency'      => 'required|in:SYP,USD',
            'exchange_rate' => 'required_if:currency,SYP|nullable|numeric|min:1',
            'is_credit'     => 'nullable|boolean',
            'amount_paid'   => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:100',
            'note'          => 'nullable|string|max:500',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|numeric|min:0.001',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        $items    = $request->input('items');
        $isCredit = (bool) $request->input('is_credit', false);

        DB::beginTransaction();
        try {
            // Validate stock availability
            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                if ($product->qty < $item['qty']) {
                    DB::rollBack();
                    return back()->withInput()
                        ->with('error', 'الكمية المطلوبة لـ "' . $product->name . '" (' . $item['qty'] . ') أكبر من المتوفر (' . $product->qty . ')');
                }
            }

            $total      = collect($items)->sum(fn($i) => $i['price'] * $i['qty']);
            $amountPaid = $isCredit ? (float) $request->input('amount_paid', 0) : $total;

            $sale = Sale::create([
                'customer_name' => $request->input('customer_name'),
                'currency'      => $request->input('currency'),
                'exchange_rate' => $request->input('exchange_rate'),
                'is_credit'     => $isCredit,
                'amount_paid'   => $amountPaid,
                'note'          => $request->input('note'),
            ]);

            foreach ($items as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'price'      => $item['price'],
                ]);

                // Decrement stock
                Product::where('id', $item['product_id'])->decrement('qty', $item['qty']);

                // Create StockMovement so the sale appears in history/dashboard/reports
                $itemTotal   = $item['price'] * $item['qty'];
                $proportional = ($total > 0) ? round($amountPaid * $itemTotal / $total, 2) : 0;
                $itemPaid     = $isCredit ? $proportional : $itemTotal;

                StockMovement::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $item['product_id'],
                    'type'          => 'out',
                    'qty'           => $item['qty'],
                    'price'         => $item['price'],
                    'currency'      => $sale->currency,
                    'exchange_rate' => $sale->exchange_rate,
                    'is_credit'     => $sale->is_credit,
                    'amount_paid'   => $itemPaid,
                    'customer_name' => $sale->customer_name,
                    'note'          => $sale->invoiceNumber(),
                ]);
            }

            // Mixed payment: store per-currency components for accurate drawer tracking
            $mixUsd  = (float) $request->input('mix_usd_paid', 0);
            $mixSyp  = (float) $request->input('mix_syp_paid', 0);
            $mixRate = (float) $request->input('mix_exchange_rate', 1);
            if ($mixUsd > 0 || $mixSyp > 0) {
                if ($mixUsd > 0) {
                    SalePayment::create([
                        'sale_id'       => $sale->id,
                        'amount'        => $mixUsd,
                        'pay_currency'  => 'USD',
                        'exchange_rate' => $mixRate,
                        'note'          => 'دفع مختلط — الجزء بالدولار',
                    ]);
                }
                if ($mixSyp > 0) {
                    SalePayment::create([
                        'sale_id'       => $sale->id,
                        'amount'        => $mixSyp,
                        'pay_currency'  => 'SYP',
                        'exchange_rate' => $mixRate,
                        'note'          => 'دفع مختلط — الجزء بالليرة',
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء الحفظ: ' . $e->getMessage());
        }

        return redirect()->route('sales.show', $sale)->with('success', 'تم إنشاء الفاتورة ' . $sale->invoiceNumber() . ' بنجاح');
    }

    public function show(Sale $sale)
    {
        $sale->load('items.product', 'salePayments');
        $usdRate = (int) Setting::get('usd_rate', 14000);
        $saleCurr = $sale->currency;
        return view('sales.show', compact('sale', 'usdRate', 'saleCurr'));
    }

    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'customer_name'  => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:500',
            'prices'         => 'nullable|array',
            'prices.*'       => 'nullable|numeric|min:0',
        ]);

        $sale->update([
            'customer_name' => $request->input('customer_name'),
            'note'          => $request->input('note'),
        ]);

        if ($request->filled('prices')) {
            foreach ($request->input('prices') as $itemId => $price) {
                $item = $sale->items()->find($itemId);
                if ($item && $price !== null) {
                    $oldPrice = (float) $item->price;
                    $item->update(['price' => (float) $price]);
                    StockMovement::where('sale_id', $sale->id)
                        ->where('product_id', $item->product_id)
                        ->where('price', $oldPrice)
                        ->update(['price' => (float) $price]);
                }
            }
            // For non-credit sales, keep amount_paid = new total so remaining() stays 0
            $sale->refresh();
            if (!$sale->is_credit) {
                $sale->update(['amount_paid' => $sale->totalAmount()]);
            }
        }

        return back()->with('success', 'تم تحديث الفاتورة ' . $sale->invoiceNumber());
    }

    public function pay(Request $request, Sale $sale)
    {
        $payCurrency = $request->input('pay_currency', $sale->currency);
        $needsRate   = $payCurrency !== $sale->currency;

        $request->validate([
            'amount'        => 'required|numeric|min:0.01',
            'pay_currency'  => 'nullable|in:SYP,USD',
            'exchange_rate' => $needsRate ? 'required|numeric|min:1' : 'nullable|numeric|min:1',
            'note'          => 'nullable|string|max:255',
        ]);

        $rate = (float) $request->input('exchange_rate', 1);

        // Convert to sale currency if paying with a different currency
        if ($needsRate) {
            if ($payCurrency === 'USD' && $sale->currency === 'SYP') {
                $inSaleCurrency = (float) $request->amount * $rate;
            } else {
                $inSaleCurrency = $rate > 0 ? (float) $request->amount / $rate : 0;
            }
        } else {
            $inSaleCurrency = (float) $request->amount;
        }

        DB::transaction(function () use ($sale, $request, $payCurrency, $needsRate, $rate, $inSaleCurrency) {
            // Re-read inside transaction with lock to prevent concurrent overpayment
            $fresh     = Sale::lockForUpdate()->find($sale->id);
            $remaining = $fresh->remaining();

            if ($remaining <= 0) {
                return; // already paid, do nothing
            }

            $payment = min(round($inSaleCurrency, $fresh->currency === 'SYP' ? 0 : 2), $remaining);
            $fresh->increment('amount_paid', $payment);

            SalePayment::create([
                'sale_id'       => $fresh->id,
                'amount'        => (float) $request->amount,
                'pay_currency'  => $payCurrency,
                'exchange_rate' => $needsRate ? $rate : null,
                'note'          => $request->note,
            ]);
        });

        $sale->refresh();

        $sym = $sale->currencySymbol();
        $dec = $sale->currency === 'SYP' ? 0 : 2;

        if ($sale->isFullyPaid()) {
            return redirect()->route('debts.index')->with('success', 'تم سداد الفاتورة ' . $sale->invoiceNumber() . ' بالكامل ✅');
        }

        return back()->with('success', 'تم تسجيل الدفعة — المتبقي: ' . number_format($sale->remaining(), $dec) . ' ' . $sym);
    }

    public function destroy(Sale $sale)
    {
        DB::beginTransaction();
        try {
            // Restore stock and delete movements created for this sale
            foreach ($sale->items as $item) {
                Product::where('id', $item->product_id)->increment('qty', $item->qty);
            }
            StockMovement::where('sale_id', $sale->id)->delete();
            $sale->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'فشل الحذف: ' . $e->getMessage());
        }

        return redirect()->route('sales.index')->with('success', 'تم حذف الفاتورة وإعادة المخزون');
    }
}
