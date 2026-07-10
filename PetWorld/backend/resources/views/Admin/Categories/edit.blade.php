@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa danh mục')

@section('styles')

@endsection

@section('content')
    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Bạn có chắc chắn muốn cập nhật danh mục này không?')">
        @csrf
        @method('PUT')

        <!-- Dashboard Header Nav Bar -->
        <div class="dashboard-header" style="margin-bottom: 24px;">
            <div class="header-title-block">
                <h1 style="color: var(--text-main); font-weight: 700; font-size: 1.75rem;">Chỉnh sửa danh mục</h1>
                <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.95rem;">Cập nhật thông tin chi tiết của
                    danh mục sản phẩm trên hệ thống cửa hàng PetWorld.</p>
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
                            <label for="name">Tên danh mục <span class="required">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $category->name) }}" required placeholder="Ví dụ: Thức ăn cho chó">
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug (Đường dẫn)</label>
                            <div class="input-icon-wrapper">
                                <input type="text" class="form-control" id="slug" name="slug"
                                    value="{{ old('slug', $category->slug) }}" placeholder="thuc-an-cho-cho">
                                <span class="input-icon-right">
                                    <i class="fa-solid fa-link"></i>
                                </span>
                            </div>
                        </div>
                    </div>



                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="description">Mô tả danh mục</label>
                        <textarea class="form-control" id="description" name="description" rows="6"
                            placeholder="Nhập mô tả chi tiết về danh mục này...">{{ old('description', $category->description ?? '') }}</textarea>
                    </div>
                </div>


            </div>

            <!-- Right Sidebar Form Column -->
            <div class="category-sidebar-col">
                <!-- Status Card -->
                <div class="form-card" style="padding: 24px;">
                    <h3 class="sidebar-card-title">TRẠNG THÁI</h3>
                    <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 16px;">
                        <label class="custom-radio-container">
                            <input type="radio" name="status" value="active" {{ old('status', $category->status ?? 'active') == 'active' ? 'checked' : '' }}>
                            <span class="radio-indicator"></span>
                            <div class="radio-label-details">
                                <span class="radio-label-title">Hiển thị công khai</span>
                            </div>
                        </label>
                        <label class="custom-radio-container">
                            <input type="radio" name="status" value="draft" {{ old('status', $category->status ?? 'active') == 'draft' ? 'checked' : '' }}>
                            <span class="radio-indicator"></span>
                            <div class="radio-label-details">
                                <span class="radio-label-title">Ẩn khỏi hệ thống</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Image Thumbnail box -->
                <div class="form-card" style="padding: 24px;">
                    <h3 class="sidebar-card-title">HÌNH ẢNH ĐẠI DIỆN</h3>
                    <div class="image-upload-wrapper" style="margin-top: 16px;">
                        <div class="upload-dropzone" id="dropzone">
                            <div class="cloud-icon">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="dropzone-text-primary">Nhấp để tải lên hoặc kéo thả</p>
                            <p class="dropzone-text-sub">PNG, JPG tối đa 5MB (800x800px)</p>
                            <input type="file" id="category_image" name="image" style="display: none;" accept="image/*">
                        </div>

                        <!-- File preview thumbnail -->
                        @php
                            $hasImage = !empty($category->image);
                        @endphp
                        <div class="file-preview-card" id="filePreview" style="display: {{ $hasImage ? 'flex' : 'none' }};">
                            @if($hasImage)
                                <input type="hidden" name="image_prefilled" value="yes">
                            @endif
                            <div class="file-preview-left">
                                <div class="file-preview-img-container">
                                    <img src="{{ $hasImage ? asset($category->image) : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?q=80&w=120' }}"
                                        class="preview-img-fallback" id="previewImage" alt="Preview">
                                </div>
                                <div class="file-preview-info">
                                    <span class="file-preview-name"
                                        id="fileName">{{ $hasImage ? basename($category->image) : 'category-thumbnail.png' }}</span>
                                    <span class="file-preview-size" id="fileSize">Đã tải lên</span>
                                </div>
                            </div>
                            <button type="button" class="btn-delete-preview" id="btnDelete" title="Xóa hình ảnh">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Advice Box -->
                <div class="advisor-quote-card">
                    <p class="advisor-quote-text">
                        "Việc sắp xếp danh mục rõ ràng giúp khách hàng của PetWorld dễ dàng tìm kiếm sản phẩm và tăng tỷ lệ
                        chuyển đổi đơn hàng lên tới 30%."
                    </p>
                    <span class="advisor-quote-author">— HỆ THỐNG TƯ VẤN PETWORLD</span>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const titleInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');

            // Automatically generate slug on title change
            if (titleInput && slugInput) {
                titleInput.addEventListener('input', function () {
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

            // Image Dropzone trigger action
            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('category_image');
            const filePreview = document.getElementById('filePreview');
            const previewImage = document.getElementById('previewImage');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const btnDelete = document.getElementById('btnDelete');

            if (dropzone && fileInput) {
                dropzone.addEventListener('click', function () {
                    fileInput.click();
                });

                fileInput.addEventListener('change', function () {
                    if (fileInput.files && fileInput.files[0]) {
                        const file = fileInput.files[0];

                        // Set filename and size
                        if (fileName) fileName.textContent = file.name;
                        if (fileSize) fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

                        // FileReader for image preview
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            if (previewImage) previewImage.src = e.target.result;
                            if (filePreview) filePreview.style.display = 'flex';
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Action delete image thumbnail preview
            if (btnDelete) {
                btnDelete.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (filePreview) filePreview.style.display = 'none';
                    if (fileInput) fileInput.value = '';

                    // Clear prefilled input if exists
                    const prefilled = document.querySelector('input[name="image_prefilled"]');
                    if (prefilled) prefilled.remove();
                });
            }
        });
    </script>
@endsection