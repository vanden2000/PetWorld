<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordOtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Bước 1: Yêu cầu quên mật khẩu (Gửi mã OTP về email)
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
        ]);

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Không tiết lộ email có tồn tại hay không vì mục đích bảo mật
            // Nhưng với hệ thống thông thường có thể báo lỗi:
            throw ValidationException::withMessages([
                'email' => ['Email này không tồn tại trong hệ thống.'],
            ]);
        }

        // Tạo mã OTP ngẫu nhiên 6 chữ số
        $otp = sprintf("%06d", random_int(100000, 999999));

        // Lưu vào bảng password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => Carbon::now()
            ]
        );

        // Gửi email
        Mail::to($email)->send(new ForgotPasswordOtpMail($otp, $user->name));

        return response()->json([
            'data' => [
                'message' => 'Mã xác nhận (OTP) đã được gửi đến email của bạn.',
            ]
        ]);
    }

    /**
     * Bước 2: Xác minh mã OTP
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'otp.required' => 'Vui lòng nhập mã OTP.',
            'otp.size' => 'Mã OTP phải có 6 chữ số.',
        ]);

        $email = $request->email;
        $otp = $request->otp;

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || !Hash::check($otp, $record->token)) {
            throw ValidationException::withMessages([
                'otp' => ['Mã OTP không hợp lệ hoặc không đúng.'],
            ]);
        }

        // Kiểm tra thời hạn 10 phút
        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            throw ValidationException::withMessages([
                'otp' => ['Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.'],
            ]);
        }

        // OTP đúng, tạo một reset_token dài thay thế OTP để tiến hành bước 3
        $resetToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($resetToken),
                'created_at' => Carbon::now()
            ]
        );

        return response()->json([
            'data' => [
                'message' => 'Xác minh OTP thành công.',
                'reset_token' => $resetToken,
            ]
        ]);
    }

    /**
     * Bước 3: Đặt lại mật khẩu mới
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'reset_token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'reset_token.required' => 'Thiếu mã xác thực (reset_token).',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $email = $request->email;
        $resetToken = $request->reset_token;

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || !Hash::check($resetToken, $record->token)) {
            throw ValidationException::withMessages([
                'reset_token' => ['Yêu cầu đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.'],
            ]);
        }

        // Thời hạn cho bước đổi pass tính từ lúc xác minh OTP là 30 phút
        if (Carbon::parse($record->created_at)->addMinutes(30)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            throw ValidationException::withMessages([
                'reset_token' => ['Phiên làm việc đã hết hạn. Vui lòng thực hiện lại từ đầu.'],
            ]);
        }

        // Cập nhật mật khẩu
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Không tìm thấy người dùng này.'],
            ]);
        }

        $user->password = $request->password;
        $user->save();

        // Xoá tất cả phiên đăng nhập (token) cũ
        $user->tokens()->delete();

        // Xoá token khỏi bảng password_reset_tokens
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json([
            'data' => [
                'message' => 'Mật khẩu của bạn đã được đặt lại thành công. Bạn có thể đăng nhập ngay bây giờ.',
            ]
        ]);
    }
}
