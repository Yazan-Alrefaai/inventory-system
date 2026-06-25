@extends('layouts.app')

@section('title', 'فاتورة جديدة')

@section('header')
<div style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">🧾 فاتورة جديدة</h1>
        <p style="color:#64748b; margin:4px 0 0; font-size:14px;">أضف المنتجات ثم احفظ الفاتورة</p>
    </div>
    <a href="{{ route('sales.index') }}" class="btn-primary" style="background:linear-gradient(135deg,#64748b,#475569);">
        ← قائمة الفواتير
    </a>
</div>
@endsection

@section('content')

<div style="display:grid; grid-template-columns:1fr 360px; gap:24px; align-items:start;">

    {{-- Left: Product search + cart --}}
    <div>
        {{-- Search bar --}}
        <div class="card" style="padding:20px; margin-bottom:20px;">
            <div style="font-weight:700; font-size:15px; margin-bottom:14px; color:#0f172a;">🔍 إضافة منتج للفاتورة</div>
            <div style="display:flex; gap:10px; margin-bottom:12px;">
                <input type="text" id="productSearch" class="input-field" placeholder="ابحث باسم المنتج..." style="flex:1;" oninput="filterProducts(this.value)">
                <input type="number" id="qtyInput" class="input-field" value="1" min="0.001" step="0.001" inputmode="decimal" style="width:90px; text-align:center;">
            </div>
            <div id="productResults" style="max-height:280px; overflow-y:auto; border:1.5px solid #e2e8f0; border-radius:10px; display:none;"></div>
        </div>

        {{-- Cart table --}}
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:700; font-size:15px; color:#0f172a;">🛒 المنتجات في الفاتورة</span>
                <span id="cartCount" style="background:#3b82f6; color:#fff; border-radius:20px; padding:2px 12px; font-size:13px; font-weight:700;">0 منتج</span>
            </div>
            <div id="cartEmpty" style="padding:40px; text-align:center; color:#94a3b8; font-size:14px;">
                لم تضف أي منتج بعد — ابحث عن منتج أعلاه وأضفه
            </div>
            <table id="cartTable" style="display:none;">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th style="text-align:center; width:90px;">الكمية</th>
                        <th style="text-align:center; width:130px;">السعر</th>
                        <th style="text-align:center; width:120px;">الإجمالي</th>
                        <th style="width:50px;"></th>
                    </tr>
                </thead>
                <tbody id="cartBody"></tbody>
                <tfoot>
                    <tr style="background:#f8fafc;">
                        <td colspan="3" style="padding:14px 20px; font-weight:700; font-size:15px; color:#0f172a;">الإجمالي الكلي</td>
                        <td style="padding:14px 20px; font-weight:800; font-size:18px; color:#2563eb; text-align:center;" id="grandTotal">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Right: Invoice details + save --}}
    <div>
        <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
            @csrf

            <div class="card" style="padding:22px; margin-bottom:16px;">
                <div style="font-weight:700; font-size:15px; margin-bottom:16px; color:#0f172a;">💰 تفاصيل الفاتورة</div>

                <div style="margin-bottom:14px;">
                    <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">اسم الزبون (اختياري)</label>
                    <input type="text" name="customer_name" class="input-field" placeholder="اسم الزبون..." value="{{ old('customer_name') }}">
                </div>

                <div style="margin-bottom:14px;">
                    <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">العملة</label>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px;">
                        <button type="button" id="btn_syp" onclick="onCurrencyChange('SYP')"
                            style="padding:9px 4px; border:2px solid #f59e0b; border-radius:10px; background:#fef9c3; color:#92400e; font-weight:800; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;">🇸🇾 ليرة</button>
                        <button type="button" id="btn_usd" onclick="onCurrencyChange('USD')"
                            style="padding:9px 4px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;">💵 دولار</button>
                        <button type="button" id="btn_mix" onclick="onCurrencyChange('MIX')"
                            style="padding:9px 4px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;">💵+🇸🇾 مختلط</button>
                    </div>
                    <input type="hidden" name="currency" id="currency" value="SYP">
                </div>

                <div id="rateRow" style="margin-bottom:14px;">
                    <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">سعر الصرف (ل.س لكل $)</label>
                    <input type="number" name="exchange_rate" id="exchange_rate" class="input-field" min="1" step="1"
                        value="{{ old('exchange_rate', $usdRate ?: '') }}" placeholder="مثال: 14000">
                    @php
                        $__rateAt2  = \App\Models\Setting::get('usd_rate_updated_at');
                        $__rateMin2 = $__rateAt2 ? (int) now()->diffInMinutes(\Carbon\Carbon::parse($__rateAt2)) : null;
                    @endphp
                    <div style="font-size:11px; margin-top:4px; color:{{ ($__rateMin2 === null || $__rateMin2 > 240) ? '#dc2626' : '#15803d' }}; font-weight:600;">
                        {{ $__rateMin2 === null ? '⚠️ لم يُحدَّث سعر الصرف بعد' : ($__rateMin2 < 60 ? '✓ آخر تحديث: منذ '.$__rateMin2.' دقيقة' : '⚠️ آخر تحديث: منذ '.intdiv($__rateMin2,60).' ساعة — تحقق من السعر') }}
                    </div>
                </div>

                {{-- Mix payment panel --}}
                <div id="mixRow" style="display:none; margin-bottom:14px; background:#f0f9ff; border:2px solid #7dd3fc; border-radius:12px; padding:12px;">
                    <div style="font-size:12px; font-weight:800; color:#0369a1; margin-bottom:10px;">💵+🇸🇾 الدفع المختلط</div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px;">
                        <div>
                            <div style="font-size:11px; font-weight:700; color:#1e40af; margin-bottom:4px;">دفع بالدولار $</div>
                            <div style="display:flex; align-items:center; gap:4px; background:#eff6ff; border-radius:8px; padding:6px 10px; border:1.5px solid #bfdbfe;">
                                <input type="number" id="mix_usd" value="0" min="0" step="0.01"
                                    oninput="calcMixSummary()"
                                    style="flex:1; border:none; background:transparent; font-size:16px; font-weight:800; color:#2563eb; outline:none; width:0; font-family:Cairo,sans-serif; direction:ltr; text-align:left;">
                                <span style="font-weight:700; color:#2563eb; font-size:13px;">$</span>
                            </div>
                        </div>
                        <div>
                            <div style="font-size:11px; font-weight:700; color:#92400e; margin-bottom:4px;">دفع بالليرة ل.س</div>
                            <div style="display:flex; align-items:center; gap:4px; background:#fef9c3; border-radius:8px; padding:6px 10px; border:1.5px solid #fde68a;">
                                <input type="number" id="mix_syp" value="0" min="0" step="1"
                                    oninput="calcMixSummary()"
                                    style="flex:1; border:none; background:transparent; font-size:16px; font-weight:800; color:#92400e; outline:none; width:0; font-family:Cairo,sans-serif; direction:ltr; text-align:left;">
                                <span style="font-weight:700; color:#92400e; font-size:13px;">ل.س</span>
                            </div>
                        </div>
                    </div>
                    <div style="background:#fff; border-radius:8px; padding:8px 10px; font-size:12px; font-weight:700;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                            <span style="color:#64748b;">الإجمالي:</span>
                            <span id="mix_total_display" style="color:#0f172a;">—</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                            <span style="color:#64748b;">مجموع المدفوع:</span>
                            <span id="mix_paid_display" style="color:#16a34a;">—</span>
                        </div>
                        <div id="mix_diff_row" style="display:none; border-top:1px solid #e2e8f0; padding-top:4px; justify-content:space-between;">
                            <span id="mix_diff_label" style="color:#64748b;">الفرق:</span>
                            <span id="mix_diff_val" style="font-weight:800;">—</span>
                        </div>
                    </div>
                </div>

                <div id="creditRow" style="margin-bottom:14px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:14px; font-weight:600; color:#374151;">
                        <input type="checkbox" name="is_credit" id="isCredit" value="1" onchange="toggleCredit(this.checked)"
                            style="width:18px; height:18px; cursor:pointer;" {{ old('is_credit') ? 'checked' : '' }}>
                        بيع آجل (دَيْن)
                    </label>
                </div>

                <div id="customerNameError" style="display:none; color:#dc2626; font-size:12px; font-weight:700; background:#fef2f2; border:1.5px solid #fecaca; border-radius:8px; padding:8px 12px; margin-bottom:10px;">
                    ⚠️ يجب إدخال اسم الزبون عند البيع بالدين — بدونه لن تعرف من يدين لك!
                </div>

                {{-- Regular credit: amount paid field (hidden in MIX mode) --}}
                <div id="paidRow" style="margin-bottom:14px; display:none;">
                    <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                        المبلغ المدفوع مقدماً
                        <span id="paidHint" style="color:#64748b; font-weight:400; font-size:12px;"></span>
                    </label>
                    <input type="number" name="amount_paid" id="amount_paid" class="input-field" min="0" step="0.01" value="{{ old('amount_paid', 0) }}" placeholder="0">
                </div>

                {{-- MIX + credit: show remaining balance summary --}}
                <div id="mixCreditSummary" style="display:none; margin-bottom:14px; background:#fef2f2; border:1.5px solid #fecaca; border-radius:10px; padding:12px;">
                    <div style="font-size:12px; font-weight:800; color:#dc2626; margin-bottom:8px;">💳 ملخص الدين المختلط</div>
                    <div style="font-size:13px; font-weight:700; display:flex; justify-content:space-between; margin-bottom:4px;">
                        <span style="color:#64748b;">المدفوع:</span>
                        <span id="mixCreditPaid" style="color:#16a34a;">—</span>
                    </div>
                    <div style="font-size:13px; font-weight:700; display:flex; justify-content:space-between;">
                        <span style="color:#64748b;">الباقي (دين):</span>
                        <span id="mixCreditRemain" style="color:#dc2626;">—</span>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">ملاحظة (اختياري)</label>
                    <textarea name="note" class="input-field" rows="2" placeholder="ملاحظات...">{{ old('note') }}</textarea>
                </div>
            </div>

            {{-- Summary card --}}
            <div class="card" style="padding:18px; margin-bottom:16px; background:linear-gradient(135deg,#f0f9ff,#e0f2fe);">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="color:#475569; font-size:14px;">عدد الأصناف</span>
                    <span id="summaryCount" style="font-weight:700;">0</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="color:#475569; font-size:14px;">إجمالي الكمية</span>
                    <span id="summaryQty" style="font-weight:700;">0</span>
                </div>
                <div style="height:1px; background:#bae6fd; margin:10px 0;"></div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:#0f172a; font-size:15px; font-weight:700;">المبلغ الإجمالي</span>
                    <span id="summaryTotal" style="font-size:18px; font-weight:800; color:#2563eb;">0</span>
                </div>
            </div>

            {{-- Hidden items container --}}
            <div id="hiddenItems"></div>

            <button type="submit" id="submitBtn" class="btn-success" style="width:100%; padding:14px; font-size:16px; justify-content:center; border-radius:12px;" disabled>
                💾 حفظ الفاتورة
            </button>
        </form>
    </div>

