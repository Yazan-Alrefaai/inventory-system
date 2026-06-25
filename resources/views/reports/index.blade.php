@extends('layouts.app')
@section('title', 'التقارير والإحصائيات')

@section('header')
<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">📊 التقارير والإحصائيات</h1>
        <p style="color:#64748b; font-size:14px; margin:4px 0 0;">
            {{ $dateFrom->format('d/m/Y') }} — {{ $dateTo->format('d/m/Y') }}
        </p>
    </div>
    <div style="display:flex; align-items:center; gap:10px;">
        <a href="{{ route('reports.export', $exportParams) }}"
           style="padding:8px 18px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none; white-space:nowrap;">
            ⬇️ تصدير CSV
        </a>
        <div style="display:flex; align-items:center; gap:6px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:20px; padding:5px 12px; font-size:12px; color:#0369a1;">
            <span style="display:inline-block; width:7px; height:7px; background:#38bdf8; border-radius:50%; animation:pulse 2s infinite;"></span>
            يتحدث كل دقيقتين — <span id="_refresh_badge" style="font-weight:700;">120ث</span>
        </div>
    </div>
</div>
<style>@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }</style>
<script>window._autoRefreshSec = 120;</script>
@endsection

@section('content')

{{-- ══ فلاتر ══ --}}
<div class="card" style="padding:18px 22px; margin-bottom:20px;">
    {{-- Quick preset buttons --}}
    <div style="display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap;">
        @foreach(['today'=>'اليوم','week'=>'هذا الأسبوع','month'=>'هذا الشهر','year'=>'هذه السنة'] as $key=>$label)
        <a href="{{ route('reports.index', array_merge(request()->except(['from','to','preset']), ['preset'=>$key])) }}"
           style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; font-family:Cairo,sans-serif;
                  {{ $preset===$key ? 'background:#0f172a; color:#fff;' : 'background:#f1f5f9; color:#374151;' }}">
            {{ $label }}
        </a>
        @endforeach
        <a href="{{ route('reports.index') }}"
           style="padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; background:#fef2f2; color:#dc2626; font-family:Cairo,sans-serif;">
            × مسح
        </a>
    </div>
    {{-- Custom range + type --}}
    <form method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:6px;">
            <label style="font-size:13px; color:#64748b; font-weight:600;">من</label>
            <input type="date" name="from" value="{{ $from ?? $dateFrom->format('Y-m-d') }}" class="input-field" style="width:150px;">
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
            <label style="font-size:13px; color:#64748b; font-weight:600;">إلى</label>
            <input type="date" name="to" value="{{ $to ?? $dateTo->format('Y-m-d') }}" class="input-field" style="width:150px;">
        </div>
        <select name="type" class="input-field" style="width:160px;">
            <option value="">الكل (بيع + شراء)</option>
            <option value="out" {{ $type==='out'?'selected':'' }}>⬆️ مبيعات فقط</option>
            <option value="in"  {{ $type==='in' ?'selected':'' }}>⬇️ مشتريات فقط</option>
        </select>
        <button type="submit" style="padding:10px 22px; background:#0f172a; color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">
            🔍 عرض
        </button>
    </form>
</div>

