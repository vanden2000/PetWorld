@extends('admin.layouts.app')

@section('title', 'Bình luận bài viết')

@section('styles')
    <style>
        .comment-page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 18px;
        }

        .comment-page-header h1 {
            margin: 0;
            padding-left: 12px;
            border-left: 4px solid #ff782d;
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--primary);
        }

        .comment-page-header p {
            margin: 6px 0 0;
            color: var(--text-muted);
            font-size: .92rem;
        }

        .comment-total {
            padding: 7px 11px;
            border-radius: 999px;
            background: #fff1e8;
            color: #b95008;
            font-size: .82rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .comment-filter-bar {
            display: grid;
            grid-template-columns: minmax(280px, 560px) minmax(190px, 230px) auto;
            align-items: end;
            gap: 14px;
            padding: 14px 16px;
            margin-bottom: 16px;
            border: 1px solid #f3d8c7;
            border-radius: 12px;
            background: #fffdfa;
            box-shadow: 0 4px 14px rgba(213, 97, 27, .05);
        }

        .comment-filter-bar .filter-col {
            margin: 0;
        }

        .comment-filter-status {
            min-width: 0;
        }

        .comment-filter-bar .filter-label {
            color: #9d5b30;
        }

        .comment-filter-bar .filter-input,
        .comment-filter-status .filter-select {
            border-color: #ebcbb7;
            background: #fff;
        }

        .comment-filter-bar .filter-input:focus,
        .comment-filter-status .filter-select:focus {
            border-color: #ff782d;
            box-shadow: 0 0 0 3px rgba(255, 120, 45, .14);
        }

        .comment-filter-status .filter-select {
            width: 100%;
            height: 38px;
            color: #5b3927;
            font-weight: 700;
        }

        .comment-filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .comment-filter-actions .btn-dark-slate,
        .comment-filter-actions .btn-clear-filters {
            height: 38px;
            margin: 0;
        }

        .comment-filter-actions .btn-clear-filters {
            border-color: #ff782d;
            background: #fff;
            color: #ff782d;
            font-weight: 800;
        }

        .comment-filter-actions .btn-clear-filters:hover {
            background: #ff782d;
            color: #fff;
        }

        .comment-table {
            min-width: 1120px;
        }

        .comment-table th {
            padding-top: 14px;
            padding-bottom: 14px;
            background: #fff8f3;
            color: #8f5735;
        }

        .comment-table td {
            padding-top: 15px;
            padding-bottom: 15px;
        }

        .comment-body {
            max-width: 450px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.45;
        }

        .comment-post-link {
            color: var(--text-main);
            font-weight: 700;
            text-decoration: none;
            line-height: 1.4;
        }

        .comment-post-link:hover {
            color: #ff782d;
        }

        .comment-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .comment-status-badge.approved {
            background: #eaf8f0;
            color: #16734a;
        }

        .comment-status-badge.hidden {
            background: #eef1f3;
            color: #5f6b76;
        }

        .comment-status-badge.pending {
            background: #fff4e8;
            color: #b95008;
        }

        .comment-status-badge.deleted {
            background: #fbeeee;
            color: #b42318;
        }

        .comment-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .comment-action-form {
            display: inline-flex;
            margin: 0;
        }

        .comment-action {
            width: 34px;
            height: 34px;
            display: inline-grid;
            place-items: center;
            border-radius: 8px;
            border: 1px solid #dfe5e1;
            background: #fff;
            cursor: pointer;
            transition: all .18s ease;
        }

        .comment-action:hover {
            transform: translateY(-1px);
        }

        .comment-action:active {
            transform: translateY(0);
        }

        .comment-action.approve {
            color: #16734a;
            border-color: #b8ead3;
            background: #f1fbf5;
        }

        .comment-action.hide {
            color: #5f6b76;
            background: #f8fafb;
        }

        .comment-action.delete {
            color: #dc2626;
            border-color: #f4caca;
            background: #fffafa;
        }

        .comment-confirm-modal[hidden] {
            display: none;
        }

        .comment-confirm-modal {
            position: fixed;
            inset: 0;
            z-index: 1100;
            display: grid;
            place-items: center;
            padding: 20px;
            background: rgba(24, 35, 29, .42);
        }

        .comment-confirm-dialog {
            width: min(100%, 410px);
            padding: 24px;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 20px 60px rgba(23, 34, 28, .24);
        }

        .comment-confirm-dialog h3 {
            margin: 0;
            color: var(--text-main);
            font-size: 1.12rem;
        }

        .comment-confirm-dialog p {
            margin: 9px 0 20px;
            color: var(--text-muted);
            line-height: 1.55;
        }

        .comment-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
        }

        .comment-confirm-actions button {
            min-width: 92px;
            height: 38px;
            border-radius: 8px;
            font: inherit;
            font-size: .86rem;
            font-weight: 800;
            cursor: pointer;
        }

        .comment-confirm-cancel {
            border: 1px solid #dfe5e1;
            background: #fff;
            color: #516058;
        }

        .comment-confirm-submit {
            border: 1px solid #ff782d;
            background: #ff782d;
            color: #fff;
        }

        .comment-confirm-submit.is-danger {
            border-color: #dc2626;
            background: #dc2626;
        }

        @media (max-width: 720px) {
            .comment-page-header {
                align-items: flex-start;
                flex-direction: column;
                gap: 6px;
            }

            .comment-filter-bar {
                grid-template-columns: 1fr;
            }

            .comment-filter-actions {
                justify-content: flex-end;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $statusTabs = [
            'all' => 'Tất cả',
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'hidden' => 'Đã ẩn',
            'deleted' => 'Đã xóa',
        ];
        $totalComments = $commentCounts->except('deleted')->sum();
    @endphp
    <div class="comment-page-header">
        <div class="header-title-block">
            <h1>Bình luận bài viết</h1>
            <p>Quản lý và gỡ bỏ bình luận bài viết của khách hàng.</p>
        </div>
        <div class="comment-total">{{ number_format($totalComments ?? 0) }} bình luận</div>
    </div>

    @if(session('success'))
        <div class="alert-panel alert-success-box">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if($selectedBlog)
        <div
            style="background: #fff8f3; border: 1px solid #ffe3d1; padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-filter" style="color: #ff782d;"></i>
                <span style="color: #5b5550; font-size: 0.95rem;">Đang hiển thị bình luận của bài viết:</span>
                <strong style="color: #2d2926; font-size: 1rem;">{{ $selectedBlog->title }}</strong>
            </div>
            <a href="{{ route('admin.blog-comments') }}"
                style="text-decoration: none; padding: 6px 14px; border-radius: 20px; background: #fff; border: 1px solid #ebdcd0; font-size: 0.85rem; font-weight: bold; color: #5b5550; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s;"
                onmouseover="this.style.background='#fcf8f5'" onmouseout="this.style.background='#fff'">
                <i class="fa-solid fa-circle-xmark"></i> Xóa bộ lọc bài viết
            </a>
        </div>
    @endif

    <form class="comment-filter-bar" method="GET">
        @if($blogId)
            <input type="hidden" name="blog_id" value="{{ $blogId }}">
        @endif
        <div class="filter-col orders-filter-search" style="flex: 1; min-width: 300px;">
            <label class="filter-label">Tìm kiếm</label>
            <div class="filter-input-wrapper">
                <i class="fa-solid fa-magnifying-glass filter-input-icon"></i>
                <input class="filter-input" id="comment-search" name="search" value="{{ $search ?? '' }}"
                    placeholder="Người dùng, bài viết, nội dung bình luận..." autocomplete="off">
            </div>
        </div>
        <div class="filter-col comment-filter-status">
            <label class="filter-label" for="comment-status">Trạng thái</label>
            <select class="filter-select" name="status" id="comment-status">
                @foreach($statusTabs as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>
                        {{ $label }} ({{ number_format($value === 'all' ? $totalComments : ($commentCounts[$value] ?? 0)) }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="comment-filter-actions">
            <a href="{{ route('admin.blog-comments', array_filter(['blog_id' => $blogId, 'status' => 'all'])) }}"
                class="btn-clear-filters">Xóa lọc</a>
        </div>
    </form>

    <div class="table-card">
        <div class="table-container">
            <table class="orders-table comment-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Nội dung bình luận</th>
                        <th style="width: 20%;">Người viết</th>
                        <th style="width: 25%;">Bài viết</th>
                        <th style="width: 12%;">Ngày gửi</th>
                        <th style="width: 10%;">Trạng thái</th>
                        <th style="width: 12%; text-align:right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comments as $comment)
                        <tr>
                            <td>
                                <div class="comment-body">{{ $comment->content }}</div>
                            </td>
                            <td>
                                <strong>{{ $comment->user?->name ?: 'Khách hàng' }}</strong>
                                <br>
                                <small style="color:var(--text-muted)">{{ $comment->user?->email }}</small>
                            </td>
                            <td>
                                <a href="{{ config('app.frontend_url') }}/news/{{ $comment->blog?->slug }}" target="_blank"
                                    class="comment-post-link">
                                    {{ $comment->blog?->title }}
                                </a>
                            </td>
                            <td>
                                {{ $comment->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                @if($comment->trashed())
                                    <span class="comment-status-badge deleted">Đã xóa</span>
                                @elseif($comment->status === 'approved')
                                    <span class="comment-status-badge approved">Đã duyệt</span>
                                @elseif($comment->status === 'hidden')
                                    <span class="comment-status-badge hidden">Đã ẩn</span>
                                @else
                                    <span class="comment-status-badge pending">Chờ duyệt</span>
                                @endif
                            </td>
                            <td style="text-align:right; vertical-align: middle;">
                                <div class="comment-actions">
                                    @if($comment->trashed())
                                        <form method="POST" action="{{ route('admin.blog-comments.restore', $comment->id) }}"
                                            class="comment-action-form">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="Khôi phục bình luận" aria-label="Khôi phục bình luận"
                                                class="comment-action approve">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                        <button type="button" title="Xóa vĩnh viễn" aria-label="Xóa vĩnh viễn"
                                            class="comment-action delete" data-comment-confirm
                                            data-action="{{ route('admin.blog-comments.force-destroy', $comment->id) }}"
                                            data-method="DELETE" data-title="Xóa vĩnh viễn bình luận?"
                                            data-message="Bình luận sẽ bị xóa vĩnh viễn và không thể khôi phục." data-danger="true"
                                            data-confirm-label="Có, xóa vĩnh viễn">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    @else
                                        @if($comment->status !== 'approved')
                                            <form method="POST" action="{{ route('admin.blog-comments.status', $comment) }}"
                                                class="comment-action-form">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" title="Duyệt bình luận" aria-label="Duyệt bình luận"
                                                    class="comment-action approve">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($comment->status !== 'hidden')
                                            <button type="button" title="Ẩn bình luận" aria-label="Ẩn bình luận"
                                                class="comment-action hide" data-comment-confirm
                                                data-action="{{ route('admin.blog-comments.status', $comment) }}" data-method="PATCH"
                                                data-status="hidden" data-title="Ẩn bình luận này?"
                                                data-message="Bình luận sẽ không còn hiển thị với khách hàng.">
                                                <i class="fa-solid fa-eye-slash"></i>
                                            </button>
                                        @endif
                                        <button type="button" title="Xóa bình luận" aria-label="Xóa bình luận"
                                            class="comment-action delete" data-comment-confirm
                                            data-action="{{ route('admin.blog-comments.destroy', $comment) }}" data-method="DELETE"
                                            data-title="Chuyển bình luận vào mục đã xóa?"
                                            data-message="Bạn vẫn có thể khôi phục bình luận trong mục Đã xóa.">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted)">
                                Chưa có bình luận nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $comments->links('admin.layouts.pagination') }}

    <div class="comment-confirm-modal" id="comment-confirm-modal" hidden>
        <div class="comment-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="comment-confirm-title">
            <h3 id="comment-confirm-title">Xác nhận thao tác</h3>
            <p id="comment-confirm-message"></p>
            <form method="POST" id="comment-confirm-form">
                @csrf
                <input type="hidden" name="_method" id="comment-confirm-method">
                <input type="hidden" name="status" id="comment-confirm-status">
                <div class="comment-confirm-actions">
                    <button type="button" class="comment-confirm-cancel" id="comment-confirm-cancel">Không</button>
                    <button type="submit" class="comment-confirm-submit" id="comment-confirm-submit">Có, tiếp tục</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.comment-filter-bar');
            const searchInput = document.getElementById('comment-search');
            const statusSelect = document.getElementById('comment-status');
            let searchTimer;

            statusSelect?.addEventListener('change', () => form?.submit());

            searchInput?.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => form?.submit(), 450);
            });

            const modal = document.getElementById('comment-confirm-modal');
            const confirmForm = document.getElementById('comment-confirm-form');
            const methodInput = document.getElementById('comment-confirm-method');
            const statusInput = document.getElementById('comment-confirm-status');
            const title = document.getElementById('comment-confirm-title');
            const message = document.getElementById('comment-confirm-message');
            const submitButton = document.getElementById('comment-confirm-submit');
            const cancelButton = document.getElementById('comment-confirm-cancel');

            const closeModal = () => {
                modal.hidden = true;
                document.body.style.overflow = '';
            };

            document.querySelectorAll('[data-comment-confirm]').forEach((button) => {
                button.addEventListener('click', () => {
                    confirmForm.action = button.dataset.action;
                    methodInput.value = button.dataset.method;
                    statusInput.value = button.dataset.status || '';
                    title.textContent = button.dataset.title;
                    message.textContent = button.dataset.message;
                    submitButton.classList.toggle('is-danger', button.dataset.danger === 'true');
                    submitButton.textContent = button.dataset.confirmLabel || (button.dataset.danger === 'true' ? 'Có, xóa bình luận' : 'Có, tiếp tục');
                    modal.hidden = false;
                    document.body.style.overflow = 'hidden';
                    cancelButton.focus();
                });
            });

            cancelButton?.addEventListener('click', closeModal);
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) closeModal();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) closeModal();
            });
        });
    </script>
@endsection