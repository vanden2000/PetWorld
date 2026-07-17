<?php

namespace App\Notifications;

use App\Models\Voucher;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewVoucherNotification extends Notification
{
    use Queueable;

    protected Voucher $voucher;

    public function __construct(Voucher $voucher)
    {
        $this->voucher = $voucher;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $discountFormatted = number_format((float) $this->voucher->discount_value, 0, ',', '.') . 'đ';
        $minOrderFormatted = number_format((float) $this->voucher->min_order_value, 0, ',', '.') . 'đ';
        
        $expiryText = $this->voucher->end_date 
            ? 'Hạn dùng đến ' . $this->voucher->end_date->format('d/m/Y') 
            : 'không giới hạn thời gian';

        return [
            'title' => 'Mã giảm giá mới: ' . $this->voucher->code,
            'message' => 'Ưu đãi cực hot! Giảm ngay ' . $discountFormatted . ' cho đơn hàng từ ' . $minOrderFormatted . '. ' . $expiryText . '.',
            'action_url' => '/shop',
            'icon' => 'gift',
            'type' => 'new_voucher',
            'voucher_id' => $this->voucher->id,
        ];
    }
}