{{-- ══ بطاقات الملخص ══ --}}
<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:20px;">
    <div class="card" style="padding:20px; border-top:4px solid #f97316;">
        <div style="color:#94a3b8; font-size:11px; margin-bottom:6px;">💰 إجمالي المبيعات</div>
        <div style="font-size:20px; font-weight:900; color:#f97316;">{{ number_format($totalSalesUsd, 2) }} $</div>
        @if($totalSalesSyp > 0)
        <div style="font-size:13px; font-weight:700; color:#d97706; margin-top:2px;">{{ number_format($totalSalesSyp, 0) }} ل.س</div>
        @endif
        <div style="color:#94a3b8; font-size:11px; margin-top:4px;">{{ $totalItemsSold }} قطعة مباعة</div>
    </div>
    <div class="card" style="padding:20px; border-top:4px solid #10b981;">
        <div style="color:#94a3b8; font-size:11px; margin-bottom:6px;">🛒 إجمالي المشتريات</div>
        <div style="font-size:20px; font-weight:900; color:#10b981;">{{ number_format($totalPurchasesUsd, 2) }} $</div>
        <div style="color:#94a3b8; font-size:11px; margin-top:4px;">{{ $totalItemsBought }} قطعة مشتراة</div>
    </div>
    <div class="card" style="padding:20px; border-top:4px solid {{ $totalProfitUsd >= 0 ? '#22c55e' : '#ef4444' }};">
        <div style="color:#94a3b8; font-size:11px; margin-bottom:6px;">📈 صافي الربح</div>
        <div style="font-size:20px; font-weight:900; color:{{ $totalProfitUsd >= 0 ? '#16a34a' : '#ef4444' }};">
            {{ number_format($totalProfitUsd, 2) }} $
        </div>
        <div style="color:#94a3b8; font-size:11px; margin-top:4px;">
            هامش {{ $profitMarginPct }}%
            <span style="color:{{ $totalProfitUsd >= 0 ? '#16a34a' : '#ef4444' }}; font-weight:700;">
                {{ $totalProfitUsd >= 0 ? '▲' : '▼' }}
            </span>
        </div>
        <div style="margin-top:8px; padding-top:8px; border-top:1px solid #e2e8f0; font-size:11px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                <span style="color:#64748b;">💵 مقبوض (نقدي):</span>
                <span style="color:#16a34a; font-weight:700;">{{ number_format($cashProfitUsd, 2) }} $</span>
            </div>
            @if($creditProfitUsd != 0)
            <div style="display:flex; justify-content:space-between;">
                <span style="color:#64748b;">⏳ آجل غير مقبوض:</span>
                <span style="color:#f59e0b; font-weight:700;">{{ number_format($creditProfitUsd, 2) }} $</span>
            </div>
            @endif
        </div>
        @if($totalProfitSyp != 0 || $totalProfitUsdOnly != 0)
        <div style="margin-top:8px; padding-top:8px; border-top:1px solid #e2e8f0; font-size:11px;">
            <div style="color:#94a3b8; font-size:10px; margin-bottom:4px; font-weight:600;">تفصيل بالعملة:</div>
            @if($totalProfitSyp != 0)
            <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                <span style="color:#64748b;">🇸🇾 ربح مبيعات ل.س:</span>
                <span style="color:{{ $totalProfitSyp >= 0 ? '#16a34a' : '#ef4444' }}; font-weight:700;">{{ number_format($totalProfitSyp, 0) }} ل.س</span>
            </div>
            @endif
            @if($totalProfitUsdOnly != 0)
            <div style="display:flex; justify-content:space-between;">
                <span style="color:#64748b;">💵 ربح مبيعات $:</span>
                <span style="color:{{ $totalProfitUsdOnly >= 0 ? '#16a34a' : '#ef4444' }}; font-weight:700;">{{ number_format($totalProfitUsdOnly, 2) }} $</span>
            </div>
            @endif
        </div>
        @endif
    </div>
    <div class="card" style="padding:20px; border-top:4px solid #3b82f6;">
        <div style="color:#94a3b8; font-size:11px; margin-bottom:6px;">💵 نقد / دين</div>
        <div style="font-size:14px; font-weight:800; color:#16a34a; margin-bottom:4px;">{{ number_format($cashSales, 2) }} $ نقداً</div>
        <div style="font-size:14px; font-weight:800; color:#ef4444;">{{ number_format($creditSales, 2) }} $ دين</div>
    </div>
    <div class="card" style="padding:20px; border-top:4px solid #8b5cf6;">
        <div style="color:#94a3b8; font-size:11px; margin-bottom:6px;">📋 عدد العمليات</div>
        <div style="font-size:28px; font-weight:900; color:#8b5cf6;">{{ $totalTransactions }}</div>
        <div style="color:#94a3b8; font-size:11px; margin-top:4px;">عملية في هذه الفترة</div>
    </div>
</div>

