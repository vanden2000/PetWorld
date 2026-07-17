<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification
{
    use Queueable;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabels = [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao hàng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        $statusIcons = [
            'pending' => 'clock',
            'confirmed' => 'check-circle',
            'shipping' => 'truck',
            'completed' => 'smile',
            'cancelled' => 'x-circle',
        ];

        $status = $this->order->order_status;
        $statusLabel = $statusLabels[$status] ?? $status;
        $icon = $statusIcons[$status] ?? 'info';
        $orderCode = 'PW' . str_pad((string) $this->order->id, 6, '0', STR_PAD_LEFT);

        return [
            'title' => 'Cập nhật đơn hàng ' . $orderCode,
            'message' => 'Đơn hàng của bạn đã chuyển sang trạng thái: ' . $statusLabel . '.',
            'action_url' => '/account/orders/' . $this->order->id,
            'icon' => $icon,
            'type' => 'order_status_updated',
            'order_id' => $this->order->id,
        ];
    }
}
