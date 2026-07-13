<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.account.edit', [
            'admin' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ], [
            'name.required' => 'Vui lòng nhập tên admin.',
            'avatar.image' => 'Ảnh đại diện không hợp lệ.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        if ($request->hasFile('avatar')) {
            if ($admin->avatar && ! str_contains($admin->avatar, '://')) {
                Storage::disk('public')->delete($admin->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $admin->update($data);

        return back()->with('success', 'Đã cập nhật tài khoản admin.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        if (! Hash::check($data['current_password'], $admin->password)) {
            return back()
                ->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.'])
                ->withInput();
        }

        if (Hash::check($data['password'], $admin->password)) {
            return back()
                ->withErrors(['password' => 'Mật khẩu mới cần khác mật khẩu hiện tại.'])
                ->withInput();
        }

        $admin->update([
            'password' => $data['password'],
        ]);

        return back()->with('success', 'Đã thay đổi mật khẩu admin.');
    }
}
