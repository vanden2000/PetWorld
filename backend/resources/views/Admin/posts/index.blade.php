@extends('admin.layouts.app')

@section('title', 'Danh sách bài viết')

@section('styles')
<style>
    /* Ngăn tiêu đề bảng bị xuống hàng */
    .orders-table th {
        white-space: nowrap !important;
    }

    /* Responsive Grid for Filter Card */
    .filters-form-grid-custom {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr minmax(190px, 1.2fr) !important;
        gap: 15px !important;
        align-items: flex-end !important;
        width: 100%;
    }

    @media (max-width: 1200px) {
        .filters-form-grid-custom {
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 20px !important;
        }
    }

    @media (max-width: 768px) {
        .filters-form-grid-custom {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 480px) {
        .filters-form-grid-custom {
            grid-template-columns: 1fr !important;
        }
    }

    /* Custom Select Dropdowns in Admin Filters */
    .custom-admin-select-container {
        position: relative;
        width: 100%;
    }

    .custom-admin-select-trigger {
        height: 38px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 0 14px;
        background-color: #ffffff;
        font-size: 0.9rem;
        color: var(--text-main);
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: var(--transition);
        user-select: none;
    }

    .custom-admin-select-trigger:hover,
    .custom-admin-select-container.open .custom-admin-select-trigger {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.1);
    }

    .custom-admin-select-trigger i {
        font-size: 0.75rem;
        color: #9ca3af;
        transition: transform 0.2s ease;
    }

    .custom-admin-select-container.open .custom-admin-select-trigger i {
        transform: rotate(180deg);
        color: var(--primary);
    }

    .custom-admin-select-options {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background-color: #ffffff;
        border: 1px solid #ebdcd0;
        border-radius: 8px;
        padding: 4px;
        margin: 0;
        list-style: none;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.06);
        z-index: 99;
        display: none;
        flex-direction: column;
        gap: 2px;
    }

    .custom-admin-select-container.open .custom-admin-select-options {
        display: flex;
    }

    .custom-admin-select-option {
        padding: 8px 12px;
        font-size: 0.88rem;
        font-weight: 500;
        color: #4b5563;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .custom-admin-select-option:hover {
        background-color: #fff4ec;
        color: var(--primary);
    }

    .custom-admin-select-option.selected {
        background-color: var(--primary);
        color: #ffffff;
    }
</style>
@endsection

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

