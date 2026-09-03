@php
    /**
     * Form dùng chung cho màn "Viết bài viết mới" và "Chỉnh sửa bài viết".
     * Các trường bám đúng các cột của bảng `blogs` trong ERD:
     * blog_category_id, user_id, title, slug, description, content, view_count, image, status.
     */
    $isEdit = $post->exists;

    $coverUrl = null;
    if ($post->image) {
        if (filter_var($post->image, FILTER_VALIDATE_URL)) {
            $coverUrl = $post->image;
        } elseif (str_starts_with($post->image, 'uploads/') || str_starts_with($post->image, 'image/') || str_starts_with($post->image, 'storage/')) {
            $coverUrl = asset($post->image);
        } else {
            $coverUrl = asset('storage/' . $post->image);
        }
    }

    $siteBase = rtrim(config('app.frontend_url'), '/') . '/news/';
    $currentStatus = old('status', $post->status ?: 'active');
    $currentSlug = old('slug', $post->slug);
    $secondaryKeywords = old('secondary_keywords', implode(', ', $post->secondary_keywords ?? []));
@endphp

@if ($errors->any())
    <div class="pe-alert pe-alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i>
        <div>
            <strong>Vui lòng kiểm tra lại các thông tin sau:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div id="pe-draft-bar" class="pe-alert pe-alert-info" style="display: none;">
    <i class="fa-solid fa-clock-rotate-left"></i>
    <div>
        <strong>Có nội dung soạn dở chưa lưu.</strong>
        <div style="font-size: 0.79rem; margin-top: 2px;">Bản nháp tạm được lưu trên trình duyệt lúc <span id="pe-draft-time">—</span>.</div>
    </div>
    <div class="pe-alert-actions">
        <button type="button" id="pe-draft-restore">Khôi phục</button>
        <button type="button" id="pe-draft-discard">Bỏ qua</button>
    </div>
</div>

<div class="pe-exit-modal" id="pe-exit-modal" role="dialog" aria-modal="true" aria-labelledby="pe-exit-title" hidden>
    <div class="pe-exit-dialog">
        <div class="pe-exit-icon"><i class="fa-solid fa-pen-to-square"></i></div>
        <h2 id="pe-exit-title">Bạn có thay đổi chưa lưu</h2>
        <p>Nội dung trên form chưa được lưu thành bài viết. Bạn muốn tiếp tục soạn, lưu nháp trên trình duyệt rồi rời đi, hay rời mà không lưu?</p>
        <div class="pe-exit-actions">
            <button type="button" class="pe-btn" id="pe-exit-stay">Ở lại tiếp tục soạn</button>
            <button type="button" class="pe-btn pe-btn-primary" id="pe-exit-save-draft">Lưu nháp &amp; rời đi</button>
            <button type="button" class="pe-exit-discard" id="pe-exit-discard">Rời đi không lưu</button>
        </div>
    </div>
</div>

