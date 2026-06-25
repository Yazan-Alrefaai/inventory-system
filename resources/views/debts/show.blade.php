@extends('layouts.app')
@section('title', 'دين: ' . ($movement->customer_name ?? 'زبون'))

@section('header')
<div style="display:flex; align-items:center; gap:16px;">
    <a href="{{ route('debts.index') }}" style="width:38px; height:38px; background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:18px; color:#374151;">←</a>
    <div>
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">💳 {{ $movement->customer_name ?? 'زبون' }}</h1>
        <p style="color:#64748b; font-size:14px; margin:4px 0 0;">{{ $movement->product->name ?? '(محذوف)' }} — {{ $movement->created_at->format('d/m/Y') }}</p>
    </div>
</div>
@endsection

@section('content')
@php
    $sym       = $movement->currencySymbol();
    $total     = $movement->totalAmount();
    $paid      = $movement->totalPaid();
    $remaining = $movement->remaining();
    $pct       = $total > 0 ? min(100, round($paid / $total * 100)) : 0;
@endphp

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; max-width:900px;">

    {{-- Left: Details + Pay Form --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- Status Card --}}
        <div class="card" style="padding:28px;">
            <div style="text-align:center; margin-bottom:20px;">
                @if($movement->isFullyPaid())
                    <div style="font-size:52px; margin-bottom:8px;">✅</div>
                    <div style="font-size:18px; font-weight:800; color:#16a34a;">تم السداد بالكامل</div>
                @else
                    <div style="font-size:52px; margin-bottom:8px;">⏳</div>
                    <div style="font-size:18px; font-weight:800; color:#ef4444;">دين قائم</div>
                @endif
            </div>

            {{-- Progress Bar --}}
            <div style="margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; font-size:12px; color:#94a3b8; margin-bottom:6px;">
                    <span>نسبة السداد</span><span>{{ $pct }}%</span>
                </div>
                <div style="height:10px; background:#f1f5f9; border-radius:5px; overflow:hidden;">
                    <div style="height:100%; width:{{ $pct }}%; background:linear-gradient(90deg,#10b981,#059669); border-radius:5px; transition:width 0.5s;"></div>
                </div>
            </div>

            @php $ddec = $movement->currency === 'SYP' ? 0 : 2; @endphp
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; text-align:center;">
                <div style="background:#f8fafc; border-radius:10px; padding:14px;">
                    <div style="color:#94a3b8; font-size:11px; margin-bottom:4px;">الإجمالي</div>
                    <div style="font-size:18px; font-weight:800; color:#0f172a;">{{ number_format($total, $ddec) }}</div>
                    <div style="color:#94a3b8; font-size:11px;">{{ $sym }}</div>
                </div>
                <div style="background:#f0fdf4; border-radius:10px; padding:14px;">
                    <div style="color:#94a3b8; font-size:11px; margin-bottom:4px;">دفع</div>
                    <div style="font-size:18px; font-weight:800; color:#16a34a;">{{ number_format($paid, $ddec) }}</div>
                    <div style="color:#94a3b8; font-size:11px;">{{ $sym }}</div>
                </div>
                <div style="background:{{ $remaining > 0 ? '#fef2f2' : '#f0fdf4' }}; border-radius:10px; padding:14px;">
                    <div style="color:#94a3b8; font-size:11px; margin-bottom:4px;">متبقي</div>
                    <div style="font-size:18px; font-weight:800; color:{{ $remaining > 0 ? '#ef4444' : '#16a34a' }};">{{ number_format($remaining, $ddec) }}</div>
                    <div style="color:#94a3b8; font-size:11px;">{{ $sym }}</div>
                </div>
            </div>

            @if($movement->exchange_rate && $movement->currency === 'SYP')
            <div style="margin-top:14px; padding:10px 14px; background:#fef9c3; border-radius:8px; font-size:12px; color:#92400e; text-align:center;">
                سعر الصرف وقت البيع: 1$ = {{ number_format($movement->exchange_rate, 0) }} ل.س
                — يعادل {{ number_format($total / $movement->exchange_rate, 2) }} $
            </div>
            @endif
        </div>

        {{-- Pay Form --}}
        @if(!$movement->isFullyPaid())
        @php $usdRate = (int) \App\Models\Setting::get('usd_rate', 14000); @endphp
        <div class="card" style="padding:28px;">
            <h3 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 20px;">💵 تسجيل دفعة جديدة</h3>
            <form action="{{ route('debts.pay', $movement) }}" method="POST" id="payForm">
                @csrf

                {{-- Currency selector --}}
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-weight:600; color:#374151; font-size:13px; margin-bottom:8px;">عملة الدفع</label>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
                        <button type="button" id="btn_same" onclick="setPayCurrency('{{ $movement->currency }}')"
                            style="padding:10px 4px; border:2px solid #10b981; border-radius:10px; background:#f0fdf4; color:#059669; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;">
                            {{ $movement->currency === 'SYP' ? '🇸🇾 ليرة' : '💵 دولار' }} (أصل الدين)
                        </button>
                        <button type="button" id="btn_other" onclick="setPayCurrency('{{ $movement->currency === 'SYP' ? 'USD' : 'SYP' }}')"
                            style="padding:10px 4px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;">
                            {{ $movement->currency === 'SYP' ? '💵 دولار' : '🇸🇾 ليرة' }}
                        </button>
                        <button type="button" id="btn_mix_pay" onclick="setPayCurrency('MIX')"
                            style="padding:10px 4px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;">
                            💵+🇸🇾 مختلط
                        </button>
                    </div>
                    <input type="hidden" name="pay_currency" id="pay_currency" value="{{ $movement->currency }}">
                </div>

                {{-- Exchange rate (shown when currency differs) --}}
                <div id="rateBox" style="display:none; margin-bottom:14px; background:#fef9c3; border-radius:10px; padding:12px 14px; border:1.5px solid #fde68a;">
                    <label style="font-size:12px; font-weight:700; color:#92400e; display:block; margin-bottom:6px;">سعر الصرف (ل.س لكل $)</label>
                    <input type="number" name="exchange_rate" id="pay_rate" value="{{ $usdRate }}" min="1"
                           class="input-field" style="font-weight:700; color:#92400e; border:none; background:transparent; font-size:16px; padding:0;"
                           oninput="calcPayEquiv()">
                </div>

                {{-- Amount --}}
                <div id="amountBox" style="margin-bottom:14px;">
                    <label style="display:block; font-weight:600; color:#374151; font-size:13px; margin-bottom:6px;">
                        المبلغ — المتبقي: <span id="remainLabel">{{ number_format($remaining, $ddec) }} {{ $sym }}</span>
                    </label>
                    <input type="number" name="amount" id="payAmount"
                           value="{{ old('amount') }}"
                           min="0.01" step="0.01"
                           placeholder="{{ number_format($remaining, $ddec, '.', '') }}"
                           class="input-field" style="font-size:22px; font-weight:800; padding:12px; color:#10b981; border-color:#bbf7d0; background:#f0fdf4;"
                           oninput="calcPayEquiv()">
                    <div id="payEquiv" style="display:none; font-size:12px; color:#64748b; margin-top:4px; font-weight:600;"></div>
                </div>

                {{-- MIX: two fields --}}
                <div id="mixPayBox" style="display:none; margin-bottom:14px;">
                    <div style="background:#f0f9ff; border:2px solid #7dd3fc; border-radius:10px; padding:12px;">
                        <div style="font-size:12px; font-weight:800; color:#0369a1; margin-bottom:10px;">💵+🇸🇾 الدفع المختلط</div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
                            <div>
                                <div style="font-size:11px; font-weight:700; color:#1e40af; margin-bottom:4px;">دولار $</div>
                                <div style="display:flex; align-items:center; gap:4px; background:#eff6ff; border-radius:8px; padding:6px 10px; border:1.5px solid #bfdbfe;">
                                    <input type="number" id="mixPayUsd" value="0" min="0" step="0.01"
                                        oninput="calcMixPayEquiv()"
                                        style="flex:1; border:none; background:transparent; font-size:16px; font-weight:800; color:#2563eb; outline:none; width:0; font-family:Cairo,sans-serif; direction:ltr; text-align:left;">
                                    <span style="color:#2563eb; font-weight:700; font-size:13px;">$</span>
                                </div>
                            </div>
                            <div>
                                <div style="font-size:11px; font-weight:700; color:#92400e; margin-bottom:4px;">ليرة ل.س</div>
                                <div style="display:flex; align-items:center; gap:4px; background:#fef9c3; border-radius:8px; padding:6px 10px; border:1.5px solid #fde68a;">
                                    <input type="number" id="mixPaySyp" value="0" min="0" step="1"
                                        oninput="calcMixPayEquiv()"
                                        style="flex:1; border:none; background:transparent; font-size:16px; font-weight:800; color:#92400e; outline:none; width:0; font-family:Cairo,sans-serif; direction:ltr; text-align:left;">
                                    <span style="color:#92400e; font-weight:700; font-size:13px;">ل.س</span>
                                </div>
                            </div>
                        </div>
                        <div style="background:#fff; border-radius:8px; padding:8px 10px; font-size:12px; font-weight:700;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span style="color:#64748b;">مجموع المدفوع:</span>
                                <span id="mixPayTotal" style="color:#16a34a;">0 {{ $sym }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#64748b;">سيبقى:</span>
                                <span id="mixPayRemain" style="color:#dc2626;">{{ number_format($remaining, $ddec) }} {{ $sym }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-weight:600; color:#374151; font-size:13px; margin-bottom:6px;">ملاحظة</label>
                    <input type="text" name="note" value="{{ old('note') }}" class="input-field" placeholder="مثال: دفع جزء من الدين">
                </div>

                <button id="debtPayBtn" type="submit" style="width:100%; padding:12px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">
                    ✅ تسجيل الدفعة
                </button>
            </form>
        </div>

        <script>
        var REMAINING     = {{ $remaining }};
        var MOV_CURRENCY  = '{{ $movement->currency }}';
        var OTHER_CURR    = MOV_CURRENCY === 'SYP' ? 'USD' : 'SYP';
        var SYM           = '{{ $sym }}';
        var DDEC          = {{ $ddec }};
        var payMode       = MOV_CURRENCY; // 'SYP','USD','MIX'

        function setPayCurrency(c) {
            payMode = c;
            var btnSame  = document.getElementById('btn_same');
            var btnOther = document.getElementById('btn_other');
            var btnMix   = document.getElementById('btn_mix_pay');
            var inactive = 'padding:10px 4px; border:2px solid #e2e8f0; border-radius:10px; background:#fff; color:#64748b; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;';
            btnSame.style.cssText  = inactive;
            btnOther.style.cssText = inactive;
            btnMix.style.cssText   = inactive;

            document.getElementById('amountBox').style.display  = c === 'MIX' ? 'none' : 'block';
            document.getElementById('mixPayBox').style.display  = c === 'MIX' ? 'block' : 'none';
            document.getElementById('rateBox').style.display    = (c !== MOV_CURRENCY && c !== 'MIX') ? 'block' : 'none';

            if (c === MOV_CURRENCY) {
                btnSame.style.cssText = 'padding:10px 4px; border:2px solid #10b981; border-radius:10px; background:#f0fdf4; color:#059669; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;';
                document.getElementById('pay_currency').value = c;
            } else if (c === 'MIX') {
                btnMix.style.cssText = 'padding:10px 4px; border:2px solid #0ea5e9; border-radius:10px; background:#f0f9ff; color:#0369a1; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;';
                document.getElementById('pay_currency').value = MOV_CURRENCY;
                document.getElementById('rateBox').style.display = 'block';
            } else {
                btnOther.style.cssText = 'padding:10px 4px; border:2px solid #f59e0b; border-radius:10px; background:#fef9c3; color:#92400e; font-weight:700; font-size:12px; cursor:pointer; font-family:Cairo,sans-serif;';
                document.getElementById('pay_currency').value = c;
            }
            calcPayEquiv();
        }

        function getRate() {
            return parseFloat(document.getElementById('pay_rate').value) || 1;
        }

        function calcPayEquiv() {
            if (payMode === 'MIX') { calcMixPayEquiv(); return; }
            var amt   = parseFloat(document.getElementById('payAmount').value) || 0;
            var equiv = document.getElementById('payEquiv');
            if (payMode !== MOV_CURRENCY && amt > 0) {
                var rate = getRate();
                var inMov = MOV_CURRENCY === 'SYP'
                    ? Math.round(amt * rate)
                    : Math.round(amt / rate * 100) / 100;
                equiv.style.display = 'block';
                equiv.textContent   = '≈ ' + inMov.toLocaleString('en-US',{useGrouping:false}) + ' ' + SYM + ' بعملة الدين';
            } else {
                equiv.style.display = 'none';
            }
        }

        function calcMixPayEquiv() {
            var rate    = getRate();
            var paidUsd = Math.max(0, parseFloat(document.getElementById('mixPayUsd').value) || 0);
            var paidSyp = Math.max(0, parseFloat(document.getElementById('mixPaySyp').value) || 0);
            var inMov, totalPaid;
            if (MOV_CURRENCY === 'SYP') {
                totalPaid = Math.round(paidUsd * rate) + paidSyp;
                inMov     = totalPaid;
            } else {
                totalPaid = paidUsd + Math.round(paidSyp / rate * 100) / 100;
                inMov     = totalPaid;
            }
            var remain = Math.max(0, REMAINING - inMov);
            document.getElementById('mixPayTotal').textContent  = inMov.toLocaleString('en-US',{useGrouping:false}) + ' ' + SYM;
            document.getElementById('mixPayRemain').textContent = remain.toLocaleString('en-US',{useGrouping:false}) + ' ' + SYM + (remain === 0 ? ' ✅' : '');
            // Store combined amount in hidden field for submission
            document.getElementById('payAmount').value = inMov;
        }

        function fillFull() {
            if (payMode === 'MIX') {
                if (MOV_CURRENCY === 'SYP') {
                    document.getElementById('mixPaySyp').value = Math.round(REMAINING);
                    document.getElementById('mixPayUsd').value = 0;
                } else {
                    document.getElementById('mixPayUsd').value = REMAINING;
                    document.getElementById('mixPaySyp').value = 0;
                }
                calcMixPayEquiv();
            } else {
                document.getElementById('payAmount').value = REMAINING;
                calcPayEquiv();
            }
        }

        // On submit: for MIX mode, submit two separate requests (USD + SYP)
        document.getElementById('payForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('debtPayBtn');
            if (payMode === 'MIX') {
                e.preventDefault();
                var paidUsd = parseFloat(document.getElementById('mixPayUsd').value) || 0;
                var paidSyp = parseFloat(document.getElementById('mixPaySyp').value) || 0;
                if (paidUsd <= 0 && paidSyp <= 0) { alert('أدخل مبلغاً للدفع'); return; }
                var rate    = getRate();
                var noteEl  = document.querySelector('#payForm input[name=note]');
                var noteVal = noteEl ? noteEl.value.trim() : '';
                var parts   = [];
                if (paidUsd > 0) parts.push(paidUsd.toLocaleString('en-US',{useGrouping:false}) + ' $');
                if (paidSyp > 0) parts.push(paidSyp.toLocaleString('en-US',{useGrouping:false}) + ' ل.س');
                var mixNote = 'دفع مختلط: ' + parts.join(' + ') + (noteVal ? ' — ' + noteVal : '');

                var token  = document.querySelector('#payForm input[name=_token]').value;
                var action = document.getElementById('payForm').action;

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
                        runNext(idx + 1);
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
    </div>

    {{-- Right: Payment History --}}
    <div class="card" style="padding:24px; height:fit-content;">
        <h3 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 18px;">📋 سجل الدفعات</h3>

        {{-- Initial payment --}}
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:#f0fdf4; border-radius:10px; margin-bottom:8px; border-right:4px solid #10b981;">
            <div>
                <div style="font-weight:700; color:#0f172a; font-size:14px;">دفعة البيع الأولى</div>
                <div style="color:#94a3b8; font-size:12px;">{{ $movement->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div style="font-weight:800; color:#16a34a; font-size:16px;">
                {{ number_format($movement->amount_paid, $ddec) }} {{ $sym }}
            </div>
        </div>

        @if($movement->debtPayments->isEmpty() && $movement->amount_paid >= $total)
            <div style="text-align:center; padding:20px; color:#94a3b8; font-size:13px;">سدد الكامل دفعة واحدة</div>
        @endif

        @foreach($movement->debtPayments->sortByDesc('created_at') as $pay)
        @php
            $sameCurrency = $pay->pay_currency === $movement->currency;
            $paySym = $pay->pay_currency === 'SYP' ? 'ل.س' : '$';
            $payDec = $pay->pay_currency === 'SYP' ? 0 : 2;
        @endphp
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:#eff6ff; border-radius:10px; margin-bottom:8px; border-right:4px solid #3b82f6;">
            <div>
                <div style="font-weight:700; color:#0f172a; font-size:14px;">{{ $pay->note ?: 'دفعة' }}</div>
                <div style="color:#94a3b8; font-size:12px;">{{ $pay->created_at->format('d/m/Y H:i') }}</div>
                @if(!$sameCurrency)
                <div style="font-size:11px; color:#f97316; font-weight:600; margin-top:2px;">
                    دُفع بـ {{ $pay->pay_currency }} — يعادل {{ number_format($pay->amountInMovementCurrency($movement), $ddec) }} {{ $sym }}
                </div>
                @endif
            </div>
            <div style="text-align:left;">
                <div style="font-weight:800; color:#2563eb; font-size:16px;">
                    {{ number_format($pay->amount, $payDec) }} {{ $paySym }}
                </div>
                @if(!$sameCurrency)
                <div style="font-size:11px; color:#94a3b8;">= {{ number_format($pay->amountInMovementCurrency($movement), $ddec) }} {{ $sym }}</div>
                @endif
            </div>
        </div>
        @endforeach

        {{-- Total line --}}
        <div style="border-top:2px solid #f1f5f9; margin-top:12px; padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-weight:700; color:#374151; font-size:14px;">إجمالي ما دُفع</div>
            <div style="font-weight:800; color:#16a34a; font-size:18px;">{{ number_format($paid, $ddec) }} {{ $sym }}</div>
        </div>
        @if($remaining > 0)
        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:8px;">
            <div style="font-weight:700; color:#374151; font-size:14px;">المتبقي</div>
            <div style="font-weight:800; color:#ef4444; font-size:18px;">{{ number_format($remaining, $ddec) }} {{ $sym }}</div>
        </div>
        @endif
    </div>

</div>
@endsection
