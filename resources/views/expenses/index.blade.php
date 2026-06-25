@extends('layouts.app')
@section('title', 'الدرج اليومي')

@section('header')
<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">🗃️ الدرج اليومي</h1>
        <p style="color:#64748b; font-size:14px; margin:4px 0 0;">مصاريف المحل + تسوية الدرج</p>
    </div>
    {{-- Date picker --}}
    <form method="GET" style="display:flex; gap:8px; align-items:center;">
        <input type="date" name="date" value="{{ $date }}" class="input-field" style="width:160px;"
               onchange="this.form.submit()">
        <a href="{{ route('expenses.index') }}" style="padding:10px 16px; background:#f1f5f9; color:#64748b; border-radius:10px; font-size:13px; font-weight:600; text-decoration:none;">اليوم</a>
    </form>
</div>
@endsection

@section('content')

@php $isToday = ($date === today()->toDateString()); @endphp

{{-- ══ تسوية الدرج ══ --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

    {{-- SYP Column --}}
    <div class="card" style="padding:0; overflow:hidden; border-top:5px solid #f59e0b;">
        <div style="padding:16px 20px; background:#fffbeb; border-bottom:1px solid #fde68a;">
            <div style="font-size:15px; font-weight:800; color:#92400e;">🇸🇾 تسوية الليرة السورية</div>
        </div>
        <div style="padding:18px 20px;">
            @php
                $netSyp = $openingSyp + $cashSalesSyp + $exchangeInSyp - $expensesSyp;
            @endphp
            <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f8fafc; border-radius:10px;">
                    <span style="font-size:13px; color:#64748b; font-weight:600;">🌅 رصيد الصباح</span>
                    <span style="font-size:18px; font-weight:800; color:#0f172a;">{{ number_format($openingSyp, 0) }} ل.س</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f0fdf4; border-radius:10px;">
                    <span style="font-size:13px; color:#64748b; font-weight:600;">💰 مبيعات نقد</span>
                    <span style="font-size:18px; font-weight:800; color:#16a34a;">+{{ number_format($cashSalesSyp, 0) }} ل.س</span>
                </div>
                @if($exchangeInSyp > 0)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f0f9ff; border-radius:10px;">
                    <span style="font-size:13px; color:#64748b; font-weight:600;">💱 تحويل عملة</span>
                    <span style="font-size:18px; font-weight:800; color:#0369a1;">+{{ number_format($exchangeInSyp, 0) }} ل.س</span>
                </div>
                @endif
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#fef2f2; border-radius:10px;">
                    <span style="font-size:13px; color:#64748b; font-weight:600;">🧾 مصاريف</span>
                    <span style="font-size:18px; font-weight:800; color:#ef4444;">−{{ number_format($expensesSyp, 0) }} ل.س</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 16px; background:{{ $netSyp >= 0 ? '#dcfce7' : '#fef2f2' }}; border-radius:10px; border:2px solid {{ $netSyp >= 0 ? '#86efac' : '#fca5a5' }};">
                    <span style="font-size:14px; font-weight:800; color:#0f172a;">🗃️ المفروض بالدرج</span>
                    <span style="font-size:22px; font-weight:900; color:{{ $netSyp >= 0 ? '#16a34a' : '#ef4444' }};">{{ number_format($netSyp, 0) }} ل.س</span>
                </div>
            </div>
        </div>
    </div>

    {{-- USD Column --}}
    <div class="card" style="padding:0; overflow:hidden; border-top:5px solid #3b82f6;">
        <div style="padding:16px 20px; background:#eff6ff; border-bottom:1px solid #bfdbfe;">
            <div style="font-size:15px; font-weight:800; color:#1e40af;">💵 تسوية الدولار</div>
        </div>
        <div style="padding:18px 20px;">
            @php $netUsd = $openingUsd + $cashSalesUsd + $exchangeInUsd - $expensesUsd; @endphp
            <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f8fafc; border-radius:10px;">
                    <span style="font-size:13px; color:#64748b; font-weight:600;">🌅 رصيد الصباح</span>
                    <span style="font-size:18px; font-weight:800; color:#0f172a;">{{ number_format($openingUsd, 2) }} $</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f0fdf4; border-radius:10px;">
                    <span style="font-size:13px; color:#64748b; font-weight:600;">💰 مبيعات نقد</span>
                    <span style="font-size:18px; font-weight:800; color:#16a34a;">+{{ number_format($cashSalesUsd, 2) }} $</span>
                </div>
                @if($exchangeInUsd > 0)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f0fdf4; border-radius:10px;">
                    <span style="font-size:13px; color:#64748b; font-weight:600;">💱 تحويل ليرة→دولار</span>
                    <span style="font-size:18px; font-weight:800; color:#16a34a;">+{{ number_format($exchangeInUsd, 2) }} $</span>
                </div>
                @endif
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#fef2f2; border-radius:10px;">
                    <span style="font-size:13px; color:#64748b; font-weight:600;">🧾 مصاريف</span>
                    <span style="font-size:18px; font-weight:800; color:#ef4444;">−{{ number_format($expensesUsd, 2) }} $</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 16px; background:{{ $netUsd >= 0 ? '#dcfce7' : '#fef2f2' }}; border-radius:10px; border:2px solid {{ $netUsd >= 0 ? '#86efac' : '#fca5a5' }};">
                    <span style="font-size:14px; font-weight:800; color:#0f172a;">🗃️ المفروض بالدرج</span>
                    <span style="font-size:22px; font-weight:900; color:{{ $netUsd >= 0 ? '#16a34a' : '#ef4444' }};">{{ number_format($netUsd, 2) }} $</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ تحويل عملة ══ --}}
