<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderStatusUpdatedNotification;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('order_status')) {
            $user = $order->user;
            if ($user) {
                $user->notify(new OrderStatusUpdatedNotification($order));
            }
        }
    }
}
