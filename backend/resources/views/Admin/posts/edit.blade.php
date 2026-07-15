@extends('admin.layouts.app')

@section('title', 'Sửa Bài viết')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<style>
    /* Two-column layout grid */
    .editor-layout-grid {
        display: grid;
        grid-template-columns: minmax(0, 2.3fr) minmax(0, 1fr);
        gap: 24px;
        align-items: start;
        margin-bottom: 40px;
    }
    
    @media (max-width: 1024px) {
        .editor-layout-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    }
    
    .form-card-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3ebe5;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        font-weight: 700;
        color: #5b5550;
        margin-bottom: 8px;
        font-size: 0.88rem;
    }
    
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        font-size: 0.92rem;
        color: var(--text-main);
        outline: none;
        transition: var(--transition);
        background: #fff;
    }
    
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.08);
    }
    
    .form-textarea {
        resize: vertical;
        min-height: 110px;
        line-height: 1.5;
    }
    
    .error-text {
        color: #dc2626;
        font-size: 0.8rem;
        margin-top: 6px;
        display: block;
        font-weight: 600;
    }
    
    /* Interactive Upload Card */
    .image-upload-card {
        border: 2px dashed #ebdcd0;
        border-radius: 12px;
        background: #faf8f6;
        cursor: pointer;
        transition: all 0.2s ease;
        overflow: hidden;
    }
    
    .image-upload-card:hover {
        border-color: var(--primary);
        background: #fffbf9;
    }
    
    .upload-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 170px;
        color: #8a7a70;
        padding: 20px;
        text-align: center;
    }
    
    .upload-placeholder i {
        font-size: 2.2rem;
        margin-bottom: 10px;
        color: var(--primary);
        opacity: 0.85;
    }
    
    .image-preview-wrapper {
        position: relative;
        height: 170px;
        width: 100%;
    }
    
    .image-preview-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .remove-preview-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(15, 23, 42, 0.7);
        color: #fff;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .remove-preview-btn:hover {
        background: #dc2626;
    }

    /* Quill custom styles */
    #editor-container {
        height: 420px;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        font-family: inherit;
        font-size: 0.95rem;
    }
    
    .ql-toolbar.ql-snow {
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        border-color: var(--border-color);
        background: #fafafa;
        padding: 10px 15px;
    }
    
    .ql-container.ql-snow {
        border-color: var(--border-color);
    }

    /* Prevent editor text stretching */
    .ql-editor {
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: pre-wrap !important;
    }
</style>
@endsection

