@extends('layouts.app')
@section('title', 'بيع بضاعة')

@section('header')
<div style="display:flex; align-items:center; gap:16px;">
    <a href="{{ route('dashboard') }}" style="width:38px; height:38px; background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:18px; color:#374151;">←</a>
    <div>
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">🛒 تسجيل بيع</h1>
        <p style="color:#64748b; font-size:14px; margin:4px 0 0;">بيع نقداً أو بالدين — بالدولار أو الليرة السورية</p>
    </div>
</div>
@endsection

@section('content')
<div style="display:grid; grid-template-columns:1.3fr 0.7fr; gap:24px; max-width:1000px;">

    <div class="card" style="padding:32px;">
        <form action="{{ route('stock.out.store') }}" method="POST" id="saleForm">
            @csrf

            @if($errors->any())
            <div style="background:#fef2f2; border:1.5px solid #fecaca; border-radius:10px; padding:14px 18px; margin-bottom:20px;">
                <div style="font-weight:700; color:#dc2626; margin-bottom:6px;">⚠️ يرجى تصحيح الأخطاء:</div>
                <ul style="margin:0; padding-right:20px; color:#dc2626; font-size:13px;">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
            @endif

            {{-- ١ المنتج --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:700; color:#374151; font-size:14px; margin-bottom:8px;">
                    <span style="background:#f97316; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; margin-left:6px;">١</span>
                    المنتج *
                </label>
                {{-- Search filter --}}
                <div style="position:relative; margin-bottom:8px;">
                    <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:16px; pointer-events:none;">🔍</span>
                    <input type="text" id="out_search" autocomplete="off" placeholder="ابحث باسم المنتج..."
                           style="width:100%; padding:10px 40px 10px 12px; border:1.5px solid #fed7aa; border-radius:10px; font-size:14px; font-family:Cairo,sans-serif; background:#fff7ed; outline:none; box-sizing:border-box;"
                           oninput="filterOutSelect(this.value)">
                </div>
                <select name="product_id" id="product_select" class="input-field" onchange="onProduct(this)" required style="font-size:15px; padding:12px 14px;">
                    <option value="">— اختر المنتج —</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" data-qty="{{ $p->qty }}" data-unit="{{ $p->unit }}" data-price="{{ $p->price }}" data-sell-price="{{ $p->defaultSellPrice() }}" data-name="{{ strtolower($p->name) }}" {{ old('product_id')==$p->id?'selected':'' }}>
                            {{ $p->name }} — متاح: @qty($p->qty) {{ $p->unit }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- بطاقة معلومات المنتج --}}
            <div id="pinfo" style="display:none; background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:12px; padding:14px 18px; margin-bottom:20px;">
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; text-align:center;">
                    <div style="background:#fff; border-radius:8px; padding:10px;">
                        <div style="color:#94a3b8; font-size:11px; margin-bottom:2px;">المخزون المتاح</div>
                        <div style="font-size:20px; font-weight:800; color:#0f172a;" id="i_qty">—</div>
                        <div style="color:#64748b; font-size:12px;" id="i_unit"></div>
                    </div>
                    <div style="background:#fff; border-radius:8px; padding:10px;">
                        <div style="color:#94a3b8; font-size:11px; margin-bottom:2px;">🛒 سعر الشراء</div>
                        <div style="font-size:18px; font-weight:800; color:#64748b;" id="i_orig_price">—</div>
                        <div style="color:#94a3b8; font-size:11px;">$ دولار</div>
                    </div>
                    <div style="background:#f0fdf4; border-radius:8px; padding:10px; border:1px solid #bbf7d0;">
                        <div style="color:#94a3b8; font-size:11px; margin-bottom:2px;">🏷️ سعر البيع</div>
                        <div style="font-size:18px; font-weight:800; color:#16a34a;" id="i_sell_price">—</div>
                        <div style="color:#94a3b8; font-size:11px;">$ دولار</div>
                    </div>
                </div>
            </div>

            {{-- ٢ العملة --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:700; color:#374151; font-size:14px; margin-bottom:8px;">
                    <span style="background:#f97316; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; margin-left:6px;">٢</span>
                    عملة البيع
                </label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <label id="btn_usd" onclick="setCurrency('USD')" style="display:flex; align-items:center; justify-content:center; gap:8px; padding:12px; border:2px solid #3b82f6; border-radius:10px; cursor:pointer; background:#eff6ff; font-weight:700; color:#3b82f6; font-size:15px;">
                        <input type="radio" name="currency" value="USD" checked style="display:none;"> 💵 دولار $
                    </label>
                    <label id="btn_syp" onclick="setCurrency('SYP')" style="display:flex; align-items:center; justify-content:center; gap:8px; padding:12px; border:2px solid #e2e8f0; border-radius:10px; cursor:pointer; background:#fff; font-weight:700; color:#64748b; font-size:15px;">
                        <input type="radio" name="currency" value="SYP" style="display:none;"> 🇸🇾 ليرة سورية
                    </label>
                </div>
            </div>

            {{-- سعر الصرف (يظهر فقط عند SYP) --}}
            <div id="syp_rate_box" style="display:none; margin-bottom:20px; background:#fef9c3; border:1.5px solid #fde68a; border-radius:10px; padding:14px 16px;">
                <label style="display:block; font-weight:700; color:#92400e; font-size:13px; margin-bottom:8px;">سعر الصرف (ليرة لكل دولار واحد)</label>
                <input type="number" name="exchange_rate" id="exchange_rate" value="{{ old('exchange_rate', $usdRate) }}" min="1"
                       class="input-field" placeholder="مثال: 14000" oninput="autoFillSyp()" style="font-size:16px; font-weight:700; color:#92400e; border-color:#fde68a; background:#fff;">
                @php
                    $__rateAt  = \App\Models\Setting::get('usd_rate_updated_at');
                    $__rateMin = $__rateAt ? (int) now()->diffInMinutes(\Carbon\Carbon::parse($__rateAt)) : null;
                @endphp
                <div style="color:#92400e; font-size:12px; margin-top:6px;">
                    السعر المضبوط: <strong>1$ = {{ number_format($usdRate, 0) }} ل.س</strong> — يمكن تعديله هنا إذا تغيّر
                    <span style="margin-right:8px; color:{{ ($__rateMin === null || $__rateMin > 240) ? '#dc2626' : '#15803d' }}; font-weight:700;">
                        ({{ $__rateMin === null ? 'لم يُحدَّث بعد' : ($__rateMin < 60 ? 'آخر تحديث: منذ '.$__rateMin.' د' : 'آخر تحديث: منذ '.intdiv($__rateMin,60).' س') }})
                    </span>
                </div>
            </div>

            {{-- ٣ الكمية والسعر --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-weight:700; color:#374151; font-size:14px; margin-bottom:8px;">
                        <span style="background:#f97316; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; margin-left:6px;">٣</span>
                        الكمية *
                    </label>
                    <input type="number" name="qty" id="qty_input" value="{{ old('qty') }}" min="0.001" step="0.001"
                           inputmode="decimal" class="input-field" placeholder="مثال: 0.750" oninput="calc()" required
                           style="font-size:22px; font-weight:800; padding:12px 14px; text-align:center;">
                    <div id="qty_err" style="display:none; color:#dc2626; font-size:12px; font-weight:600; margin-top:5px; padding:6px 10px; background:#fef2f2; border-radius:8px;">⚠️ أكبر من المخزون!</div>
                </div>
                <div>
                    <label style="display:block; font-weight:700; color:#374151; font-size:14px; margin-bottom:8px;">
                        سعر القطعة * <span id="curr_label" style="font-weight:400; color:#94a3b8; font-size:12px;">($)</span>
                    </label>
                    <div style="position:relative;">
                        <input type="number" name="sale_price" id="price_input" value="{{ old('sale_price') }}" min="0" step="0.01"
                               class="input-field" placeholder="0.00" oninput="calc()" required
                               style="font-size:22px; font-weight:800; padding:12px 42px 12px 14px; color:#f97316; border-color:#fed7aa; background:#fff7ed; text-align:center;">
                        <span id="sym_label" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:16px; font-weight:800; color:#f97316;">$</span>
                    </div>
                </div>
            </div>

            {{-- إجمالي البيع --}}
            <div id="total_box" style="display:none; background:linear-gradient(135deg,#f97316,#ea580c); border-radius:12px; padding:14px 20px; margin-bottom:20px; align-items:center; justify-content:space-between;">
                <div style="color:rgba(255,255,255,0.8); font-size:14px;">إجمالي هذه الصفقة</div>
                <div style="color:#fff; font-size:28px; font-weight:800;" id="grand_total">0</div>
            </div>

            <hr style="border:none; border-top:1px solid #f1f5f9; margin:0 0 20px;">

            {{-- ٤ اسم الزبون --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:700; color:#374151; font-size:14px; margin-bottom:8px;">
                    <span style="background:#f97316; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; margin-left:6px;">٤</span>
                    اسم الزبون <span style="font-weight:400; color:#94a3b8; font-size:12px;">(مطلوب عند البيع بالدين)</span>
                </label>
                <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}"
                       class="input-field" placeholder="مثال: أبو أحمد الحلبي">
            </div>

            {{-- ٥ نوع الدفع --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:700; color:#374151; font-size:14px; margin-bottom:8px;">
                    <span style="background:#f97316; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; margin-left:6px;">٥</span>
                    طريقة الدفع
                </label>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                    <label id="btn_cash" onclick="setPayment('cash')" style="display:flex; align-items:center; justify-content:center; gap:8px; padding:14px; border:2px solid #10b981; border-radius:10px; cursor:pointer; background:#f0fdf4; font-weight:700; color:#059669; font-size:14px;">
                        <input type="radio" name="is_credit" value="0" checked style="display:none;"> ✅ نقداً — كامل
                    </label>
                    <label id="btn_debt" onclick="setPayment('debt')" style="display:flex; align-items:center; justify-content:center; gap:8px; padding:14px; border:2px solid #e2e8f0; border-radius:10px; cursor:pointer; background:#fff; font-weight:700; color:#64748b; font-size:14px;">
                        <input type="radio" name="is_credit" value="1" style="display:none;"> 💳 بالدين
                    </label>
                    <label id="btn_mix" onclick="setPayment('mix')" style="display:flex; align-items:center; justify-content:center; gap:8px; padding:14px; border:2px solid #e2e8f0; border-radius:10px; cursor:pointer; background:#fff; font-weight:700; color:#64748b; font-size:14px;">
                        <input type="radio" name="is_credit" value="1" style="display:none;"> 💵🇸🇾 مختلط
                    </label>
                </div>
            </div>

            {{-- حقل الدين: كم دفع؟ --}}
            <div id="debt_box" style="display:none; background:#fef2f2; border:1.5px solid #fecaca; border-radius:12px; padding:18px 20px; margin-bottom:20px;">
                <div style="font-weight:700; color:#dc2626; font-size:14px; margin-bottom:14px;">💳 تفاصيل الدفع الجزئي</div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-weight:600; color:#374151; font-size:13px; margin-bottom:6px;">كم دفع الزبون الآن؟ <span id="debt_sym">$</span></label>
                    <input type="number" name="amount_paid" id="amt_paid" value="{{ old('amount_paid', 0) }}" min="0" step="0.01"
                           class="input-field" placeholder="0" oninput="calcDebt()"
                           style="font-size:20px; font-weight:800; color:#dc2626; border-color:#fecaca; background:#fff;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
                    <div style="background:#fff; border-radius:8px; padding:12px; text-align:center;">
                        <div style="color:#94a3b8; font-size:11px;">دفع الآن</div>
                        <div style="font-size:20px; font-weight:800; color:#10b981;" id="d_paid">0</div>
                    </div>
                    <div style="background:#fff; border-radius:8px; padding:12px; text-align:center;">
                        <div style="color:#94a3b8; font-size:11px;">يتبقى عليه</div>
                        <div style="font-size:20px; font-weight:800; color:#dc2626;" id="d_remain">0</div>
                    </div>
                </div>
                <div id="full_pay_note" style="display:none; margin-top:10px; padding:8px 12px; background:#f0fdf4; border-radius:8px; color:#166534; font-size:13px; font-weight:600;">✅ دفع الكامل — سيُسجَّل نقداً</div>
            </div>

            {{-- حقل الدفع المختلط --}}
            <div id="mix_box" style="display:none; background:#f0f9ff; border:1.5px solid #7dd3fc; border-radius:12px; padding:18px 20px; margin-bottom:20px;">
                <div style="font-weight:700; color:#0369a1; font-size:14px; margin-bottom:14px;">💵🇸🇾 تفاصيل الدفع المختلط</div>
                <input type="hidden" name="payment_mode" id="payment_mode_field" value="normal">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-weight:600; color:#374151; font-size:13px; margin-bottom:6px;">💵 دفع بالدولار $</label>
                        <input type="number" name="mix_usd" id="mix_usd" value="{{ old('mix_usd', 0) }}" min="0" step="0.01"
                               class="input-field" placeholder="0.00" oninput="calcMix()"
                               style="font-size:18px; font-weight:800; color:#1d4ed8; border-color:#93c5fd; background:#eff6ff;">
                    </div>
                    <div>
                        <label style="display:block; font-weight:600; color:#374151; font-size:13px; margin-bottom:6px;">🇸🇾 دفع بالليرة ل.س</label>
                        <input type="number" name="mix_syp" id="mix_syp" value="{{ old('mix_syp', 0) }}" min="0" step="1"
                               class="input-field" placeholder="0" oninput="calcMix()"
                               style="font-size:18px; font-weight:800; color:#92400e; border-color:#fde68a; background:#fef9c3;">
                    </div>
                </div>
                <div id="mix_rate_box" style="margin-bottom:14px; background:#fef9c3; border:1px solid #fde68a; border-radius:8px; padding:12px;">
                    <label style="display:block; font-weight:600; color:#92400e; font-size:12px; margin-bottom:6px;">سعر الصرف للتحويل (ليرة / دولار)</label>
                    <input type="number" name="mix_exchange_rate" id="mix_exchange_rate" value="{{ old('mix_exchange_rate', $usdRate) }}" min="1"
                           class="input-field" placeholder="{{ $usdRate }}" oninput="calcMix()"
                           style="font-size:15px; font-weight:700; color:#92400e; border-color:#fde68a; background:#fff;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-top:10px;">
                    <div style="background:#fff; border-radius:8px; padding:12px; text-align:center;">
                        <div style="color:#94a3b8; font-size:11px;">دفع بالدولار</div>
                        <div style="font-size:16px; font-weight:800; color:#1d4ed8;" id="mx_usd_show">0 $</div>
                    </div>
                    <div style="background:#fff; border-radius:8px; padding:12px; text-align:center;">
                        <div style="color:#94a3b8; font-size:11px;">دفع بالليرة</div>
                        <div style="font-size:16px; font-weight:800; color:#92400e;" id="mx_syp_show">0 ل.س</div>
                    </div>
                    <div style="background:#fff; border-radius:8px; padding:12px; text-align:center;">
                        <div style="color:#94a3b8; font-size:11px;">يتبقى عليه</div>
                        <div style="font-size:16px; font-weight:800; color:#dc2626;" id="mx_remain">0</div>
                    </div>
                </div>
                <div id="mix_full_note" style="display:none; margin-top:10px; padding:8px 12px; background:#f0fdf4; border-radius:8px; color:#166534; font-size:13px; font-weight:600;">✅ دفع الكامل بالدفع المختلط — سيُسجَّل نقداً</div>
            </div>

            {{-- ٦ ملاحظة --}}
            <div style="margin-bottom:24px;">
                <label style="display:block; font-weight:700; color:#374151; font-size:14px; margin-bottom:8px;">
                    <span style="background:#f97316; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; margin-left:6px;">٦</span>
                    ملاحظة (اختياري)
                </label>
                <input type="text" name="note" value="{{ old('note') }}" class="input-field" placeholder="مثال: خصم زبون قديم">
            </div>

            <button type="submit" id="sub_btn"
                    style="width:100%; padding:16px; font-size:17px; font-weight:800; background:linear-gradient(135deg,#f97316,#ea580c); color:#fff; border:none; border-radius:14px; cursor:pointer;">
                🛒 تأكيد البيع
            </button>
        </form>
    </div>

    {{-- Sidebar --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        @php
            $todaySales  = \App\Models\StockMovement::where('type','out')->whereDate('created_at',today())->get()->sum(fn($m)=>$m->totalAmountUsd());
            $todayCount  = \App\Models\StockMovement::where('type','out')->whereDate('created_at',today())->count();
            $activeDebts = \App\Models\StockMovement::where('type','out')->where('is_credit',true)->whereNull('sale_id')->with('debtPayments')->get()->filter(fn($m)=>$m->remaining()>0);
        @endphp

        <div class="card" style="padding:20px;">
            <h4 style="font-weight:700; color:#0f172a; font-size:14px; margin:0 0 14px;">📊 مبيعات اليوم</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                <div style="background:#fff7ed; border-radius:10px; padding:12px; text-align:center;">
                    <div style="color:#94a3b8; font-size:11px;">عدد الصفقات</div>
                    <div style="font-size:24px; font-weight:800; color:#f97316;">{{ $todayCount }}</div>
                </div>
                <div style="background:#fff7ed; border-radius:10px; padding:12px; text-align:center;">
                    <div style="color:#94a3b8; font-size:11px;">ديون مفتوحة</div>
                    <div style="font-size:24px; font-weight:800; color:#ef4444;">{{ $activeDebts->count() }}</div>
                </div>
            </div>
            <div style="background:linear-gradient(135deg,#f97316,#ea580c); border-radius:10px; padding:14px; text-align:center;">
                <div style="color:rgba(255,255,255,0.8); font-size:12px;">مبيعات اليوم (بالدولار)</div>
                <div style="color:#fff; font-size:26px; font-weight:800;">{{ number_format($todaySales, 2) }} $</div>
            </div>
        </div>

        @if($activeDebts->count() > 0)
        <div class="card" style="padding:20px; border-top:3px solid #ef4444;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                <h4 style="font-weight:700; color:#dc2626; font-size:14px; margin:0;">💳 ديون مفتوحة</h4>
                <a href="{{ route('debts.index') }}" style="color:#3b82f6; font-size:12px; text-decoration:none; font-weight:600;">عرض الكل</a>
            </div>
            @foreach($activeDebts->take(4) as $d)
            <div style="padding:10px 0; border-bottom:1px solid #f1f5f9;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-weight:700; color:#0f172a; font-size:13px;">{{ $d->customer_name ?? 'زبون' }}</div>
                        <div style="color:#94a3b8; font-size:11px;">{{ $d->product->name ?? '—' }}</div>
                    </div>
                    <div style="font-weight:800; color:#dc2626; font-size:13px;">
                        {{ number_format($d->remaining(), 0) }} {{ $d->currencySymbol() }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="card" style="padding:20px;">
            <h4 style="font-weight:700; color:#0f172a; font-size:14px; margin:0 0 14px;">🕐 آخر المبيعات</h4>
            @php $lastOut = \App\Models\StockMovement::where('type','out')->with('product')->latest()->take(5)->get(); @endphp
            @forelse($lastOut as $m)
            <div style="padding:9px 0; border-bottom:1px solid #f1f5f9;">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div>
                        <div style="font-weight:600; color:#0f172a; font-size:12px;">{{ $m->product->name ?? '—' }}</div>
                        @if($m->customer_name)
                            <div style="color:#64748b; font-size:11px;">{{ $m->customer_name }}</div>
                        @endif
                        <div style="color:#94a3b8; font-size:10px;">{{ $m->created_at->diffForHumans() }}</div>
                    </div>
                    <div style="text-align:left;">
                        <div style="font-weight:700; color:#f97316; font-size:12px;">{{ number_format($m->totalAmount(), 0) }} {{ $m->currencySymbol() }}</div>
                        @if($m->is_credit && !$m->isFullyPaid())
                            <div style="font-size:10px; color:#ef4444; font-weight:600;">دين</div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center; color:#94a3b8; padding:20px;">لا توجد مبيعات</div>
            @endforelse
        </div>

    </div>
</div>

<script>
let stock=0, unit='', origPrice=0, sellPrice=0, currency='USD', payMode='cash';

function filterOutSelect(q) {
    const sel = document.getElementById('product_select');
    const ql  = q.toLowerCase().trim();
    Array.from(sel.options).forEach(function(opt) {
        if (!opt.value) return;
        const name = (opt.dataset.name || opt.text).toLowerCase();
        opt.style.display = (!ql || name.includes(ql)) ? '' : 'none';
    });
    const chosen = sel.options[sel.selectedIndex];
    if (chosen && chosen.value && chosen.style.display === 'none') {
        sel.value = '';
        document.getElementById('pinfo').style.display = 'none';
    }
}

function setCurrency(c) {
    currency = c;
    const usdBtn = document.getElementById('btn_usd');
    const sypBtn = document.getElementById('btn_syp');
    const sypBox = document.getElementById('syp_rate_box');
    const sym    = document.getElementById('sym_label');
    const cl     = document.getElementById('curr_label');
    const ds     = document.getElementById('debt_sym');
    if (c === 'USD') {
        usdBtn.style.borderColor='#3b82f6'; usdBtn.style.background='#eff6ff'; usdBtn.style.color='#3b82f6';
        sypBtn.style.borderColor='#e2e8f0'; sypBtn.style.background='#fff'; sypBtn.style.color='#64748b';
        sypBox.style.display='none'; sym.textContent='$'; cl.textContent='($)'; if(ds) ds.textContent='$';
        document.querySelectorAll('input[name=currency]')[0].checked = true;
        if (stock > 0 && origPrice > 0 && !document.getElementById('price_input').value)
            document.getElementById('price_input').value = origPrice.toFixed(2);
    } else {
        sypBtn.style.borderColor='#f59e0b'; sypBtn.style.background='#fef9c3'; sypBtn.style.color='#92400e';
        usdBtn.style.borderColor='#e2e8f0'; usdBtn.style.background='#fff'; usdBtn.style.color='#64748b';
        sypBox.style.display='block'; sym.textContent='ل.س'; cl.textContent='(ل.س)'; if(ds) ds.textContent='ل.س';
        document.querySelectorAll('input[name=currency]')[1].checked = true;
        autoFillSyp();
    }
    calc();
}

function autoFillSyp() {
    if (currency !== 'SYP') return;
    const base = sellPrice > 0 ? sellPrice : origPrice;
    if (base <= 0) return;
    const rate = parseFloat(document.getElementById('exchange_rate').value) || 0;
    if (rate > 0) {
        document.getElementById('price_input').value = Math.round(base * rate);
    }
    calc();
}

function setPayment(mode) {
    payMode = mode;
    const cashBtn = document.getElementById('btn_cash');
    const debtBtn = document.getElementById('btn_debt');
    const mixBtn  = document.getElementById('btn_mix');
    const debtBox = document.getElementById('debt_box');
    const mixBox  = document.getElementById('mix_box');
    const pmField = document.getElementById('payment_mode_field');
    const cName   = document.getElementById('customer_name');

    // reset all buttons
    cashBtn.style.borderColor='#e2e8f0'; cashBtn.style.background='#fff'; cashBtn.style.color='#64748b';
    debtBtn.style.borderColor='#e2e8f0'; debtBtn.style.background='#fff'; debtBtn.style.color='#64748b';
    mixBtn.style.borderColor='#e2e8f0';  mixBtn.style.background='#fff';  mixBtn.style.color='#64748b';
    debtBox.style.display='none';
    mixBox.style.display='none';
    // restore currency buttons (hidden in mix mode)
    document.getElementById('btn_usd').style.display = '';
    document.getElementById('btn_syp').style.display = '';

    if (mode === 'cash') {
        cashBtn.style.borderColor='#10b981'; cashBtn.style.background='#f0fdf4'; cashBtn.style.color='#059669';
        document.querySelectorAll('input[name=is_credit]')[0].checked = true;
        pmField.value = 'normal';
    } else if (mode === 'debt') {
        debtBtn.style.borderColor='#ef4444'; debtBtn.style.background='#fef2f2'; debtBtn.style.color='#dc2626';
        document.querySelectorAll('input[name=is_credit]')[1].checked = true;
        debtBox.style.display='block';
        pmField.value = 'normal';
        cName.focus();
        calcDebt();
    } else { // mix — always USD
        mixBtn.style.borderColor='#0284c7'; mixBtn.style.background='#f0f9ff'; mixBtn.style.color='#0369a1';
        document.querySelectorAll('input[name=is_credit]')[1].checked = true;
        mixBox.style.display='block';
        pmField.value = 'mix';
        // force USD and hide currency buttons
        // Reset price to USD value first (avoids leftover SYP price being sent as USD)
        var pi = document.getElementById('price_input');
        if (sellPrice > 0) { pi.value = sellPrice.toFixed(2); }
        else if (origPrice > 0) { pi.value = origPrice.toFixed(2); }
        setCurrency('USD');
        document.getElementById('btn_usd').style.display = 'none';
        document.getElementById('btn_syp').style.display = 'none';
        document.getElementById('syp_rate_box').style.display = 'none';
        cName.focus();
        calcMix();
    }
}

function onProduct(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!sel.value) { document.getElementById('pinfo').style.display='none'; return; }
    stock     = parseFloat(opt.dataset.qty);
    unit      = opt.dataset.unit;
    origPrice = parseFloat(opt.dataset.price)     || 0;
    sellPrice = parseFloat(opt.dataset.sellPrice) || origPrice;
    document.getElementById('i_qty').textContent        = stock;
    document.getElementById('i_unit').textContent       = unit;
    document.getElementById('i_orig_price').textContent = origPrice.toFixed(2);
    document.getElementById('i_sell_price').textContent = sellPrice.toFixed(2);
    document.getElementById('pinfo').style.display      = 'block';
    const pi = document.getElementById('price_input');
    if (!pi.value) {
        if (currency === 'SYP') { autoFillSyp(); }
        else { pi.value = sellPrice.toFixed(2); }
    }
    calc();
}

function calc() {
    const qty   = parseFloat(document.getElementById('qty_input').value)  || 0;
    const price = parseFloat(document.getElementById('price_input').value) || 0;
    const sym   = currency === 'SYP' ? 'ل.س' : '$';
    const errEl = document.getElementById('qty_err');
    const btn   = document.getElementById('sub_btn');
    const tbox  = document.getElementById('total_box');

    if (qty > stock && stock > 0) {
        errEl.style.display='block'; btn.disabled=true; btn.style.opacity='0.5';
    } else {
        errEl.style.display='none'; btn.disabled=false; btn.style.opacity='1';
    }

    const total = qty * price;
    if (qty > 0 && price > 0) {
        document.getElementById('grand_total').textContent = number(total) + ' ' + sym;
        tbox.style.display='flex';
    } else {
        tbox.style.display='none';
    }
    if (payMode === 'debt') calcDebt();
    if (payMode === 'mix')  calcMix();
}

function calcDebt() {
    if (payMode !== 'debt') return;
    const qty   = parseFloat(document.getElementById('qty_input').value)  || 0;
    const price = parseFloat(document.getElementById('price_input').value) || 0;
    const paid  = parseFloat(document.getElementById('amt_paid').value)  || 0;
    const total = qty * price;
    const sym   = currency === 'SYP' ? 'ل.س' : '$';
    const safePaid = Math.min(paid, total);
    const remain   = Math.max(0, total - safePaid);
    document.getElementById('d_paid').textContent   = number(safePaid) + ' ' + sym;
    document.getElementById('d_remain').textContent = number(remain)   + ' ' + sym;
    const note = document.getElementById('full_pay_note');
    note.style.display = (remain <= 0 && total > 0) ? 'block' : 'none';
}

function calcMix() {
    const qty      = parseFloat(document.getElementById('qty_input').value)     || 0;
    const price    = parseFloat(document.getElementById('price_input').value) || 0;
    const usdPaid  = parseFloat(document.getElementById('mix_usd').value)     || 0;
    const sypPaid  = parseFloat(document.getElementById('mix_syp').value)     || 0;
    const rate     = parseFloat(document.getElementById('mix_exchange_rate').value) || {{ $usdRate }};
    const totalUsd = qty * price; // always USD for mix mode
    const sypInUsd = sypPaid / rate;
    const totalPaidUsd = usdPaid + sypInUsd;
    const remainUsd    = Math.max(0, totalUsd - totalPaidUsd);

    document.getElementById('mx_usd_show').textContent = number(usdPaid) + ' $';
    document.getElementById('mx_syp_show').textContent = number(sypPaid) + ' ل.س';
    document.getElementById('mx_remain').textContent   = number(remainUsd) + ' $';
    const note = document.getElementById('mix_full_note');
    note.style.display = (remainUsd <= 0 && totalUsd > 0) ? 'block' : 'none';
}

function number(n) { return n % 1 === 0 ? Math.round(n).toLocaleString('en-US') : Number(n).toFixed(2); }

window.onload = () => {
    const sel = document.getElementById('product_select');
    if (sel.value) onProduct(sel);
    @if(old('currency') === 'SYP') setCurrency('SYP'); @endif
    @if(old('payment_mode') === 'mix') setPayment('mix');
    @elseif(old('is_credit') == '1') setPayment('debt');
    @endif
};

document.getElementById('saleForm').addEventListener('submit', function(e) {
    // For mix mode, only treat as credit if there's a remaining balance
    var mixHasRemain = false;
    if (payMode === 'mix') {
        var _qty   = parseFloat(document.getElementById('qty_input').value)   || 0;
        var _price = parseFloat(document.getElementById('price_input').value) || 0;
        var _usd   = parseFloat(document.getElementById('mix_usd').value)     || 0;
        var _syp   = parseFloat(document.getElementById('mix_syp').value)     || 0;
        var _rate  = parseFloat(document.getElementById('mix_exchange_rate').value) || 1;
        mixHasRemain = (_qty * _price) - (_usd + _syp / _rate) > 0.01;
    }
    const isCredit = payMode === 'debt' || (payMode === 'mix' && mixHasRemain);
    const custName = document.getElementById('customer_name').value.trim();
    const price    = parseFloat(document.getElementById('price_input').value) || 0;
    const qty      = parseFloat(document.getElementById('qty_input').value)     || 0;

    if (isCredit && !custName) {
        e.preventDefault();
        const inp = document.getElementById('customer_name');
        inp.style.borderColor = '#ef4444';
        inp.style.background  = '#fef2f2';
        inp.focus();
        let err = document.getElementById('_cname_err');
        if (!err) {
            err = document.createElement('div');
            err.id = '_cname_err';
            err.style.cssText = 'color:#dc2626; font-size:13px; font-weight:700; margin-top:6px; padding:8px 12px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px;';
            inp.insertAdjacentElement('afterend', err);
        }
        err.textContent = '⚠️ يجب إدخال اسم الزبون عند البيع بالدين — بدونه لن تعرف من يدين لك!';
        setTimeout(() => { inp.style.borderColor = ''; inp.style.background = ''; if(err) err.remove(); }, 5000);
        return;
    }

    if (payMode === 'mix') {
        const rate    = parseFloat(document.getElementById('mix_exchange_rate').value) || {{ $usdRate }};
        const usdPaid = parseFloat(document.getElementById('mix_usd').value) || 0;
        const sypPaid = parseFloat(document.getElementById('mix_syp').value) || 0;
        const total   = qty * price;
        const paid    = usdPaid + sypPaid / rate;
        if (paid > total + 0.01) {
            e.preventDefault();
            alert('⚠️ المبلغ المدفوع أكبر من إجمالي الفاتورة!');
            return;
        }
        // force currency to USD for mix mode
        document.querySelectorAll('input[name=currency]')[0].checked = true;
    }

    if (qty > 0 && price === 0) {
        if (!confirm('⚠️ سعر البيع صفر — هل أنت متأكد أنك تريد البيع مجاناً؟')) {
            e.preventDefault();
        }
    }
});
</script>
@endsection
