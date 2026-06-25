@extends('layouts.app')
@section('title', 'التصنيفات')

@section('header')
<div>
    <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">🗂️ التصنيفات</h1>
    <p style="color:#64748b; font-size:14px; margin:4px 0 0;">إدارة تصنيفات المنتجات</p>
</div>
@endsection

@section('content')

@if(session('error'))
<div style="background:#fef2f2; color:#ef4444; border-radius:10px; padding:12px 18px; margin-bottom:16px; font-weight:600; font-size:14px;">⚠️ {{ session('error') }}</div>
@endif

<div style="display:grid; grid-template-columns:1fr 2fr; gap:20px; align-items:start;">

    {{-- Add form --}}
    <div class="card" style="padding:24px;">
        <h3 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 18px;">➕ إضافة تصنيف جديد</h3>
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px;">
                <input type="text" name="name" value="{{ old('name') }}" placeholder="مثال: أدوات كهربائية"
                       class="input-field" style="font-size:15px; padding:12px;">
                @error('name')
                    <div style="color:#ef4444; font-size:12px; margin-top:6px;">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" style="width:100%; padding:12px; background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; font-family:Cairo,sans-serif;">
                💾 حفظ التصنيف
            </button>
        </form>
    </div>

    {{-- Categories list --}}
    <div class="card" style="overflow:hidden;">
        <div style="padding:16px 22px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
            <h3 style="font-size:15px; font-weight:700; color:#0f172a; margin:0;">التصنيفات الحالية</h3>
            <span style="background:#f1f5f9; color:#64748b; font-size:13px; font-weight:700; padding:4px 12px; border-radius:20px;">{{ $categories->count() }} تصنيف</span>
        </div>
        @if($categories->isEmpty())
        <div style="padding:48px; text-align:center; color:#94a3b8;">
            <div style="font-size:40px; margin-bottom:10px;">🗂️</div>
            <div style="font-size:15px; font-weight:600;">لا توجد تصنيفات بعد</div>
        </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم التصنيف</th>
                    <th style="text-align:center;">عدد المنتجات</th>
                    <th style="text-align:center; width:180px;">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $i => $cat)
                <tr id="row-{{ $cat->id }}">
                    <td style="color:#94a3b8; font-size:13px;">{{ $i + 1 }}</td>
                    <td>
                        {{-- Display mode --}}
                        <div id="name-{{ $cat->id }}" style="font-weight:700; color:#0f172a; font-size:15px;">{{ $cat->name }}</div>
                        {{-- Edit mode (hidden) --}}
                        <form id="form-{{ $cat->id }}" action="{{ route('categories.update', $cat) }}" method="POST" style="display:none;">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $cat->name }}"
                                style="border:1.5px solid #3b82f6; border-radius:8px; padding:6px 10px; font-size:14px; font-family:Cairo,sans-serif; width:100%; outline:none;">
                        </form>
                    </td>
                    <td style="text-align:center;">
                        <span style="background:#eff6ff; color:#3b82f6; padding:4px 14px; border-radius:20px; font-size:13px; font-weight:700;">
                            {{ $cat->products_count }} منتج
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <div id="actions-{{ $cat->id }}" style="display:flex; gap:6px; justify-content:center;">
                            {{-- Edit button --}}
                            <button onclick="startEdit({{ $cat->id }})"
                                style="background:#eff6ff; color:#3b82f6; border:1.5px solid #bfdbfe; border-radius:8px; padding:6px 12px; font-size:13px; font-weight:700; cursor:pointer; font-family:Cairo,sans-serif;">
                                ✏️ تعديل
                            </button>
                            {{-- Delete button --}}
                            @if($cat->products_count === 0)
                            <form action="{{ route('categories.destroy', $cat) }}" method="POST"
                                  onsubmit="return confirm('حذف تصنيف {{ $cat->name }}؟')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:#fef2f2; color:#ef4444; border:1.5px solid #fecaca; border-radius:8px; padding:6px 12px; font-size:13px; font-weight:700; cursor:pointer; font-family:Cairo,sans-serif;">
                                    🗑️ حذف
                                </button>
                            </form>
                            @else
                            <span style="color:#94a3b8; font-size:12px; padding:6px 4px;">يحتوي منتجات</span>
                            @endif
                        </div>
                        {{-- Save/Cancel (hidden until edit) --}}
                        <div id="edit-actions-{{ $cat->id }}" style="display:none; gap:6px; justify-content:center;">
                            <button onclick="submitEdit({{ $cat->id }})"
                                style="background:#f0fdf4; color:#16a34a; border:1.5px solid #bbf7d0; border-radius:8px; padding:6px 12px; font-size:13px; font-weight:700; cursor:pointer; font-family:Cairo,sans-serif;">
                                💾 حفظ
                            </button>
                            <button onclick="cancelEdit({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                                style="background:#f8fafc; color:#64748b; border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 12px; font-size:13px; font-weight:700; cursor:pointer; font-family:Cairo,sans-serif;">
                                إلغاء
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

<script>
function startEdit(id) {
    document.getElementById('name-' + id).style.display = 'none';
    document.getElementById('form-' + id).style.display = 'block';
    document.getElementById('actions-' + id).style.display = 'none';
    document.getElementById('edit-actions-' + id).style.display = 'flex';
    document.querySelector('#form-' + id + ' input[name=name]').focus();
}
function cancelEdit(id, originalName) {
    document.getElementById('name-' + id).style.display = 'block';
    document.getElementById('form-' + id).style.display = 'none';
    document.getElementById('actions-' + id).style.display = 'flex';
    document.getElementById('edit-actions-' + id).style.display = 'none';
    document.querySelector('#form-' + id + ' input[name=name]').value = originalName;
}
function submitEdit(id) {
    document.getElementById('form-' + id).submit();
}
</script>
        @endif
    </div>

</div>
@endsection
