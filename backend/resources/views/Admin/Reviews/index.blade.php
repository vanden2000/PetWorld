@extends('admin.layouts.app')

@section('title', 'Đánh giá sản phẩm')

@section('styles')
    <style>
        .review-row-actions {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
        }

        .review-action {
            width: 34px;
            height: 34px;
            display: inline-grid;
            place-items: center;
            border: 1px solid #dfe5e1;
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
            transition: all .18s ease;
        }

        .review-action:hover {
            transform: translateY(-1px);
        }

        .review-action.approve {
            border-color: #b8ead3;
            background: #f1fbf5;
            color: #16734a;
        }

        .review-action.hide {
            background: #f8fafb;
            color: #5f6b76;
        }

        .review-stars {
            color: #f59e0b;
            letter-spacing: 1px;
            font-size: .92rem;
        }

        .review-comment {
            display: -webkit-box;
            max-width: 330px;
            margin-top: 6px;
            color: var(--text-muted);
            line-height: 1.45;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }

        .review-verified {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 7px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #fff1e8;
            color: #b95008;
            font-size: .72rem;
            font-weight: 800;
        }

        .review-product {
            color: var(--text-main);
            font-weight: 700;
            line-height: 1.4;
        }

        .review-status {
            display: inline-flex;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .review-status.approved {
            background: #eaf8f0;
            color: #16734a;
        }

        .review-status.hidden {
            background: #eef1f3;
            color: #5f6b76;
        }

        .review-status.pending {
            background: #fff4e8;
            color: #b95008;
        }
    </style>
@endsection

@section('content')
    <div class="dashboard-header">
        <div class="header-title-block">
            <h1>Đánh giá sản phẩm</h1>
            <p>Kiểm duyệt đánh giá từ các đơn hàng đã hoàn thành.</p>
        </div>
    </div>
    @if(session('success'))
    <div class="alert-panel alert-success-box"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>@endif
    <form class="filters-card orders-filter-card" method="GET" id="review-filter-form">
        <div class="filter-col orders-filter-search"><label class="filter-label">Tìm kiếm</label>
            <div class="filter-input-wrapper"><i class="fa-solid fa-magnifying-glass filter-input-icon"></i><input
                    class="filter-input" id="review-search" name="search" value="{{ $filters['search'] ?? '' }}"
                    placeholder="Khách hàng, sản phẩm, nội dung..." autocomplete="off"></div>
        </div>
        <div class="filter-col"><label class="filter-label">Trạng thái</label><select class="filter-select"
                id="review-status" name="status">
                <option value="">Tất cả</option>
                @foreach(['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'hidden' => 'Đã ẩn'] as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach
            </select></div>
        <div class="filter-col"><label class="filter-label">Số sao</label><select class="filter-select" id="review-rating"
                name="rating">
                <option value="">Tất cả</option>@for($star = 5; $star >= 1; $star--)
                    <option value="{{ $star }}" @selected((string) ($filters['rating'] ?? '') === (string) $star)>{{ $star }} sao
                </option>@endfor
            </select></div>
        <div class="filter-col orders-filter-actions"><a href="{{ route('admin.reviews') }}" class="btn-clear-filters">Xóa
                lọc</a></div>
    </form>
    <div class="table-card">
        <div class="table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Đánh giá</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th>Ngày gửi</th>
                        <th>Trạng thái</th>
                        <th style="text-align:right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td>
                                <div class="review-stars" aria-label="{{ $review->rating }} trên 5 sao">
                                    {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                                <div class="review-comment" title="{{ $review->comment }}">
                                    {{ $review->comment ?: 'Không có nội dung bình luận.' }}</div>
                            </td>
                            <td>
                                <strong>{{ $review->user?->name ?: 'Khách hàng' }}</strong><br>
                                <small style="color:var(--text-muted)">{{ $review->user?->email }}</small>
                            </td>
                            <td>
                                <div class="review-product">{{ $review->orderItem?->product_name }}</div><small
                                    style="color:var(--text-muted)">{{ $review->orderItem?->productVariant?->sku }}</small>
                            </td>
                            <td>{{ $review->created_at?->format('d/m/Y H:i') }}</td>
                            <td><span
                                    class="review-status {{ $review->status }}">{{ ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'hidden' => 'Đã ẩn'][$review->status] }}</span>
                            </td>
                            <td style="text-align:right">
                                <div class="review-row-actions">@if($review->status !== 'approved')
                                    <form method="POST" class="admin-confirm-form" data-confirm-message="{{ $review->status === 'hidden' ? 'Bạn có chắc muốn hiển thị lại đánh giá này?' : 'Bạn có chắc muốn hiển thị đánh giá này?' }}" action="{{ route('admin.reviews.status.update', $review) }}">@csrf
                                        @method('PATCH')<input type="hidden" name="status" value="approved"><button
                                            type="submit" class="review-action approve"
                                            title="{{ $review->status === 'hidden' ? 'Duyệt lại đánh giá' : 'Duyệt đánh giá' }}"
                                            aria-label="{{ $review->status === 'hidden' ? 'Duyệt lại đánh giá' : 'Duyệt đánh giá' }}"><i
                                                class="fa-solid {{ $review->status === 'hidden' ? 'fa-rotate-left' : 'fa-check' }}"></i></button>
                                </form>@endif @if($review->status !== 'hidden')
                                    <form method="POST" class="admin-confirm-form" data-confirm-message="Bạn có chắc muốn ẩn đánh giá này?" action="{{ route('admin.reviews.status.update', $review) }}">@csrf
                                        @method('PATCH')<input type="hidden" name="status" value="hidden"><button type="submit"
                                            class="review-action hide" title="Ẩn đánh giá" aria-label="Ẩn đánh giá"><i
                                                class="fa-solid fa-eye-slash"></i></button>
                                    </form>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty<tr>
                        <td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted)">Chưa có đánh giá phù
                            hợp.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
        {{ $reviews->links('admin.layouts.pagination') }}
    </div>
<<<<<<< HEAD
    {{ $reviews->links('admin.layouts.pagination') }}
=======

    <div class="review-confirm-modal" id="review-confirm-modal" hidden>
        <div class="review-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="review-confirm-title">
            <h3 id="review-confirm-title">Ẩn đánh giá này?</h3>
            <p>Đánh giá sẽ không còn hiển thị với khách hàng. Bạn có thể duyệt lại sau.</p>
            <form method="POST" id="review-confirm-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="hidden">
                <div class="review-confirm-actions"><button type="button" class="review-confirm-cancel"
                        id="review-confirm-cancel">Không</button><button type="submit" class="review-confirm-submit">Có, ẩn
                        đánh giá</button></div>
            </form>
        </div>
    </div>
>>>>>>> 047989117ac474d52a26948b7c17a43bd297a074
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('review-filter-form');
            const searchInput = document.getElementById('review-search');
            let searchTimer;

            document.getElementById('review-status')?.addEventListener('change', () => form?.submit());
            document.getElementById('review-rating')?.addEventListener('change', () => form?.submit());
            searchInput?.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => form?.submit(), 450);
            });

        });
    </script>
@endsection
