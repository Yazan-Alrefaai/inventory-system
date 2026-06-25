<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:categories,name']);
        Category::create(['name' => $request->name]);
        return back()->with('success', 'تمت إضافة التصنيف: ' . $request->name);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|string|max:100|unique:categories,name,' . $category->id]);
        $category->update(['name' => $request->name]);
        return back()->with('success', 'تم تعديل التصنيف بنجاح');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'لا يمكن حذف التصنيف لأنه يحتوي على منتجات — انقل المنتجات أولاً');
        }
        $category->delete();
        return back()->with('success', 'تم حذف التصنيف: ' . $category->name);
    }
}