</div>

@php
$productsJson = $products->map(fn($p) => [
    'id'        => $p->id,
    'name'      => $p->name,
    'cat'       => $p->category->name ?? '',
    'qty'       => $p->qty,
    'unit'      => $p->unit,
    'price'     => (float) $p->defaultSellPrice(),
    'buy_price' => (float) $p->price,
]);
@endphp
<script>
const ALL_PRODUCTS = @json($productsJson);

const USD_RATE = {{ $usdRate ?: 0 }};
let cart = [];

function getCurrentRate() {
    return parseFloat(document.getElementById('exchange_rate').value) || 0;
}

function getSalePrice(productUsdPrice) {
    const currency = document.getElementById('currency').value;
    const rate = getCurrentRate();
    if (currency === 'SYP' && rate > 0) {
        return Math.round(productUsdPrice * rate);
    }
    return productUsdPrice;
}

function filterProducts(q) {
    const box = document.getElementById('productResults');
    if (!q.trim()) { box.style.display = 'none'; return; }
    const ql = q.toLowerCase();
    const results = ALL_PRODUCTS.filter(p => p.name.toLowerCase().includes(ql) || p.cat.toLowerCase().includes(ql));
    if (!results.length) { box.innerHTML = '<div style="padding:14px 16px; color:#94a3b8;">لا توجد نتائج</div>'; box.style.display = 'block'; return; }
    const currency = document.getElementById('currency').value;
    const sym = currency === 'SYP' ? 'ل.س' : '$';
    box.innerHTML = results.map(p => {
        const displayPrice = getSalePrice(p.price);
        const usdHint = (currency === 'SYP' && p.price > 0) ? ` <span style="color:#94a3b8;font-size:11px;">(${fmt(p.price)} $)</span>` : '';
        return `<div onclick="addToCart(${p.id})" style="padding:12px 16px; cursor:pointer; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; transition:background 0.15s;"
            onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background='#fff'">
            <div>
                <div style="font-weight:600; font-size:14px;">${p.name}</div>
                <div style="font-size:12px; color:#64748b;">${p.cat} — متوفر: ${p.qty} ${p.unit}</div>
            </div>
            <div style="font-weight:700; color:#2563eb; font-size:13px;">${fmt(displayPrice)} ${sym}${usdHint}</div>
        </div>`;
    }).join('');
    box.style.display = 'block';
}

