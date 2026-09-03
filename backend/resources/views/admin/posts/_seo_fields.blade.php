<div class="pe-card pe-seo-fields-card" id="pe-seo-fields-card">
    <div class="pe-card-head">
        <i class="fa-solid fa-magnifying-glass-chart"></i>
        <h2>SEO cơ bản</h2>
    </div>
    <div class="pe-card-body">
        <div class="pe-field">
            <label class="pe-label" for="focus_keyword">Từ khóa chính</label>
            <input type="text" id="focus_keyword" name="focus_keyword" class="pe-input" maxlength="120"
                   value="{{ old('focus_keyword', $post->focus_keyword) }}"
                   placeholder="Ví dụ: cách chọn thức ăn cho mèo con">
            <div class="pe-help">Một cụm từ mô tả đúng điều người đọc đang tìm kiếm.</div>
            @error('focus_keyword') <span class="pe-error">{{ $message }}</span> @enderror
        </div>

        <div class="pe-field">
            <label class="pe-label" for="seo_title">SEO title <span class="pe-counter" id="pe-seo-title-counter">0/70</span></label>
            <input type="text" id="seo_title" name="seo_title" class="pe-input" maxlength="70"
                   value="{{ old('seo_title', $post->seo_title) }}"
                   placeholder="Để trống để dùng tiêu đề bài viết">
            <div class="pe-help">Nên từ 30–60 ký tự. Nếu để trống, website dùng tiêu đề bài viết.</div>
            @error('seo_title') <span class="pe-error">{{ $message }}</span> @enderror
        </div>

        <div class="pe-field">
            <label class="pe-label" for="meta_description">Meta description <span class="pe-counter" id="pe-meta-description-counter">0/180</span></label>
            <textarea id="meta_description" name="meta_description" class="pe-textarea pe-seo-textarea" maxlength="180"
                      placeholder="Để trống để dùng mô tả ngắn hiện tại">{{ old('meta_description', $post->meta_description) }}</textarea>
            <div class="pe-help">Nên từ 120–160 ký tự, tóm tắt lợi ích chính của bài viết.</div>
            @error('meta_description') <span class="pe-error">{{ $message }}</span> @enderror
        </div>

        <div class="pe-field">
            <label class="pe-label" for="secondary_keywords">Từ khóa phụ</label>
            <input type="text" id="secondary_keywords" name="secondary_keywords" class="pe-input" maxlength="700"
                   value="{{ $secondaryKeywords }}"
                   placeholder="Mèo con, dinh dưỡng mèo, thức ăn hạt">
            <div class="pe-help">Phân tách bằng dấu phẩy, tối đa 6 cụm từ. Không cần nhồi vào bài viết.</div>
            @error('secondary_keywords') <span class="pe-error">{{ $message }}</span> @enderror
        </div>

        <div class="pe-field">
            <label class="pe-label" for="search_intent">Ý định tìm kiếm</label>
            <select id="search_intent" name="search_intent" class="pe-select">
                <option value="">— Chưa xác định —</option>
                <option value="informational" @selected(old('search_intent', $post->search_intent) === 'informational')>Tìm hiểu / hướng dẫn</option>
                <option value="commercial" @selected(old('search_intent', $post->search_intent) === 'commercial')>Cân nhắc / so sánh</option>
                <option value="transactional" @selected(old('search_intent', $post->search_intent) === 'transactional')>Mua hàng</option>
                <option value="navigational" @selected(old('search_intent', $post->search_intent) === 'navigational')>Tìm một trang cụ thể</option>
            </select>
            <div class="pe-help">Giúp AI và người viết giữ đúng mục tiêu của bài.</div>
            @error('search_intent') <span class="pe-error">{{ $message }}</span> @enderror
        </div>

        <div class="pe-field">
            <label class="pe-label" for="canonical_url">Đường dẫn canonical</label>
            <input type="url" id="canonical_url" name="canonical_url" class="pe-input" maxlength="255"
                   value="{{ old('canonical_url', $post->canonical_url) }}"
                   placeholder="Để trống nếu đây là bài viết gốc">
            <div class="pe-help">Chỉ điền khi bài viết đăng lại từ nguồn khác, để Google biết bản gốc nằm ở đâu.</div>
            @error('canonical_url') <span class="pe-error">{{ $message }}</span> @enderror
        </div>

        <label class="pe-status-option">
            <input type="hidden" name="noindex" value="0">
            <input type="checkbox" name="noindex" value="1" @checked(old('noindex', $post->noindex))>
            <span>
                <strong>Không cho Google lập chỉ mục (noindex)</strong>
                <small>Bài viết vẫn xem được qua link nhưng sẽ không xuất hiện trên kết quả tìm kiếm.</small>
            </span>
        </label>
        @error('noindex') <span class="pe-error">{{ $message }}</span> @enderror

        <div class="pe-google-preview" id="pe-google-preview" data-base-url="{{ $siteBase }}">
            <div class="pe-google-preview-label"><i class="fa-brands fa-google"></i> Xem trước trên Google</div>
            <div class="pe-google-preview-site"><span class="pe-google-preview-favicon">P</span><span>PetWorld</span></div>
            <div class="pe-google-preview-url" id="pe-google-preview-url"></div>
            <div class="pe-google-preview-title" id="pe-google-preview-title">Tiêu đề bài viết</div>
            <div class="pe-google-preview-description" id="pe-google-preview-description">Mô tả bài viết sẽ xuất hiện tại đây.</div>
        </div>
    </div>
</div>
