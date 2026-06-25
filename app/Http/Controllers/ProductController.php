<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products   = $query->latest()->paginate(20);
        $categories = Category::all();
        $usdRate    = (int) Setting::get('usd_rate', 14000);

        return view('products.index', compact('products', 'categories', 'usdRate'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit'        => 'required|string|max:50',
            'qty'         => 'required|numeric|min:0',
            'min_qty'     => 'required|numeric|min:0',
            'price'       => 'required|numeric|min:0',
            'sell_price'  => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string',
        ]);

        Product::create($request->all());

        $msg = 'تم إضافة المنتج بنجاح';
        if ($request->filled('sell_price') && (float)$request->sell_price > 0 && (float)$request->sell_price < (float)$request->price) {
            $msg .= ' — ⚠️ تنبيه: سعر البيع أقل من سعر الشراء!';
        }
        return redirect()->route('products.index')->with('success', $msg);
    }

    public function show(Product $product)
    {
        return redirect()->route('products.edit', $product);
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit'        => 'required|string|max:50',
            'qty'         => 'required|numeric|min:0',
            'min_qty'     => 'required|numeric|min:0',
            'price'       => 'required|numeric|min:0',
            'sell_price'  => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string',
        ]);

        $product->update($request->all());

        $msg = 'تم تعديل المنتج بنجاح';
        if ($request->filled('sell_price') && (float)$request->sell_price > 0 && (float)$request->sell_price < (float)$request->price) {
            $msg .= ' — ⚠️ تنبيه: سعر البيع أقل من سعر الشراء!';
        }
        return redirect()->route('products.index')->with('success', $msg);
    }

    public function destroy(Product $product)
    {
        if ($product->stockMovements()->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'لا يمكن حذف المنتج "' . $product->name . '" لأنه يحتوي على سجل حركات — يمكنك تعطيله بدلاً من الحذف');
        }
        $product->delete();
        return redirect()->route('products.index')->with('success', 'تم حذف المنتج');
    }
}