@section('content')
<div class="dashboard-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div class="header-title-block">
        <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">Quản lý / Bài viết / Chỉnh sửa</div>
        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-main);">Chỉnh sửa bài viết</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Cập nhật thông tin bài viết "{{ $post->title }}".</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.posts') }}" class="btn-clear-filters" style="margin: 0; text-decoration: none; padding: 10px 20px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; font-weight: bold; background: #fff; border: 1px solid #ebdcd0; color: #5b5550; transition: all 0.2s;" onmouseover="this.style.borderColor='#999'" onmouseout="this.style.borderColor='#ebdcd0'">
            <i class="fa-solid fa-arrow-left-long"></i> Quay lại
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert-panel alert-danger-box" style="margin-bottom: 24px; background: #fef2f2; border: 1px solid #fca5a5; border-radius: 12px; padding: 16px 20px; color: #dc2626;">
        <div style="font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-exclamation"></i> Vui lòng kiểm tra và sửa các lỗi sau:
        </div>
        <ul style="margin: 0; padding-left: 20px; font-size: 0.88rem; line-height: 1.6;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="post-create-form" action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="editor-layout-grid">
        
        {{-- Cột Trái: Nội dung chính --}}
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="form-card">
                <div class="form-card-title">
                    <i class="fa-solid fa-pen-nib" style="color: var(--primary);"></i> Soạn thảo nội dung
                </div>
                
                {{-- Tiêu đề --}}
                <div class="form-group">
                    <label class="form-label" for="title">Tiêu đề bài viết <span style="color: #dc2626;">*</span></label>
                    <input type="text" id="title" name="title" class="form-input" value="{{ old('title', $post->title) }}" placeholder="Nhập tiêu đề hấp dẫn, chuẩn SEO..." required>
                    @error('title') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                {{-- Mô tả ngắn --}}
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" for="description">Mô tả ngắn (Tóm tắt) <span style="color: #dc2626;">*</span></label>
                    <textarea id="description" name="description" class="form-textarea" placeholder="Tóm tắt ngắn gọn nội dung chính bài viết (hiển thị trên thẻ bài viết bên ngoài trang chủ)..." maxlength="500" required>{{ old('description', $post->description) }}</textarea>
                    @error('description') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                {{-- Nội dung chi tiết --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Nội dung bài viết <span style="color: #dc2626;">*</span></label>
                    <div id="editor-container">{!! old('content', $post->content) !!}</div>
                    <input type="hidden" name="content" id="content" value="{{ old('content', $post->content) }}">
                    @error('content') <span class="error-text">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Cột Phải: Thiết lập & Ảnh bìa --}}
        <div style="display: flex; flex-direction: column; gap: 24px; position: sticky; top: 20px;">
            {{-- Thiết lập --}}
            <div class="form-card">
                <div class="form-card-title">
                    <i class="fa-solid fa-sliders" style="color: var(--primary);"></i> Cấu hình
                </div>

                {{-- Danh mục --}}
                <div class="form-group">
                    <label class="form-label" for="blog_category_id">Danh mục bài viết <span style="color: #dc2626;">*</span></label>
                    <select id="blog_category_id" name="blog_category_id" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('blog_category_id') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                {{-- Trạng thái --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="status">Trạng thái xuất bản <span style="color: #dc2626;">*</span></label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="active" @selected(old('status', $post->status) === 'active')>Xuất bản (Active)</option>
                        <option value="inactive" @selected(old('status', $post->status) === 'inactive')>Lưu nháp (Inactive)</option>
                    </select>
                    @error('status') <span class="error-text">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Ảnh bìa --}}
            <div class="form-card">
                <div class="form-card-title">
                    <i class="fa-regular fa-image" style="color: var(--primary);"></i> Ảnh bìa bài viết <span style="color: #dc2626;">*</span>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <div class="image-upload-card" onclick="document.getElementById('image').click()">
                        <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        
                        {{-- Placeholder --}}
                        <div id="upload-placeholder" class="upload-placeholder" style="{{ $post->image ? 'display: none;' : '' }}">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span style="font-size: 0.88rem; font-weight: 700; color: #2d2926;">Tải ảnh bìa mới</span>
                            <span style="font-size: 0.74rem; color: var(--text-muted); margin-top: 4px;">PNG, JPG, JPEG, GIF, WEBP tối đa 5MB</span>
                        </div>

                        {{-- Preview --}}
                        <div id="image-preview-wrapper" class="image-preview-wrapper" style="{{ $post->image ? 'display: block;' : 'display: none;' }}">
                            <img id="image-preview" src="{{ $post->image ? (filter_var($post->image, FILTER_VALIDATE_URL) ? $post->image : (str_starts_with($post->image, 'storage/') ? asset($post->image) : asset('storage/' . $post->image))) : '#' }}" alt="Preview">
                            <button type="button" class="remove-preview-btn" onclick="event.stopPropagation(); removePreview();">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    @error('image') <span class="error-text" style="margin-top: 10px;">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Nút lưu --}}
            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 8px;">
                <button type="submit" class="btn-dark-slate" style="width: 100%; padding: 14px; border-radius: 30px; font-weight: bold; font-size: 0.95rem; border: none; background: var(--primary); color: #fff; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(255, 120, 45, 0.25); display: inline-flex; align-items: center; justify-content: center; gap: 6px;" onmouseover="this.style.background='var(--primary-hover)'" onmouseout="this.style.background='var(--primary)'">
                    <i class="fa-solid fa-circle-check"></i> Cập nhật bài viết
                </button>
                <a href="{{ route('admin.posts') }}" class="btn-clear-filters" style="margin: 0; text-align: center; text-decoration: none; padding: 12px; border-radius: 30px; font-weight: bold; background: #fff; border: 1px solid #ebdcd0; color: #5b5550; font-size: 0.95rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#fbf9f7'" onmouseout="this.style.background='#fff'">
                    Hủy bỏ
                </a>
            </div>
        </div>

    </div>
</form>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    // Image Preview Handlers
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('upload-placeholder').style.display = 'none';
                document.getElementById('image-preview-wrapper').style.display = 'block';
                // Remove required status once a file is chosen
                document.getElementById('image').removeAttribute('required');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removePreview() {
        const input = document.getElementById('image');
        input.value = ''; // Reset input file
        input.setAttribute('required', 'required'); // Require file if old image was cleared
        document.getElementById('image-preview').src = '#';
        document.getElementById('image-preview-wrapper').style.display = 'none';
        document.getElementById('upload-placeholder').style.display = 'flex';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Nhập nội dung chi tiết bài viết ở đây...',
            modules: {
                toolbar: [
                    [{ header: [2, 3, 4, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        // Sync Quill editor to hidden input on submit
        const form = document.getElementById('post-create-form');
        form.addEventListener('submit', function(e) {
            const contentInput = document.querySelector('#content');
            if (quill.getText().trim() !== '') {
                contentInput.value = quill.root.innerHTML;
            } else {
                contentInput.value = '';
            }
        });
    });
</script>
@endsection