<div class="card" style="padding:18px 22px; margin-bottom:20px; border-right:5px solid #0ea5e9;">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
        <span style="font-size:18px;">💱</span>
        <span style="font-size:15px; font-weight:800; color:#0f172a;">تحويل عملة — دولار → ليرة</span>
        <span style="font-size:12px; color:#64748b; font-weight:500;">يطلع من درج الدولار ويدخل لدرج الليرة</span>
    </div>
    <form action="{{ route('expenses.exchange') }}" method="POST" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">
        <div>
            <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:4px;">مبلغ الدولار $</div>
            <div style="display:flex; align-items:center; gap:6px; background:#eff6ff; border-radius:10px; padding:8px 12px; border:1.5px solid #bfdbfe;">
                <input type="number" name="usd_amount" min="0.01" step="0.01" placeholder="0.00"
                       class="input-field" style="width:110px; border:none; background:transparent; font-size:18px; font-weight:800; color:#1e40af; padding:0;"
                       oninput="calcExchange()">
                <span style="font-weight:700; color:#1e40af;">$</span>
            </div>
        </div>
        <div>
            <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:4px;">سعر الصرف</div>
            <div style="display:flex; align-items:center; gap:6px; background:#fef9c3; border-radius:10px; padding:8px 12px; border:1.5px solid #fde68a;">
                <input type="number" name="exchange_rate" min="1" step="1" placeholder="{{ \App\Models\Setting::get('usd_rate', 14000) }}"
                       value="{{ \App\Models\Setting::get('usd_rate', 14000) }}"
                       class="input-field" style="width:110px; border:none; background:transparent; font-size:18px; font-weight:800; color:#92400e; padding:0;"
                       oninput="calcExchange()">
                <span style="font-weight:700; color:#92400e;">ل.س/$</span>
            </div>
        </div>
        <div>
            <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:4px;">يدخل للدرج</div>
            <div style="background:#f0fdf4; border-radius:10px; padding:8px 14px; border:1.5px solid #86efac; min-width:140px; text-align:center;">
                <span style="font-size:18px; font-weight:800; color:#16a34a;" id="exchange_result">0 ل.س</span>
            </div>
        </div>
        <button type="submit" style="padding:10px 22px; background:linear-gradient(135deg,#0ea5e9,#0284c7); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">
            💱 تسجيل التحويل
        </button>
    </form>
</div>
<script>
function calcExchange() {
    const usd  = parseFloat(document.querySelector('input[name=usd_amount]').value) || 0;
    const rate = parseFloat(document.querySelector('input[name=exchange_rate]').value) || 0;
    const syp  = Math.round(usd * rate);
    document.getElementById('exchange_result').textContent = syp.toLocaleString('en-US',{useGrouping:false}) + ' ل.س';
}
function calcExchangeReverse() {
    const syp  = parseFloat(document.querySelector('input[name=syp_amount]').value) || 0;
    const rate = parseFloat(document.querySelector('input[name=rev_exchange_rate]').value) || 0;
    const usd  = rate > 0 ? (syp / rate).toFixed(2) : '0.00';
    document.getElementById('exchange_reverse_result').textContent = usd + ' $';
}
</script>

