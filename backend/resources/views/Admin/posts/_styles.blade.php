<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<style>
    /* ============ Trang soạn thảo bài viết ============ */
    .pe-page { padding-bottom: 48px; }

    .pe-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }
    .pe-kicker {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    .pe-kicker a { color: inherit; text-decoration: none; }
    .pe-kicker a:hover { color: var(--primary); }
    .pe-header h1 {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.25;
    }
    .pe-header p { color: var(--text-muted); margin-top: 6px; font-size: 0.9rem; }
    .pe-header-actions { display: flex; align-items: center; gap: 10px; }

    /* ---- Nút dùng chung ---- */
    .pe-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        background: var(--surface-color);
        color: var(--text-main);
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: var(--transition);
        white-space: nowrap;
    }
    .pe-btn:hover { border-color: #cfd9d4; background: #fbfdfc; }
    .pe-btn-primary {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
        padding: 13px 20px;
        box-shadow: 0 6px 18px rgba(255, 120, 45, 0.24);
    }
    .pe-btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); }
    .pe-btn-ghost { background: transparent; }
    .pe-btn-block { width: 100%; }
    .pe-btn-danger-text { color: var(--danger); }
    .pe-btn-danger-text:hover { background: var(--danger-light); border-color: #fca5a5; }

    /* ---- Bố cục 2 cột ---- */
    .pe-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 24px;
        align-items: start;
    }
    .pe-col { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
    .pe-col-side { position: sticky; top: 20px; }

    @media (max-width: 1180px) {
        .pe-grid { grid-template-columns: 1fr; }
        .pe-col-side { position: static; }
    }

    /* ---- Thẻ ---- */
    .pe-card {
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--shadow-subtle);
        overflow: hidden;
    }
    .pe-card-head {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        background: #fcfdfd;
    }
    .pe-card-head i { color: var(--primary); font-size: 0.95rem; }
    .pe-card-head h2 {
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--text-main);
        margin: 0;
    }
    .pe-card-head .pe-card-hint {
        margin-left: auto;
        font-size: 0.74rem;
        color: var(--text-muted);
        font-weight: 600;
    }
    .pe-card-body { padding: 20px; }
    .pe-card-body.is-flush { padding: 0; }

    /* ---- Trường nhập ---- */
    .pe-field { margin-bottom: 20px; }
    .pe-field:last-child { margin-bottom: 0; }
    .pe-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.83rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 8px;
    }
    .pe-label .pe-req { color: var(--danger); }
    .pe-label .pe-counter {
        margin-left: auto;
        font-size: 0.74rem;
        font-weight: 700;
        color: var(--text-muted);
        font-variant-numeric: tabular-nums;
    }
    .pe-counter.is-ok { color: var(--success); }
    .pe-counter.is-warn { color: #b45309; }
    .pe-counter.is-over { color: var(--danger); }

    .pe-input, .pe-select, .pe-textarea {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        background: var(--surface-color);
        color: var(--text-main);
        font-family: inherit;
        font-size: 0.9rem;
        outline: none;
        transition: var(--transition);
    }
    .pe-input::placeholder, .pe-textarea::placeholder { color: #9db0a8; }
    .pe-input:focus, .pe-select:focus, .pe-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.1);
    }
    .pe-input-title { font-size: 1.05rem; font-weight: 700; padding: 13px 16px; }
    .pe-textarea { min-height: 96px; resize: vertical; line-height: 1.6; }
    .pe-select { appearance: none; cursor: pointer; padding-right: 38px;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%235A7268' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center; background-size: 18px;
    }
    .pe-help { font-size: 0.76rem; color: var(--text-muted); margin-top: 7px; line-height: 1.5; }
    .pe-error { display: block; font-size: 0.78rem; font-weight: 600; color: var(--danger); margin-top: 7px; }

    /* ---- Đường dẫn (slug) ---- */
    .pe-slug-row { display: flex; align-items: center; gap: 8px; }
    .pe-slug-prefix {
        flex: none;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        background: #f3f7f5;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 9px 10px;
        white-space: nowrap;
        max-width: 45%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pe-slug-row .pe-input { font-size: 0.84rem; }
    .pe-slug-lock { flex: none; width: 40px; height: 40px; padding: 0; border-radius: 10px; }
    .pe-slug-lock.is-auto { color: var(--primary); background: #fff4ec; border-color: #ffd9c2; }

    /* ---- Trình soạn thảo ---- */
    #pe-editor { min-height: 460px; font-family: inherit; font-size: 0.95rem; }
    .pe-editor-wrap { border-top: 1px solid var(--border-color); }
    .ql-toolbar.ql-snow {
        border: none;
        border-bottom: 1px solid var(--border-color);
        background: #fcfdfd;
        padding: 10px 16px;
        position: sticky;
        top: 0;
        z-index: 5;
    }
    .ql-container.ql-snow { border: none; }
    .ql-editor {
        padding: 22px 24px;
        line-height: 1.75;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .ql-editor h2 { font-size: 1.32rem; font-weight: 800; margin: 22px 0 10px; }
    .ql-editor h3 { font-size: 1.12rem; font-weight: 800; margin: 18px 0 8px; }
    .ql-editor p { margin-bottom: 12px; }
    .ql-editor img { max-width: 100%; border-radius: 10px; }
    .ql-editor blockquote { border-left: 3px solid var(--primary); background: #fff8f3; padding: 10px 16px; border-radius: 0 8px 8px 0; }
    .ql-editor.ql-blank::before { font-style: normal; color: #9db0a8; left: 24px; right: 24px; }
    .ql-snow .ql-picker.ql-header .ql-picker-label::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item::before { font-family: inherit; }

    .pe-editor-foot {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
        padding: 11px 20px;
        border-top: 1px solid var(--border-color);
        background: #fcfdfd;
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--text-muted);
    }
    .pe-editor-foot span { display: inline-flex; align-items: center; gap: 6px; }
    .pe-editor-foot i { color: var(--primary); opacity: 0.8; }
    .pe-editor-foot .pe-foot-right { margin-left: auto; }
    .pe-uploading { color: var(--primary); }

    /* ---- Trạng thái xuất bản ---- */
    .pe-status-list { display: grid; gap: 10px; }
    .pe-status-option {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 12px 14px;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        cursor: pointer;
        transition: var(--transition);
        background: var(--surface-color);
    }
    .pe-status-option:hover { border-color: #cfd9d4; background: #fbfdfc; }
    .pe-status-option input { margin-top: 3px; accent-color: var(--primary); width: 16px; height: 16px; cursor: pointer; }
    .pe-status-option strong { display: block; font-size: 0.87rem; color: var(--text-main); }
    .pe-status-option small { display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 3px; line-height: 1.45; }
    .pe-status-option:has(input:checked) { border-color: var(--primary); background: #fff8f3; box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.07); }

    /* ---- Ảnh bìa ---- */
    .pe-dropzone {
        position: relative;
        border: 2px dashed #dfe8e3;
        border-radius: 14px;
        background: #f8fbfa;
        cursor: pointer;
        overflow: hidden;
        transition: var(--transition);
    }
    .pe-dropzone:hover, .pe-dropzone.is-dragging { border-color: var(--primary); background: #fff8f3; }
    .pe-dropzone-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 4px;
        min-height: 168px;
        padding: 20px;
    }
    .pe-dropzone-empty i { font-size: 1.9rem; color: var(--primary); opacity: 0.85; margin-bottom: 6px; }
    .pe-dropzone-empty strong { font-size: 0.86rem; color: var(--text-main); }
    .pe-dropzone-empty small { font-size: 0.73rem; color: var(--text-muted); }
    .pe-dropzone-preview { position: relative; display: none; }
    .pe-dropzone-preview img { display: block; width: 100%; height: 190px; object-fit: cover; }
    .pe-dropzone.has-image .pe-dropzone-empty { display: none; }
    .pe-dropzone.has-image .pe-dropzone-preview { display: block; }
    .pe-image-actions { position: absolute; top: 10px; right: 10px; display: flex; gap: 6px; }
    .pe-image-btn {
        width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        border: none; border-radius: 50%;
        background: rgba(15, 23, 42, 0.62);
        color: #fff; cursor: pointer;
        backdrop-filter: blur(2px);
        transition: var(--transition);
    }
    .pe-image-btn:hover { background: rgba(15, 23, 42, 0.85); }
    .pe-image-btn.is-danger:hover { background: var(--danger); }
    .pe-image-meta {
        display: none;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        word-break: break-all;
    }
    .pe-dropzone.has-image ~ .pe-image-meta { display: flex; }

    /* ---- Thông tin bài viết ---- */
    .pe-meta-list { display: grid; gap: 11px; }
    .pe-meta-row { display: flex; align-items: center; gap: 10px; font-size: 0.8rem; }
    .pe-meta-row i { width: 16px; text-align: center; color: var(--text-muted); }
    .pe-meta-row span { color: var(--text-muted); }
    .pe-meta-row strong { margin-left: auto; color: var(--text-main); font-weight: 700; text-align: right; }

    /* ---- Trợ lý SEO ---- */
    .pe-seo-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .pe-seo-ring {
        position: relative;
        width: 54px; height: 54px;
        flex: none;
        border-radius: 50%;
        display: grid; place-items: center;
        background: conic-gradient(var(--primary) 0deg, #eef3f1 0deg);
    }
    .pe-seo-ring::after {
        content: ''; position: absolute;
        inset: 5px; border-radius: 50%; background: var(--surface-color);
    }
    .pe-seo-ring b { position: relative; z-index: 1; font-size: 0.88rem; font-weight: 800; color: var(--text-main); }
    .pe-seo-head strong { font-size: 0.88rem; color: var(--text-main); display: block; }
    .pe-seo-head small { font-size: 0.75rem; color: var(--text-muted); }
    .pe-seo-list { list-style: none; display: grid; gap: 7px; margin: 0; padding: 0; }
    .pe-seo-list li {
        display: flex; align-items: flex-start; gap: 8px;
        font-size: 0.765rem; line-height: 1.5; color: var(--text-muted);
    }
    .pe-seo-list li::before {
        content: '!';
        flex: none;
        width: 16px; height: 16px; margin-top: 1px;
        display: inline-grid; place-items: center;
        border-radius: 50%;
        background: var(--warning-light); color: #b45309;
        font-size: 10px; font-weight: 800;
    }
    .pe-seo-list li.ok { color: #237343; }
    .pe-seo-list li.ok::before { content: '✓'; background: var(--success-light); color: #237343; }

    /* ---- Thanh hành động ---- */
    .pe-actions { display: grid; gap: 10px; }

    /* ---- Cảnh báo / khôi phục nháp ---- */
    .pe-alert {
        display: flex; align-items: flex-start; gap: 11px;
        padding: 14px 18px; border-radius: 12px;
        margin-bottom: 20px; font-size: 0.86rem; line-height: 1.55;
    }
    .pe-alert i { margin-top: 2px; }
    .pe-alert-danger { background: var(--danger-light); border: 1px solid #fca5a5; color: #b91c1c; }
    .pe-alert-danger ul { margin: 6px 0 0; padding-left: 18px; }
    .pe-alert-info { background: var(--info-light); border: 1px solid #bfdbfe; color: #1d4ed8; }
    .pe-alert-info .pe-alert-actions { margin-left: auto; display: flex; gap: 8px; flex: none; }
    .pe-alert-info button {
        border: 1px solid #bfdbfe; background: #fff; color: #1d4ed8;
        border-radius: 8px; padding: 6px 12px; font-family: inherit;
        font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: var(--transition);
    }
    .pe-alert-info button:hover { background: #dbeafe; }

    /* ---- Xem trước ---- */
    .pe-modal {
        position: fixed; inset: 0; z-index: 999;
        display: none; align-items: flex-start; justify-content: center;
        padding: 40px 20px;
        background: rgba(15, 30, 25, 0.55);
        backdrop-filter: blur(3px);
        overflow-y: auto;
    }
    .pe-modal.is-open { display: flex; }
    .pe-modal-box {
        width: 100%; max-width: 820px;
        background: var(--surface-color);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
    }
    .pe-modal-head {
        display: flex; align-items: center; gap: 10px;
        padding: 15px 22px; border-bottom: 1px solid var(--border-color);
        background: #fcfdfd;
    }
    .pe-modal-head strong { font-size: 0.92rem; }
    .pe-modal-head button {
        margin-left: auto; width: 34px; height: 34px;
        border: 1px solid var(--border-color); background: #fff;
        border-radius: 9px; cursor: pointer; color: var(--text-muted);
        transition: var(--transition);
    }
    .pe-modal-head button:hover { color: var(--danger); border-color: #fca5a5; }
    .pe-preview { padding: 26px 32px 36px; }
    .pe-preview-cover { width: 100%; height: 300px; object-fit: cover; border-radius: 14px; margin-bottom: 22px; }
    .pe-preview-badge {
        display: inline-block; padding: 4px 12px; border-radius: 20px;
        background: #fff4ec; color: var(--primary); border: 1px solid #ffd9c2;
        font-size: 0.75rem; font-weight: 700; margin-bottom: 12px;
    }
    .pe-preview h1 { font-size: 1.85rem; font-weight: 800; line-height: 1.3; color: var(--text-main); }
    .pe-preview-meta { display: flex; gap: 16px; flex-wrap: wrap; margin: 12px 0 18px; font-size: 0.78rem; color: var(--text-muted); font-weight: 600; }
    .pe-preview-desc {
        font-size: 0.98rem; color: #40534c; line-height: 1.7;
        padding: 14px 18px; border-left: 3px solid var(--primary);
        background: #fff8f3; border-radius: 0 10px 10px 0; margin-bottom: 22px;
    }
    .pe-preview-body { font-size: 0.97rem; line-height: 1.8; color: #2b3b35; }
    .pe-preview-body h2 { font-size: 1.35rem; font-weight: 800; margin: 26px 0 10px; }
    .pe-preview-body h3 { font-size: 1.13rem; font-weight: 800; margin: 20px 0 8px; }
    .pe-preview-body p { margin-bottom: 14px; }
    .pe-preview-body ul, .pe-preview-body ol { margin: 0 0 14px 20px; }
    .pe-preview-body img { max-width: 100%; border-radius: 12px; margin: 10px 0; }
    .pe-preview-body blockquote { border-left: 3px solid var(--primary); background: #fff8f3; padding: 12px 18px; border-radius: 0 10px 10px 0; margin-bottom: 14px; }
    .pe-preview-body a { color: var(--primary); }

    @media (max-width: 620px) {
        .pe-header h1 { font-size: 1.45rem; }
        .pe-preview { padding: 20px; }
        .pe-preview-cover { height: 190px; }
        .pe-slug-row { flex-wrap: wrap; }
        .pe-slug-prefix { max-width: 100%; }
    }
</style>