function addToCart(productId) {
    const p    = ALL_PRODUCTS.find(x => x.id === productId);
    const qty  = parseFloat(document.getElementById('qtyInput').value);
    const defaultPrice = getSalePrice(p.price);
    const existing = cart.find(x => x.id === productId);

    if (existing) {
        const newQty = existing.qty + qty;
        if (newQty > p.qty) { alert('الكمية المطلوبة (' + newQty + ') أكبر من المتوفر (' + p.qty + ')'); return; }
        existing.qty = newQty;
    } else {
        if (qty > p.qty) { alert('الكمية المطلوبة (' + qty + ') أكبر من المتوفر (' + p.qty + ')'); return; }
        cart.push({ id: p.id, name: p.name, unit: p.unit, qty: qty, price: defaultPrice, priceUsd: p.price, maxQty: p.qty });
    }

    document.getElementById('productSearch').value = '';
    document.getElementById('productResults').style.display = 'none';
    renderCart();
}

function removeFromCart(idx) {
    cart.splice(idx, 1);
    renderCart();
}

function updateQty(idx, val) {
    const q = parseFloat(val);
    if (isNaN(q) || q <= 0) return;
    if (q > cart[idx].maxQty) { alert('الكمية المتوفرة: ' + cart[idx].maxQty); return; }
    cart[idx].qty = q;
    renderCart();
}

