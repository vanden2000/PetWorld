<style>
    /* ============ Danh sách bài viết ============ */
    .pl-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }
    .pl-header h1 { font-size: 1.75rem; font-weight: 800; color: var(--text-main); }
    .pl-header p { color: var(--text-muted); margin-top: 6px; font-size: 0.9rem; }
    .pl-header-actions { display: flex; align-items: center; gap: 10px; }

    .pl-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 18px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        background: var(--surface-color);
        color: var(--text-main);
        font-family: inherit;
        font-size: 0.87rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: var(--transition);
        white-space: nowrap;
    }
    .pl-btn:hover { border-color: #cfd9d4; background: #fbfdfc; }
    .pl-btn-primary {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
        box-shadow: 0 6px 18px rgba(255, 120, 45, 0.22);
    }
    .pl-btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); }

    /* ---- Thẻ số liệu ---- */
    .pl-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    .pl-stat {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        box-shadow: var(--shadow-subtle);
    }
    .pl-stat-icon {
        flex: none;
        width: 42px; height: 42px;
        display: grid; place-items: center;
        border-radius: 12px;
        font-size: 1rem;
    }
    .pl-stat-icon.is-total { background: #fff4ec; color: var(--primary); }
    .pl-stat-icon.is-live { background: var(--success-light); color: var(--success); }
    .pl-stat-icon.is-draft { background: #eef2f0; color: var(--text-muted); }
    .pl-stat-icon.is-views { background: var(--info-light); color: var(--info); }
    .pl-stat-value { font-size: 1.3rem; font-weight: 800; color: var(--text-main); line-height: 1.2; }
    .pl-stat-label { font-size: 0.76rem; font-weight: 600; color: var(--text-muted); margin-top: 2px; }

    @media (max-width: 1000px) { .pl-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 560px) { .pl-stats { grid-template-columns: 1fr; } }

    /* ---- Bộ lọc ---- */
    .pl-filters {
        display: grid;
        grid-template-columns: minmax(0, 1.8fr) repeat(4, minmax(0, 1fr)) auto;
        gap: 14px;
        align-items: end;
        padding: 18px 20px;
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        box-shadow: var(--shadow-subtle);
        margin-bottom: 20px;
    }
    .pl-filter label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 7px;
    }
    .pl-input, .pl-select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        background: var(--surface-color);
        color: var(--text-main);
        font-family: inherit;
        font-size: 0.87rem;
        outline: none;
        transition: var(--transition);
    }
    .pl-input:focus, .pl-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.1);
    }
    .pl-select {
        appearance: none; cursor: pointer; padding-right: 36px;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%235A7268' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 11px center; background-size: 18px;
    }
    .pl-search-wrap { position: relative; }
    .pl-search-wrap i {
        position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
        color: var(--text-muted); font-size: 0.85rem; pointer-events: none;
    }
    .pl-search-wrap .pl-input { padding-left: 36px; }
    .pl-filter-actions { display: flex; gap: 8px; }

    @media (max-width: 1100px) {
        .pl-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pl-filter-actions { grid-column: 1 / -1; }
    }
    @media (max-width: 560px) { .pl-filters { grid-template-columns: 1fr; } }

    /* ---- Bảng ---- */
    .pl-table-card {
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--shadow-subtle);
        overflow: hidden;
    }
    .pl-table-scroll { overflow-x: auto; }
    .pl-table { width: 100%; border-collapse: collapse; min-width: 1020px; }
    .pl-table thead th {
        padding: 13px 16px;
        text-align: left;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--text-muted);
        background: #fcfdfd;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }
    .pl-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #f0f4f2;
        vertical-align: middle;
        font-size: 0.87rem;
        color: var(--text-main);
    }
    .pl-table tbody tr:last-child td { border-bottom: none; }
    .pl-table tbody tr { transition: var(--transition); }
    .pl-table tbody tr:hover { background: #fbfdfc; }

    /* Ô bài viết */
    .pl-post-cell { display: flex; align-items: center; gap: 13px; min-width: 320px; }
    .pl-thumb {
        flex: none;
        width: 78px; height: 54px;
        border-radius: 9px;
        object-fit: cover;
        border: 1px solid var(--border-color);
        background: #f3f7f5;
    }
    .pl-thumb-empty {
        flex: none;
        width: 78px; height: 54px;
        border-radius: 9px;
        border: 1px dashed #d9e3de;
        background: #f7faf8;
        display: grid; place-items: center;
        color: #a9bcb4;
        font-size: 1.05rem;
    }
    .pl-post-title {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-weight: 700;
        color: var(--text-main);
        text-decoration: none;
        line-height: 1.4;
    }
    .pl-post-title:hover { color: var(--primary); }
    .pl-post-slug {
        display: inline-flex; align-items: center; gap: 5px;
        margin-top: 5px;
        font-size: 0.74rem;
        color: var(--text-muted);
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        max-width: 320px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }

    /* Huy hiệu */
    .pl-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 11px;
        border-radius: 20px;
        font-size: 0.76rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .pl-badge-category { background: #fff4ec; color: var(--primary); border: 1px solid #ffdcc6; }
    .pl-badge-none { background: #f3f7f5; color: var(--text-muted); border: 1px solid var(--border-color); }
    .pl-badge-live { background: var(--success-light); color: #12805c; border: 1px solid #b8ead3; }
    .pl-badge-draft { background: #eef2f0; color: var(--text-muted); border: 1px solid var(--border-color); }

    .pl-metric { display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .pl-metric i { color: var(--text-muted); font-size: 0.82rem; }
    .pl-comment-link {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 11px; border-radius: 20px;
        border: 1px solid var(--border-color); background: var(--surface-color);
        color: var(--text-main); text-decoration: none;
        font-size: 0.8rem; font-weight: 700;
        transition: var(--transition);
    }
    .pl-comment-link:hover { border-color: var(--primary); color: var(--primary); background: #fff8f3; }
    .pl-date { font-size: 0.82rem; color: var(--text-muted); white-space: nowrap; }
    .pl-date small { display: block; font-size: 0.72rem; opacity: 0.8; }

    /* Nút thao tác */
    .pl-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }
    .pl-action {
        width: 34px; height: 34px;
        display: inline-grid; place-items: center;
        border: 1px solid var(--border-color);
        border-radius: 9px;
        background: var(--surface-color);
        color: var(--text-muted);
        font-size: 0.85rem;
        cursor: pointer;
        text-decoration: none;
        transition: var(--transition);
    }
    .pl-action:hover { border-color: var(--primary); color: var(--primary); background: #fff8f3; }
    .pl-action.is-danger:hover { border-color: #fca5a5; color: var(--danger); background: var(--danger-light); }
    .pl-action-form { display: inline-flex; margin: 0; }

    /* Trạng thái rỗng */
    .pl-empty { padding: 56px 24px; text-align: center; }
    .pl-empty-icon {
        width: 68px; height: 68px; margin: 0 auto 16px;
        display: grid; place-items: center;
        border-radius: 50%;
        background: #fff4ec; color: var(--primary);
        font-size: 1.7rem;
    }
    .pl-empty strong { display: block; font-size: 1rem; color: var(--text-main); margin-bottom: 6px; }
    .pl-empty p { font-size: 0.87rem; color: var(--text-muted); margin-bottom: 18px; }

    /* Hộp xác nhận xóa */
    .pl-modal {
        position: fixed; inset: 0; z-index: 999;
        display: none; align-items: center; justify-content: center;
        padding: 20px;
        background: rgba(15, 30, 25, 0.55);
        backdrop-filter: blur(3px);
    }
    .pl-modal.is-open { display: flex; }
    .pl-modal-box {
        width: min(460px, 100%);
        background: var(--surface-color);
        border-radius: 16px;
        padding: 26px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
    }
    .pl-modal-icon {
        width: 52px; height: 52px; margin-bottom: 16px;
        display: grid; place-items: center;
        border-radius: 50%;
        background: var(--danger-light); color: var(--danger);
        font-size: 1.25rem;
    }
    .pl-modal-box h3 { font-size: 1.08rem; font-weight: 800; color: var(--text-main); margin-bottom: 8px; }
    .pl-modal-box p { font-size: 0.87rem; color: var(--text-muted); line-height: 1.6; }
    .pl-modal-target {
        margin-top: 12px; padding: 11px 14px;
        border: 1px solid var(--border-color); border-radius: 10px;
        background: #fbfdfc;
        font-size: 0.86rem; font-weight: 700; color: var(--text-main);
        word-break: break-word;
    }
    .pl-modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
    .pl-modal-danger {
        background: var(--danger); border-color: var(--danger); color: #fff;
    }
    .pl-modal-danger:hover { background: #dc2626; border-color: #dc2626; }
</style>