{{-- ══ الرسم البياني + أكثر المنتجات مبيعاً ══ --}}
<div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:20px;">
    <div class="card" style="padding:22px;">
        <h3 style="font-size:15px; font-weight:700; color:#0f172a; margin:0 0 16px;">
            📈 مبيعات ومشتريات — {{ $chartMode==='month' ? 'شهري' : 'يومي' }}
        </h3>
        <canvas id="reportChart" height="100"></canvas>
    </div>
    <div class="card" style="padding:22px;">
        <h3 style="font-size:15px; font-weight:700; color:#0f172a; margin:0 0 16px;">🏆 أكثر المنتجات مبيعاً</h3>
        @forelse($topProducts as $i => $p)
        <div style="display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f1f5f9;">
            <div style="width:24px; height:24px; background:{{ ['#f97316','#3b82f6','#10b981','#8b5cf6','#f43f5e'][$i] }}; color:#fff; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex-shrink:0;">{{ $i+1 }}</div>
            <div style="flex:1; min-width:0;">
                <div style="font-weight:700; color:#0f172a; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $p['name'] }}</div>
                <div style="color:#94a3b8; font-size:11px;">{{ $p['qty'] }} قطعة</div>
            </div>
            <div style="font-weight:800; color:#f97316; font-size:13px; white-space:nowrap;">{{ number_format($p['value'], 0) }} $</div>
        </div>
        @empty
        <div style="text-align:center; padding:30px; color:#94a3b8; font-size:13px;">لا توجد مبيعات</div>
        @endforelse
    </div>
</div>