function updatePrice(idx, val) {
    const p = parseFloat(val);
    if (isNaN(p) || p < 0) return;
    cart[idx].price = p;
    renderCart();
}

function renderCart() {
    const body    = document.getElementById('cartBody');
    const empty   = document.getElementById('cartEmpty');
    const table   = document.getElementById('cartTable');
    const hidden  = document.getElementById('hiddenItems');
    const submit  = document.getElementById('submitBtn');
    const sym     = document.getElementById('currency').value === 'SYP' ? 'ل.س' : '$';

    if (!cart.length) {
        empty.style.display = ''; table.style.display = 'none';
        submit.disabled = true;
        document.getElementById('cartCount').textContent = '0 منتج';
        document.getElementById('grandTotal').textContent = '0';
        updateSummary(0, 0, 0, sym);
        hidden.innerHTML = '';
        return;
    }

    table.style.display = ''; empty.style.display = 'none'; submit.disabled = false;
    document.getElementById('cartCount').textContent = cart.length + ' منتج';

    const currency = document.getElementById('currency').value;
    const rate = getCurrentRate();
    let total = 0, totalQty = 0;
    body.innerHTML = cart.map((item, idx) => {
        const sub = item.price * item.qty;
        total += sub; totalQty += item.qty;
        const usdEquiv = (currency === 'SYP' && rate > 0 && item.priceUsd > 0)
            ? `<div style="font-size:11px; color:#94a3b8; margin-top:2px;">≈ ${fmt(item.priceUsd)} $</div>`
            : '';
        return `<tr>
            <td>
                <div style="font-weight:600;">${item.name}</div>
                <div style="font-size:12px; color:#64748b;">${item.unit}</div>
            </td>
            <td style="text-align:center;">
                <input type="text" inputmode="numeric" dir="ltr" value="${item.qty}"
                    onblur="updateQty(${idx}, this.value); this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc'"
                    style="width:70px; border:1.5px solid #cbd5e1; border-radius:8px; padding:6px 8px; text-align:center; font-size:15px; font-family:inherit; background:#f8fafc; outline:none;" onfocus="this.style.borderColor='#3b82f6'; this.style.background='#fff'">
            </td>
            <td style="text-align:center;">
                <input type="text" inputmode="numeric" dir="ltr" value="${item.price}"
                    onblur="updatePrice(${idx}, this.value); this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc'"
                    style="width:120px; border:1.5px solid #cbd5e1; border-radius:8px; padding:6px 8px; text-align:center; font-size:15px; font-family:inherit; background:#f8fafc; outline:none;" onfocus="this.style.borderColor='#3b82f6'; this.style.background='#fff'">
                ${usdEquiv}
            </td>
            <td style="text-align:center; font-weight:700; color:#2563eb;">${fmt(sub)} ${sym}</td>
            <td style="text-align:center;">
                <button type="button" onclick="removeFromCart(${idx})"
                    style="background:#fef2f2; border:none; color:#ef4444; width:32px; height:32px; border-radius:8px; cursor:pointer; font-size:18px; line-height:1;">×</button>
            </td>
        </tr>`;
    }).join('');

    document.getElementById('grandTotal').textContent = fmt(total) + ' ' + sym;
    updateSummary(cart.length, totalQty, total, sym);

    hidden.innerHTML = cart.map((item, idx) => `
        <input type="hidden" name="items[${idx}][product_id]" value="${item.id}">
        <input type="hidden" name="items[${idx}][qty]"        value="${item.qty}">
        <input type="hidden" name="items[${idx}][price]"      value="${item.price}">
    `).join('');
}

