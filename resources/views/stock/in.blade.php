@extends('layouts.app')
@section('title', 'إدخال بضاعة')

@section('header')
<div style="display:flex; align-items:center; gap:16px;">
    <a href="{{ route('dashboard') }}" style="width:38px; height:38px; background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:18px; color:#374151;">←</a>
    <div>
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">⬇️ إدخال بضاعة</h1>
        <p style="color:#64748b; font-size:14px; margin:4px 0 0;">تسجيل بضاعة واردة وتحديد سعر الشراء</p>
    </div>
</div>
@endsection

@section('content')
<div style="display:grid; grid-template-columns:1.2fr 0.8fr; gap:24px; max-width:960px;">

    <div class="card" style="padding:32px;">
        <form action="{{ route('stock.in.store') }}" method="POST" id="stockInForm">
            @csrf

            @if($errors->any())
            <div style="background:#fef2f2; border:1.5px solid #fecaca; border-radius:10px; padding:14px 18px; margin-bottom:20px;">
                <div style="font-weight:700; color:#dc2626; margin-bottom:6px;">⚠️ يرجى تصحيح الأخطاء:</div>
                <ul style="margin:0; padding-right:20px; color:#dc2626; font-size:13px;">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
            @endif

            {{-- Hidden product_id --}}
            <input type="hidden" name="product_id" id="product_id_hidden">

            {{-- Step 1: Search --}}
            <div style="margin-bottom:22px;">
                <label style="display:block; font-weight:700; color:#374151; font-size:14px; margin-bottom:8px;">
                    <span style="background:#10b981; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; margin-left:6px;">١</span>
                    ابحث عن المنتج
                </label>
                <div style="position:relative;">
                    <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:18px; pointer-events:none;">🔍</span>
                    <input type="text" id="product_search" autocomplete="off"
                           placeholder="اكتب اسم المنتج... مثال: مسمار، صمولة"
                           class="input-field"
                           style="padding-right:44px; font-size:15px; padding-top:13px; padding-bottom:13px; border-color:#bbf7d0; background:#f0fdf4;"
                           oninput="filterIn(this.value)">
                    <div id="in_results" style="display:none; position:absolute; top:calc(100% + 6px); right:0; left:0; background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.12); z-index:100; max-height:280px; overflow-y:auto;"></div>
                </div>
                {{-- Selected product badge --}}
                <div id="selected_badge" style="display:none; margin-top:10px; padding:10px 14px; background:#f0fdf4; border:1.5px solid #bbf7d0; border-radius:10px; justify-content:space-between; align-items:center;">
                    <div>
                        <span style="font-weight:700; color:#0f172a; font-size:14px;" id="badge_name">—</span>
                        <span style="color:#94a3b8; font-size:12px; margin-right:8px;" id="badge_cat"></span>
                    </div>
                    <button type="button" onclick="clearSelection()"
                            style="background:#fef2f2; color:#ef4444; border:none; border-radius:6px; padding:4px 10px; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif; font-weight:600;">× تغيير</button>
                </div>
            </div>

            {{-- Product Info --}}
            <div id="product_info" style="display:none; background:#f0fdf4; border:1.5px solid #bbf7d0; border-radius:14px; padding:16px 20px; margin-bottom:22px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; text-align:center;">
                    <div style="background:#fff; border-radius:10px; padding:12px;">
                        <div style="color:#94a3b8; font-size:11px; margin-bottom:4px;">المخزون الحالي</div>
                        <div style="font-size:22px; font-weight:800; color:#0f172a;" id="info_qty">—</div>
                        <div style="color:#64748b; font-size:12px;" id="info_unit"></div>
                    </div>
                    <div style="background:#fff; border-radius:10px; padding:12px;">
                        <div style="color:#94a3b8; font-size:11px; margin-bottom:4px;">سعر الشراء المسجل</div>
                        <div style="font-size:22px; font-weight:800; color:#3b82f6;" id="info_price">—</div>
                        <div style="color:#64748b; font-size:12px;">دولار $</div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Qty --}}
            <div style="margin-bottom:22px;">
                <label style="display:block; font-weight:700; color:#374151; font-size:14px; margin-bottom:8px;">
                    <span style="background:#10b981; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; margin-left:6px;">٢</span>
                    الكمية الواردة
                </label>
                <input type="number" name="qty" id="qty_input" value="{{ old('qty') }}" min="0.001" step="0.001"
                       inputmode="decimal" class="input-field" placeholder="مثال: 33.400"
                       oninput="recalculate()" required
                       style="font-size:18px; padding:12px 14px; font-weight:700;">
            </div>

            {{-- Step 3: Price --}}
            <div style="margin-bottom:22px;">
                <label style="display:block; font-weight:700; color:#374151; font-size:14px; margin-bottom:8px;">
                    <span style="background:#10b981; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; margin-left:6px;">٣</span>
                    سعر الشراء للقطعة ($)
                    <span style="font-weight:400; color:#94a3b8; font-size:12px; margin-right:6px;">اختياري — لحساب قيمة الوارد</span>
                </label>
                <div style="position:relative;">
                    <input type="number" name="price" id="price_input" value="{{ old('price') }}" min="0" step="0.01"
                           class="input-field" placeholder="0.00"
                           oninput="recalculate()"
                           style="font-size:20px; padding:14px 50px 14px 14px; font-weight:800; color:#10b981; border-color:#bbf7d0; background:#f0fdf4;">
                    <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:18px; font-weight:800; color:#10b981;">$</span>
                </div>
            </div>

            {{-- Total Preview --}}
            <div id="total_preview" style="display:none; background:linear-gradient(135deg,#10b981,#059669); border-radius:14px; padding:16px 24px; margin-bottom:24px; text-align:center;">
                <div style="color:rgba(255,255,255,0.8); font-size:13px; margin-bottom:4px;">قيمة هذا الوارد</div>
                <div style="color:#fff; font-size:32px; font-weight:800;" id="grand_total">0.00 $</div>
                <div style="color:rgba(255,255,255,0.7); font-size:12px;" id="total_desc">—</div>
            </div>

            {{-- Step 4: Note --}}
            <div style="margin-bottom:28px;">
                <label style="display:block; font-weight:700; color:#374151; font-size:14px; margin-bottom:8px;">
                    <span style="background:#10b981; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; margin-left:6px;">٤</span>
                    ملاحظة (اختياري)
                </label>
                <input type="text" name="note" value="{{ old('note') }}"
                       class="input-field" placeholder="مثال: شراء من المستودع المركزي">
            </div>

            <button type="submit" id="submitBtn" disabled
                    style="width:100%; padding:16px; font-size:17px; font-weight:800; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius:14px; cursor:pointer; opacity:0.5;">
                ✅ تسجيل الوارد
            </button>
            <div id="submit_hint" style="text-align:center; color:#94a3b8; font-size:12px; margin-top:8px;">اختر منتجاً أولاً لتفعيل الزر</div>
        </form>
    </div>

    {{-- Sidebar --}}
    <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="card" style="padding:20px;">
            <h4 style="font-weight:700; color:#0f172a; font-size:14px; margin:0 0 14px;">📊 إحصائيات اليوم</h4>
            @php
                $todayIn    = \App\Models\StockMovement::where('type','in')->whereDate('created_at',today())->sum('qty');
                $todayValue = \App\Models\StockMovement::where('type','in')->whereNotNull('price')->whereDate('created_at',today())->selectRaw('SUM(qty * price) as t')->value('t') ?? 0;
                $todayCount = \App\Models\StockMovement::where('type','in')->whereDate('created_at',today())->count();
            @endphp
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
                <div style="background:#f0fdf4; border-radius:10px; padding:12px; text-align:center;">
                    <div style="color:#94a3b8; font-size:11px;">قطع واردة</div>
                    <div style="font-size:24px; font-weight:800; color:#10b981;">{{ $todayIn }}</div>
                </div>
                <div style="background:#f0fdf4; border-radius:10px; padding:12px; text-align:center;">
                    <div style="color:#94a3b8; font-size:11px;">عدد العمليات</div>
                    <div style="font-size:24px; font-weight:800; color:#10b981;">{{ $todayCount }}</div>
                </div>
            </div>
            <div style="background:linear-gradient(135deg,#10b981,#059669); border-radius:10px; padding:14px; text-align:center;">
                <div style="color:rgba(255,255,255,0.8); font-size:12px;">قيمة الوارد اليوم</div>
                <div style="color:#fff; font-size:26px; font-weight:800;">{{ number_format($todayValue, 2) }} $</div>
            </div>
        </div>

        <div class="card" style="padding:20px;">
            <h4 style="font-weight:700; color:#0f172a; font-size:14px; margin:0 0 14px;">🕐 آخر الواردات</h4>
            @php $lastIn = \App\Models\StockMovement::where('type','in')->with('product')->latest()->take(6)->get(); @endphp
            @forelse($lastIn as $m)
            <div style="padding:10px 0; border-bottom:1px solid #f1f5f9;">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div>
                        <div style="font-weight:600; color:#0f172a; font-size:13px;">{{ $m->product->name ?? '(محذوف)' }}</div>
                        <div style="color:#94a3b8; font-size:11px;">{{ $m->created_at->diffForHumans() }}</div>
                    </div>
                    <div style="text-align:left;">
                        <div style="font-weight:700; color:#10b981; font-size:13px;">+@qty($m->qty) {{ $m->product->unit ?? 'قطعة' }}</div>
                        @if($m->price)
                            <div style="font-weight:800; color:#0f172a; font-size:12px;">{{ number_format($m->price * $m->qty, 2) }} $</div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center; color:#94a3b8; padding:20px;">لا توجد سجلات</div>
            @endforelse
        </div>
    </div>
