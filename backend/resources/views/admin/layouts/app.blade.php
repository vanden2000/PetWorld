<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - PetWorld</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <link rel="icon" href="{{ asset('image/logo/Special_Offer_1-removebg-preview.png') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @yield('styles')
</head>

<body>

    <!-- Sidebar Layout -->
    <aside class="sidebar">
        <!-- Brand Logo/Details -->
        <div class="brand-shell">
            <a href="{{ route('admin.dashboard') }}" class="brand">
                <div class="brand-icon">
                    <i class="fa-solid fa-paw"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-name">PetWorld</span>
                    <span class="brand-sub">Admin Dashboard</span>
                </div>
            </a>
            <button type="button" class="sidebar-collapse-toggle" aria-label="Thu gọn menu" aria-expanded="true"
                title="Thu gọn menu">
                <i class="fa-solid fa-angles-left"></i>
            </button>
        </div>

        <!-- Menu items -->
        <nav class="sidebar-nav">
            <ul class="menu-list">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="menu-item-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Tổng quan</span>
                    </a>
                </li>
                <li
                    class="menu-item-dropdown {{ request()->routeIs('admin.orders*') || request()->routeIs('admin.reviews*') ? 'open' : '' }}">
                    <a href="{{ route('admin.orders')}}"
                        class="menu-item-link dropdown-toggle orders-dropdown-toggle {{ request()->routeIs('admin.orders') ? 'active' : '' }} ">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Đơn Hàng</span>
                        <i class="fa-solid fa-chevron-down dropdown-arrow"
                            style="margin-left: auto; font-size: 0.75rem; transition: var(--transition);"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ route('admin.orders') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}"><i
                                    class="fa-solid fa-list-ul"></i><span>Danh sách đơn hàng</span></a></li>
                        <li><a href="{{ route('admin.reviews') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}"><i
                                    class="fa-solid fa-star"></i><span>Đánh giá sản phẩm</span></a></li>
                    </ul>
                </li>
                @php
                    $isProductMenuActive =
                        request()->routeIs('admin.products*') ||
                        request()->routeIs('admin.pet-species*') ||
                        request()->routeIs('admin.categories*') ||
                        request()->routeIs('admin.brands*');
                @endphp
                <li class="menu-item-dropdown {{ $isProductMenuActive ? 'open' : '' }}">
                    <a href="{{ route('admin.products') }}"
                        class="menu-item-link dropdown-toggle {{ $isProductMenuActive ? 'active' : '' }}">
                        <i class="fa-solid fa-box-open"></i>
                        <span>Sản phẩm</span>

                        <i class="fa-solid fa-chevron-down dropdown-arrow"
                            style="margin-left: auto; font-size: 0.75rem; transition: var(--transition);">
                        </i>
                    </a>

                    <ul class="submenu">
                        <li>
                            <a href="{{ route('admin.products') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.products') ? 'active' : '' }}">
                                <i class="fa-solid fa-list-ul"></i>
                                <span>Danh sách</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.products.create') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                                <i class="fa-solid fa-square-plus"></i>
                                <span>Thêm sản phẩm</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.products.variants') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.products.variants') ? 'active' : '' }}">
                                <i class="fa-solid fa-tags"></i>
                                <span>Biến thể</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.pet-species') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.pet-species*') ? 'active' : '' }}">
                                <i class="fa-solid fa-paw"></i>
                                <span>Loài thú cưng</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.categories') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                                <i class="fa-solid fa-folder-tree"></i>
                                <span>Danh mục</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.brands') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.brands*') ? 'active' : '' }}">
                                <i class="fa-solid fa-tag"></i>
                                <span>Thương hiệu</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('admin.users') }}"
                        class="menu-item-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i>
                        <span>Người Dùng</span>
                    </a>
                </li>
                <li
                    class="menu-item-dropdown {{ request()->routeIs('admin.home-sections*') || request()->routeIs('admin.banners*') ? 'open' : '' }}">
                    <a href="{{ route('admin.home-sections.index') }}"
                        class="menu-item-link dropdown-toggle {{ request()->routeIs('admin.home-sections*') || request()->routeIs('admin.banners*') ? 'active' : '' }}">
                        <i class="fa-solid fa-house"></i>
                        <span>Trang chủ</span>
                        <i class="fa-solid fa-chevron-down dropdown-arrow"
                            style="margin-left: auto; font-size: 0.75rem; transition: var(--transition);">
                        </i>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ route('admin.home-sections.index') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.home-sections*') ? 'active' : '' }}"><i
                                    class="fa-solid fa-layer-group"></i><span>Quản lý khu vực</span></a></li>
                        <li><a href="{{ route('admin.banners') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.banners*') ? 'active' : '' }}"><i
                                    class="fa-solid fa-images"></i><span>Banner trang chủ</span></a></li>
                    </ul>
                </li>
                <li
                    class="menu-item-dropdown {{ request()->routeIs('admin.posts*') || request()->routeIs('admin.blog-comments*') ? 'open' : '' }}">
                    <a href="{{ route('admin.posts') }}"
                        class="menu-item-link dropdown-toggle {{ request()->routeIs('admin.posts*') || request()->routeIs('admin.blog-comments*') ? 'active' : '' }}">
                        <i class="fa-solid fa-newspaper"></i>
                        <span>Bài Viết</span>
                        <i class="fa-solid fa-chevron-down dropdown-arrow"
                            style="margin-left: auto; font-size: 0.75rem; transition: var(--transition);"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ route('admin.posts') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.posts') ? 'active' : '' }}"><i
                                    class="fa-solid fa-list-ul"></i><span>Danh sách bài viết</span></a></li>
                        <li><a href="{{ route('admin.blog-comments') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.blog-comments*') ? 'active' : '' }}"><i
                                    class="fa-solid fa-comments"></i><span>Quản lý bình luận</span></a></li>
                    </ul>
                </li>
                <li class="menu-section-label"><span>Tiếp thị &amp; AI</span></li>
                <li>
                    <a href="{{ route('admin.vouchers') }}"
                        class="menu-item-link {{ request()->routeIs('admin.vouchers*') ? 'active' : '' }}">
                        <i class="fa-solid fa-ticket"></i>
                        <span>Voucher</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.knowledge') }}"
                        class="menu-item-link {{ request()->routeIs('admin.knowledge*') ? 'active' : '' }}">
                        <i class="fa-solid fa-robot"></i><span>Kiến thức chatbot</span>
                    </a>
                </li>
                <li class="menu-section-label"><span>Phân tích</span></li>
                <li class="menu-item-dropdown {{ request()->routeIs('admin.reports*') ? 'open' : '' }}">
                    <a href="#"
                        class="menu-item-link dropdown-toggle {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-simple"></i>
                        <span>Báo Cáo</span>
                        <i class="fa-solid fa-chevron-down dropdown-arrow"
                            style="margin-left: auto; font-size: 0.75rem; transition: var(--transition);"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="{{ route('admin.reports.revenue') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.reports.revenue') ? 'active' : '' }}">
                                <i class="fa-solid fa-wallet"></i>
                                <span>Doanh Thu</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.order-status') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.reports.order-status') ? 'active' : '' }}">
                                <i class="fa-solid fa-chart-pie"></i>
                                <span>Trạng Thái Đơn</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.customers') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.reports.customers') ? 'active' : '' }}">
                                <i class="fa-solid fa-users"></i>
                                <span>Khách Hàng</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.best-sellers') }}"
                                class="submenu-item-link {{ request()->routeIs('admin.reports.best-sellers') ? 'active' : '' }}">
                                <i class="fa-solid fa-fire"></i>
                                <span>Sản Phẩm Bán Chạy</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-section-label"><span>Hệ thống</span></li>
                <li>
                    <a href="{{ route('admin.account.edit') }}"
                        class="menu-item-link {{ request()->routeIs('admin.account.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-gear"></i>
                        <span>Tài khoản admin</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar logout -->
        <div class="sidebar-footer">
            <form action="{{ route('admin.logout') }}" method="POST" id="logout-form" style="width: 100%;">
                @csrf
                <button type="submit" class="sidebar-logout" style="width: 100%; cursor: pointer;">
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
                    <input type="text" id="admin-global-search" name="admin_global_search" class="search-input"
                        placeholder="Tìm kiếm hệ thống...">
                </div>
            </div>

            <div class="header-right">
                <div class="notification-menu">
                    <button type="button" class="header-icon-btn">
                        <i class="fa-regular fa-bell"></i>
                        @if(($adminNotificationsTotal ?? 0) > 0)
                            <span class="badge-dot"></span>
                            <span
                                class="notification-count">{{ $adminNotificationsTotal > 99 ? '99+' : $adminNotificationsTotal }}</span>
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
                        <i class="fa-solid fa-chevron-down"
                            style="font-size: 0.75rem; color: var(--text-muted); margin-left: 4px;"></i>
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

    <div class="admin-confirm-modal" id="admin-confirm-modal" hidden aria-hidden="true">
        <div class="admin-confirm-card" role="dialog" aria-modal="true" aria-labelledby="admin-confirm-title" aria-describedby="admin-confirm-message">
            <div class="admin-confirm-icon" aria-hidden="true">?</div>
            <h3 id="admin-confirm-title">Xác nhận cập nhật</h3>
            <p id="admin-confirm-message">Bạn có chắc muốn cập nhật trạng thái đơn hàng?</p>
            <div class="admin-confirm-actions">
                <button type="button" class="admin-confirm-cancel" id="admin-confirm-cancel">Hủy bỏ</button>
                <button type="button" class="admin-confirm-approve" id="admin-confirm-approve">Đồng ý</button>
            </div>
        </div>
    </div>

    @php
        $validationToast = $errors->any()
            ? $errors->first() . ($errors->count() > 1 ? ' và ' . ($errors->count() - 1) . ' mục khác.' : '')
            : null;
        $adminToast = session('success') ? ['success', session('success')] : (session('error') ? ['error', session('error')] : (session('warning') ? ['warning', session('warning')] : ($validationToast ? ['error', $validationToast] : null)));
    @endphp
    @if($adminToast)
        <div class="admin-global-toast {{ $adminToast[0] }}" id="admin-global-toast" role="alert">
            <i class="fa-solid {{ $adminToast[0] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
            <span>{{ $adminToast[1] }}</span><button type="button" aria-label="Đóng thông báo">&times;</button>
        </div>
    @endif

    <style>
        .admin-global-toast {
            position: fixed;
            right: 22px;
            bottom: 24px;
            z-index: 1100;
            display: flex;
            align-items: center;
            gap: 9px;
            max-width: min(420px, calc(100vw - 28px));
            padding: 12px 14px;
            border: 1px solid;
            border-radius: 9px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .16);
            font-size: .86rem;
            font-weight: 700;
            transition: opacity .2s ease, transform .2s ease;
        }

        .admin-global-toast.success {
            color: #176b37;
            border-color: #bde5ca;
            background: #ecf9f0;
        }

        .admin-global-toast.error {
            color: #a12626;
            border-color: #f2c5c5;
            background: #fff1f1;
        }

        .admin-global-toast.warning {
            color: #9a4b1b;
            border-color: #f0d5bc;
            background: #fff8ed;
        }

        .admin-global-toast button {
            margin-left: 4px;
            padding: 0;
            border: 0;
            color: inherit;
            background: transparent;
            font-size: 1.25rem;
            line-height: 1;
            cursor: pointer;
        }

        .admin-global-toast.is-hidden {
            opacity: 0;
            transform: translateY(8px);
            pointer-events: none;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('.sidebar');
            const collapseToggle = document.querySelector('.sidebar-collapse-toggle');
            const sidebarStateKey = 'petworld.admin.sidebar.collapsed';

            const syncSidebarState = (collapsed) => {
                document.body.classList.toggle('sidebar-collapsed', collapsed);
                collapseToggle?.setAttribute('aria-expanded', String(!collapsed));
                collapseToggle?.setAttribute('aria-label', collapsed ? 'Mở rộng menu' : 'Thu gọn menu');
                collapseToggle?.setAttribute('title', collapsed ? 'Mở rộng menu' : 'Thu gọn menu');
                collapseToggle?.querySelector('i')?.classList.toggle('fa-angles-right', collapsed);
                collapseToggle?.querySelector('i')?.classList.toggle('fa-angles-left', !collapsed);

                sidebar?.querySelectorAll('.menu-item-link, .sidebar-logout').forEach((item) => {
                    const label = item.querySelector('span')?.textContent?.trim();
                    if (label) item.setAttribute('title', collapsed ? label : '');
                });
            };

            const initiallyCollapsed = window.localStorage.getItem(sidebarStateKey) === 'true';
            syncSidebarState(initiallyCollapsed);

            collapseToggle?.addEventListener('click', function () {
                const collapsed = !document.body.classList.contains('sidebar-collapsed');
                window.localStorage.setItem(sidebarStateKey, String(collapsed));
                syncSidebarState(collapsed);
            });

            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function (e) {
                    const isCollapsed = document.body.classList.contains('sidebar-collapsed') && window.innerWidth > 992;
                    if (isCollapsed) {
                        e.preventDefault();
                        const parent = this.closest('.menu-item-dropdown');
                        window.localStorage.setItem(sidebarStateKey, 'false');
                        syncSidebarState(false);
                        sidebar?.querySelectorAll('.menu-item-dropdown.open').forEach((item) => {
                            if (item !== parent) item.classList.remove('open');
                        });
                        parent?.classList.add('open');
                        return;
                    }

                    e.preventDefault();
                    const parent = this.closest('.menu-item-dropdown');
                    if (parent) {
                        const willOpen = !parent.classList.contains('open');
                        sidebar?.querySelectorAll('.menu-item-dropdown.open').forEach((item) => {
                            if (item !== parent) item.classList.remove('open');
                        });
                        parent.classList.toggle('open', willOpen);
                    }
                });
            });

            const adminToast = document.getElementById('admin-global-toast');
            if (adminToast) {
                document.querySelectorAll('.alert-panel').forEach((alert) => { alert.hidden = true; });
                const hideToast = () => adminToast.classList.add('is-hidden');
                adminToast.querySelector('button')?.addEventListener('click', hideToast);
                setTimeout(hideToast, 3000);
            }

            const confirmModal = document.getElementById('admin-confirm-modal');
            const confirmCancel = document.getElementById('admin-confirm-cancel');
            const confirmApprove = document.getElementById('admin-confirm-approve');
            const confirmMessage = document.getElementById('admin-confirm-message');
            let pendingConfirmationForm = null;

            const closeConfirmModal = () => {
                if (!confirmModal) return;
                confirmModal.hidden = true;
                confirmModal.setAttribute('aria-hidden', 'true');
                pendingConfirmationForm = null;
            };

            const openConfirmModal = (form) => {
                if (!confirmModal) return;
                pendingConfirmationForm = form;
                confirmApprove.disabled = false;
                confirmApprove.textContent = 'Đồng ý';
                confirmMessage.textContent = form.dataset.confirmMessage || 'Bạn có chắc muốn cập nhật trạng thái đơn hàng?';
                confirmModal.hidden = false;
                confirmModal.setAttribute('aria-hidden', 'false');
                confirmApprove.focus();
            };

            document.querySelectorAll('.quick-status-menu form, .admin-confirm-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    openConfirmModal(form);
                });
            });

            confirmCancel?.addEventListener('click', closeConfirmModal);

            confirmApprove?.addEventListener('click', () => {
                if (!pendingConfirmationForm) return;

                confirmApprove.disabled = true;
                confirmApprove.textContent = 'Đang cập nhật...';
                HTMLFormElement.prototype.submit.call(pendingConfirmationForm);
            });

            confirmModal?.addEventListener('click', (event) => {
                if (event.target === confirmModal) closeConfirmModal();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !confirmModal?.hidden) closeConfirmModal();
            });
        });
    </script>
    @yield('scripts')
</body>

</html>