function updateSummary(count, qty, total, sym) {
    document.getElementById('summaryCount').textContent = count;
    document.getElementById('summaryQty').textContent   = qty;
    document.getElementById('summaryTotal').textContent = fmt(total) + ' ' + sym;
    updatePaidHint();
}

function fmt(n) {
    var v = Number(n);
    return v % 1 === 0 ? String(Math.round(v)) : v.toFixed(2);
}

function onCurrencyChange(val) {
    // Reset button styles
    const btnSyp = document.getElementById('btn_syp');
    const btnUsd = document.getElementById('btn_usd');
    const btnMix = document.getElementById('btn_mix');
    const inactiveStyle = 'padding:9px 4px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;';
    btnSyp.style.cssText = inactiveStyle;
    btnUsd.style.cssText = inactiveStyle;
    btnMix.style.cssText = inactiveStyle;

    const rate = getCurrentRate();

    if (val === 'SYP') {
        btnSyp.style.cssText = 'padding:9px 4px; border:2px solid #f59e0b; border-radius:10px; background:#fef9c3; color:#92400e; font-weight:800; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;';
        document.getElementById('currency').value = 'SYP';
        document.getElementById('rateRow').style.display = '';
        document.getElementById('mixRow').style.display = 'none';
        document.getElementById('mixCreditSummary').style.display = 'none';
        if (document.getElementById('isCredit').checked) document.getElementById('paidRow').style.display = '';
        cart.forEach(item => {
            if (rate > 0 && item.priceUsd > 0) item.price = Math.round(item.priceUsd * rate);
        });
    } else if (val === 'USD') {
        btnUsd.style.cssText = 'padding:9px 4px; border:2px solid #3b82f6; border-radius:10px; background:#eff6ff; color:#3b82f6; font-weight:800; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;';
        document.getElementById('currency').value = 'USD';
        document.getElementById('rateRow').style.display = 'none';
        document.getElementById('mixRow').style.display = 'none';
        document.getElementById('mixCreditSummary').style.display = 'none';
        if (document.getElementById('isCredit').checked) document.getElementById('paidRow').style.display = '';
        cart.forEach(item => {
            if (item.priceUsd > 0) item.price = item.priceUsd;
        });
    } else { // MIX
        btnMix.style.cssText = 'padding:9px 4px; border:2px solid #0ea5e9; border-radius:10px; background:#f0f9ff; color:#0369a1; font-weight:800; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;';
        document.getElementById('currency').value = 'SYP'; // submit as SYP
        document.getElementById('rateRow').style.display = '';
        document.getElementById('mixRow').style.display = 'block';
        // Switch credit UI: hide regular paid row, show mix credit summary if credit checked
        document.getElementById('paidRow').style.display = 'none';
        document.getElementById('mixCreditSummary').style.display =
            document.getElementById('isCredit').checked ? 'block' : 'none';
        cart.forEach(item => {
            if (rate > 0 && item.priceUsd > 0) item.price = Math.round(item.priceUsd * rate);
        });
        calcMixSummary();
    }
    renderCart();
}

