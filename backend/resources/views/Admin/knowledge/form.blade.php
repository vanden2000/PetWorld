@extends('admin.layouts.app')
@section('title', $article->exists ? 'Sửa kiến thức chatbot' : 'Thêm kiến thức chatbot')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <style>
        .knowledge-quill-editor { min-height: 420px; background: #fff; border-radius: 8px; }
        .knowledge-quill-editor .ql-editor { min-height: 380px; font-size: 0.92rem; line-height: 1.6; }
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
        .knowledge-preview { border: 1px solid #f3d8c7; border-radius: 12px; padding: 20px; background: #fffdfa; }
        .knowledge-preview h4 { display:flex; align-items:center; gap:8px; margin:0 0 14px; color:var(--text-main); font-size:.95rem; }
        .knowledge-preview h4 i { color:#ff782d; }
        .knowledge-preview-status { display:inline-flex; margin-bottom:12px; padding:5px 9px; border-radius:999px; background:#fff1e8; color:#b95008; font-size:.75rem; font-weight:800; }
        .knowledge-preview-label { margin:12px 0 4px; color:var(--text-muted); font-size:.72rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
        .knowledge-preview-value { color:var(--text-main); font-size:.88rem; line-height:1.5; white-space:pre-wrap; }
        .knowledge-question-row { display:flex; align-items:center; gap:8px; margin-bottom:8px; }
        .knowledge-question-row .form-control { flex:1; }
        .knowledge-question-remove { flex:none; width:38px; height:38px; border:1px solid #f4caca; border-radius:8px; background:#fffafa; color:#dc2626; cursor:pointer; }
        .knowledge-add-question { display:inline-flex; align-items:center; gap:6px; margin-top:2px; padding:8px 11px; border:1px solid #f3d8c7; border-radius:8px; background:#fff; color:#b95008; font:inherit; font-size:.84rem; font-weight:700; cursor:pointer; }
        .knowledge-publish-check { border:1px solid #f3d8c7; border-radius:12px; padding:16px 20px; background:#fffaf5; }
        .knowledge-publish-check h4 { margin:0 0 9px; color:#8f4b20; font-size:.9rem; }
        .knowledge-publish-check ul { margin:0; padding-left:18px; color:#8f4b20; font-size:.84rem; line-height:1.55; }
        .knowledge-publish-check.is-ready { border-color:#b8ead3; background:#f4fbf7; }
        .knowledge-publish-check.is-ready h4, .knowledge-publish-check.is-ready ul { color:#16734a; }
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
                    <label for="summary">Tóm tắt cho chatbot</label>
                    <textarea class="form-control @error('summary') is-invalid @enderror" id="summary" name="summary" rows="4" maxlength="1000" placeholder="Tóm tắt câu trả lời chính mà chatbot nên ưu tiên...">{{ old('summary', $article->summary) }}</textarea>
                    <div class="species-help" style="margin-top:6px;">Nêu ý chính, điều kiện hoặc mốc thời gian quan trọng trong 1–3 câu.</div>
                    @error('summary')<div class="error-message">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="category">Nhóm kiến thức <span class="required" style="color: #d93025;">*</span></label>
                    <select class="form-control @error('category') is-invalid @enderror" id="category" name="category" required>
                        @foreach(\App\Models\KnowledgeArticle::CATEGORIES as $value=>$label)
                            <option value="{{ $value }}" @selected(old('category',$article->category)===$value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="content">Nội dung đã kiểm duyệt <span class="required" style="color: #d93025;">*</span></label>
                    <textarea id="content" name="content" required hidden>{{ old('content', $article->content) }}</textarea>
                    <div id="knowledge-editor" class="knowledge-quill-editor"></div>
                    <div class="species-help" style="margin-top:6px;">Soạn nội dung dạng HTML. Nội dung này hiển thị trên trang chính sách/hướng dẫn và là nguồn trả lời chính sách của chatbot.</div>
                    @error('content')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label>Câu hỏi khách thường hỏi</label>
                    <div id="knowledge-questions">
                        @forelse(old('questions', $article->questions ?? []) as $question)
                            <div class="knowledge-question-row">
                                <input type="text" class="form-control" name="questions[]" value="{{ $question }}" maxlength="200" placeholder="Ví dụ: Bao lâu thì tôi nhận được đơn hàng?">
                                <button type="button" class="knowledge-question-remove" aria-label="Xóa câu hỏi" title="Xóa câu hỏi"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        @empty
                            <div class="knowledge-question-row">
                                <input type="text" class="form-control" name="questions[]" maxlength="200" placeholder="Ví dụ: Bao lâu thì tôi nhận được đơn hàng?">
                                <button type="button" class="knowledge-question-remove" aria-label="Xóa câu hỏi" title="Xóa câu hỏi"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        @endforelse
                    </div>
                    <button type="button" id="knowledge-add-question" class="knowledge-add-question"><i class="fa-solid fa-plus"></i> Thêm câu hỏi</button>
                    <div class="species-help" style="margin-top:8px;">Mỗi câu hỏi là một cách khách có thể diễn đạt cùng một nhu cầu.</div>
                    @error('questions.*')<div class="error-message">{{ $message }}</div>@enderror
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

            <aside class="knowledge-preview" aria-live="polite">
                <h4><i class="fa-solid fa-robot"></i> Chatbot sẽ nhận</h4>
                <div class="knowledge-preview-status" id="knowledge-preview-status"></div>
                <div class="knowledge-preview-label">Tiêu đề</div>
                <div class="knowledge-preview-value" id="knowledge-preview-title">{{ old('title', $article->title) ?: 'Chưa có tiêu đề' }}</div>
                <div class="knowledge-preview-label">Nhóm kiến thức</div>
                <div class="knowledge-preview-value" id="knowledge-preview-category"></div>
                <div class="knowledge-preview-label">Tóm tắt</div>
                <div class="knowledge-preview-value" id="knowledge-preview-summary">{{ old('summary', $article->summary) ?: 'Chưa có tóm tắt' }}</div>
                <div class="knowledge-preview-label">Câu hỏi nhận diện</div>
                <div class="knowledge-preview-value" id="knowledge-preview-questions">Chưa có câu hỏi</div>
                <div class="knowledge-preview-label">Nội dung</div>
                <div class="knowledge-preview-value" id="knowledge-preview-content">{{ old('content', $article->content) ?: 'Chưa có nội dung' }}</div>
            </aside>

            <aside class="knowledge-publish-check" id="knowledge-publish-check" hidden aria-live="polite">
                <h4 id="knowledge-publish-check-title"></h4>
                <ul id="knowledge-publish-check-list"></ul>
            </aside>

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

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const title = document.getElementById('title');
    const category = document.getElementById('category');
    const content = document.getElementById('content');
    const summary = document.getElementById('summary');
    const statusInputs = document.querySelectorAll('input[name="status"]');

    /* Trình soạn thảo HTML (Quill) — đồng bộ về textarea ẩn #content. */
    const stripHtml = (html) => {
        const tmp = document.createElement('div');
        tmp.innerHTML = html ?? '';
        return tmp.textContent || '';
    };
    const quill = new Quill('#knowledge-editor', {
        theme: 'snow',
        placeholder: 'Nhập nội dung quy định chi tiết (tiêu đề, đoạn văn, danh sách)...',
        modules: {
            toolbar: [
                [{ header: [2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link', 'clean'],
            ],
        },
    });
    if (content?.value.trim()) {
        quill.clipboard.dangerouslyPasteHTML(content.value);
    }
    const previewStatus = document.getElementById('knowledge-preview-status');
    const previewTitle = document.getElementById('knowledge-preview-title');
    const previewCategory = document.getElementById('knowledge-preview-category');
    const previewSummary = document.getElementById('knowledge-preview-summary');
    const previewQuestions = document.getElementById('knowledge-preview-questions');
    const previewContent = document.getElementById('knowledge-preview-content');
    const publishCheck = document.getElementById('knowledge-publish-check');
    const publishCheckTitle = document.getElementById('knowledge-publish-check-title');
    const publishCheckList = document.getElementById('knowledge-publish-check-list');

    const syncPreview = () => {
        const status = document.querySelector('input[name="status"]:checked')?.value || 'draft';
        const statusLabels = {
            published: 'Đang dùng bởi chatbot',
            draft: 'Bản nháp · Chatbot không dùng',
            archived: 'Đã lưu trữ · Chatbot không dùng',
        };
        const text = stripHtml(content?.value || '').trim() || 'Chưa có nội dung';
        const summaryText = summary?.value.trim() || 'Chưa có tóm tắt';
        const questionTexts = [...document.querySelectorAll('input[name="questions[]"]')]
            .map((input) => input.value.trim())
            .filter(Boolean);

        previewStatus.textContent = statusLabels[status];
        previewTitle.textContent = title?.value.trim() || 'Chưa có tiêu đề';
        previewCategory.textContent = category?.selectedOptions[0]?.textContent || 'Chưa chọn nhóm';
        previewSummary.textContent = summaryText;
        previewQuestions.textContent = questionTexts.length ? questionTexts.map((question) => `• ${question}`).join('\n') : 'Chưa có câu hỏi';
        previewContent.textContent = text.length > 420 ? `${text.slice(0, 420)}…` : text;

        if (status !== 'published') {
            publishCheck.hidden = true;
            return;
        }

        const warnings = [];
        if (text.length < 100) warnings.push('Nội dung dưới 100 ký tự, chatbot có thể trả lời thiếu thông tin.');
        if (!summary?.value.trim()) warnings.push('Chưa có tóm tắt để chatbot ưu tiên câu trả lời chính.');
        if (!questionTexts.length) warnings.push('Chưa có câu hỏi nhận diện, chatbot có thể khó tìm đúng bài này.');
        publishCheck.hidden = false;
        publishCheck.classList.toggle('is-ready', warnings.length === 0);
        publishCheckTitle.textContent = warnings.length ? 'Kiểm tra trước khi xuất bản' : 'Sẵn sàng để chatbot sử dụng';
        publishCheckList.innerHTML = warnings.length ? warnings.map((warning) => `<li>${warning}</li>`).join('') : '<li>Đủ nội dung, tóm tắt và câu hỏi nhận diện.</li>';
    };

    [title, category, summary].forEach((field) => field?.addEventListener('input', syncPreview));
    category?.addEventListener('change', syncPreview);
    statusInputs.forEach((input) => input.addEventListener('change', syncPreview));
    const syncContent = () => { content.value = quill.getSemanticHTML(); };
    quill.on('text-change', () => { syncContent(); syncPreview(); });
    document.querySelector('form')?.addEventListener('submit', syncContent);
    syncPreview();

    const questions = document.getElementById('knowledge-questions');
    const addQuestion = document.getElementById('knowledge-add-question');
    const questionRow = () => {
        const row = document.createElement('div');
        row.className = 'knowledge-question-row';
        row.innerHTML = '<input type="text" class="form-control" name="questions[]" maxlength="200" placeholder="Ví dụ: Bao lâu thì tôi nhận được đơn hàng?"><button type="button" class="knowledge-question-remove" aria-label="Xóa câu hỏi" title="Xóa câu hỏi"><i class="fa-solid fa-trash-can"></i></button>';
        return row;
    };
    addQuestion?.addEventListener('click', () => questions?.appendChild(questionRow()));
    questions?.addEventListener('input', syncPreview);
    questions?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.knowledge-question-remove');
        if (!removeButton) return;
        const rows = questions.querySelectorAll('.knowledge-question-row');
        if (rows.length === 1) {
            rows[0].querySelector('input').value = '';
            syncPreview();
            return;
        }
        removeButton.closest('.knowledge-question-row').remove();
        syncPreview();
    });
});
</script>
@endsection
