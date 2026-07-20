<?php

namespace App\Observers;

use App\Models\Voucher;
use App\Models\User;
use App\Notifications\NewVoucherNotification;

class VoucherObserver
{
    /**
     * Handle the Voucher "created" event.
     */
    public function created(Voucher $voucher): void
    {
        if ($voucher->status === 'active') {
            $this->notifyAllUsers($voucher);
        }
    }

    /**
     * Handle the Voucher "updated" event.
     */
    public function updated(Voucher $voucher): void
    {
        if ($voucher->wasChanged('status') && $voucher->status === 'active') {
            $this->notifyAllUsers($voucher);
        }
    }

    /**
     * Gửi thông báo cho tất cả khách hàng
     */
    protected function notifyAllUsers(Voucher $voucher): void
    {
        $users = User::query()
            ->where('role', 'user')
            ->where('status', 'active')
            ->get();

        foreach ($users as $user) {
            $user->notify(new NewVoucherNotification($voucher));
        }
    }
}
