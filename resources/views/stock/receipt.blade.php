<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال بيع</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f1f5f9; padding: 20px; }
        .wrap { max-width: 420px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 30px rgba(0,0,0,0.1); }
        .hdr { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; padding: 22px 24px; position: relative; overflow: hidden; }
        .hdr::before { content:''; position:absolute; top:-20px; right:-20px; width:90px; height:90px; border-radius:50%; background:linear-gradient(135deg,#8b5cf6,#3b82f6,#22c55e); opacity:0.18; }
        .hdr::after  { content:''; position:absolute; bottom:-30px; left:30px; width:70px; height:70px; border-radius:50%; background:linear-gradient(135deg,#f43f5e,#f97316,#eab308); opacity:0.12; }
        .hdr-inner { display:flex; align-items:center; gap:12px; position:relative; z-index:1; }
        .hdr .store { font-size: 18px; font-weight: 800; }
        .hdr .sub { color: #94a3b8; font-size: 11px; margin-top: 3px; }
        .hdr .contact { color: #64748b; font-size: 10px; margin-top: 4px; }
        .divider { border: none; border-top: 2px dashed #e2e8f0; margin: 0; }
        .section { padding: 18px 28px; }
        .row { display: flex; justify-content: space-between; padding: 7px 0; font-size: 14px; color: #374151; border-bottom: 1px solid #f8fafc; }
        .row:last-child { border-bottom: none; }
        .row .label { color: #94a3b8; font-size: 13px; }
        .row .value { font-weight: 700; color: #0f172a; }
        .total-box { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; padding: 16px 28px; display: flex; justify-content: space-between; align-items: center; }
        .total-box .t-label { font-size: 14px; font-weight: 600; }
        .total-box .t-amount { font-size: 22px; font-weight: 900; }
        .debt-box { background: #fef3c7; padding: 10px 28px; display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; color: #92400e; }
        .footer { padding: 16px 28px; text-align: center; color: #94a3b8; font-size: 12px; border-top: 1px solid #f1f5f9; }
        .badge { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .badge-cash { background: #dcfce7; color: #15803d; }
        .badge-credit { background: #fef3c7; color: #92400e; }
        .actions { max-width: 420px; margin: 16px auto; display: flex; gap: 10px; }
        .btn { flex: 1; padding: 11px; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; text-align: center; border: none; text-decoration: none; display: block; font-family: inherit; }
        .btn-print { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
        .btn-new   { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; }
        .btn-back  { background: #f1f5f9; color: #64748b; border: 1.5px solid #e2e8f0; }
        @media print {
            body { background: #fff; padding: 0; }
            .wrap { box-shadow: none; border-radius: 0; max-width: 100%; }
            .actions { display: none; }
        }
    </style>
</head>
<body>

@php $sym = $movement->currencySymbol(); @endphp

<div class="actions no-print">
    <button onclick="window.print()" class="btn btn-print">🖨️ طباعة الإيصال</button>
    <a href="{{ route('stock.out') }}" class="btn btn-new">+ بيع جديد</a>
    <a href="{{ route('stock.history') }}" class="btn btn-back">السجل</a>
</div>

<div class="wrap">
    {{-- Header --}}
    <div class="hdr">
        <div class="hdr-inner">
            <svg viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg" width="46" height="46" style="flex-shrink:0;">
                <defs>
                    <linearGradient id="hg2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#8b5cf6"/>
                        <stop offset="100%" stop-color="#6366f1"/>
                    </linearGradient>
                    <linearGradient id="sg2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%"   stop-color="#f43f5e"/>
                        <stop offset="33%"  stop-color="#f97316"/>
                        <stop offset="66%"  stop-color="#22c55e"/>
                        <stop offset="100%" stop-color="#3b82f6"/>
                    </linearGradient>
                    <clipPath id="hc2">
                        <polygon points="22,6 38,20 38,40 6,40 6,20"/>
                    </clipPath>
                </defs>
                <rect x="0" y="0" width="44" height="44" rx="11" fill="url(#hg2)"/>
                <ellipse cx="27" cy="30" rx="14" ry="13" fill="url(#sg2)" clip-path="url(#hc2)" opacity="0.9"/>
                <polygon points="22,6 38,20 38,40 6,40 6,20" fill="none" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/>
                <rect x="18" y="31" width="8" height="9" rx="4" fill="#fff" opacity="0.9"/>
                <rect x="27" y="24" width="7" height="6" rx="1.5" fill="#fff" opacity="0.7"/>
            </svg>
            <div>
                <div class="store">لمسات الإبداع</div>
                <div class="sub">خردوات — بديل رخام — بديل خشب — جبسم بورد</div>
                <div class="contact">المليحة - الشارع العام - دخلة عصائر جنتي &nbsp;|&nbsp; 0934609813</div>
            </div>
        </div>
    </div>

    <hr class="divider">

    {{-- Meta --}}
    <div class="section">
        <div class="row">
            <span class="label">رقم العملية</span>
            <span class="value">#{{ $movement->id }}</span>
        </div>
        <div class="row">
            <span class="label">التاريخ</span>
            <span class="value">{{ $movement->created_at->format('d/m/Y  H:i') }}</span>
        </div>
        @if($movement->customer_name)
        <div class="row">
            <span class="label">الزبون</span>
            <span class="value">{{ $movement->customer_name }}</span>
        </div>
        @endif
        <div class="row">
            <span class="label">نوع البيع</span>
            <span class="value">
                @if($movement->is_credit)
                    <span class="badge badge-credit">💳 آجل</span>
                @else
                    <span class="badge badge-cash">✅ نقدي</span>
                @endif
            </span>
        </div>
    </div>

    <hr class="divider">

    {{-- Item --}}
    <div class="section">
        <div class="row">
            <span class="label">المنتج</span>
            <span class="value">{{ $movement->product->name ?? '(محذوف)' }}</span>
        </div>
        <div class="row">
            <span class="label">الكمية</span>
            <span class="value">@qty($movement->qty) {{ $movement->product->unit ?? '' }}</span>
        </div>
        <div class="row">
            <span class="label">سعر الوحدة</span>
            <span class="value">{{ number_format($movement->price, $movement->currency === 'SYP' ? 0 : 2) }} {{ $sym }}</span>
        </div>
        @if($movement->currency === 'SYP' && $movement->exchange_rate)
        <div class="row">
            <span class="label">سعر الصرف</span>
            <span class="value">{{ number_format($movement->exchange_rate, 0) }} ل.س / $</span>
        </div>
        @endif
        @if($movement->note)
        <div class="row">
            <span class="label">ملاحظة</span>
            <span class="value" style="color:#64748b; font-weight:500;">{{ $movement->note }}</span>
        </div>
        @endif
    </div>

    {{-- Total --}}
    @php $rdec = $movement->currency === 'SYP' ? 0 : 2; @endphp
    <div class="total-box">
        <span class="t-label">الإجمالي</span>
        <span class="t-amount">{{ number_format($movement->totalAmount(), $rdec) }} {{ $sym }}</span>
    </div>

    @if($movement->is_credit)
    @php
        $totalPaid = $movement->totalPaid();
        $remAmt    = $movement->remaining();
    @endphp
    <div class="debt-box">
        <span>المدفوع: {{ number_format($totalPaid, $rdec) }} {{ $sym }}</span>
        <span>المتبقي: {{ number_format($remAmt, $rdec) }} {{ $sym }}</span>
    </div>
    @endif

    {{-- mixed payment summary (fully paid) --}}
    @if(!$movement->is_credit && $movement->debtPayments->isNotEmpty())
    @php
        $mixSypPart = $movement->debtPayments->where('pay_currency','SYP')->sum('amount');
        $mixUsdPart = $movement->amount_paid ?? 0;
    @endphp
    <div class="debt-box" style="background:#f0fdf4; color:#15803d;">
        <span>💵 دولار: {{ number_format($mixUsdPart, 2) }} $</span>
        <span>🇸🇾 ليرة: {{ number_format($mixSypPart, 0) }} ل.س</span>
    </div>
    @endif

    <div class="footer">
        شكراً لتعاملكم معنا — لمسات الإبداع<br>
        <span style="font-size:10px;">0934609813 — 0954494500</span>
    </div>
</div>

<script>
    // Auto-open print dialog on load if coming from a fresh sale
    @if(session('success'))
        window.addEventListener('load', () => setTimeout(() => window.print(), 400));
    @endif
</script>
</body>
</html>
