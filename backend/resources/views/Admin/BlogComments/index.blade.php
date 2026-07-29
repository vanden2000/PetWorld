@extends('admin.layouts.app')

@section('title', 'Bình luận bài viết')

@section('content')
<div class="dashboard-header">
    <div class="header-title-block">
        <h1>Bình luận bài viết</h1>
        <p>Quản lý và ẩn/hiện bình luận bài viết của khách hàng.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert-panel alert-success-box">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

@if($selectedBlog)
    <div style="background: #fff8f3; border: 1px solid #ffe3d1; padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-filter" style="color: var(--primary-orange);"></i>
            <span style="color: #5b5550; font-size: 0.95rem;">Đang hiển thị bình luận của bài viết:</span>
            <strong style="color: #2d2926; font-size: 1rem;">{{ $selectedBlog }}</strong>
        </div>
        <a href="{{ route('admin.blog-comments', array_diff_key($filters, ['blog_id' => null])) }}" style="text-decoration: none; padding: 6px 14px; border-radius: 20px; background: #fff; border: 1px solid #ebdcd0; font-size: 0.85rem; font-weight: bold; color: #5b5550; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s;" onmouseover="this.style.background='#fcf8f5'" onmouseout="this.style.background='#fff'">
            <i class="fa-solid fa-circle-xmark"></i> Xóa bộ lọc bài viết
        </a>
    </div>
@endif

<form class="filters-card orders-filter-card" method="GET">
    <div class="filter-col orders-filter-search">
        <label class="filter-label">Tìm kiếm</label>
        <div class="filter-input-wrapper">
            <i class="fa-solid fa-magnifying-glass filter-input-icon"></i>
            <input class="filter-input" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Người dùng, bài viết, nội dung bình luận...">
        </div>
    </div>
    <div class="filter-col">
        <label class="filter-label">Bài viết</label>
        <select class="filter-select" name="blog_id">
            <option value="">Tất cả bài viết</option>
            @foreach($blogOptions as $id => $title)
                <option value="{{ $id }}" @selected((string) ($filters['blog_id'] ?? '') === (string) $id)>{{ \Illuminate\Support\Str::limit($title, 50) }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-col">
        <label class="filter-label">Trạng thái</label>
        <select class="filter-select" name="status">
            <option value="">Tất cả</option>
            @foreach(['visible' => 'Đang hiển thị', 'hidden' => 'Đã ẩn'] as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-col orders-filter-actions">
        <button class="btn-dark-slate"><i class="fa-solid fa-filter"></i><span>Lọc</span></button>
        <a href="{{ route('admin.blog-comments') }}" class="btn-clear-filters">Xóa lọc</a>
    </div>
</form>

<div class="table-card">
    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th style="width: 32%;">Nội dung bình luận</th>
                    <th style="width: 18%;">Người viết</th>
                    <th style="width: 22%;">Bài viết</th>
                    <th style="width: 11%;">Ngày gửi</th>
                    <th style="width: 9%;">Trạng thái</th>
                    <th style="width: 8%; text-align:right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comments as $comment)
                    <tr>
                        <td>
                            <div style="font-size: 0.95rem; color: {{ $comment->is_hidden ? 'var(--text-muted)' : '#2d2926' }}; white-space: pre-wrap;">{{ $comment->content }}</div>
                        </td>
                        <td>
                            <strong>{{ $comment->user?->name ?: 'Khách hàng' }}</strong>
                            <br>
                            <small style="color:var(--text-muted)">{{ $comment->user?->email }}</small>
                        </td>
                        <td>
                            <a href="{{ config('app.frontend_url') }}/news/{{ $comment->blog?->slug }}" target="_blank" style="color: var(--primary-orange); font-weight: 600; text-decoration: none;">
                                {{ $comment->blog?->title }}
                            </a>
                        </td>
                        <td>
                            {{ $comment->created_at?->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <span class="badge-fulfillment {{ $comment->is_hidden ? 'cancelled' : 'delivered' }}">
                                {{ $comment->is_hidden ? 'Đã ẩn' : 'Đang hiển thị' }}
                            </span>
                        </td>
                        <td style="text-align:right; vertical-align: middle;">
                            <form method="POST" action="{{ route('admin.blog-comments.visibility', $comment) }}" style="display:inline-block">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_hidden" value="{{ $comment->is_hidden ? 0 : 1 }}">
                                @if($comment->is_hidden)
                                    <button type="submit" style="border: 1px solid #ebdcd0; background: #fff; padding: 6px 12px; border-radius: 8px; color: #16a34a; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f0fdf4'; this.style.borderColor='#86efac'" onmouseout="this.style.background='#fff'; this.style.borderColor='#ebdcd0'">
                                        <i class="fa-solid fa-eye" style="margin-right: 4px;"></i> Hiện
                                    </button>
                                @else
                                    <button type="submit" style="border: 1px solid #ebdcd0; background: #fff; padding: 6px 12px; border-radius: 8px; color: #b45309; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#fffbeb'; this.style.borderColor='#fcd34d'" onmouseout="this.style.background='#fff'; this.style.borderColor='#ebdcd0'">
                                        <i class="fa-solid fa-eye-slash" style="margin-right: 4px;"></i> Ẩn
                                    </button>
                                @endif
                            </form>
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
@endsection
