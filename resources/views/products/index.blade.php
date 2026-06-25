@extends('layouts.app')
@section('title', 'المنتجات')

@section('header')
<div style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">إدارة المنتجات</h1>
        <p style="color:#64748b; font-size:14px; margin:4px 0 0;">{{ $products->total() }} منتج مسجل</p>
    </div>
    <a href="{{ route('products.create') }}" class="btn-primary">+ إضافة منتج جديد</a>
</div>
@endsection

@section('content')

{{-- Filters --}}
<div class="card" style="padding:20px; margin-bottom:20px;">
    <form method="GET" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
        <div style="flex:1; min-width:200px; position:relative;">
            <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#94a3b8;">🔍</span>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="ابحث باسم المنتج..."
                   class="input-field" style="padding-right:36px;">
        </div>
        <select name="category_id" class="input-field" style="width:180px;">
            <option value="">كل الأصناف</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id ? 'selected':'' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary" style="padding:10px 20px;">بحث</button>
        @if(request()->hasAny(['search','category_id']))
            <a href="{{ route('products.index') }}" style="padding:10px 16px; border:1.5px solid #e2e8f0; border-radius:10px; color:#64748b; font-size:14px; font-weight:500; text-decoration:none; background:#fff;">مسح</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="card" style="overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th style="width:35%;">المنتج</th>
                <th>الصنف</th>
                <th>الكمية الحالية</th>
                <th>مستوى المخزون</th>
                <th>سعر الشراء</th>
                <th>سعر البيع</th>
                <th style="text-align:center;">هامش الربح</th>
                <th style="text-align:center;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            @php
                $pct = $product->min_qty > 0 ? min(100, round($product->qty / ($product->min_qty * 2) * 100)) : 100;
                $barColor = $product->isLowStock() ? '#ef4444' : ($pct < 75 ? '#f97316' : '#10b981');
            @endphp
            <tr>
                <td>
                    <div style="font-weight:700; color:#0f172a; font-size:14px;">{{ $product->name }}</div>
                    @if($product->notes)
                        <div style="color:#94a3b8; font-size:12px; margin-top:2px;">{{ Str::limit($product->notes, 50) }}</div>
                    @endif
                </td>
                <td>
                    <span style="background:#eff6ff; color:#3b82f6; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;">{{ $product->category->name }}</span>
                </td>
                <td>
                    <span style="font-size:20px; font-weight:800; color:{{ $product->isLowStock() ? '#ef4444' : '#0f172a' }};">@qty($product->qty)</span>
                    <span style="color:#94a3b8; font-size:13px;"> {{ $product->unit }}</span>
                    @if($product->isLowStock())
                        <div><span style="background:#fef2f2; color:#dc2626; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px;">منخفض ⚠️</span></div>
                    @endif
                </td>
                <td style="min-width:120px;">
                    <div style="margin-bottom:4px; font-size:11px; color:#94a3b8;">الحد الأدنى: @qty($product->min_qty)</div>
                    <div class="stock-bar">
                        <div class="stock-bar-fill" style="width:{{ $pct }}%; background:{{ $barColor }};"></div>
                    </div>
                </td>
                <td>
                    <span style="font-weight:600; color:#64748b;">{{ number_format($product->price, 2) }} $</span>
                    @if($usdRate > 0 && $product->price > 0)
                    <div style="font-size:11px; color:#94a3b8; margin-top:2px;">≈ {{ number_format($product->price * $usdRate, 0) }} ل.س</div>
                    @endif
                </td>
                <td>
                    @if($product->sell_price > 0)
                        <span style="font-weight:700; color:#16a34a;">{{ number_format($product->sell_price, 2) }} $</span>
                        @if($usdRate > 0)
                        <div style="font-size:11px; color:#94a3b8; margin-top:2px;">≈ {{ number_format($product->sell_price * $usdRate, 0) }} ل.س</div>
                        @endif
                    @else
                        <span style="color:#f59e0b; font-size:12px; font-weight:600;">غير محدد</span>
                    @endif
                </td>
                @php
                    $margin = null;
                    if ($product->price > 0 && $product->sell_price > 0) {
                        $margin = round(($product->sell_price - $product->price) / $product->price * 100, 1);
                    }
                @endphp
                <td style="text-align:center;">
                    @if($margin !== null)
                        <span style="font-weight:700; padding:3px 10px; border-radius:20px; font-size:13px;
                                     background:{{ $margin >= 0 ? '#dcfce7' : '#fef2f2' }};
                                     color:{{ $margin >= 0 ? '#15803d' : '#dc2626' }};">
                            {{ $margin >= 0 ? '+' : '' }}{{ $margin }}%
                        </span>
                    @else
                        <span style="color:#cbd5e1;">—</span>
                    @endif
                </td>
                <td style="text-align:center;">
                    <div style="display:flex; gap:8px; justify-content:center;">
                        <a href="{{ route('products.edit', $product) }}"
                           style="background:#eff6ff; color:#3b82f6; padding:6px 14px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none;">✏️ تعديل</a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST"
                              onsubmit="return confirm('حذف {{ $product->name }}؟ لا يمكن التراجع.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    style="background:#fef2f2; color:#ef4444; padding:6px 14px; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer;">🗑 حذف</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding:60px; color:#94a3b8;">
                    <div style="font-size:48px; margin-bottom:12px;">📭</div>
                    <div style="font-size:16px; font-weight:600;">لا توجد منتجات</div>
                    <div style="font-size:13px; margin-top:4px;">
                        <a href="{{ route('products.create') }}" style="color:#3b82f6;">أضف أول منتج الآن</a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($products->hasPages())
    <div style="padding:16px 20px; border-top:1px solid #f1f5f9; display:flex; justify-content:center;">
        {{ $products->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
