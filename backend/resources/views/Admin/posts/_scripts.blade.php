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

    const refreshSeo = () => {
        const title = titleInput.value.trim();
        const desc = descInput.value.trim();
        const html = editorHtml();
        const text = editorText();
        const words = wordCount();

        const checks = [
            [`Tiêu đề dài 30–60 ký tự (hiện ${title.length})`, title.length >= 30 && title.length <= 60],
            [`Mô tả ngắn dài 120–160 ký tự (hiện ${desc.length})`, desc.length >= 120 && desc.length <= 160],
            ['Đã chọn danh mục bài viết', !!categorySelect.value],
            ['Có ảnh bìa', dropzone.classList.contains('has-image')],
            ['Đường dẫn ngắn gọn, không dấu (≤ 80 ký tự)', /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slugInput.value) && slugInput.value.length <= 80],
            [`Nội dung tối thiểu 300 từ (hiện ${words})`, words >= 300],
            ['Có tiêu đề phụ H2/H3 trong nội dung', /<h[23][\s>]/i.test(html)],
            ['Có ít nhất 1 ảnh minh họa trong nội dung', /<img[\s>]/i.test(html)],
            ['Có liên kết tới trang khác của website', /<a[\s>]/i.test(html)],
            ['Tiêu đề xuất hiện trong đoạn mở đầu', !!title && text.slice(0, 300).toLowerCase().includes(title.toLowerCase().split(' ').slice(0, 3).join(' '))],
        ];

        const passed = checks.filter(([, ok]) => ok).length;
        const score = Math.round((passed / checks.length) * 100);

        seoScoreEl.textContent = score;
        seoRing.style.background = `conic-gradient(${score >= 80 ? '#10b981' : score >= 50 ? '#ff782d' : '#f59e0b'} ${score * 3.6}deg, #eef3f1 0deg)`;
        seoLabelEl.textContent = score >= 80 ? 'Rất tốt' : score >= 50 ? 'Đạt cơ bản' : 'Cần bổ sung';
        seoList.innerHTML = checks
            .map(([label, ok]) => `<li class="${ok ? 'ok' : ''}">${label}</li>`)
            .join('');
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

        document.getElementById('pe-word-count').textContent = wordCount();
        document.getElementById('pe-char-count').textContent = editorText().length;
        document.getElementById('pe-read-time').textContent = readTime();

        contentInput.value = isEmptyEditor() ? '' : editorHtml();
        refreshSeo();
    };

    titleInput.addEventListener('input', () => {
        if (slugAuto) slugInput.value = toSlug(titleInput.value);
        refresh();
        markDirty();
    });
    descInput.addEventListener('input', () => { refresh(); markDirty(); });
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
    const DRAFT_KEY = CONFIG.draftKey;
    const autosaveState = document.getElementById('pe-autosave-state');
    let dirty = false;
    let submitting = false;
    let saveTimer = null;

    const markDirty = () => {
        dirty = true;
        if (!DRAFT_KEY) { autosaveState.textContent = 'Có thay đổi chưa lưu'; return; }
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => {
            try {
                localStorage.setItem(DRAFT_KEY, JSON.stringify({
                    title: titleInput.value,
                    slug: slugInput.value,
                    description: descInput.value,
                    content: isEmptyEditor() ? '' : editorHtml(),
                    category: categorySelect.value,
                    at: Date.now(),
                }));
                autosaveState.textContent = 'Đã lưu nháp tạm lúc ' + new Date().toLocaleTimeString('vi-VN');
            } catch (err) {
                autosaveState.textContent = 'Có thay đổi chưa lưu';
            }
        }, 1200);
    };

    if (DRAFT_KEY) {
        try {
            const raw = localStorage.getItem(DRAFT_KEY);
            const saved = raw ? JSON.parse(raw) : null;
            const hasContent = saved && (saved.title || saved.description || saved.content);
            if (hasContent && !titleInput.value && !descInput.value) {
                const bar = document.getElementById('pe-draft-bar');
                bar.style.display = 'flex';
                document.getElementById('pe-draft-time').textContent = new Date(saved.at).toLocaleString('vi-VN');
                document.getElementById('pe-draft-restore').addEventListener('click', () => {
                    titleInput.value = saved.title || '';
                    slugInput.value = saved.slug || '';
                    descInput.value = saved.description || '';
                    if (saved.category) categorySelect.value = saved.category;
                    if (saved.content) quill.clipboard.dangerouslyPasteHTML(saved.content);
                    bar.style.display = 'none';
                    refresh();
                });
                document.getElementById('pe-draft-discard').addEventListener('click', () => {
                    localStorage.removeItem(DRAFT_KEY);
                    bar.style.display = 'none';
                });
            }
        } catch (err) { /* bỏ qua bản nháp hỏng */ }
    }

    /* ---------------- Gửi form ---------------- */
    form.addEventListener('submit', (e) => {
        contentInput.value = isEmptyEditor() ? '' : editorHtml();

        if (!contentInput.value) {
            e.preventDefault();
            alert('Vui lòng nhập nội dung chi tiết cho bài viết.');
            quill.focus();
            return;
        }

        // Đang ở chế độ tự động: để trống cho server tự sinh slug (và tự thêm hậu tố nếu trùng).
        if (slugAuto) slugInput.value = '';

        submitting = true;
        if (DRAFT_KEY) localStorage.removeItem(DRAFT_KEY);

        const button = document.getElementById('pe-submit');
        button.disabled = true;
        document.getElementById('pe-submit-label').textContent = 'Đang lưu...';
    });

    window.addEventListener('beforeunload', (e) => {
        if (!dirty || submitting) return;
        e.preventDefault();
        e.returnValue = '';
    });

    // Ctrl/Cmd + S để lưu nhanh
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    renderSlugState();
    refresh();
    dirty = false;
    autosaveState.textContent = 'Chưa có thay đổi';
})();
</script>
