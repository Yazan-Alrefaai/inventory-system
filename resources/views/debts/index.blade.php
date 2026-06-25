@extends('layouts.app')
@section('title', 'الديون')

@section('header')
<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
    <div>
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">💳 الديون</h1>
        <p style="color:#64748b; font-size:14px; margin:4px 0 0;">متابعة مبيعات الدين والمبالغ المتبقية</p>
    </div>
    <div style="display:flex; align-items:center; gap:10px;">
        <div style="display:flex; align-items:center; gap:6px; background:#fef2f2; border:1px solid #fecaca; border-radius:20px; padding:5px 12px; font-size:12px; color:#991b1b;">
            <span style="display:inline-block; width:7px; height:7px; background:#ef4444; border-radius:50%; animation:pulse 2s infinite;"></span>
            يتحدث تلقائياً — <span id="_refresh_badge" style="font-weight:700;">60ث</span>
        </div>
        <a href="{{ route('sales.create') }}" style="padding:10px 20px; background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; border-radius:10px; font-weight:700; font-size:14px; text-decoration:none;">🧾 فاتورة جديدة</a>
    </div>
</div>
<style>@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }</style>
<script>window._autoRefreshSec = 300;</script>
@endsection

@section('content')

{{-- Search --}}
<div class="card" style="padding:16px 20px; margin-bottom:20px;">
    <form method="GET" style="display:flex; gap:10px; align-items:center;">
        <div style="position:relative; flex:1; max-width:400px;">
            <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#94a3b8;">🔍</span>
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="ابحث باسم الزبون..."
                   class="input-field" style="padding-right:36px;">
        </div>
        <button type="submit" class="btn-primary" style="padding:10px 20px;">بحث</button>
        @if($search)
            <a href="{{ route('debts.index') }}"
               style="padding:10px 16px; border:1.5px solid #e2e8f0; border-radius:10px; color:#64748b; font-size:14px; font-weight:500; text-decoration:none; background:#fff;">× مسح</a>
        @endif
    </form>
    @if($search)
    <div style="margin-top:8px; font-size:13px; color:#64748b;">
        نتائج البحث عن: <strong style="color:#0f172a;">{{ $search }}</strong>
    </div>
    @endif
</div>

@php
$totalActiveSyp = $activeMovementDebts->where('currency','SYP')->sum(fn($m) => $m->remaining())
                + $activeSaleDebts->where('currency','SYP')->sum(fn($s) => $s->remaining());
$totalActiveUsd = $activeMovementDebts->where('currency','USD')->sum(fn($m) => $m->remaining())
                + $activeSaleDebts->where('currency','USD')->sum(fn($s) => $s->remaining());
$totalActive    = $activeMovementDebts->count() + $activeSaleDebts->count();
$totalPaid      = $paidMovementDebts->count()   + $paidSaleDebts->count();
@endphp

{{-- Summary --}}
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px;">
    <div class="card" style="padding:20px; border-top:4px solid #ef4444;">
        <div style="color:#94a3b8; font-size:12px; margin-bottom:6px;">ديون مفتوحة</div>
        <div style="font-size:32px; font-weight:800; color:#ef4444;">{{ $totalActive }}</div>
        <div style="color:#94a3b8; font-size:12px; margin-top:2px;">زبون لم يسدد بعد</div>
    </div>
    <div class="card" style="padding:20px; border-top:4px solid #f97316;">
        <div style="color:#94a3b8; font-size:12px; margin-bottom:6px;">إجمالي المتبقي 💰</div>
        @if($totalActiveSyp > 0)
        <div style="font-size:26px; font-weight:800; color:#f97316;">{{ number_format($totalActiveSyp, 0) }} ل.س</div>
        @endif
        @if($totalActiveUsd > 0)
        <div style="font-size:20px; font-weight:700; color:#f97316; margin-top:2px;">{{ number_format($totalActiveUsd, 2) }} $</div>
        @endif
        @if($totalActiveSyp == 0 && $totalActiveUsd == 0)
        <div style="font-size:20px; font-weight:700; color:#94a3b8;">—</div>
        @endif
    </div>
    <div class="card" style="padding:20px; border-top:4px solid #10b981;">
        <div style="color:#94a3b8; font-size:12px; margin-bottom:6px;">ديون مسددة ✅</div>
        <div style="font-size:32px; font-weight:800; color:#10b981;">{{ $totalPaid }}</div>
        <div style="color:#94a3b8; font-size:12px; margin-top:2px;">تم السداد بالكامل</div>
    </div>
