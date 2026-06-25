@extends('layouts.app')

@section('title', 'الفواتير')

@section('header')
<div style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">🧾 الفواتير</h1>
        <p style="color:#64748b; margin:4px 0 0; font-size:14px;">{{ $sales->total() }} فاتورة مسجلة</p>
    </div>
    <a href="{{ route('sales.create') }}" class="btn-primary">+ فاتورة جديدة</a>
</div>
@endsection

@section('content')

{{-- Filters --}}
<div class="card" style="padding:18px 20px; margin-bottom:20px;">
    <form method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <div style="position:relative; flex:1; min-width:180px;">
            <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#94a3b8;">🔍</span>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="ابحث باسم الزبون..." class="input-field" style="padding-right:36px;">
        </div>
        <input type="date" name="date" value="{{ request('date') }}" class="input-field" style="width:160px;">
        <select name="status" class="input-field" style="width:140px;">
            <option value="">كل الحالات</option>
            <option value="cash"   {{ request('status')==='cash'   ? 'selected':'' }}>✅ نقدي</option>
            <option value="credit" {{ request('status')==='credit' ? 'selected':'' }}>💳 آجل</option>
        </select>
        <button type="submit" class="btn-primary" style="padding:10px 20px;">بحث</button>
        @if(request()->hasAny(['search','date','status']))
            <a href="{{ route('sales.index') }}"
               style="padding:10px 16px; border:1.5px solid #e2e8f0; border-radius:10px; color:#64748b; font-size:14px; font-weight:500; text-decoration:none; background:#fff;">× مسح</a>
        @endif
    </form>
</div>

@if($sales->isEmpty())
    <div class="card" style="padding:60px; text-align:center; color:#94a3b8;">
        <div style="font-size:48px; margin-bottom:16px;">🧾</div>
        <div style="font-size:18px; font-weight:600; margin-bottom:8px;">لا توجد فواتير</div>
        @if(request()->hasAny(['search','date','status']))
            <div style="font-size:14px; margin-bottom:20px;">جرّب تغيير الفلتر</div>
            <a href="{{ route('sales.index') }}" style="color:#3b82f6; font-weight:600;">← عرض كل الفواتير</a>
        @else
            <div style="font-size:14px; margin-bottom:24px;">ابدأ بإنشاء أول فاتورة للزبون</div>
            <a href="{{ route('sales.create') }}" class="btn-primary" style="display:inline-flex;">+ فاتورة جديدة</a>
        @endif
    </div>
@else

<div class="card" style="padding:0; overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th>رقم الفاتورة</th>
                <th>الزبون</th>
                <th style="text-align:center;">التاريخ</th>
                <th style="text-align:center;">الأصناف</th>
                <th style="text-align:center;">الإجمالي</th>
                <th style="text-align:center;">الحالة</th>
                <th style="text-align:center;">المتبقي</th>
                <th style="text-align:center;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            @php $sym = $sale->currencySymbol(); @endphp
            <tr>
                <td>
                    <a href="{{ route('sales.show', $sale) }}" style="font-weight:700; color:#2563eb; text-decoration:none;">
                        {{ $sale->invoiceNumber() }}
                    </a>
                </td>
                <td style="font-weight:600; color:#374151;">{{ $sale->customer_name ?: '—' }}</td>
                <td style="text-align:center; color:#64748b; font-size:13px;">{{ $sale->created_at->format('d/m/Y') }}</td>
                <td style="text-align:center; color:#64748b;">{{ $sale->items->count() }} صنف</td>
                <td style="text-align:center; font-weight:700; color:#2563eb;">
                    {{ number_format($sale->totalAmount(), $sale->currency === 'SYP' ? 0 : 2) }} {{ $sym }}
                </td>
                <td style="text-align:center;">
                    @if(!$sale->is_credit)
                        <span style="background:#dcfce7; color:#15803d; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;">✅ نقدي</span>
                    @elseif($sale->isFullyPaid())
                        <span style="background:#dcfce7; color:#15803d; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;">✅ مسدد</span>
                    @else
                        <span style="background:#fef3c7; color:#92400e; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;">💳 آجل</span>
                    @endif
                </td>
                <td style="text-align:center; font-weight:700; color:{{ $sale->remaining() > 0 ? '#ef4444' : '#94a3b8' }};">
                    @if($sale->remaining() > 0)
                        {{ number_format($sale->remaining(), $sale->currency === 'SYP' ? 0 : 2) }} {{ $sym }}
                    @else
                        —
                    @endif
                </td>
                <td style="text-align:center;">
                    <a href="{{ route('sales.show', $sale) }}"
                       style="background:#eff6ff; color:#2563eb; padding:5px 14px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none;">
                        عرض
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($sales->hasPages())
<div style="margin-top:20px; display:flex; justify-content:center;">
    {{ $sales->appends(request()->query())->links() }}
</div>
@endif

@endif

@endsection
