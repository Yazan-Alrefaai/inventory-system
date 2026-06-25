@extends('layouts.app')
@section('title', 'سجل الحركات')

@section('header')
<div style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">📋 سجل الحركات</h1>
        <p style="color:#64748b; font-size:14px; margin:4px 0 0;">كل عمليات البيع والشراء</p>
    </div>
    <div style="display:flex; align-items:center; gap:12px;">
        <div style="display:flex; align-items:center; gap:6px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:20px; padding:5px 12px; font-size:12px; color:#15803d;">
            <span style="display:inline-block; width:7px; height:7px; background:#22c55e; border-radius:50%; animation:pulse 2s infinite;"></span>
            يتحدث تلقائياً — <span id="_refresh_badge" style="font-weight:700;">60ث</span>
        </div>
        <a href="{{ route('stock.in') }}"  style="padding:10px 18px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none;">⬇️ إدخال</a>
        <a href="{{ route('stock.out') }}" style="padding:10px 18px; background:linear-gradient(135deg,#f97316,#ea580c); color:#fff; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none;">⬆️ بيع</a>
    </div>
</div>
<style>@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }</style>
<script>window._autoRefreshSec = 300;</script>
@endsection

@section('content')

{{-- Summary Cards --}}
@php
    $todayIn    = \App\Models\StockMovement::where('type','in')->whereDate('created_at',today())->sum('qty');
    $todayOut   = \App\Models\StockMovement::where('type','out')->whereDate('created_at',today())->sum('qty');
    $totalIn    = \App\Models\StockMovement::where('type','in')->sum('qty');
    $totalOut   = \App\Models\StockMovement::where('type','out')->sum('qty');
@endphp

<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px;">
    <div class="card" style="padding:20px; border-top:4px solid #10b981;">
        <div style="color:#94a3b8; font-size:12px; margin-bottom:6px;">وارد اليوم</div>
        <div style="font-size:28px; font-weight:800; color:#10b981;">{{ $todayIn }}</div>
        <div style="color:#94a3b8; font-size:11px; margin-top:2px;">قطعة أضيفت</div>
    </div>
    <div class="card" style="padding:20px; border-top:4px solid #f97316;">
        <div style="color:#94a3b8; font-size:12px; margin-bottom:6px;">صادر اليوم</div>
        <div style="font-size:28px; font-weight:800; color:#f97316;">{{ $todayOut }}</div>
        <div style="color:#94a3b8; font-size:11px; margin-top:2px;">قطعة بيعت</div>
    </div>
    <div class="card" style="padding:20px; border-top:4px solid #3b82f6; position:relative; overflow:hidden;">
        <div style="color:#94a3b8; font-size:12px; margin-bottom:6px;">مبيعات اليوم 💰</div>
        <div style="font-size:24px; font-weight:800; color:#3b82f6;">{{ number_format($todaySales, 2) }}</div>
        <div style="color:#94a3b8; font-size:11px; margin-top:2px;">دولار $</div>
    </div>
    <div class="card" style="padding:20px; border-top:4px solid #8b5cf6;">
        <div style="color:#94a3b8; font-size:12px; margin-bottom:6px;">
            {{ $isFiltered ? 'مبيعات الفترة المحددة 💰' : 'إجمالي المبيعات 💰' }}
        </div>
        <div style="font-size:24px; font-weight:800; color:#8b5cf6;">{{ number_format($totalSales, 2) }}</div>
        <div style="color:#94a3b8; font-size:11px; margin-top:2px;">$ — {{ $filterLabel }}</div>
    </div>
</div>

{{-- Chart --}}
<div class="card" style="padding:24px; margin-bottom:20px;">
    <h3 style="font-size:15px; font-weight:700; color:#0f172a; margin:0 0 20px;">حركة المخزون — آخر 14 يوم</h3>
    <canvas id="histChart" height="60"></canvas>
</div>

