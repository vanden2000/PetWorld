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
                <li>
                    <a href="{{ route('admin.orders')}}" class="menu-item-link {{ request()->routeIs('admin.orders') ? 'active' : '' }} ">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Đơn Hàng</span>
                    </a>
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
                    <a href="#" class="menu-item-link">
                        <i class="fa-solid fa-chart-simple"></i>
                        <span>Báo Cáo</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item-link">
                        <i class="fa-solid fa-gear"></i>
                        <span>Cài Đặt</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar CTA -->
        <div class="sidebar-footer">
            <button class="btn-add-product">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Đăng Xuất</span>
            </button>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="wrapper">
        <!-- Top Header Navigation -->
        <header class="header">
            <div class="header-left">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" class="search-input" placeholder="Tìm kiếm hệ thống...">
                </div>
            </div>
            
            <div class="header-right">
                <a href="#" class="header-icon-btn">
                    <i class="fa-regular fa-bell"></i>
                    <span class="badge-dot"></span>
                </a>
                <a href="#" class="header-icon-btn">
                    <i class="fa-regular fa-circle-question"></i>
                </a>
                <a href="#" class="support-link">
                    <span>Support</span>
                </a>
                
                <div class="divider-vertical"></div>
                
                <a href="#" class="profile-menu">
                    <!-- Default stock profile picture style using initial letter or avatar -->
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=256&auto=format&fit=crop" alt="Admin Avatar" class="profile-avatar">
                    <div class="profile-details">
                        <span class="profile-name">Admin</span>
                        <span class="profile-role">Quản lý</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: var(--text-muted); margin-left: 4px;"></i>
                </a>
            </div>
        </header>

        <!-- Page Yield Content -->
        <main class="content-body">
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownArrows = document.querySelectorAll('.dropdown-toggle .dropdown-arrow');
            dropdownArrows.forEach(arrow => {
                arrow.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const parent = this.closest('.menu-item-dropdown');
                    if (parent) {
                        parent.classList.toggle('open');
                    }
                });
            });
        });
    </script>
    @yield('scripts')
</body>
</html>