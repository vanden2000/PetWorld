@extends('admin.layouts.app')

@section('title', 'Quản lý Banner')

@section('styles')
<style>
    .banner-admin-header h1 { color: var(--text-main); font-size: 1.9rem; font-weight: 800; letter-spacing: 0; }
    .banner-admin-header p { color: var(--text-muted); margin-top: 6px; font-size: 0.92rem; }
    .banner-admin-table-card { border-radius: 10px; overflow: hidden; }
    .banner-admin-thumbnail { width: 140px; height: 70px; border: 1px solid var(--border-color); border-radius: 6px; background: #fff; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: var(--shadow-subtle); }
    .banner-admin-thumbnail img { width: 100%; height: 100%; object-fit: cover; }
    .banner-admin-description { font-size: 0.9rem; color: var(--text-main); font-weight: 500; }
    .banner-admin-action { width: 34px; height: 34px; padding: 0; justify-content: center; text-decoration: none; display: inline-flex; align-items: center; }
    
    /* Toggle switch styles */
    .switch {
      position: relative;
      display: inline-block;
      width: 42px;
      height: 22px;
    }

    .switch input { 
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #cbd5e1;
      transition: .2s;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 16px;
      width: 16px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .2s;
    }

    input:checked + .slider {
      background-color: var(--primary);
    }

    input:focus + .slider {
      box-shadow: 0 0 1px var(--primary);
    }

    input:checked + .slider:before {
      transform: translateX(20px);
    }

    .slider.round {
      border-radius: 22px;
    }

    .slider.round:before {
      border-radius: 50%;
    }

    .toast-notification {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background-color: #1f2e2a;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .toast-notification.show {
        transform: translateY(0);
        opacity: 1;
    }
</style>
@endsection

@section('content')
<div class="banner-admin-page">
    <div class="dashboard-header banner-admin-header" style="margin-bottom: 24px;">
        <div class="header-title-block">
            <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">Quản lý / Banner</div>
            <h1>Banners</h1>
            <p>Quản lý danh sách các hình ảnh slider banner hiển thị ngoài trang chủ khách hàng.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.banners.create') }}" class="categories-add-btn">
                <i class="fa-solid fa-plus" style="font-size: 0.95rem;"></i>
                <span>Thêm banner mới</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background-color: var(--success-light); color: var(--success); padding: 12px 18px; border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.15); margin-bottom: 20px; font-size: 0.9rem; font-weight: 500; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="categories-filter-bar">
        <div class="categories-filter-left">
            <button class="btn-filter-action" type="button"><i class="fa-solid fa-filter"></i><span>Bộ lọc</span></button>
        </div>
        <div class="categories-filter-right">
            <span class="categories-display-text">Hiển thị {{ $banners->count() }} banner</span>
        </div>
    </div>

    <div class="table-card banner-admin-table-card">
        <div class="table-container">
            <table class="category-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th style="width: 180px;">Hình ảnh</th>
                        <th>Mô tả</th>
                        <th>Đường dẫn liên kết (Link)</th>
                        <th style="width: 140px; text-align: center;">Trạng thái</th>
                        <th style="width: 120px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $index => $banner)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="banner-admin-thumbnail">
                                    @php
                                        $imagePath = $banner->image;
                                        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                                            $imageUrl = $imagePath;
                                        } elseif (str_starts_with($imagePath, 'uploads/') || str_starts_with($imagePath, 'image/')) {
                                            $imageUrl = asset($imagePath);
                                        } elseif (str_starts_with($imagePath, 'storage/')) {
                                            $imageUrl = asset($imagePath);
                                        } else {
                                            $imageUrl = asset('storage/' . $imagePath);
                                        }
                                    @endphp
                                    <img src="{{ $imageUrl }}" alt="Banner Thumbnail">
                                </div>
                            </td>
                            <td>
                                <span class="banner-admin-description">{{ $banner->description ?: 'Không có mô tả' }}</span>
                            </td>
                            <td>
                                @if($banner->link)
                                    <a href="{{ $banner->link }}" target="_blank" rel="noopener" style="color: var(--primary); text-decoration: none; font-size: 0.88rem; font-weight: 600;">
                                        {{ $banner->link }}
                                    </a>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">—</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <span class="badge-status {{ $banner->status }}" id="badge-{{ $banner->id }}">
                                        <span style="font-size: 0.9rem; line-height: 1;">•</span> 
                                        <span class="status-text">{{ $banner->status === 'active' ? 'Active' : 'Inactive' }}</span>
                                    </span>
                                    
                                    <label class="switch">
                                        <input type="checkbox" class="status-toggle" data-id="{{ $banner->id }}" {{ $banner->status === 'active' ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 8px;">
                                    <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn-filter-action banner-admin-action" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen" style="font-size: 0.78rem;"></i>
                                    </a>
                                    
                                    <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa banner này?');" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-filter-action banner-admin-action" title="Xóa" style="color: var(--danger); background: rgba(239, 68, 68, 0.08); border-color: rgba(239, 68, 68, 0.15); cursor: pointer;">
                                            <i class="fa-solid fa-trash-can" style="font-size: 0.78rem;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 34px;">Chưa có banner nào được tạo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="toast-message" class="toast-notification">
    <i class="fa-solid fa-circle-check" style="color: var(--success);"></i>
    <span id="toast-text">Đã cập nhật thành công!</span>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.status-toggle');
        const toast = document.getElementById('toast-message');
        const toastText = document.getElementById('toast-text');

        function showToast(message) {
            toastText.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const bannerId = this.dataset.id;
                
                fetch(`/admin/banners/${bannerId}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.getElementById(`badge-${bannerId}`);
                        if (badge) {
                            badge.className = `badge-status ${data.status}`;
                            badge.querySelector('.status-text').textContent = data.status === 'active' ? 'Active' : 'Inactive';
                        }
                        showToast(data.message);
                    } else {
                        // revert checkbox state on failure
                        this.checked = !this.checked;
                        showToast('Có lỗi xảy ra, vui lòng thử lại!');
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.checked = !this.checked;
                    showToast('Lỗi kết nối máy chủ!');
                });
            });
        });
    });
</script>
@endsection