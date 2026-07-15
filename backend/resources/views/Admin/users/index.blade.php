@extends('admin.layouts.app')

@section('title', 'Quản lý người dùng')

@section('styles')
    <style>
        .users-page {
            display: grid;
            gap: 22px;
        }

        .users-hero {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: end;
        }

        .users-hero h1 {
            margin: 0;
            color: var(--text-main);
            font-size: 1.55rem;
            letter-spacing: -.4px;
        }

        .users-hero p {
            margin: 5px 0 0;
            color: var(--text-muted);
        }

        .users-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .user-stat {
            min-height: 116px;
            padding: 17px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: #fff;
            box-shadow: var(--shadow-subtle);
        }

        .user-stat .stat-icon {
            display: inline-grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: var(--primary-light);
            color: var(--primary);
        }

        .user-stat .stat-label {
            display: block;
            margin-top: 11px;
            color: var(--text-muted);
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .user-stat strong {
            display: block;
            margin-top: 3px;
            color: var(--text-main);
            font-size: 1.35rem;
        }

        .users-filter {
            display: grid;
            grid-template-columns: minmax(240px, 1fr) 160px 160px auto;
            gap: 12px;
            align-items: end;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: #fff;
        }

        .users-filter label {
            display: block;
            margin-bottom: 6px;
            color: var(--text-muted);
            font-size: .73rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .users-filter input,
        .users-filter select {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-main);
            background: #fff;
            outline: none;
        }

        .users-filter input:focus,
        .users-filter select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 120, 45, .12);
        }

        .users-filter-actions {
            display: flex;
            gap: 8px;
        }

        .users-filter-actions button,
        .users-filter-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 42px;
            padding: 0 14px;
            border: 0;
            border-radius: 8px;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .users-filter-actions button {
            background: var(--primary);
            color: #fff;
        }

        .users-filter-actions a {
            color: var(--text-muted);
            background: var(--bg-color);
        }

        .users-table-card {
            overflow: hidden;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: #fff;
            box-shadow: var(--shadow-subtle);
        }

        .users-table-heading {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            padding: 17px 18px;
            border-bottom: 1px solid var(--border-color);
        }

        .users-table-heading h2 {
            margin: 0;
            color: var(--text-main);
            font-size: 1rem;
        }

        .users-table-heading span {
            color: var(--text-muted);
            font-size: .8rem;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th {
            padding: 11px 14px;
            color: var(--text-muted);
            background: #fafbfb;
            font-size: .7rem;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .users-table td {
            padding: 14px;
            border-top: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .user-person {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 210px;
        }

        .user-avatar {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 800;
        }

        .user-avatar-img {
            place-items: center;
            width: 38px;
            height: 38px;
            object-fit: cover;
            border-radius: 50%;
        }

        .user-person strong,
        .user-person small {
            display: block;
        }

        .user-person strong {
            color: var(--text-main);
            font-size: .86rem;
        }

        .user-person small {
            overflow: hidden;
            color: var(--text-muted);
            font-size: .76rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-role,
        .user-status,
        .user-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 9px;
            font-size: .72rem;
            font-weight: 800;
        }

        .user-role.admin {
            color: #7c3aed;
            background: #f3e8ff;
        }

        .user-role.user,
        .user-badge {
            color: #475569;
            background: #f1f5f9;
        }

        .user-status.active {
            color: #15803d;
            background: #dcfce7;
        }

        .user-status.inactive {
            color: #a16207;
            background: #fef9c3;
        }

        .user-status.blocked {
            color: #b91c1c;
            background: #fee2e2;
        }

        .users-empty {
            padding: 48px 20px;
            color: var(--text-muted);
            text-align: center;
        }

        .user-email {
            color: var(--primary);
            text-decoration: none;
        }

        .user-actions {
            display: inline-flex;
            gap: 7px;
        }

        .user-actions form {
            margin: 0;
        }

        .user-action {
            display: inline-grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: #fff;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition);
        }

        .user-action:hover {
            transform: translateY(-1px);
        }

        .user-action.lock:hover {
            border-color: #ef4444;
            background: #fef2f2;
            color: #dc2626;
        }

        .user-action.unlock:hover {
            border-color: #16a34a;
            background: #f0fdf4;
            color: #15803d;
        }

        .user-action.admin:hover {
            border-color: #8b5cf6;
            background: #f5f3ff;
            color: #7c3aed;
        }

        .users-alert {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 14px;
            border-radius: 9px;
            font-weight: 600;
        }

        .users-alert.success {
            color: #15803d;
            background: #dcfce7;
        }

        .users-alert.error {
            color: #b91c1c;
            background: #fee2e2;
        }

        .users-pagination {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            padding: 14px 18px;
            border-top: 1px solid var(--border-color);
        }

        .users-pagination a,
        .users-pagination span {
            display: grid;
            place-items: center;
            min-width: 32px;
            height: 32px;
            border-radius: 7px;
            color: var(--text-main);
            text-decoration: none;
        }

        .users-pagination .active {
            background: var(--primary);
            color: #fff;
        }

        .user-confirm-modal[hidden] {
            display: none;
        }

        .user-confirm-modal {
            position: fixed;
            inset: 0;
            z-index: 1100;
            display: grid;
            place-items: center;
            padding: 20px;
            background: rgba(15, 23, 42, .5);
        }

        .user-confirm-card {
            width: min(400px, 100%);
            padding: 24px;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .25);
        }

        .user-confirm-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border-radius: 11px;
            background: var(--primary-light);
            color: var(--primary);
        }

        .user-confirm-card h3 {
            margin: 14px 0 7px;
            color: var(--text-main);
            font-size: 1.08rem;
        }

        .user-confirm-card p {
            margin: 0;
            color: var(--text-muted);
            line-height: 1.55;
        }

        .user-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
            margin-top: 22px;
        }

        .user-confirm-actions button {
            border: 0;
            border-radius: 8px;
            padding: 10px 15px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .user-confirm-cancel {
            background: var(--bg-color);
            color: var(--text-main);
        }

        .user-confirm-submit {
            background: var(--primary);
            color: #fff;
        }

        @media (max-width: 900px) {
            .users-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .users-filter {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 620px) {

            .users-hero,
            .users-table-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .users-stats,
            .users-filter {
                grid-template-columns: 1fr;
            }

            .users-table-card {
                overflow-x: auto;
            }

            .users-table {
                min-width: 760px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="users-page">
        @if(session('success'))
        <div class="users-alert success"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>@endif
        @if(session('error'))
        <div class="users-alert error"><i class="fa-solid fa-circle-exclamation"></i>{{ session('error') }}</div>@endif
        <header class="users-hero">
            <div>
                <h1>Quản lý người dùng</h1>
                <p>Theo dõi khách hàng, tài khoản quản trị và trạng thái hoạt động.</p>
            </div>
        </header>

        <section class="users-stats">
            <article class="user-stat"><span class="stat-icon"><i class="fa-solid fa-users"></i></span><span
                    class="stat-label">Tổng người dùng</span><strong>{{ number_format($stats['total']) }}</strong></article>
            <article class="user-stat"><span class="stat-icon"><i class="fa-solid fa-user-check"></i></span><span
                    class="stat-label">Đang hoạt động</span><strong>{{ number_format($stats['active']) }}</strong></article>
            <article class="user-stat"><span class="stat-icon"><i class="fa-solid fa-user-slash"></i></span><span
                    class="stat-label">Đã khóa</span><strong>{{ number_format($stats['blocked']) }}</strong></article>
            <article class="user-stat"><span class="stat-icon"><i class="fa-solid fa-user-plus"></i></span><span
                    class="stat-label">Mới trong tháng</span><strong>{{ number_format($stats['newThisMonth']) }}</strong>
            </article>
        </section>

        <form class="users-filter" method="GET" action="{{ route('admin.users') }}">
            <div><label for="user-search">Tìm kiếm</label><input id="user-search" name="search"
                    value="{{ $filters['search'] ?? '' }}" placeholder="Tên, email hoặc số điện thoại"></div>
            <div><label for="user-role">Vai trò</label><select id="user-role" name="role">
                    <option value="">Tất cả</option>
                    <option value="user" @selected(($filters['role'] ?? '') === 'user')>Khách hàng</option>
                    <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Quản trị viên</option>
                </select></div>
            <div><label for="user-status">Trạng thái</label><select id="user-status" name="status">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Hoạt động</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Không hoạt động</option>
                    <option value="blocked" @selected(($filters['status'] ?? '') === 'blocked')>Đã khóa</option>
                </select></div>
            <div class="users-filter-actions"><button type="submit"><i class="fa-solid fa-filter"></i>&nbsp; Lọc</button><a
                    href="{{ route('admin.users') }}" title="Xóa bộ lọc"><i class="fa-solid fa-rotate-left"></i></a></div>
        </form>

        <section class="users-table-card">
            <div class="users-table-heading">
                <h2>Danh sách tài khoản</h2><span>{{ number_format($users->total()) }} kết quả</span>
            </div>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Đơn hàng</th>
                        <th>Xác thực email</th>
                        <th>Tham gia</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <td>
                            <div class="user-person">
                                <span class="user-avatar">
                                    @if ($user->avatar)
                                        <img src="{{ asset('./storage/' . $user->avatar) }}"
                                            alt="{{ $user->name ?: 'Ảnh đại diện' }}" class="user-avatar-img">
                                    @else
                                                            {{ mb_strtoupper(
                                            mb_substr($user->name ?: $user->email, 0, 1)
                                        ) }}
                                    @endif
                                </span>
                                <div>
                                    <strong>{{ $user->name ?: 'Chưa đặt tên' }}</strong>

                                    <small>
                                        <a class="user-email" href="mailto:{{ $user->email }}">
                                            {{ $user->email }}
                                        </a>

                                        @if ($user->phone)
                                            · {{ $user->phone }}
                                        @endif
                                    </small>
                                </div>
                            </div>

                        </td>
                        <td><span
                                class="user-role {{ $user->role }}">{{ $user->role === 'admin' ? 'Quản trị viên' : 'Khách hàng' }}</span>
                        </td>
                        <td><span
                                class="user-status {{ $user->status }}">{{ ['active' => 'Hoạt động', 'inactive' => 'Không hoạt động', 'blocked' => 'Đã khóa'][$user->status] ?? $user->status }}</span>
                        </td>
                        <td>{{ number_format($user->orders_count) }}</td>
                        <td><span class="user-badge {{ $user->email_verified_at ? 'bg-success' : 'bg-warning' }}">
                                {{ $user->email_verified_at ? 'Đã xác thực' : 'Chưa xác thực' }}
                            </span></td>
                        <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                        <td>@if((int) $user->id !== (int) auth()->id())
                            <div class="user-actions">
                                <form class="js-user-action" method="POST"
                                    action="{{ route('admin.users.status.update', $user) }}"
                                    data-title="{{ $user->status === 'active' ? 'Khóa tài khoản?' : 'Mở khóa tài khoản?' }}"
                                    data-message="{{ $user->status === 'active' ? 'Tài khoản sẽ không thể đăng nhập cho đến khi được mở khóa.' : 'Tài khoản sẽ có thể đăng nhập và sử dụng lại.' }}"
                                    data-submit="{{ $user->status === 'active' ? 'Xác nhận khóa' : 'Xác nhận mở khóa' }}">
                                    @csrf @method('PATCH')<input type="hidden" name="status"
                                        value="{{ $user->status === 'active' ? 'blocked' : 'active' }}"><button
                                        class="user-action {{ $user->status === 'active' ? 'lock' : 'unlock' }}" type="submit"
                                        title="{{ $user->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}"
                                        aria-label="{{ $user->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}"><i
                                            class="fa-solid {{ $user->status === 'active' ? 'fa-lock' : 'fa-lock-open' }}"></i></button>
                                </form>@if($user->role !== 'admin')
                                    <form class="js-user-action" method="POST"
                                        action="{{ route('admin.users.grant-admin', $user) }}" data-title="Cấp quyền quản trị viên?"
                                        data-message="Tài khoản này sẽ có thể truy cập khu vực quản trị PetWorld."
                                        data-submit="Xác nhận cấp quyền">@csrf @method('PATCH')<button class="user-action admin"
                                            type="submit" title="Cấp quyền admin" aria-label="Cấp quyền admin"><i
                                class="fa-solid fa-user-shield"></i></button></form>@else
                                    <form class="js-user-action" method="POST"
                                        action="{{ route('admin.users.revoke-admin', $user) }}" data-title="Thu hồi quyền quản trị viên?"
                                        data-message="Tài khoản này sẽ không còn truy cập được khu vực quản trị PetWorld."
                                        data-submit="Xác nhận thu hồi">@csrf @method('PATCH')<button class="user-action lock"
                                            type="submit" title="Thu hồi quyền admin" aria-label="Thu hồi quyền admin"><i
                                class="fa-solid fa-user-minus"></i></button></form>@endif
                        </div>@else<span style="color:var(--text-muted);font-size:.78rem">Tài khoản của bạn</span>@endif
                        </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="users-empty"><i class="fa-solid fa-user-group"
                                    style="font-size:1.4rem;color:var(--primary)"></i><br><br>Không tìm thấy người dùng phù
                                hợp
                                với bộ lọc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($users->hasPages())
                <nav class="users-pagination" aria-label="Phân trang người dùng">
                    @foreach($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                        @if($page === $users->currentPage())<span class="active">{{ $page }}</span>@else<a
                    href="{{ $url }}">{{ $page }}</a>@endif @endforeach
            </nav>@endif
        </section>
    </div>

    <div class="user-confirm-modal" id="user-confirm-modal" hidden aria-hidden="true">
        <div class="user-confirm-card" role="dialog" aria-modal="true" aria-labelledby="user-confirm-title">
            <span class="user-confirm-icon"><i class="fa-solid fa-shield-halved"></i></span>
            <h3 id="user-confirm-title">Xác nhận thao tác?</h3>
            <p id="user-confirm-message"></p>
            <form method="POST" id="user-confirm-form">
                @csrf
                @method('PATCH')
                <span id="user-confirm-payload"></span>
                <div class="user-confirm-actions">
                    <button class="user-confirm-cancel" type="button" id="user-confirm-cancel">Hủy</button>
                    <button class="user-confirm-submit" type="submit" id="user-confirm-submit">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('user-confirm-modal');
            const modalForm = document.getElementById('user-confirm-form');
            const title = document.getElementById('user-confirm-title');
            const message = document.getElementById('user-confirm-message');
            const submit = document.getElementById('user-confirm-submit');
            const payload = document.getElementById('user-confirm-payload');
            const cancel = document.getElementById('user-confirm-cancel');
            let trigger = null;

            function closeModal() {
                modal.hidden = true;
                modal.setAttribute('aria-hidden', 'true');
                trigger?.querySelector('button')?.focus();
            }

            document.querySelectorAll('.js-user-action').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    trigger = form;
                    modalForm.action = form.action;
                    title.textContent = form.dataset.title;
                    message.textContent = form.dataset.message;
                    submit.textContent = form.dataset.submit;
                    payload.innerHTML = '';
                    form.querySelectorAll('input[type="hidden"]').forEach(function (input) {
                        if (input.name !== '_token' && input.name !== '_method') {
                            const copy = document.createElement('input');
                            copy.type = 'hidden'; copy.name = input.name; copy.value = input.value;
                            payload.appendChild(copy);
                        }
                    });
                    modal.hidden = false;
                    modal.setAttribute('aria-hidden', 'false');
                    cancel.focus();
                });
            });

            cancel.addEventListener('click', closeModal);
            modal.addEventListener('click', function (event) { if (event.target === modal) closeModal(); });
        });
    </script>
@endsection
