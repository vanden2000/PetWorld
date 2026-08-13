<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSupportLog;
use App\Services\PasswordResetOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(['user', 'admin'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'blocked'])],
        ]);

        $users = User::query()
            ->withCount('orders')
            ->withSum(['orders as completed_spend' => fn ($query) => $query->where('order_status', 'completed')], 'total_amount')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'stats' => [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'blocked' => User::where('status', 'blocked')->count(),
                'newThisMonth' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'blocked'])]]);

        $this->ensureCanManage($user);

        if ($user->role === 'user' && $user->status === 'blocked' && $data['status'] === 'active') {
            return back()->with('error', 'Vui lòng dùng thao tác mở khóa để ghi nhận lý do hỗ trợ.');
        }

        if ($user->role === 'admin' && $user->status === 'active' && $data['status'] !== 'active' && $this->activeAdminCountExcept($user) === 0) {
            return back()->with('error', 'Cần giữ lại ít nhất một quản trị viên đang hoạt động.');
        }

        $user->update(['status' => $data['status']]);

        return back()->with('success', $data['status'] === 'blocked' ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.');
    }

    public function resendVerification(User $user): RedirectResponse
    {
        $this->ensureCanSupport($user);

        if ($user->hasVerifiedEmail()) {
            return back()->with('error', 'Tài khoản này đã xác thực email.');
        }

        if ($user->status === 'blocked') {
            return back()->with('error', 'Không thể gửi email xác thực cho tài khoản đang bị khóa.');
        }

        $rateLimitKey = 'admin-resend-verification:' . $user->id;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return back()->with('error', 'Đã gửi email xác thực quá nhiều lần. Vui lòng thử lại sau ' . RateLimiter::availableIn($rateLimitKey) . ' giây.');
        }

        $user->sendEmailVerificationNotification();
        RateLimiter::hit($rateLimitKey, 86400);

        UserSupportLog::create([
            'user_id' => $user->id,
            'admin_id' => auth()->id(),
            'action' => UserSupportLog::VERIFICATION_RESENT,
        ]);

        return back()->with('success', 'Đã gửi lại email xác thực cho khách hàng.');
    }

    public function sendPasswordResetOtp(User $user, PasswordResetOtpService $passwordResetOtp): RedirectResponse
    {
        $this->ensureCanSupport($user);

        if (! $user->hasVerifiedEmail()) {
            return back()->with('error', 'Khách hàng cần xác thực email trước khi đặt lại mật khẩu.');
        }

        if ($user->status !== 'active') {
            return back()->with('error', 'Không thể gửi mã đặt lại mật khẩu cho tài khoản không hoạt động.');
        }

        $rateLimitKey = 'admin-send-password-reset-otp:' . $user->id;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return back()->with('error', 'Đã gửi mã đặt lại mật khẩu quá nhiều lần. Vui lòng thử lại sau ' . RateLimiter::availableIn($rateLimitKey) . ' giây.');
        }

        $passwordResetOtp->send($user);
        RateLimiter::hit($rateLimitKey, 86400);

        UserSupportLog::create([
            'user_id' => $user->id,
            'admin_id' => auth()->id(),
            'action' => UserSupportLog::PASSWORD_RESET_SENT,
        ]);

        return back()->with('success', 'Đã gửi mã OTP đặt lại mật khẩu cho khách hàng.');
    }

    public function unblock(Request $request, User $user): RedirectResponse
    {
        $this->ensureCanSupport($user);

        if ($user->status !== 'blocked') {
            return back()->with('error', 'Chỉ có thể mở khóa tài khoản đang bị khóa.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $user->update(['status' => 'active']);

        UserSupportLog::create([
            'user_id' => $user->id,
            'admin_id' => auth()->id(),
            'action' => UserSupportLog::ACCOUNT_UNBLOCKED,
            'reason' => $data['reason'],
        ]);

        return back()->with('success', 'Đã mở khóa tài khoản khách hàng.');
    }

    public function revokeSessions(Request $request, User $user): RedirectResponse
    {
        $this->ensureCanSupport($user);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $user->tokens()->delete();

        UserSupportLog::create([
            'user_id' => $user->id,
            'admin_id' => auth()->id(),
            'action' => UserSupportLog::SESSIONS_REVOKED,
            'reason' => $data['reason'],
        ]);

        return back()->with('success', 'Đã đăng xuất khách hàng khỏi tất cả thiết bị.');
    }

    public function grantAdmin(Request $request, User $user): RedirectResponse
    {
        $this->ensureCanManage($user);

        if ($user->role === 'admin') {
            return back()->with('error', 'Tài khoản này đã có quyền quản trị viên.');
        }

        $user->update(['role' => 'admin']);

        return back()->with('success', 'Đã cấp quyền quản trị viên cho tài khoản.');
    }

    public function revokeAdmin(User $user): RedirectResponse
    {
        $this->ensureCanManage($user);

        if ($user->role !== 'admin') {
            return back()->with('error', 'Tài khoản này không có quyền quản trị viên.');
        }

        if ($user->status === 'active' && $this->activeAdminCountExcept($user) === 0) {
            return back()->with('error', 'Cần giữ lại ít nhất một quản trị viên đang hoạt động.');
        }

        $user->update(['role' => 'user']);

        return back()->with('success', 'Đã thu hồi quyền quản trị viên của tài khoản.');
    }

    private function ensureCanManage(User $user): void
    {
        if ((int) $user->id === (int) auth()->id()) {
            abort(403, 'Bạn không thể thay đổi trạng thái hoặc quyền của chính mình tại đây.');
        }
    }

    private function ensureCanSupport(User $user): void
    {
        $this->ensureCanManage($user);

        if ($user->role !== 'user') {
            abort(403, 'Chỉ có thể hỗ trợ tài khoản khách hàng.');
        }
    }

    private function activeAdminCountExcept(User $user): int
    {
        return User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->whereKeyNot($user->id)
            ->count();
    }
}
