<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لمسات الإبداع')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif; }
        .sidebar { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 16px; border-radius: 10px;
            color: #94a3b8; font-weight: 500; font-size: 14px;
            transition: all 0.2s; margin-bottom: 4px; text-decoration: none;
        }
        .nav-link:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .nav-link.active { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; box-shadow: 0 4px 15px rgba(139,92,246,0.4); }
        .nav-link .icon { font-size: 18px; width: 24px; text-align: center; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.07), 0 4px 20px rgba(0,0,0,0.04); }
        .kpi-card { border-radius: 16px; padding: 24px; position: relative; overflow: hidden; }
        .kpi-card::after { content: ''; position: absolute; top: -20px; left: -20px; width: 80px; height: 80px; border-radius: 50%; opacity: 0.1; background: #fff; }
        .btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; padding: 10px 24px; border-radius: 10px; font-weight: 600; font-size: 14px; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(59,130,246,0.4); }
        .btn-success { background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 10px 24px; border-radius: 10px; font-weight: 600; font-size: 14px; border: none; cursor: pointer; transition: all 0.2s; }
        .btn-success:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(16,185,129,0.4); }
        .btn-danger { background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; padding: 10px 24px; border-radius: 10px; font-weight: 600; font-size: 14px; border: none; cursor: pointer; transition: all 0.2s; }
        .input-field { width: 100%; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 14px; outline: none; transition: border 0.2s; background: #f8fafc; }
        .input-field:focus { border-color: #3b82f6; background: #fff; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .badge-in { background: #dcfce7; color: #15803d; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-out { background: #fef3c7; color: #b45309; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .stock-bar { height: 6px; border-radius: 3px; background: #e2e8f0; overflow: hidden; }
        .stock-bar-fill { height: 100%; border-radius: 3px; transition: width 0.5s; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f8fafc; padding: 13px 20px; text-align: right; font-weight: 600; font-size: 13px; color: #64748b; border-bottom: 1px solid #f1f5f9; }
        tbody td { padding: 14px 20px; border-bottom: 1px solid #f8fafc; font-size: 14px; color: #374151; }
        tbody tr:hover { background: #f8fafc; }
        tbody tr:last-child td { border-bottom: none; }
        select.input-field { appearance: none; }
        ::-webkit-scrollbar { width: 5px; } ::-webkit-scrollbar-track { background: transparent; } ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body style="background: #f1f5f9; min-height: 100vh;">

<div style="display: flex; min-height: 100vh;">

    {{-- Sidebar --}}
    <aside class="sidebar" style="width: 250px; position: fixed; height: 100vh; display: flex; flex-direction: column; z-index: 100;">

        {{-- Logo --}}
        <div style="padding: 22px 20px 18px; border-bottom: 1px solid rgba(255,255,255,0.07);">
            <div style="display: flex; align-items: center; gap: 12px;">
                {{-- House logo matching the business card --}}
                <div style="width: 44px; height: 44px; flex-shrink:0;">
                    <svg viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg" width="44" height="44">
                        <defs>
                            <linearGradient id="houseGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%"   stop-color="#8b5cf6"/>
                                <stop offset="100%" stop-color="#6366f1"/>
                            </linearGradient>
                            <linearGradient id="splashGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%"   stop-color="#f43f5e"/>
                                <stop offset="25%"  stop-color="#f97316"/>
                                <stop offset="50%"  stop-color="#eab308"/>
                                <stop offset="75%"  stop-color="#22c55e"/>
                                <stop offset="100%" stop-color="#3b82f6"/>
                            </linearGradient>
                            <clipPath id="houseClip">
                                <polygon points="22,6 38,20 38,40 6,40 6,20"/>
                            </clipPath>
                        </defs>
                        {{-- House background --}}
                        <rect x="0" y="0" width="44" height="44" rx="10" fill="url(#houseGrad)"/>
                        {{-- Colorful splash inside house (clipped) --}}
                        <ellipse cx="26" cy="28" rx="14" ry="14" fill="url(#splashGrad)" clip-path="url(#houseClip)" opacity="0.9"/>
                        {{-- House outline --}}
                        <polygon points="22,5 39,19 39,41 5,41 5,19" fill="none" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/>
                        {{-- Roof ridge --}}
                        <line x1="22" y1="5" x2="22" y2="5" stroke="#fff" stroke-width="2"/>
                        {{-- Door --}}
                        <rect x="18" y="31" width="8" height="10" rx="4" fill="#fff" opacity="0.85"/>
                        {{-- Window --}}
                        <rect x="27" y="24" width="7" height="6" rx="1.5" fill="#fff" opacity="0.7"/>
                    </svg>
                </div>
                <div>
                    <div style="color: #fff; font-weight: 800; font-size: 16px; line-height: 1.2; letter-spacing:0.3px;">لمسات الإبداع</div>
                    <div style="color: #64748b; font-size: 11px; margin-top:2px;">نظام إدارة المخزون</div>
                </div>
            </div>
        </div>

        {{-- Nav --}}
        <nav style="flex: 1; padding: 16px 12px; overflow-y: auto;">
            <div style="font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 1px; padding: 0 8px; margin-bottom: 8px;">القائمة الرئيسية</div>

            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="icon">📊</span> لوحة التحكم
            </a>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <span class="icon">📦</span> إدارة المنتجات
            </a>

            <div style="font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 1px; padding: 12px 8px 8px;">حركة المخزون</div>

            <a href="{{ route('stock.in') }}" class="nav-link {{ request()->routeIs('stock.in*') ? 'active' : '' }}">
                <span class="icon">⬇️</span> إدخال بضاعة
            </a>
            <a href="{{ route('stock.out') }}" class="nav-link {{ request()->routeIs('stock.out*') ? 'active' : '' }}">
                <span class="icon">⬆️</span> إخراج بضاعة
            </a>
            <a href="{{ route('debts.index') }}" class="nav-link {{ request()->routeIs('debts.*') ? 'active' : '' }}" style="position:relative;">
                <span class="icon">💳</span> الديون
                @php
                    $debtCount = \App\Models\StockMovement::where('type','out')->where('is_credit',true)->whereNull('sale_id')->with('debtPayments')->get()->filter(fn($m)=>$m->remaining()>0)->count()
                               + \App\Models\Sale::where('is_credit',true)->get()->filter(fn($s)=>$s->remaining()>0)->count();
                @endphp
                @if($debtCount > 0)
                    <span style="background:#ef4444; color:#fff; border-radius:20px; padding:1px 7px; font-size:11px; font-weight:700; margin-right:auto;">{{ $debtCount }}</span>
                @endif
            </a>
            <a href="{{ route('stock.history') }}" class="nav-link {{ request()->routeIs('stock.history') ? 'active' : '' }}">
                <span class="icon">📋</span> سجل الحركات
            </a>
            <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <span class="icon">🧾</span> الفواتير
            </a>

            <div style="font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 1px; padding: 12px 8px 8px;">إدارة</div>

            <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <span class="icon">🗃️</span> الدرج اليومي
            </a>
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <span class="icon">📊</span> التقارير
            </a>
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <span class="icon">🗂️</span> التصنيفات
            </a>
        </nav>

        {{-- Footer --}}
        @php
            $__usdRate       = (int) \App\Models\Setting::get('usd_rate', 14000);
            $__rateUpdatedAt = \App\Models\Setting::get('usd_rate_updated_at');
            $__rateAge       = $__rateUpdatedAt ? (int) now()->diffInMinutes(\Carbon\Carbon::parse($__rateUpdatedAt)) : null;
            $__rateStale     = $__rateAge === null || $__rateAge > 240;
        @endphp
        <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.07);">
            <div style="color: #475569; font-size: 12px; margin-bottom:10px;">📅 {{ now()->translatedFormat('l، d F Y') }}</div>

            {{-- Exchange Rate Widget --}}
            <div style="background:rgba(255,255,255,0.05); border:1px solid {{ $__rateStale ? 'rgba(251,191,36,0.4)' : 'rgba(255,255,255,0.1)' }}; border-radius:10px; padding:10px 12px; margin-bottom:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="color:#94a3b8; font-size:11px; font-weight:600;">💵 سعر الصرف</span>
                    @if($__rateStale)
                        <span style="color:#fbbf24; font-size:10px; font-weight:700;">⚠️ قديم</span>
                    @else
                        <span style="color:#34d399; font-size:10px; font-weight:600;">✓ محدَّث</span>
                    @endif
                </div>
                <div style="color:#fff; font-weight:800; font-size:15px; margin-bottom:3px;">1$ = {{ number_format($__usdRate, 0) }} ل.س</div>
                <div style="color:#64748b; font-size:10px; margin-bottom:8px;">
                    @if($__rateAge === null)
                        لم يُحدَّث بعد
                    @elseif($__rateAge < 60)
                        آخر تحديث: منذ {{ $__rateAge }} دقيقة
                    @else
                        آخر تحديث: منذ {{ intdiv($__rateAge, 60) }} ساعة
                    @endif
                </div>
                <form action="{{ route('settings.rate') }}" method="POST" style="display:flex; gap:6px;">
                    @csrf
                    <input type="number" name="usd_rate" min="1" placeholder="السعر الجديد"
                           style="flex:1; min-width:0; border:1px solid rgba(255,255,255,0.15); border-radius:7px; padding:5px 8px; font-size:12px; font-weight:700; color:#fff; background:rgba(255,255,255,0.08); outline:none; font-family:Cairo,sans-serif;"
                           onfocus="this.style.borderColor='rgba(251,191,36,0.6)'" onblur="this.style.borderColor='rgba(255,255,255,0.15)'">
                    <button type="submit"
                            style="background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; border:none; border-radius:7px; padding:5px 10px; font-size:11px; font-weight:700; cursor:pointer; white-space:nowrap; font-family:Cairo,sans-serif;">
                        تحديث
                    </button>
                </form>
            </div>

            <a href="{{ route('settings.backup') }}"
               style="display:flex; align-items:center; gap:8px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:8px 12px; color:#94a3b8; font-size:12px; font-weight:600; text-decoration:none; transition:all 0.2s;"
               onmouseover="this.style.background='rgba(255,255,255,0.12)'; this.style.color='#fff';"
               onmouseout="this.style.background='rgba(255,255,255,0.06)'; this.style.color='#94a3b8';">
                💾 تنزيل نسخة احتياطية
            </a>
        </div>
    </aside>

    {{-- Main Content --}}
    <main style="flex: 1; margin-right: 250px; padding: 32px; min-height: 100vh;">

        {{-- Page Header --}}
        <div style="margin-bottom: 28px;">
            @yield('header')
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-error">⚠️ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $e)
                    <div>❌ {{ $e }}</div>
                @endforeach
            </div>
        @endif

        {{-- Stale exchange rate banner --}}
        @if($__rateStale ?? false)
        <div style="background:#fffbeb; border:1.5px solid #fde68a; border-radius:12px; padding:12px 18px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
            <div>
                <span style="font-weight:700; color:#92400e; font-size:14px;">⚠️ سعر الصرف يحتاج تحديث — </span>
                <span style="color:#78350f; font-size:13px;">
                    @if(($__rateAge ?? null) === null)
                        لم يُحدَّث سعر الدولار منذ التثبيت.
                    @elseif($__rateAge < 60)
                        آخر تحديث منذ {{ $__rateAge }} دقيقة.
                    @else
                        آخر تحديث منذ {{ intdiv($__rateAge, 60) }} ساعة.
                    @endif
                    السعر الحالي: <strong>1$ = {{ number_format($__usdRate ?? 0, 0) }} ل.س</strong>
                </span>
            </div>
            <form action="{{ route('settings.rate') }}" method="POST" style="display:flex; gap:8px; align-items:center; flex-shrink:0;">
                @csrf
                <input type="number" name="usd_rate" min="1" placeholder="السعر الجديد"
                       style="border:1.5px solid #fde68a; border-radius:8px; padding:6px 10px; font-size:14px; font-weight:700; color:#92400e; background:#fffbeb; width:140px; font-family:Cairo,sans-serif; outline:none;">
                <button type="submit"
                        style="background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; border:none; border-radius:8px; padding:7px 16px; font-size:13px; font-weight:700; cursor:pointer; white-space:nowrap; font-family:Cairo,sans-serif;">
                    تحديث الآن
                </button>
            </form>
        </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
// ══════════════════════════════════════════════════════════════════════════════
// GLOBAL NUMBER INPUT FIX
// Fixes reversed/garbled digits on Windows + Arabic locale for ALL numeric fields.
// Strategy:
//   1. Convert every type="number" → type="text" + inputmode + dir="ltr"
//   2. Apply dir="ltr" to ANY input that has inputmode=numeric/decimal (including
//      dynamically-created ones in cart rows, modals, etc.)
//   3. Block non-digit keystrokes in numeric fields
//   4. Convert Arabic/Hindi numerals to Latin on every keystroke
// ══════════════════════════════════════════════════════════════════════════════
(function () {
    'use strict';

    function toLatinDigits(s) {
        return String(s)
            .replace(/[٠-٩]/g, function(c){ return c.charCodeAt(0) - 0x0660; })
            .replace(/[۰-۹]/g, function(c){ return c.charCodeAt(0) - 0x06F0; });
    }

    // Determines if an input should be treated as numeric
    // NOTE: after upgradeOne(), type="number" becomes type="text" + inputmode="decimal/numeric"
    // so we must check inputmode, not just type
    function isNumericInput(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        var im = (el.getAttribute('inputmode') || '').toLowerCase();
        var t  = (el.type || '').toLowerCase();
        return t === 'number' || im === 'numeric' || im === 'decimal' || el.dataset.numFixed === '1';
    }

    // Upgrade a single input: type="number" → text + LTR
    function upgradeOne(el) {
        if (!el || el.tagName !== 'INPUT') return;
        if (el.dataset.numFixed) return; // already processed
        var t = (el.type || '').toLowerCase();
        if (t === 'number') {
            var step = el.getAttribute('step') || '';
            var isFloat = step && step !== '1' && step !== '0';
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

    // Upgrade all numeric inputs inside a root element
    function upgradeAll(root) {
        var sel = (root || document);
        // type="number" first
        sel.querySelectorAll('input[type="number"]').forEach(upgradeOne);
        // then any already-converted inputmode inputs
        sel.querySelectorAll('input[inputmode="numeric"], input[inputmode="decimal"]').forEach(upgradeOne);
    }

    // Run immediately (before DOMContentLoaded in case script is at end of body)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { upgradeAll(document); });
    } else {
        upgradeAll(document);
    }

    // MutationObserver: catch dynamically-injected inputs (cart rows, modals, innerHTML changes)
    var observer = new MutationObserver(function(mutations) {
        for (var i = 0; i < mutations.length; i++) {
            var added = mutations[i].addedNodes;
            for (var j = 0; j < added.length; j++) {
                var node = added[j];
                if (node.nodeType !== 1) continue;
                // If the node itself is an input
                if (node.tagName === 'INPUT') { upgradeOne(node); }
                // Or check all inputs inside it
                else { upgradeAll(node); }
            }
        }
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });

    // ── Arabic/Hindi digit normalization + comma→dot on every input event ────────
    document.addEventListener('input', function(e) {
        var el = e.target;
        if (!el || el.tagName === 'SELECT') return;
        var v = el.value;

        // Convert Arabic/Hindi digits to Latin
        var n = toLatinDigits(v);

        // For numeric inputs: convert comma to dot, remove invalid chars
        if (isNumericInput(el)) {
            // comma → dot
            n = n.replace(/،/g, '.').replace(/,/g, '.');
            // keep only digits, dot, minus — and only one dot
            var parts = n.replace(/[^0-9.\-]/g, '').split('.');
            if (parts.length > 2) n = parts[0] + '.' + parts.slice(1).join('');
            else n = parts.join('.');
            // keep leading minus only
            if (n.indexOf('-') > 0) n = n.replace(/-/g, '');
        }

        if (n !== v) {
            var s = el.selectionStart, end = el.selectionEnd;
            el.value = n;
            try { el.setSelectionRange(s, end); } catch(_) {}
        }
    }, true);

    // ── Block non-numeric keystrokes in numeric fields ─────────────────────────
    // We do NOT block '.' or ',' here — the input event handler cleans up instead.
    // This avoids Windows Arabic keyboard issues where '.' arrives as 'Process'.
    document.addEventListener('keydown', function(e) {
        var el = e.target;
        if (!isNumericInput(el)) return;
        var key = e.key;

        // Always allow: control keys, navigation, clipboard
        if (e.ctrlKey || e.metaKey || e.altKey) return;
        var nav = ['Backspace','Delete','ArrowLeft','ArrowRight','ArrowUp','ArrowDown',
                   'Tab','Enter','Home','End','Escape'];
        if (nav.indexOf(key) !== -1) return;

        // Allow Latin digits
        if (/^[0-9]$/.test(key)) return;

        // Allow dot and comma (comma → dot handled in input event)
        if (key === '.' || key === ',') return;

        // Allow Windows IME / Arabic keyboard events
        if (key === 'Process' || key === 'Unidentified') return;

        // Allow minus only at position 0
        if (key === '-' && el.selectionStart === 0 && el.value.indexOf('-') === -1) return;

        // Allow Arabic/Hindi digits (converted to Latin in input event)
        var code = key.codePointAt(0);
        if ((code >= 0x0660 && code <= 0x0669) || (code >= 0x06F0 && code <= 0x06F9)) return;

        // Block everything else
        e.preventDefault();
    }, true);

    // Final safety net: normalize all inputs on form submit
    document.addEventListener('submit', function (e) {
        e.target.querySelectorAll('input, textarea').forEach(function (el) {
            if (el.value) {
                el.value = toLatinDigits(el.value).replace(/,/g, '.');
            }
        });
    }, true);

    // ── Auto-dismiss success alerts ───────────────────────────────────────────
    document.querySelectorAll('.alert-success').forEach(function (el) {
        // Countdown bar inside the alert
        var bar = document.createElement('div');
        bar.style.cssText = 'height:3px; background:#16a34a; border-radius:2px; margin-top:8px; width:100%; transition:width 4s linear;';
        el.appendChild(bar);
        setTimeout(function () { bar.style.width = '0'; }, 50);
        setTimeout(function () {
            el.style.transition = 'opacity 0.6s ease, max-height 0.6s ease, margin 0.5s ease, padding 0.5s ease';
            el.style.overflow   = 'hidden';
            el.style.opacity    = '0';
            el.style.maxHeight  = '0';
            el.style.padding    = '0';
            el.style.marginBottom = '0';
        }, 4100);
    });

    // ── Auto-refresh for read-only pages ──────────────────────────────────────
    // Individual views set window._autoRefreshSec = N to opt in.
    if (window._autoRefreshSec) {
        var countdown = window._autoRefreshSec;
        var badge = document.getElementById('_refresh_badge');
        if (badge) {
            var _refreshTimer = setInterval(function () {
                countdown--;
                badge.textContent = countdown + 'ث';
                if (countdown <= 0) {
                    clearInterval(_refreshTimer);
                    location.reload();
                }
            }, 1000);
        } else {
            setTimeout(function () { location.reload(); }, window._autoRefreshSec * 1000);
        }
    }
}());

// ── Strip thousands commas from all PHP-rendered numbers in the page ──────────
// Walks every text node and removes commas between digits (e.g. 123,456 → 123456)
(function stripNumberCommas() {
    function fix(node) {
        if (node.nodeType === 3) { // text node
            var v = node.nodeValue;
            // Replace pattern: digit,digit (thousands separator only, not decimal)
            var fixed = v.replace(/(\d),(\d)/g, '$1$2');
            if (fixed !== v) node.nodeValue = fixed;
        } else if (node.nodeType === 1 && !['SCRIPT','STYLE','INPUT','TEXTAREA'].includes(node.tagName)) {
            for (var i = 0; i < node.childNodes.length; i++) fix(node.childNodes[i]);
        }
    }
    document.addEventListener('DOMContentLoaded', function() { fix(document.body); });
    // Also watch for dynamic content (charts, JS-rendered rows)
    var obs2 = new MutationObserver(function(muts) {
        muts.forEach(function(m) {
            m.addedNodes.forEach(function(n) { fix(n); });
        });
    });
    obs2.observe(document.documentElement, { childList: true, subtree: true });
}());
</script>
</body>
</html>