</div>

@php
$inProductsData = $products->map(fn($p) => [
    'id'    => $p->id,
    'name'  => $p->name,
    'cat'   => $p->category->name ?? '',
    'qty'   => $p->qty,
    'unit'  => $p->unit,
    'price' => (float) $p->price,
]);
@endphp
<script>
const IN_PRODUCTS = @json($inProductsData);
let selectedProduct = null;
let unit = '';

function filterIn(q) {
    const box = document.getElementById('in_results');
    if (!q.trim()) { box.style.display = 'none'; return; }
    const ql = q.toLowerCase();
    const results = IN_PRODUCTS.filter(p => p.name.toLowerCase().includes(ql) || p.cat.toLowerCase().includes(ql));
    if (!results.length) {
        box.innerHTML = '<div style="padding:16px; text-align:center; color:#94a3b8;">لا توجد نتائج</div>';
    } else {
        box.innerHTML = results.map(p => `
            <div onclick="selectProduct(${p.id})"
                 style="padding:13px 16px; cursor:pointer; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;"
                 onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='#fff'">
                <div>
                    <div style="font-weight:700; color:#0f172a; font-size:14px;">${p.name}</div>
                    <div style="color:#94a3b8; font-size:12px;">${p.cat} — مخزون: <strong style="color:#0f172a;">${p.qty}</strong> ${p.unit}</div>
                </div>
                <div style="font-weight:700; color:#3b82f6; font-size:13px;">${p.price.toFixed(2)} $</div>
            </div>`).join('');
    }
    box.style.display = 'block';
}

