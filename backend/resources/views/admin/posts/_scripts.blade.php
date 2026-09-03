<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
(function () {
    const CONFIG = @json($peConfig ?? []);

    /* ---------------- Trình soạn thảo ---------------- */
    const quill = new Quill('#pe-editor', {
        theme: 'snow',
        placeholder: 'Bắt đầu viết nội dung bài viết tại đây...',
        modules: {
            toolbar: [
                [{ header: [2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                ['blockquote', 'code-block'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ indent: '-1' }, { indent: '+1' }],
                [{ align: [] }],
                ['link', 'image', 'video'],
                ['clean'],
            ],
        },
    });

    const form = document.getElementById('pe-form');
    const contentInput = document.getElementById('content');
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const descInput = document.getElementById('description');
    const seoTitleInput = document.getElementById('seo_title');
    const metaDescriptionInput = document.getElementById('meta_description');
    const focusKeywordInput = document.getElementById('focus_keyword');
    const secondaryKeywordsInput = document.getElementById('secondary_keywords');
    const searchIntentSelect = document.getElementById('search_intent');
    const coverAltInput = document.getElementById('cover_alt');
    const categorySelect = document.getElementById('blog_category_id');
    const imageInput = document.getElementById('image');
    const dropzone = document.getElementById('pe-dropzone');
    const coverPreview = document.getElementById('pe-cover-preview');
    const coverName = document.getElementById('pe-cover-name');

    const editorHtml = () => quill.root.innerHTML;
    const editorText = () => quill.getText().replace(/\s+/g, ' ').trim();
    const isEmptyEditor = () => quill.getLength() <= 1 && !quill.root.querySelector('img, iframe');
    const wordCount = () => (editorText() ? editorText().split(' ').length : 0);
    const readTime = () => Math.max(1, Math.round(wordCount() / 200));

    /* ---------------- Slug ---------------- */
    const toSlug = (value) => value
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/[đĐ]/g, 'd')
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/[\s-]+/g, '-');

    // Bài mới: slug bám theo tiêu đề cho tới khi người dùng mở khóa để tự nhập.
    let slugAuto = !CONFIG.isEdit;
    const slugToggle = document.getElementById('pe-slug-toggle');
    const slugState = document.getElementById('pe-slug-state');

    const renderSlugState = () => {
        slugInput.readOnly = slugAuto;
        slugToggle.classList.toggle('is-auto', slugAuto);
        slugToggle.querySelector('i').className = slugAuto ? 'fa-solid fa-lock' : 'fa-solid fa-pen';
        slugState.textContent = slugAuto ? 'Tự động theo tiêu đề' : 'Đang chỉnh sửa thủ công';
    };

    slugToggle.addEventListener('click', () => {
        slugAuto = !slugAuto;
        if (slugAuto) slugInput.value = toSlug(titleInput.value);
        else slugInput.focus();
        renderSlugState();
        refresh();
    });

    slugInput.addEventListener('blur', () => {
        if (!slugAuto) slugInput.value = toSlug(slugInput.value);
        refresh();
    });

    /* ---------------- Bộ đếm ký tự ---------------- */
    const setCounter = (el, text, state) => {
        el.textContent = text;
        el.className = 'pe-counter' + (state ? ' ' + state : '');
    };

    /* ---------------- Trợ lý tối ưu ---------------- */
    const seoList = document.getElementById('pe-seo-list');
    const seoRing = document.getElementById('pe-seo-ring');
    const seoScoreEl = document.getElementById('pe-seo-score');
    const seoLabelEl = document.getElementById('pe-seo-label');
    const seoShowAll = document.getElementById('pe-seo-show-all');
    let isShowingAllSeoChecks = false;

    const refreshSeo = () => {
        const title = (seoTitleInput.value.trim() || titleInput.value.trim());
        const desc = (metaDescriptionInput.value.trim() || descInput.value.trim());
        const focusKeyword = focusKeywordInput.value.trim().toLocaleLowerCase('vi-VN');
        const html = editorHtml();
        const text = editorText();
        const words = wordCount();
        const normalizedText = text.toLocaleLowerCase('vi-VN');
        const keywordOccurrences = focusKeyword
            ? normalizedText.split(focusKeyword).length - 1
            : 0;
        const keywordDensity = words ? (keywordOccurrences / words) * 100 : 0;
        const activeSlug = slugInput.value.trim() || toSlug(titleInput.value);

        const checks = [
            { label: `SEO title dài 30–60 ký tự (hiện ${title.length})`, ok: title.length >= 30 && title.length <= 60, weight: 10, target: 'seo_title', priority: 5 },
            { label: `Meta description dài 120–160 ký tự (hiện ${desc.length})`, ok: desc.length >= 120 && desc.length <= 160, weight: 10, target: 'meta_description', priority: 5 },
            { label: 'Đã nhập từ khóa chính', ok: !!focusKeyword, weight: 5, target: 'focus_keyword', priority: 4 },
            { label: 'Từ khóa chính có trong đoạn mở đầu', ok: !!focusKeyword && normalizedText.slice(0, 500).includes(focusKeyword), weight: 5, target: 'content', priority: 3 },
            { label: 'Từ khóa chính có trong một tiêu đề H2/H3', ok: !!focusKeyword && Array.from(new DOMParser().parseFromString(html, 'text/html').querySelectorAll('h2, h3')).some((heading) => heading.textContent.toLocaleLowerCase('vi-VN').includes(focusKeyword)), weight: 5, target: 'content', priority: 3 },
            { label: 'Từ khóa được dùng tự nhiên, không lặp quá nhiều', ok: !focusKeyword || keywordDensity <= 2.5, weight: 5, target: 'content', priority: 2 },
            { label: `Nội dung tối thiểu 600 từ (hiện ${words})`, ok: words >= 600, weight: 12, target: 'content', priority: 5 },
            { label: 'Có cấu trúc tiêu đề H2/H3', ok: /<h[23][\s>]/i.test(html), weight: 6, target: 'content', priority: 4 },
            { label: 'Có ảnh minh họa trong nội dung', ok: /<img[\s>]/i.test(html), weight: 3, target: 'content', priority: 1 },
            { label: 'Có liên kết nội bộ hoặc liên kết tham khảo', ok: /<a[\s>]/i.test(html), weight: 4, target: 'content', priority: 2 },
            { label: 'Đã chọn danh mục bài viết', ok: !!categorySelect.value, weight: 3, target: 'blog_category_id', priority: 4 },
            { label: 'Có ảnh bìa', ok: dropzone.classList.contains('has-image'), weight: 3, target: 'image', priority: 4 },
            { label: 'Ảnh bìa có alt mô tả', ok: !dropzone.classList.contains('has-image') || coverAltInput.value.trim().length >= 8, weight: 4, target: 'cover_alt', priority: 3 },
            { label: 'Đường dẫn ngắn gọn, không dấu (≤ 80 ký tự)', ok: /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(activeSlug) && activeSlug.length <= 80, weight: 5, target: 'slug', priority: 3 },
            { label: 'Đã xác định ý định tìm kiếm', ok: !!searchIntentSelect.value, weight: 5, target: 'search_intent', priority: 2 },
        ];

        const score = checks.reduce((total, check) => total + (check.ok ? check.weight : 0), 0);

        seoScoreEl.textContent = score;
        seoRing.style.background = `conic-gradient(${score >= 80 ? '#10b981' : score >= 50 ? '#ff782d' : '#f59e0b'} ${score * 3.6}deg, #eef3f1 0deg)`;
        seoLabelEl.textContent = score >= 80 ? 'Rất tốt' : score >= 50 ? 'Đạt cơ bản' : 'Cần bổ sung';
        const importantChecks = checks
            .filter((check) => !check.ok)
            .sort((left, right) => right.priority - left.priority)
            .slice(0, 3);
        const visibleChecks = isShowingAllSeoChecks
            ? checks
            : (importantChecks.length ? importantChecks : checks.filter((check) => check.ok).slice(0, 3));
        seoList.innerHTML = visibleChecks
            .map((check) => `<li class="${check.ok ? 'ok' : ''}"><span>${check.label}</span>${!check.ok ? `<button type="button" data-seo-target="${check.target}">Sửa</button>` : ''}</li>`)
            .join('');
        seoShowAll.hidden = checks.length <= visibleChecks.length;
        seoShowAll.textContent = isShowingAllSeoChecks ? 'Thu gọn kiểm tra' : `Xem toàn bộ ${checks.length} kiểm tra`;
    };

    /* ---------------- Cập nhật toàn trang ---------------- */
    const refresh = () => {
        const titleLen = titleInput.value.trim().length;
        setCounter(
            document.getElementById('pe-title-counter'),
            `${titleLen} ký tự`,
            titleLen === 0 ? '' : (titleLen >= 30 && titleLen <= 60 ? 'is-ok' : 'is-warn')
        );

        const descLen = descInput.value.trim().length;
        setCounter(
            document.getElementById('pe-desc-counter'),
            `${descLen}/500`,
            descLen === 0 ? '' : (descLen >= 120 && descLen <= 160 ? 'is-ok' : (descLen > 500 ? 'is-over' : 'is-warn'))
        );

        const seoTitleLen = seoTitleInput.value.trim().length;
        setCounter(
            document.getElementById('pe-seo-title-counter'),
            `${seoTitleLen}/70`,
            seoTitleLen === 0 || (seoTitleLen >= 30 && seoTitleLen <= 60) ? 'is-ok' : 'is-warn'
        );

        const metaDescriptionLen = metaDescriptionInput.value.trim().length;
        setCounter(
            document.getElementById('pe-meta-description-counter'),
            `${metaDescriptionLen}/180`,
            metaDescriptionLen === 0 || (metaDescriptionLen >= 120 && metaDescriptionLen <= 160) ? 'is-ok' : 'is-warn'
        );

        const previewTitle = seoTitleInput.value.trim() || titleInput.value.trim() || 'Tiêu đề bài viết';
        const previewDescription = metaDescriptionInput.value.trim() || descInput.value.trim() || 'Mô tả bài viết sẽ xuất hiện tại đây.';
        const previewSlug = slugInput.value.trim() || toSlug(titleInput.value) || 'duong-dan-bai-viet';
        const googlePreview = document.getElementById('pe-google-preview');
        document.getElementById('pe-google-preview-title').textContent = previewTitle;
        document.getElementById('pe-google-preview-description').textContent = previewDescription;
        document.getElementById('pe-google-preview-url').textContent = `${googlePreview.dataset.baseUrl}${previewSlug}`;

        document.getElementById('pe-word-count').textContent = wordCount();
        document.getElementById('pe-char-count').textContent = editorText().length;
        document.getElementById('pe-read-time').textContent = readTime();

        contentInput.value = isEmptyEditor() ? '' : editorHtml();
        refreshSeo();
    };

    const focusSeoTarget = (target) => {
        const element = target === 'content'
            ? document.getElementById('pe-editor')
            : (target === 'image' ? dropzone : document.getElementById(target));
        if (!element) return;
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (target === 'content') quill.focus();
        else if (typeof element.focus === 'function') element.focus({ preventScroll: true });
    };

    seoShowAll.addEventListener('click', () => {
        isShowingAllSeoChecks = !isShowingAllSeoChecks;
        refreshSeo();
    });
    seoList.addEventListener('click', (event) => {
        const button = event.target.closest('[data-seo-target]');
        if (button) focusSeoTarget(button.dataset.seoTarget);
    });

    titleInput.addEventListener('input', () => {
        if (slugAuto) slugInput.value = toSlug(titleInput.value);
        refresh();
        markDirty();
    });
    descInput.addEventListener('input', () => { refresh(); markDirty(); });
    [seoTitleInput, metaDescriptionInput, focusKeywordInput, secondaryKeywordsInput, coverAltInput].forEach((input) => {
        input.addEventListener('input', () => { refresh(); markDirty(); });
    });
    searchIntentSelect.addEventListener('change', () => { refresh(); markDirty(); });
    categorySelect.addEventListener('change', () => { refresh(); markDirty(); });
    slugInput.addEventListener('input', refresh);
    quill.on('text-change', () => { refresh(); markDirty(); });

    /* ---------------- Ảnh bìa: chọn / kéo thả ---------------- */
    const MAX_SIZE = 5 * 1024 * 1024;

    const showCover = (file) => {
        if (!file) return;
        if (!file.type.startsWith('image/')) { alert('Tệp tải lên phải là hình ảnh.'); return; }
        if (file.size > MAX_SIZE) { alert('Ảnh bìa không được vượt quá 5MB.'); return; }

        const reader = new FileReader();
        reader.onload = (e) => {
            coverPreview.src = e.target.result;
            dropzone.classList.add('has-image');
            coverName.textContent = `${file.name} · ${(file.size / 1024).toFixed(0)} KB`;
            document.getElementById('pe-cover-meta').style.display = 'flex';
            refresh();
        };
        reader.readAsDataURL(file);
    };

    dropzone.addEventListener('click', (e) => {
        if (e.target.closest('.pe-image-btn')) return;
        imageInput.click();
    });
    dropzone.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); imageInput.click(); }
    });
    document.getElementById('pe-cover-change').addEventListener('click', () => imageInput.click());

    imageInput.addEventListener('change', () => {
        showCover(imageInput.files[0]);
        markDirty();
    });

    ['dragenter', 'dragover'].forEach((evt) => dropzone.addEventListener(evt, (e) => {
        e.preventDefault();
        dropzone.classList.add('is-dragging');
    }));
    ['dragleave', 'drop'].forEach((evt) => dropzone.addEventListener(evt, (e) => {
        e.preventDefault();
        dropzone.classList.remove('is-dragging');
    }));
    dropzone.addEventListener('drop', (e) => {
        const file = e.dataTransfer?.files?.[0];
        if (!file) return;
        const transfer = new DataTransfer();
        transfer.items.add(file);
        imageInput.files = transfer.files;
        showCover(file);
        markDirty();
    });

    if (coverPreview.getAttribute('src')) {
        document.getElementById('pe-cover-meta').style.display = 'flex';
    }

    /* ---------------- Xem trước ---------------- */
    const modal = document.getElementById('pe-preview-modal');
    const openPreview = () => {
        document.getElementById('pe-preview-title').textContent = titleInput.value.trim() || 'Tiêu đề bài viết';
        document.getElementById('pe-preview-desc').textContent = descInput.value.trim() || 'Mô tả ngắn của bài viết sẽ hiển thị tại đây.';
        document.getElementById('pe-preview-body').innerHTML = isEmptyEditor() ? '<p><em>Chưa có nội dung.</em></p>' : editorHtml();
        document.getElementById('pe-preview-readtime').textContent = readTime();
        document.getElementById('pe-preview-category').textContent =
            categorySelect.options[categorySelect.selectedIndex]?.value ? categorySelect.options[categorySelect.selectedIndex].text : 'Chưa chọn danh mục';

        const cover = document.getElementById('pe-preview-cover');
        const src = coverPreview.getAttribute('src');
        if (src) { cover.src = src; cover.style.display = 'block'; } else { cover.style.display = 'none'; }

        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };
    const closePreview = () => {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    document.getElementById('pe-preview-open').addEventListener('click', openPreview);
    document.getElementById('pe-preview-close').addEventListener('click', closePreview);
    modal.addEventListener('click', (e) => { if (e.target === modal) closePreview(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.classList.contains('is-open')) closePreview(); });

    /* ---------------- Lưu nháp tạm trên trình duyệt ---------------- */
    let DRAFT_KEY = CONFIG.draftKey;
    const autosaveState = document.getElementById('pe-autosave-state');
    let dirty = false;
    let submitting = false;
    let saveTimer = null;

    const saveDraftNow = () => {
        if (!DRAFT_KEY) return false;
        try {
            localStorage.setItem(DRAFT_KEY, JSON.stringify({
                title: titleInput.value,
                slug: slugInput.value,
                description: descInput.value,
                seoTitle: seoTitleInput.value,
                metaDescription: metaDescriptionInput.value,
                focusKeyword: focusKeywordInput.value,
                secondaryKeywords: secondaryKeywordsInput.value,
                searchIntent: searchIntentSelect.value,
                coverAlt: coverAltInput.value,
                content: isEmptyEditor() ? '' : editorHtml(),
                category: categorySelect.value,
                at: Date.now(),
            }));
            autosaveState.textContent = 'Đã lưu nháp tạm lúc ' + new Date().toLocaleTimeString('vi-VN');
            return true;
        } catch (err) {
            autosaveState.textContent = 'Có thay đổi chưa lưu';
            return false;
        }
    };

    const markDirty = () => {
        dirty = true;
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveDraftNow, 1200);
    };

    if (DRAFT_KEY) {
        try {
            const raw = localStorage.getItem(DRAFT_KEY);
            const saved = raw ? JSON.parse(raw) : null;
            const hasContent = saved && (saved.title || saved.description || saved.content || saved.seoTitle || saved.metaDescription);
            const shouldOfferRestore = hasContent && (!CONFIG.isEdit ? !titleInput.value && !descInput.value : true);
            if (shouldOfferRestore) {
                const bar = document.getElementById('pe-draft-bar');
                bar.style.display = 'flex';
                document.getElementById('pe-draft-time').textContent = new Date(saved.at).toLocaleString('vi-VN');
                document.getElementById('pe-draft-restore').addEventListener('click', () => {
                    titleInput.value = saved.title || '';
                    slugInput.value = saved.slug || '';
                    descInput.value = saved.description || '';
                    seoTitleInput.value = saved.seoTitle || '';
                    metaDescriptionInput.value = saved.metaDescription || '';
                    focusKeywordInput.value = saved.focusKeyword || '';
                    secondaryKeywordsInput.value = saved.secondaryKeywords || '';
                    searchIntentSelect.value = saved.searchIntent || '';
                    coverAltInput.value = saved.coverAlt || '';
                    if (saved.category) categorySelect.value = saved.category;
                    if (saved.content) quill.clipboard.dangerouslyPasteHTML(saved.content);
                    bar.style.display = 'none';
                    refresh();
                    markDirty();
                });
                document.getElementById('pe-draft-discard').addEventListener('click', () => {
                    localStorage.removeItem(DRAFT_KEY);
                    bar.style.display = 'none';
                });
            }
        } catch (err) { /* bỏ qua bản nháp hỏng */ }
    }

    const showPostToast = (message, isError = false) => {
        document.getElementById('admin-global-toast')?.remove();
        const toast = document.createElement('div');
        toast.id = 'admin-global-toast';
        toast.className = `admin-global-toast ${isError ? 'error' : 'success'}`;
        const icon = document.createElement('i');
        icon.className = `fa-solid ${isError ? 'fa-triangle-exclamation' : 'fa-circle-check'}`;
        const text = document.createElement('span'); text.textContent = message;
        const close = document.createElement('button'); close.type = 'button'; close.setAttribute('aria-label', 'Đóng thông báo'); close.innerHTML = '&times;';
        close.addEventListener('click', () => toast.remove());
        toast.append(icon, text, close); document.body.appendChild(toast);
        window.setTimeout(() => toast.classList.add('is-hidden'), 3000);
    };

    const showValidationError = (payload) => {
        const firstError = Object.values(payload?.errors || {}).flat()[0] || payload?.message || 'Vui lòng kiểm tra lại thông tin bài viết.';
        showPostToast(firstError, true);
    };

    const enableSavedPostMode = (data) => {
        if (data?.slug) slugInput.value = data.slug;
        if (!data?.edit_url) return;
        form.action = data.edit_url;
        if (!form.querySelector('input[name="_method"]')) {
            const method = document.createElement('input'); method.type = 'hidden'; method.name = '_method'; method.value = 'PUT'; form.appendChild(method);
        }
        DRAFT_KEY = `petworld.admin.post.draft.edit.${data.id}`;
        slugAuto = false;
        renderSlugState();
        window.history.replaceState({}, '', data.edit_url);
        document.getElementById('pe-submit-label').textContent = 'Cập nhật bài viết';
    };

    /* ---------------- Gửi form không chuyển trang ---------------- */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        contentInput.value = isEmptyEditor() ? '' : editorHtml();

        if (!contentInput.value) {
            showPostToast('Vui lòng nhập nội dung chi tiết cho bài viết.', true);
            quill.focus();
            return;
        }

        // Đang ở chế độ tự động: để trống cho server tự sinh slug (và tự thêm hậu tố nếu trùng).
        if (slugAuto) slugInput.value = '';

        const button = document.getElementById('pe-submit');
        button.disabled = true;
        document.getElementById('pe-submit-label').textContent = 'Đang lưu...';

        submitting = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                showValidationError(payload);
                return;
            }

            if (DRAFT_KEY) localStorage.removeItem(DRAFT_KEY);
            dirty = false;
            enableSavedPostMode(payload.data || {});
            showPostToast(payload.message || 'Đã lưu bài viết thành công.');
        } catch (error) {
            showPostToast('Đã xảy ra lỗi khi lưu bài viết. Vui lòng thử lại.', true);
        } finally {
            submitting = false;
            button.disabled = false;
            if (!button.querySelector('span').textContent.includes('Cập nhật')) {
                document.getElementById('pe-submit-label').textContent = form.querySelector('input[name="_method"]') ? 'Cập nhật bài viết' : 'Lưu & Xuất bản';
            }
        }
    });

    window.addEventListener('beforeunload', (e) => {
        if (!dirty || submitting) return;
        e.preventDefault();
        e.returnValue = '';
    });

    /* Liên kết nội bộ có lựa chọn rõ ràng; đóng tab/tải lại vẫn dùng hộp xác nhận chuẩn của trình duyệt. */
    const exitModal = document.getElementById('pe-exit-modal');
    let pendingNavigationUrl = null;
    const closeExitModal = () => {
        exitModal.hidden = true;
        pendingNavigationUrl = null;
    };
    const leavePage = (shouldKeepDraft) => {
        if (!pendingNavigationUrl) return;
        clearTimeout(saveTimer);
        if (shouldKeepDraft && !saveDraftNow()) {
            alert('Chưa thể lưu nháp trên trình duyệt. Hãy thử lại hoặc chọn rời đi không lưu.');
            return;
        }
        if (!shouldKeepDraft && DRAFT_KEY) localStorage.removeItem(DRAFT_KEY);
        dirty = false;
        const nextUrl = pendingNavigationUrl;
        closeExitModal();
        window.location.assign(nextUrl);
    };

    document.addEventListener('click', (event) => {
        if (!dirty || submitting || event.defaultPrevented || !exitModal.hidden) return;
        const link = event.target.closest('a[href]');
        if (!link || link.target || link.hasAttribute('download')) return;

        const nextUrl = new URL(link.href, window.location.href);
        const isSameDocument = nextUrl.pathname === window.location.pathname
            && nextUrl.search === window.location.search
            && nextUrl.hash;
        if (nextUrl.origin !== window.location.origin || isSameDocument) return;

        event.preventDefault();
        pendingNavigationUrl = nextUrl.href;
        exitModal.hidden = false;
        document.getElementById('pe-exit-stay').focus();
    }, true);

    document.getElementById('pe-exit-stay').addEventListener('click', closeExitModal);
    document.getElementById('pe-exit-save-draft').addEventListener('click', () => leavePage(true));
    document.getElementById('pe-exit-discard').addEventListener('click', () => leavePage(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !exitModal.hidden) closeExitModal();
    });

    // Ctrl/Cmd + S để lưu nhanh
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    /* ---------------- Trợ lý nội dung AI: chỉ đề xuất, không tự lưu ---------------- */
    const aiCard = document.getElementById('pe-ai-card');
    const aiResult = document.getElementById('pe-ai-result');
    const aiStatus = document.getElementById('pe-ai-status');
    const aiUndo = document.getElementById('pe-ai-undo');
    const aiActions = Array.from(document.querySelectorAll('.pe-ai-action'));
    const aiLabels = {
        title: 'Tiêu đề bài viết',
        description: 'Mô tả ngắn',
        content: 'Nội dung chi tiết',
        seo_title: 'SEO title',
        meta_description: 'Meta description',
        focus_keyword: 'Từ khóa chính',
        secondary_keywords: 'Từ khóa phụ',
        search_intent: 'Ý định tìm kiếm',
        blog_category_id: 'Danh mục',
        cover_alt: 'Alt ảnh bìa',
    };
    let aiSuggestions = {};
    let aiSnapshot = null;
    let aiAction = null;
    let aiFeedback = { audit: [], warnings: [] };

    const showAiStatus = (message, isError = false) => {
        aiStatus.textContent = message;
        aiStatus.classList.toggle('is-error', isError);
        aiStatus.hidden = !message;
    };

    const currentPostForAi = () => ({
        title: titleInput.value.trim(),
        blog_category_id: categorySelect.value || null,
        description: descInput.value.trim(),
        content: isEmptyEditor() ? '' : editorHtml(),
        seo_title: seoTitleInput.value.trim(),
        meta_description: metaDescriptionInput.value.trim(),
        focus_keyword: focusKeywordInput.value.trim(),
        secondary_keywords: secondaryKeywordsInput.value.split(',').map((item) => item.trim()).filter(Boolean).slice(0, 6),
        search_intent: searchIntentSelect.value || null,
        cover_alt: coverAltInput.value.trim(),
    });

    const takeAiSnapshot = () => ({
        ...currentPostForAi(),
        slug: slugInput.value,
    });

    const restoreAiSnapshot = (snapshot) => {
        titleInput.value = snapshot.title || '';
        slugInput.value = snapshot.slug || '';
        descInput.value = snapshot.description || '';
        seoTitleInput.value = snapshot.seo_title || '';
        metaDescriptionInput.value = snapshot.meta_description || '';
        focusKeywordInput.value = snapshot.focus_keyword || '';
        secondaryKeywordsInput.value = (snapshot.secondary_keywords || []).join(', ');
        searchIntentSelect.value = snapshot.search_intent || '';
        coverAltInput.value = snapshot.cover_alt || '';
        categorySelect.value = snapshot.blog_category_id || '';
        quill.clipboard.dangerouslyPasteHTML(snapshot.content || '');
        refresh();
        markDirty();
    };

    const replaceArticleIntro = (introHtml) => {
        const current = new DOMParser().parseFromString(editorHtml(), 'text/html');
        const intro = new DOMParser().parseFromString(introHtml, 'text/html');
        const introNodes = Array.from(intro.body.children);
        const firstParagraph = current.body.querySelector('p');

        if (firstParagraph && introNodes.length) firstParagraph.replaceWith(...introNodes);
        else current.body.prepend(...introNodes);

        quill.clipboard.dangerouslyPasteHTML(current.body.innerHTML);
    };

    const applyAiField = (field) => {
        const value = aiSuggestions[field];
        if (value === null || value === undefined || value === '' || (Array.isArray(value) && !value.length)) return;
        if (!aiSnapshot) aiSnapshot = takeAiSnapshot();

        if (field === 'title') {
            titleInput.value = value;
            if (slugAuto) slugInput.value = toSlug(value);
        } else if (field === 'description') descInput.value = value;
        else if (field === 'content') {
            if (aiAction === 'rewrite_intro') replaceArticleIntro(value);
            else quill.clipboard.dangerouslyPasteHTML(value);
        } else if (field === 'seo_title') seoTitleInput.value = value;
        else if (field === 'meta_description') metaDescriptionInput.value = value;
        else if (field === 'focus_keyword') focusKeywordInput.value = value;
        else if (field === 'secondary_keywords') secondaryKeywordsInput.value = value.join(', ');
        else if (field === 'search_intent') searchIntentSelect.value = value;
        else if (field === 'blog_category_id') categorySelect.value = String(value);
        else if (field === 'cover_alt') coverAltInput.value = value;

        delete aiSuggestions[field];
        refresh();
        markDirty();
        aiUndo.hidden = false;
        renderAiResult();
    };

    const displayAiValue = (field, value) => {
        if (field === 'content') return new DOMParser().parseFromString(value, 'text/html').body.textContent.trim();
        if (field === 'secondary_keywords') return value.join(', ');
        if (field === 'search_intent') return { informational: 'Tìm hiểu / hướng dẫn', commercial: 'Cân nhắc / so sánh', transactional: 'Mua hàng', navigational: 'Tìm trang cụ thể' }[value] || value;
        if (field === 'blog_category_id') return categorySelect.querySelector(`option[value="${value}"]`)?.textContent || 'Danh mục đã chọn';
        return String(value);
    };

    const renderAiResult = (response = null) => {
        if (response) {
            aiFeedback = {
                audit: Array.isArray(response.audit) ? response.audit : [],
                warnings: Array.isArray(response.warnings) ? response.warnings : [],
            };
        }
        aiResult.replaceChildren();
        const fields = Object.entries(aiSuggestions).filter(([, value]) => value !== null && value !== '' && (!Array.isArray(value) || value.length));

        fields.forEach(([field, value]) => {
            const item = document.createElement('div'); item.className = 'pe-ai-suggestion';
            const label = document.createElement('strong'); label.textContent = aiLabels[field] || field;
            const preview = document.createElement('p'); preview.textContent = displayAiValue(field, value);
            const apply = document.createElement('button'); apply.type = 'button'; apply.className = 'pe-ai-apply'; apply.textContent = 'Áp dụng';
            apply.addEventListener('click', () => applyAiField(field));
            item.append(label, preview, apply); aiResult.appendChild(item);
        });

        if (fields.length > 1) {
            const footer = document.createElement('div'); footer.className = 'pe-ai-footer';
            const applyAll = document.createElement('button'); applyAll.type = 'button'; applyAll.className = 'pe-ai-apply'; applyAll.textContent = 'Áp dụng tất cả đề xuất';
            applyAll.addEventListener('click', () => Object.keys({ ...aiSuggestions }).forEach(applyAiField));
            footer.appendChild(applyAll); aiResult.appendChild(footer);
        }

        aiFeedback.audit.forEach((item) => {
            const audit = document.createElement('p'); audit.className = `pe-ai-audit ${item.level === 'success' ? 'is-success' : ''}`;
            audit.textContent = `${item.field ? `${item.field}: ` : ''}${item.message}`;
            aiResult.appendChild(audit);
        });

        aiFeedback.warnings.forEach((warning) => {
            const audit = document.createElement('p'); audit.className = 'pe-ai-audit'; audit.textContent = `Lưu ý: ${warning}`;
            aiResult.appendChild(audit);
        });

        aiResult.hidden = !aiResult.childElementCount;
        if (!aiResult.childElementCount && response) showAiStatus('AI chưa có đề xuất đủ dữ liệu để áp dụng. Hãy bổ sung brief hoặc nội dung bài viết.', false);
    };

    const requestAiSuggestion = async (action) => {
        if (!titleInput.value.trim()) {
            showAiStatus('Hãy nhập tiêu đề bài viết trước khi dùng trợ lý AI.', true);
            titleInput.focus();
            return;
        }

        if (action === 'improve_post_content' && !editorText()) {
            showAiStatus('Hãy nhập nội dung bài viết trước khi dùng chức năng cải thiện toàn bài.', true);
            quill.focus();
            return;
        }

        if (action === 'rewrite_intro') {
            const hasOpening = Array.from(new DOMParser().parseFromString(editorHtml(), 'text/html').querySelectorAll('p'))
                .some((paragraph) => paragraph.textContent.trim().length >= 20);
            if (!hasOpening) {
                showAiStatus('Hãy viết ít nhất một đoạn mở bài trước khi yêu cầu AI viết lại mở bài.', true);
                quill.focus();
                return;
            }
        }

        aiActions.forEach((button) => { button.disabled = true; });
        showAiStatus('AI đang tạo đề xuất, vui lòng chờ…');
        aiResult.hidden = true;
        aiAction = action;

        try {
            const response = await fetch(aiCard.dataset.aiUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || '',
                },
                body: JSON.stringify({
                    action,
                    post: currentPostForAi(),
                    options: {
                        length: document.getElementById('pe-ai-length').value,
                        tone: document.getElementById('pe-ai-tone').value,
                    },
                }),
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(payload.message || 'Chưa thể tạo đề xuất AI.');

            aiSuggestions = payload.data?.suggestions || {};
            renderAiResult(payload.data || {});
            if (aiResult.childElementCount) showAiStatus('Đề xuất đã sẵn sàng. Hãy kiểm tra trước khi áp dụng.');
        } catch (error) {
            showAiStatus(error.message || 'Đã xảy ra lỗi khi tạo đề xuất AI.', true);
        } finally {
            aiActions.forEach((button) => { button.disabled = false; });
        }
    };

    document.getElementById('pe-ai-toggle').addEventListener('click', () => {
        const collapsed = aiCard.classList.toggle('is-collapsed');
        document.getElementById('pe-ai-toggle').textContent = collapsed ? 'Mở trợ lý' : 'Thu gọn';
        document.getElementById('pe-ai-toggle').setAttribute('aria-expanded', String(!collapsed));
    });
    aiActions.forEach((button) => button.addEventListener('click', () => requestAiSuggestion(button.dataset.aiAction)));
    aiUndo.addEventListener('click', () => {
        if (!aiSnapshot) return;
        restoreAiSnapshot(aiSnapshot);
        aiSnapshot = null;
        aiUndo.hidden = true;
        showAiStatus('Đã hoàn tác các thay đổi vừa áp dụng từ AI.');
    });

    renderSlugState();
    refresh();
    dirty = false;
    autosaveState.textContent = 'Chưa có thay đổi';
})();
</script>
