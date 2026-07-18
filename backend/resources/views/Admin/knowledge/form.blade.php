@extends('admin.layouts.app')
@section('title', $article->exists ? 'Sửa kiến thức chatbot' : 'Thêm kiến thức chatbot')

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
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 20px;
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
    </style>
@endsection

@section('content')
<form method="POST" action="{{ $article->exists ? route('admin.knowledge.update', $article) : route('admin.knowledge.store') }}">
    @csrf
    @if($article->exists)
        @method('PUT')
    @endif

    <!-- Dashboard Header Nav Bar -->
    <div class="dashboard-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div class="header-title-block">
            <h1 style="color: var(--text-main); font-weight: 700; font-size: 1.75rem;">
                {{ $article->exists ? 'Sửa bài kiến thức' : 'Thêm bài kiến thức' }}
            </h1>
            <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.95rem;">
                Nội dung xuất bản sẽ là nguồn trả lời chính sách của chatbot.
            </p>
        </div>
        <div class="header-actions" style="display: flex; gap: 12px;">
            <a href="{{ route('admin.knowledge') }}" class="btn-cancel">Hủy</a>
            <button type="submit" class="btn-save">
                <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i>Lưu bài viết
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-panel alert-success-box" style="margin-bottom: 24px;">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Responsive Columns Grid -->
    <div class="category-create-grid">
        <!-- Left Main Form Column -->
        <div class="category-main-col">
            <!-- General Information Form Card -->
            <div class="form-card">
                <div class="form-card-title">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Thông tin chung</span>
                </div>

                <div class="form-group">
                    <label for="title">Tiêu đề bài viết <span class="required" style="color: #d93025;">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $article->title) }}" required placeholder="Ví dụ: Quy định thời gian giao hàng hỏa tốc">
                    @error('title')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="category">Nhóm kiến thức <span class="required" style="color: #d93025;">*</span></label>
                    <select class="form-control @error('category') is-invalid @enderror" id="category" name="category" required>
                        @foreach(['shipping'=>'Giao hàng','payment'=>'Thanh toán','returns'=>'Đổi trả','voucher'=>'Voucher','contact'=>'Liên hệ'] as $value=>$label)
                            <option value="{{ $value }}" @selected(old('category',$article->category)===$value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="content">Nội dung đã kiểm duyệt <span class="required" style="color: #d93025;">*</span></label>
                    <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="16" required placeholder="Nhập nội dung quy định chi tiết để chatbot trả lời khách hàng..." style="resize: vertical; font-family: inherit; line-height: 1.6;">{{ old('content', $article->content) }}</textarea>
                    @error('content')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Right Sidebar Form Column -->
        <div class="category-sidebar-col">
            <!-- Status Card -->
            <div class="form-card" style="padding: 24px;">
                <div class="form-card-title" style="margin-bottom: 16px; padding-bottom: 8px;">
                    <i class="fa-solid fa-toggle-on"></i>
                    <span>Trạng thái xuất bản</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <label class="custom-radio-container">
                        <input type="radio" name="status" value="published" {{ old('status', $article->status ?: 'draft') === 'published' ? 'checked' : '' }}>
                        <span class="radio-indicator"></span>
                        <div class="radio-label-details">
                            <span class="radio-label-title">Xuất bản (Published)</span>
                        </div>
                    </label>
                    <label class="custom-radio-container">
                        <input type="radio" name="status" value="draft" {{ old('status', $article->status ?: 'draft') === 'draft' ? 'checked' : '' }}>
                        <span class="radio-indicator"></span>
                        <div class="radio-label-details">
                            <span class="radio-label-title">Lưu nháp (Draft)</span>
                        </div>
                    </label>
                    <label class="custom-radio-container">
                        <input type="radio" name="status" value="archived" {{ old('status', $article->status ?: 'draft') === 'archived' ? 'checked' : '' }}>
                        <span class="radio-indicator"></span>
                        <div class="radio-label-details">
                            <span class="radio-label-title">Lưu trữ (Archived)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Tips Card -->
            <div style="background-color: #fff9e6; border: 1px solid #ffeeba; border-radius: 12px; padding: 20px;">
                <h4 style="color: #856404; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-lightbulb"></i> Hướng dẫn soạn nội dung:
                </h4>
                <ul style="color: #856404; font-size: 0.85rem; padding-left: 20px; line-height: 1.6; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                    <li><strong>Tiêu đề:</strong> Đặt tiêu đề rõ ràng, mô tả đúng nhóm nội dung (ví dụ: Chính sách hoàn trả trong 7 ngày).</li>
                    <li><strong>Phân nhóm:</strong> Chọn đúng nhóm chủ đề để chatbot dễ dàng phân loại kiến thức khi hội thoại.</li>
                    <li><strong>Nội dung:</strong> Diễn đạt rõ ràng, mạch lạc, tránh viết chung chung. Hãy cung cấp số liệu, quy định cụ thể để chatbot trả lời chính xác nhất.</li>
                </ul>
            </div>
        </div>
    </div>
</form>
@endsection