function calcMixSummary() {
    const rate      = getCurrentRate();
    const paidUsd   = Math.max(0, parseFloat(document.getElementById('mix_usd').value) || 0);
    const paidSyp   = Math.max(0, parseFloat(document.getElementById('mix_syp').value) || 0);
    const totalSyp  = cart.reduce((s, item) => s + item.price * item.qty, 0);
    const paidTotal = Math.round(paidUsd * rate) + paidSyp;
    const diff      = paidTotal - totalSyp;

    document.getElementById('mix_total_display').textContent = totalSyp.toLocaleString('en-US') + ' ل.س';
    const parts = [];
    if (paidUsd > 0) parts.push(paidUsd.toLocaleString('en-US') + ' $');
    if (paidSyp > 0) parts.push(paidSyp.toLocaleString('en-US') + ' ل.س');
    document.getElementById('mix_paid_display').textContent = parts.length
        ? parts.join(' + ') + ' = ' + paidTotal.toLocaleString('en-US') + ' ل.س'
        : '0 ل.س';

    const diffRow = document.getElementById('mix_diff_row');
    if (paidTotal > 0) {
        diffRow.style.display = 'flex';
        if (diff > 0) {
            document.getElementById('mix_diff_label').textContent = 'زيادة (فكة):';
            document.getElementById('mix_diff_val').textContent   = diff.toLocaleString('en-US') + ' ل.س';
            document.getElementById('mix_diff_val').style.color   = '#16a34a';
        } else if (diff < 0) {
            document.getElementById('mix_diff_label').textContent = 'ناقص:';
            document.getElementById('mix_diff_val').textContent   = Math.abs(diff).toLocaleString('en-US') + ' ل.س';
            document.getElementById('mix_diff_val').style.color   = '#dc2626';
        } else {
            document.getElementById('mix_diff_label').textContent = '✅ مطابق تماماً';
            document.getElementById('mix_diff_val').textContent   = '';
        }
    } else {
        diffRow.style.display = 'none';
    }

    // Update MIX+credit summary box if credit is checked
    if (document.getElementById('isCredit').checked) {
        const remain = Math.max(0, totalSyp - paidTotal);
        document.getElementById('mixCreditPaid').textContent   = paidTotal.toLocaleString('en-US') + ' ل.س';
        document.getElementById('mixCreditRemain').textContent = remain.toLocaleString('en-US') + ' ل.س' + (remain === 0 ? ' ✅' : '');
        document.getElementById('mixCreditSummary').style.display = 'block';
    }
}

function toggleCredit(checked) {
    const isMix = document.getElementById('mixRow').style.display !== 'none';
    if (isMix) {
        // MIX mode: show/hide the mix credit summary instead of regular paid row
        document.getElementById('paidRow').style.display = 'none';
        document.getElementById('mixCreditSummary').style.display = checked ? 'block' : 'none';
        if (checked) calcMixSummary();
    } else {
        document.getElementById('paidRow').style.display = checked ? '' : 'none';
        document.getElementById('mixCreditSummary').style.display = 'none';
    }
    document.getElementById('customerNameError').style.display = 'none';
    updatePaidHint();
}

