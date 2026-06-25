<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة {{ $sale->invoiceNumber() }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f1f5f9; padding: 20px; }
        .invoice-wrap { max-width: 750px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 30px rgba(0,0,0,0.1); }
        .inv-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; padding: 28px 36px; display: flex; justify-content: space-between; align-items: flex-start; position: relative; overflow: hidden; }
        .inv-header::before { content: ''; position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #3b82f6, #22c55e); opacity: 0.15; }
        .inv-header::after  { content: ''; position: absolute; bottom: -40px; left: 60px; width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #f43f5e, #f97316, #eab308); opacity: 0.1; }
        .inv-title { font-size: 22px; font-weight: 800; }
        .inv-sub { color: #94a3b8; font-size: 12px; margin-top: 4px; }
        .inv-num { text-align: left; }
        .inv-num .num { font-size: 22px; font-weight: 800; color: #60a5fa; }
        .inv-num .date { color: #94a3b8; font-size: 13px; margin-top: 4px; }
        .inv-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 0; border-bottom: 1px solid #f1f5f9; }
        .inv-meta-box { padding: 20px 36px; }
        .inv-meta-box:first-child { border-left: 1px solid #f1f5f9; }
        .meta-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        .meta-val { font-size: 15px; font-weight: 600; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f8fafc; padding: 12px 36px; text-align: right; font-size: 12px; font-weight: 700; color: #64748b; border-bottom: 1px solid #f1f5f9; }
        tbody td { padding: 14px 36px; font-size: 14px; color: #374151; border-bottom: 1px solid #f8fafc; }
        tbody tr:last-child td { border-bottom: none; }
        .totals { background: #f8fafc; border-top: 2px solid #e2e8f0; }
        .total-row { display: flex; justify-content: space-between; padding: 10px 36px; font-size: 14px; color: #475569; }
        .total-row.grand { background: linear-gradient(135deg,#3b82f6,#2563eb); color: #fff; padding: 16px 36px; font-size: 17px; font-weight: 800; }
        .total-row.debt { background: #fef3c7; color: #92400e; font-weight: 700; }
        .inv-footer { padding: 20px 36px; text-align: center; color: #94a3b8; font-size: 12px; border-top: 1px solid #f1f5f9; }
        .badge-credit { background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .badge-paid { background: #dcfce7; color: #15803d; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .pay-panel { max-width: 750px; margin: 20px auto 0; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 30px rgba(0,0,0,0.1); border-top: 4px solid #f59e0b; }
        .input-field { width: 100%; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 14px; outline: none; font-family: inherit; transition: border 0.2s; background: #f8fafc; }
        .input-field:focus { border-color: #f59e0b; background: #fff; box-shadow: 0 0 0 3px rgba(245,158,11,0.1); }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 12px; padding: 14px 18px; margin-bottom: 16px; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 12px; padding: 14px 18px; margin-bottom: 16px; }

        @media print {
            body { background: #fff; padding: 0; }
            .invoice-wrap { box-shadow: none; border-radius: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

{{-- Top action bar --}}
<div class="no-print" style="max-width:750px; margin:0 auto 16px; display:flex; justify-content:space-between; align-items:center;">
    <a href="{{ route('sales.index') }}" style="color:#3b82f6; font-size:14px; font-weight:600; text-decoration:none;">← قائمة الفواتير</a>
    <div style="display:flex; gap:10px;">
        <button onclick="toggleEdit()" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed); color:#fff; border:none; padding:9px 18px; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">✏️ تعديل</button>
        <button onclick="window.print()" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; padding:9px 18px; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">🖨️ طباعة</button>
        <form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('تأكيد حذف الفاتورة وإعادة المخزون؟')">
            @csrf @method('DELETE')
            <button type="submit" style="background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; border:none; padding:9px 18px; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">🗑️ حذف</button>
        </form>
    </div>
</div>

{{-- Flash messages --}}
<div class="no-print" style="max-width:750px; margin:0 auto;">
    @if(session('success'))
        <div class="alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">⚠️ {{ session('error') }}</div>
    @endif
</div>

{{-- Invoice --}}
<div class="invoice-wrap">

    {{-- Header --}}
    <div class="inv-header">
        <div style="display:flex; align-items:center; gap:14px; position:relative; z-index:1;">
            {{-- Logo SVG --}}
            <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" width="52" height="52" style="flex-shrink:0;">
                <defs>
                    <linearGradient id="hg" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#8b5cf6"/>
                        <stop offset="100%" stop-color="#6366f1"/>
                    </linearGradient>
                    <linearGradient id="sg" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%"   stop-color="#f43f5e"/>
                        <stop offset="33%"  stop-color="#f97316"/>
                        <stop offset="66%"  stop-color="#22c55e"/>
                        <stop offset="100%" stop-color="#3b82f6"/>
                    </linearGradient>
                    <clipPath id="hc">
                        <polygon points="25,7 43,22 43,45 7,45 7,22"/>
                    </clipPath>
                </defs>
                <rect x="0" y="0" width="50" height="50" rx="12" fill="url(#hg)"/>
                <ellipse cx="30" cy="33" rx="16" ry="15" fill="url(#sg)" clip-path="url(#hc)" opacity="0.9"/>
                <polygon points="25,7 43,22 43,45 7,45 7,22" fill="none" stroke="#fff" stroke-width="2.5" stroke-linejoin="round"/>
                <rect x="20" y="34" width="10" height="11" rx="5" fill="#fff" opacity="0.9"/>
                <rect x="31" y="26" width="8" height="7" rx="2" fill="#fff" opacity="0.7"/>
            </svg>
            <div>
                <div class="inv-title">لمسات الإبداع</div>
                <div class="inv-sub">خردوات — بديل رخام — بديل خشب — جبسم بورد</div>
                <div style="color:#64748b; font-size:11px; margin-top:3px;">المليحة - الشارع العام - دخلة عصائر جنتي</div>
            </div>
        </div>
        <div class="inv-num" style="position:relative; z-index:1;">
            <div class="num">{{ $sale->invoiceNumber() }}</div>
            <div class="date">{{ $sale->created_at->format('Y/m/d  H:i') }}</div>
        </div>
    </div>

    {{-- Meta --}}
    <div class="inv-meta">
        <div class="inv-meta-box">
            <div class="meta-label">الزبون</div>
            <div class="meta-val">{{ $sale->customer_name ?: 'زبون نقدي' }}</div>
        </div>
        <div class="inv-meta-box">
            <div class="meta-label">حالة الدفع</div>
            <div class="meta-val">
                @if($sale->is_credit)
                    <span class="badge-credit">آجل</span>
                    @if($sale->isFullyPaid()) <span class="badge-paid">مسدد بالكامل</span> @endif
                @else
                    <span class="badge-paid">نقدي مسدد</span>
                @endif
            </div>
        </div>
        @if($sale->note)
        <div class="inv-meta-box" style="grid-column:span 2; border-top:1px solid #f1f5f9;">
            <div class="meta-label">ملاحظة</div>
            <div class="meta-val" style="color:#64748b;">{{ $sale->note }}</div>
        </div>
        @endif
    </div>

    {{-- Items table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>المنتج</th>
                <th style="text-align:center;">الكمية</th>
                <th style="text-align:center;">سعر الوحدة</th>
                <th style="text-align:left;">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $i => $item)
            <tr>
                <td style="color:#94a3b8; font-size:13px;">{{ $i + 1 }}</td>
                <td>
                    <div style="font-weight:600;">{{ $item->product->name ?? '—' }}</div>
                    <div style="font-size:12px; color:#94a3b8;">{{ $item->product->unit ?? '' }}</div>
                </td>
                <td style="text-align:center; font-weight:600;">@qty($item->qty)</td>
                @php $dec = $sale->currency === 'SYP' ? 0 : 2; @endphp
                <td style="text-align:center;">{{ number_format($item->price, $dec) }} {{ $sale->currencySymbol() }}</td>
                <td style="text-align:left; font-weight:700; color:#2563eb;">{{ number_format($item->subtotal(), $dec) }} {{ $sale->currencySymbol() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        @if($sale->currency === 'SYP' && $sale->exchange_rate > 0)
        <div class="total-row">
            <span>سعر الصرف</span>
            <span>{{ number_format($sale->exchange_rate, 0) }} ل.س / $</span>
        </div>
        @endif

        @if($sale->is_credit)
        <div class="total-row">
            <span>المدفوع</span>
            @php $sdec = $sale->currency === 'SYP' ? 0 : 2; @endphp
            <span>{{ number_format($sale->totalPaid(), $sdec) }} {{ $sale->currencySymbol() }}</span>
        </div>
        @if($sale->remaining() > 0)
        <div class="total-row debt">
            <span>المتبقي (دَيْن)</span>
            <span>{{ number_format($sale->remaining(), $sdec) }} {{ $sale->currencySymbol() }}</span>
        </div>
        @endif
        @endif

        <div class="total-row grand">
            <span>الإجمالي الكلي</span>
            <span>{{ number_format($sale->totalAmount(), $sale->currency === 'SYP' ? 0 : 2) }} {{ $sale->currencySymbol() }}</span>
        </div>
    </div>

    <div class="inv-footer">
        شكراً لتعاملكم معنا — <strong>لمسات الإبداع</strong><br>
        <span style="font-size:11px;">المليحة - الشارع العام - دخلة عصائر جنتي &nbsp;|&nbsp; 0934609813 — 0954494500</span>
    </div>
</div>

{{-- Payment history (only for credit sales with payments) --}}
@if($sale->is_credit && $sale->salePayments->isNotEmpty())
@php $phDec = $sale->currency === 'SYP' ? 0 : 2; $phSym = $sale->currencySymbol(); @endphp
<div class="no-print" style="max-width:750px; margin:20px auto 0; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 30px rgba(0,0,0,0.1); border-top:4px solid #10b981;">
    <div style="padding:16px 28px; border-bottom:1px solid #d1fae5; background:#f0fdf4; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:15px; font-weight:800; color:#065f46;">📋 سجل الدفعات</div>
        <div style="font-size:13px; color:#047857;">إجمالي ما دُفع: <strong>{{ number_format($sale->totalPaid(), $phDec) }} {{ $phSym }}</strong></div>
    </div>
    <div style="padding:16px 28px;">
        {{-- Initial down payment --}}
        @php $s = $sale; $initPaid = $s->amount_paid - $s->salePayments->sum(fn($p) => $p->amountInSaleCurrency($s)); @endphp
        @if($initPaid > 0.001)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f0fdf4; border-radius:10px; margin-bottom:8px; border-right:4px solid #10b981;">
            <div>
                <div style="font-weight:700; color:#0f172a; font-size:14px;">دفعة البيع الأولى</div>
                <div style="color:#94a3b8; font-size:12px;">{{ $sale->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div style="font-weight:800; color:#16a34a; font-size:16px;">{{ number_format($initPaid, $phDec) }} {{ $phSym }}</div>
        </div>
        @endif
        {{-- Follow-up payments --}}
        @foreach($sale->salePayments->sortBy('created_at') as $sp)
        @php
            $spSym = $sp->pay_currency === 'SYP' ? 'ل.س' : '$';
            $spDec = $sp->pay_currency === 'SYP' ? 0 : 2;
            $sameCurr = $sp->pay_currency === $sale->currency;
        @endphp
        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#eff6ff; border-radius:10px; margin-bottom:8px; border-right:4px solid #3b82f6;">
            <div>
                <div style="font-weight:700; color:#0f172a; font-size:14px;">{{ $sp->note ?: 'دفعة' }}</div>
                <div style="color:#94a3b8; font-size:12px;">{{ $sp->created_at->format('d/m/Y H:i') }}</div>
                @if(!$sameCurr)
                <div style="font-size:11px; color:#f97316; font-weight:600; margin-top:2px;">
                    دُفع بـ{{ $sp->pay_currency }} — يعادل {{ number_format($sp->amountInSaleCurrency($sale), $phDec) }} {{ $phSym }}
                </div>
                @endif
            </div>
            <div style="text-align:left;">
                <div style="font-weight:800; color:#2563eb; font-size:16px;">{{ number_format($sp->amount, $spDec) }} {{ $spSym }}</div>
                @if(!$sameCurr)
                <div style="font-size:11px; color:#94a3b8;">= {{ number_format($sp->amountInSaleCurrency($sale), $phDec) }} {{ $phSym }}</div>
                @endif
            </div>
        </div>
        @endforeach
        {{-- Remaining --}}
        @if($sale->remaining() > 0)
        <div style="border-top:2px solid #f1f5f9; margin-top:10px; padding-top:10px; display:flex; justify-content:space-between;">
            <span style="font-weight:700; color:#dc2626; font-size:14px;">⏳ المتبقي</span>
            <span style="font-weight:800; color:#dc2626; font-size:16px;">{{ number_format($sale->remaining(), $phDec) }} {{ $phSym }}</span>
        </div>
        @endif
    </div>
</div>
@endif

{{-- Edit panel --}}
<div id="edit-panel" class="no-print" style="display:none; max-width:750px; margin:20px auto 0; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 30px rgba(0,0,0,0.1); border-top:4px solid #8b5cf6;">
    <div style="padding:20px 28px; border-bottom:1px solid #ede9fe; background:#faf5ff;">
        <div style="font-size:16px; font-weight:800; color:#6d28d9;">✏️ تعديل الفاتورة</div>
        <div style="font-size:13px; color:#7c3aed; margin-top:4px;">يمكنك تعديل اسم الزبون والملاحظة وأسعار الأصناف</div>
    </div>
    <form method="POST" action="{{ route('sales.update', $sale) }}" style="padding:24px 28px;">
        @csrf @method('PUT')
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
            <div>
                <label style="font-size:13px; font-weight:700; color:#374151; display:block; margin-bottom:6px;">اسم الزبون</label>
                <input type="text" name="customer_name" value="{{ $sale->customer_name }}" class="input-field" placeholder="زبون نقدي">
            </div>
            <div>
                <label style="font-size:13px; font-weight:700; color:#374151; display:block; margin-bottom:6px;">ملاحظة</label>
                <input type="text" name="note" value="{{ $sale->note }}" class="input-field" placeholder="ملاحظة اختيارية">
            </div>
        </div>

        <div style="font-size:13px; font-weight:700; color:#374151; margin-bottom:10px;">أسعار الأصناف ({{ $sale->currencySymbol() }})</div>
        <div style="border:1.5px solid #ede9fe; border-radius:12px; overflow:hidden; margin-bottom:20px;">
            @foreach($sale->items as $item)
            <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid #f5f3ff; {{ $loop->last ? 'border-bottom:none;' : '' }}">
                <div style="flex:1; font-weight:600; color:#0f172a; font-size:14px;">{{ $item->product->name ?? '—' }}</div>
                <div style="color:#94a3b8; font-size:13px;">× @qty($item->qty)</div>
                <input type="number" name="prices[{{ $item->id }}]" value="{{ $item->price }}"
                       step="{{ $sale->currency === 'SYP' ? '1' : '0.01' }}" min="0"
                       style="width:130px; border:1.5px solid #e2e8f0; border-radius:8px; padding:8px 10px; font-size:14px; font-family:inherit; text-align:center;"
                       onfocus="this.style.borderColor='#8b5cf6'" onblur="this.style.borderColor='#e2e8f0'">
                <span style="color:#94a3b8; font-size:13px; white-space:nowrap;">{{ $sale->currencySymbol() }}</span>
            </div>
            @endforeach
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed); color:#fff; border:none; padding:11px 28px; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;">
                💾 حفظ التعديلات
            </button>
            <button type="button" onclick="toggleEdit()" style="border:1.5px solid #e2e8f0; background:#fff; color:#64748b; padding:11px 20px; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                إلغاء
            </button>
        </div>
    </form>
</div>

{{-- Inline debt payment panel (only if credit and not fully paid) --}}
@if($sale->is_credit && !$sale->isFullyPaid())
@php
    $payDec     = $sale->currency === 'SYP' ? 0 : 2;
    $saleRem    = $sale->remaining();
    $saleSym    = $sale->currencySymbol();
    $saleCurr   = $sale->currency;
    $usdRate    = (int) \App\Models\Setting::get('usd_rate', 14000);
@endphp
<div class="pay-panel no-print" style="font-family:Cairo,sans-serif;">
    <div style="padding:20px 28px; border-bottom:1px solid #fef3c7; background:#fffbeb;">
        <div style="font-size:16px; font-weight:800; color:#92400e;">💳 تسجيل دفعة جديدة</div>
        <div style="font-size:13px; color:#b45309; margin-top:4px;">
            المتبقي: <strong>{{ number_format($saleRem, $payDec) }} {{ $saleSym }}</strong>
        </div>
    </div>
    <form method="POST" action="{{ route('sales.pay', $sale) }}" id="salePayForm" style="padding:24px 28px;">
        @csrf

        {{-- Currency selector --}}
        <div style="margin-bottom:16px;">
            <div style="font-size:13px; font-weight:700; color:#374151; margin-bottom:8px;">عملة الدفع</div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
                <button type="button" id="spBtnSame" onclick="spSetCurr('same')"
                    style="padding:10px 4px; border:2px solid #f59e0b; border-radius:10px; background:#fffbeb; color:#b45309; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;">
                    {{ $saleCurr === 'SYP' ? '🇸🇾 ليرة (أصل الدين)' : '💵 دولار (أصل الدين)' }}
                </button>
                <button type="button" id="spBtnOther" onclick="spSetCurr('other')"
                    style="padding:10px 4px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;">
                    {{ $saleCurr === 'SYP' ? '💵 دولار' : '🇸🇾 ليرة' }}
                </button>
                <button type="button" id="spBtnMix" onclick="spSetCurr('mix')"
                    style="padding:10px 4px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;">
                    💵+🇸🇾 مختلط
                </button>
            </div>
            <input type="hidden" name="pay_currency" id="spCurrHidden" value="{{ $saleCurr }}">
        </div>

        {{-- Exchange rate box --}}
        <div id="spRateBox" style="display:none; margin-bottom:14px; background:#fef9c3; border-radius:10px; padding:12px 14px; border:1.5px solid #fde68a;">
            <div style="font-size:12px; font-weight:700; color:#92400e; margin-bottom:6px;">سعر الصرف (ل.س لكل $)</div>
            <input type="number" name="exchange_rate" id="spRate" value="{{ $usdRate }}" min="1"
                   style="width:100%; border:none; background:transparent; font-size:18px; font-weight:800; color:#92400e; outline:none; font-family:Cairo,sans-serif; direction:ltr; text-align:right;"
                   oninput="spCalc()">
        </div>

        {{-- Single amount --}}
        <div id="spAmtBox" style="margin-bottom:14px;">
            <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                المبلغ — المتبقي: <span id="spRemLabel">{{ number_format($saleRem, $payDec) }} {{ $saleSym }}</span>
            </label>
            <input type="number" name="amount" id="spAmount"
                   min="0.01" step="0.01"
                   placeholder="{{ number_format($saleRem, $payDec, '.', '') }}"
                   class="input-field" style="font-size:20px; font-weight:800; color:#b45309; border-color:#fde68a; background:#fffbeb;"
                   oninput="spCalc()">
            <div id="spEquiv" style="display:none; font-size:12px; color:#64748b; margin-top:4px; font-weight:600;"></div>
        </div>

        {{-- Mix: two fields --}}
        <div id="spMixBox" style="display:none; margin-bottom:14px;">
            <div style="background:#f0f9ff; border:2px solid #7dd3fc; border-radius:10px; padding:12px;">
                <div style="font-size:12px; font-weight:800; color:#0369a1; margin-bottom:10px;">💵+🇸🇾 الدفع المختلط</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
                    <div>
                        <div style="font-size:11px; font-weight:700; color:#1e40af; margin-bottom:4px;">دولار $</div>
                        <div style="display:flex; align-items:center; gap:4px; background:#eff6ff; border-radius:8px; padding:6px 10px; border:1.5px solid #bfdbfe;">
                            <input type="number" id="spMixUsd" value="0" min="0" step="0.01"
                                oninput="spCalcMix()"
                                style="flex:1; border:none; background:transparent; font-size:16px; font-weight:800; color:#2563eb; outline:none; width:0; font-family:Cairo,sans-serif; direction:ltr; text-align:left;">
                            <span style="color:#2563eb; font-weight:700;">$</span>
                        </div>
                    </div>
                    <div>
                        <div style="font-size:11px; font-weight:700; color:#92400e; margin-bottom:4px;">ليرة ل.س</div>
                        <div style="display:flex; align-items:center; gap:4px; background:#fef9c3; border-radius:8px; padding:6px 10px; border:1.5px solid #fde68a;">
                            <input type="number" id="spMixSyp" value="0" min="0" step="1"
                                oninput="spCalcMix()"
                                style="flex:1; border:none; background:transparent; font-size:16px; font-weight:800; color:#92400e; outline:none; width:0; font-family:Cairo,sans-serif; direction:ltr; text-align:left;">
                            <span style="color:#92400e; font-weight:700;">ل.س</span>
                        </div>
                    </div>
                </div>
                <div style="background:#fff; border-radius:8px; padding:8px 10px; font-size:12px; font-weight:700;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                        <span style="color:#64748b;">مجموع:</span>
                        <span id="spMixTotal" style="color:#16a34a;">0 {{ $saleSym }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#64748b;">سيبقى:</span>
                        <span id="spMixRemain" style="color:#dc2626;">{{ number_format($saleRem, $payDec) }} {{ $saleSym }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">ملاحظة (اختياري)</label>
            <input type="text" name="note" class="input-field" placeholder="مثال: دفع نقداً">
        </div>

        <button id="salePayBtn" type="submit" style="width:100%; padding:12px; background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:15px; cursor:pointer; font-family:Cairo,sans-serif;">
            ✅ تسجيل الدفعة
        </button>
    </form>
</div>

<script>
var SP_REM   = {{ $saleRem }};
var SP_CURR  = '{{ $saleCurr }}';
var SP_SYM   = '{{ $saleSym }}';
var SP_DEC   = {{ $payDec }};
var spMode   = SP_CURR;

function spSetCurr(which) {
    spMode = which;
    var inactive = 'padding:10px 4px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;';
    var active   = 'padding:10px 4px; border:2px solid #f59e0b; border-radius:10px; background:#fffbeb; color:#b45309; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;';
    var activeMix= 'padding:10px 4px; border:2px solid #0ea5e9; border-radius:10px; background:#f0f9ff; color:#0369a1; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;';
    document.getElementById('spBtnSame').style.cssText  = which==='same'  ? active   : inactive;
    document.getElementById('spBtnOther').style.cssText = which==='other' ? active   : inactive;
    document.getElementById('spBtnMix').style.cssText   = which==='mix'   ? activeMix: inactive;

    document.getElementById('spAmtBox').style.display  = which==='mix' ? 'none' : 'block';
    document.getElementById('spMixBox').style.display  = which==='mix' ? 'block' : 'none';
    document.getElementById('spRateBox').style.display = (which==='other'||which==='mix') ? 'block' : 'none';

    var otherCurr = SP_CURR==='SYP' ? 'USD' : 'SYP';
    if (which==='same')  document.getElementById('spCurrHidden').value = SP_CURR;
    if (which==='other') document.getElementById('spCurrHidden').value = otherCurr;
    if (which==='mix')   document.getElementById('spCurrHidden').value = SP_CURR;
    spCalc();
}

function spGetRate() { return parseFloat(document.getElementById('spRate').value)||1; }

function spCalc() {
    if (spMode==='mix') { spCalcMix(); return; }
    var amt   = parseFloat(document.getElementById('spAmount').value)||0;
    var equiv = document.getElementById('spEquiv');
    var otherCurr = SP_CURR==='SYP' ? 'USD' : 'SYP';
    if (spMode==='other' && amt>0) {
        var rate  = spGetRate();
        var inSale = SP_CURR==='SYP' ? Math.round(amt*rate) : Math.round(amt/rate*100)/100;
        equiv.style.display='block';
        equiv.textContent = '≈ '+inSale.toLocaleString('en-US')+' '+SP_SYM+' بعملة الدين';
    } else { equiv.style.display='none'; }
}

function spCalcMix() {
    var rate    = spGetRate();
    var paidUsd = Math.max(0, parseFloat(document.getElementById('spMixUsd').value)||0);
    var paidSyp = Math.max(0, parseFloat(document.getElementById('spMixSyp').value)||0);
    var inSale;
    if (SP_CURR==='SYP') {
        inSale = Math.round(paidUsd*rate)+paidSyp;
    } else {
        inSale = paidUsd + Math.round(paidSyp/rate*100)/100;
    }
    var remain = Math.max(0, SP_REM-inSale);
    document.getElementById('spMixTotal').textContent  = inSale.toLocaleString('en-US')+' '+SP_SYM;
    document.getElementById('spMixRemain').textContent = remain.toLocaleString('en-US')+' '+SP_SYM+(remain===0?' ✅':'');
    document.getElementById('spAmount').value = inSale;
}

document.getElementById('salePayForm').addEventListener('submit', function(e) {
    var btn = document.getElementById('salePayBtn');
    if (spMode==='mix') {
        e.preventDefault();
        var paidUsd = parseFloat(document.getElementById('spMixUsd').value)||0;
        var paidSyp = parseFloat(document.getElementById('spMixSyp').value)||0;
        if (paidUsd <= 0 && paidSyp <= 0) { alert('أدخل مبلغاً للدفع'); return; }
        var rate    = spGetRate();
        var noteEl  = this.querySelector('input[name=note]');
        var noteVal = noteEl ? noteEl.value.trim() : '';
        var parts   = [];
        if (paidUsd>0) parts.push(paidUsd+' $');
        if (paidSyp>0) parts.push(paidSyp+' ل.س');
        var mixNote = 'دفع مختلط: '+parts.join(' + ')+(noteVal?' — '+noteVal:'');

        // Submit two separate payments via two sequential AJAX fetches
        // First: USD part (if any), then: SYP part (if any)
        var token = this.querySelector('input[name=_token]').value;
        var action = this.action;

        function postPayment(currency, amount, note, callback) {
            var fd = new FormData();
            fd.append('_token', token);
            fd.append('pay_currency', currency);
            fd.append('amount', amount);
            fd.append('exchange_rate', rate);
            fd.append('note', note);
            fetch(action, { method:'POST', body:fd, redirect:'follow' })
                .then(function(r) { callback(r.ok || r.redirected, r); })
                .catch(function() { callback(false, null); });
        }

        var submits = [];
        if (paidUsd > 0) submits.push({ currency:'USD', amount:paidUsd });
        if (paidSyp > 0) submits.push({ currency:'SYP', amount:paidSyp });

        function runNext(idx) {
            if (idx >= submits.length) { window.location.reload(); return; }
            var s = submits[idx];
            var n = idx === 0 ? mixNote : '(تكملة مختلط)';
            postPayment(s.currency, s.amount, n, function(ok) {
                if (!ok) { alert('حدث خطأ أثناء تسجيل الدفعة'); return; }
                runNext(idx+1);
            });
        }
        btn.disabled = true;
        btn.textContent = '⏳ جاري التسجيل...';
        runNext(0);
    } else {
        btn.disabled = true;
        btn.textContent = '⏳ جاري التسجيل...';
    }
});
</script>
@endif

<script>
function toggleEdit() {
    var p = document.getElementById('edit-panel');
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
    if (p.style.display === 'block') p.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Number input fix (same as layouts/app.blade.php) ─────────────────────────
(function () {
    function toLatinDigits(s) {
        return String(s)
            .replace(/[٠-٩]/g, function(c){ return c.charCodeAt(0) - 0x0660; })
            .replace(/[۰-۹]/g, function(c){ return c.charCodeAt(0) - 0x06F0; });
    }
    function isDecimal(el) {
        var im = (el.getAttribute('inputmode') || '').toLowerCase();
        var t  = (el.type || '').toLowerCase();
        var step = el.getAttribute('step') || '';
        return t === 'number' || im === 'decimal' || (im === 'numeric' && step && step !== '1');
    }
    function upgradeOne(el) {
        if (!el || el.tagName !== 'INPUT' || el.dataset.numFixed) return;
        var t = (el.type || '').toLowerCase();
        if (t === 'number') {
            var step = el.getAttribute('step') || '';
            var isFloat = step && step !== '1';
            el.type = 'text';
            el.setAttribute('inputmode', isFloat ? 'decimal' : 'numeric');
        }
        var im = (el.getAttribute('inputmode') || '').toLowerCase();
        if (im === 'numeric' || im === 'decimal') {
            el.setAttribute('dir', 'ltr');
            el.setAttribute('autocomplete', 'off');
            if (!el.style.textAlign) el.style.textAlign = 'left';
            el.dataset.numFixed = '1';
        }
    }
    document.querySelectorAll('input[type="number"]').forEach(upgradeOne);
    document.querySelectorAll('input[inputmode="numeric"], input[inputmode="decimal"]').forEach(upgradeOne);

    document.addEventListener('input', function(e) {
        var el = e.target;
        if (!el || el.tagName !== 'INPUT') return;
        var v = el.value, n = toLatinDigits(v);
        if (n !== v) { el.value = n; }
    }, true);

    document.addEventListener('keydown', function(e) {
        var el = e.target;
        if (!el || el.tagName !== 'INPUT') return;
        var im = (el.getAttribute('inputmode') || '').toLowerCase();
        if (im !== 'numeric' && im !== 'decimal') return;
        if (e.ctrlKey || e.metaKey || e.altKey) return;
        var nav = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Enter','Home','End','Escape'];
        if (nav.indexOf(e.key) !== -1) return;
        if (/^[0-9]$/.test(e.key)) return;
        if (im === 'decimal' && (e.key === '.' || e.key === ',') && el.value.indexOf('.') === -1) {
            if (e.key === ',') {
                e.preventDefault();
                var sp = el.selectionStart != null ? el.selectionStart : el.value.length;
                var ep = el.selectionEnd   != null ? el.selectionEnd   : sp;
                el.value = el.value.substring(0, sp) + '.' + el.value.substring(ep);
                try { el.setSelectionRange(sp + 1, sp + 1); } catch(_) {}
                el.dispatchEvent(new Event('input', { bubbles: true }));
            }
            return;
        }
        e.preventDefault();
    }, true);

    document.addEventListener('submit', function(e) {
        e.target.querySelectorAll('input').forEach(function(el) {
            if (el.value) el.value = toLatinDigits(el.value).replace(/,/g, '.');
        });
    }, true);
}());
</script>
</body>
</html>
