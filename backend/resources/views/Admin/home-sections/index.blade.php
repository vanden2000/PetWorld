@extends('Admin.layouts.app')

@section('title', 'Quản Lý Hiển Thị Trang Chủ')

@section('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        background: #ffffff;
        padding: 1.25rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    }
    .page-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .page-title i {
        color: #4f46e5;
        font-size: 1.4rem;
        background: #eef2ff;
        padding: 0.5rem;
        border-radius: 8px;
    }
    .card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }
    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }
    .table-custom th {
        background: #f8fafc;
        padding: 1rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }
    .table-custom td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.925rem;
        color: #334155;
    }
    .table-custom tr:hover {
        background-color: #f8fafc;
    }
    .badge-key {
        display: inline-block;
        font-family: monospace;
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        background: #f1f5f9;
        color: #475569;
        margin-top: 0.25rem;
    }
    .form-control-sm {
        padding: 0.4rem 0.6rem;
        font-size: 0.875rem;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        transition: border-color 0.2s;
    }
    .form-control-sm:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #10b981;
    }
    input:checked + .slider:before {
        transform: translateX(20px);
    }
    .btn-save {
        background: #4f46e5;
        color: #ffffff;
        padding: 0.6rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.2s;
    }
    .btn-save:hover {
        background: #4338ca;
    }
    .btn-reset {
        background: #f1f5f9;
        color: #64748b;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }
    .btn-reset:hover {
        background: #e2e8f0;
        color: #334155;
    }
    .alert-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fa-solid fa-layer-group"></i>
            Quản Lý Hiển Thị Trang Chủ
        </h1>
        <div style="display: flex; gap: 0.75rem;">
            <button type="button" class="btn-reset" onclick="if(confirm('Bạn có chắc chắn muốn khôi phục lại cấu hình vị trí mặc định cho 12 khối?')) document.getElementById('reset-default-form').submit();">
                <i class="fa-solid fa-rotate-left"></i> Khôi phục mặc định
            </button>

            <button type="submit" form="home-sections-form" class="btn-save">
                <i class="fa-solid fa-floppy-disk"></i> Lưu cấu hình
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Form khôi phục mặc định độc lập -->
    <form id="reset-default-form" action="{{ route('admin.home-sections.reset') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Các form Toggle trạng thái độc lập -->
    @foreach($sections as $section)
        <form id="toggle-form-{{ $section->id }}" action="{{ route('admin.home-sections.toggle', $section->id) }}" method="POST" style="display: none;">
            @csrf
            @method('PATCH')
        </form>
    @endforeach

    <!-- Form chính lưu thay đổi tất cả các khối -->
    <form id="home-sections-form" action="{{ route('admin.home-sections.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 70px; text-align: center;">Bật/Tắt</th>
                        <th style="width: 80px; text-align: center;">Thứ tự</th>
                        <th>Tên khối hiển thị</th>
                        <th>Tiêu đề ngoài Trang chủ (Custom Title)</th>
                        <th style="width: 140px;">Số lượng hiển thị</th>
                        <th style="width: 100px; text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sections as $index => $section)
                        <tr>
                            <td style="text-align: center;">
                                <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $section->id }}">
                                <label class="switch">
                                    <input type="checkbox" name="active_sections[]" value="{{ $section->id }}" {{ $section->is_active ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </td>
                            <td style="text-align: center;">
                                <input type="number" name="sections[{{ $index }}][order]" value="{{ $section->order }}" class="form-control-sm" style="width: 60px; text-align: center;" min="1" max="99">
                            </td>
                            <td>
                                <strong style="color: #1e293b; display: block;">{{ $section->name }}</strong>
                                <span class="badge-key">key: {{ $section->key }}</span>
                            </td>
                            <td>
                                <input type="text" name="sections[{{ $index }}][custom_title]" value="{{ $section->custom_title }}" class="form-control-sm" style="width: 100%; max-width: 320px;" placeholder="Theo tiêu đề mặc định...">
                            </td>
                            <td>
                                @if(in_array($section->key, ['featured_products', 'new_products', 'accessories_promo', 'sale_products_tabs', 'testimonials', 'latest_blogs', 'brands']))
                                    <input type="number" name="sections[{{ $index }}][limit]" value="{{ $section->limit }}" class="form-control-sm" style="width: 80px;" min="1" max="100">
                                @else
                                    <input type="hidden" name="sections[{{ $index }}][limit]" value="">
                                    <span style="color: #94a3b8; font-size: 0.85rem;">(Mặc định)</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <button type="button" onclick="document.getElementById('toggle-form-{{ $section->id }}').submit();" style="background: none; border: none; cursor: pointer; color: {{ $section->is_active ? '#ef4444' : '#10b981' }}; font-size: 0.875rem; font-weight: 600;" title="{{ $section->is_active ? 'Bấm để Tắt' : 'Bấm để Bật' }}">
                                    @if($section->is_active)
                                        <i class="fa-solid fa-eye-slash"></i> Tắt
                                    @else
                                        <i class="fa-solid fa-eye"></i> Bật
                                    @endif
                                </button>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="submit" class="btn-save" style="padding: 0.75rem 2rem; font-size: 1rem;">
                <i class="fa-solid fa-floppy-disk"></i> Lưu tất cả thay đổi
            </button>
        </div>
    </form>
</div>
@endsection