<form class="filters-card orders-filter-card filters-form-grid-custom" method="GET" action="{{ route('admin.posts') }}">
    <!-- Row 1: Search, Category, Author -->
    <div class="filter-col">
        <label class="filter-label">Tìm kiếm bài viết</label>
        <div class="filter-input-wrapper">
            <i class="fa-solid fa-magnifying-glass filter-input-icon"></i>
            <input class="filter-input" name="search" value="{{ $search ?? '' }}" placeholder="Tiêu đề, mô tả ngắn...">
        </div>
    </div>

    <div class="filter-col">
        <label class="filter-label">Chuyên mục</label>
        <div class="filter-input-wrapper">
            <div class="custom-admin-select-container">
                <div class="custom-admin-select-trigger">
                    <span>Tất cả danh mục</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <input type="hidden" name="category_id" value="{{ $categoryId ?? '' }}">
                <div class="custom-admin-select-options">
                    <div class="custom-admin-select-option" data-value="">Tất cả danh mục</div>
                    @foreach($categories as $cat)
                        <div class="custom-admin-select-option" data-value="{{ $cat->id }}">{{ $cat->name }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="filter-col">
        <label class="filter-label">Tác giả</label>
        <div class="filter-input-wrapper">
            <div class="custom-admin-select-container">
                <div class="custom-admin-select-trigger">
                    <span>Tất cả tác giả</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <input type="hidden" name="author_id" value="{{ $authorId ?? '' }}">
                <div class="custom-admin-select-options">
                    <div class="custom-admin-select-option" data-value="">Tất cả tác giả</div>
                    @foreach($authors as $author)
                        <div class="custom-admin-select-option" data-value="{{ $author->id }}">{{ $author->name }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Status, Sort, Actions -->
    <div class="filter-col">
        <label class="filter-label">Trạng thái</label>
        <div class="filter-input-wrapper">
            <div class="custom-admin-select-container">
                <div class="custom-admin-select-trigger">
                    <span>Tất cả trạng thái</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <input type="hidden" name="status" value="{{ $status ?? '' }}">
                <div class="custom-admin-select-options">
                    <div class="custom-admin-select-option" data-value="">Tất cả trạng thái</div>
                    <div class="custom-admin-select-option" data-value="active">Đã xuất bản</div>
                    <div class="custom-admin-select-option" data-value="inactive">Bản nháp</div>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-col">
        <label class="filter-label">Sắp xếp</label>
        <div class="filter-input-wrapper">
            <div class="custom-admin-select-container">
                <div class="custom-admin-select-trigger">
                    <span>Mới nhất</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <input type="hidden" name="sort" value="{{ $sort ?? 'newest' }}">
                <div class="custom-admin-select-options">
                    <div class="custom-admin-select-option" data-value="newest">Mới nhất</div>
                    <div class="custom-admin-select-option" data-value="oldest">Cũ nhất</div>
                    <div class="custom-admin-select-option" data-value="popular">Nhiều lượt xem nhất</div>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-col orders-filter-actions" style="display: flex; gap: 10px; margin-top: auto; padding-bottom: 2px;">
        <button class="btn-dark-slate" style="flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; cursor: pointer;">
            <i class="fa-solid fa-filter"></i>
            <span>Lọc</span>
        </button>
        <a href="{{ route('admin.posts') }}" class="btn-clear-filters" style="flex: 1; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; border-radius: 7px; box-sizing: border-box; padding: 0;">
            Xóa lọc
        </a>
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
                    <th style="width: 12%;">Tác giả</th>
                    <th style="width: 8%; text-align: center;">Lượt xem</th>
                    <th style="width: 8%; text-align: center;">Bình luận</th>
                    <th style="width: 10%;">Ngày tạo</th>
                    <th style="width: 10%;">Trạng thái</th>
                    <th style="width: 14%; text-align: right;">Thao tác</th>
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
                                    } elseif (str_starts_with($post->image, 'uploads/') || str_starts_with($post->image, 'blogs/')) {
                                        $postImageUrl = asset('storage/' . $post->image);
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
                            <strong style="color: #2d2926; font-size: 0.95rem; display: block; line-height: 1.4;">{{ $post->title }}</strong>
                            <span style="font-size: 0.82rem; color: var(--text-muted); display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; max-width: 450px; margin-top: 4px;">
                                {{ $post->description }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-fulfillment delivered" style="background: rgba(255, 120, 45, 0.08); color: #ff782d; border: 1px solid rgba(255, 120, 45, 0.15); font-size: 0.8rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; display: inline-block; white-space: nowrap;">
                                {{ $post->category?->name ?? 'Chưa phân loại' }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 0.9rem; color: #5b5550; display: inline-flex; align-items: center; gap: 6px; font-weight: 500; white-space: nowrap;">
                                <i class="fa-regular fa-user-circle" style="font-size: 1.05rem; color: #9ca3af;"></i>
                                {{ $post->author?->name ?? 'Admin' }}
                            </span>
                        </td>
                        <td style="text-align: center; font-weight: 600; color: #2d2926; font-size: 0.9rem; white-space: nowrap;">
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
                            <div style="display: inline-flex; align-items: center; gap: 8px; justify-content: flex-end; width: 100%;">
                                <a href="{{ route('admin.posts.edit', $post) }}" 
                                   title="Sửa bài viết"
                                   style="text-decoration: none; border: 1px solid #ebdcd0; background: #fff; width: 34px; height: 34px; border-radius: 8px; color: #ff782d; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem;" 
                                   onmouseover="this.style.background='#fff8f3'; this.style.borderColor='var(--primary)'" 
                                   onmouseout="this.style.background='#fff'; this.style.borderColor='#ebdcd0'">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" 
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này? Tất cả bình luận liên quan cũng sẽ bị xóa.')" 
                                      style="display:inline-block; margin: 0; padding: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            title="Xóa bài viết"
                                            style="border: 1px solid #ebdcd0; background: #fff; width: 34px; height: 34px; border-radius: 8px; color: #dc2626; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; padding: 0;" 
                                            onmouseover="this.style.background='#fef2f2'; this.style.borderColor='#fca5a5'" 
                                            onmouseout="this.style.background='#fff'; this.style.borderColor='#ebdcd0'">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
                // Close other dropdowns
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

                    // Update hidden input and trigger text
                    hiddenInput.value = val;
                    triggerText.textContent = text;

                    // Update selected class
                    options.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');

                    // Close dropdown
                    dropdown.classList.remove('open');
                });
            });
        });

        // Close on click outside
        document.addEventListener('click', function() {
            dropdowns.forEach(dropdown => dropdown.classList.remove('open'));
        });
    });
</script>
@endsection