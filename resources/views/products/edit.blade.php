@extends('layouts.app')
@section('title', 'تعديل منتج')

@section('header')
<div style="display:flex; align-items:center; gap:16px;">
    <a href="{{ route('products.index') }}" style="width:38px; height:38px; background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:18px;">←</a>
    <div>
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">تعديل: {{ $product->name }}</h1>
        <p style="color:#64748b; font-size:14px; margin:4px 0 0;">تحديث بيانات المنتج</p>
    </div>
</div>
@endsection

@section('content')
<div style="max-width:680px;">
    <div class="card" style="padding:32px;">

        @if($errors->any())
        <div style="background:#fef2f2; border:1.5px solid #fecaca; border-radius:10px; padding:14px 18px; margin-bottom:20px;">
            <div style="font-weight:700; color:#dc2626; margin-bottom:8px;">⚠️ يرجى تصحيح الأخطاء التالية:</div>
            <ul style="margin:0; padding-right:20px; color:#dc2626; font-size:13px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('products.update', $product) }}" method="POST">
            @csrf @method('PUT')

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; color:#374151; font-size:14px; margin-bottom:8px;">اسم المنتج *</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}"
                       class="input-field" style="{{ $errors->has('name') ? 'border-color:#ef4444;' : '' }}">
                @error('name')<div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; color:#374151; font-size:14px; margin-bottom:8px;">الصنف *</label>
                <select name="category_id" class="input-field" style="{{ $errors->has('category_id') ? 'border-color:#ef4444;' : '' }}">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id',$product->category_id)==$cat->id ? 'selected':'' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; color:#374151; font-size:14px; margin-bottom:8px;">الوحدة *</label>
                <select name="unit" class="input-field">
                    @foreach(['قطعة','كيلو','متر','صندوق','كرتون','علبة','لفة'] as $u)
                        <option value="{{ $u }}" {{ old('unit',$product->unit)===$u ? 'selected':'' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-weight:600; color:#374151; font-size:14px; margin-bottom:8px;">🛒 سعر الشراء <span style="color:#94a3b8; font-weight:400;">(بالدولار $)</span></label>
                    <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" step="0.01"
                           class="input-field" style="{{ $errors->has('price') ? 'border-color:#ef4444;' : '' }}">
                    <p style="color:#94a3b8; font-size:11px; margin-top:4px;">السعر الذي اشتريت به بالدولار</p>
                    @error('price')<div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block; font-weight:600; color:#374151; font-size:14px; margin-bottom:8px;">🏷️ سعر البيع <span style="color:#94a3b8; font-weight:400;">(بالدولار $)</span></label>
                    <input type="number" name="sell_price" value="{{ old('sell_price', $product->sell_price) }}" min="0" step="0.01"
                           class="input-field" style="{{ $errors->has('sell_price') ? 'border-color:#ef4444;' : '' }}">
                    <p style="color:#94a3b8; font-size:11px; margin-top:4px;">السعر الافتراضي — يمكن تغييره عند البيع</p>
                    @error('sell_price')<div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; color:#374151; font-size:14px; margin-bottom:8px;">📦 الكمية الحالية في المخزن</label>
                <input type="number" name="qty" value="{{ old('qty', $product->qty) }}" min="0" step="0.001"
                       inputmode="decimal" placeholder="مثال: 33.400"
                       class="input-field" style="{{ $errors->has('qty') ? 'border-color:#ef4444;' : '' }}">
                <p style="color:#94a3b8; font-size:11px; margin-top:4px;">يمكنك تعديل الكمية مباشرة — للكسور استخدم النقطة مثال: 33.400</p>
                @error('qty')<div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; color:#374151; font-size:14px; margin-bottom:8px;">حد التنبيه (أدنى كمية)</label>
                <input type="number" name="min_qty" value="{{ old('min_qty', $product->min_qty) }}" min="0" step="0.001"
                       inputmode="decimal" class="input-field" style="{{ $errors->has('min_qty') ? 'border-color:#ef4444;' : '' }}">
                <p style="color:#94a3b8; font-size:12px; margin-top:4px;">سيظهر تنبيه عند الوصول لهذا الرقم</p>
                @error('min_qty')<div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:28px;">
                <label style="display:block; font-weight:600; color:#374151; font-size:14px; margin-bottom:8px;">ملاحظات</label>
                <textarea name="notes" rows="2" class="input-field">{{ old('notes', $product->notes) }}</textarea>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn-primary" style="padding:12px 32px; font-size:15px;">💾 حفظ التعديلات</button>
                <a href="{{ route('products.index') }}"
                   style="padding:12px 24px; border:1.5px solid #e2e8f0; border-radius:10px; color:#64748b; font-size:14px; font-weight:500; text-decoration:none; background:#fff;">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