function updatePaidHint() {
    const isCredit = document.getElementById('isCredit').checked;
    if (!isCredit) return;
    const sym = document.getElementById('currency').value === 'SYP' ? 'ل.س' : '$';
    // Read total from summary
    const totalText = document.getElementById('summaryTotal').textContent;
    document.getElementById('paidHint').textContent = '— الإجمالي: ' + totalText;
}

// Close dropdown on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('#productSearch') && !e.target.closest('#productResults')) {
        document.getElementById('productResults').style.display = 'none';
    }
});

// Form submit guard
document.getElementById('saleForm').addEventListener('submit', function(e) {
    const isCredit = document.getElementById('isCredit').checked;
    const custName = document.querySelector('input[name=customer_name]').value.trim();
    const errBox   = document.getElementById('customerNameError');
    const currency = document.getElementById('currency').value;
    const isMix    = document.getElementById('btn_mix').style.background.includes('f0f9ff') ||
                     document.getElementById('mixRow').style.display !== 'none';

    if (isCredit && !custName) {
        e.preventDefault();
        errBox.style.display = 'block';
        document.querySelector('input[name=customer_name]').focus();
        document.querySelector('input[name=customer_name]').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    errBox.style.display = 'none';

    // MIX mode: set amount_paid in SYP and append note
    if (isMix) {
        const rate    = getCurrentRate();
        const paidUsd = Math.max(0, parseFloat(document.getElementById('mix_usd').value) || 0);
        const paidSyp = Math.max(0, parseFloat(document.getElementById('mix_syp').value) || 0);
        const totalSyp = cart.reduce((s, item) => s + item.price * item.qty, 0);
        // Total paid in SYP equivalent
        const paidTotalSyp = Math.round(paidUsd * rate) + paidSyp;

        // Set amount_paid hidden input (SaleController reads this for credit sales)
        let apEl = document.getElementById('mix_amount_paid_hidden');
        if (!apEl) {
            apEl = document.createElement('input');
            apEl.type = 'hidden'; apEl.name = 'amount_paid'; apEl.id = 'mix_amount_paid_hidden';
            document.getElementById('saleForm').appendChild(apEl);
        }
        apEl.value = Math.min(paidTotalSyp, totalSyp);

        // Store mix components separately so the controller can create per-currency SalePayment records
        ['mix_usd_hidden','mix_syp_hidden','mix_rate_hidden'].forEach(function(id) {
            if (!document.getElementById(id)) {
                const el = document.createElement('input');
                el.type = 'hidden'; el.id = id;
                el.name = id === 'mix_usd_hidden' ? 'mix_usd_paid'
                        : id === 'mix_syp_hidden' ? 'mix_syp_paid'
                        : 'mix_exchange_rate';
                document.getElementById('saleForm').appendChild(el);
            }
        });
        document.getElementById('mix_usd_hidden').value  = paidUsd;
        document.getElementById('mix_syp_hidden').value  = paidSyp;
        document.getElementById('mix_rate_hidden').value = rate;

        // If fully paid, uncheck credit
        if (paidTotalSyp >= totalSyp) {
            document.getElementById('isCredit').checked = false;
            document.querySelector('input[name=is_credit]') && (document.querySelector('input[name=is_credit]').value = '0');
        }

        // Append note with payment breakdown
        if (paidUsd > 0 || paidSyp > 0) {
            const noteEl = document.querySelector('textarea[name=note]');
            const parts  = [];
            if (paidUsd > 0) parts.push(paidUsd.toLocaleString('en-US') + ' $');
            if (paidSyp > 0) parts.push(paidSyp.toLocaleString('en-US') + ' ل.س');
            const existing = noteEl.value.trim();
            noteEl.value = ('دفع مختلط: ' + parts.join(' + ')) + (existing ? ' — ' + existing : '');
        }
    }

    // Warn if any item has price 0
    const hasZeroPrice = cart.some(item => item.price === 0);
    if (hasZeroPrice) {
        if (!confirm('⚠️ بعض المنتجات سعرها صفر — هل أنت متأكد أنك تريد البيع مجاناً؟')) {
            e.preventDefault();
        }
    }
});

// Init — default to SYP
onCurrencyChange('SYP');
</script>

@endsection
