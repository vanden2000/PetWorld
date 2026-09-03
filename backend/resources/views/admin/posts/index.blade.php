@extends('admin.layouts.app')

@section('title', 'Danh sách bài viết')

@section('styles') @include('admin.posts._index_styles') @endsection

@section('content')
@php
    $siteBase = rtrim(config('app.frontend_url'), '/') . '/news/';
@endphp

<div class="pl-header">
    <div class="header-title-block">
        <h1>Danh sách bài viết</h1>
        <p>Quản lý tin tức, cẩm nang chăm sóc thú cưng của PetWorld.</p>
    </div>
    <div class="pl-header-actions">
        <a href="{{ route('admin.blog-comments') }}" class="pl-btn">
            <i class="fa-solid fa-comments"></i> Quản lý bình luận
        </a>
        <a href="{{ route('admin.posts.create') }}" class="pl-btn pl-btn-primary">
            <i class="fa-solid fa-plus"></i> Thêm bài viết mới
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert-panel alert-success-box">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

{{-- Số liệu tổng quan --}}
<div class="pl-stats">
    <div class="pl-stat">
        <div class="pl-stat-info">
            <div class="pl-stat-value">{{ number_format($totalCount) }}</div>
            <div class="pl-stat-label">Tổng bài viết</div>
        </div>
        <div class="pl-stat-icon is-total"><i class="fa-solid fa-newspaper"></i></div>
    </div>
    <div class="pl-stat">
        <div class="pl-stat-info">
            <div class="pl-stat-value">{{ number_format($publishedCount) }}</div>
            <div class="pl-stat-label">Đang xuất bản</div>
        </div>
        <div class="pl-stat-icon is-live" style="color: #16734a;"><i class="fa-solid fa-circle-check"></i></div>
    </div>
    <div class="pl-stat">
        <div class="pl-stat-info">
            <div class="pl-stat-value">{{ number_format($draftCount) }}</div>
            <div class="pl-stat-label">Bản nháp</div>
        </div>
        <div class="pl-stat-icon is-draft" style="color: #6b7280;"><i class="fa-regular fa-file-lines"></i></div>
    </div>
    <div class="pl-stat">
        <div class="pl-stat-info">
            <div class="pl-stat-value">{{ number_format($totalViews) }}</div>
            <div class="pl-stat-label">Tổng lượt xem</div>
        </div>
        <div class="pl-stat-icon is-views" style="color: #0284c7;"><i class="fa-regular fa-eye"></i></div>
    </div>
</div>

