@extends('admin.layouts.app')

@php
    $avatarUrl = $admin->avatar
        ? (str_contains($admin->avatar, '://') ? $admin->avatar : asset('storage/' . $admin->avatar))
        : asset('image/logo/logo.png');
@endphp

@section('title', 'Quản lý tài khoản admin')

@section('content')
    @if(session('success'))
        <div class="alert-panel alert-success-box">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-panel alert-error-box">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="dashboard-header">
        <div class="header-title-block">
            <h1>Quản lý tài khoản admin</h1>
            <p>Cập nhật ảnh đại diện, thông tin hiển thị và mật khẩu đăng nhập.</p>
        </div>
    </div>

    <div class="admin-account-grid">
        <form class="admin-account-card" method="POST" action="{{ route('admin.account.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="admin-account-avatar-block">
                <img src="{{ $avatarUrl }}" alt="{{ $admin->name }}" class="admin-account-avatar">
                <div>
                    <h3>Thông tin admin</h3>
                    <p>{{ $admin->email }}</p>
                </div>
            </div>

            <label class="filter-label" for="name">Tên hiển thị</label>
            <input id="name" name="name" class="filter-input account-input" value="{{ old('name', $admin->name) }}" required>

            <label class="filter-label" for="phone">Số điện thoại</label>
            <input id="phone" name="phone" class="filter-input account-input" value="{{ old('phone', $admin->phone) }}">

            <label class="filter-label" for="avatar">Ảnh đại diện</label>
            <input id="avatar" name="avatar" type="file" class="filter-input account-input" accept="image/*">

            <button class="btn-dark-slate account-submit-btn" type="submit">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Lưu thông tin</span>
            </button>
        </form>

        <form class="admin-account-card" method="POST" action="{{ route('admin.account.password.update') }}">
            @csrf
            @method('PUT')

            <h3>Thay đổi mật khẩu</h3>
            <p class="account-card-subtitle">Mật khẩu mới nên có ít nhất 6 ký tự.</p>

            <label class="filter-label" for="current_password">Mật khẩu hiện tại</label>
            <input id="current_password" name="current_password" type="password" class="filter-input account-input" required>

            <label class="filter-label" for="password">Mật khẩu mới</label>
            <input id="password" name="password" type="password" class="filter-input account-input" required>

            <label class="filter-label" for="password_confirmation">Xác nhận mật khẩu mới</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="filter-input account-input" required>

            <button class="btn-dark-slate account-submit-btn" type="submit">
                <i class="fa-solid fa-key"></i>
                <span>Đổi mật khẩu</span>
            </button>
        </form>
    </div>
@endsection
