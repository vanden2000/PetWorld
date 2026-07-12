@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa danh mục')

@section('styles')
    <style>
        .error-message {
            color: #d93025;
            font-size: 0.85rem;
            margin-top: 4px;
        }
        .form-control.is-invalid {
            border-color: #d93025;
        }
        .form-group label {
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
            display: inline-block;
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: #fff;
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.15);
        }
        .btn-cancel {
            background-color: #f1f3f4;
            color: #5f6368;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-cancel:hover {
            background-color: #e8eaed;
        }
        .btn-save {
            background-color: var(--primary);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-save:hover {
            filter: brightness(0.95);
        }
        /* Custom layout styles matching voucher edit grid */
        .category-create-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }
        .form-card {
            background-color: #fff;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 24px;
            margin-bottom: 24px;
        }
        .form-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-main);
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 12px;
        }
        .form-card-title i {
            color: var(--primary);
        }
        .form-group-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .custom-radio-container {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s;
        }
        .custom-radio-container:hover {
            background-color: #f8f9fa;
        }
        .radio-label-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-main);
        }
        @media (max-width: 992px) {
            .category-create-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
<form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Bạn có chắc chắn muốn cập nhật danh mục này không?')">
    @csrf
    @method('PUT')
    
    <!-- Dashboard Header Nav Bar -->
    <div class="dashboard-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div class="header-title-block">
            <h1 style="color: var(--text-main); font-weight: 700; font-size: 1.75rem;">Chỉnh sửa danh mục</h1>
            <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.95rem;">Cập nhật thông tin chi tiết của danh mục sản phẩm trên hệ thống cửa hàng PetWorld.</p>
        </div>
        <div class="header-actions" style="display: flex; gap: 12px;">
            <a href="{{ route('admin.categories') }}" class="btn-cancel">Hủy</a>
            <button type="submit" class="btn-save">Cập nhật danh mục</button>
        </div>
    </div>

    <!-- Responsive Columns -->
    <div class="category-create-grid">
        <!-- Left Main Form Column -->
        <div class="category-main-col">
            <!-- General Information Form Card -->
            <div class="form-card">
                <div class="form-card-title">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Thông tin chung</span>
                </div>

                <div class="form-group-row">
                    <div class="form-group">
                        <label for="name">Tên danh mục <span class="required" style="color: #d93025;">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required placeholder="Ví dụ: Thức ăn cho chó">
                        @error('name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug (Đường dẫn)</label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="thuc-an-cho-cho">
                        @error('slug')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Mô tả danh mục</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Nhập mô tả chi tiết về danh mục này...">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="category_image">Hình ảnh đại diện</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="category_image" name="image" accept="image/*">
                    @error('image')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">Tải lên ảnh PNG, JPG, GIF tối đa 5MB. Để trống nếu muốn giữ nguyên ảnh cũ.</p>
                    
                    @if(!empty($category->image))
                        <div style="margin-top: 16px;">
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px; font-weight: 600;">Ảnh hiện tại:</p>
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" style="max-height: 100px; object-fit: contain; border-radius: 8px; border: 1px solid var(--border-color);">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar Form Column -->
        <div class="category-sidebar-col">
            <!-- Status Card -->
            <div class="form-card" style="padding: 24px;">
                <div class="form-card-title" style="margin-bottom: 16px; padding-bottom: 8px;">
                    <i class="fa-solid fa-toggle-on"></i>
                    <span>Trạng thái</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <label class="custom-radio-container">
                        <input type="radio" name="status" value="active" {{ old('status', $category->status) === 'active' ? 'checked' : '' }}>
                        <span class="radio-indicator"></span>
                        <div class="radio-label-details">
                            <span class="radio-label-title">Hiển thị (Active)</span>
                        </div>
                    </label>
                    <label class="custom-radio-container">
                        <input type="radio" name="status" value="draft" {{ old('status', $category->status) === 'draft' ? 'checked' : '' }}>
                        <span class="radio-indicator"></span>
                        <div class="radio-label-details">
                            <span class="radio-label-title">Tạm ẩn (Draft)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Tips Card -->
            <div style="background-color: #fff9e6; border: 1px solid #ffeeba; border-radius: 12px; padding: 20px;">
                <h4 style="color: #856404; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-lightbulb"></i> Lưu ý thiết lập:
                </h4>
                <ul style="color: #856404; font-size: 0.85rem; padding-left: 20px; line-height: 1.5; margin: 0;">
                    <li>Tên danh mục nên ngắn gọn và mô tả đúng loại sản phẩm (ví dụ: Thức ăn hạt, Phụ kiện...).</li>
                    <li>Đường dẫn (Slug) tự động tạo từ tên danh mục, dùng để tối ưu SEO cho đường dẫn URL.</li>
                    <li>Trạng thái "Hiển thị" giúp khách hàng có thể nhìn thấy danh mục và sản phẩm ngoài trang chủ.</li>
                </ul>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const titleInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        // Automatically generate slug on title change
        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function() {
                let slug = titleInput.value.toLowerCase();
                // Normalize and remove Vietnamese tone marks/accents
                slug = slug.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                // Replace Đ/đ to D/d
                slug = slug.replace(/[đĐ]/g, 'd');
                // Remove non-word (non-alphanumeric) chars besides spaces/hyphens
                slug = slug.replace(/[^a-z0-9\s-]/g, '');
                // Replace spaces/multiple hyphens with a single hyphen
                slug = slug.replace(/[\s-]+/g, '-');
                // Remove leading/trailing hyphens
                slug = slug.replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            });
        }
    });
</script>
@endsection