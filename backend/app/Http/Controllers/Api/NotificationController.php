<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo của người dùng (phân trang).
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->paginate(10);

        return response()->json([
            'data' => [
                'notifications' => collect($notifications->items())->map(fn ($n) => [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? '',
                    'message' => $n->data['message'] ?? '',
                    'action_url' => $n->data['action_url'] ?? '',
                    'icon' => $n->data['icon'] ?? 'info',
                    'type' => $n->data['type'] ?? 'general',
                    'read_at' => $n->read_at ? $n->read_at->toIso8601String() : null,
                    'created_at' => $n->created_at ? $n->created_at->toIso8601String() : null,
                ]),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'from' => $notifications->firstItem(),
                    'to' => $notifications->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Lấy số lượng thông báo chưa đọc.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json([
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Đánh dấu một thông báo là đã đọc.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu thông báo là đã đọc.'
        ]);
    }

    /**
     * Đánh dấu toàn bộ thông báo là đã đọc.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu toàn bộ thông báo là đã đọc.'
        ]);
    }
}
