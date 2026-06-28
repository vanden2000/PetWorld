<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được đăng ký.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'role' => 'user',
            'status' => 'active',
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => $this->formatUser($user),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! $this->passwordMatches($credentials['password'], $user)) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Tài khoản của bạn đã bị khoá hoặc chưa kích hoạt.'],
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => $this->formatUser($user),
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // Xoá đúng token đang dùng để đăng xuất khỏi thiết bị hiện tại.
        $request->user()->currentAccessToken()->delete();

        return response()->json(['data' => ['message' => 'Đã đăng xuất.']]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => ['user' => $this->formatUser($request->user())]]);
    }

    /**
     * Xác thực mật khẩu, hỗ trợ cả dữ liệu cũ.
     *
     * Hệ thống PHP cũ lưu mật khẩu bằng md5(); user mới dùng bcrypt (Laravel).
     * Khi user cũ đăng nhập đúng, tự nâng cấp mật khẩu sang bcrypt để lần sau
     * đi theo chuẩn mới (cast 'hashed' sẽ tự băm khi gán).
     */
    private function passwordMatches(string $plain, User $user): bool
    {
        $stored = $user->password;

        // Hash bcrypt chuẩn của Laravel ($2y$/$2a$/$2b$).
        if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$2b$')) {
            return Hash::check($plain, $stored);
        }

        // Dữ liệu cũ: md5 không salt — xác thực rồi nâng cấp.
        if (hash_equals($stored, md5($plain))) {
            $user->password = $plain;
            $user->save();

            return true;
        }

        return false;
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'role' => $user->role,
        ];
    }
}
