@extends('admin.layouts.app')

@section('title', 'Đánh giá sản phẩm')

@section('content')
<div class="dashboard-header"><div class="header-title-block"><h1>Đánh giá sản phẩm</h1><p>Kiểm duyệt đánh giá từ các đơn hàng đã hoàn thành.</p></div></div>
@if(session('success'))<div class="alert-panel alert-success-box"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>@endif
<form class="filters-card orders-filter-card" method="GET">
    <div class="filter-col orders-filter-search"><label class="filter-label">Tìm kiếm</label><div class="filter-input-wrapper"><i class="fa-solid fa-magnifying-glass filter-input-icon"></i><input class="filter-input" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Khách hàng, sản phẩm, nội dung..."></div></div>
    <div class="filter-col"><label class="filter-label">Trạng thái</label><select class="filter-select" name="status"><option value="">Tất cả</option>@foreach(['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'hidden' => 'Đã ẩn'] as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="filter-col"><label class="filter-label">Số sao</label><select class="filter-select" name="rating"><option value="">Tất cả</option>@for($star = 5; $star >= 1; $star--)<option value="{{ $star }}" @selected((string) ($filters['rating'] ?? '') === (string) $star)>{{ $star }} sao</option>@endfor</select></div>
    <div class="filter-col orders-filter-actions"><button class="btn-dark-slate"><i class="fa-solid fa-filter"></i><span>Lọc</span></button><a href="{{ route('admin.reviews') }}" class="btn-clear-filters">Xóa lọc</a></div>
</form>
<div class="table-card"><div class="table-container"><table class="orders-table"><thead><tr><th>Đánh giá</th><th>Khách hàng</th><th>Sản phẩm</th><th>Ngày gửi</th><th>Trạng thái</th><th style="text-align:right">Thao tác</th></tr></thead><tbody>
@forelse($reviews as $review)
<tr><td><div style="color:#f59e0b;letter-spacing:1px">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div><div style="max-width:310px;margin-top:5px;color:var(--text-muted)">{{ $review->comment ?: 'Không có nội dung bình luận.' }}</div></td><td><strong>{{ $review->user?->name ?: 'Khách hàng' }}</strong><br><small style="color:var(--text-muted)">{{ $review->user?->email }}</small></td><td><strong>{{ $review->orderItem?->product_name }}</strong><br><small style="color:var(--text-muted)">{{ $review->orderItem?->productVariant?->sku }}</small></td><td>{{ $review->created_at?->format('d/m/Y H:i') }}</td><td><span class="quick-status-trigger badge-fulfillment {{ $review->status === 'approved' ? 'delivered' : ($review->status === 'hidden' ? 'cancelled' : 'pending') }}">{{ ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'hidden' => 'Đã ẩn'][$review->status] }}</span></td><td style="text-align:right"><form method="POST" action="{{ route('admin.reviews.status.update', $review) }}" style="display:inline">@csrf @method('PATCH')<select name="status" onchange="this.form.submit()" aria-label="Cập nhật trạng thái"><option value="pending" @selected($review->status === 'pending')>Chờ duyệt</option><option value="approved" @selected($review->status === 'approved')>Duyệt</option><option value="hidden" @selected($review->status === 'hidden')>Ẩn</option></select></form></td></tr>
@empty<tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted)">Chưa có đánh giá phù hợp.</td></tr>@endforelse
</tbody></table></div></div>
@if($reviews->hasPages())<div class="pagination-container">{{ $reviews->links() }}</div>@endif
@endsection