{{-- ══ تحويل عكسي: ليرة → دولار ══ --}}
<div class="card" style="padding:18px 22px; margin-bottom:20px; border-right:5px solid #f59e0b;">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
        <span style="font-size:18px;">💱</span>
        <span style="font-size:15px; font-weight:800; color:#0f172a;">تحويل عملة — ليرة → دولار</span>
        <span style="font-size:12px; color:#64748b; font-weight:500;">يطلع من درج الليرة ويدخل لدرج الدولار</span>
    </div>
    <form action="{{ route('expenses.exchange.reverse') }}" method="POST" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">
        <div>
            <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:4px;">مبلغ الليرة ل.س</div>
            <div style="display:flex; align-items:center; gap:6px; background:#fef9c3; border-radius:10px; padding:8px 12px; border:1.5px solid #fde68a;">
                <input type="number" name="syp_amount" min="1" step="1" placeholder="0"
                       class="input-field" style="width:130px; border:none; background:transparent; font-size:18px; font-weight:800; color:#92400e; padding:0;"
                       oninput="calcExchangeReverse()">
                <span style="font-weight:700; color:#92400e;">ل.س</span>
            </div>
        </div>
        <div>
            <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:4px;">سعر الصرف</div>
            <div style="display:flex; align-items:center; gap:6px; background:#fef9c3; border-radius:10px; padding:8px 12px; border:1.5px solid #fde68a;">
                <input type="number" name="rev_exchange_rate" min="1" step="1" placeholder="{{ \App\Models\Setting::get('usd_rate', 14000) }}"
                       value="{{ \App\Models\Setting::get('usd_rate', 14000) }}"
                       class="input-field" style="width:110px; border:none; background:transparent; font-size:18px; font-weight:800; color:#92400e; padding:0;"
                       oninput="calcExchangeReverse()">
                <span style="font-weight:700; color:#92400e;">ل.س/$</span>
            </div>
        </div>
        <div>
            <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:4px;">يدخل للدرج</div>
            <div style="background:#eff6ff; border-radius:10px; padding:8px 14px; border:1.5px solid #bfdbfe; min-width:120px; text-align:center;">
                <span style="font-size:18px; font-weight:800; color:#1e40af;" id="exchange_reverse_result">0.00 $</span>
            </div>
        </div>
        <button type="submit" style="padding:10px 22px; background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">
            💱 تسجيل التحويل
        </button>
    </form>
</div>

