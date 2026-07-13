@extends('admin.layouts.app')

@section('title', 'Thêm thương hiệu mới')

@section('styles')
<style>
    .brand-alert { margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; display: flex; align-items: flex-start; gap: 10px; font-size: 0.9rem; font-weight: 700; }
    .brand-alert-error { background: #fff1f1; border: 1px solid #ffd1d1; color: var(--danger); }
    .brand-alert ul { margin: 4px 0 0; padding-left: 18px; font-weight: 600; }
</style>
@endsection

@section('content')
@if($errors->any())
    <div class="brand-alert brand-alert-error">
        <i class="fa-solid fa-circle-exclamation" style="margin-top: 2px;"></i>
        <div>
            <div>Vui lòng kiểm tra lại thông tin thương hiệu.</div>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Dashboard Header Nav Bar -->
    <div class="dashboard-header" style="margin-bottom: 24px;">
        <div class="header-title-block">
            <h1 style="color: var(--text-main); font-weight: 700; font-size: 1.75rem;">Thêm Thương Hiệu Mới</h1>
        </div>
        <div class="header-actions" style="display: flex; gap: 12px;">
            <a href="{{ route('admin.brands') }}" class="btn-cancel">Hủy bỏ</a>
            <button type="submit" class="btn-save">Lưu Thương Hiệu</button>
        </div>
    </div>

    <!-- General Info Card -->
    <div class="form-card">
        <div class="form-card-title">
            <i class="fa-solid fa-circle-info"></i>
            <span>Thông Tin Chung</span>
        </div>

        <div class="form-group-row">
            <div class="form-group">
                <label for="name">Tên Thương Hiệu <span class="required">*</span></label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="Nhập tên thương hiệu">
            </div>
            <div class="form-group">
                <label for="slug">Đường dẫn (Slug)</label>
                <div class="input-icon-wrapper">
                    <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}" placeholder="brand-name-slug">
                    <span class="input-icon-right">
                        <i class="fa-solid fa-link"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="website">Địa chỉ Website</label>
            <input type="url" class="form-control" id="website" name="website" value="{{ old('website') }}" placeholder="https://example.com">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label>Mô tả chi tiết</label>
            <div class="editor-wrapper">
                <div class="editor-toolbar">
                    <button type="button" class="editor-btn" title="Bold"><i class="fa-solid fa-bold"></i></button>
                    <button type="button" class="editor-btn" title="Italic"><i class="fa-solid fa-italic"></i></button>
                    <button type="button" class="editor-btn" title="Bullet List"><i class="fa-solid fa-list-ul"></i></button>
                    <button type="button" class="editor-btn" title="Insert Link"><i class="fa-solid fa-link"></i></button>
                </div>
                <textarea class="editor-textarea" id="description" name="description" rows="6" placeholder="Nhập mô tả về câu chuyện thương hiệu của bạn...">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Brand Identity & Status Card -->
    <div class="form-card">
        <div class="form-card-title">
            <i class="fa-solid fa-image"></i>
            <span>Nhận Diện Thương Hiệu</span>
        </div>

        <div class="brand-identity-grid">
            <!-- Left Side: Logo upload -->
            <div class="form-group" style="margin-bottom: 0;">
                <label>Logo Thương Hiệu</label>
                <div class="image-upload-wrapper" style="margin-top: 8px;">
                    <div class="upload-dropzone" id="dropzone" style="padding: 30px 16px;">
                        <div class="cloud-icon">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <p class="dropzone-text-primary">Kéo và thả logo vào đây</p>
                        <p class="dropzone-text-sub">PNG, JPG tối đa 5MB</p>
                        <input type="file" id="brand_logo" name="image" style="display: none;" accept="image/*">
                    </div>

                    <!-- File preview card -->
                    <div class="file-preview-card" id="filePreview" style="display: none; margin-top: 14px;">
                        <div class="file-preview-left">
                            <div class="file-preview-img-container">
                                <img src="" class="preview-img-fallback" id="previewImage" alt="Preview">
                            </div>
                            <div class="file-preview-info">
                                <span class="file-preview-name" id="fileName">logo-thumbnail.png</span>
                                <span class="file-preview-size" id="fileSize">0.00 MB</span>
                            </div>
                        </div>
                        <button type="button" class="btn-delete-preview" id="btnDelete" title="Xóa hình ảnh">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Side: Status option list -->
            <div class="form-group" style="margin-bottom: 0;">
                <label>Trạng thái hoạt động</label>
                <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 8px;">
                    <!-- Active card -->
                    <label class="brand-radio-card active" id="radioActiveCard">
                        <span class="custom-radio-container">
                            <input type="radio" name="status" value="active" checked id="radioActive">
                            <span class="radio-indicator"></span>
                            <span class="radio-label-title" style="margin-left: 8px;">Đang Hoạt Động</span>
                        </span>
                        <span class="radio-sub-label">Hiển thị công khai</span>
                    </label>

                    <!-- Inactive/Draft card -->
                    <label class="brand-radio-card" id="radioDraftCard">
                        <span class="custom-radio-container">
                            <input type="radio" name="status" value="draft" id="radioDraft">
                            <span class="radio-indicator"></span>
                            <span class="radio-label-title" style="margin-left: 8px;">Ngừng Hoạt Động</span>
                        </span>
                        <span class="radio-sub-label">Lưu nháp / Ẩn</span>
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
        const titleInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        // Automatically generate slug on name input
        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function() {
                let slug = titleInput.value.toLowerCase();
                slug = slug.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                slug = slug.replace(/[đĐ]/g, 'd');
                slug = slug.replace(/[^a-z0-9\s-]/g, '');
                slug = slug.replace(/[\s-]+/g, '-');
                slug = slug.replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            });
        }

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
        const fileInput = document.getElementById('brand_logo');
        const filePreview = document.getElementById('filePreview');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const btnDelete = document.getElementById('btnDelete');

        if (dropzone && fileInput) {
            dropzone.addEventListener('click', function() {
                fileInput.click();
            });

            fileInput.addEventListener('change', function() {
                if (fileInput.files && fileInput.files[0]) {
                    const file = fileInput.files[0];
                    if (fileName) fileName.textContent = file.name;
                    if (fileSize) fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (previewImage) previewImage.src = e.target.result;
                        if (filePreview) filePreview.style.display = 'flex';
                    };
                    reader.readAsDataURL(file);
                }
            });
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
