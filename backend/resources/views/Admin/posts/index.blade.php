@extends('admin.layouts.app')

@section('title', 'Danh sách bài viết')

@section('content')
<div class="dashboard-header">
    <div class="header-title-block">
        <h1>Danh sách bài viết</h1>
        <p>Quản lý tin tức, bài viết của cửa hàng PetWorld.</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.posts.create') }}" class="categories-add-btn" style="text-decoration: none; padding: 10px 20px; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; font-weight: bold; background: var(--primary); color: #fff; border: none; transition: var(--transition);" onmouseover="this.style.background='var(--primary-hover)'" onmouseout="this.style.background='var(--primary)'">
            <i class="fa-solid fa-plus" style="font-size: 0.95rem;"></i>
            <span>Thêm bài viết mới</span>
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert-panel alert-success-box">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

<form class="filters-card orders-filter-card" method="GET">
    <div class="filter-col orders-filter-search" style="flex: 1; min-width: 300px;">
        <label class="filter-label">Tìm kiếm bài viết</label>
        <div class="filter-input-wrapper">
            <i class="fa-solid fa-magnifying-glass filter-input-icon"></i>
            <input class="filter-input" name="search" value="{{ $search ?? '' }}" placeholder="Tiêu đề, mô tả ngắn, danh mục...">
        </div>
    </div>
    <div class="filter-col orders-filter-actions">
        <button class="btn-dark-slate"><i class="fa-solid fa-filter"></i><span>Lọc</span></button>
        <a href="{{ route('admin.posts') }}" class="btn-clear-filters">Xóa lọc</a>
    </div>
</form>

<div class="table-card">
    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th style="width: 80px;">Hình ảnh</th>
                    <th style="width: 32%;">Tiêu đề bài viết</th>
                    <th style="width: 14%;">Danh mục</th>
                    <th style="width: 10%;">Tác giả</th>
                    <th style="width: 8%; text-align: center;">Lượt xem</th>
                    <th style="width: 8%; text-align: center;">Bình luận</th>
                    <th style="width: 10%;">Ngày tạo</th>
                    <th style="width: 10%;">Trạng thái</th>
                    <th style="width: 10%; text-align: right;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>
                            @php
                                $postImageUrl = asset('images/default-blog.jpg');
                                if ($post->image) {
                                    if (filter_var($post->image, FILTER_VALIDATE_URL)) {
                                        $postImageUrl = $post->image;
                                    } elseif (str_starts_with($post->image, 'uploads/') || str_starts_with($post->image, 'image/')) {
                                        $postImageUrl = asset($post->image);
                                    } elseif (str_starts_with($post->image, 'storage/')) {
                                        $postImageUrl = asset($post->image);
                                    } else {
                                        $postImageUrl = asset('storage/' . $post->image);
                                    }
                                }
                            @endphp
                            <img src="{{ $postImageUrl }}" 
                                 alt="{{ $post->title }}" 
                                 style="width: 64px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid #ebdcd0;">
                        </td>
                        <td>
                            <strong style="color: #2d2926; font-size: 0.95rem;">{{ $post->title }}</strong>
                            <br>
                            <span style="font-size: 0.82rem; color: var(--text-muted); display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; max-width: 350px;">
                                {{ $post->description }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-fulfillment delivered" style="background: rgba(255, 120, 45, 0.08); color: #ff782d; border: 1px solid rgba(255, 120, 45, 0.15); font-size: 0.8rem; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                                {{ $post->category?->name ?? 'Chưa phân loại' }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 0.9rem; color: #5b5550;">{{ $post->author?->name ?? 'Admin' }}</span>
                        </td>
                        <td style="text-align: center; font-weight: 600; color: #2d2926; font-size: 0.9rem;">
                            👁 {{ number_format($post->view_count) }}
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.blog-comments', ['blog_id' => $post->id]) }}" 
                               style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none; font-weight: bold; background: #fff8f3; border: 1px solid #ffe3d1; padding: 6px 12px; border-radius: 20px; color: var(--primary-orange); font-size: 0.85rem; transition: all 0.2s;"
                               onmouseover="this.style.background='#ff782d'; this.style.color='#fff'"
                               onmouseout="this.style.background='#fff8f3'; this.style.color='var(--primary-orange)'"
                               title="Xem các bình luận của bài viết này">
                                <i class="fa-solid fa-comment-dots"></i> {{ $post->comments_count }}
                            </a>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; color: var(--text-muted);">{{ $post->created_at?->format('d/m/Y') }}</span>
                        </td>
                        <td>
                            @if($post->status === 'active')
                                <span class="badge-fulfillment delivered" style="background: rgba(16, 185, 129, 0.08); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.15); font-size: 0.78rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; display: inline-block; white-space: nowrap;">
                                    Đã xuất bản
                                </span>
                            @else
                                <span class="badge-fulfillment pending" style="background: rgba(107, 114, 128, 0.08); color: #6b7280; border: 1px solid rgba(107, 114, 128, 0.15); font-size: 0.78rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; display: inline-block; white-space: nowrap;">
                                    Bản nháp
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right; vertical-align: middle; white-space: nowrap;">
                            <a href="{{ route('admin.posts.edit', $post) }}" style="text-decoration: none; border: 1px solid #ebdcd0; background: #fff; padding: 6px 12px; border-radius: 8px; color: #ff782d; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px; font-weight: 600; font-size: 0.85rem; margin-right: 6px;" onmouseover="this.style.background='#fff8f3'; this.style.borderColor='var(--primary)'" onmouseout="this.style.background='#fff'; this.style.borderColor='#ebdcd0'">
                                <i class="fa-solid fa-pen-to-square"></i> Sửa
                            </a>
                            <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này? Tất cả bình luận liên quan cũng sẽ bị xóa.')" style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="border: 1px solid #ebdcd0; background: #fff; padding: 6px 12px; border-radius: 8px; color: #dc2626; cursor: pointer; transition: all 0.2s ease; font-weight: 600; font-size: 0.85rem;" onmouseover="this.style.background='#fef2f2'; this.style.borderColor='#fca5a5'" onmouseout="this.style.background='#fff'; this.style.borderColor='#ebdcd0'">
                                    <i class="fa-solid fa-trash-can" style="margin-right: 4px;"></i> Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:32px;color:var(--text-muted)">
                            Chưa có bài viết nào phù hợp.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $posts->links('admin.layouts.pagination') }}
@endsection