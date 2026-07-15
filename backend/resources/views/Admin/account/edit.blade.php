@extends('admin.layouts.app')

@php
    $avatarUrl = $admin->avatar
        ? (str_contains($admin->avatar, '://') ? $admin->avatar : asset('storage/' . $admin->avatar))
        : asset('image/logo/logo.png');
@endphp

@section('title', 'Quản lý tài khoản admin')

@section('styles')
<style>
    .account-page { display: grid; gap: 22px; max-width: 1180px; }
    .account-hero { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px; border: 1px solid var(--border-color); border-radius: 14px; background: #fff; box-shadow: var(--shadow-subtle); }
    .account-identity { display: flex; align-items: center; gap: 16px; min-width: 0; }
    .account-identity img { width: 76px; height: 76px; flex: 0 0 auto; border: 4px solid var(--primary-light); border-radius: 50%; object-fit: cover; }
    .account-identity h1 { margin: 0; color: var(--text-main); font-size: 1.45rem; letter-spacing: -.4px; }
    .account-identity p { margin: 5px 0 0; overflow: hidden; color: var(--text-muted); text-overflow: ellipsis; white-space: nowrap; }
    .account-badges { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 10px; }
    .account-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 9px; border-radius: 999px; font-size: .72rem; font-weight: 800; }
    .account-badge.admin { color: #7c3aed; background: #f3e8ff; } .account-badge.active { color: #15803d; background: #dcfce7; }
    .account-hero-note { padding: 10px 13px; border-radius: 9px; color: #9a3412; background: #fff7ed; font-size: .79rem; line-height: 1.4; }
    .account-layout { display: grid; grid-template-columns: minmax(0, 1.45fr) minmax(300px, .8fr); gap: 20px; align-items: start; }
    .account-card { padding: 22px; border: 1px solid var(--border-color); border-radius: 12px; background: #fff; box-shadow: var(--shadow-subtle); }
    .account-card + .account-card { margin-top: 20px; }
    .account-card-header { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 20px; }
    .account-card-header i { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 9px; color: var(--primary); background: var(--primary-light); }
    .account-card-header h2 { margin: 0; color: var(--text-main); font-size: 1rem; }
    .account-card-header p { margin: 3px 0 0; color: var(--text-muted); font-size: .82rem; line-height: 1.45; }
    .account-fields { display: grid; gap: 15px; }
    .account-field label { display: block; margin-bottom: 7px; color: var(--text-muted); font-size: .73rem; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; }
    .account-input, .account-readonly { width: 100%; height: 43px; padding: 0 12px; border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-main); background: #fff; outline: none; }
    .account-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(255, 120, 45, .12); }
    .account-readonly { display: flex; align-items: center; color: var(--text-muted); background: var(--bg-color); }
    .account-avatar-editor { display: flex; align-items: center; gap: 13px; padding: 12px; border: 1px dashed #f4b892; border-radius: 10px; background: #fffaf6; }
    .account-avatar-editor img { width: 52px; height: 52px; border-radius: 50%; object-fit: cover; }
    .account-avatar-editor strong, .account-avatar-editor small { display: block; } .account-avatar-editor small { margin-top: 3px; color: var(--text-muted); }
    .account-avatar-editor input { max-width: 210px; color: var(--text-muted); font-size: .78rem; }
    .account-submit { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 43px; margin-top: 20px; padding: 0 16px; border: 0; border-radius: 8px; color: #fff; background: var(--primary); font: inherit; font-weight: 800; cursor: pointer; transition: var(--transition); }
    .account-submit:hover { background: var(--primary-hover); transform: translateY(-1px); }
    .password-input { position: relative; } .password-input input { padding-right: 45px; }
    .password-toggle { position: absolute; top: 50%; right: 8px; width: 32px; height: 32px; border: 0; border-radius: 7px; color: var(--text-muted); background: transparent; cursor: pointer; transform: translateY(-50%); }
    .password-toggle:hover { color: var(--primary); background: var(--primary-light); }
    .password-hint { display: flex; gap: 8px; margin-top: 12px; padding: 11px; border-radius: 8px; color: #9a3412; background: #fff7ed; font-size: .78rem; line-height: 1.45; }
    .security-list { display: grid; gap: 0; }
    .security-item { display: flex; justify-content: space-between; gap: 15px; padding: 13px 0; border-bottom: 1px solid var(--border-color); }
    .security-item:last-child { border-bottom: 0; }
    .security-item span { color: var(--text-muted); font-size: .8rem; } .security-item strong { color: var(--text-main); font-size: .82rem; text-align: right; }
    @media (max-width: 900px) { .account-layout { grid-template-columns: 1fr; } .account-card + .account-card { margin-top: 0; } }
    @media (max-width: 600px) { .account-hero { align-items: flex-start; flex-direction: column; } .account-hero-note { width: 100%; } .account-avatar-editor { align-items: flex-start; flex-direction: column; } }
</style>
@endsection

@section('content')
<div class="account-page">
    @if(session('success'))<div class="alert-panel alert-success-box"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert-panel alert-error-box"><i class="fa-solid fa-triangle-exclamation"></i><span>{{ $errors->first() }}</span></div>@endif

    <header class="account-hero">
        <div class="account-identity">
            <img id="hero-avatar" src="{{ $avatarUrl }}" alt="{{ $admin->name }}">
            <div><h1>{{ $admin->name }}</h1><p>{{ $admin->email }}</p><div class="account-badges"><span class="account-badge admin"><i class="fa-solid fa-shield-halved"></i> Quản trị viên</span><span class="account-badge active"><i class="fa-solid fa-circle-check"></i> Đang hoạt động</span></div></div>
        </div>
        <div class="account-hero-note"><i class="fa-solid fa-lock"></i> Thông tin và bảo mật tài khoản được cập nhật độc lập.</div>
    </header>

    <div class="account-layout">
        <form class="account-card" method="POST" action="{{ route('admin.account.profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="account-card-header"><i class="fa-solid fa-user-gear"></i><div><h2>Hồ sơ cá nhân</h2><p>Thông tin hiển thị của bạn trong khu vực quản trị.</p></div></div>
            <div class="account-fields">
                <div class="account-field"><label for="name">Tên hiển thị</label><input id="name" name="name" class="account-input" value="{{ old('name', $admin->name) }}" required></div>
                <div class="account-field"><label>Email đăng nhập</label><div class="account-readonly">{{ $admin->email }}</div></div>
                <div class="account-field"><label for="phone">Số điện thoại</label><input id="phone" name="phone" class="account-input" value="{{ old('phone', $admin->phone) }}" placeholder="Chưa cập nhật"></div>
                <div class="account-field"><label for="avatar">Ảnh đại diện</label><div class="account-avatar-editor"><img id="avatar-preview" src="{{ $avatarUrl }}" alt="Xem trước ảnh đại diện"><div><strong>Thay ảnh đại diện</strong><small>JPG, PNG, GIF hoặc WEBP, tối đa 2 MB.</small></div><input id="avatar" name="avatar" type="file" accept="image/*"></div></div>
            </div>
            <button class="account-submit" type="submit"><i class="fa-solid fa-floppy-disk"></i> Lưu thông tin</button>
        </form>

        <aside>
            <section class="account-card">
                <div class="account-card-header"><i class="fa-solid fa-shield-heart"></i><div><h2>Trạng thái bảo mật</h2><p>Tóm tắt quyền truy cập hiện tại.</p></div></div>
                <div class="security-list"><div class="security-item"><span>Vai trò</span><strong>Quản trị viên</strong></div><div class="security-item"><span>Trạng thái</span><strong>Đang hoạt động</strong></div><div class="security-item"><span>Email</span><strong>{{ $admin->email_verified_at ? 'Đã xác minh' : 'Chưa xác minh' }}</strong></div><div class="security-item"><span>Tham gia</span><strong>{{ $admin->created_at?->format('d/m/Y') }}</strong></div></div>
            </section>
            <form class="account-card" method="POST" action="{{ route('admin.account.password.update') }}">
                @csrf @method('PUT')
                <div class="account-card-header"><i class="fa-solid fa-key"></i><div><h2>Đổi mật khẩu</h2><p>Dùng mật khẩu mạnh để bảo vệ quyền quản trị.</p></div></div>
                <div class="account-fields">
                    <div class="account-field"><label for="current_password">Mật khẩu hiện tại</label><div class="password-input"><input id="current_password" name="current_password" type="password" class="account-input" required><button type="button" class="password-toggle" data-target="current_password" aria-label="Hiện mật khẩu"><i class="fa-regular fa-eye"></i></button></div></div>
                    <div class="account-field"><label for="password">Mật khẩu mới</label><div class="password-input"><input id="password" name="password" type="password" class="account-input" minlength="6" required><button type="button" class="password-toggle" data-target="password" aria-label="Hiện mật khẩu"><i class="fa-regular fa-eye"></i></button></div></div>
                    <div class="account-field"><label for="password_confirmation">Xác nhận mật khẩu mới</label><div class="password-input"><input id="password_confirmation" name="password_confirmation" type="password" class="account-input" minlength="6" required><button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Hiện mật khẩu"><i class="fa-regular fa-eye"></i></button></div></div>
                </div>
                <div class="password-hint"><i class="fa-solid fa-circle-info"></i><span>Mật khẩu mới cần ít nhất 6 ký tự và khác mật khẩu hiện tại.</span></div>
                <button class="account-submit" type="submit"><i class="fa-solid fa-key"></i> Cập nhật mật khẩu</button>
            </form>
        </aside>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('avatar')?.addEventListener('change', function () {
        const file = this.files?.[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        document.getElementById('avatar-preview').src = url;
        document.getElementById('hero-avatar').src = url;
    });
    document.querySelectorAll('.password-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.dataset.target);
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.setAttribute('aria-label', visible ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
            button.querySelector('i').className = visible ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
        });
    });
</script>
@endsection
