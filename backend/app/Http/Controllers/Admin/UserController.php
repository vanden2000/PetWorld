<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        if ($user->role === 'admin' && $user->status === 'active' && $data['status'] !== 'active' && $this->activeAdminCountExcept($user) === 0) {
            return back()->with('error', 'Cần giữ lại ít nhất một quản trị viên đang hoạt động.');
        }

        $user->update(['status' => $data['status']]);

        return back()->with('success', $data['status'] === 'blocked' ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.');
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

    private function activeAdminCountExcept(User $user): int
    {
        return User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->whereKeyNot($user->id)
            ->count();
    }
}
