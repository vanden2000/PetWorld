@extends('admin.layouts.app')
@section('title', 'Kiến thức chatbot')

@section('styles')
    <style>
        .categories-add-btn {
            background-color: var(--primary);
            color: #ffffff !important;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(255, 120, 45, 0.15);
            border: none;
        }

        .categories-add-btn:hover {
            background-color: var(--primary-hover);
            box-shadow: 0 6px 16px rgba(255, 120, 45, 0.25);
            transform: translateY(-1px);
        }

        .categories-filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            background: var(--surface-color);
            padding: 16px 24px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            margin-bottom: 24px;
            box-shadow: var(--shadow-subtle);
        }

        .search-wrapper {
            position: relative;
            min-width: 320px;
        }

        .search-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.9rem;
            pointer-events: none;
        }

        .search-input-field {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: var(--font-main);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .search-input-field:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.1);
        }

        .btn-filter-submit {
            background-color: #1f2e2a;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-filter-submit:hover {
            background-color: #2c3f3a;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }

        .badge-status.active {
            background-color: #e6f7ec;
            color: #10b981;
        }

        .badge-status.active .dot {
            width: 6px;
            height: 6px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            animation: pulse-green 2.5s infinite;
        }

        .badge-status.draft {
            background-color: #f1f3f5;
            color: #6c757d;
        }

        .badge-status.draft .dot {
            width: 6px;
            height: 6px;
            background-color: #6c757d;
            border-radius: 50%;
            display: inline-block;
        }

        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .badge-category {
            display: inline-block;
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 6px;
        }
    </style>
@endsection

@section('content')
<!-- Dashboard Header Nav Bar -->
<div class="dashboard-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div class="header-title-block">
        <h1 style="color: var(--text-main); font-weight: 700; font-size: 1.75rem;">Kiến thức chatbot</h1>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.95rem;">Chỉ các bài viết có trạng thái xuất bản mới được chatbot sử dụng để trả lời người dùng.</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.knowledge.create') }}" class="categories-add-btn">
            <i class="fa-solid fa-plus"></i> Thêm bài kiến thức
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert-panel alert-success-box" style="margin-bottom: 24px;">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<!-- Search & Filter Bar -->
<div class="categories-filter-bar">
    <form action="{{ route('admin.knowledge') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; width: 100%;">
        <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" class="search-input-field" placeholder="Tìm kiếm bài viết theo tiêu đề..." value="{{ $search }}">
        </div>
        <button type="submit" class="btn-filter-submit">Lọc</button>
        @if($search)
            <a href="{{ route('admin.knowledge') }}" class="btn-cancel" style="padding: 9px 16px;">Xóa bộ lọc</a>
        @endif
    </form>
</div>

<!-- Table Card List -->
<div class="table-card">
    <div class="table-container">
        <table class="orders-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="padding: 16px 24px; text-align: left;">Tiêu đề bài viết</th>
                    <th style="padding: 16px 24px; text-align: left;">Nhóm kiến thức</th>
                    <th style="padding: 16px 24px; text-align: left;">Trạng thái</th>
                    <th style="padding: 16px 24px; text-align: center;">Phiên bản</th>
                    <th style="padding: 16px 24px; text-align: left;">Ngày cập nhật</th>
                    <th style="padding: 16px 24px; text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($articles as $article)
                    <tr>
                        <td style="padding: 16px 24px;">
                            <strong style="color: var(--text-main); font-size: 0.95rem;">{{ $article->title }}</strong>
                        </td>
                        <td style="padding: 16px 24px;">
                            <span class="badge-category">
                                {{ ['shipping'=>'Giao hàng','payment'=>'Thanh toán','returns'=>'Đổi trả','voucher'=>'Voucher','contact'=>'Liên hệ'][$article->category] ?? $article->category }}
                            </span>
                        </td>
                        <td style="padding: 16px 24px;">
                            @if($article->status === 'published')
                                <span class="badge-status active"><span class="dot"></span>Xuất bản</span>
                            @elseif($article->status === 'draft')
                                <span class="badge-status draft"><span class="dot"></span>Lưu nháp</span>
                            @else
                                <span class="badge-status draft" style="background-color: #fce8e6; color: #c5221f;">
                                    <span class="dot" style="background-color: #c5221f;"></span>Lưu trữ
                                </span>
                            @endif
                        </td>
                        <td style="padding: 16px 24px; text-align: center;">
                            <span style="font-weight: 600; color: var(--text-muted);">v{{ $article->version }}</span>
                        </td>
                        <td style="padding: 16px 24px;">
                            <span style="color: var(--text-muted); font-size: 0.9rem;">
                                {{ $article->updated_at?->format('d/m/Y H:i') }}
                            </span>
                        </td>
                        <td style="padding: 16px 24px; text-align: right;">
                            <a href="{{ route('admin.knowledge.edit', $article) }}" style="color: var(--primary); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-pen-to-square"></i> Sửa
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 32px; text-align: center; color: var(--text-muted);">
                            Chưa có bài kiến thức nào phù hợp.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 16px;">
    {{ $articles->links('pagination::bootstrap-4') }}
</div>
@endsection