</div>

{{-- ===== ACTIVE DEBTS ===== --}}
@if($totalActive > 0)
<div class="card" style="overflow:hidden; margin-bottom:24px;">
    <div style="padding:18px 24px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:10px;">
        <div style="width:10px; height:10px; background:#ef4444; border-radius:50%;"></div>
        <h3 style="font-size:16px; font-weight:700; color:#0f172a; margin:0;">ديون لم تُسدَّد بعد</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>الزبون</th>
                <th>البيع</th>
                <th>التاريخ</th>
                <th>الإجمالي</th>
                <th style="color:#10b981;">دُفع</th>
                <th style="color:#ef4444;">متبقي</th>
                <th style="text-align:center;">إجراء</th>
            </tr>
        </thead>
        <tbody>
            {{-- Invoice debts --}}
            @foreach($activeSaleDebts->sortByDesc(fn($s) => $s->remaining()) as $s)
            @php $sdec = $s->currency === 'SYP' ? 0 : 2; $sdays = $s->created_at->diffInDays(now()); $sid = 'sd_'.$s->id; @endphp
            <tr style="background:#fffbeb; cursor:pointer;" onclick="toggleDetails('{{ $sid }}')">
                <td>
                    <div style="font-weight:800; color:#0f172a; font-size:15px;">{{ $s->customer_name ?: 'زبون نقدي' }}</div>
                    <div style="font-size:11px; color:#94a3b8; margin-top:2px;">اضغط لعرض التفاصيل ▾</div>
                </td>
                <td>
                    <div style="font-weight:600; color:#374151; font-size:13px;">🧾 {{ $s->invoiceNumber() }}</div>
                    <div style="color:#94a3b8; font-size:11px;">{{ $s->items->count() }} صنف — {{ $s->items->sum('qty') }} قطعة</div>
                </td>
                <td>
                    <div style="color:#64748b; font-size:13px;">{{ $s->created_at->format('d/m/Y') }}</div>
                    <div style="font-size:11px; font-weight:700; color:{{ $sdays > 30 ? '#dc2626' : ($sdays > 14 ? '#f97316' : '#94a3b8') }};">
                        منذ {{ $sdays }} يوم{{ $sdays > 30 ? ' ⚠️' : '' }}
                    </div>
                </td>
                <td>
                    <span style="font-weight:800; color:#0f172a; font-size:15px;">{{ number_format($s->totalAmount(), $sdec) }}</span>
                    <span style="color:#94a3b8; font-size:12px;"> {{ $s->currencySymbol() }}</span>
                </td>
                <td>
                    <span style="font-weight:700; color:#10b981; font-size:15px;">{{ number_format($s->totalPaid(), $sdec) }}</span>
                    <span style="color:#94a3b8; font-size:12px;"> {{ $s->currencySymbol() }}</span>
                </td>
                <td>
                    <span style="font-weight:800; color:#ef4444; font-size:15px;">{{ number_format($s->remaining(), $sdec) }}</span>
                    <span style="color:#94a3b8; font-size:12px;"> {{ $s->currencySymbol() }}</span>
                </td>
                <td style="text-align:center;" onclick="event.stopPropagation()">
                    <div style="display:flex; flex-direction:column; gap:6px; align-items:center;">
                        <button type="button"
                            onclick="openFullPayModal('sale','{{ route('sales.show', $s) }}','{{ number_format($s->remaining(), $sdec, '.', '') }}','{{ $s->currency }}','{{ addslashes($s->customer_name ?? 'الزبون') }}')"
                            style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; padding:7px 14px; border-radius:8px; font-size:12px; font-weight:700; border:none; cursor:pointer; white-space:nowrap; font-family:Cairo,sans-serif;">
                            ✅ استلمت الكامل
                        </button>
                        <a href="{{ route('sales.show', $s) }}"
                           style="background:#fef3c7; color:#92400e; padding:5px 14px; border-radius:8px; font-size:12px; font-weight:700; text-decoration:none;">💳 دفعة جزئية</a>
                    </div>
                </td>
            </tr>
            {{-- Details row: payment history for invoice debt --}}
            <tr id="{{ $sid }}" style="display:none; background:#fefce8;">
                <td colspan="7" style="padding:0;">
                    <div style="padding:14px 20px;">
                        <div style="font-size:12px; font-weight:800; color:#92400e; margin-bottom:10px;">📋 تفاصيل الدفعات — {{ $s->customer_name ?: 'زبون' }}</div>
                        {{-- Items --}}
                        <div style="margin-bottom:10px; background:#fff; border-radius:8px; padding:10px 14px; font-size:12px;">
                            <div style="font-weight:700; color:#374151; margin-bottom:6px;">🛒 المنتجات:</div>
                            @foreach($s->items as $item)
                            <div style="display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px solid #f1f5f9; color:#64748b;">
                                <span>{{ $item->product->name ?? '—' }} × @qty($item->qty)</span>
                                <span style="font-weight:700; color:#0f172a;">{{ number_format($item->price * $item->qty, $sdec) }} {{ $s->currencySymbol() }}</span>
                            </div>
                            @endforeach
                        </div>
                        {{-- Payment timeline --}}
                        <div style="background:#fff; border-radius:8px; padding:10px 14px; font-size:12px;">
                            <div style="font-weight:700; color:#374151; margin-bottom:6px;">💰 سجل الدفعات:</div>
                            @php
                                $spList   = $s->salePayments->sortBy('created_at');
                                $initDown = (float)$s->amount_paid - $spList->sum(fn($p) => $p->amountInSaleCurrency($s));
                            @endphp
                            {{-- Initial down payment (if any) --}}
                            @if($initDown > 0.001)
                            <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid #f1f5f9;">
                                <span style="color:#64748b;">🟡 دفعة أولى ({{ $s->created_at->format('d/m/Y') }})</span>
                                <span style="font-weight:700; color:#10b981;">{{ number_format($initDown, $sdec) }} {{ $s->currencySymbol() }}</span>
                            </div>
                            @endif
                            @if($s->note)
                            <div style="padding:3px 0; color:#94a3b8; font-size:11px;">ملاحظة: {{ $s->note }}</div>
                            @endif
                            {{-- Follow-up payments --}}
                            @foreach($spList as $sp)
                            @php $spSym = $sp->pay_currency === 'SYP' ? 'ل.س' : '$'; $spDec = $sp->pay_currency === 'SYP' ? 0 : 2; @endphp
                            <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid #f1f5f9;">
                                <span style="color:#64748b;">💵 {{ $sp->created_at->format('d/m/Y') }}{{ $sp->note ? ' — '.$sp->note : '' }}</span>
                                <span style="font-weight:700; color:#2563eb;">{{ number_format($sp->amount, $spDec) }} {{ $spSym }}</span>
                            </div>
                            @endforeach
                            <div style="display:flex; justify-content:space-between; padding:6px 0; margin-top:4px; border-top:2px solid #e2e8f0; font-weight:800;">
                                <span style="color:#16a34a;">✅ إجمالي المدفوع</span>
                                <span style="color:#16a34a;">{{ number_format($s->totalPaid(), $sdec) }} {{ $s->currencySymbol() }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:4px 0; font-weight:800;">
                                <span style="color:#dc2626;">⏳ المتبقي</span>
                                <span style="color:#dc2626;">{{ number_format($s->remaining(), $sdec) }} {{ $s->currencySymbol() }}</span>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach

            {{-- Legacy single-item debts --}}
            @foreach($activeMovementDebts->sortByDesc(fn($m) => $m->remaining()) as $m)
            @php $mdec = $m->currency === 'SYP' ? 0 : 2; $mdays = $m->created_at->diffInDays(now()); $mid = 'md_'.$m->id; @endphp
            <tr style="cursor:pointer;" onclick="toggleDetails('{{ $mid }}')">
                <td>
                    <div style="font-weight:800; color:#0f172a; font-size:15px;">{{ $m->customer_name ?? 'غير محدد' }}</div>
                    <div style="font-size:11px; color:#94a3b8; margin-top:2px;">اضغط لعرض التفاصيل ▾</div>
                </td>
                <td>
                    <div style="font-weight:600; color:#374151; font-size:13px;">{{ ($m->product ? $m->product->name : '—') }}</div>
                    <div style="color:#94a3b8; font-size:11px;">@qty($m->qty) {{ $m->product->unit ?? 'قطعة' }}</div>
                </td>
                <td>
                    <div style="color:#64748b; font-size:13px;">{{ $m->created_at->format('d/m/Y') }}</div>
                    <div style="font-size:11px; font-weight:700; color:{{ $mdays > 30 ? '#dc2626' : ($mdays > 14 ? '#f97316' : '#94a3b8') }};">
                        منذ {{ $mdays }} يوم{{ $mdays > 30 ? ' ⚠️' : '' }}
                    </div>
                </td>
                <td>
                    <span style="font-weight:800; color:#0f172a; font-size:15px;">{{ number_format($m->totalAmount(), $mdec) }}</span>
                    <span style="color:#94a3b8; font-size:12px;"> {{ $m->currencySymbol() }}</span>
                </td>
                <td>
                    <span style="font-weight:700; color:#10b981; font-size:15px;">{{ number_format($m->totalPaid(), $mdec) }}</span>
                    <span style="color:#94a3b8; font-size:12px;"> {{ $m->currencySymbol() }}</span>
                </td>
                <td>
                    <span style="font-weight:800; color:#ef4444; font-size:15px;">{{ number_format($m->remaining(), $mdec) }}</span>
                    <span style="color:#94a3b8; font-size:12px;"> {{ $m->currencySymbol() }}</span>
                </td>
                <td style="text-align:center;" onclick="event.stopPropagation()">
                    <div style="display:flex; flex-direction:column; gap:6px; align-items:center;">
                        <button type="button"
                            onclick="openFullPayModal('movement','{{ route('debts.show', $m) }}','{{ number_format($m->remaining(), $mdec, '.', '') }}','{{ $m->currency }}','{{ addslashes($m->customer_name ?? 'الزبون') }}')"
                            style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; padding:7px 14px; border-radius:8px; font-size:12px; font-weight:700; border:none; cursor:pointer; white-space:nowrap; font-family:Cairo,sans-serif;">
                            ✅ استلمت الكامل
                        </button>
                        <a href="{{ route('debts.show', $m) }}"
                           style="background:#eff6ff; color:#3b82f6; padding:5px 14px; border-radius:8px; font-size:12px; font-weight:700; text-decoration:none;">💳 دفعة جزئية</a>
                    </div>
                </td>
            </tr>
            {{-- Details row: payment history for legacy debt --}}
            <tr id="{{ $mid }}" style="display:none; background:#f0f9ff;">
                <td colspan="7" style="padding:0;">
                    <div style="padding:14px 20px;">
                        <div style="font-size:12px; font-weight:800; color:#1e40af; margin-bottom:10px;">📋 تفاصيل الدفعات — {{ $m->customer_name ?? 'غير محدد' }}</div>
                        <div style="background:#fff; border-radius:8px; padding:10px 14px; font-size:12px;">
                            <div style="font-weight:700; color:#374151; margin-bottom:6px;">💰 سجل الدفعات:</div>
                            <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid #f1f5f9;">
                                <span style="color:#64748b;">🟡 دفعة أولى ({{ $m->created_at->format('d/m/Y H:i') }})</span>
                                <span style="font-weight:700; color:#10b981;">{{ number_format($m->amount_paid ?? 0, $mdec) }} {{ $m->currencySymbol() }}</span>
                            </div>
                            @foreach($m->debtPayments->sortBy('created_at') as $dp)
                            <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid #f1f5f9;">
                                <span style="color:#64748b;">💚 دفعة ({{ $dp->created_at->format('d/m/Y H:i') }}){{ $dp->note ? ' — '.$dp->note : '' }}</span>
                                <span style="font-weight:700; color:#10b981;">{{ number_format($dp->amount, $mdec) }} {{ $m->currencySymbol() }}</span>
                            </div>
                            @endforeach
                            <div style="display:flex; justify-content:space-between; padding:6px 0; margin-top:4px; border-top:2px solid #e2e8f0; font-weight:800;">
                                <span style="color:#16a34a;">✅ إجمالي المدفوع</span>
                                <span style="color:#16a34a;">{{ number_format($m->totalPaid(), $mdec) }} {{ $m->currencySymbol() }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:4px 0; font-weight:800;">
                                <span style="color:#dc2626;">⏳ المتبقي</span>
                                <span style="color:#dc2626;">{{ number_format($m->remaining(), $mdec) }} {{ $m->currencySymbol() }}</span>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="card" style="padding:48px; text-align:center; margin-bottom:24px;">
    <div style="font-size:56px; margin-bottom:12px;">✅</div>
    <div style="font-size:20px; font-weight:700; color:#0f172a; margin-bottom:6px;">لا توجد ديون مفتوحة</div>
    <div style="color:#94a3b8; font-size:14px;">كل المبيعات مسددة</div>
</div>
@endif

{{-- ===== PAID DEBTS ===== --}}
@if($totalPaid > 0)
<div class="card" style="overflow:hidden;">
    <div style="padding:18px 24px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; gap:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:10px; height:10px; background:#10b981; border-radius:50%;"></div>
            <h3 style="font-size:16px; font-weight:700; color:#0f172a; margin:0;">ديون مسددة بالكامل ({{ $totalPaid }})</h3>
        </div>
        <button onclick="document.getElementById('paidSection').classList.toggle('hidden-paid')"
                style="background:#f1f5f9; border:none; border-radius:8px; padding:6px 14px; font-size:13px; font-weight:600; color:#64748b; cursor:pointer; font-family:Cairo,sans-serif;">
            عرض / إخفاء
        </button>
    </div>
    <style>.hidden-paid { display:none !important; }</style>
    <div id="paidSection" class="hidden-paid">
    <table>
        <thead>
            <tr>
                <th>الزبون</th>
                <th>البيع</th>
                <th>التاريخ</th>
                <th>الإجمالي</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paidSaleDebts as $s)
            <tr style="opacity:0.7;">
                <td style="font-weight:700; color:#374151;">{{ $s->customer_name ?: 'زبون نقدي' }}</td>
                <td>
                    <a href="{{ route('sales.show', $s) }}" style="color:#3b82f6; text-decoration:none; font-weight:600; font-size:13px;">
                        🧾 {{ $s->invoiceNumber() }}
                    </a>
                    <div style="color:#94a3b8; font-size:11px;">{{ $s->items->count() }} صنف</div>
                </td>
                <td style="color:#94a3b8; font-size:12px;">{{ $s->created_at->format('d/m/Y') }}</td>
                <td style="font-weight:700; color:#374151;">{{ number_format($s->totalAmount(), $s->currency === 'SYP' ? 0 : 2) }} {{ $s->currencySymbol() }}</td>
                <td><span style="background:#dcfce7; color:#16a34a; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700;">✅ مسدد</span></td>
            </tr>
            @endforeach

            @foreach($paidMovementDebts as $m)
            <tr style="opacity:0.7;">
                <td style="font-weight:700; color:#374151;">{{ $m->customer_name ?? 'غير محدد' }}</td>
                <td style="color:#64748b; font-size:13px;">{{ $m->product->name ?? '(محذوف)' }}</td>
                <td style="color:#94a3b8; font-size:12px;">{{ $m->created_at->format('d/m/Y') }}</td>
                <td style="font-weight:700; color:#374151;">{{ number_format($m->totalAmount(), $m->currency === 'SYP' ? 0 : 2) }} {{ $m->currencySymbol() }}</td>
                <td><span style="background:#dcfce7; color:#16a34a; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700;">✅ مسدد</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>{{-- end paidSection --}}
</div>
@endif

{{-- Full Payment Modal --}}
<div id="fullPayOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:28px; width:380px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,0.3); font-family:Cairo,sans-serif; direction:rtl;">
        <div style="font-size:18px; font-weight:800; color:#0f172a; margin-bottom:6px;">✅ استلمت الكامل</div>
        <div style="font-size:13px; color:#64748b; margin-bottom:20px;">من: <strong id="fpCustomer"></strong> — المبلغ: <strong id="fpAmount"></strong></div>

        {{-- Currency choice --}}
        <div style="margin-bottom:16px;">
            <div style="font-size:13px; font-weight:700; color:#374151; margin-bottom:8px;">استلمت بأي عملة؟</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;" id="fpCurrBtns">
                <button type="button" id="fpBtnSame" onclick="fpSetCurr('same')"
                    style="padding:12px 8px; border:2px solid #10b981; border-radius:10px; background:#f0fdf4; color:#059669; font-weight:700; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;">
                    <span id="fpSameLbl">نفس عملة الدين</span>
                </button>
                <button type="button" id="fpBtnOther" onclick="fpSetCurr('other')"
                    style="padding:12px 8px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:700; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;">
                    <span id="fpOtherLbl">العملة الأخرى</span>
                </button>
            </div>
        </div>

        {{-- Exchange rate (when different currency) --}}
        <div id="fpRateBox" style="display:none; margin-bottom:16px; background:#fef9c3; border-radius:10px; padding:12px; border:1.5px solid #fde68a;">
            <label style="font-size:12px; font-weight:700; color:#92400e; display:block; margin-bottom:6px;">سعر الصرف (ل.س لكل $)</label>
            <input type="number" id="fpRate" value="{{ \App\Models\Setting::get('usd_rate', 14000) }}" min="1"
                   style="width:100%; border:none; background:transparent; font-size:18px; font-weight:800; color:#92400e; outline:none; font-family:Cairo,sans-serif; direction:ltr; text-align:right;">
        </div>

        <div style="display:flex; gap:10px; margin-top:4px;">
            <button type="button" onclick="fpConfirm()"
                style="flex:1; padding:12px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">
                ✅ تأكيد الاستلام
            </button>
            <button type="button" onclick="closeFpModal()"
                style="padding:12px 18px; background:#f1f5f9; color:#64748b; border:1.5px solid #e2e8f0; border-radius:10px; font-weight:600; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">
                إلغاء
            </button>
        </div>
    </div>
</div>

{{-- Hidden form submitted by JS --}}
<form id="fpForm" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="amount"        id="fpAmtInput">
    <input type="hidden" name="pay_currency"  id="fpCurrInput">
    <input type="hidden" name="exchange_rate" id="fpRateInput">
    <input type="hidden" name="note"          value="سداد كامل من صفحة الديون">
    <input type="hidden" name="_fullpay"      value="1">
</form>

<script>
var _fpType, _fpShowUrl, _fpAmount, _fpDebtCurr, _fpChosenCurr;

function openFullPayModal(type, showUrl, amount, debtCurr, customer) {
    _fpType      = type;
    _fpShowUrl   = showUrl;
    _fpAmount    = parseFloat(amount);
    _fpDebtCurr  = debtCurr;
    _fpChosenCurr = debtCurr;

    document.getElementById('fpCustomer').textContent = customer;
    var sym = debtCurr === 'SYP' ? 'ل.س' : '$';
    var dec = debtCurr === 'SYP' ? 0 : 2;
    document.getElementById('fpAmount').textContent   = _fpAmount.toLocaleString('en-US') + ' ' + sym;

    var sameLbl  = debtCurr === 'SYP' ? '🇸🇾 ليرة سورية' : '💵 دولار';
    var otherLbl = debtCurr === 'SYP' ? '💵 دولار'        : '🇸🇾 ليرة سورية';
    document.getElementById('fpSameLbl').textContent  = sameLbl;
    document.getElementById('fpOtherLbl').textContent = otherLbl;

    fpSetCurr('same');
    var overlay = document.getElementById('fullPayOverlay');
    overlay.style.display = 'flex';
}

function fpSetCurr(which) {
    var inactive = 'padding:12px 8px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:700; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;';
    var active   = 'padding:12px 8px; border:2px solid #10b981; border-radius:10px; background:#f0fdf4; color:#059669; font-weight:700; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif;';
    document.getElementById('fpBtnSame').style.cssText  = which === 'same'  ? active : inactive;
    document.getElementById('fpBtnOther').style.cssText = which === 'other' ? active : inactive;
    _fpChosenCurr = which === 'same' ? _fpDebtCurr : (_fpDebtCurr === 'SYP' ? 'USD' : 'SYP');
    document.getElementById('fpRateBox').style.display = (which === 'other') ? 'block' : 'none';
}

function closeFpModal() {
    document.getElementById('fullPayOverlay').style.display = 'none';
}

function fpConfirm() {
    var rate = parseFloat(document.getElementById('fpRate').value) || 14000;
    // Calculate the actual amount to send (in chosen currency matching remaining in debt currency)
    var amountToSend;
    if (_fpChosenCurr === _fpDebtCurr) {
        amountToSend = _fpAmount;
    } else if (_fpChosenCurr === 'USD' && _fpDebtCurr === 'SYP') {
        // paying USD, debt is SYP: send USD amount = remaining / rate
        amountToSend = Math.round(_fpAmount / rate * 100) / 100;
    } else {
        // paying SYP, debt is USD: send SYP amount = remaining * rate
        amountToSend = Math.round(_fpAmount * rate);
    }

    // For sale debts, redirect to show page with query params to auto-submit
    // For movement debts, post to debts.pay route
    var form = document.getElementById('fpForm');
    // Build the pay URL from show URL: replace /show path pattern
    var payUrl;
    if (_fpType === 'sale') {
        payUrl = _fpShowUrl.replace(/\/sales\/(\d+)$/, '/sales/$1/pay');
    } else {
        payUrl = _fpShowUrl.replace(/\/debts\/(\d+)$/, '/debts/$1/pay');
    }
    form.action = payUrl;
    document.getElementById('fpAmtInput').value  = amountToSend;
    document.getElementById('fpCurrInput').value = _fpChosenCurr;
    document.getElementById('fpRateInput').value = (_fpChosenCurr !== _fpDebtCurr) ? rate : '';
    form.submit();
}

function toggleDetails(id) {
    var row = document.getElementById(id);
    if (!row) return;
    row.style.display = row.style.display !== 'none' ? 'none' : 'table-row';
}

document.getElementById('fullPayOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeFpModal();
});
</script>
@endsection