function selectProduct(id) {
    selectedProduct = IN_PRODUCTS.find(p => p.id === id);
    if (!selectedProduct) return;
    unit = selectedProduct.unit;

    document.getElementById('product_id_hidden').value = selectedProduct.id;
    document.getElementById('product_search').value    = '';
    document.getElementById('in_results').style.display = 'none';

    // Show badge
    document.getElementById('badge_name').textContent = selectedProduct.name;
    document.getElementById('badge_cat').textContent  = selectedProduct.cat;
    document.getElementById('selected_badge').style.display = 'flex';

    // Show info card
    document.getElementById('info_qty').textContent   = selectedProduct.qty;
    document.getElementById('info_unit').textContent  = unit;
    document.getElementById('info_price').textContent = selectedProduct.price.toFixed(2);
    document.getElementById('product_info').style.display = 'block';

    // Pre-fill price if empty
    const priceInput = document.getElementById('price_input');
    if (!priceInput.value) priceInput.value = selectedProduct.price.toFixed(2);

    // Enable submit
    document.getElementById('submitBtn').disabled = false;
    document.getElementById('submitBtn').style.opacity = '1';
    document.getElementById('submit_hint').style.display = 'none';

    recalculate();
    document.getElementById('qty_input').focus();
}

function clearSelection() {
    selectedProduct = null;
    unit = '';
    document.getElementById('product_id_hidden').value = '';
    document.getElementById('selected_badge').style.display = 'none';
    document.getElementById('product_info').style.display  = 'none';
    document.getElementById('total_preview').style.display = 'none';
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').style.opacity = '0.5';
    document.getElementById('submit_hint').style.display = 'block';
    document.getElementById('product_search').value = '';
    document.getElementById('product_search').focus();
}

function recalculate() {
    if (!selectedProduct) return;
    const qty   = parseFloat(document.getElementById('qty_input').value) || 0;
    const price = parseFloat(document.getElementById('price_input').value) || 0;
    const total = qty * price;
    if (qty > 0 && price > 0) {
        document.getElementById('grand_total').textContent = Number(total).toFixed(2) + ' $';
        document.getElementById('total_desc').textContent  = qty + ' ' + unit + ' × ' + price.toFixed(2) + ' $';
        document.getElementById('total_preview').style.display = 'block';
    } else {
        document.getElementById('total_preview').style.display = 'none';
    }
}

// Close dropdown on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('#product_search') && !e.target.closest('#in_results'))
        document.getElementById('in_results').style.display = 'none';
});

// Block form submit if no product selected
document.getElementById('stockInForm').addEventListener('submit', function(e) {
    if (!document.getElementById('product_id_hidden').value) {
        e.preventDefault();
        document.getElementById('product_search').focus();
        document.getElementById('product_search').style.borderColor = '#ef4444';
        setTimeout(() => document.getElementById('product_search').style.borderColor = '#bbf7d0', 2000);
    }
});
</script>
@endsection
