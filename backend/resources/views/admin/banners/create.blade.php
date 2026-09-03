@extends('admin.layouts.app')

@section('title', 'Thêm banner mới')

@section('styles')
<style>
    /* Styling consistency with existing create forms */
    .banner-identity-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 30px;
    }
    @media (max-width: 768px) {
        .banner-identity-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
</style>
@endsection

@section('content')
<form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Dashboard Header Nav Bar -->
    <div class="dashboard-header" style="margin-bottom: 24px;">
        <div class="header-title-block">
            <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">Quản lý Banner / Tạo mới</div>
            <h1 style="color: var(--text-main); font-weight: 700; font-size: 1.75rem;">Thêm Banner Mới</h1>
        </div>
        <div class="header-actions" style="display: flex; gap: 12px;">
            <a href="{{ route('admin.banners') }}" class="btn-cancel">Hủy bỏ</a>
            <button type="submit" class="btn-save">Lưu Banner</button>
        </div>
    </div>

    @if ($errors->any())
        <div style="background-color: var(--danger-light); color: var(--danger); padding: 12px 18px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.15); margin-bottom: 20px; font-size: 0.9rem; font-weight: 500;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- General Info Card -->
    <div class="form-card">
        <div class="form-card-title">
            <i class="fa-solid fa-circle-info"></i>
            <span>Thông Tin Chung</span>
        </div>

        <div class="form-group">
            <label for="link">Đường dẫn liên kết (Link)</label>
            <div class="input-icon-wrapper">
                <input type="text" class="form-control" id="link" name="link" value="{{ old('link') }}" placeholder="Nhập link chuyển hướng khi click banner (Ví dụ: /shop, /shop?category=thuc-an-hat...)">
                <span class="input-icon-right">
                    <i class="fa-solid fa-link"></i>
                </span>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="description">Mô tả banner</label>
            <textarea class="form-control" id="description" name="description" rows="4" style="height: auto; resize: vertical;" placeholder="Nhập mô tả ngắn gọn hoặc nội dung text của banner này...">{{ old('description') }}</textarea>
        </div>
    </div>

    <!-- Image & Status Card -->
    <div class="form-card">
        <div class="form-card-title">
            <i class="fa-solid fa-image"></i>
            <span>Hình Ảnh & Trạng Thái</span>
        </div>

        <div class="banner-identity-grid">
            <!-- Left Side: Image upload -->
            <div class="form-group" style="margin-bottom: 0;">
                <label>Hình Ảnh Banner <span class="required">*</span></label>
                <div class="image-upload-wrapper" style="margin-top: 8px;">
                    <div class="upload-dropzone" id="dropzone" style="padding: 40px 16px; cursor: pointer;">
                        <div class="cloud-icon">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <p class="dropzone-text-primary">Kéo và thả hình ảnh vào đây hoặc click để chọn</p>
                        <p class="dropzone-text-sub">PNG, JPG, JPEG, GIF tối đa 5MB</p>
                        <input type="file" id="banner_image" name="image" style="display: none;" accept="image/*" required>
                    </div>

                    <!-- File preview card -->
                    <div class="file-preview-card" id="filePreview" style="display: none; margin-top: 14px;">
                        <div class="file-preview-left">
                            <div class="file-preview-img-container" style="width: 120px; height: 60px;">
                                <img src="" class="preview-img-fallback" id="previewImage" alt="Preview" style="object-fit: cover; width: 100%; height: 100%;">
                            </div>
                            <div class="file-preview-info">
                                <span class="file-preview-name" id="fileName">banner-thumbnail.png</span>
                                <span class="file-preview-size" id="fileSize">0.00 MB</span>
                            </div>
                        </div>
                        <button type="button" class="btn-delete-preview" id="btnDelete" title="Xóa hình ảnh">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Side: Status options -->
            <div class="form-group" style="margin-bottom: 0;">
                <label>Trạng thái hiển thị</label>
                <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 8px;">
                    <!-- Active card -->
                    <label class="brand-radio-card active" id="radioActiveCard">
                        <span class="custom-radio-container">
                            <input type="radio" name="status" value="active" checked id="radioActive">
                            <span class="radio-indicator"></span>
                            <span class="radio-label-title" style="margin-left: 8px;">Đang Hoạt Động</span>
                        </span>
                        <span class="radio-sub-label">Hiển thị công khai ngoài trang chủ khách hàng</span>
                    </label>

                    <!-- Inactive/Draft card -->
                    <label class="brand-radio-card" id="radioDraftCard">
                        <span class="custom-radio-container">
                            <input type="radio" name="status" value="draft" id="radioDraft">
                            <span class="radio-indicator"></span>
                            <span class="radio-label-title" style="margin-left: 8px;">Ngừng Hoạt Động</span>
                        </span>
                        <span class="radio-sub-label">Lưu nháp, tạm thời ẩn khỏi trang chủ</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Active radio styling toggle behavior
        const radioActive = document.getElementById('radioActive');
        const radioDraft = document.getElementById('radioDraft');
        const activeCard = document.getElementById('radioActiveCard');
        const draftCard = document.getElementById('radioDraftCard');

        function updateRadioCards() {
            if (radioActive.checked) {
                activeCard.classList.add('active');
                draftCard.classList.remove('active');
            } else {
                activeCard.classList.remove('active');
                draftCard.classList.add('active');
            }
        }

        if (radioActive && radioDraft) {
            radioActive.addEventListener('change', updateRadioCards);
            radioDraft.addEventListener('change', updateRadioCards);
        }

        // Dropzone interaction
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('banner_image');
        const filePreview = document.getElementById('filePreview');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const btnDelete = document.getElementById('btnDelete');

        if (dropzone && fileInput) {
            dropzone.addEventListener('click', function() {
                fileInput.click();
            });

            // Drag and drop events
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = 'var(--primary)';
                    dropzone.style.background = 'rgba(255, 120, 45, 0.04)';
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = 'var(--border-color)';
                    dropzone.style.background = 'transparent';
                }, false);
            });

            dropzone.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files[0]) {
                    fileInput.files = files;
                    handleFile(files[0]);
                }
            });

            fileInput.addEventListener('change', function() {
                if (fileInput.files && fileInput.files[0]) {
                    handleFile(fileInput.files[0]);
                }
            });
        }

        function handleFile(file) {
            if (fileName) fileName.textContent = file.name;
            if (fileSize) fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

            const reader = new FileReader();
            reader.onload = function(e) {
                if (previewImage) previewImage.src = e.target.result;
                if (filePreview) filePreview.style.display = 'flex';
            };
            reader.readAsDataURL(file);
        }

        if (btnDelete) {
            btnDelete.addEventListener('click', function(e) {
                e.stopPropagation();
                if (filePreview) filePreview.style.display = 'none';
                if (fileInput) fileInput.value = '';
            });
        }
    });
</script>
@endsection
