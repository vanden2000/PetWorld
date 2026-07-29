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

{{-- Số liệu tổng quan: đếm trực tiếp từ bảng blogs --}}
<div class="pl-stats">
    <div class="pl-stat">
        <div class="pl-stat-icon is-total"><i class="fa-solid fa-newspaper"></i></div>
        <div>
            <div class="pl-stat-value">{{ number_format($totalCount) }}</div>
            <div class="pl-stat-label">Tổng bài viết</div>
        </div>
    </div>
    <div class="pl-stat">
        <div class="pl-stat-icon is-live"><i class="fa-solid fa-circle-check"></i></div>
        <div>
            <div class="pl-stat-value">{{ number_format($publishedCount) }}</div>
            <div class="pl-stat-label">Đang xuất bản</div>
        </div>
    </div>
    <div class="pl-stat">
        <div class="pl-stat-icon is-draft"><i class="fa-regular fa-file-lines"></i></div>
        <div>
            <div class="pl-stat-value">{{ number_format($draftCount) }}</div>
            <div class="pl-stat-label">Bản nháp</div>
        </div>
    </div>
    <div class="pl-stat">
        <div class="pl-stat-icon is-views"><i class="fa-regular fa-eye"></i></div>
        <div>
            <div class="pl-stat-value">{{ number_format($totalViews) }}</div>
            <div class="pl-stat-label">Tổng lượt xem</div>
        </div>
    </div>
</div>

{{-- Bộ lọc: search + blog_category_id + status + sắp xếp --}}
<form method="GET" action="{{ route('admin.posts') }}" class="pl-filters">
    <div class="pl-filter">
        <label for="search">Tìm kiếm</label>
        <div class="pl-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="search" name="search" class="pl-input"
                   value="{{ $search }}" placeholder="Tiêu đề, mô tả, đường dẫn, danh mục...">
        </div>
    </div>

    <div class="pl-filter">
        <label for="category_id">Danh mục</label>
        <select id="category_id" name="category_id" class="pl-select">
            <option value="all">Tất cả danh mục</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="pl-filter">
        <label for="author_id">Tác giả</label>
        <select id="author_id" name="author_id" class="pl-select">
            <option value="all">Tất cả tác giả</option>
            @foreach($authors as $author)
                <option value="{{ $author->id }}" @selected((string) $authorId === (string) $author->id)>
                    {{ $author->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="pl-filter">
        <label for="status">Trạng thái</label>
        <select id="status" name="status" class="pl-select">
            <option value="all" @selected($status === 'all')>Tất cả trạng thái</option>
            <option value="active" @selected($status === 'active')>Đang xuất bản</option>
            <option value="inactive" @selected($status === 'inactive')>Bản nháp</option>
        </select>
    </div>

    <div class="pl-filter">
        <label for="sort">Sắp xếp</label>
        <select id="sort" name="sort" class="pl-select">
            <option value="latest" @selected($sort === 'latest')>Mới nhất</option>
            <option value="oldest" @selected($sort === 'oldest')>Cũ nhất</option>
            <option value="most_viewed" @selected($sort === 'most_viewed')>Xem nhiều nhất</option>
            <option value="most_commented" @selected($sort === 'most_commented')>Nhiều bình luận nhất</option>
        </select>
    </div>

    <div class="pl-filter pl-filter-actions">
        <button type="submit" class="pl-btn pl-btn-primary">
            <i class="fa-solid fa-sliders"></i> Lọc
        </button>
        <a href="{{ route('admin.posts') }}" class="pl-btn">Xóa lọc</a>
    </div>
</form>

<div class="pl-table-card">
    <div class="pl-table-scroll">
        <table class="pl-table">
            <thead>
                <tr>
                    <th>Bài viết</th>
                    <th>Danh mục</th>
                    <th>Tác giả</th>
                    <th style="text-align: center;">Lượt xem</th>
                    <th style="text-align: center;">Bình luận</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th style="text-align: right;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    @php
                        $postImageUrl = null;
                        if ($post->image) {
                            if (filter_var($post->image, FILTER_VALIDATE_URL)) {
                                $postImageUrl = $post->image;
                            } elseif (str_starts_with($post->image, 'uploads/') || str_starts_with($post->image, 'image/') || str_starts_with($post->image, 'storage/')) {
                                $postImageUrl = asset($post->image);
                            } else {
                                $postImageUrl = asset('storage/' . $post->image);
                            }
                        }
                    @endphp
                    <tr>
                        <td>
                            <div class="pl-post-cell">
                                @if($postImageUrl)
                                    <img class="pl-thumb" src="{{ $postImageUrl }}" alt="{{ $post->title }}" loading="lazy">
                                @else
                                    <span class="pl-thumb-empty" title="Chưa có ảnh bìa">
                                        <i class="fa-regular fa-image"></i>
                                    </span>
                                @endif
                                <div style="min-width: 0;">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="pl-post-title">{{ $post->title }}</a>
                                    <span class="pl-post-slug" title="{{ $siteBase }}{{ $post->slug }}">
                                        <i class="fa-solid fa-link"></i> /news/{{ $post->slug }}
                                    </span>
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
                        <td>{{ $post->author?->name ?? 'Admin' }}</td>
                        <td style="text-align: center;">
                            <span class="pl-metric"><i class="fa-regular fa-eye"></i> {{ number_format($post->view_count) }}</span>
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.blog-comments', ['blog_id' => $post->id]) }}"
                               class="pl-comment-link" title="Xem bình luận của bài viết này">
                                <i class="fa-solid fa-comment-dots"></i> {{ number_format($post->comments_count) }}
                            </a>
                        </td>
                        <td>
                            <span class="pl-date">
                                {{ $post->created_at?->format('d/m/Y') }}
                                <small>{{ $post->created_at?->format('H:i') }}</small>
                            </span>
                        </td>
                        <td>
                            @if($post->status === 'active')
                                <span class="pl-badge pl-badge-live"><i class="fa-solid fa-circle-check"></i> Đã xuất bản</span>
                            @else
                                <span class="pl-badge pl-badge-draft"><i class="fa-regular fa-file-lines"></i> Bản nháp</span>
                            @endif
                        </td>
                        <td>
                            <div class="pl-row-actions">
                                @if($post->status === 'active')
                                    <a href="{{ $siteBase }}{{ $post->slug }}" target="_blank" rel="noopener"
                                       class="pl-action" title="Xem trên website">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                @endif

                                {{-- Đổi nhanh cột status của bảng blogs --}}
                                <form method="POST" action="{{ route('admin.posts.status', $post) }}" class="pl-action-form">
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
(function () {
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

    // Đổi bộ lọc là áp dụng ngay, không cần bấm nút Lọc
    ['category_id', 'author_id', 'status', 'sort'].forEach((id) => {
        document.getElementById(id)?.addEventListener('change', (e) => e.target.form.submit());
    });
})();
</script>
@endsection