{{-- ══ رصيد افتتاح ══ --}}
<div class="card" style="padding:18px 22px; margin-bottom:20px; border-right:5px solid #8b5cf6;">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
        <span style="font-size:18px;">🌅</span>
        <span style="font-size:15px; font-weight:800; color:#0f172a;">رصيد افتتاح الدرج — {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
    </div>
    <form action="{{ route('expenses.opening') }}" method="POST" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">
        <div style="display:flex; align-items:center; gap:8px; background:#faf5ff; border-radius:10px; padding:10px 14px; border:1.5px solid #e9d5ff;">
            <input type="number" name="opening_syp" value="{{ $openingSyp }}" min="0" step="1" placeholder="0"
                   class="input-field" style="width:140px; border:none; background:transparent; font-size:16px; font-weight:800; color:#7c3aed; padding:0;">
            <span style="font-weight:700; color:#7c3aed;">ل.س</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px; background:#eff6ff; border-radius:10px; padding:10px 14px; border:1.5px solid #bfdbfe;">
            <input type="number" name="opening_usd" value="{{ $openingUsd }}" min="0" step="0.01" placeholder="0"
                   class="input-field" style="width:120px; border:none; background:transparent; font-size:16px; font-weight:800; color:#1e40af; padding:0;">
            <span style="font-weight:700; color:#1e40af;">$</span>
        </div>
        <button type="submit" style="padding:10px 22px; background:linear-gradient(135deg,#8b5cf6,#7c3aed); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">
            💾 حفظ الافتتاح
        </button>
        <span style="color:#94a3b8; font-size:12px;">كم كان في الدرج عند بداية اليوم؟</span>
    </form>
</div>

{{-- ══ إضافة مصروف ══ --}}
<div style="display:grid; grid-template-columns:1fr 2fr; gap:20px; margin-bottom:20px; align-items:start;">

    <div class="card" style="padding:22px;">
        <h3 style="font-size:15px; font-weight:700; color:#0f172a; margin:0 0 16px;">➕ تسجيل مصروف</h3>
        <form action="{{ route('expenses.store') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">التصنيف</label>
                <select name="category" class="input-field" style="font-size:14px;">
                    @foreach(['مستلزمات محل','سكر وقهوة','كهرباء','إيجار','رواتب','صيانة','نقل','طعام','أخرى'] as $cat)
                        <option value="{{ $cat }}" {{ old('category')===$cat ? 'selected':'' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:grid; grid-template-columns:2fr 1fr; gap:10px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">المبلغ</label>
                    <input type="number" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01"
                           placeholder="0" class="input-field" style="font-size:15px; font-weight:700;">
                    @error('amount') <div style="color:#ef4444; font-size:11px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">العملة</label>
                    <select name="currency" class="input-field" style="font-size:14px;">
                        <option value="SYP" {{ old('currency','SYP')==='SYP'?'selected':'' }}>ل.س</option>
                        <option value="USD" {{ old('currency')==='USD'?'selected':'' }}>$</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">ملاحظة (اختياري)</label>
                <input type="text" name="note" value="{{ old('note') }}" placeholder="مثال: علبة سكر للمحل"
                       class="input-field" style="font-size:13px;">
            </div>

            <button type="submit" style="width:100%; padding:12px; background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">
                🧾 تسجيل المصروف
            </button>
        </form>
    </div>

    {{-- Expenses list for selected day --}}
    <div class="card" style="overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
            <h3 style="font-size:15px; font-weight:700; color:#0f172a; margin:0;">
                🧾 مصاريف {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
            </h3>
            <div style="display:flex; gap:8px; align-items:center;">
                <span style="background:#fef2f2; color:#ef4444; font-size:13px; font-weight:700; padding:3px 12px; border-radius:20px;">
                    {{ number_format($expensesSyp, 0) }} ل.س
                </span>
                @if($expensesUsd > 0)
                <span style="background:#fef2f2; color:#ef4444; font-size:13px; font-weight:700; padding:3px 12px; border-radius:20px;">
                    {{ number_format($expensesUsd, 2) }} $
                </span>
                @endif
            </div>
        </div>

        @if($expenses->isEmpty())
        <div style="padding:40px; text-align:center; color:#94a3b8;">
            <div style="font-size:36px; margin-bottom:8px;">✅</div>
            <div style="font-size:14px; font-weight:600;">لا توجد مصاريف مسجلة لهذا اليوم</div>
        </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>التصنيف</th>
                    <th>الملاحظة</th>
                    <th>المبلغ</th>
                    <th style="text-align:center;">حذف</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $exp)
                <tr>
                    <td>
                        <span style="background:{{ $exp->type === 'exchange_in' ? '#e0f2fe' : '#fef3c7' }}; color:{{ $exp->type === 'exchange_in' ? '#0369a1' : '#92400e' }}; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;">
                            {{ $exp->type === 'exchange_in' ? '💱' : '' }} {{ $exp->category }}
                        </span>
                    </td>
                    <td style="color:#64748b; font-size:13px;">{{ $exp->note ?? '—' }}</td>
                    <td>
                        <span style="font-weight:800; color:{{ $exp->type === 'exchange_in' ? '#16a34a' : '#ef4444' }}; font-size:15px;">
                            {{ $exp->type === 'exchange_in' ? '+' : '−' }}{{ number_format($exp->amount, $exp->currency === 'SYP' ? 0 : 2) }}
                        </span>
                        <span style="color:#94a3b8; font-size:12px;"> {{ $exp->currencySymbol() }}</span>
                        <div style="font-size:11px; color:#94a3b8;">{{ $exp->created_at->format('H:i') }}</div>
                    </td>
                    <td style="text-align:center;">
                        <form action="{{ route('expenses.destroy', $exp) }}" method="POST"
                              onsubmit="return confirm('حذف هذا المصروف؟')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:#fef2f2; color:#ef4444; border:1.5px solid #fecaca; border-radius:8px; padding:5px 10px; font-size:13px; cursor:pointer; font-family:Cairo,sans-serif; font-weight:700;">🗑️</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- ══ سجل المصاريف السابقة ══ --}}
@if($recentExpenses->count() > 1)
<div class="card" style="overflow:hidden;">
    <div style="padding:14px 20px; border-bottom:1px solid #f1f5f9;">
        <h3 style="font-size:15px; font-weight:700; color:#0f172a; margin:0;">📅 سجل المصاريف السابقة</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>التصنيف</th>
                <th>الملاحظة</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentExpenses as $day => $dayExpenses)
                @if($day !== $date)
                    <tr style="background:#f8fafc;">
                        <td colspan="4" style="font-weight:700; color:#374151; font-size:13px; padding:10px 20px;">
                            📅 {{ \Carbon\Carbon::parse($day)->format('d/m/Y') }}
                            <span style="color:#ef4444; font-weight:800; margin-right:8px;">
                                {{ number_format($dayExpenses->where('currency','SYP')->sum('amount'), 0) }} ل.س
                            </span>
                            @if($dayExpenses->where('currency','USD')->sum('amount') > 0)
                                <span style="color:#ef4444; font-weight:800;">+ {{ number_format($dayExpenses->where('currency','USD')->sum('amount'), 2) }} $</span>
                            @endif
                        </td>
                    </tr>
                    @foreach($dayExpenses as $exp)
                    <tr style="opacity:0.8;">
                        <td style="color:#94a3b8; font-size:12px;">{{ $exp->date->format('d/m') }}</td>
                        <td><span style="background:#f1f5f9; color:#64748b; padding:2px 8px; border-radius:10px; font-size:11px;">{{ $exp->category }}</span></td>
                        <td style="color:#64748b; font-size:12px;">{{ $exp->note ?? '—' }}</td>
                        <td style="font-weight:700; color:#ef4444; font-size:13px;">{{ number_format($exp->amount, $exp->currency === 'SYP' ? 0 : 2) }} {{ $exp->currencySymbol() }}</td>
                    </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
