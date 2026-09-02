@extends('admin.layouts.app')

@php
    $isCreate = $isCreate ?? false;
@endphp

@section('title', $isCreate ? 'Thêm Sản phẩm' : 'Sửa Sản phẩm')

@section('styles')
@vite('resources/js/admin/product-description-editor.js')
<style>
    /* Scoped variables for orange theme matching style.css */
    :root {
        --theme-primary: var(--primary);
        --theme-primary-hover: var(--primary-hover);
        --theme-primary-light: rgba(255, 120, 45, 0.08);
        --theme-border: var(--border-color);
        --theme-text-main: var(--text-main);
        --theme-text-gray: var(--text-muted);
        --theme-bg: var(--bg-color);
    }

    /* Page Header */
    .listing-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        position: sticky;
        top: 0;
        background-color: var(--theme-bg);
        padding: 10px 0;
        z-index: 10;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    }

    .listing-title h1 {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--theme-text-main);
    }

    .action-header-buttons {
        display: flex;
        gap: 12px;
    }

    .btn-action-cancel {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        color: var(--theme-text-main);
        border: 1px solid var(--theme-border);
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 50ms ease;
    }

    .btn-action-cancel:hover {
        background-color: #f9f9f9;
        border-color: #ccc;
    }

    .btn-action-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: var(--theme-primary);
        color: #ffffff;
        border: 1px solid var(--theme-primary);
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 50ms ease;
        box-shadow: 0 4px 6px -1px rgba(255, 120, 45, 0.15);
    }

    .btn-action-save:hover {
        background-color: var(--theme-primary-hover);
        border-color: var(--theme-primary-hover);
    }

    .product-save-modal {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: grid;
        place-items: center;
        padding: 20px;
        background: rgba(15, 23, 42, .48);
    }

    .product-save-modal[hidden] { display: none; }
    .product-save-modal-card {
        width: min(100%, 430px);
        padding: 26px;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .25);
    }

    .product-save-modal-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 50%;
        color: var(--theme-primary);
        background: var(--theme-primary-light);
    }

    .product-save-modal-card h3 { margin: 15px 0 8px; color: var(--theme-text-main); }
    .product-save-modal-card p { margin: 0; color: var(--theme-text-gray); line-height: 1.55; }
    .product-save-modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
    .product-save-modal-actions button { min-width: 100px; }

    .product-save-toast {
        position: fixed;
        z-index: 1001;
        right: 24px;
        bottom: 96px;
        display: flex;
        align-items: center;
        gap: 9px;
        max-width: min(420px, calc(100vw - 32px));
        padding: 12px 15px;
        border-radius: 9px;
        color: #176b37;
        background: #ecf9f0;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .14);
        font-weight: 700;
    }
    .product-save-toast.error { color: #a12626; background: #fff1f1; }
    .product-save-toast[hidden] { display: none; }
    /* Icon canh bao / thanh cong dung dau thong bao */
    .product-save-toast-icon { flex-shrink: 0; align-self: flex-start; margin-top: 1px; font-size: 1.02rem; line-height: 1.35; }

    .product-ai-card { border: 1px solid rgba(255, 120, 45, .48); border-left: 5px solid var(--theme-primary); background: linear-gradient(135deg, #fff9f5 0%, #ffffff 58%); box-shadow: 0 10px 24px rgba(207, 92, 24, .14); }
    .product-ai-card.is-collapsed { overflow: hidden; padding: 12px 14px; }
    .product-ai-card.is-collapsed .form-card-title { margin: 0; padding: 0; border: 0; }
    .product-ai-card.is-collapsed .product-ai-note, .product-ai-card.is-collapsed .product-ai-actions, .product-ai-card.is-collapsed .product-ai-status, .product-ai-card.is-collapsed .product-ai-result, .product-ai-card.is-collapsed .btn-ai-undo { display: none; }
    .product-ai-card .form-card-title { position: sticky; top: 76px; z-index: 3; margin: -18px -18px 16px; padding: 18px 18px 12px; color: #a8440d; background: linear-gradient(135deg, #fff9f5 0%, #ffffff 58%); }
    .product-ai-card .form-card-title i { display: grid; place-items: center; width: 28px; height: 28px; border-radius: 50%; color: #fff; background: var(--theme-primary); box-shadow: 0 4px 10px rgba(255, 120, 45, .3); }
    .product-ai-toggle { margin-left: auto; border: 1px solid rgba(255, 120, 45, .32); background: #fff5ee; color: #b44a11; border-radius: 999px; padding: 5px 8px; cursor: pointer; font: inherit; font-size: .72rem; font-weight: 800; }
    .product-ai-note { margin: -4px 0 14px; color: var(--theme-text-gray); font-size: .82rem; line-height: 1.5; }
    .product-ai-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .btn-ai-action, .btn-ai-undo { border: 1px solid rgba(255, 120, 45, .4); background: #fff; color: #b44a11; border-radius: 8px; padding: 8px 10px; cursor: pointer; font: inherit; font-size: .78rem; font-weight: 800; }
    .btn-ai-action:hover, .btn-ai-action:focus-visible { color: #fff; background: var(--theme-primary); border-color: var(--theme-primary); }
    .btn-ai-action:disabled { cursor: wait; opacity: .65; }
    .product-ai-status { margin-top: 12px; padding: 9px 10px; border-radius: 8px; color: #a8440d; background: #fff0e5; font-size: .8rem; font-weight: 700; }
    .product-ai-status.error { color: #a12626; background: #fff1f1; }
    .product-ai-result { margin-top: 14px; display: grid; gap: 9px; }
    .product-ai-suggestion { border: 1px solid #f1dfd3; border-radius: 8px; padding: 10px; background: #fff; }
    .product-ai-suggestion strong { display: block; font-size: .78rem; color: var(--theme-text-main); margin-bottom: 5px; }
    .product-ai-suggestion p { margin: 0 0 8px; white-space: pre-wrap; color: var(--theme-text-gray); font-size: .8rem; line-height: 1.45; }
    .product-ai-apply { border: 0; color: #fff; background: var(--theme-primary); border-radius: 6px; padding: 6px 9px; font-size: .75rem; font-weight: 800; cursor: pointer; }
    .product-ai-apply:hover, .product-ai-apply:focus-visible { background: var(--theme-primary-hover); }
    .product-ai-footer { display: flex; gap: 8px; flex-wrap: wrap; }
    .product-ai-warning { margin: 0; padding-left: 18px; color: #9a4b1b; font-size: .78rem; }
    .btn-ai-undo { margin-top: 12px; color: #8a2929; border-color: #f2c5c5; }

    @media (max-width: 900px) { .product-ai-card .form-card-title { top: 68px; } }

    /* Core column structure */
    .create-listing-wrapper {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        align-items: start;
    }

    .layout-column-main, .layout-column-sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Card styling styling */
    .form-card {
        background-color: #ffffff;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .form-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--theme-text-main);
        margin-bottom: 20px;
        border-bottom: 1px solid #f3f4f6;
        padding-bottom: 12px;
    }

    .form-card-title i {
        color: var(--theme-primary);
        font-size: 1.15rem;
    }

    /* Form control structures */
    .form-control-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    .form-control-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
    }

    .form-control-group.no-margin {
        margin-bottom: 0;
    }

    .form-field-label {
        font-size: 0.76rem;
        font-weight: 800;
        color: var(--theme-text-gray);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Dau sao do danh dau truong bat buoc nhap */
    .required-mark {
        color: #dc2626;
        font-weight: 900;
        margin-left: 2px;
    }

    .input-text-field {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        font-family: inherit;
        font-size: 0.9rem;
        color: var(--theme-text-main);
        outline: none;
        background-color: #ffffff;
        transition: all 0.1s ease;
    }

    .input-text-field:focus {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.08);
    }

    .input-select-field {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        font-family: inherit;
        font-size: 0.9rem;
        color: var(--theme-text-main);
        outline: none;
        background-color: #ffffff;
        cursor: pointer;
        transition: all 0.1s ease;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%235A7268' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 16px;
    }

    .input-select-field:focus {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.08);
    }

    /* Rich text editor area mockup styling */
    .editor-wrapper {
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        overflow: hidden;
    }

    .editor-toolbar {
        display: flex;
        gap: 4px;
        background-color: #f9fafb;
        border-bottom: 1px solid var(--theme-border);
        padding: 8px 12px;
    }

    .btn-editor-tool {
        background: none;
        border: none;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        color: var(--theme-text-gray);
        cursor: pointer;
        font-size: 0.85rem;
    }

    .btn-editor-tool:hover {
        background-color: #e5e7eb;
        color: var(--theme-text-main);
    }

    .editor-textarea {
        width: 100%;
        min-height: 180px;
        padding: 14px;
        border: none;
        outline: none;
        font-family: inherit;
        font-size: 0.9rem;
        color: var(--theme-text-main);
        resize: vertical;
    }

    /* Media Uploader Box layout */
    .upload-zone-wrapper {
        border: 2px dashed var(--theme-border);
        border-radius: 6px;
        padding: 32px 16px;
        text-align: center;
        cursor: pointer;
        background-color: #fdfdfd;
        transition: all 0.15s ease;
    }

    .upload-zone-wrapper:hover {
        background-color: #fafafa;
        border-color: var(--theme-primary);
    }

    .upload-zone-wrapper.is-dragging {
        background: var(--theme-primary-light);
        border-color: var(--theme-primary);
    }

    .upload-zone-icon {
        font-size: 1.8rem;
        color: var(--theme-primary);
        margin-bottom: 12px;
    }

    .upload-zone-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--theme-text-main);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .upload-zone-sub {
        font-size: 0.72rem;
        color: var(--theme-text-gray);
        margin-top: 4px;
    }

    /* Upload thumbnails styling */
    .thumbnails-wrap-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .thumbnail-img-box {
        position: relative;
        width: 86px;
        height: 86px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid var(--theme-border);
        background: #ffffff;
        cursor: grab;
    }

    .thumbnail-img-box.is-dragging {
        opacity: 0.45;
    }

    .thumbnail-img-box.is-selected {
        box-shadow: 0 0 0 2px rgba(255, 120, 45, 0.28);
    }

    .thumbnail-img-box.is-primary {
        border: 2px solid var(--theme-primary);
    }

    .thumbnail-img-box.pending-delete img {
        opacity: 0.22;
    }

    .thumbnail-img-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .btn-delete-thumb {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 16px;
        height: 16px;
        background-color: rgba(0, 0, 0, 0.6);
        color: #ffffff;
        border: none;
        border-radius: 50%;
        font-size: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .btn-delete-thumb:hover {
        background-color: var(--theme-danger);
    }

    .btn-primary-thumb {
        position: absolute;
        left: 3px;
        bottom: 3px;
        width: 24px;
        height: 24px;
        border: 0;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.65);
        color: #ffffff;
        cursor: pointer;
    }

    .thumbnail-primary-label {
        position: absolute;
        left: 3px;
        top: 3px;
        border-radius: 999px;
        padding: 2px 6px;
        background: var(--theme-primary);
        color: #ffffff;
        font-size: 0.58rem;
        font-weight: 800;
    }

    .btn-undo-delete {
        position: absolute;
        inset: 0;
        border: 0;
        background: rgba(255, 255, 255, 0.76);
        color: var(--theme-primary);
        cursor: pointer;
        font-size: 0.72rem;
        font-weight: 800;
    }

    .image-upload-error {
        display: none;
        margin-top: 10px;
        color: var(--theme-danger);
        font-size: 0.76rem;
        line-height: 1.45;
    }

    .image-upload-error.visible {
        display: block;
    }

    .image-alt-editor {
        margin-top: 14px;
    }

    .image-alt-editor textarea {
        min-height: 76px;
        resize: vertical;
    }

    .thumbnail-btn-add {
        width: 86px;
        height: 86px;
        border-radius: 6px;
        border: 1px dashed var(--theme-border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--theme-text-gray);
        cursor: pointer;
        font-size: 1.1rem;
        transition: all 0.15s ease;
    }

    .thumbnail-btn-add:hover {
        border-color: var(--theme-primary);
        color: var(--theme-primary);
        background-color: var(--theme-primary-light);
    }

    /* Tags Styling */
    .tags-input-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        padding: 8px 10px;
        background-color: #ffffff;
    }

    .tag-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background-color: #f3f4f6;
        color: var(--theme-text-main);
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .tag-pill-btn-remove {
        border: none;
        background: none;
        color: var(--theme-text-gray);
        cursor: pointer;
        font-size: 0.7rem;
    }

    .tag-pill-btn-remove:hover {
        color: var(--theme-danger);
    }

    .tag-field-input {
        border: none;
        outline: none;
        font-family: inherit;
        font-size: 0.85rem;
        color: var(--theme-text-main);
        flex-grow: 1;
        min-width: 60px;
        padding: 2px;
    }

    /* Product Variants Config styling */
    .variants-card-headline {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #f3f4f6;
        padding-bottom: 12px;
    }

    .variants-card-headline h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--theme-text-main);
    }

    .variants-card-headline i {
        color: var(--theme-primary);
    }

    .btn-add-attribute-rule {
        background: none;
        border: none;
        color: var(--theme-primary);
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: color 0.1s ease;
    }

    .btn-add-attribute-rule:hover {
        color: var(--theme-primary-hover);
        text-decoration: underline;
    }

    .attribute-group-card {
        background-color: #f9fafb;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        padding: 16px;
        margin-bottom: 20px;
        display: grid;
        grid-template-columns: 1fr 2fr auto;
        gap: 16px;
        align-items: flex-end;
    }

    .btn-delete-attribute-row {
        background-color: #ffffff;
        border: 1px solid var(--theme-border);
        color: var(--theme-danger);
        width: 38px;
        height: 38px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.15s ease;
        margin-bottom: 10px;
    }

    .btn-delete-attribute-row:hover {
        background-color: #fef2f2;
        border-color: rgba(239, 68, 68, 0.2);
    }

    /* Variants table styling */
    .variants-list-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 14px;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
    }

    .variants-list-table th {
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--theme-text-gray);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background-color: #f9fafb;
        padding: 10px 14px;
        border-bottom: 1px solid var(--theme-border);
        text-align: left;
    }

    .variants-list-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--theme-border);
        font-size: 0.88rem;
        color: var(--theme-text-main);
        vertical-align: middle;
    }

    .cell-input-small {
        padding: 6px 10px;
        border: 1px solid var(--theme-border);
        border-radius: 4px;
        font-family: inherit;
        font-size: 0.85rem;
        color: var(--theme-text-main);
        outline: none;
        width: 100%;
    }

    .cell-input-small:focus {
        border-color: var(--theme-primary);
    }

    .variant-builder-tools {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .product-attributes-overview {
        margin-top: 14px;
        padding: 14px;
        border: 1px solid var(--theme-border);
        border-radius: 10px;
        background: #fafcfb;
    }

    .product-attributes-overview-head,
    .variant-missing-combinations {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .product-attributes-overview-head strong {
        color: var(--theme-text-main);
        font-size: .88rem;
    }

    .product-attributes-overview-head span,
    .variant-missing-combinations span {
        color: var(--theme-text-gray);
        font-size: .78rem;
    }

    .product-attribute-groups {
        display: grid;
        gap: 10px;
        margin-top: 12px;
    }

    .product-attribute-group {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .product-attribute-name {
        min-width: 88px;
        padding-top: 5px;
        color: var(--theme-text-gray);
        font-size: .76rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .product-attribute-values {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .product-attribute-value {
        border: 1px solid #b8dfc6;
        border-radius: 999px;
        padding: 4px 8px;
        color: #18703a;
        background: #edf9f0;
        font-size: .77rem;
        font-weight: 700;
    }

    .variant-missing-combinations {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid var(--theme-border);
    }

    .variant-missing-combinations[hidden] { display: none; }

    .variant-builder-note {
        color: var(--theme-text-gray);
        font-size: 0.82rem;
    }

    .variant-option-picker {
        display: grid;
        grid-template-columns: minmax(120px, 0.9fr) minmax(120px, 1fr) auto;
        gap: 8px;
        margin-bottom: 8px;
    }

    .variant-option-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        min-height: 28px;
    }

    .variant-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--theme-primary-light);
        color: var(--theme-primary);
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 0.75rem;
        font-weight: 800;
    }

    .variant-chip button {
        border: 0;
        background: none;
        color: inherit;
        cursor: pointer;
        font-size: 0.8rem;
        padding: 0;
    }

    .btn-add-variant-row {
        background: var(--theme-primary);
        border: 0;
        border-radius: 6px;
        color: #ffffff;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.82rem;
        font-weight: 800;
        padding: 9px 12px;
    }

    .btn-variant-mini {
        align-items: center;
        border: 1px solid var(--theme-border);
        background: var(--theme-primary);
        border-radius: 4px;
        cursor: pointer;
        color: #ffffff;
        display: inline-flex;
        gap: 5px;
        height: 34px;
        padding: 0 10px;
    }

    /* Chu nau tren nen cam nhat truoc day bi chim (tuong phan 1.85).
       Chu trang tren nen cam dam hon dat 3.55 - du ro cho chu dam. */
    .js-toggle-variant-visibility { color: #ffffff; background: #e35f12; border-color: #e35f12; }

    .variant-card-actions { display: flex; align-items: center; gap: 8px; }
    /* Nut xoa dung tong do de tach khoi hanh dong an/hien. */
    .btn-variant-delete { color: #b42318; background: #fff; border-color: #f3c4bf; }
    .btn-variant-delete:hover { color: #ffffff; background: #b42318; border-color: #b42318; }

    .variant-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .variant-summary-grid > div {
        padding: 12px;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        background: #fafcfb;
    }

    .variant-summary-grid span,
    .variant-summary-grid strong {
        display: block;
    }

    .variant-summary-grid span {
        color: var(--theme-text-gray);
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .variant-summary-grid strong {
        margin-top: 4px;
        color: var(--theme-text-main);
        font-size: 1rem;
    }

    .variants-card-list {
        display: grid;
        gap: 12px;
    }

    .variant-editor-card {
        border: 1px solid var(--theme-border);
        border-radius: 10px;
        padding: 16px;
        background: #fff;
    }

    .variant-editor-card.is-new {
        border-color: var(--theme-primary);
        background: var(--theme-primary-light);
    }

    .variant-editor-card.is-low-stock {
        border-color: #ef4444;
        background: #fff8f7;
        box-shadow: 0 0 0 1px rgba(239, 68, 68, .08);
    }

    .variant-editor-card.is-out-of-stock {
        border-color: #dc2626;
        background: #fef2f2;
        box-shadow: 0 0 0 1px rgba(220, 38, 38, .12);
    }

    .variant-card-header,
    .variant-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .variant-card-toggle {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 0;
        border: 0;
        background: transparent;
        text-align: left;
        cursor: pointer;
        /* Button khong tu ke thua font cua body, phai khai bao lai neu khong
           chu "Bien the moi" se dung font mac dinh cua he dieu hanh. */
        font-family: inherit;
    }

    .variant-card-toggle::after {
        content: '\f078';
        margin-left: 8px;
        color: var(--theme-text-gray);
        font-family: "Font Awesome 6 Free";
        font-size: .76rem;
        font-weight: 900;
        transition: transform .16s ease;
    }

    .variant-editor-card:not(.is-collapsed) .variant-card-toggle::after { transform: rotate(180deg); }
    .variant-editor-card.is-collapsed .variant-card-details { display: none; }

    .variant-add-menu { position: relative; }
    .variant-add-menu-list {
        position: absolute;
        z-index: 5;
        top: calc(100% + 6px);
        right: 0;
        min-width: 190px;
        padding: 5px;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .13);
    }

    .variant-add-menu-list[hidden] { display: none; }
    .variant-add-menu-item {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 8px;
        padding: 9px;
        border: 0;
        border-radius: 5px;
        background: transparent;
        color: var(--theme-text-main);
        text-align: left;
        cursor: pointer;
        font-size: .82rem;
        font-weight: 700;
    }
    .variant-add-menu-item:hover { background: var(--theme-primary-light); color: var(--theme-primary); }

    .variant-card-title {
        display: block;
        color: var(--theme-text-main);
        font-size: .95rem;
        font-weight: 800;
    }

    .variant-card-sku {
        display: block;
        margin-top: 3px;
        color: var(--theme-text-gray);
        font-size: .76rem;
    }

    .variant-sku-hint {
        display: block;
        margin-top: 5px;
        color: var(--theme-text-gray);
        font-size: .73rem;
    }

    .js-variant-sku.is-duplicate {
        border-color: #dc2626;
        background: #fff7f7;
    }

    .variant-card-status {
        border-radius: 999px;
        padding: 10px 10px;
        font-size: .72rem;
        font-weight: 800;
    }

    .variant-card-status.active { color: var(--theme-success); background: rgba(11, 228, 91, 0.72); }
    .variant-card-status.inactive { color: var(--theme-text-gray); background: rgba(128, 128, 128, 0.72); }
    .variant-card-status-group { display: inline-flex; flex-wrap: wrap; justify-content: flex-end; gap: 6px; }
    .variant-stock-status { border-radius: 999px; padding: 10px; font-size: .72rem; font-weight: 800; }
    .variant-stock-status.low { color: #b91c1c; background: #fee2e2; }
    .variant-stock-status.out { color: #fff; background: #dc2626; }
    .variant-stock-status[hidden] { display: none; }

    .variant-price-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin: 16px 0;
    }

    .variant-card-field label {
        display: block;
        margin-bottom: 5px;
        color: var(--theme-text-gray);
        /* Dong bo voi .form-field-label de nhan trong the bien the khong bi
           chen chuc so voi nhan o phan thong tin san pham. */
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .price-format-preview {
        display: block;
        min-height: 17px;
        margin-top: 4px;
        color: var(--theme-primary);
        font-size: .72rem;
        font-weight: 800;
    }

    .variant-card-footer {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid var(--theme-border);
    }

    .variant-visibility-toggle {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: var(--theme-text-gray);
        font-size: .8rem;
        font-weight: 700;
    }

    .variants-empty-state {
        padding: 30px;
        border: 1px dashed var(--theme-border);
        border-radius: 8px;
        text-align: center;
        color: var(--theme-text-gray);
    }

    /* Fixed Actions Bottom Bar */
    .bottom-fixed-actions-bar {
        position: fixed;
        bottom: 0;
        left: 260px; /* Aligns perfectly with layout sidebar width */
        right: 0;
        background-color: #ffffff;
        border-top: 1px solid var(--theme-border);
        padding: 16px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 100;
        box-shadow: 0 -4px 10px rgba(0,0,0,0.03);
    }

    .last-edited-text {
        font-size: 0.78rem;
        color: var(--theme-text-gray);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .last-edited-text i {
        font-size: 0.9rem;
    }

    /* Hide sidebar gap on responsive view */
    @media (max-width: 900px) {
        .create-listing-wrapper {
            grid-template-columns: 1fr;
        }
        .bottom-fixed-actions-bar {
            left: 0;
        }
    }

    /* Ecommerce admin polish */
    .listing-header {
        top: 0;
        margin: -8px 0 18px;
        padding: 14px 0;
        background: color-mix(in srgb, var(--theme-bg) 92%, #ffffff);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--theme-border);
    }

    .listing-title h1 {
        font-size: 1.45rem;
        letter-spacing: 0;
        line-height: 1.2;
    }

    .create-listing-wrapper {
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 18px;
    }

    .layout-column-main,
    .layout-column-sidebar {
        gap: 18px;
        min-width: 0;
    }

    .form-card {
        border-radius: 8px;
        padding: 18px;
        box-shadow: none;
    }

    .form-card-title,
    .variants-card-headline {
        margin-bottom: 16px;
        padding-bottom: 10px;
    }

    .form-card-title {
        font-size: 1rem;
    }

    .form-control-row {
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 14px;
    }

    .product-name-slug-row {
        grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
    }

    .slug-input-wrap {
        display: flex;
        gap: 8px;
    }

    .slug-regenerate-button {
        flex: 0 0 auto;
        border: 1px solid var(--theme-border);
        border-radius: 6px;
        background: #ffffff;
        color: var(--theme-primary);
        cursor: pointer;
        font-weight: 800;
        padding: 0 12px;
    }

    .slug-field-note {
        color: var(--theme-text-gray);
        font-size: 0.75rem;
        margin-top: 5px;
    }

    .seo-field-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .seo-character-count {
        color: var(--theme-text-gray);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .seo-character-count.warning {
        color: var(--theme-warning);
    }

    .seo-counter-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .seo-limit-warning {
        display: none;
        color: #dc2626;
        font-size: 0.75rem;
        font-weight: 800;
    }

    .seo-limit-warning.is-visible {
        display: inline;
    }

    .input-text-field.seo-limit-exceeded {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    .seo-description-field {
        resize: none;
        min-height: 88px;
        overflow-y: hidden;
        line-height: 1.55;
    }

    .short-description-field {
        resize: none;
        min-height: 96px;
        overflow-y: hidden;
        line-height: 1.55;
    }

    .seo-preview {
        margin-top: 18px;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        padding: 16px;
        background: #ffffff;
    }

    .seo-preview-label {
        display: block;
        margin-bottom: 12px;
        color: var(--theme-text-gray);
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .seo-preview-title {
        margin: 0 0 4px;
        color: #1a0dab;
        font-size: 1.1rem;
        font-weight: 500;
        line-height: 1.3;
    }

    .seo-preview-url {
        margin-bottom: 5px;
        color: #188038;
        font-size: 0.82rem;
        word-break: break-all;
    }

    .seo-preview-description {
        margin: 0;
        color: #4d5156;
        font-size: 0.86rem;
        line-height: 1.5;
    }

    .input-text-field,
    .input-select-field,
    .cell-input-small {
        min-height: 40px;
    }

    .editor-textarea {
        min-height: 150px;
        line-height: 1.55;
    }

    .upload-zone-wrapper {
        padding: 24px 14px;
    }

    .thumbnails-wrap-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(64px, 1fr));
    }

    .thumbnail-img-box,
    .thumbnail-btn-add {
        width: 100%;
        aspect-ratio: 1;
        height: auto;
    }

    .variants-list-table {
        min-width: 980px;
        table-layout: fixed;
        overflow: hidden;
    }

    .variants-list-table th,
    .variants-list-table td {
        padding: 10px;
    }

    .variant-builder-tools {
        align-items: center;
        background: #f9fafb;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        padding: 10px;
    }

    .variant-option-picker {
        gap: 6px;
    }

    .variant-chip {
        max-width: 100%;
        word-break: break-word;
    }

    .btn-action-cancel,
    .btn-action-save,
    .btn-add-variant-row {
        min-height: 40px;
        white-space: nowrap;
    }

    .bottom-fixed-actions-bar {
        padding: 12px 28px;
        box-shadow: 0 -10px 24px rgba(31, 46, 42, 0.08);
    }

    @media (max-width: 1180px) {
        .create-listing-wrapper {
            grid-template-columns: 1fr;
        }

        .layout-column-sidebar {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .listing-header,
        .variant-builder-tools,
        .bottom-fixed-actions-bar {
            align-items: stretch;
            flex-direction: column;
        }

        .action-header-buttons {
            width: 100%;
        }

        .action-header-buttons > * {
            flex: 1;
        }

        .layout-column-sidebar {
            display: flex;
        }

        .form-card {
            padding: 14px;
        }

        .product-name-slug-row {
            grid-template-columns: 1fr;
        }

        .variant-summary-grid,
        .variant-price-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endsection

@section('content')

    <!-- Sticky Header Row -->
    <div class="listing-header">
        <div class="listing-title">
            <h1>{{ $isCreate ? 'Thêm Sản Phẩm' : 'Sửa Sản Phẩm' }}</h1>
        </div>
       
    </div>

    <!-- Main Form Grid wrapper -->
    <form id="product-edit-form" action="{{ $isCreate ? route('admin.products.store') : route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @unless($isCreate)
            @method('PUT')
        @endunless
        
        <div class="create-listing-wrapper">
            
            <!-- Left Side main information column -->
            <div class="layout-column-main">
                
                <!-- General Info Card -->
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fa-regular fa-circle-question"></i>
                        <span>Thông Tin Chung</span>
                    </div>

                    <div class="form-control-row product-name-slug-row">
                        <div class="form-control-group no-margin">
                            <label for="name" class="form-field-label">Tên Sản Phẩm <span class="required-mark">*</span></label>
                            <input type="text" id="name" name="name" class="input-text-field" required
                                   placeholder="Ví dụ: Thức ăn hạt Royal Canin cho chó con"
                                   value="{{ old('name', $product->name) }}">
                        </div>
                        <div class="form-control-group no-margin">
                            <label for="slug" class="form-field-label">Slug <span class="required-mark">*</span></label>
                            <div class="slug-input-wrap">
                                <input type="text" id="slug" name="slug" class="input-text-field" required maxlength="180"
                                       placeholder="thuc-an-hat-royal-canin"
                                       value="{{ old('slug', $product->slug) }}">
                                <button type="button" id="regenerate-slug" class="slug-regenerate-button" title="Tạo lại slug từ tên">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </div>
                            <div class="slug-field-note">{{ $isCreate ? 'Tự tạo từ tên sản phẩm; bạn có thể chỉnh sửa trước khi tạo.' : 'Slug cũ được giữ để chuyển hướng SEO khi thay đổi.' }}</div>
                        </div>
                    </div>

                    <div class="form-control-group" style="margin-top: 14px;">
                        <div class="seo-field-heading">
                            <label for="short_description" class="form-field-label">Mô Tả Ngắn</label>
                            <span class="seo-counter-status"><span class="seo-limit-warning" data-limit-warning>Quá ký tự</span><span id="short-description-count" class="seo-character-count">0/300</span></span>
                        </div>
                        <textarea id="short_description" name="short_description" class="input-text-field short-description-field" maxlength="500"
                                  placeholder="Tóm tắt lợi ích và đặc điểm nổi bật của sản phẩm">{{ old('short_description', $product->short_description) }}</textarea>
                    </div>

                    <div class="form-control-group" style="margin-top: 10px;">
                        <label for="description" class="form-field-label">Mô Tả Sản Phẩm</label>
                        <div class="product-description-editor">
                            <div id="description-editor"></div>
                            <textarea id="description" name="description" hidden
                                      data-placeholder="Mô tả chi tiết sản phẩm, thành phần và hướng dẫn sử dụng...">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fa-solid fa-magnifying-glass-chart"></i>
                        <span>SEO tìm kiếm</span>
                    </div>

                    <div class="form-control-group">
                        <div class="seo-field-heading">
                            <label for="seo_title" class="form-field-label">Tiêu đề SEO</label>
                            <span class="seo-counter-status"><span class="seo-limit-warning" data-limit-warning>Quá ký tự</span><span id="seo-title-count" class="seo-character-count">0/60</span></span>
                        </div>
                        <input type="text" id="seo_title" name="seo_title" class="input-text-field" maxlength="255"
                               placeholder="Để trống để sử dụng tên sản phẩm"
                               value="{{ old('seo_title', $product->seo_title) }}">
                    </div>

                    <div class="form-control-group no-margin">
                        <div class="seo-field-heading">
                            <label for="seo_description" class="form-field-label">Mô tả SEO</label>
                            <span class="seo-counter-status"><span class="seo-limit-warning" data-limit-warning>Quá ký tự</span><span id="seo-description-count" class="seo-character-count">0/160</span></span>
                        </div>
                        <textarea id="seo_description" name="seo_description" class="input-text-field seo-description-field" maxlength="320"
                                  placeholder="Để trống để sử dụng nội dung mô tả sản phẩm">{{ old('seo_description', $product->seo_description) }}</textarea>
                    </div>

                    <div class="seo-preview" aria-live="polite">
                        <span class="seo-preview-label">Xem trước trên Google</span>
                        <div id="seo-preview-title" class="seo-preview-title"></div>
                        <div id="seo-preview-url" class="seo-preview-url"></div>
                        <p id="seo-preview-description" class="seo-preview-description"></p>
                    </div>
                    @include('Admin.Products._seo_score')
                </div>

                @unless($isCreate)
                    <section class="form-card product-ai-card" id="product-ai-card" data-product-id="{{ $product->id }}">
                        <div class="form-card-title">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span>Trợ lý nội dung AI</span>
                            <button type="button" id="btn-toggle-product-ai" class="product-ai-toggle" aria-expanded="true">Thu gọn</button>
                        </div>
                        <p class="product-ai-note">AI chỉ tạo đề xuất trên form. Nội dung chỉ được lưu khi bạn bấm <strong>Lưu sản phẩm</strong>.</p>
                        <div class="product-ai-actions">
                            <button type="button" class="btn-ai-action" data-ai-action="generate_seo_content">Viết content chuẩn SEO</button>
                            <button type="button" class="btn-ai-action" data-ai-action="improve_existing_content">Cải thiện content</button>
                            <button type="button" class="btn-ai-action" data-ai-action="generate_seo_meta">Tạo SEO title &amp; description</button>
                            <button type="button" class="btn-ai-action" data-ai-action="suggest_product_profile">Gợi ý hồ sơ tư vấn</button>
                            <button type="button" class="btn-ai-action" data-ai-action="audit_seo">Kiểm tra SEO</button>
                            <button type="button" class="btn-ai-action" data-ai-action="generate_image_alt">Tạo alt ảnh</button>
                        </div>
                        <div id="product-ai-status" class="product-ai-status" aria-live="polite" hidden></div>
                        <div id="product-ai-result" class="product-ai-result" hidden></div>
                        <button type="button" id="btn-undo-product-ai" class="btn-ai-undo" hidden>Hoàn tác thay đổi AI</button>
                    </section>
                @endunless


            </div>

            <!-- Right Side media and organization column -->
            <div class="layout-column-sidebar">
                
                <!-- Media Card -->
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fa-regular fa-image"></i>
                        <span>Hình Ảnh <span class="required-mark">*</span> <small id="image-count" style="color: var(--theme-text-gray); font-weight: 600;"></small></span>
                    </div>

                    <div class="upload-zone-wrapper" id="product-image-dropzone" role="button" tabindex="0">
                        <i class="fa-solid fa-cloud-arrow-up upload-zone-icon"></i>
                        <div class="upload-zone-title">Kéo thả hoặc nhấn để tải lên</div>
                        <div class="upload-zone-sub">JPG, PNG, WebP · tối đa 5MB/ảnh · tối đa 8 ảnh.</div>
                        <input type="file" id="product-images-input" name="images[]" multiple accept="image/jpeg,image/png,image/webp" hidden>
                    </div>

                    @php
                        $imageValidationError = $errors->first('images')
                            ?: $errors->first('images.*')
                            ?: $errors->first('deleted_image_ids')
                            ?: $errors->first('primary_image_id')
                            ?: $errors->first('primary_image_new_index')
                            ?: $errors->first('image_order')
                            ?: $errors->first('image_alt_texts')
                            ?: $errors->first('new_image_alt_texts');
                    @endphp
                    <div id="image-upload-error" class="image-upload-error {{ $imageValidationError ? 'visible' : '' }}" role="alert">{{ $imageValidationError }}</div>
                    <div id="deleted-image-inputs"></div>
                    <div id="image-metadata-inputs"></div>
                    <input type="hidden" id="image-alt-payload" name="image_alt_payload" value="[]">
                    <input type="hidden" id="primary-image-id" name="primary_image_id" value="{{ $product->primaryImage?->id }}">
                    <input type="hidden" id="primary-image-new-index" name="primary_image_new_index" value="">

                    <div class="thumbnails-wrap-row" id="upload-thumbnails-preview">
                        @foreach($product->images->sortByDesc('is_primary') as $img)
                            @php
                                $fallbackImageUrl = asset('image/logo/logo.png');
                                $imgSrc = $fallbackImageUrl;
                                $path = ltrim((string) $img->image_url, '/');

                                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                                    $imgSrc = $path;
                                } elseif (str_starts_with($path, 'storage/') || str_starts_with($path, 'image/')) {
                                    $imgSrc = asset($path);
                                } elseif (str_starts_with($path, 'products/')) {
                                    $imgSrc = asset('storage/'.$path);
                                } elseif ($path !== '') {
                                    $imgSrc = asset('storage/'.$path);
                                }

                                $imgAlt = $img->alt_text ?: $product->name.($loop->iteration > 1 ? ' - ảnh '.$loop->iteration : '');
                            @endphp
                            <div class="thumbnail-img-box js-existing-image {{ $img->is_primary ? 'is-primary' : '' }}" draggable="true" data-image-id="{{ $img->id }}" data-alt-text="{{ $img->alt_text ?? '' }}">
                                <img src="{{ $imgSrc }}" alt="{{ $imgAlt }}"
                                     onerror="this.onerror=null;this.src='{{ $fallbackImageUrl }}';">
                                @if($img->is_primary)
                                    <span class="thumbnail-primary-label">Ảnh chính</span>
                                @endif
                                <button type="button" class="btn-primary-thumb js-set-primary" title="Đặt làm ảnh chính"><i class="fa-solid fa-star"></i></button>
                                <button type="button" class="btn-delete-thumb js-delete-existing-image" title="Xóa ảnh">&times;</button>
                            </div>
                        @endforeach
                        <div class="thumbnail-btn-add" id="add-product-image" role="button" tabindex="0">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                    </div>
                    <div class="image-alt-editor">
                        <label for="image-alt-text" class="form-field-label">Mô tả ảnh (Alt text)</label>
                        <textarea id="image-alt-text" class="input-text-field" maxlength="255" disabled placeholder="Chọn một ảnh để thêm mô tả SEO">
                        </textarea>
                        <div class="slug-field-note"><span id="image-alt-count">0</span>/125 ký tự khuyến nghị.</div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fa-solid fa-sitemap"></i>
                        <span>Phân loại</span>
                    </div>

                    <div class="form-control-group">
                        <label for="category_id" class="form-field-label">Danh Mục <span class="required-mark">*</span></label>
                        <select id="category_id" name="category_id" class="input-select-field" required>
                            <option value="" disabled>Chọn danh mục</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control-group no-margin">
                        <label for="brand_id" class="form-field-label">Thương Hiệu <span class="required-mark">*</span></label>
                        <select id="brand_id" name="brand_id" class="input-select-field" required>
                            <option value="" disabled>Chọn thương hiệu</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @include('Admin.Products._pet_species')
                </div>
                @include('Admin.Products._advice_attributes')

                <div class="form-card">
                    <div class="form-card-title" style="border: none; margin-bottom: 0;">
                        <i class="fa-solid fa-tags"></i>
                        <span>Thẻ sản phẩm</span>
                    </div>
                    <div class="form-control-group">
                        <label for="tags_input" class="form-field-label">Thẻ</label>
                        <div class="tags-input-container">
                            <span class="tag-pill">
                                Hữu cơ
                                <button type="button" class="tag-pill-btn-remove" onclick="this.closest('.tag-pill').remove();">&times;</button>
                            </span>
                            <span class="tag-pill">
                                Chó con
                                <button type="button" class="tag-pill-btn-remove" onclick="this.closest('.tag-pill').remove();">&times;</button>
                            </span>
                            <input type="text" id="tags_input" placeholder="Thêm thẻ..." class="tag-field-input">
                        </div>
                    </div>
                </div>

            </div>

        </div>
         <!-- Variants Attributes Config Card -->
                <div class="form-card">
                    <div class="variants-card-headline">
                        <h3>
                            <i class="fa-solid fa-sliders"></i>
                            <span>Biến Thể Sản Phẩm</span>
                        </h3>
                    </div>

                    <div class="variant-builder-tools">
                        <span class="variant-builder-note">Thiết lập thuộc tính trước, sau đó nhập giá và tồn kho cho từng biến thể.</span>
                        <div class="variant-add-menu">
                            <button type="button" id="btn-toggle-variant-add-menu" class="btn-add-variant-row" aria-expanded="false" aria-controls="variant-add-menu-list">
                                <i class="fa-solid fa-plus"></i> Thêm
                            </button>
                            <div id="variant-add-menu-list" class="variant-add-menu-list" hidden>
                                <button type="button" class="variant-add-menu-item" data-add-variant="attribute"><i class="fa-solid fa-sliders"></i> Thêm thuộc tính</button>
                                <button type="button" class="variant-add-menu-item" data-add-variant="variant"><i class="fa-solid fa-box"></i> Thêm biến thể</button>
                            </div>
                        </div>
                    </div>

                    <div class="product-attributes-overview" aria-live="polite">
                        <div class="product-attributes-overview-head">
                            <strong>Thuộc tính sản phẩm</strong>
                            <span id="product-attributes-note">Chưa có thuộc tính nào</span>
                        </div>
                        <div id="product-attribute-groups" class="product-attribute-groups"></div>
                        <div id="variant-missing-combinations" class="variant-missing-combinations" hidden>
                            <span id="variant-missing-note"></span>
                            <button type="button" id="btn-generate-missing-variants" class="btn-add-variant-row">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Tạo biến thể còn thiếu
                            </button>
                        </div>
                    </div>

                    <div class="variant-summary-grid" id="variant-summary" aria-live="polite">
                        <div><span>Biến thể</span><strong id="variant-count">0</strong></div>
                        <div><span>Đang hiển thị</span><strong id="variant-active-count">0</strong></div>
                        <div><span>Tổng tồn kho</span><strong id="variant-stock-total">0</strong></div>
                        <div><span>Khoảng giá</span><strong id="variant-price-range">Chưa có giá</strong></div>
                    </div>

                    <div id="variants-card-list" class="variants-card-list" style="margin-top: 14px;">
                        <div id="variants-empty-state" class="variants-empty-state">
                            Chưa có biến thể. Bấm <strong>Thêm biến thể</strong> để tạo SKU đầu tiên.
                        </div>
                    </div>
                </div>

        <!-- Fixed Bottom Drawer Action Bar -->
        <div class="bottom-fixed-actions-bar">
            <div class="last-edited-text">
                <i class="fa-solid fa-history"></i>
                <span>{{ $isCreate ? 'Sản phẩm mới chưa được lưu' : 'Chỉnh sửa lần cuối bởi Admin: Vừa xong' }}</span>
            </div>
            <div class="action-header-buttons">
                <a href="{{ route('admin.products') }}" class="btn-action-cancel">Hủy</a>
                <button type="submit" class="btn-action-save">{{ $isCreate ? 'Tạo sản phẩm' : 'Lưu sản phẩm' }}</button>
            </div>
        </div>
    </form>

    <div id="product-save-modal" class="product-save-modal" hidden role="dialog" aria-modal="true" aria-labelledby="product-save-modal-title">
        <div class="product-save-modal-card">
            <div class="product-save-modal-icon"><i class="fa-solid fa-floppy-disk"></i></div>
            <h3 id="product-save-modal-title">{{ $isCreate ? 'Xác nhận tạo sản phẩm' : 'Xác nhận lưu thay đổi' }}</h3>
            <p>{{ $isCreate ? 'Bạn có muốn tạo sản phẩm mới với các thông tin đã nhập?' : 'Bạn có muốn cập nhật thông tin sản phẩm? Dữ liệu sẽ được lưu mà không rời khỏi trang chỉnh sửa.' }}</p>
            <div class="product-save-modal-actions">
                <button type="button" id="btn-cancel-product-save" class="btn-action-cancel">Hủy</button>
                <button type="button" id="btn-confirm-product-save" class="btn-action-save">{{ $isCreate ? 'Tạo sản phẩm' : 'Lưu thay đổi' }}</button>
            </div>
        </div>
    </div>
    <div id="product-save-toast" class="product-save-toast" role="status" aria-live="polite" hidden></div>
    <div id="image-delete-modal" class="product-save-modal" hidden role="dialog" aria-modal="true" aria-labelledby="image-delete-modal-title"><div class="product-save-modal-card"><div class="product-save-modal-icon"><i class="fa-solid fa-trash"></i></div><h3 id="image-delete-modal-title">Xác nhận xóa ảnh</h3><p>Ảnh sẽ bị bỏ khỏi sản phẩm khi lưu. Thao tác này không thể hoàn tác.</p><div class="product-save-modal-actions"><button type="button" id="btn-cancel-image-delete" class="btn-action-cancel">Hủy</button><button type="button" id="btn-confirm-image-delete" class="btn-action-save">Xóa ảnh</button></div></div></div>
    @include('Admin.Products._unsaved_changes_guard')

@endsection

@include('Admin.Products._readiness_panel')

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productEditForm = document.getElementById('product-edit-form');
        const productSaveModal = document.getElementById('product-save-modal');
        const btnCancelProductSave = document.getElementById('btn-cancel-product-save');
        const btnConfirmProductSave = document.getElementById('btn-confirm-product-save');
        const productSaveToast = document.getElementById('product-save-toast');
        const saveButtons = [...document.querySelectorAll('.btn-action-save[type="submit"]')];
        const isCreatingProduct = @json($isCreate);
        const confirmSaveLabel = isCreatingProduct ? 'Tạo sản phẩm' : 'Lưu thay đổi';
        const imageDeleteModal = document.getElementById('image-delete-modal');
        let pendingImageDeleteBox = null;
        let productIsSaving = false;
        let toastTimeout;
        const productAiCard = document.getElementById('product-ai-card');
        const productAiResult = document.getElementById('product-ai-result');
        const productAiStatus = document.getElementById('product-ai-status');
        const undoProductAiButton = document.getElementById('btn-undo-product-ai');
        const toggleProductAiButton = document.getElementById('btn-toggle-product-ai');
        let productAiSuggestions = null;
        let productAiSnapshot = null;

        productEditForm.noValidate = true;

        function confirmImageDelete(box) { pendingImageDeleteBox = box; imageDeleteModal.hidden = false; }
        document.getElementById('btn-cancel-image-delete').addEventListener('click', () => { pendingImageDeleteBox = null; imageDeleteModal.hidden = true; });
        document.getElementById('btn-confirm-image-delete').addEventListener('click', () => {
            const box = pendingImageDeleteBox; if (!box) return;
            if (box.classList.contains('js-new-image')) selectedImages = selectedImages.filter(item => item.key !== box.dataset.imageKey);
            else deletedImageIds.add(Number(box.dataset.imageId));
            if (selectedImageToken === imageToken(box)) selectedImageToken = null;
            if (selectedPrimary?.value === (box.classList.contains('js-new-image') ? box.dataset.imageKey : Number(box.dataset.imageId))) selectedPrimary = null;
            box.remove(); pendingImageDeleteBox = null; imageDeleteModal.hidden = true; updateImageState();
        });

        function showProductSaveToast(message, isError = false) {
            // Dựng bằng DOM thay vì innerHTML để nội dung thông báo luôn được escape.
            productSaveToast.textContent = '';

            const icon = document.createElement('i');
            icon.className = isError
                ? 'fa-solid fa-triangle-exclamation product-save-toast-icon'
                : 'fa-solid fa-circle-check product-save-toast-icon';
            icon.setAttribute('aria-hidden', 'true');

            const text = document.createElement('span');
            text.textContent = message;

            productSaveToast.append(icon, text);
            productSaveToast.classList.toggle('error', isError);
            productSaveToast.hidden = false;
            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => { productSaveToast.hidden = true; }, 3000);
        }

        if (productAiCard) {
            const aiEndpoint = @json(route('admin.products.ai.improve'));
            const csrfToken = productEditForm.querySelector('input[name="_token"]')?.value;
            const aiLabels = {
                short_description: 'Mô tả ngắn', description: 'Mô tả chi tiết', focus_keyword: 'Focus keyword',
                seo_title: 'SEO title', seo_description: 'SEO description', category_id: 'Danh mục',
                pet_species_ids: 'Loài thú cưng phù hợp', advice_life_stages: 'Giai đoạn sống phù hợp',
                advice_needs: 'Nhu cầu được hỗ trợ', image_alts: 'Alt ảnh',
            };

            const selectedText = (selector) => [...productEditForm.querySelectorAll(selector)]
                .filter((input) => input.checked)
                .map((input) => productEditForm.querySelector(`label[for="${input.id}"]`)?.innerText?.trim() || input.value);
            const stripHtml = (value) => {
                const node = document.createElement('div'); node.innerHTML = value || ''; return node.textContent || node.innerText || '';
            };
            const suggestionDisplayValue = (field, value) => {
                if (field === 'category_id') {
                    return document.getElementById('category_id')?.querySelector(`option[value="${CSS.escape(String(value))}"]`)?.textContent?.trim() || String(value);
                }
                const checkboxGroups = {
                    pet_species_ids: 'pet_species_ids',
                    advice_life_stages: 'advice_life_stages',
                    advice_needs: 'advice_needs',
                };
                if (Array.isArray(value) && checkboxGroups[field]) {
                    return value.map((item) => {
                        const input = productEditForm.querySelector(`input[name="${checkboxGroups[field]}[]"][value="${CSS.escape(String(item))}"]`);
                        const label = input ? productEditForm.querySelector(`label[for="${input.id}"]`) : null;
                        return label?.querySelector('strong')?.textContent?.trim() || label?.innerText?.trim() || String(item);
                    }).join(', ');
                }
                return Array.isArray(value) ? value.join(', ') : stripHtml(value);
            };
            const currentProduct = () => ({
                name: document.getElementById('name')?.value || '',
                category: document.getElementById('category_id')?.selectedOptions?.[0]?.text?.trim() || '',
                brand: document.getElementById('brand_id')?.selectedOptions?.[0]?.text?.trim() || '',
                short_description: document.getElementById('short_description')?.value || '',
                description: document.getElementById('description')?.value || '',
                focus_keyword: document.getElementById('focus_keyword')?.value || '',
                seo_title: document.getElementById('seo_title')?.value || '',
                seo_description: document.getElementById('seo_description')?.value || '',
                variants: [...document.querySelectorAll('.variant-card')].map((card) =>
                    [...card.querySelectorAll('select')]
                        .map((select) => select.selectedOptions?.[0]?.textContent?.trim())
                        .filter((value) => value && !value.startsWith('Chọn'))
                        .join(' / ')
                ).filter(Boolean),
                pet_species: selectedText('input[name="pet_species_ids[]"]'),
            });
            const currentImageIds = () => [...document.querySelectorAll('.js-existing-image[data-image-id]')]
                .map((box) => Number(box.dataset.imageId))
                .filter(Number.isInteger);
            const setAiStatus = (message = '', isError = false) => {
                productAiStatus.hidden = !message;
                productAiStatus.textContent = message;
                productAiStatus.classList.toggle('error', isError);
            };
            const setProductAiCollapsed = (collapsed) => {
                productAiCard.classList.toggle('is-collapsed', collapsed);
                toggleProductAiButton.setAttribute('aria-expanded', String(!collapsed));
                toggleProductAiButton.textContent = collapsed ? 'Mở trợ lý' : 'Thu gọn';
            };
            const snapshotAiFields = () => {
                if (productAiSnapshot) return;
                productAiSnapshot = {
                    short_description: document.getElementById('short_description')?.value || '',
                    description: document.getElementById('description')?.value || '',
                    focus_keyword: document.getElementById('focus_keyword')?.value || '',
                    seo_title: document.getElementById('seo_title')?.value || '',
                    seo_description: document.getElementById('seo_description')?.value || '',
                    category_id: document.getElementById('category_id')?.value || '',
                    pet_species_ids: [...productEditForm.querySelectorAll('input[name="pet_species_ids[]"]')].filter((input) => input.checked).map((input) => input.value),
                    advice_life_stages: [...productEditForm.querySelectorAll('input[name="advice_life_stages[]"]')].filter((input) => input.checked).map((input) => input.value),
                    advice_needs: [...productEditForm.querySelectorAll('input[name="advice_needs[]"]')].filter((input) => input.checked).map((input) => input.value),
                    image_alts: [...document.querySelectorAll('.js-existing-image[data-image-id]')].map((box) => ({ id: Number(box.dataset.imageId), alt_text: box.dataset.altText || '' })),
                };
                undoProductAiButton.hidden = false;
            };
            const updateTextField = (field, value) => {
                const element = document.getElementById(field);
                if (!element) return;
                element.value = value;
                element.dispatchEvent(new Event('input', { bubbles: true }));
                element.dispatchEvent(new Event('change', { bubbles: true }));
            };
            const updateCheckboxes = (name, values) => {
                const allowed = new Set(values.map(String));
                productEditForm.querySelectorAll(`input[name="${name}[]"]`).forEach((input) => {
                    input.checked = allowed.has(input.value);
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            };
            const updateImageAlts = (values) => {
                values.forEach(({ id, alt_text: altText }) => {
                    const box = document.querySelector(`.js-existing-image[data-image-id="${CSS.escape(String(id))}"]`);
                    if (box) box.dataset.altText = altText;
                });
                const selectedBox = document.querySelector('.thumbnail-img-box.is-selected');
                if (selectedBox && typeof imageAltInput !== 'undefined') {
                    imageAltInput.value = selectedBox.dataset.altText || '';
                    imageAltCount.textContent = imageAltInput.value.length;
                }
                if (typeof syncImageMetadata === 'function') syncImageMetadata();
            };
            const applyAiField = (field) => {
                const value = productAiSuggestions?.[field];
                if (value === undefined || value === null || (typeof value === 'string' && value.trim() === '') || (Array.isArray(value) && value.length === 0)) return;
                snapshotAiFields();
                if (field === 'description') {
                    updateTextField(field, value);
                    window.dispatchEvent(new CustomEvent('petworld:description-set', { detail: { html: value } }));
                } else if (field === 'category_id') {
                    updateTextField(field, String(value));
                } else if (field === 'image_alts') {
                    updateImageAlts(value);
                } else if (['pet_species_ids', 'advice_life_stages', 'advice_needs'].includes(field)) {
                    const names = { pet_species_ids: 'pet_species_ids', advice_life_stages: 'advice_life_stages', advice_needs: 'advice_needs' };
                    updateCheckboxes(names[field], value);
                } else {
                    updateTextField(field, value);
                }
                setAiStatus('Đã áp dụng đề xuất AI vào form — chưa lưu.');
            };
            const renderAiResult = (data) => {
                productAiSuggestions = data.suggestions || {};
                productAiResult.innerHTML = '';
                Object.entries(aiLabels).forEach(([field, label]) => {
                    const value = productAiSuggestions[field];
                    if (value === undefined || value === null || value === '' || (Array.isArray(value) && value.length === 0)) return;
                    if (field === 'image_alts') {
                        value.forEach((item) => {
                            const box = document.querySelector(`.js-existing-image[data-image-id="${CSS.escape(String(item.id))}"]`);
                            const order = box ? [...document.querySelectorAll('.js-existing-image')].indexOf(box) + 1 : item.id;
                            const suggestion = document.createElement('div'); suggestion.className = 'product-ai-suggestion';
                            const title = document.createElement('strong'); title.textContent = `Alt ảnh ${order}`;
                            const preview = document.createElement('p'); preview.textContent = item.alt_text;
                            const apply = document.createElement('button'); apply.type = 'button'; apply.className = 'product-ai-apply'; apply.textContent = 'Áp dụng';
                            apply.addEventListener('click', () => {
                                productAiSuggestions = { image_alts: [item] };
                                applyAiField('image_alts');
                                productAiSuggestions = data.suggestions || {};
                                suggestion.remove();
                            });
                            suggestion.append(title, preview, apply); productAiResult.appendChild(suggestion);
                        });
                        return;
                    }
                    const box = document.createElement('div'); box.className = 'product-ai-suggestion';
                    const title = document.createElement('strong'); title.textContent = label;
                    const preview = document.createElement('p');
                    preview.textContent = suggestionDisplayValue(field, value);
                    const apply = document.createElement('button'); apply.type = 'button'; apply.className = 'product-ai-apply'; apply.textContent = 'Áp dụng';
                    apply.addEventListener('click', () => {
                        applyAiField(field);
                        box.remove();
                    });
                    box.append(title, preview, apply); productAiResult.appendChild(box);
                });
                if (Array.isArray(data.warnings) && data.warnings.length) {
                    const warnings = document.createElement('ul'); warnings.className = 'product-ai-warning';
                    data.warnings.forEach((warning) => { const item = document.createElement('li'); item.textContent = warning; warnings.appendChild(item); });
                    productAiResult.appendChild(warnings);
                }
                if (Array.isArray(data.audit) && data.audit.length) {
                    const auditTitle = document.createElement('strong'); auditTitle.textContent = 'Kết quả kiểm tra SEO';
                    const audit = document.createElement('ul'); audit.className = 'product-ai-warning';
                    data.audit.forEach((item) => {
                        const row = document.createElement('li');
                        row.textContent = typeof item === 'string' ? item : (item.message || item.title || 'Có một điểm cần kiểm tra lại.');
                        audit.appendChild(row);
                    });
                    productAiResult.append(auditTitle, audit);
                }
                if (productAiResult.children.length) {
                    const footer = document.createElement('div'); footer.className = 'product-ai-footer';
                    const applyAll = document.createElement('button'); applyAll.type = 'button'; applyAll.className = 'product-ai-apply'; applyAll.textContent = 'Áp dụng tất cả';
                    applyAll.addEventListener('click', () => {
                        Object.keys(aiLabels).forEach(applyAiField);
                        productAiResult.querySelectorAll('.product-ai-suggestion').forEach((suggestion) => suggestion.remove());
                        applyAll.hidden = true;
                    });
                    const finish = document.createElement('button'); finish.type = 'button'; finish.className = 'btn-ai-action'; finish.textContent = 'Xong, kiểm tra form';
                    finish.addEventListener('click', () => setProductAiCollapsed(true));
                    footer.append(applyAll, finish); productAiResult.appendChild(footer);
                    productAiResult.hidden = false;
                } else {
                    productAiResult.hidden = true;
                }
            };
            const runAi = async (action, button) => {
                const name = document.getElementById('name')?.value?.trim();
                if (!name) { setAiStatus('Hãy nhập tên sản phẩm trước khi dùng AI.', true); return; }
                const imageIds = action === 'generate_image_alt' ? currentImageIds() : [];
                if (action === 'generate_image_alt' && imageIds.length === 0) { setAiStatus('Hãy lưu ít nhất một ảnh sản phẩm trước khi tạo alt bằng AI.', true); return; }
                const buttons = [...productAiCard.querySelectorAll('.btn-ai-action')];
                buttons.forEach((item) => { item.disabled = true; });
                button.textContent = 'AI đang xử lý...'; setAiStatus('AI đang tạo đề xuất. Nội dung hiện tại chưa thay đổi.'); productAiResult.hidden = true;
                try {
                    const response = await fetch(aiEndpoint, {
                        method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ action, product: currentProduct(), product_id: Number(productAiCard.dataset.productId), image_ids: imageIds, options: { length: action === 'generate_seo_content' ? 'detailed' : 'standard', tone: 'professional' } }),
                    });
                    const json = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(json.message || 'AI chưa thể trả đề xuất.');
                    renderAiResult(json.data || {}); setAiStatus('AI đã tạo đề xuất. Hãy xem và chọn phần muốn áp dụng.');
                } catch (error) { setAiStatus(error.message || 'Không thể kết nối AI. Form chưa thay đổi.', true); }
                finally { buttons.forEach((item) => { item.disabled = false; }); button.textContent = button.dataset.aiLabel; }
            };
            productAiCard.querySelectorAll('.btn-ai-action').forEach((button) => {
                button.dataset.aiLabel = button.textContent;
                button.addEventListener('click', () => runAi(button.dataset.aiAction, button));
            });
            toggleProductAiButton.addEventListener('click', () => {
                setProductAiCollapsed(!productAiCard.classList.contains('is-collapsed'));
            });
            undoProductAiButton.addEventListener('click', () => {
                if (!productAiSnapshot) return;
                Object.entries(productAiSnapshot).forEach(([field, value]) => {
                    if (field === 'description') { updateTextField(field, value); window.dispatchEvent(new CustomEvent('petworld:description-set', { detail: { html: value } })); }
                    else if (field === 'category_id') updateTextField(field, value);
                    else if (field === 'image_alts') updateImageAlts(value);
                    else if (['pet_species_ids', 'advice_life_stages', 'advice_needs'].includes(field)) updateCheckboxes(field === 'pet_species_ids' ? 'pet_species_ids' : field, value);
                    else updateTextField(field, value);
                });
                productAiSnapshot = null; undoProductAiButton.hidden = true; setAiStatus('Đã hoàn tác các thay đổi AI trên form.');
            });
        }

        function productFieldLabel(field) {
            const explicitLabel = field.id ? productEditForm.querySelector(`label[for="${CSS.escape(field.id)}"]`) : null;
            const nearbyLabel = field.closest('.form-control-group, .variant-card-field')?.querySelector('label');
            const label = explicitLabel || nearbyLabel;
            const fallback = field.placeholder || field.name || 'trường bắt buộc';

            return (label?.textContent || fallback).replace('*', '').replace(/\s+/g, ' ').trim();
        }

        function invalidProductFields() {
            return [...productEditForm.querySelectorAll('input, select, textarea')]
                .filter(field => field.willValidate && !field.checkValidity());
        }

        function showMissingFieldsToast(invalidFields) {
            // Trường có thông báo riêng (vd: giá giảm bằng 0) thì hiện đúng câu đó,
            // đừng gộp vào câu "nhập đủ thông tin bắt buộc" gây khó hiểu.
            const custom = invalidFields.find(field => field.validationMessage && !field.validity.valueMissing);

            if (custom) {
                showProductSaveToast(custom.validationMessage, true);
            } else {
                const fields = [...new Set(invalidFields.map(productFieldLabel))].slice(0, 3);
                const remaining = invalidFields.length > 3 ? ` và ${invalidFields.length - 3} trường khác` : '';
                showProductSaveToast(`Vui lòng nhập đủ thông tin bắt buộc: ${fields.join(', ')}${remaining}.`, true);
            }

            // Cuộn tới đúng ô vừa được nhắc tên trong thông báo.
            const firstInvalid = custom || invalidFields[0];
            const variantCard = firstInvalid.closest('.variant-editor-card');
            if (variantCard) setVariantCardOpen(variantCard, true);

            firstInvalid.scrollIntoView({ block: 'center', behavior: 'smooth' });
            setTimeout(() => firstInvalid.focus({ preventScroll: true }), 250);
        }

        function productFormHasMissingFields() {
            const invalidFields = invalidProductFields();
            if (!invalidFields.length) return false;

            showMissingFieldsToast(invalidFields);
            return true;
        }

        function closeProductSaveModal() {
            productSaveModal.hidden = true;
            btnConfirmProductSave.disabled = false;
            btnConfirmProductSave.textContent = confirmSaveLabel;
        }

        productEditForm.addEventListener('submit', function(event) {
            event.preventDefault();
            if (productIsSaving || productFormHasMissingFields()) {
                sessionStorage.removeItem('petworld.product.exit_after_save');
                return;
            }
            productSaveModal.hidden = false;
            btnConfirmProductSave.focus();
        });

        btnCancelProductSave.addEventListener('click', closeProductSaveModal);
        productSaveModal.addEventListener('click', event => {
            if (event.target === productSaveModal && !productIsSaving) closeProductSaveModal();
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !productSaveModal.hidden && !productIsSaving) closeProductSaveModal();
        });

        btnConfirmProductSave.addEventListener('click', async function() {
            updateImageState();
            if (productFormHasMissingFields()) {
                sessionStorage.removeItem('petworld.product.exit_after_save');
                closeProductSaveModal();
                return;
            }

            if (isCreatingProduct) {
                btnConfirmProductSave.disabled = true;
                btnConfirmProductSave.textContent = 'Đang tạo...';
                productEditForm.submit();
                return;
            }

            productIsSaving = true;
            btnConfirmProductSave.disabled = true;
            btnConfirmProductSave.textContent = 'Đang lưu...';
            saveButtons.forEach(button => button.disabled = true);

            try {
                const response = await fetch(productEditForm.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                    body: new FormData(productEditForm),
                });
                const payload = await response.json();

                if (!response.ok) {
                    const errors = Object.values(payload.errors || {}).flat();
                    throw new Error(errors[0] || payload.message || 'Không thể lưu thay đổi.');
                }

                closeProductSaveModal();
                document.querySelector('.last-edited-text span').textContent = `Đã lưu lúc: ${payload.updated_at}`;
                showProductSaveToast(payload.message || 'Đã lưu thay đổi.');
                window.dispatchEvent(new CustomEvent('petworld:product-saved'));
                const exitAfterSave = sessionStorage.getItem('petworld.product.exit_after_save');
                if (exitAfterSave) {
                    sessionStorage.removeItem('petworld.product.exit_after_save');
                    try {
                        const nextUrl = new URL(exitAfterSave, window.location.href);
                        if (nextUrl.origin === window.location.origin) window.location.assign(nextUrl.href);
                    } catch (ignore) { /* URL rời trang không hợp lệ thì chỉ giữ lại trang hiện tại. */ }
                }
            } catch (error) {
                sessionStorage.removeItem('petworld.product.exit_after_save');
                closeProductSaveModal();
                showProductSaveToast(error.message || 'Đã xảy ra lỗi khi lưu sản phẩm.', true);
            } finally {
                productIsSaving = false;
                saveButtons.forEach(button => button.disabled = false);
            }
        });

        const tagsInput = document.getElementById('tags_input');

        tagsInput?.addEventListener('keydown', function(event) {
            if (event.key !== 'Enter') return;

            event.preventDefault();
            const text = this.value.trim();
            if (!text) return;

            const pill = document.createElement('span');
            pill.className = 'tag-pill';
            pill.innerHTML = `${text} <button type="button" class="tag-pill-btn-remove" onclick="this.closest('.tag-pill').remove();">&times;</button>`;
            this.parentNode.insertBefore(pill, this);
            this.value = '';
        });

        const productNameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        const regenerateSlugButton = document.getElementById('regenerate-slug');
        let slugWasEditedManually = false;

        const seoTitleInput = document.getElementById('seo_title');
        const seoDescriptionInput = document.getElementById('seo_description');
        const shortDescriptionInput = document.getElementById('short_description');
        const shortDescriptionCount = document.getElementById('short-description-count');
        const seoTitleCount = document.getElementById('seo-title-count');
        const seoDescriptionCount = document.getElementById('seo-description-count');
        const seoPreviewTitle = document.getElementById('seo-preview-title');
        const seoPreviewUrl = document.getElementById('seo-preview-url');
        const seoPreviewDescription = document.getElementById('seo-preview-description');
        const descriptionInput = document.getElementById('description');

        function slugify(value) {
            return value
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/Đ/g, 'D')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .slice(0, 180)
                .replace(/-+$/g, '');
        }

        productNameInput.addEventListener('input', function() {
            if (!slugWasEditedManually) slugInput.value = slugify(this.value);
            refreshAutomaticVariantSkus();
        });

        slugInput.addEventListener('input', function() {
            slugWasEditedManually = true;
            this.value = slugify(this.value);
        });

        regenerateSlugButton.addEventListener('click', function() {
            slugWasEditedManually = false;
            slugInput.value = slugify(productNameInput.value);
            slugInput.focus();
        });

        function plainText(html) {
            const temporary = document.createElement('div');
            temporary.innerHTML = html || '';

            return (temporary.textContent || '').replace(/\s+/g, ' ').trim();
        }

        function truncate(value, length) {
            return value.length > length ? `${value.slice(0, length - 1).trim()}…` : value;
        }

        function resizeSeoDescription() {
            seoDescriptionInput.style.height = 'auto';
            seoDescriptionInput.style.height = `${seoDescriptionInput.scrollHeight}px`;
        }

        function resizeShortDescription() {
            shortDescriptionInput.style.height = 'auto';
            shortDescriptionInput.style.height = `${shortDescriptionInput.scrollHeight}px`;
        }

        function setCharacterWarning(input, count, limit) {
            const exceeded = input.value.length > limit;
            count.classList.toggle('warning', exceeded);
            input.classList.toggle('seo-limit-exceeded', exceeded);
            input.setAttribute('aria-invalid', exceeded ? 'true' : 'false');
            input.closest('.form-control-group')?.querySelector('[data-limit-warning]')?.classList.toggle('is-visible', exceeded);
        }

        function updateSeoPreview() {
            const title = seoTitleInput.value.trim() || `${productNameInput.value.trim() || 'Tên sản phẩm'} | PetWorld`;
            const description = seoDescriptionInput.value.trim()
                || shortDescriptionInput.value.trim()
                || plainText(descriptionInput.value)
                || 'Mô tả sản phẩm sẽ xuất hiện tại đây khi bạn nhập nội dung.';

            seoTitleCount.textContent = `${seoTitleInput.value.length}/60`;
            seoDescriptionCount.textContent = `${seoDescriptionInput.value.length}/160`;
            shortDescriptionCount.textContent = `${shortDescriptionInput.value.length}/300`;
            setCharacterWarning(seoTitleInput, seoTitleCount, 60);
            setCharacterWarning(seoDescriptionInput, seoDescriptionCount, 160);
            setCharacterWarning(shortDescriptionInput, shortDescriptionCount, 300);
            seoPreviewTitle.textContent = truncate(title, 60);
            seoPreviewUrl.textContent = `PetWorld › shop › ${slugInput.value || 'slug-san-pham'}`;
            seoPreviewDescription.textContent = truncate(description, 160);
            resizeSeoDescription();
            resizeShortDescription();
        }

        [productNameInput, slugInput, shortDescriptionInput, seoTitleInput, seoDescriptionInput, descriptionInput].forEach(input => {
            input.addEventListener('input', updateSeoPreview);
        });

        updateSeoPreview();

        const variantTypes = @json($variantTypeOptions);

        const initialVariants = @json($productVariantRows);

        const btnToggleVariantAddMenu = document.getElementById('btn-toggle-variant-add-menu');
        const variantAddMenuList = document.getElementById('variant-add-menu-list');
        const variantsCardList = document.getElementById('variants-card-list');
        const variantsEmptyState = document.getElementById('variants-empty-state');
        const productAttributeGroups = document.getElementById('product-attribute-groups');
        const productAttributesNote = document.getElementById('product-attributes-note');
        const missingCombinationsPanel = document.getElementById('variant-missing-combinations');
        const missingCombinationsNote = document.getElementById('variant-missing-note');
        const btnGenerateMissingVariants = document.getElementById('btn-generate-missing-variants');
        const basePriceInput = document.getElementById('price');
        const baseSalePriceInput = document.getElementById('sale_price');
        const baseQtyInput = document.getElementById('quantity');

        [basePriceInput, baseSalePriceInput].forEach(input => {
            input?.addEventListener('input', updateVariantSummary);
        });

        let variantIndex = 0;

        function updateVariantsEmptyState() {
            const hasRows = variantsCardList.querySelectorAll('article[data-index]').length > 0;
            variantsEmptyState.hidden = hasRows;
        }

        function typeOptions() {
            return variantTypes
                .map(type => `<option value="${type.id}">${type.name}</option>`)
                .join('');
        }

        function valueOptions(typeId, selectedIds = []) {
            const type = variantTypes.find(item => String(item.id) === String(typeId)) || variantTypes[0];
            if (!type) return '';

            return type.values
                .map(value => `<option value="${value.id}" ${selectedIds.includes(Number(value.id)) ? 'selected' : ''}>${value.value}</option>`)
                .join('');
        }

        function findValue(valueId) {
            for (const type of variantTypes) {
                const value = type.values.find(item => Number(item.id) === Number(valueId));
                if (value) return { type, value };
            }

            return null;
        }

        function renderChips(row, selectedIds) {
            const chips = row.querySelector('.variant-option-chips');
            const hidden = row.querySelector('.variant-hidden-values');
            chips.innerHTML = '';
            hidden.innerHTML = '';

            selectedIds.forEach(valueId => {
                const found = findValue(valueId);
                if (!found) return;

                const chip = document.createElement('span');
                chip.className = 'variant-chip';
                chip.innerHTML = `${found.type.name}: ${found.value.value} <button type="button" data-value-id="${valueId}">&times;</button>`;
                chips.appendChild(chip);

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `variants[${row.dataset.index}][value_ids][]`;
                input.value = valueId;
                hidden.appendChild(input);
            });

            updateVariantCard(row);
            updateVariantSku(row);
            updateVariantSummary();
            updateProductAttributesOverview();
        }

        function activeAttributeGroups() {
            const groups = new Map();

            variantsCardList.querySelectorAll('article[data-index]').forEach(row => {
                selectedIdsFor(row).forEach(valueId => {
                    const found = findValue(valueId);
                    if (!found) return;

                    if (!groups.has(found.type.id)) {
                        groups.set(found.type.id, { type: found.type, values: new Map() });
                    }

                    groups.get(found.type.id).values.set(found.value.id, found.value);
                });
            });

            return [...groups.values()];
        }

        function missingVariantCombinations(groups) {
            if (groups.length < 2 || groups.some(group => group.values.size === 0)) return [];

            let combinations = [[]];
            groups.forEach(group => {
                combinations = combinations.flatMap(combination =>
                    [...group.values.values()].map(value => [...combination, Number(value.id)])
                );
            });

            const existing = new Set([...variantsCardList.querySelectorAll('article[data-index]')]
                .map(row => selectedIdsFor(row).sort((a, b) => a - b).join(',')));

            return combinations.filter(combination => !existing.has([...combination].sort((a, b) => a - b).join(',')));
        }

        function updateProductAttributesOverview() {
            const groups = activeAttributeGroups();
            productAttributeGroups.innerHTML = '';

            if (groups.length === 0) {
                productAttributesNote.textContent = 'Thêm giá trị vào một biến thể để bắt đầu';
            } else {
                const valueCount = groups.reduce((total, group) => total + group.values.size, 0);
                productAttributesNote.textContent = `${groups.length} nhóm thuộc tính · ${valueCount} giá trị đang dùng`;

                groups.forEach(group => {
                    const item = document.createElement('div');
                    item.className = 'product-attribute-group';
                    const values = [...group.values.values()]
                        .map(value => `<span class="product-attribute-value">${value.value}</span>`)
                        .join('');
                    item.innerHTML = `<span class="product-attribute-name">${group.type.name}</span><div class="product-attribute-values">${values}</div>`;
                    productAttributeGroups.appendChild(item);
                });
            }

            const missing = missingVariantCombinations(groups);
            const mayGenerate = missing.length > 0 && missing.length <= 50;
            missingCombinationsPanel.hidden = !mayGenerate;

            if (mayGenerate) {
                missingCombinationsNote.textContent = `Còn thiếu ${missing.length} tổ hợp từ các thuộc tính trên.`;
            } else if (missing.length > 50) {
                missingCombinationsPanel.hidden = false;
                missingCombinationsNote.textContent = `Có ${missing.length} tổ hợp còn thiếu. Hãy thêm từng biến thể để tránh tạo quá nhiều dòng.`;
                btnGenerateMissingVariants.hidden = true;
            }

            if (missing.length <= 50) btnGenerateMissingVariants.hidden = false;
            return missing;
        }

        function updateVariantCard(row) {
            const selectedIds = selectedIdsFor(row);
            const title = selectedIds
                .map(valueId => findValue(valueId)?.value?.value)
                .filter(Boolean)
                .join(' / ');
            const sku = row.querySelector('.js-variant-sku')?.value?.trim() || '';
            const isVisible = row.querySelector('.js-variant-visible')?.checked ?? false;
            const quantityInput = row.querySelector('.js-variant-quantity');
            const quantity = Number(quantityInput?.value);
            const hasQuantity = quantityInput?.value !== '' && Number.isFinite(quantity);
            const isOutOfStock = hasQuantity && quantity === 0;
            const isLowStock = hasQuantity && quantity > 0 && quantity < 10;
            const titleElement = row.querySelector('.js-variant-title');
            const skuPreview = row.querySelector('.js-variant-sku-preview');
            const status = row.querySelector('.js-variant-status');
            const weightInput = row.querySelector('.js-variant-weight');
            const stockStatus = row.querySelector('.js-variant-stock-status');

            if (titleElement) titleElement.textContent = title || (row.dataset.isNew === 'true' ? 'Biến thể mới' : 'Biến thể');
            if (skuPreview) skuPreview.textContent = `SKU: ${sku || 'Chưa nhập SKU'}`;
            if (status) {
                status.textContent = isVisible ? 'Đang hiển thị' : 'Đã ẩn';
                status.classList.toggle('active', isVisible);
                status.classList.toggle('inactive', !isVisible);
            }
            if (stockStatus) {
                stockStatus.hidden = !isLowStock && !isOutOfStock;
                stockStatus.textContent = isOutOfStock ? 'Hết hàng' : 'Sắp hết hàng';
                stockStatus.classList.toggle('low', isLowStock);
                stockStatus.classList.toggle('out', isOutOfStock);
            }
            row.classList.toggle('is-low-stock', isLowStock);
            row.classList.toggle('is-out-of-stock', isOutOfStock);
            updatePricePreview(row.querySelector('.js-variant-price'), row.querySelector('.js-variant-price-preview'));
            updatePricePreview(row.querySelector('.js-variant-sale-price'), row.querySelector('.js-variant-sale-price-preview'));
            if (weightInput) {
                const weight = Number(weightInput.value);
                const weightMissing = isVisible && (!weightInput.value || !Number.isFinite(weight) || weight <= 0);
                weightInput.setCustomValidity(weightMissing ? 'Biến thể đang hiển thị cần cân nặng đóng gói lớn hơn 0g.' : '');
                weightInput.classList.toggle('is-invalid', weightMissing);
            }

            const visibilityButton = row.querySelector('.js-toggle-variant-visibility');
            if (visibilityButton) {
                visibilityButton.innerHTML = isVisible
                    ? '<i class="fa-solid fa-eye-slash"></i><span>Ẩn</span>'
                    : '<i class="fa-solid fa-eye"></i><span>Hiện</span>';
                visibilityButton.title = isVisible ? 'Ẩn biến thể khỏi khách hàng' : 'Hiện biến thể cho khách hàng';
            }
        }

        function formatVariantPrice(value) {
            return `${new Intl.NumberFormat('vi-VN').format(value)}đ`;
        }

        function updatePricePreview(input, preview) {
            if (!input || !preview) return;
            const value = Number(input.value);
            preview.textContent = Number.isFinite(value) && value >= 0 ? formatVariantPrice(value) : '';
        }

        function validateSalePrice(priceInput, salePriceInput) {
            if (!priceInput || !salePriceInput) return;

            const price = Number(priceInput.value);
            const salePrice = salePriceInput.value === '' ? null : Number(salePriceInput.value);

            // Số 0 (hoặc âm) vi phạm ràng buộc DB: để trống mới là "không giảm giá".
            let message = '';
            if (salePrice !== null && salePrice <= 0) {
                message = 'Giá giảm phải lớn hơn 0. Để trống ô này nếu không giảm giá.';
            } else if (salePrice !== null && Number.isFinite(price) && salePrice >= price) {
                message = 'Giá giảm phải nhỏ hơn giá bán.';
            }

            salePriceInput.setCustomValidity(message);
            salePriceInput.classList.toggle('is-invalid', message !== '');
        }

        function updateVariantSummary() {
            const cards = [...variantsCardList.querySelectorAll('article[data-index]')];
            validateSalePrice(basePriceInput, baseSalePriceInput);
            const activeCards = cards.filter(card => card.querySelector('.js-variant-visible')?.checked);
            const stock = cards.reduce((total, card) => total + (Number(card.querySelector('.js-variant-quantity')?.value) || 0), 0);
            const prices = cards
                .map(card => {
                    const price = Number(card.querySelector('.js-variant-price')?.value);
                    const salePrice = Number(card.querySelector('.js-variant-sale-price')?.value);

                    validateSalePrice(
                        card.querySelector('.js-variant-price'),
                        card.querySelector('.js-variant-sale-price'),
                    );

                    return salePrice > 0 && salePrice < price ? salePrice : price;
                })
                .filter(price => Number.isFinite(price) && price >= 0);

            document.getElementById('variant-count').textContent = cards.length;
            document.getElementById('variant-active-count').textContent = activeCards.length;
            document.getElementById('variant-stock-total').textContent = stock;
            document.getElementById('variant-price-range').textContent = prices.length === 0
                ? 'Chưa có giá'
                : Math.min(...prices) === Math.max(...prices)
                    ? formatVariantPrice(Math.min(...prices))
                    : `${formatVariantPrice(Math.min(...prices))} – ${formatVariantPrice(Math.max(...prices))}`;
        }

        function setVariantCardOpen(row, open = true) {
            if (open) {
                variantsCardList.querySelectorAll('.variant-editor-card').forEach(card => {
                    if (card !== row) {
                        card.classList.add('is-collapsed');
                        card.querySelector('.variant-card-toggle')?.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            row.classList.toggle('is-collapsed', !open);
            row.querySelector('.variant-card-toggle')?.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        function addVariantRow(initial = {}, options = {}) {
            const index = variantIndex++;
            const selectedIds = (initial.value_ids || []).map(Number);
            const firstTypeId = variantTypes[0]?.id || '';
            const row = document.createElement('article');
            row.className = `variant-editor-card ${initial.id ? 'is-collapsed' : 'is-new'}`;
            row.dataset.index = index;
            row.dataset.selectedIds = JSON.stringify(selectedIds);
            row.dataset.isNew = initial.id ? 'false' : 'true';

            const sku = initial.sku || '';
            const price = initial.price ?? basePriceInput?.value ?? '';
            const salePrice = initial.sale_price ?? baseSalePriceInput?.value ?? '';
            const quantity = initial.quantity ?? baseQtyInput?.value ?? '';
            const weightGrams = initial.weight_grams ?? '';
            const active = initial.status ? initial.status === 'active' : true;

            row.innerHTML = `
                <div class="variant-card-header">
                    <button type="button" class="variant-card-toggle" aria-expanded="${initial.id ? 'false' : 'true'}">
                        <span>
                            <span class="variant-card-title js-variant-title">${initial.id ? 'Biến thể' : 'Biến thể mới'}</span>
                            <span class="variant-card-sku js-variant-sku-preview">SKU: ${sku || 'Chưa nhập SKU'}</span>
                        </span>
                        <span class="variant-card-status-group">
                            <span class="variant-card-status ${active ? 'active' : 'inactive'} js-variant-status">${active ? 'Đang hiển thị' : 'Đã ẩn'}</span>
                            <span class="variant-stock-status js-variant-stock-status" hidden></span>
                        </span>
                    </button>
                </div>
                <div class="variant-card-details">
                <div class="variant-card-field" style="margin-top: 14px;">
                    <label>Thuộc tính <span class="required-mark">*</span></label>
                    <input type="hidden" name="variants[${index}][id]" value="${initial.id || ''}">
                    <div class="variant-option-picker">
                        <select class="cell-input-small js-variant-type">${typeOptions()}</select>
                        <select class="cell-input-small js-variant-value">${valueOptions(firstTypeId)}</select>
                        <button type="button" class="btn-variant-mini js-add-option" title="Thêm thuộc tính">Thêm thuộc tính</button>
                    </div>
                    <div class="variant-option-chips"></div>
                    <div class="variant-hidden-values"></div>
                </div>
                <div class="variant-card-field" style="margin-top: 14px;">
                    <label>SKU <span class="required-mark">*</span></label>
                    <input type="text" name="variants[${index}][sku]" value="${sku}" class="cell-input-small js-variant-sku" placeholder="Tự tạo từ tên sản phẩm và thuộc tính" required>
                    <small class="variant-sku-hint js-variant-sku-hint">SKU tự tạo từ tên sản phẩm và thuộc tính.</small>
                </div>
                <div class="variant-price-grid">
                    <div class="variant-card-field"><label>Giá bán <span class="required-mark">*</span></label><input type="number" name="variants[${index}][price]" value="${price}" class="cell-input-small js-variant-price" step="1000" min="1000" max="1000000000" required><small class="price-format-preview js-variant-price-preview"></small></div>
                    <div class="variant-card-field"><label>Giá giảm</label><input type="number" name="variants[${index}][sale_price]" value="${salePrice || ''}" class="cell-input-small js-variant-sale-price" step="any" max="1000000000" placeholder="Để trống nếu không giảm"><small class="price-format-preview js-variant-sale-price-preview"></small></div>
                    <div class="variant-card-field"><label>Tồn kho <span class="required-mark">*</span></label><input type="number" name="variants[${index}][quantity]" value="${quantity}" class="cell-input-small js-variant-quantity" min="0" max="100000" required></div>
                    <div class="variant-card-field"><label>Cân nặng đóng gói (g)</label><input type="number" name="variants[${index}][weight_grams]" value="${weightGrams}" class="cell-input-small js-variant-weight" min="0" max="50000" step="1" placeholder="Ví dụ: 1100"><small>Nhập cả bao bì; bắt buộc khi biến thể hiển thị bán.</small></div>
                </div>
                <div class="variant-card-footer">
                    <label class="variant-visibility-toggle"><input type="checkbox" class="js-variant-visible" name="variants[${index}][visible]" value="1" ${active ? 'checked' : ''}> Hiển thị cho khách</label>
                    <div class="variant-card-actions">
                        <button type="button" class="btn-variant-mini btn-variant-delete js-remove-variant" title="Xoá biến thể này khỏi sản phẩm"><i class="fa-solid fa-trash-can"></i><span>Xoá</span></button>
                        <button type="button" class="btn-variant-mini js-toggle-variant-visibility" title="Ẩn biến thể khỏi khách hàng"><i class="fa-solid fa-eye-slash"></i><span>Ẩn</span></button>
                    </div>
                </div>
                </div>
            `;

            variantsCardList.appendChild(row);
            row.querySelector('.js-variant-sku').dataset.skuManual = initial.id && sku ? 'true' : 'false';
            renderChips(row, selectedIds);
            updateVariantsEmptyState();
            updateVariantCard(row);
            updateVariantSummary();
            updateProductAttributesOverview();
            if (!initial.id) {
                setVariantCardOpen(row);
                if (options.focusAttributes) row.querySelector('.js-variant-type')?.focus();
            }
        }

        function selectedIdsFor(row) {
            return JSON.parse(row.dataset.selectedIds || '[]');
        }

        function suggestedVariantSku(row) {
            const productPart = slugify(productNameInput.value).toUpperCase() || 'SAN-PHAM';
            const attributeParts = selectedIdsFor(row)
                .map(valueId => slugify(findValue(valueId)?.value?.value || '').toUpperCase())
                .filter(Boolean);

            return [productPart, ...attributeParts].join('-').slice(0, 120).replace(/-+$/g, '');
        }

        function validateVariantSkus() {
            const inputs = [...variantsCardList.querySelectorAll('.js-variant-sku')];
            const skuGroups = new Map();

            inputs.forEach(input => {
                const key = input.value.trim().toUpperCase();
                if (!key) return;
                if (!skuGroups.has(key)) skuGroups.set(key, []);
                skuGroups.get(key).push(input);
            });

            inputs.forEach(input => {
                const duplicated = input.value.trim() && skuGroups.get(input.value.trim().toUpperCase())?.length > 1;
                input.classList.toggle('is-duplicate', Boolean(duplicated));
                input.setCustomValidity(duplicated ? 'SKU này đang trùng với một biến thể khác.' : '');
                const hint = input.closest('.variant-card-field')?.querySelector('.js-variant-sku-hint');
                if (hint) hint.textContent = duplicated
                    ? 'SKU bị trùng. Hãy đổi SKU hoặc chỉnh thuộc tính.'
                    : input.dataset.skuManual === 'true'
                        ? 'SKU đã được sửa thủ công.'
                        : 'SKU tự tạo từ tên sản phẩm và thuộc tính.';
            });
        }

        function updateVariantSku(row) {
            const skuInput = row.querySelector('.js-variant-sku');
            if (!skuInput) return;

            if (skuInput.dataset.skuManual !== 'true') skuInput.value = suggestedVariantSku(row);
            updateVariantCard(row);
            validateVariantSkus();
        }

        function refreshAutomaticVariantSkus() {
            variantsCardList.querySelectorAll('article[data-index]').forEach(updateVariantSku);
        }

        variantsCardList.addEventListener('change', function(event) {
            const row = event.target.closest('.variant-editor-card');
            if (!row) return;

            if (event.target.classList.contains('js-variant-type')) {
                const valueSelect = row.querySelector('.js-variant-value');
                valueSelect.innerHTML = valueOptions(event.target.value, selectedIdsFor(row));
            }

            updateVariantCard(row);
            updateVariantSummary();
        });

        variantsCardList.addEventListener('click', function(event) {
            const row = event.target.closest('.variant-editor-card');
            if (!row) return;

            if (event.target.closest('.variant-card-toggle')) {
                setVariantCardOpen(row, row.classList.contains('is-collapsed'));
                return;
            }

            if (event.target.closest('.js-add-option')) {
                const valueId = Number(row.querySelector('.js-variant-value').value);
                const ids = selectedIdsFor(row);

                if (valueId && !ids.includes(valueId)) {
                    ids.push(valueId);
                    row.dataset.selectedIds = JSON.stringify(ids);
                    renderChips(row, ids);
                }
            }

            if (event.target.closest('.variant-chip button')) {
                const valueId = Number(event.target.closest('button').dataset.valueId);
                const ids = selectedIdsFor(row).filter(id => id !== valueId);
                row.dataset.selectedIds = JSON.stringify(ids);
                renderChips(row, ids);
            }

            if (event.target.closest('.js-toggle-variant-visibility')) {
                const visibleInput = row.querySelector('.js-variant-visible');
                if (!visibleInput) return;
                visibleInput.checked = !visibleInput.checked;
                updateVariantCard(row);
                updateVariantSummary();
            }

            if (event.target.closest('.js-remove-variant')) {
                const label = row.querySelector('.js-variant-title')?.textContent?.trim() || 'biến thể này';
                if (!confirm(`Xoá ${label}? Thao tác này chỉ có hiệu lực sau khi bạn lưu sản phẩm.`)) return;

                // Không cần đánh số lại: Laravel nhận mảng thưa (variants[0], variants[2]...)
                // và syncSubmittedVariants chỉ duyệt các phần tử thực sự được gửi lên.
                row.remove();
                updateVariantsEmptyState();
                validateVariantSkus();
                updateVariantSummary();
                updateProductAttributesOverview();
            }
        });

        variantsCardList.addEventListener('input', function(event) {
            const row = event.target.closest('.variant-editor-card');
            if (!row) return;

            if (event.target.classList.contains('js-variant-sku')) {
                event.target.dataset.skuManual = 'true';
                validateVariantSkus();
            }

            updateVariantCard(row);
            updateVariantSummary();
        });

        btnToggleVariantAddMenu.addEventListener('click', () => {
            const isOpening = variantAddMenuList.hidden;
            variantAddMenuList.hidden = !isOpening;
            btnToggleVariantAddMenu.setAttribute('aria-expanded', isOpening ? 'true' : 'false');
        });

        variantAddMenuList.addEventListener('click', event => {
            const action = event.target.closest('[data-add-variant]')?.dataset.addVariant;
            if (!action) return;

            variantAddMenuList.hidden = true;
            btnToggleVariantAddMenu.setAttribute('aria-expanded', 'false');
            addVariantRow({}, { focusAttributes: action === 'attribute' });
        });

        document.addEventListener('click', event => {
            if (!event.target.closest('.variant-add-menu')) {
                variantAddMenuList.hidden = true;
                btnToggleVariantAddMenu.setAttribute('aria-expanded', 'false');
            }
        });
        btnGenerateMissingVariants.addEventListener('click', () => {
            const missing = updateProductAttributesOverview();
            missing.forEach(valueIds => addVariantRow({ value_ids: valueIds }));
        });

        if (initialVariants.length > 0) {
            initialVariants.forEach(variant => addVariantRow(variant));
        } else if (@json($isCreate)) {
            addVariantRow();
        }
        updateVariantsEmptyState();
        updateVariantSummary();
        updateProductAttributesOverview();

        const imagesInput = document.getElementById('product-images-input');
        const thumbnailsPreview = document.getElementById('upload-thumbnails-preview');
        const imageDropzone = document.getElementById('product-image-dropzone');
        const addProductImage = document.getElementById('add-product-image');
        const imageCount = document.getElementById('image-count');
        const imageError = document.getElementById('image-upload-error');
        const deletedImageInputs = document.getElementById('deleted-image-inputs');
        const imageMetadataInputs = document.getElementById('image-metadata-inputs');
        const imageAltPayloadInput = document.getElementById('image-alt-payload');
        const primaryImageIdInput = document.getElementById('primary-image-id');
        const primaryImageNewIndexInput = document.getElementById('primary-image-new-index');
        const imageAltInput = document.getElementById('image-alt-text');
        const imageAltCount = document.getElementById('image-alt-count');
        const allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp'];
        const maxImageSize = 5 * 1024 * 1024;
        const maxImages = 8;
        let selectedImages = [];
        let deletedImageIds = new Set();
        let selectedPrimary = primaryImageIdInput.value
            ? { type: 'existing', value: Number(primaryImageIdInput.value) }
            : null;
        let selectedImageToken = null;

        function activeExistingImages() {
            return [...thumbnailsPreview.querySelectorAll('.js-existing-image')]
                .filter(box => !deletedImageIds.has(Number(box.dataset.imageId)));
        }

        function showImageError(message = '') {
            imageError.textContent = message;
            imageError.classList.toggle('visible', message !== '');
        }

        function syncFileInput() {
            const transfer = new DataTransfer();
            selectedImages.forEach(item => transfer.items.add(item.file));
            imagesInput.files = transfer.files;
        }

        function syncDeletedInputs() {
            deletedImageInputs.innerHTML = '';
            deletedImageIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'deleted_image_ids[]';
                input.value = id;
                deletedImageInputs.appendChild(input);
            });
        }

        function imageToken(box) {
            return box.classList.contains('js-existing-image')
                ? `existing:${box.dataset.imageId}`
                : `new:${box.dataset.imageKey}`;
        }

        function syncImageMetadata() {
            const boxes = [...thumbnailsPreview.querySelectorAll('.thumbnail-img-box')]
                .filter(box => !box.classList.contains('pending-delete'));
            const newOrderKeys = boxes
                .filter(box => box.classList.contains('js-new-image'))
                .map(box => box.dataset.imageKey);
            const imagesByKey = new Map(selectedImages.map(image => [image.key, image]));
            if (newOrderKeys.length === selectedImages.length) {
                selectedImages = newOrderKeys.map(key => imagesByKey.get(key)).filter(Boolean);
            }
            imageMetadataInputs.innerHTML = '';
            imageAltPayloadInput.value = JSON.stringify(boxes.map(box => ({
                token: imageToken(box),
                alt_text: box.dataset.altText || '',
            })));

            boxes.forEach(box => {
                const order = document.createElement('input');
                order.type = 'hidden';
                order.name = 'image_order[]';
                order.value = imageToken(box);
                imageMetadataInputs.appendChild(order);

                const alt = document.createElement('input');
                alt.type = 'hidden';
                alt.value = box.dataset.altText || '';
                if (box.classList.contains('js-existing-image')) {
                    alt.name = `image_alt_texts[${box.dataset.imageId}]`;
                } else {
                    alt.name = `new_image_alt_texts[${box.dataset.imageKey}]`;
                    const key = document.createElement('input');
                    key.type = 'hidden';
                    key.name = 'new_image_keys[]';
                    key.value = box.dataset.imageKey;
                    imageMetadataInputs.appendChild(key);
                }
                imageMetadataInputs.appendChild(alt);
            });

            selectedImages
                .filter(image => !newOrderKeys.includes(image.key))
                .forEach(image => {
                    const order = document.createElement('input');
                    order.type = 'hidden';
                    order.name = 'image_order[]';
                    order.value = `new:${image.key}`;

                    const key = document.createElement('input');
                    key.type = 'hidden';
                    key.name = 'new_image_keys[]';
                    key.value = image.key;

                    const alt = document.createElement('input');
                    alt.type = 'hidden';
                    alt.name = `new_image_alt_texts[${image.key}]`;
                    alt.value = image.altText || '';
                    imageMetadataInputs.append(order, key, alt);
                });
        }

        function chooseFallbackPrimary() {
            const existing = activeExistingImages()[0];
            if (existing) return { type: 'existing', value: Number(existing.dataset.imageId) };
            if (selectedImages[0]) return { type: 'new', value: selectedImages[0].key };

            return null;
        }

        function syncPrimaryInputs() {
            if (!selectedPrimary) selectedPrimary = chooseFallbackPrimary();

            primaryImageIdInput.value = selectedPrimary?.type === 'existing' ? selectedPrimary.value : '';
            primaryImageNewIndexInput.value = selectedPrimary?.type === 'new'
                ? selectedImages.findIndex(item => item.key === selectedPrimary.value)
                : '';
        }

        function updateImageState() {
            const total = activeExistingImages().length + selectedImages.length;
            if (!selectedPrimary) selectedPrimary = chooseFallbackPrimary();
            imageCount.textContent = `${total}/${maxImages} ảnh`;
            addProductImage.hidden = total >= maxImages;

            thumbnailsPreview.querySelectorAll('.thumbnail-img-box').forEach(box => {
                box.classList.toggle('is-selected', imageToken(box) === selectedImageToken);
                const isPrimary = box.classList.contains('js-existing-image')
                    ? selectedPrimary?.type === 'existing' && selectedPrimary.value === Number(box.dataset.imageId)
                    : selectedPrimary?.type === 'new' && selectedPrimary.value === box.dataset.imageKey;
                box.classList.toggle('is-primary', isPrimary);
                let label = box.querySelector('.thumbnail-primary-label');

                if (isPrimary && !label) {
                    label = document.createElement('span');
                    label.className = 'thumbnail-primary-label';
                    label.textContent = 'Ảnh chính';
                    box.appendChild(label);
                } else if (!isPrimary && label) {
                    label.remove();
                }
            });

            syncFileInput();
            syncDeletedInputs();
            syncImageMetadata();
            syncPrimaryInputs();
        }

        function renderNewImages() {
            thumbnailsPreview.querySelectorAll('.js-new-image').forEach(box => box.remove());

            selectedImages.forEach(item => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const box = document.createElement('div');
                    box.className = 'thumbnail-img-box js-new-image';
                    box.draggable = true;
                    box.dataset.imageKey = item.key;
                    box.dataset.altText = item.altText || '';
                    box.innerHTML = `
                        <img src="${e.target.result}" alt="Ảnh mới đang chờ lưu">
                        <button type="button" class="btn-primary-thumb js-set-primary" title="Đặt làm ảnh chính"><i class="fa-solid fa-star"></i></button>
                        <button type="button" class="btn-delete-thumb js-delete-new-image" title="Bỏ ảnh">&times;</button>
                    `;
                    thumbnailsPreview.insertBefore(box, addProductImage);
                    updateImageState();
                };
                reader.readAsDataURL(item.file);
            });

            updateImageState();
        }

        function addImages(files) {
            showImageError();
            const errors = [];

            for (const file of files) {
                if (!allowedImageTypes.includes(file.type)) {
                    errors.push(`${file.name}: định dạng không được hỗ trợ.`);
                    continue;
                }
                if (file.size > maxImageSize) {
                    errors.push(`${file.name}: vượt quá 5MB.`);
                    continue;
                }
                if (activeExistingImages().length + selectedImages.length >= maxImages) {
                    errors.push(`Chỉ được sử dụng tối đa ${maxImages} ảnh.`);
                    break;
                }

                const signature = `${file.name}:${file.size}:${file.lastModified}`;
                if (!selectedImages.some(item => item.signature === signature)) {
                    selectedImages.push({ key: crypto.randomUUID(), file, signature, altText: '' });
                }
            }

            if (errors.length) showImageError(errors.join(' '));
            if (!selectedPrimary) selectedPrimary = chooseFallbackPrimary();
            renderNewImages();
        }

        function openImagePicker() {
            imagesInput.click();
        }

        imagesInput.addEventListener('change', function() {
            addImages([...this.files]);
        });
        imageDropzone.addEventListener('click', openImagePicker);
        addProductImage.addEventListener('click', openImagePicker);

        [imageDropzone, addProductImage].forEach(element => {
            element.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openImagePicker();
                }
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            imageDropzone.addEventListener(eventName, event => {
                event.preventDefault();
                imageDropzone.classList.add('is-dragging');
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            imageDropzone.addEventListener(eventName, event => {
                event.preventDefault();
                imageDropzone.classList.remove('is-dragging');
            });
        });
        imageDropzone.addEventListener('drop', event => addImages([...event.dataTransfer.files]));

        function selectImage(box) {
            selectedImageToken = imageToken(box);
            thumbnailsPreview.querySelectorAll('.thumbnail-img-box').forEach(item => {
                item.classList.toggle('is-selected', imageToken(item) === selectedImageToken);
            });
            imageAltInput.disabled = false;
            imageAltInput.name = box.classList.contains('js-existing-image')
                ? `image_alt_texts[${box.dataset.imageId}]`
                : `new_image_alt_texts[${box.dataset.imageKey}]`;
            imageAltInput.value = box.dataset.altText || '';
            imageAltCount.textContent = imageAltInput.value.length;
        }

        function movePrimaryToFront(box) {
            const firstImage = [...thumbnailsPreview.querySelectorAll('.thumbnail-img-box')]
                .find(item => !item.classList.contains('pending-delete'));
            if (firstImage && firstImage !== box) {
                thumbnailsPreview.insertBefore(box, firstImage);
            }
        }

        imageAltInput.addEventListener('input', function() {
            const box = [...thumbnailsPreview.querySelectorAll('.thumbnail-img-box')]
                .find(item => imageToken(item) === selectedImageToken);
            if (!box) return;

            box.dataset.altText = this.value;
            if (box.classList.contains('js-new-image')) {
                const image = selectedImages.find(item => item.key === box.dataset.imageKey);
                if (image) image.altText = this.value;
            }
            imageAltCount.textContent = this.value.length;
            updateImageState();
        });

        let draggedImage = null;
        thumbnailsPreview.addEventListener('dragstart', event => {
            const box = event.target.closest('.thumbnail-img-box');
            if (!box || box.classList.contains('pending-delete')) return;
            draggedImage = box;
            box.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
        });
        thumbnailsPreview.addEventListener('dragover', event => {
            if (!draggedImage) return;
            const target = event.target.closest('.thumbnail-img-box');
            if (!target || target === draggedImage || target.classList.contains('pending-delete')) return;
            event.preventDefault();
            const before = event.clientX < target.getBoundingClientRect().left + target.offsetWidth / 2;
            thumbnailsPreview.insertBefore(draggedImage, before ? target : target.nextSibling);
        });
        thumbnailsPreview.addEventListener('dragend', () => {
            if (!draggedImage) return;
            draggedImage.classList.remove('is-dragging');
            draggedImage = null;
            updateImageState();
        });

        thumbnailsPreview.addEventListener('click', event => {
            const box = event.target.closest('.thumbnail-img-box');
            if (!box) return;

            if (!event.target.closest('button')) selectImage(box);

            if (event.target.closest('.js-set-primary')) {
                selectedPrimary = box.classList.contains('js-existing-image')
                    ? { type: 'existing', value: Number(box.dataset.imageId) }
                    : { type: 'new', value: box.dataset.imageKey };
                movePrimaryToFront(box);
                selectImage(box);
                updateImageState();
            }

            if (event.target.closest('.js-delete-new-image')) {
                confirmImageDelete(box);
                return;
            }

            if (event.target.closest('.js-delete-existing-image')) {
                confirmImageDelete(box);
            }
        });

        document.getElementById('product-edit-form').addEventListener('submit', event => {
            if (activeExistingImages().length + selectedImages.length < 1) {
                event.preventDefault();
                showImageError('Sản phẩm phải có ít nhất một ảnh.');
                imageDropzone.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            syncPrimaryInputs();
        });

        updateImageState();
    });
</script>
@endsection