<form id="pe-form" method="POST"
      action="{{ $isEdit ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="pe-grid">
        {{-- ============ CỘT TRÁI: nội dung bài viết ============ --}}
        <div class="pe-col">
            <div class="pe-card">
                <div class="pe-card-head">
                    <i class="fa-solid fa-pen-nib"></i>
                    <h2>Thông tin bài viết</h2>
                </div>
                <div class="pe-card-body">
                    {{-- blogs.title --}}
                    <div class="pe-field">
                        <label class="pe-label" for="title">
                            Tiêu đề bài viết <span class="pe-req">*</span>
                            <span class="pe-counter" id="pe-title-counter">0 ký tự</span>
                        </label>
                        <input type="text" id="title" name="title" class="pe-input pe-input-title" maxlength="255"
                               value="{{ old('title', $post->title) }}"
                               placeholder="Ví dụ: 7 lưu ý khi chọn thức ăn cho mèo con dưới 6 tháng" required>
                        <div class="pe-help">Độ dài lý tưởng cho SEO là 30–60 ký tự.</div>
                        @error('title') <span class="pe-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- blogs.slug --}}
                    <div class="pe-field">
                        <label class="pe-label" for="slug">
                            Đường dẫn (slug)
                            <span class="pe-counter" id="pe-slug-state">Tự động theo tiêu đề</span>
                        </label>
                        <div class="pe-slug-row">
                            <span class="pe-slug-prefix" title="{{ $siteBase }}">{{ $siteBase }}</span>
                            <input type="text" id="slug" name="slug" class="pe-input" maxlength="255"
                                   value="{{ $currentSlug }}" placeholder="tu-dong-tao-tu-tieu-de"
                                   {{ $isEdit ? '' : 'readonly' }}>
                            <button type="button" id="pe-slug-toggle"
                                    class="pe-btn pe-slug-lock {{ $isEdit ? '' : 'is-auto' }}"
                                    title="Bật/tắt chỉnh sửa đường dẫn">
                                <i class="fa-solid {{ $isEdit ? 'fa-pen' : 'fa-lock' }}"></i>
                            </button>
                        </div>
                        <div class="pe-help">
                            Chỉ dùng chữ thường không dấu, số và dấu gạch ngang. Để trống hệ thống sẽ tự sinh từ tiêu đề.
                            @if($isEdit) <strong>Lưu ý:</strong> đổi đường dẫn sẽ làm hỏng các liên kết cũ đã chia sẻ. @endif
                        </div>
                        @error('slug') <span class="pe-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- blogs.description --}}
                    <div class="pe-field">
                        <label class="pe-label" for="description">
                            Mô tả ngắn (tóm tắt) <span class="pe-req">*</span>
                            <span class="pe-counter" id="pe-desc-counter">0/500</span>
                        </label>
                        <textarea id="description" name="description" class="pe-textarea" maxlength="500"
                                  placeholder="Tóm tắt nội dung chính, hiển thị trên danh sách tin tức và kết quả tìm kiếm..."
                                  required>{{ old('description', $post->description) }}</textarea>
                        <div class="pe-help">Nên viết 120–160 ký tự để hiển thị đầy đủ trên Google.</div>
                        @error('description') <span class="pe-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <section class="pe-card pe-ai-card" id="pe-ai-card" data-ai-url="{{ route('admin.posts.ai.improve') }}">
                <div class="pe-card-head pe-ai-head">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <h2>Trợ lý nội dung AI</h2>
                    <button type="button" class="pe-ai-toggle" id="pe-ai-toggle" aria-expanded="true">Thu gọn</button>
                </div>
                <div class="pe-ai-body" id="pe-ai-body">
                    <p class="pe-ai-note">AI chỉ đề xuất trên form. Bạn xem, áp dụng từng phần và tự bấm <strong>Lưu bài viết</strong> khi đã kiểm tra.</p>
                    <div class="pe-ai-options">
                        <label>Độ dài
                            <select id="pe-ai-length" class="pe-select">
                                <option value="standard">Tiêu chuẩn</option>
                                <option value="detailed">Chi tiết</option>
                            </select>
                        </label>
                        <label>Giọng văn
                            <select id="pe-ai-tone" class="pe-select">
                                <option value="friendly">Thân thiện</option>
                                <option value="professional">Chuyên nghiệp</option>
                            </select>
                        </label>
                    </div>
                    <div class="pe-ai-actions">
                        <button type="button" class="pe-ai-action" data-ai-action="generate_post_draft">Viết bản nháp từ tiêu đề</button>
                        <button type="button" class="pe-ai-action" data-ai-action="improve_post_content">Cải thiện toàn bài</button>
                        <button type="button" class="pe-ai-action" data-ai-action="rewrite_intro">Viết lại mở bài</button>
                        <button type="button" class="pe-ai-action" data-ai-action="generate_seo_meta">Tạo SEO title &amp; meta</button>
                        <button type="button" class="pe-ai-action" data-ai-action="audit_seo">Kiểm tra SEO bằng AI</button>
                    </div>
                    <div class="pe-ai-status" id="pe-ai-status" aria-live="polite" hidden></div>
                    <div class="pe-ai-result" id="pe-ai-result" hidden></div>
                    <button type="button" class="pe-btn pe-ai-undo" id="pe-ai-undo" hidden>Hoàn tác đề xuất AI</button>
                </div>
            </section>

            {{-- blogs.content --}}
            <div class="pe-card">
                <div class="pe-card-head">
                    <i class="fa-solid fa-align-left"></i>
                    <h2>Nội dung chi tiết <span class="pe-req">*</span></h2>
                    <span class="pe-card-hint">Dùng tiêu đề H2/H3 để chia đoạn cho dễ đọc</span>
                </div>
                <div class="pe-card-body is-flush">
                    <div class="pe-editor-wrap">
                        <div id="pe-editor">{!! old('content', $post->content) !!}</div>
                    </div>
                    <div class="pe-editor-foot">
                        <span><i class="fa-solid fa-font"></i> <b id="pe-word-count">0</b>&nbsp;từ</span>
                        <span><i class="fa-solid fa-text-width"></i> <b id="pe-char-count">0</b>&nbsp;ký tự</span>
                        <span><i class="fa-regular fa-clock"></i> Đọc khoảng <b id="pe-read-time">0</b>&nbsp;phút</span>
                        <span class="pe-foot-right" id="pe-autosave-state">Chưa có thay đổi</span>
                    </div>
                </div>
                <input type="hidden" name="content" id="content" value="{{ old('content', $post->content) }}">
                @error('content')
                    <div style="padding: 0 20px 16px;"><span class="pe-error">{{ $message }}</span></div>
                @enderror
            </div>

            @include('admin.posts._seo_fields')
        </div>

        {{-- ============ CỘT PHẢI: cấu hình ============ --}}
        <div class="pe-col pe-col-side">
            {{-- blogs.status --}}
            <div class="pe-card">
                <div class="pe-card-head">
                    <i class="fa-solid fa-paper-plane"></i>
                    <h2>Xuất bản</h2>
                </div>
                <div class="pe-card-body">
                    <div class="pe-status-list">
                        <label class="pe-status-option">
                            <input type="radio" name="status" value="active" @checked($currentStatus === 'active')>
                            <span>
                                <strong>Xuất bản</strong>
                                <small>Bài viết hiển thị công khai trên trang tin tức PetWorld.</small>
                            </span>
                        </label>
                        <label class="pe-status-option">
                            <input type="radio" name="status" value="inactive" @checked($currentStatus === 'inactive')>
                            <span>
                                <strong>Bản nháp</strong>
                                <small>Chỉ quản trị viên nhìn thấy, khách hàng không truy cập được.</small>
                            </span>
                        </label>
                    </div>
                    @error('status') <span class="pe-error">{{ $message }}</span> @enderror

                    {{-- blogs.published_at --}}
                    <div class="pe-field" style="margin-top: 14px;">
                        <label class="pe-label" for="published_at">Ngày xuất bản</label>
                        <input type="datetime-local" id="published_at" name="published_at" class="pe-input"
                               value="{{ old('published_at', ($post->published_at ?: ($isEdit ? $post->created_at : now()))?->format('Y-m-d\TH:i')) }}">
                        <div class="pe-help">Hiển thị trên bài viết và gửi cho Google qua schema Article.</div>
                        @error('published_at') <span class="pe-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="pe-actions" style="margin-top: 18px;">
                        <button type="submit" class="pe-btn pe-btn-primary pe-btn-block" id="pe-submit">
                            <i class="fa-solid fa-circle-check"></i>
                            <span id="pe-submit-label">{{ $isEdit ? 'Cập nhật bài viết' : 'Lưu & Xuất bản' }}</span>
                        </button>
                        <button type="button" class="pe-btn pe-btn-block" id="pe-preview-open">
                            <i class="fa-regular fa-eye"></i> Xem trước
                        </button>
                        <a href="{{ route('admin.posts') }}" class="pe-btn pe-btn-ghost pe-btn-block">Hủy bỏ</a>
                    </div>
                </div>
            </div>

            {{-- blogs.blog_category_id --}}
            <div class="pe-card">
                <div class="pe-card-head">
                    <i class="fa-solid fa-folder-open"></i>
                    <h2>Danh mục</h2>
                </div>
                <div class="pe-card-body">
                    <div class="pe-field">
                        <label class="pe-label" for="blog_category_id">Danh mục bài viết <span class="pe-req">*</span></label>
                        <select id="blog_category_id" name="blog_category_id" class="pe-select" required>
                            <option value="">— Chọn danh mục —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                        @selected(old('blog_category_id', $post->blog_category_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($categories->isEmpty())
                            <div class="pe-help">Chưa có danh mục nào đang hoạt động. Hãy tạo danh mục bài viết trước.</div>
                        @endif
                        @error('blog_category_id') <span class="pe-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- blogs.image --}}
            <div class="pe-card">
                <div class="pe-card-head">
                    <i class="fa-regular fa-image"></i>
                    <h2>Ảnh bìa @unless($isEdit)<span class="pe-req">*</span>@endunless</h2>
                </div>
                <div class="pe-card-body">
                    <div id="pe-dropzone" class="pe-dropzone {{ $coverUrl ? 'has-image' : '' }}" role="button" tabindex="0">
                        <input type="file" id="image" name="image" accept="image/*" hidden
                               {{ $isEdit ? '' : 'required' }}>
                        <div class="pe-dropzone-empty">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <strong>Kéo thả hoặc nhấn để chọn ảnh</strong>
                            <small>JPG, PNG, GIF, WEBP · tối đa 5MB</small>
                            <small>Tỷ lệ đẹp nhất: 16:9</small>
                        </div>
                        <div class="pe-dropzone-preview">
                            <img id="pe-cover-preview" src="{{ $coverUrl ?: '' }}" alt="Ảnh bìa bài viết">
                            <div class="pe-image-actions">
                                <button type="button" class="pe-image-btn" id="pe-cover-change" title="Chọn ảnh khác">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="pe-image-meta" id="pe-cover-meta">
                        <i class="fa-regular fa-file-image"></i>
                        <span id="pe-cover-name">{{ $isEdit && $post->image ? basename($post->image) : '' }}</span>
                    </div>
                    @if($isEdit)
                        <div class="pe-help">Không chọn ảnh mới thì hệ thống giữ nguyên ảnh bìa hiện tại.</div>
                    @endif
                    <div class="pe-field pe-cover-alt-field">
                        <label class="pe-label" for="cover_alt">Alt ảnh bìa</label>
                        <input type="text" id="cover_alt" name="cover_alt" class="pe-input" maxlength="255"
                               value="{{ old('cover_alt', $post->cover_alt) }}"
                               placeholder="Mô tả đúng nội dung ảnh bìa">
                        <div class="pe-help">Hỗ trợ khả năng tiếp cận và tìm kiếm hình ảnh. Không cần mở đầu bằng “ảnh của”.</div>
                        @error('cover_alt') <span class="pe-error">{{ $message }}</span> @enderror
                    </div>
                    @error('image') <span class="pe-error">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Trợ lý tối ưu nội dung --}}
            <div class="pe-card">
                <div class="pe-card-head">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <h2>Gợi ý tối ưu</h2>
                </div>
                <div class="pe-card-body">
                    <div class="pe-seo-head">
                        <div class="pe-seo-ring" id="pe-seo-ring"><b id="pe-seo-score">0</b></div>
                        <div>
                            <strong id="pe-seo-label">Cần bổ sung</strong>
                            <small>Mức độ hoàn thiện của bài viết</small>
                        </div>
                    </div>
                    <ul class="pe-seo-list" id="pe-seo-list"></ul>
                    <button type="button" class="pe-seo-show-all" id="pe-seo-show-all" hidden>Xem toàn bộ kiểm tra</button>
                </div>
            </div>

            {{-- Thông tin hệ thống: user_id, view_count, timestamps, Comments --}}
            @if($isEdit)
                <div class="pe-card">
                    <div class="pe-card-head">
                        <i class="fa-solid fa-circle-info"></i>
                        <h2>Thông tin bài viết</h2>
                    </div>
                    <div class="pe-card-body">
                        <div class="pe-meta-list">
                            <div class="pe-meta-row">
                                <i class="fa-regular fa-user"></i><span>Tác giả</span>
                                <strong>{{ $post->author?->name ?? 'Admin' }}</strong>
                            </div>
                            <div class="pe-meta-row">
                                <i class="fa-regular fa-eye"></i><span>Lượt xem</span>
                                <strong>{{ number_format($post->view_count) }}</strong>
                            </div>
                            <div class="pe-meta-row">
                                <i class="fa-regular fa-comments"></i><span>Bình luận</span>
                                <strong>{{ number_format($post->comments()->count()) }}</strong>
                            </div>
                            <div class="pe-meta-row">
                                <i class="fa-regular fa-calendar-plus"></i><span>Ngày tạo</span>
                                <strong>{{ $post->created_at?->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                            <div class="pe-meta-row">
                                <i class="fa-regular fa-pen-to-square"></i><span>Cập nhật</span>
                                <strong>{{ $post->updated_at?->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</form>

{{-- ============ Hộp xem trước ============ --}}
<div class="pe-modal" id="pe-preview-modal" role="dialog" aria-modal="true" aria-label="Xem trước bài viết">
    <div class="pe-modal-box">
        <div class="pe-modal-head">
            <i class="fa-regular fa-eye" style="color: var(--primary);"></i>
            <strong>Xem trước bài viết</strong>
            <button type="button" id="pe-preview-close" aria-label="Đóng"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <article class="pe-preview">
            <img class="pe-preview-cover" id="pe-preview-cover" src="{{ $coverUrl ?: '' }}" alt="" style="{{ $coverUrl ? '' : 'display:none' }}">
            <span class="pe-preview-badge" id="pe-preview-category">Chưa chọn danh mục</span>
            <h1 id="pe-preview-title">Tiêu đề bài viết</h1>
            <div class="pe-preview-meta">
                <span><i class="fa-regular fa-user"></i> {{ $isEdit ? ($post->author?->name ?? 'Admin') : (auth()->user()?->name ?? 'Admin') }}</span>
                <span><i class="fa-regular fa-calendar"></i> {{ ($isEdit ? $post->created_at : now())?->format('d/m/Y') }}</span>
                <span><i class="fa-regular fa-clock"></i> <b id="pe-preview-readtime">0</b> phút đọc</span>
            </div>
            <div class="pe-preview-desc" id="pe-preview-desc">Mô tả ngắn của bài viết sẽ hiển thị tại đây.</div>
            <div class="pe-preview-body" id="pe-preview-body"></div>
        </article>
    </div>
</div>