{{-- Filters --}}
<div class="card" style="padding:16px 20px; margin-bottom:16px;">
    <form method="GET" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
        <select name="type" class="input-field" style="width:170px;">
            <option value="">كل الحركات</option>
            <option value="in"  {{ request('type')==='in'  ? 'selected':'' }}>⬇️ وارد فقط</option>
            <option value="out" {{ request('type')==='out' ? 'selected':'' }}>⬆️ مبيعات فقط</option>
        </select>
        <input type="date" name="date" value="{{ request('date') }}" class="input-field" style="width:170px;">
        <button type="submit" style="padding:10px 20px; background:#0f172a; color:#fff; border:none; border-radius:10px; font-weight:600; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">فلتر</button>
        @if(request()->hasAny(['type','date']))
            <a href="{{ route('stock.history') }}" style="padding:10px 16px; border:1.5px solid #e2e8f0; border-radius:10px; color:#64748b; font-size:14px; font-weight:500; text-decoration:none; background:#fff;">× مسح</a>
        @endif
        <div style="margin-right:auto; background:#f8fafc; border-radius:8px; padding:6px 14px; color:#64748b; font-size:13px; font-weight:600;">
            {{ $movements->total() }} سجل
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card" style="overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th style="width:4%;">#</th>
                <th style="width:20%;">المنتج</th>
                <th style="width:9%;">النوع</th>
                <th style="width:9%;">الكمية</th>
                <th style="width:13%; background:#fffbeb;">سعر القطعة 💰</th>
                <th style="width:13%; background:#fffbeb;">الإجمالي 💰</th>
                <th style="width:14%;">ملاحظة</th>
                <th style="width:10%;">التاريخ</th>
                <th style="width:8%; text-align:center;">حذف</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $i => $m)
            <tr>
                <td style="color:#94a3b8; font-size:13px;">{{ $movements->firstItem() + $i }}</td>
                <td>
                    <div style="font-weight:700; color:#0f172a; font-size:14px;">{{ $m->product->name ?? '—' }}</div>
                    @if($m->product && $m->product->category)
                    <div style="font-size:11px; color:#64748b; margin-top:2px;">{{ $m->product->category->name }}</div>
                    @endif
                </td>
                <td>
                    @if($m->type === 'in')
                        <span style="background:#dcfce7; color:#16a34a; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700;">⬇️ وارد</span>
                    @else
                        <span style="background:#fef3c7; color:#b45309; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700;">⬆️ مبيع</span>
                    @endif
                </td>
                <td>
                    <span style="font-size:18px; font-weight:800; color:{{ $m->type==='in' ? '#16a34a' : '#d97706' }};">
                        {{ $m->type==='in' ? '+' : '-' }}@qty($m->qty)
                    </span>
                </td>
                {{-- Price per unit --}}
                <td style="background:#fffbeb;">
                    @if($m->price)
                        @if($m->currency === 'SYP')
                            <div style="display:inline-flex; align-items:center; gap:4px; background:#fef9c3; border:1px solid #fde68a; border-radius:6px; padding:2px 6px; margin-bottom:3px;">
                                <span style="font-size:10px; font-weight:800; color:#92400e;">ل.س</span>
                            </div><br>
                        @endif
                        <span style="font-size:16px; font-weight:800; color:#b45309;">{{ number_format($m->price, 0) }}</span>
                        <span style="color:#94a3b8; font-size:11px;"> {{ $m->currencySymbol() }}</span>
                        @if($m->currency === 'SYP' && $m->exchange_rate)
                            <div style="font-size:10px; color:#94a3b8; margin-top:1px;">≈ {{ number_format($m->price / $m->exchange_rate, 2) }} $</div>
                        @endif
                    @else
                        <span style="color:#cbd5e1; font-size:13px;">—</span>
                    @endif
                </td>
                {{-- Total --}}
                <td style="background:#fffbeb;">
                    @if($m->price)
                        @php $rowTotal = $m->price * $m->qty; @endphp
                        @if($m->currency === 'SYP')
                            <span style="display:inline-block; background:#fef3c7; color:#92400e; font-size:10px; font-weight:800; padding:2px 7px; border-radius:20px; margin-bottom:3px; border:1px solid #fde68a;">🇸🇾 سوري</span><br>
                        @endif
                        <span style="font-size:16px; font-weight:800; color:{{ $m->type==='in' ? '#16a34a' : '#ea580c' }};">
                            {{ number_format($rowTotal, 0) }}
                        </span>
                        <span style="color:#94a3b8; font-size:11px;"> {{ $m->currencySymbol() }}</span>
                        @if($m->currency === 'SYP' && $m->exchange_rate)
                            <div style="font-size:10px; color:#94a3b8; margin-top:1px;">≈ {{ number_format($rowTotal / $m->exchange_rate, 2) }} $</div>
                        @endif
                    @else
                        <span style="color:#cbd5e1; font-size:13px;">—</span>
                    @endif
                </td>
                <td style="color:#64748b; font-size:13px; max-width:180px;">
                    {{-- debt badge --}}
                    @if($m->type === 'out' && $m->is_credit)
                        @php
                            // For invoice movements, get remaining from the Sale (payments are in SalePayment/amount_paid)
                            if ($m->sale_id && $m->sale) {
                                $rem = $m->sale->remaining();
                                $mdec = $m->sale->currency === 'SYP' ? 0 : 2;
                                $debtUrl = route('sales.show', $m->sale_id);
                            } else {
                                $rem  = $m->remaining();
                                $mdec = $m->currency === 'SYP' ? 0 : 2;
                                $debtUrl = route('debts.show', $m);
                            }
                        @endphp
                        @if($rem > 0)
                            <a href="{{ $debtUrl }}" style="text-decoration:none;">
                                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:5px 9px; margin-bottom:5px;">
                                    <div style="font-size:10px; font-weight:700; color:#b91c1c; margin-bottom:2px;">💳 دين متبقٍ</div>
                                    <div style="font-size:14px; font-weight:800; color:#dc2626;">{{ number_format($rem, $mdec) }} {{ $m->currencySymbol() }}</div>
                                    <div style="font-size:10px; color:#94a3b8; margin-top:1px;">{{ $m->customer_name ?? 'زبون' }}</div>
                                </div>
                            </a>
                        @else
                            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:4px 9px; margin-bottom:5px; display:inline-block;">
                                <span style="font-size:11px; font-weight:700; color:#15803d;">✅ سُدِّد كاملاً</span>
                            </div>
                        @endif
                        {{-- دفعات جزئية --}}
                        @if($m->debtPayments->isNotEmpty())
                            <div style="margin-top:4px;">
                                @foreach($m->debtPayments->sortBy('created_at') as $dp)
                                @php
                                    $dpSym = $dp->pay_currency === 'SYP' ? 'ل.س' : '$';
                                    $dpDec = $dp->pay_currency === 'SYP' ? 0 : 2;
                                @endphp
                                <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:6px; padding:3px 7px; margin-bottom:3px; font-size:10px;">
                                    <span style="color:#0369a1; font-weight:700;">💵 {{ number_format($dp->amount, $dpDec) }} {{ $dpSym }}</span>
                                    <span style="color:#94a3b8; margin-right:4px;">{{ $dp->created_at->format('d/m H:i') }}</span>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                    <div style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $m->note ?? '—' }}</div>
                </td>
                <td>
                    <div style="font-size:13px; color:#374151; font-weight:600;">{{ $m->created_at->format('d/m/Y') }}</div>
                    <div style="font-size:11px; color:#94a3b8;">{{ $m->created_at->format('H:i') }}</div>
                </td>
                <td style="text-align:center;">
                    @if($m->sale_id)
                        <a href="{{ route('sales.show', $m->sale_id) }}" title="عرض الفاتورة"
                           style="background:#eff6ff; color:#3b82f6; border:1.5px solid #bfdbfe; border-radius:8px; padding:5px 10px; font-size:13px; font-family:Cairo,sans-serif; font-weight:700; text-decoration:none; display:inline-block;">
                            🧾
                        </a>
                    @else
                    <form action="{{ route('stock.destroy', $m) }}" method="POST"
                          onsubmit="return confirm('حذف هذه الحركة؟ سيتم إعادة الكمية للمخزون تلقائياً.')">
                        @csrf @method('DELETE')
                        <button type="submit" title="حذف وإعادة المخزون"
                                style="background:#fef2f2; color:#ef4444; border:1.5px solid #fecaca; border-radius:8px; padding:5px 10px; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif; font-weight:700;">
                            🗑️
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; padding:60px; color:#94a3b8;">
                    <div style="font-size:48px; margin-bottom:12px;">📭</div>
                    <div style="font-size:16px; font-weight:600;">لا توجد حركات</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($movements->hasPages())
    <div style="padding:16px 20px; border-top:1px solid #f1f5f9; display:flex; justify-content:center;">
        {{ $movements->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@php
    $days14  = collect(range(13,0))->map(fn($i) => now()->subDays($i)->format('Y-m-d'));
    $labels14 = $days14->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toJson();
    $in14    = $days14->map(fn($d) => \App\Models\StockMovement::where('type','in')->whereDate('created_at',$d)->sum('qty'))->toJson();
    $out14   = $days14->map(fn($d) => \App\Models\StockMovement::where('type','out')->whereDate('created_at',$d)->sum('qty'))->toJson();
    $sales14 = $days14->map(fn($d) => round(\App\Models\StockMovement::where('type','out')->whereNotNull('price')->whereDate('created_at',$d)->get()->sum(fn($m)=>$m->totalAmountUsd()), 2))->toJson();
@endphp

<script>
new Chart(document.getElementById('histChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! $labels14 !!},
        datasets: [
            { label: 'وارد (قطع)',  data: {!! $in14 !!},    backgroundColor: 'rgba(16,185,129,0.7)',  borderRadius: 6, yAxisID: 'y' },
            { label: 'مبيع (قطع)',  data: {!! $out14 !!},   backgroundColor: 'rgba(249,115,22,0.7)',  borderRadius: 6, yAxisID: 'y' },
            { label: 'مبيعات ($)',  data: {!! $sales14 !!}, backgroundColor: 'rgba(139,92,246,0.15)', borderRadius: 6, yAxisID: 'y2',
              type: 'line', borderColor: '#8b5cf6', borderWidth: 2.5, pointBackgroundColor: '#8b5cf6', tension: 0.4, fill: true }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { font: { family:'Cairo', size:12 }, usePointStyle:true } } },
        scales: {
            y:  { beginAtZero:true, position:'right', grid:{ color:'#f1f5f9' }, ticks:{ font:{family:'Cairo'} }, title:{ display:true, text:'قطع', font:{family:'Cairo',size:11} } },
            y2: { beginAtZero:true, position:'left',  grid:{ display:false }, ticks:{ font:{family:'Cairo'}, callback: v => v+'$' }, title:{ display:true, text:'$ مبيعات', font:{family:'Cairo',size:11} } },
            x:  { grid:{ display:false }, ticks:{ font:{family:'Cairo'} } }
        }
    }
});
</script>
@endsection
