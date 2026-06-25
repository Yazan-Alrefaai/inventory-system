@extends('layouts.app')
@section('title', 'لوحة التحكم')

@section('header')
<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size:26px; font-weight:800; color:#0f172a; margin:0;">👋 أهلاً — لوحة التحكم</h1>
        <p style="color:#64748b; font-size:14px; margin:4px 0 0;">📅 {{ now()->translatedFormat('l، d F Y') }}</p>
    </div>
    <div style="display:flex; align-items:center; gap:6px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:20px; padding:5px 12px; font-size:12px; color:#15803d;">
        <span style="display:inline-block; width:7px; height:7px; background:#22c55e; border-radius:50%; animation:pulse 2s infinite;"></span>
        يتحدث كل دقيقة — <span id="_refresh_badge" style="font-weight:700;">60ث</span>
    </div>
</div>
<style>@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }</style>
<script>window._autoRefreshSec = 300;</script>
@endsection

@section('content')

{{-- ══ بيع سريع ══ --}}
<div class="card" style="padding:22px; margin-bottom:20px; border-right:6px solid #f97316; background:linear-gradient(135deg,#fff7ed,#fff);">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
        <span style="font-size:26px;">🛒</span>
        <div>
            <div style="font-size:18px; font-weight:800; color:#0f172a;">بيع سريع</div>
            <div style="color:#94a3b8; font-size:13px;">ابحث عن المنتج واضغط عليه لبيعه فوراً</div>
        </div>
    </div>
    <div style="position:relative;">
        <input type="text" id="quick_search" placeholder="🔍  اكتب اسم المنتج... مثال: مسمار، صمولة، مفصلات"
               class="input-field" autocomplete="off"
               style="font-size:17px; padding:15px 18px; border-color:#fed7aa; background:#fff; border-width:2px;"
               oninput="quickSearch(this.value)">
        <div id="quick_results" style="display:none; position:absolute; top:calc(100% + 6px); right:0; left:0; background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.12); z-index:999; max-height:320px; overflow-y:auto;"></div>
    </div>
</div>