{{-- Bộ lọc --}}
<form method="GET" action="{{ route('admin.posts') }}" class="pl-filters">
    <!-- Search -->
    <div class="pl-filter">
        <label for="search">Tìm kiếm</label>
        <div class="pl-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="search" name="search" class="pl-input"
                   value="{{ $search }}" placeholder="Tiêu đề hoặc mô tả...">
        </div>
    </div>

    <!-- Category -->
    <div class="pl-filter">
        <label>Danh mục</label>
        <div class="custom-admin-select-container">
            <div class="custom-admin-select-trigger">
                <span>Tất cả</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <input type="hidden" name="category_id" id="category_id" value="{{ $categoryId }}">
            <div class="custom-admin-select-options">
                <div class="custom-admin-select-option {{ $categoryId === 'all' ? 'selected' : '' }}" data-value="all">Tất cả</div>
                @foreach($categories as $category)
                    <div class="custom-admin-select-option {{ (string)$categoryId === (string)$category->id ? 'selected' : '' }}" data-value="{{ $category->id }}">{{ $category->name }}</div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Author -->
    <div class="pl-filter">
        <label>Tác giả</label>
        <div class="custom-admin-select-container">
            <div class="custom-admin-select-trigger">
                <span>Tất cả</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <input type="hidden" name="author_id" id="author_id" value="{{ $authorId }}">
            <div class="custom-admin-select-options">
                <div class="custom-admin-select-option {{ $authorId === 'all' ? 'selected' : '' }}" data-value="all">Tất cả</div>
                @foreach($authors as $author)
                    <div class="custom-admin-select-option {{ (string)$authorId === (string)$author->id ? 'selected' : '' }}" data-value="{{ $author->id }}">{{ $author->name }}</div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Status -->
    <div class="pl-filter">
        <label>Trạng thái</label>
        <div class="custom-admin-select-container">
            <div class="custom-admin-select-trigger">
                <span>Tất cả</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <input type="hidden" name="status" id="status" value="{{ $status }}">
            <div class="custom-admin-select-options">
                <div class="custom-admin-select-option {{ $status === 'all' ? 'selected' : '' }}" data-value="all">Tất cả</div>
                <div class="custom-admin-select-option {{ $status === 'active' ? 'selected' : '' }}" data-value="active">Đang xuất bản</div>
                <div class="custom-admin-select-option {{ $status === 'inactive' ? 'selected' : '' }}" data-value="inactive">Bản nháp</div>
            </div>
        </div>
    </div>

    <!-- Sort -->
    <div class="pl-filter">
        <label>Sắp xếp</label>
        <div class="custom-admin-select-container">
            <div class="custom-admin-select-trigger">
                <span>Mới nhất</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <input type="hidden" name="sort" id="sort" value="{{ $sort }}">
            <div class="custom-admin-select-options">
                <div class="custom-admin-select-option {{ $sort === 'latest' ? 'selected' : '' }}" data-value="latest">Mới nhất</div>
                <div class="custom-admin-select-option {{ $sort === 'oldest' ? 'selected' : '' }}" data-value="oldest">Cũ nhất</div>
                <div class="custom-admin-select-option {{ $sort === 'most_viewed' ? 'selected' : '' }}" data-value="most_viewed">Xem nhiều nhất</div>
                <div class="custom-admin-select-option {{ $sort === 'most_commented' ? 'selected' : '' }}" data-value="most_commented">Bình luận nhiều nhất</div>
            </div>
        </div>
    </div>

    <!-- Reset -->
    <div class="pl-filter">
        <a href="{{ route('admin.posts') }}" class="btn-reset-filters" style="text-decoration: none;">
            <i class="fa-solid fa-rotate-left"></i>
            <span>Xóa lọc</span>
        </a>
    </div>
</form>