{{-- ══ جدول التفاصيل ══ --}}
<div class="card" style="overflow:hidden;">
    <div style="padding:16px 22px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
        <h3 style="font-size:15px; font-weight:700; color:#0f172a; margin:0;">📋 تفاصيل الحركات</h3>
        <span style="background:#f1f5f9; color:#64748b; font-size:13px; font-weight:700; padding:4px 12px; border-radius:20px;">{{ $movements->count() }} سجل</span>
    </div>
    @if($movements->isEmpty())
    <div style="padding:60px; text-align:center; color:#94a3b8;">
        <div style="font-size:48px; margin-bottom:12px;">📭</div>
        <div style="font-size:16px; font-weight:600;">لا توجد حركات في هذه الفترة</div>
    </div>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:18%;">المنتج</th>
                <th style="width:9%;">النوع</th>
                <th style="width:8%;">الكمية</th>
                <th style="width:14%; background:#fffbeb;">سعر القطعة</th>
                <th style="width:14%; background:#fffbeb;">الإجمالي</th>
                <th style="width:12%;">الزبون / ملاحظة</th>
                <th style="width:9%;">الدفع</th>
                <th style="width:11%;">التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $i => $m)
            <tr>
                <td style="color:#94a3b8; font-size:12px;">{{ $i + 1 }}</td>
                <td>
                    <div style="font-weight:700; color:#0f172a; font-size:13px;">{{ $m->product->name ?? '(محذوف)' }}</div>
                    <div style="color:#94a3b8; font-size:11px;">{{ $m->product->category->name ?? '' }}</div>
                </td>
                <td>
                    @if($m->type === 'in')
                        <span style="background:#dcfce7; color:#16a34a; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">⬇️ وارد</span>
                    @else
                        <span style="background:#fef3c7; color:#b45309; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">⬆️ مبيع</span>
                    @endif
                </td>
                <td>
                    <span style="font-size:16px; font-weight:800; color:{{ $m->type==='in'?'#16a34a':'#d97706' }};">
                        {{ $m->type==='in'?'+':'-' }}@qty($m->qty)
                    </span>
                </td>
                <td style="background:#fffbeb;">
                    @if($m->price)
                        @if($m->currency==='SYP')
                            <span style="display:inline-block; background:#fef9c3; color:#92400e; font-size:10px; font-weight:700; padding:1px 5px; border-radius:4px; margin-bottom:2px;">ل.س</span><br>
                        @endif
                        <span style="font-weight:800; color:#b45309; font-size:14px;">{{ number_format($m->price, $m->currency === 'SYP' ? 0 : 2) }}</span>
                        <span style="color:#94a3b8; font-size:10px;"> {{ $m->currencySymbol() }}</span>
                    @else
                        <span style="color:#cbd5e1;">—</span>
                    @endif
                </td>
                <td style="background:#fffbeb;">
                    @if($m->price)
                        @if($m->currency==='SYP')
                            <span style="display:inline-block; background:#fef3c7; color:#92400e; font-size:10px; font-weight:700; padding:1px 5px; border-radius:10px; margin-bottom:2px;">🇸🇾 سوري</span><br>
                        @endif
                        <span style="font-weight:800; color:{{ $m->type==='in'?'#16a34a':'#ea580c' }}; font-size:14px;">{{ number_format($m->totalAmount(), $m->currency === 'SYP' ? 0 : 2) }}</span>
                        <span style="color:#94a3b8; font-size:10px;"> {{ $m->currencySymbol() }}</span>
                        @if($m->currency==='SYP' && $m->exchange_rate)
                            <div style="font-size:10px; color:#94a3b8;">≈ {{ number_format($m->totalAmountUsd(), 2) }} $</div>
                        @endif
                    @else
                        <span style="color:#cbd5e1;">—</span>
                    @endif
                </td>
                <td style="font-size:12px; color:#64748b; max-width:120px;">
                    @if($m->customer_name)
                        <div style="font-weight:600; color:#374151;">{{ $m->customer_name }}</div>
                    @endif
                    @if($m->note)
                        <div style="color:#94a3b8;">{{ $m->note }}</div>
                    @endif
                    @if(!$m->customer_name && !$m->note) — @endif
                </td>
                <td>
                    @if($m->type==='out')
                        @if($m->is_credit)
                            @if($m->isFullyPaid())
                                <span style="background:#dcfce7; color:#16a34a; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:700;">✅ مسدد</span>
                            @else
                                <span style="background:#fef2f2; color:#ef4444; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:700;">دين</span>
                                <div style="font-size:10px; color:#ef4444; margin-top:2px;">{{ number_format($m->remaining(), $m->currency === 'SYP' ? 0 : 2) }} {{ $m->currencySymbol() }}</div>
                            @endif
                        @else
                            <span style="background:#f0fdf4; color:#16a34a; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:700;">💵 نقد</span>
                        @endif
                    @else
                        <span style="color:#94a3b8; font-size:12px;">—</span>
                    @endif
                </td>
                <td>
                    <div style="font-size:13px; font-weight:600; color:#374151;">{{ $m->created_at->format('d/m/Y') }}</div>
                    <div style="font-size:11px; color:#94a3b8;">{{ $m->created_at->format('H:i') }}</div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{-- Footer totals --}}
    <div style="padding:14px 22px; border-top:2px solid #f1f5f9; display:flex; gap:24px; flex-wrap:wrap; background:#f8fafc;">
        <div style="font-size:13px; color:#374151;">
            <span style="color:#94a3b8;">إجمالي المبيعات:</span>
            <strong style="color:#f97316; margin-right:4px;">{{ number_format($totalSalesUsd, 2) }} $</strong>
            @if($totalSalesSyp > 0)
                <strong style="color:#d97706;">+ {{ number_format($totalSalesSyp, 0) }} ل.س</strong>
            @endif
        </div>
        <div style="font-size:13px; color:#374151;">
            <span style="color:#94a3b8;">إجمالي المشتريات:</span>
            <strong style="color:#10b981; margin-right:4px;">{{ number_format($totalPurchasesUsd, 2) }} $</strong>
        </div>
        <div style="font-size:13px; color:#374151;">
            <span style="color:#94a3b8;">صافي الربح:</span>
            <strong style="color:{{ $totalProfitUsd >= 0 ? '#16a34a' : '#ef4444' }}; margin-right:4px;">
                {{ number_format($totalProfitUsd, 2) }} $ ({{ $profitMarginPct }}%)
            </strong>
        </div>
    </div>
    @endif
</div>

@php
$chartLabelsJson = json_encode($chartLabels);
$chartSalesJson  = json_encode($chartSales);
$chartBuysJson   = json_encode($chartBuys);
@endphp
<script>
new Chart(document.getElementById('reportChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! $chartLabelsJson !!},
        datasets: [
            {
                label: 'مبيعات ($)',
                data: {!! $chartSalesJson !!},
                backgroundColor: 'rgba(249,115,22,0.75)',
                borderRadius: 5,
            },
            {
                label: 'مشتريات ($)',
                data: {!! $chartBuysJson !!},
                backgroundColor: 'rgba(16,185,129,0.65)',
                borderRadius: 5,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { labels: { font: { family:'Cairo', size:12 }, usePointStyle:true } },
            tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(2) + ' $' } }
        },
        scales: {
            x: { grid: { display:false }, ticks: { font: { family:'Cairo', size:11 } } },
            y: { beginAtZero:true, grid: { color:'#f1f5f9' }, ticks: { font: { family:'Cairo' }, callback: v => v+'$' } }
        }
    }
});
</script>
@endsection