{{-- ══ Quick Sale Modal ══ --}}
<div id="qs_modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:20px; padding:32px; width:500px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,0.3); position:relative;">
        <button onclick="closeModal()" style="position:absolute; top:16px; left:16px; background:#f1f5f9; border:none; border-radius:8px; width:34px; height:34px; font-size:20px; cursor:pointer; line-height:1;">×</button>

        {{-- Product info --}}
        <div id="qs_product_header" style="margin-bottom:20px; padding:16px; background:#f8fafc; border-radius:12px;">
            <div style="font-size:20px; font-weight:800; color:#0f172a; margin-bottom:8px;" id="qs_name">—</div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <span style="background:#eff6ff; color:#3b82f6; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;" id="qs_cat">—</span>
                <span style="background:#f0fdf4; color:#16a34a; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700;">متاح: <span id="qs_stock">—</span></span>
                <span style="background:#fff7ed; color:#c2410c; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700;">سعر البيع: <span id="qs_price_display">—</span> $</span>
            </div>
        </div>

        <form action="{{ route('stock.out.store') }}" method="POST" id="qs_form">
            @csrf
            <input type="hidden" name="product_id" id="qs_pid">
            <input type="hidden" name="currency" id="qs_currency" value="USD">
            <input type="hidden" name="is_credit" id="qs_is_credit" value="0">

            {{-- العملة --}}
            <div style="margin-bottom:16px;">
                <div style="font-size:13px; font-weight:700; color:#374151; margin-bottom:8px;">💱 العملة</div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
                    <button type="button" id="qs_usd_btn" onclick="qsCurrency('USD')"
                            style="padding:10px 6px; border:2px solid #3b82f6; border-radius:10px; background:#eff6ff; color:#3b82f6; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;">💵 دولار $</button>
                    <button type="button" id="qs_syp_btn" onclick="qsCurrency('SYP')"
                            style="padding:10px 6px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;">🇸🇾 ليرة</button>
                    <button type="button" id="qs_mix_btn" onclick="qsCurrency('MIX')"
                            style="padding:10px 6px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;">💵+🇸🇾 مختلط</button>
                </div>
            </div>

            {{-- سعر الصرف --}}
            <div id="qs_rate_row" style="display:none; margin-bottom:14px;">
                <label style="font-size:12px; font-weight:700; color:#92400e; display:block; margin-bottom:4px;">سعر الصرف (كم ليرة = 1 دولار)</label>
                <input type="number" name="exchange_rate" id="qs_rate" placeholder="مثال: 14000" min="1"
                       class="input-field" oninput="qsCalc()" style="font-size:16px; padding:10px; border-color:#fde68a; background:#fef9c3; color:#92400e; font-weight:700;">
            </div>

            {{-- الكمية والسعر --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                <div>
                    <label style="font-size:13px; font-weight:700; color:#374151; display:block; margin-bottom:6px;">📦 الكمية</label>
                    <input type="number" name="qty" id="qs_qty" value="1" min="0.001" step="0.001" inputmode="decimal" class="input-field" oninput="qsCalc()"
                           style="font-size:26px; font-weight:900; text-align:center; padding:10px; color:#0f172a;">
                    <div id="qs_qty_err" style="display:none; color:#dc2626; font-size:12px; margin-top:4px; font-weight:700; text-align:center;">⚠️ الكمية أكبر من المتاح!</div>
                </div>
                <div>
                    <label style="font-size:13px; font-weight:700; color:#374151; display:block; margin-bottom:6px;">💰 السعر <span id="qs_sym" style="color:#f97316;">$</span></label>
                    <input type="number" name="sale_price" id="qs_sale_price" min="0" step="0.01" class="input-field" oninput="qsCalc()"
                           style="font-size:22px; font-weight:900; color:#f97316; text-align:center; padding:10px; border-color:#fed7aa; background:#fff7ed;">
                </div>
            </div>

            {{-- الإجمالي --}}
            <div style="background:linear-gradient(135deg,#0f172a,#1e293b); border-radius:12px; padding:16px 20px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
                <span style="color:rgba(255,255,255,0.7); font-size:14px; font-weight:600;">💵 الإجمالي</span>
                <span style="color:#fff; font-size:28px; font-weight:900;" id="qs_total_val">—</span>
            </div>

            {{-- قسم الدفع المختلط --}}
            <div id="qs_mix_fields" style="display:none; background:#f0f9ff; border:2px solid #7dd3fc; border-radius:12px; padding:14px; margin-bottom:14px;">
                <div style="font-size:13px; font-weight:800; color:#0369a1; margin-bottom:12px;">💵+🇸🇾 تفاصيل الدفع المختلط</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div>
                        <div style="font-size:12px; font-weight:700; color:#1e40af; margin-bottom:4px;">دفع بالدولار 💵</div>
                        <div style="display:flex; align-items:center; gap:6px; background:#eff6ff; border-radius:8px; padding:6px 10px; border:1.5px solid #bfdbfe;">
                            <input type="number" id="qs_mix_usd" value="0" min="0" step="0.01"
                                   oninput="qsCalcMix()"
                                   style="flex:1; border:none; background:transparent; font-size:18px; font-weight:800; color:#2563eb; outline:none; width:0; font-family:Cairo,sans-serif; direction:ltr; text-align:left;">
                            <span style="font-weight:700; color:#2563eb; font-size:14px;">$</span>
                        </div>
                    </div>
                    <div>
                        <div style="font-size:12px; font-weight:700; color:#92400e; margin-bottom:4px;">دفع بالليرة 🇸🇾</div>
                        <div style="display:flex; align-items:center; gap:6px; background:#fef9c3; border-radius:8px; padding:6px 10px; border:1.5px solid #fde68a;">
                            <input type="number" id="qs_mix_syp" value="0" min="0" step="1"
                                   oninput="qsCalcMix()"
                                   style="flex:1; border:none; background:transparent; font-size:18px; font-weight:800; color:#92400e; outline:none; width:0; font-family:Cairo,sans-serif; direction:ltr; text-align:left;">
                            <span style="font-weight:700; color:#92400e; font-size:13px;">ل.س</span>
                        </div>
                    </div>
                </div>
                <div style="background:#fff; border-radius:8px; padding:10px 12px; font-size:13px; font-weight:700;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="color:#64748b;">الإجمالي بالليرة:</span>
                        <span style="color:#0f172a; font-weight:800;" id="qs_mix_total_syp">0 ل.س</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="color:#64748b;">مجموع المدفوع:</span>
                        <span style="color:#16a34a;" id="qs_mix_paid_total">0 ل.س</span>
                    </div>
                    <div id="qs_mix_diff_row" style="display:none; border-top:1px solid #e2e8f0; padding-top:6px; justify-content:space-between;">
                        <span style="color:#64748b;" id="qs_mix_diff_label">الفرق:</span>
                        <span id="qs_mix_diff_val" style="font-weight:800;">0 ل.س</span>
                    </div>
                </div>
            </div>

            {{-- نقد / دين --}}
            <div id="qs_pay_btns" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
                <button type="button" id="qs_cash_btn" onclick="qsPayment('cash')"
                        style="padding:12px; border:2px solid #10b981; border-radius:10px; background:#f0fdf4; color:#059669; font-weight:800; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">✅ نقداً</button>
                <button type="button" id="qs_debt_btn" onclick="qsPayment('debt')"
                        style="padding:12px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">💳 دَيْن</button>
            </div>

            {{-- حقول الدين --}}
            <div id="qs_debt_fields" style="display:none; background:#fef2f2; border-radius:10px; padding:14px; margin-bottom:14px; border:1.5px solid #fecaca;">
                <input type="text" name="customer_name" id="qs_cname" class="input-field" placeholder="👤 اسم الزبون (مطلوب)"
                       style="margin-bottom:10px; font-weight:700; font-size:15px;">

                {{-- Regular (non-MIX) debt: show amount paid field --}}
                <div id="qs_debt_regular">
                    <label style="font-size:12px; font-weight:700; color:#374151; display:block; margin-bottom:4px;">دفع الآن <span id="qs_debt_sym">$</span></label>
                    <input type="number" name="amount_paid" id="qs_amt_paid" value="0" min="0" step="0.01" class="input-field"
                           oninput="qsCalcDebt()" style="color:#dc2626; font-weight:700; margin-bottom:8px;">
                    <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; padding:8px 12px; background:#fff; border-radius:8px;">
                        <span style="color:#10b981;">✅ دفع: <span id="qs_d_paid">0</span></span>
                        <span style="color:#dc2626;">⏳ متبقي: <span id="qs_d_remain">0</span></span>
                    </div>
                </div>

                {{-- MIX debt: show remaining summary from mix fields --}}
                <div id="qs_debt_mix_summary" style="display:none; background:#fff; border-radius:8px; padding:10px 12px; font-size:13px; font-weight:700;">
                    <div style="color:#64748b; font-size:11px; margin-bottom:8px;">المبلغ المدفوع مأخوذ من حقلي الدولار والليرة أعلاه</div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="color:#64748b;">✅ دفع:</span>
                        <span style="color:#16a34a; font-weight:800;" id="qs_mix_debt_paid">—</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#64748b;">⏳ متبقي دين:</span>
                        <span style="color:#dc2626; font-weight:800;" id="qs_mix_debt_remain">—</span>
                    </div>
                </div>
            </div>

            <button type="submit" id="qs_submit"
                    style="width:100%; padding:16px; background:linear-gradient(135deg,#f97316,#ea580c); color:#fff; border:none; border-radius:12px; font-size:18px; font-weight:900; cursor:pointer; font-family:Cairo,sans-serif; letter-spacing:1px;">
                🛒 تأكيد البيع
            </button>
        </form>
    </div>
</div>

{{-- ══ أرقام اليوم ══ --}}
<div style="margin-bottom:8px; font-size:13px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:1px;">📊 ملخص اليوم</div>
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:14px;">

    {{-- مبيعات اليوم --}}
    <div class="card" style="padding:20px; border-top:5px solid #10b981; text-align:center;">
        <div style="font-size:34px; margin-bottom:4px;">💰</div>
        <div style="font-size:22px; font-weight:900; color:#10b981; line-height:1.1;">{{ number_format($todaySalesVal, 2) }} $</div>
        @if($todaySalesSyp > 0)
        <div style="font-size:15px; font-weight:700; color:#059669; margin-top:3px;">{{ number_format($todaySalesSyp, 0) }} ل.س</div>
        @endif
        @if($weekChangePct !== null)
        <div style="font-size:12px; font-weight:700; margin-top:5px; color:{{ $weekChangePct >= 0 ? '#10b981' : '#ef4444' }};">
            {{ $weekChangePct >= 0 ? '▲' : '▼' }} {{ abs($weekChangePct) }}% عن الأسبوع الماضي
        </div>
        @endif
        <div style="font-size:12px; color:#64748b; margin-top:4px; font-weight:600;">مبيعات اليوم</div>
    </div>

    {{-- ربح اليوم --}}
    <div class="card" style="padding:20px; border-top:5px solid {{ $todayProfitUsd >= 0 ? '#8b5cf6' : '#ef4444' }}; text-align:center;">
        <div style="font-size:34px; margin-bottom:4px;">📈</div>
        <div style="font-size:22px; font-weight:900; color:{{ $todayProfitUsd >= 0 ? '#7c3aed' : '#ef4444' }}; line-height:1.1;">
            {{ $todayProfitUsd >= 0 ? '+' : '' }}{{ number_format($todayProfitUsd, 2) }} $
        </div>
        <div style="font-size:12px; color:#94a3b8; margin-top:4px;">ربح تقديري (بسعر الشراء)</div>
        <div style="font-size:12px; color:#64748b; margin-top:2px; font-weight:600;">ربح اليوم</div>
    </div>

    {{-- الكاش في الدرج --}}
    <div class="card" style="padding:20px; border-top:5px solid #f59e0b; text-align:center;">
        <div style="font-size:34px; margin-bottom:4px;">🗄️</div>
        @if($cashInDrawerSyp > 0 || $cashInDrawerUsd > 0 || $drawerOpenSyp > 0 || $drawerOpenUsd > 0)
            @if($cashInDrawerSyp != 0)
            <div style="font-size:20px; font-weight:900; color:#d97706; line-height:1.1;">{{ number_format($cashInDrawerSyp, 0) }} ل.س</div>
            @endif
            @if($cashInDrawerUsd != 0)
            <div style="font-size:16px; font-weight:700; color:#d97706; margin-top:2px;">{{ number_format($cashInDrawerUsd, 2) }} $</div>
            @endif
        @else
            <div style="font-size:14px; color:#94a3b8; margin-top:4px;">حدد رصيد الافتتاح من صفحة المصاريف</div>
        @endif
        <div style="font-size:12px; color:#64748b; margin-top:4px; font-weight:600;">الكاش في الدرج</div>
        <a href="{{ route('expenses.index') }}" style="font-size:11px; color:#3b82f6; text-decoration:none; font-weight:600;">تفاصيل ←</a>
    </div>

    {{-- الكميات --}}
    <div class="card" style="padding:20px; border-top:5px solid #3b82f6; text-align:center;">
        <div style="font-size:34px; margin-bottom:4px;">📦</div>
        <div style="display:flex; justify-content:center; gap:20px; margin-top:4px;">
            <div>
                <div style="font-size:22px; font-weight:900; color:#3b82f6;">{{ $totalIn }}</div>
                <div style="font-size:11px; color:#64748b; font-weight:600;">وردت ⬇️</div>
            </div>
            <div style="width:1px; background:#f1f5f9;"></div>
            <div>
                <div style="font-size:22px; font-weight:900; color:#f97316;">{{ $totalOut }}</div>
                <div style="font-size:11px; color:#64748b; font-weight:600;">بيعت ⬆️</div>
            </div>
        </div>
        <div style="font-size:12px; color:#64748b; margin-top:6px; font-weight:600;">قطع اليوم</div>
    </div>

    {{-- الديون --}}
    <a href="{{ route('debts.index') }}" style="text-decoration:none;">
    <div class="card" style="padding:20px; border-top:5px solid {{ $activeDebtsCount > 0 ? '#ef4444' : '#10b981' }}; text-align:center; height:100%; box-sizing:border-box;">
        <div style="font-size:34px; margin-bottom:4px;">{{ $activeDebtsCount > 0 ? '🔴' : '✅' }}</div>
        <div style="font-size:24px; font-weight:900; color:{{ $activeDebtsCount > 0 ? '#ef4444' : '#10b981' }}; line-height:1.1;">{{ $activeDebtsCount }}</div>
        @if($activeDebtsCount > 0)
        <div style="font-size:13px; font-weight:700; color:#ef4444; margin-top:4px;">
            @if($totalDebtSyp > 0){{ number_format($totalDebtSyp, 0) }} ل.س@endif
            @if($totalDebtUsd > 0) / {{ number_format($totalDebtUsd, 2) }} $@endif
        </div>
        @if($oldestDebtDays !== null)
        <div style="font-size:11px; font-weight:700; margin-top:5px; padding:3px 10px; border-radius:20px; display:inline-block;
                    background:{{ $oldestDebtDays > 30 ? '#fef2f2' : '#fff7ed' }};
                    color:{{ $oldestDebtDays > 30 ? '#dc2626' : '#c2410c' }};">
            {{ $oldestDebtDays > 30 ? '⚠️' : '⏳' }} أقدم دين: {{ $oldestDebtDays }} يوم
        </div>
        @endif
        @endif
        <div style="font-size:12px; color:#64748b; margin-top:4px; font-weight:600;">
            {{ $activeDebtsCount > 0 ? 'دين مفتوح — اضغط' : 'لا ديون مفتوحة' }}
        </div>
    </div>
    </a>
</div>

{{-- ══ معلومات المخزون ══ --}}
<div style="margin-bottom:8px; font-size:13px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:1px;">📦 حالة المخزون</div>
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; margin-bottom:20px;">
    <div class="card" style="padding:20px; display:flex; align-items:center; gap:16px;">
        <div style="width:52px; height:52px; background:linear-gradient(135deg,#3b82f6,#2563eb); border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:24px; flex-shrink:0;">📦</div>
        <div>
            <div style="font-size:28px; font-weight:900; color:#0f172a; line-height:1;">{{ $totalProducts }}</div>
            <div style="font-size:13px; color:#64748b; margin-top:2px;">صنف مسجل</div>
            @if($deadStockCount > 0)
            <a href="{{ route('products.index') }}" style="font-size:11px; color:#f97316; font-weight:700; text-decoration:none;">⚠️ {{ $deadStockCount }} ما بيع من شهر</a>
            @endif
        </div>
    </div>
    <div class="card" style="padding:20px; display:flex; align-items:center; gap:16px;">
        <div style="width:52px; height:52px; background:linear-gradient(135deg,#8b5cf6,#7c3aed); border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:24px; flex-shrink:0;">🗃️</div>
        <div>
            <div style="font-size:28px; font-weight:900; color:#0f172a; line-height:1;">{{ number_format($totalQty) }}</div>
            <div style="font-size:13px; color:#64748b; margin-top:2px;">قطعة في المخزن</div>
        </div>
    </div>
    <div class="card" style="padding:20px; display:flex; align-items:center; gap:16px;">
        <div style="width:52px; height:52px; background:linear-gradient(135deg,#f59e0b,#d97706); border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:24px; flex-shrink:0;">🛒</div>
        <div>
            <div style="font-size:22px; font-weight:900; color:#0f172a; line-height:1.1;">{{ number_format($totalValue, 0) }} $</div>
            <div style="font-size:11px; color:#94a3b8; margin-top:1px;">بسعر الشراء</div>
            @if($totalSellValue > 0)
            <div style="font-size:14px; font-weight:700; color:#059669; margin-top:2px;">{{ number_format($totalSellValue, 0) }} $ بيع</div>
            @endif
            <div style="font-size:12px; color:#64748b; margin-top:1px;">قيمة المخزون</div>
        </div>
    </div>
</div>

{{-- ══ سعر الدولار ══ --}}
<div class="card" style="padding:0; margin-bottom:20px; overflow:hidden; border-right:5px solid #f59e0b;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; padding:16px 22px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <div style="font-size:36px;">💱</div>
            <div>
                <div style="color:#92400e; font-size:12px; font-weight:700; margin-bottom:2px;">سعر الدولار الحالي (يُستخدم في البيع بالليرة)</div>
                <div style="font-size:30px; font-weight:900; color:#d97706; line-height:1;">
                    1 $ = <span id="rate_display">{{ number_format($usdRate, 0) }}</span> <span style="font-size:18px;">ل.س</span>
                </div>
            </div>
        </div>
        <button onclick="document.getElementById('rate_form_wrap').classList.toggle('hidden-rate')"
                style="padding:10px 22px; background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">
            ✏️ تعديل السعر
        </button>
    </div>
    <div id="rate_form_wrap" class="hidden-rate" style="border-top:1px solid #fde68a; background:#fffbeb; padding:16px 22px;">
        <form action="{{ route('settings.rate') }}" method="POST" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            @csrf
            <label style="font-weight:700; color:#92400e; font-size:15px; white-space:nowrap;">1 $ =</label>
            <input type="number" name="usd_rate" value="{{ $usdRate }}" min="1" step="1"
                   class="input-field" style="font-size:22px; font-weight:800; color:#d97706; border-color:#fde68a; background:#fff; padding:10px 14px; text-align:center; max-width:180px;">
            <span style="font-weight:700; color:#92400e; font-size:16px;">ل.س</span>
            <button type="submit" style="padding:10px 24px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">💾 حفظ</button>
            <button type="button" onclick="document.getElementById('rate_form_wrap').classList.add('hidden-rate')"
                    style="padding:10px 16px; background:#f1f5f9; color:#64748b; border:none; border-radius:10px; font-weight:600; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">إلغاء</button>
        </form>
    </div>
</div>
<style>.hidden-rate { display: none !important; }</style>

{{-- ══ مبيعات آخر 7 أيام ══ --}}
<div style="margin-bottom:8px; font-size:13px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:1px;">📈 مبيعات آخر 7 أيام</div>
<div class="card" style="padding:24px; margin-bottom:20px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
        <div style="font-size:16px; font-weight:800; color:#0f172a;">كم بعنا كل يوم؟ (بالدولار)</div>
        <div style="display:flex; gap:16px; font-size:12px; font-weight:600;">
            <span style="display:flex; align-items:center; gap:5px;"><span style="width:12px; height:12px; background:#10b981; border-radius:3px; display:inline-block;"></span> وارد (قطع)</span>
            <span style="display:flex; align-items:center; gap:5px;"><span style="width:12px; height:12px; background:#f97316; border-radius:3px; display:inline-block;"></span> مبيع (قطع)</span>
            <span style="display:flex; align-items:center; gap:5px;"><span style="width:12px; height:3px; background:#8b5cf6; border-radius:3px; display:inline-block;"></span> مبيعات ($)</span>
        </div>
    </div>
    <div style="color:#94a3b8; font-size:12px; margin-bottom:16px;">الأعمدة الخضراء = ما ورد، الأعمدة البرتقالية = ما بيع، الخط البنفسجي = قيمة المبيعات بالدولار</div>
    <canvas id="movementChart" height="80"></canvas>
</div>

{{-- ══ أكثر مبيعاً + بضاعة راكدة ══ --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">

    {{-- Top 5 sellers this week --}}
    <div class="card" style="padding:22px;">
        <div style="font-size:15px; font-weight:800; color:#0f172a; margin-bottom:4px;">🏆 الأكثر مبيعاً هذا الأسبوع</div>
        <div style="color:#94a3b8; font-size:12px; margin-bottom:14px;">عدد القطع المبيعة من {{ now()->startOfWeek()->format('d/m') }} حتى اليوم</div>
        @if($topSellers->isEmpty())
            <div style="text-align:center; padding:24px; color:#94a3b8;">
                <div style="font-size:36px; margin-bottom:8px;">📭</div>
                <div style="font-size:13px;">لا توجد مبيعات هذا الأسبوع بعد</div>
            </div>
        @else
            @foreach($topSellers as $i => $row)
            @php
                $medals = ['🥇','🥈','🥉','4️⃣','5️⃣'];
                $medal  = $medals[$i] ?? ($i+1 . '.');
            @endphp
            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; margin-bottom:6px; background:{{ $i===0 ? '#fffbeb' : '#f8fafc' }}; border-radius:10px; border:1.5px solid {{ $i===0 ? '#fde68a' : '#f1f5f9' }};">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:22px; line-height:1;">{{ $medal }}</span>
                    <div>
                        <div style="font-weight:700; color:#0f172a; font-size:14px;">{{ $row['product']->name }}</div>
                        <div style="font-size:11px; color:#94a3b8;">{{ $row['product']->category->name ?? '' }}</div>
                    </div>
                </div>
                <div style="text-align:center; background:{{ $i===0 ? '#fef9c3' : '#eff6ff' }}; border-radius:8px; padding:4px 12px;">
                    <div style="font-size:18px; font-weight:900; color:{{ $i===0 ? '#d97706' : '#3b82f6' }};">{{ $row['total_qty'] }}</div>
                    <div style="font-size:10px; color:#94a3b8;">{{ $row['product']->unit }}</div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    {{-- Dead stock --}}
    <div class="card" style="padding:22px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
            <div style="font-size:15px; font-weight:800; color:#0f172a;">📦 بضاعة راكدة</div>
            @if($deadStockCount > 0)
            <span style="background:#fff7ed; color:#c2410c; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700;">{{ $deadStockCount }} منتج</span>
            @endif
        </div>
        <div style="color:#94a3b8; font-size:12px; margin-bottom:14px;">منتجات موجودة في المخزن ولم تُباع خلال آخر 30 يوم</div>
        @if($deadProducts->isEmpty())
            <div style="text-align:center; padding:24px; color:#10b981;">
                <div style="font-size:36px; margin-bottom:8px;">✅</div>
                <div style="font-size:13px; font-weight:700;">كل البضاعة تتحرك — ممتاز!</div>
            </div>
        @else
            <div style="max-height:230px; overflow-y:auto;">
            @foreach($deadProducts as $p)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; margin-bottom:6px; background:#fff7ed; border-radius:10px; border:1.5px solid #fed7aa;">
                <div>
                    <div style="font-weight:700; color:#0f172a; font-size:13px;">{{ $p->name }}</div>
                    <div style="font-size:11px; color:#94a3b8;">{{ $p->category->name ?? '' }}</div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="text-align:center;">
                        <div style="font-size:16px; font-weight:800; color:#c2410c;">@qty($p->qty)</div>
                        <div style="font-size:10px; color:#94a3b8;">{{ $p->unit }}</div>
                    </div>
                    <a href="{{ route('stock.out') }}?product_id={{ $p->id }}"
                       style="background:#fff; border:1.5px solid #fed7aa; color:#c2410c; border-radius:7px; padding:4px 8px; font-size:11px; font-weight:700; text-decoration:none;">
                        بيع ⬆️
                    </a>
                </div>
            </div>
            @endforeach
            @if($deadStockCount > 6)
            <div style="text-align:center; padding:8px; color:#94a3b8; font-size:12px;">+ {{ $deadStockCount - 6 }} منتج آخر</div>
            @endif
            </div>
        @endif
    </div>
</div>

{{-- ══ صف السفلي ══ --}}
<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-bottom:20px;">

    {{-- توزيع المخزون --}}
    <div class="card" style="padding:24px;">
        <div style="font-size:15px; font-weight:800; color:#0f172a; margin-bottom:4px;">🗂️ توزيع المخزون</div>
        <div style="color:#94a3b8; font-size:12px; margin-bottom:16px;">كم قطعة في كل صنف؟</div>
        <canvas id="categoryChart" height="200"></canvas>
    </div>

    {{-- مخزون منخفض --}}
    <div class="card" style="padding:24px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
            <div style="font-size:15px; font-weight:800; color:#0f172a;">⚠️ يحتاج تعبئة</div>
            @if($lowStockProducts->count() > 0)
            <span style="background:#fef2f2; color:#dc2626; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700;">{{ $lowStockProducts->count() }} منتج</span>
            @endif
        </div>
        <div style="color:#94a3b8; font-size:12px; margin-bottom:16px;">منتجات وصلت للحد الأدنى</div>
        @if($lowStockProducts->isEmpty())
            <div style="text-align:center; padding:28px; color:#10b981;">
                <div style="font-size:42px; margin-bottom:8px;">✅</div>
                <div style="font-size:14px; font-weight:700;">كل المنتجات بمستوى جيد</div>
            </div>
        @else
            <div style="max-height:220px; overflow-y:auto;">
            @foreach($lowStockProducts as $p)
            <div style="display:flex; justify-content:space-between; align-items:center; background:#fef2f2; border:1.5px solid #fecaca; border-radius:10px; padding:10px 14px; margin-bottom:8px;">
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:700; color:#0f172a; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $p->name }}</div>
                    <div style="color:#94a3b8; font-size:11px;">الحد الأدنى: {{ $p->min_qty }}</div>
                </div>
                <div style="text-align:center; margin:0 10px;">
                    <div style="font-weight:900; color:#dc2626; font-size:20px;">@qty($p->qty)</div>
                    <div style="color:#94a3b8; font-size:10px;">{{ $p->unit }}</div>
                </div>
                <a href="{{ route('stock.in') }}?product_id={{ $p->id }}"
                   style="background:#dcfce7; color:#15803d; border-radius:8px; padding:5px 10px; font-size:11px; font-weight:700; text-decoration:none; white-space:nowrap; flex-shrink:0;">
                    ⬇️ طلب
                </a>
            </div>
            @endforeach
            </div>
        @endif
    </div>

    {{-- آخر الحركات --}}
    <div class="card" style="padding:24px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
            <div style="font-size:15px; font-weight:800; color:#0f172a;">📋 آخر الحركات</div>
            <a href="{{ route('stock.history') }}" style="color:#3b82f6; font-size:12px; text-decoration:none; font-weight:700;">عرض الكل ←</a>
        </div>
        <div style="color:#94a3b8; font-size:12px; margin-bottom:16px;">أحدث عمليات البيع والإدخال</div>
        @forelse($recentMovements as $m)
        <div style="display:flex; align-items:center; justify-content:space-between; padding:9px 0; border-bottom:1px solid #f1f5f9;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:15px; background:{{ $m->type==='in'?'#dcfce7':'#fef3c7' }}; flex-shrink:0;">
                    {{ $m->type==='in'?'⬇️':'⬆️' }}
                </div>
                <div>
                    <div style="font-weight:700; color:#0f172a; font-size:13px; max-width:130px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $m->product->name ?? '(محذوف)' }}</div>
                    <div style="color:#94a3b8; font-size:11px;">{{ $m->created_at->format('d/m H:i') }}</div>
                </div>
            </div>
            <div style="text-align:left; flex-shrink:0;">
                <div style="font-weight:800; font-size:15px; color:{{ $m->type==='in'?'#16a34a':'#d97706' }}">
                    {{ $m->type==='in'?'+':'-' }}@qty($m->qty)
                </div>
                @if($m->price && $m->type==='out')
                    <div style="font-size:11px; color:#94a3b8; font-weight:600;">{{ number_format($m->totalAmount(),0) }} {{ $m->currencySymbol() }}</div>
                @endif
                @if($m->is_credit && !$m->isFullyPaid())
                    <div style="font-size:10px; background:#fef2f2; color:#dc2626; border-radius:10px; padding:1px 6px; font-weight:700; text-align:center;">دين</div>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align:center; padding:28px; color:#94a3b8;"><div style="font-size:42px; margin-bottom:8px;">📭</div>لا توجد حركات بعد</div>
        @endforelse
    </div>
</div>

{{-- ══ روابط سريعة ══ --}}
<div style="margin-bottom:8px; font-size:13px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:1px;">⚡ الإجراءات الشائعة</div>
<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:8px;">
    <a href="{{ route('stock.in') }}" style="text-decoration:none;">
        <div class="card" style="padding:18px; text-align:center; border-top:4px solid #10b981; transition:transform 0.15s; cursor:pointer;"
             onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:30px; margin-bottom:6px;">⬇️</div>
            <div style="font-weight:700; color:#0f172a; font-size:13px;">إدخال بضاعة</div>
        </div>
    </a>
    <a href="{{ route('sales.create') }}" style="text-decoration:none;">
        <div class="card" style="padding:18px; text-align:center; border-top:4px solid #3b82f6; transition:transform 0.15s; cursor:pointer;"
             onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:30px; margin-bottom:6px;">🧾</div>
            <div style="font-weight:700; color:#0f172a; font-size:13px;">فاتورة جديدة</div>
        </div>
    </a>
    <a href="{{ route('debts.index') }}" style="text-decoration:none;">
        <div class="card" style="padding:18px; text-align:center; border-top:4px solid {{ $activeDebtsCount > 0 ? '#ef4444' : '#94a3b8' }}; transition:transform 0.15s; cursor:pointer;"
             onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:30px; margin-bottom:6px;">💳</div>
            <div style="font-weight:700; color:#0f172a; font-size:13px;">الديون</div>
            @if($activeDebtsCount > 0)
                <div style="background:#fef2f2; color:#dc2626; font-size:11px; font-weight:700; border-radius:20px; padding:2px 8px; margin-top:4px;">{{ $activeDebtsCount }} غير مسدد</div>
            @endif
        </div>
    </a>
    <a href="{{ route('products.index') }}" style="text-decoration:none;">
        <div class="card" style="padding:18px; text-align:center; border-top:4px solid #8b5cf6; transition:transform 0.15s; cursor:pointer;"
             onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:30px; margin-bottom:6px;">📦</div>
            <div style="font-weight:700; color:#0f172a; font-size:13px;">المنتجات</div>
        </div>
    </a>
    <a href="{{ route('reports.index') }}" style="text-decoration:none;">
        <div class="card" style="padding:18px; text-align:center; border-top:4px solid #f59e0b; transition:transform 0.15s; cursor:pointer;"
             onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:30px; margin-bottom:6px;">📊</div>
            <div style="font-weight:700; color:#0f172a; font-size:13px;">التقارير</div>
        </div>
    </a>
</div>

{{-- ══ Products JSON for quick sale ══ --}}
@php
$qsProductsData = $allProducts->map(fn($p) => [
    'id'        => $p->id,
    'name'      => $p->name,
    'cat'       => $p->category->name ?? '',
    'qty'       => $p->qty,
    'unit'      => $p->unit,
    'price'     => (float) $p->defaultSellPrice(),
    'buy_price' => (float) $p->price,
]);

// Chart data passed from controller (no extra DB queries needed)
@endphp

<script>
const QS_PRODUCTS = @json($qsProductsData);
let qsSelected = null, qsCurr = 'USD', qsPay = 'cash';
const QS_USD_RATE = {{ $usdRate }};

function quickSearch(q) {
    const box = document.getElementById('quick_results');
    if (!q.trim()) { box.style.display='none'; return; }
    const ql = q.toLowerCase();
    const results = QS_PRODUCTS.filter(p => p.name.toLowerCase().includes(ql) || p.cat.toLowerCase().includes(ql));
    if (!results.length) {
        box.innerHTML = '<div style="padding:20px; text-align:center; color:#94a3b8; font-size:15px;">🔍 لا توجد نتائج</div>';
    } else {
        box.innerHTML = results.map(p => `
            <div onclick="openModal(${p.id})"
                 style="padding:14px 18px; cursor:pointer; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;"
                 onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background='#fff'">
                <div>
                    <div style="font-weight:700; color:#0f172a; font-size:15px;">${p.name}</div>
                    <div style="color:#94a3b8; font-size:12px; margin-top:2px;">${p.cat} — متاح: <strong style="color:#0f172a;">${p.qty}</strong> ${p.unit}</div>
                </div>
                <div style="font-weight:900; color:#f97316; font-size:16px; text-align:left;">${p.price.toFixed(2)} $</div>
            </div>`).join('');
    }
    box.style.display = 'block';
}

function openModal(id) {
    qsSelected = QS_PRODUCTS.find(p => p.id === id);
    if (!qsSelected) return;
    document.getElementById('quick_results').style.display = 'none';
    document.getElementById('quick_search').value = '';
    document.getElementById('qs_pid').value              = qsSelected.id;
    document.getElementById('qs_name').textContent       = qsSelected.name;
    document.getElementById('qs_cat').textContent        = qsSelected.cat;
    document.getElementById('qs_stock').textContent      = qsSelected.qty + ' ' + qsSelected.unit;
    document.getElementById('qs_price_display').textContent = qsSelected.price.toFixed(2);
    document.getElementById('qs_sale_price').value       = qsSelected.price.toFixed(2);
    document.getElementById('qs_qty').value              = 1;
    document.getElementById('qs_amt_paid').value         = 0;
    qsCurr = 'USD'; qsPay = 'cash';
    document.getElementById('qs_currency').value         = 'USD';
    document.getElementById('qs_is_credit').value        = '0';
    document.getElementById('qs_rate').value             = QS_USD_RATE;
    document.getElementById('qs_rate_row').style.display = 'none';
    document.getElementById('qs_debt_fields').style.display = 'none';
    document.getElementById('qs_mix_fields').style.display = 'none';
    document.getElementById('qs_pay_btns').style.display = 'grid';
    document.getElementById('qs_mix_usd').value = 0;
    document.getElementById('qs_mix_syp').value = 0;
    // Reset buttons
    document.getElementById('qs_usd_btn').style.cssText = 'padding:10px 6px; border:2px solid #3b82f6; border-radius:10px; background:#eff6ff; color:#3b82f6; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;';
    document.getElementById('qs_syp_btn').style.cssText = 'padding:10px 6px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;';
    document.getElementById('qs_mix_btn').style.cssText = 'padding:10px 6px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;';
    document.getElementById('qs_cash_btn').style.cssText = 'padding:12px; border:2px solid #10b981; border-radius:10px; background:#f0fdf4; color:#059669; font-weight:800; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;';
    document.getElementById('qs_debt_btn').style.cssText = 'padding:12px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;';
    qsCalc();
    document.getElementById('qs_modal').style.display = 'flex';
    setTimeout(() => document.getElementById('qs_qty').focus(), 100);
}

function closeModal() { document.getElementById('qs_modal').style.display = 'none'; }

function qsCurrency(c) {
    qsCurr = c;
    const sym = document.getElementById('qs_sym'), ds = document.getElementById('qs_debt_sym');
    // Reset all currency buttons
    document.getElementById('qs_usd_btn').style.cssText = 'padding:10px 6px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;';
    document.getElementById('qs_syp_btn').style.cssText = 'padding:10px 6px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;';
    document.getElementById('qs_mix_btn').style.cssText = 'padding:10px 6px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;';

    document.getElementById('qs_mix_fields').style.display = 'none';
    document.getElementById('qs_pay_btns').style.display = 'grid';

    if (c === 'USD') {
        document.getElementById('qs_usd_btn').style.cssText = 'padding:10px 6px; border:2px solid #3b82f6; border-radius:10px; background:#eff6ff; color:#3b82f6; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;';
        document.getElementById('qs_currency').value = 'USD';
        document.getElementById('qs_rate_row').style.display = 'none';
        sym.textContent = '$'; ds.textContent = '$';
        if (qsSelected) document.getElementById('qs_sale_price').value = qsSelected.price.toFixed(2);
    } else if (c === 'SYP') {
        document.getElementById('qs_syp_btn').style.cssText = 'padding:10px 6px; border:2px solid #f59e0b; border-radius:10px; background:#fef9c3; color:#92400e; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;';
        document.getElementById('qs_currency').value = 'SYP';
        document.getElementById('qs_rate_row').style.display = 'block';
        sym.textContent = 'ل.س'; ds.textContent = 'ل.س';
        const rateEl = document.getElementById('qs_rate');
        if (!rateEl.value || rateEl.value == '0') rateEl.value = QS_USD_RATE;
        const rate = parseFloat(rateEl.value) || QS_USD_RATE;
        if (rate > 0 && qsSelected) document.getElementById('qs_sale_price').value = Math.round(qsSelected.price * rate);
    } else { // MIX
        document.getElementById('qs_mix_btn').style.cssText = 'padding:10px 6px; border:2px solid #0ea5e9; border-radius:10px; background:#f0f9ff; color:#0369a1; font-weight:800; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;';
        document.getElementById('qs_currency').value = 'SYP'; // backend gets SYP
        document.getElementById('qs_rate_row').style.display = 'block';
        sym.textContent = '$'; // price shown in USD
        const rateEl = document.getElementById('qs_rate');
        if (!rateEl.value || rateEl.value == '0') rateEl.value = QS_USD_RATE;
        if (qsSelected) document.getElementById('qs_sale_price').value = qsSelected.price.toFixed(2);
        document.getElementById('qs_mix_fields').style.display = 'block';
        document.getElementById('qs_pay_btns').style.display = 'grid'; // keep cash/debt buttons
        document.getElementById('qs_is_credit').value = '0';
        document.getElementById('qs_debt_fields').style.display = 'none';
        document.getElementById('qs_mix_usd').value = qsSelected ? qsSelected.price.toFixed(2) : 0;
        document.getElementById('qs_mix_syp').value = 0;
        // Reset to cash mode visually
        document.getElementById('qs_cash_btn').style.cssText = 'padding:12px; border:2px solid #10b981; border-radius:10px; background:#f0fdf4; color:#059669; font-weight:800; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;';
        document.getElementById('qs_debt_btn').style.cssText = 'padding:12px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;';
    }
    qsCalc();
}

function qsCalcMix() {
    if (!qsSelected) return;
    const qty      = parseFloat(document.getElementById('qs_qty').value) || 0;
    const priceUsd = parseFloat(document.getElementById('qs_sale_price').value) || 0;
    const rate     = parseFloat(document.getElementById('qs_rate').value) || QS_USD_RATE;
    const paidUsd  = Math.max(0, parseFloat(document.getElementById('qs_mix_usd').value) || 0);
    const paidSypDirect = Math.max(0, parseFloat(document.getElementById('qs_mix_syp').value) || 0);
    const totalUsd = qty * priceUsd;
    const totalSyp = Math.round(totalUsd * rate);

    // Total paid in SYP equivalent
    const paidUsdAsSyp = Math.round(paidUsd * rate);
    const paidTotalSyp = paidUsdAsSyp + paidSypDirect;
    const diff         = paidTotalSyp - totalSyp;

    document.getElementById('qs_mix_total_syp').textContent  = totalSyp.toLocaleString('en-US') + ' ل.س';
    document.getElementById('qs_mix_paid_total').textContent =
        (paidUsd > 0 ? paidUsd.toLocaleString('en-US') + ' $' : '') +
        (paidUsd > 0 && paidSypDirect > 0 ? ' + ' : '') +
        (paidSypDirect > 0 ? paidSypDirect.toLocaleString('en-US') + ' ل.س' : '') +
        (paidTotalSyp > 0 ? ' = ' + paidTotalSyp.toLocaleString('en-US') + ' ل.س' : '0 ل.س');

    // Show diff row
    const diffRow = document.getElementById('qs_mix_diff_row');
    if (paidTotalSyp > 0) {
        diffRow.style.display = 'flex';
        if (diff > 0) {
            document.getElementById('qs_mix_diff_label').textContent = 'زيادة (فكة):';
            document.getElementById('qs_mix_diff_val').textContent   = diff.toLocaleString('en-US') + ' ل.س';
            document.getElementById('qs_mix_diff_val').style.color   = '#16a34a';
        } else if (diff < 0) {
            document.getElementById('qs_mix_diff_label').textContent = 'ناقص:';
            document.getElementById('qs_mix_diff_val').textContent   = Math.abs(diff).toLocaleString('en-US') + ' ل.س';
            document.getElementById('qs_mix_diff_val').style.color   = '#dc2626';
        } else {
            document.getElementById('qs_mix_diff_label').textContent = '✅ مطابق';
            document.getElementById('qs_mix_diff_val').textContent   = '';
            document.getElementById('qs_mix_diff_val').style.color   = '#16a34a';
        }
    } else {
        diffRow.style.display = 'none';
    }

    // Update total display
    const parts = [];
    if (paidUsd > 0) parts.push(paidUsd.toLocaleString('en-US') + ' $');
    if (paidSypDirect > 0) parts.push(paidSypDirect.toLocaleString('en-US') + ' ل.س');
    document.getElementById('qs_total_val').textContent =
        totalSyp.toLocaleString('en-US') + ' ل.س' +
        (parts.length ? ' (' + parts.join(' + ') + ')' : '');

    // Update debt summary if debt mode is active
    if (qsPay === 'debt') {
        const remain = Math.max(0, totalSyp - paidTotalSyp);
        const paidLabel = parts.length ? parts.join(' + ') + ' = ' + paidTotalSyp.toLocaleString('en-US') + ' ل.س' : '0 ل.س';
        document.getElementById('qs_mix_debt_paid').textContent   = paidLabel;
        document.getElementById('qs_mix_debt_remain').textContent = remain.toLocaleString('en-US') + ' ل.س' + (remain === 0 ? ' ✅' : '');
    }
}

function qsPayment(mode) {
    qsPay = mode;
    document.getElementById('qs_is_credit').value = mode === 'debt' ? '1' : '0';
    const df = document.getElementById('qs_debt_fields');
    const isMix = (qsCurr === 'MIX');

    if (mode === 'cash') {
        document.getElementById('qs_cash_btn').style.cssText = 'padding:12px; border:2px solid #10b981; border-radius:10px; background:#f0fdf4; color:#059669; font-weight:800; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;';
        document.getElementById('qs_debt_btn').style.cssText = 'padding:12px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;';
        df.style.display = 'none';
    } else {
        document.getElementById('qs_debt_btn').style.cssText = 'padding:12px; border:2px solid #ef4444; border-radius:10px; background:#fef2f2; color:#dc2626; font-weight:800; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;';
        document.getElementById('qs_cash_btn').style.cssText = 'padding:12px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:800; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;';
        df.style.display = 'block';
        // MIX mode: hide regular paid field, show mix debt summary
        document.getElementById('qs_debt_regular').style.display     = isMix ? 'none'  : 'block';
        document.getElementById('qs_debt_mix_summary').style.display = isMix ? 'block' : 'none';
        if (isMix) qsCalcMix(); // refresh the mix debt summary
        document.getElementById('qs_cname').focus();
    }
}

function qsCalc() {
    if (!qsSelected) return;
    const qty   = parseFloat(document.getElementById('qs_qty').value) || 0;
    const price = parseFloat(document.getElementById('qs_sale_price').value) || 0;
    const btn   = document.getElementById('qs_submit');
    const err   = document.getElementById('qs_qty_err');
    if (qty <= 0 || qty > qsSelected.qty) {
        err.style.display='block';
        err.textContent = qty <= 0 ? '⚠️ الكمية يجب أن تكون أكبر من صفر' : '⚠️ الكمية أكبر من المتاح!';
        btn.disabled=true; btn.style.opacity='0.4';
    } else { err.style.display='none'; btn.disabled=false; btn.style.opacity='1'; }

    if (qsCurr === 'MIX') {
        qsCalcMix();
        return;
    }
    const total = qty * price;
    const sym   = qsCurr === 'SYP' ? 'ل.س' : '$';
    document.getElementById('qs_total_val').textContent = total.toLocaleString('en-US') + ' ' + sym;
    qsCalcDebt();
}

function qsCalcDebt() {
    if (qsPay !== 'debt') return;
    const qty   = parseFloat(document.getElementById('qs_qty').value) || 0;
    const price = parseFloat(document.getElementById('qs_sale_price').value) || 0;
    const paid  = parseFloat(document.getElementById('qs_amt_paid').value) || 0;
    const total = qty * price;
    const sym   = qsCurr === 'SYP' ? 'ل.س' : '$';
    document.getElementById('qs_d_paid').textContent   = Math.min(paid, total).toLocaleString('en-US') + ' ' + sym;
    document.getElementById('qs_d_remain').textContent = Math.max(0, total - paid).toLocaleString('en-US') + ' ' + sym;
}

document.getElementById('qs_modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Customer name validation for debt quick-sales
document.getElementById('qs_form').addEventListener('submit', function(e) {
    // MIX mode: send as USD price + payment_mode=mix + mix_usd + mix_syp
    // This way the controller correctly splits USD→USD bucket and SYP→SYP bucket in drawer
    if (qsCurr === 'MIX') {
        const priceUsd = parseFloat(document.getElementById('qs_sale_price').value) || 0;
        const rate     = parseFloat(document.getElementById('qs_rate').value) || QS_USD_RATE;
        if (rate <= 0) { e.preventDefault(); alert('يرجى إدخال سعر الصرف'); return; }
        const qty     = parseFloat(document.getElementById('qs_qty').value) || 0;
        const paidUsd = Math.min(Math.max(0, parseFloat(document.getElementById('qs_mix_usd').value) || 0), priceUsd * qty);
        const paidSyp = Math.max(0, parseFloat(document.getElementById('qs_mix_syp').value) || 0);

        // Keep sale price as USD, set currency=USD, add mix fields
        document.getElementById('qs_currency').value = 'USD';
        // qs_sale_price stays as USD price (no conversion)

        function addHidden(name, id, val) {
            let el = document.getElementById(id);
            if (!el) {
                el = document.createElement('input');
                el.type = 'hidden'; el.name = name; el.id = id;
                document.getElementById('qs_form').appendChild(el);
            }
            el.value = val;
        }
        addHidden('payment_mode',      'qs_pm_hidden',       'mix');
        addHidden('mix_usd',           'qs_mix_usd_hidden',  paidUsd);
        addHidden('mix_syp',           'qs_mix_syp_hidden',  paidSyp);
        addHidden('mix_exchange_rate', 'qs_mix_rate_hidden', rate);

        const parts = [];
        if (paidUsd > 0) parts.push(paidUsd.toLocaleString('en-US') + ' $');
        if (paidSyp > 0) parts.push(paidSyp.toLocaleString('en-US') + ' ل.س');
        addHidden('note', 'qs_mix_note_hidden', parts.length ? 'دفع مختلط: ' + parts.join(' + ') : '');
        return;
    }

    // Non-MIX debt validation: customer name required
    if (qsPay !== 'debt') return;
    const name = document.getElementById('qs_cname').value.trim();
    if (!name) {
        e.preventDefault();
        const inp = document.getElementById('qs_cname');
        inp.style.borderColor = '#ef4444';
        inp.style.background  = '#fef2f2';
        inp.placeholder = '⚠️ اسم الزبون مطلوب للبيع بالدين!';
        inp.focus();
        setTimeout(() => {
            inp.style.borderColor = '';
            inp.style.background  = '';
            inp.placeholder = '👤 اسم الزبون (مطلوب)';
        }, 3000);
        return;
    }
    // MIX+debt: also require customer name
    if (qsCurr === 'MIX' && qsPay === 'debt') {
        if (!name) { e.preventDefault(); return; }
    }
});
document.addEventListener('click', function(e) {
    if (!e.target.closest('#quick_search') && !e.target.closest('#quick_results'))
        document.getElementById('quick_results').style.display = 'none';
});

// ══ الرسم البياني الرئيسي ══
new Chart(document.getElementById('movementChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! $chartLabels !!},
        datasets: [
            {
                label: 'وارد (قطع)',
                data: {!! $chartIn !!},
                backgroundColor: 'rgba(16,185,129,0.75)',
                borderRadius: 6,
                yAxisID: 'y'
            },
            {
                label: 'مبيع (قطع)',
                data: {!! $chartOut !!},
                backgroundColor: 'rgba(249,115,22,0.75)',
                borderRadius: 6,
                yAxisID: 'y'
            },
            {
                label: 'مبيعات ($)',
                data: {!! $chartSales !!},
                type: 'line',
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139,92,246,0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#8b5cf6',
                pointRadius: 6,
                pointHoverRadius: 8,
                tension: 0.4,
                fill: true,
                yAxisID: 'y2'
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                rtl: true,
                titleFont: { family: 'Cairo', size: 13 },
                bodyFont: { family: 'Cairo', size: 13 },
                callbacks: {
                    label: function(ctx) {
                        if (ctx.dataset.label === 'مبيعات ($)') return ' مبيعات: ' + ctx.parsed.y.toLocaleString('en-US') + ' $';
                        if (ctx.dataset.label === 'وارد (قطع)')  return ' وارد: ' + ctx.parsed.y + ' قطعة';
                        return ' مبيع: ' + ctx.parsed.y + ' قطعة';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                position: 'right',
                grid: { color: '#f1f5f9' },
                ticks: { font: { family: 'Cairo', size: 11 }, color: '#64748b' },
                title: { display: true, text: 'عدد القطع', font: { family: 'Cairo', size: 11 }, color: '#64748b' }
            },
            y2: {
                beginAtZero: true,
                position: 'left',
                grid: { display: false },
                ticks: { font: { family: 'Cairo', size: 11 }, color: '#8b5cf6', callback: v => v + ' $' },
                title: { display: true, text: 'مبيعات $', font: { family: 'Cairo', size: 11 }, color: '#8b5cf6' }
            },
            x: {
                grid: { display: false },
                ticks: { font: { family: 'Cairo', size: 12 }, color: '#374151' }
            }
        }
    }
});

// ══ رسم التصنيفات ══
new Chart(document.getElementById('categoryChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: {!! $catLabels !!},
        datasets: [{
            data: {!! $catQty !!},
            backgroundColor: ['#3b82f6','#10b981','#f97316','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f59e0b'],
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        cutout: '60%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { family: 'Cairo', size: 11 }, usePointStyle: true, padding: 10 }
            },
            tooltip: {
                titleFont: { family: 'Cairo' },
                bodyFont: { family: 'Cairo' },
                callbacks: {
                    label: function(ctx) {
                        return ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString('en-US') + ' قطعة';
                    }
                }
            }
        }
    }
});
</script>
@endsection
