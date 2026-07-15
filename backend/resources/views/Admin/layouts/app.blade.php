<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - PetWorld</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @yield('styles')
</head>
<body>

    <!-- Sidebar Layout -->
    <aside class="sidebar">
        <!-- Brand Logo/Details -->
        <a href="{{ route('admin.dashboard') }}" class="brand">
            <div class="brand-icon">
                <i class="fa-solid fa-paw"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">PetWorld</span>
                <span class="brand-sub">Admin Dashboard</span>
            </div>
        </a>

        <!-- Menu items -->
        <nav style="flex-grow: 1;">
            <ul class="menu-list">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="menu-item-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-house"></i>
                        <span>Thống Kê</span>
                    </a>
                </li>
                <li class="menu-item-dropdown {{ request()->routeIs('admin.orders*') || request()->routeIs('admin.reviews*') ? 'open' : '' }}">
                    <a href="{{ route('admin.orders')}}" class="menu-item-link dropdown-toggle orders-dropdown-toggle {{ request()->routeIs('admin.orders') ? 'active' : '' }} ">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Đơn Hàng</span>
                        <i class="fa-solid fa-chevron-down dropdown-arrow" style="margin-left: auto; font-size: 0.75rem; transition: var(--transition);"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ route('admin.orders') }}" class="submenu-item-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}"><i class="fa-solid fa-list-ul"></i><span>Danh sách đơn hàng</span></a></li>
                        <li><a href="{{ route('admin.reviews') }}" class="submenu-item-link {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}"><i class="fa-solid fa-star"></i><span>Đánh giá sản phẩm</span></a></li>
                    </ul>
                </li>
                <li class="menu-item-dropdown {{ request()->routeIs('admin.categories*') ? 'open' : '' }}">
                    <a href="{{ route('admin.categories') }}" class="menu-item-link dropdown-toggle {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                        <i class="fa-solid fa-list"></i>
                        <span>Danh Mục</span>
                        <i class="fa-solid fa-chevron-down dropdown-arrow" style="margin-left: auto; font-size: 0.75rem; transition: var(--transition);"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="{{ route('admin.categories') }}" class="submenu-item-link {{ request()->routeIs('admin.categories') && !request()->routeIs('admin.categories.create') ? 'active' : '' }}">
                                <i class="fa-solid fa-list-ul"></i>
                                <span>Danh sách</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.categories.create') }}" class="submenu-item-link {{ request()->routeIs('admin.categories.create') ? 'active' : '' }}">
                                <i class="fa-solid fa-square-plus"></i>
                                <span>Thêm danh mục</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item-dropdown {{ request()->routeIs('admin.brands*') ? 'open' : '' }}">
                    <a href="{{ route('admin.brands') }}" class="menu-item-link dropdown-toggle {{ request()->routeIs('admin.brands*') ? 'active' : '' }}">
                        <i class="fa-solid fa-trademark"></i>
                        <span>Thương Hiệu</span>
                        <i class="fa-solid fa-chevron-down dropdown-arrow" style="margin-left: auto; font-size: 0.75rem; transition: var(--transition);"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="{{ route('admin.brands') }}" class="submenu-item-link {{ request()->routeIs('admin.brands') && !request()->routeIs('admin.brands.create') ? 'active' : '' }}">
                                <i class="fa-solid fa-list-ul"></i>
                                <span>Danh sách</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.brands.create') }}" class="submenu-item-link {{ request()->routeIs('admin.brands.create') ? 'active' : '' }}">
                                <i class="fa-solid fa-square-plus"></i>
                                <span>Thêm thương hiệu</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item-dropdown {{ request()->routeIs('admin.products*') ? 'open' : '' }}">
                    <a href="{{ route('admin.products') }}" class="menu-item-link dropdown-toggle {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                        <i class="fa-solid fa-box"></i>
                        <span>Sản Phẩm</span>
                        <i class="fa-solid fa-chevron-down dropdown-arrow" style="margin-left: auto; font-size: 0.75rem; transition: var(--transition);"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="{{ route('admin.products') }}" class="submenu-item-link {{ request()->routeIs('admin.products') && !request()->routeIs('admin.products.create') && !request()->routeIs('admin.products.variants') ? 'active' : '' }}">
                                <i class="fa-solid fa-list-ul"></i>
                                <span>Danh sách</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.products.create') }}" class="submenu-item-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                                <i class="fa-solid fa-square-plus"></i>
                                <span>Thêm sản phẩm</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.products.variants') }}" class="submenu-item-link {{ request()->routeIs('admin.products.variants') ? 'active' : '' }}">
                                <i class="fa-solid fa-tags"></i>
                                <span>Biến thể</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('admin.users') }}" class="menu-item-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i>
                        <span>Người Dùng</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.vouchers') }}" class="menu-item-link {{ request()->routeIs('admin.vouchers*') ? 'active' : '' }}">
                        <i class="fa-solid fa-ticket"></i>
                        <span>Voucher</span>
                    </a>
                </li>
                <li class="menu-item-dropdown {{ request()->routeIs('admin.posts*') || request()->routeIs('admin.blog-comments*') ? 'open' : '' }}">
                    <a href="{{ route('admin.posts') }}" class="menu-item-link dropdown-toggle {{ request()->routeIs('admin.posts*') || request()->routeIs('admin.blog-comments*') ? 'active' : '' }}">
                        <i class="fa-solid fa-newspaper"></i>
                        <span>Bài Viết</span>
                        <i class="fa-solid fa-chevron-down dropdown-arrow" style="margin-left: auto; font-size: 0.75rem; transition: var(--transition);"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ route('admin.posts') }}" class="submenu-item-link {{ request()->routeIs('admin.posts') ? 'active' : '' }}"><i class="fa-solid fa-list-ul"></i><span>Danh sách bài viết</span></a></li>
                        <li><a href="{{ route('admin.blog-comments') }}" class="submenu-item-link {{ request()->routeIs('admin.blog-comments*') ? 'active' : '' }}"><i class="fa-solid fa-comments"></i><span>Quản lý bình luận</span></a></li>
                    </ul>
                </li>
                <li class="menu-item-dropdown {{ request()->routeIs('admin.reports*') ? 'open' : '' }}">
                    <a href="#" class="menu-item-link dropdown-toggle {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-simple"></i>
                        <span>Báo Cáo</span>
                        <i class="fa-solid fa-chevron-down dropdown-arrow" style="margin-left: auto; font-size: 0.75rem; transition: var(--transition);"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="{{ route('admin.reports.revenue') }}" class="submenu-item-link {{ request()->routeIs('admin.reports.revenue') ? 'active' : '' }}">
                                <i class="fa-solid fa-wallet"></i>
                                <span>Doanh Thu</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.order-status') }}" class="submenu-item-link {{ request()->routeIs('admin.reports.order-status') ? 'active' : '' }}">
                                <i class="fa-solid fa-chart-pie"></i>
                                <span>Trạng Thái Đơn</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.customers') }}" class="submenu-item-link {{ request()->routeIs('admin.reports.customers') ? 'active' : '' }}">
                                <i class="fa-solid fa-users"></i>
                                <span>Khách Hàng</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.best-sellers') }}" class="submenu-item-link {{ request()->routeIs('admin.reports.best-sellers') ? 'active' : '' }}">
                                <i class="fa-solid fa-fire"></i>
                                <span>Sản Phẩm Bán Chạy</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.low-stock') }}" class="submenu-item-link {{ request()->routeIs('admin.reports.low-stock') ? 'active' : '' }}">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>Sắp Hết Hàng</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.latest-orders') }}" class="submenu-item-link {{ request()->routeIs('admin.reports.latest-orders') ? 'active' : '' }}">
                                <i class="fa-solid fa-clock"></i>
                                <span>Đơn Hàng Mới Nhất</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('admin.account.edit') }}" class="menu-item-link {{ request()->routeIs('admin.account.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-gear"></i>
                        <span>Tài khoản admin</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar CTA -->
        <div class="sidebar-footer" style="padding: 1rem;">
            <form action="{{ route('admin.logout') }}" method="POST" id="logout-form" style="width: 100%;">
                @csrf
                <button type="submit" class="btn-add-product" style="width: 100%; cursor: pointer;">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Đăng Xuất</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="wrapper">
        <!-- Top Header Navigation -->
        <header class="header">
            <div class="header-left">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="admin-global-search" name="admin_global_search" class="search-input" placeholder="Tìm kiếm hệ thống...">
                </div>
            </div>
            
            <div class="header-right">
                <div class="notification-menu">
                    <button type="button" class="header-icon-btn">
                        <i class="fa-regular fa-bell"></i>
                        @if(($adminNotificationsTotal ?? 0) > 0)
                            <span class="badge-dot"></span>
                            <span class="notification-count">{{ $adminNotificationsTotal > 99 ? '99+' : $adminNotificationsTotal }}</span>
                        @endif
                    </button>
                    <div class="notification-dropdown">
                        <div class="notification-dropdown-header">
                            <strong>Thông báo</strong>
                            <span>{{ $adminNotificationsTotal ?? 0 }} mới</span>
                        </div>
                        @foreach(($adminNotifications ?? []) as $notification)
                            <a href="{{ $notification['url'] }}" class="notification-item">
                                <span class="notification-item-icon">
                                    <i class="{{ $notification['icon'] }}"></i>
                                </span>
                                <span class="notification-item-text">{{ $notification['label'] }}</span>
                                <strong>{{ $notification['count'] }}</strong>
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="#" class="header-icon-btn">
                    <i class="fa-regular fa-circle-question"></i>
                </a>
                
                <div class="divider-vertical"></div>
                
                @php
                    $adminUser = Auth::user();
                    $adminAvatar = $adminUser && $adminUser->avatar
                        ? (str_contains($adminUser->avatar, '://') ? $adminUser->avatar : asset('storage/' . $adminUser->avatar))
                        : asset('image/logo/logo.png');
                @endphp
                <div class="profile-dropdown">
                    <button type="button" class="profile-menu">
                        <img src="{{ $adminAvatar }}" alt="Admin Avatar" class="profile-avatar">
                        <div class="profile-details">
                            <span class="profile-name">{{ $adminUser?->name ?? 'Admin' }}</span>
                            <span class="profile-role">Admin đang đăng nhập</span>
                        </div>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: var(--text-muted); margin-left: 4px;"></i>
                    </button>
                    <div class="profile-dropdown-menu">
                        <a href="{{ route('admin.account.edit') }}" class="profile-dropdown-item">
                            <i class="fa-regular fa-user"></i>
                            <span>Quản lý tài khoản</span>
                        </a>
                        <form action="{{ route('admin.logout') }}" method="POST" id="logout-form">
                            @csrf
                            <button type="submit" class="profile-dropdown-item profile-logout-btn">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span>Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Yield Content -->
        <main class="content-body">
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href === '#' || href === 'javascript:void(0);' || e.target.classList.contains('dropdown-arrow')) {
                        e.preventDefault();
                        const parent = this.closest('.menu-item-dropdown');
                        if (parent) {
                            parent.classList.toggle('open');
                        }
                    }
                });
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