{{-- Danh sách bảng --}}
<div class="pl-table-card">
    <div class="pl-table-scroll">
        <table class="pl-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Bài viết</th>
                    <th style="width: 15%;">Danh mục</th>
                    <th style="width: 12%;">Tác giả</th>
                    <th style="width: 10%; text-align: center;">Lượt xem</th>
                    <th style="width: 10%; text-align: center;">Bình luận</th>
                    <th style="width: 12%;">Ngày tạo</th>
                    <th style="width: 11%;">Trạng thái</th>
                    <th style="width: 10%; text-align: right;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>
                            <div class="pl-post-cell">
                                @if($post->image)
                                    @php
                                        $postImageUrl = asset('images/default-blog.jpg');
                                        if (filter_var($post->image, FILTER_VALIDATE_URL)) {
                                            $postImageUrl = $post->image;
                                        } elseif (str_starts_with($post->image, 'uploads/') || str_starts_with($post->image, 'blogs/')) {
                                            $postImageUrl = asset('storage/' . $post->image);
                                        } elseif (str_starts_with($post->image, 'storage/')) {
                                            $postImageUrl = asset($post->image);
                                        } else {
                                            $postImageUrl = asset('storage/' . $post->image);
                                        }
                                    @endphp
                                    <img src="{{ $postImageUrl }}" alt="{{ $post->title }}" class="pl-thumb">
                                @else
                                    <div class="pl-thumb-empty"><i class="fa-regular fa-newspaper"></i></div>
                                @endif
                                <div class="pl-post-info">
                                    <a href="{{ $siteBase . $post->slug }}" target="_blank" class="pl-post-title">
                                        {{ $post->title }}
                                    </a>
                                    <div class="pl-post-slug">
                                        <i class="fa-solid fa-link"></i> /news/{{ $post->slug }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($post->category)
                                <span class="pl-badge pl-badge-category">{{ $post->category->name }}</span>
                            @else
                                <span class="pl-badge pl-badge-none">Chưa phân loại</span>
                            @endif
                        </td>
                        <td>
                            <span class="pl-metric" style="color: #4b5563; font-weight: 500; font-size: 0.88rem;">
                                <i class="fa-regular fa-circle-user" style="color: #9ca3af; font-size: 1rem;"></i>
                                {{ $post->author?->name ?? 'Admin' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span class="pl-metric" style="justify-content: center; font-size: 0.88rem; font-weight: 600;">
                                <i class="fa-regular fa-eye" style="color: #9ca3af;"></i>
                                {{ number_format($post->view_count) }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.blog-comments', ['blog_id' => $post->id]) }}" class="pl-comment-link" title="Xem bình luận">
                                <i class="fa-regular fa-comment-dots"></i>
                                <span>{{ $post->comments_count }}</span>
                            </a>
                        </td>
                        <td>
                            <span class="pl-date">
                                {{ $post->created_at?->format('d/m/Y') }}
                                <small style="color: var(--text-muted); display: inline-block; margin-left: 6px;">{{ $post->created_at?->format('H:i') }}</small>
                            </span>
                        </td>
                        <td>
                            @if($post->status === 'active')
                                <span class="pl-badge pl-badge-live"><i class="fa-solid fa-circle" style="font-size: 0.45rem; color: #10b981; margin-right: 4px;"></i> Đang xuất bản</span>
                            @else
                                <span class="pl-badge pl-badge-draft"><i class="fa-solid fa-circle" style="font-size: 0.45rem; color: #6b7280; margin-right: 4px;"></i> Bản nháp</span>
                            @endif
                        </td>
                        <td style="text-align: right; vertical-align: middle; white-space: nowrap;">
                            <div class="pl-row-actions">
                                <form method="POST" action="{{ route('admin.posts.status', $post) }}" class="pl-action-form admin-confirm-form"
                                    data-confirm-message="{{ $post->status === 'active' ? 'Bạn có chắc muốn chuyển bài viết về bản nháp?' : 'Bạn có chắc muốn xuất bản bài viết?' }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $post->status === 'active' ? 'inactive' : 'active' }}">
                                    <button type="submit" class="pl-action"
                                            title="{{ $post->status === 'active' ? 'Chuyển về bản nháp' : 'Xuất bản bài viết' }}">
                                        <i class="fa-solid {{ $post->status === 'active' ? 'fa-eye-slash' : 'fa-upload' }}"></i>
                                    </button>
                                </form>

                                <a href="{{ route('admin.posts.edit', $post) }}" class="pl-action" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

                                <button type="button" class="pl-action is-danger" title="Xóa bài viết"
                                        data-delete-post
                                        data-title="{{ $post->title }}"
                                        data-comments="{{ $post->comments_count }}"
                                        data-action="{{ route('admin.posts.destroy', $post) }}">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="pl-empty">
                                <div class="pl-empty-icon"><i class="fa-regular fa-newspaper"></i></div>
                                @if($search || $categoryId !== 'all' || $status !== 'all')
                                    <strong>Không tìm thấy bài viết phù hợp</strong>
                                    <p>Thử đổi từ khóa hoặc bỏ bớt điều kiện lọc.</p>
                                    <a href="{{ route('admin.posts') }}" class="pl-btn">Xóa bộ lọc</a>
                                @else
                                    <strong>Chưa có bài viết nào</strong>
                                    <p>Bắt đầu bằng bài cẩm nang chăm sóc thú cưng đầu tiên của bạn.</p>
                                    <a href="{{ route('admin.posts.create') }}" class="pl-btn pl-btn-primary">
                                        <i class="fa-solid fa-plus"></i> Viết bài đầu tiên
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- Table Footer status bar -->
    <div class="species-table-footer" style="border-top: 1px solid var(--border-color); padding: 14px 20px; background: #fafbfc; display: flex; justify-content: flex-end; align-items: center; border-radius: 0 0 16px 16px;">
        <div style="font-size: 0.82rem; color: var(--text-muted); font-weight: 500;">
            Hiển thị <strong style="color: var(--text-main); font-weight: 700;">{{ $posts->firstItem() ?? 0 }} - {{ $posts->lastItem() ?? 0 }}</strong> trên <strong style="color: var(--text-main); font-weight: 700;">{{ $posts->total() }}</strong> bài viết
        </div>
    </div>
</div>

{{ $posts->links('admin.layouts.pagination') }}

{{-- Xác nhận xóa --}}
<div class="pl-modal" id="pl-delete-modal" role="dialog" aria-modal="true" aria-labelledby="pl-delete-heading">
    <div class="pl-modal-box">
        <div class="pl-modal-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3 id="pl-delete-heading">Xóa bài viết này?</h3>
        <p id="pl-delete-note">Thao tác này không thể hoàn tác.</p>
        <div class="pl-modal-target" id="pl-delete-title"></div>
        <form method="POST" id="pl-delete-form">
            @csrf
            @method('DELETE')
            <div class="pl-modal-actions">
                <button type="button" class="pl-btn" id="pl-delete-cancel">Hủy</button>
                <button type="submit" class="pl-btn pl-modal-danger">
                    <i class="fa-solid fa-trash-can"></i> Xóa bài viết
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('pl-delete-modal');
    const form = document.getElementById('pl-delete-form');
    const titleBox = document.getElementById('pl-delete-title');
    const note = document.getElementById('pl-delete-note');

    const close = () => {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-delete-post]').forEach((button) => {
        button.addEventListener('click', () => {
            const comments = Number(button.dataset.comments || 0);
            form.action = button.dataset.action;
            titleBox.textContent = button.dataset.title;
            note.textContent = comments > 0
                ? `Bài viết đang có ${comments} bình luận, tất cả sẽ bị xóa cùng. Thao tác này không thể hoàn tác.`
                : 'Thao tác này không thể hoàn tác.';
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        });
    });

    document.getElementById('pl-delete-cancel').addEventListener('click', close);
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) close();
    });

    // Custom Select Dropdowns logic
    const dropdowns = document.querySelectorAll('.custom-admin-select-container');

    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.custom-admin-select-trigger');
        const triggerText = trigger.querySelector('span');
        const hiddenInput = dropdown.querySelector('input[type="hidden"]');
        const options = dropdown.querySelectorAll('.custom-admin-select-option');

        // Set initial state based on hidden input value
        const initialValue = hiddenInput.value;
        let matchedOption = Array.from(options).find(opt => opt.getAttribute('data-value') === initialValue);
        if (matchedOption) {
            triggerText.textContent = matchedOption.textContent;
            options.forEach(opt => opt.classList.remove('selected'));
            matchedOption.classList.add('selected');
        }

        // Toggle Open/Close
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdowns.forEach(other => {
                if (other !== dropdown) other.classList.remove('open');
            });
            dropdown.classList.toggle('open');
        });

        // Handle option selection
        options.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                const val = option.getAttribute('data-value');
                const text = option.textContent;

                hiddenInput.value = val;
                triggerText.textContent = text;

                options.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                dropdown.classList.remove('open');

                // Submit form automatically immediately on selection
                const form = dropdown.closest('form');
                if (form) {
                    form.submit();
                }
            });
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        dropdowns.forEach(dropdown => dropdown.classList.remove('open'));
    });
});
</script>
@endsection
