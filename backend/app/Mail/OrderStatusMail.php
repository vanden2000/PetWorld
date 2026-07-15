<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {
    }

    public function envelope(): Envelope
    {
        $orderCode = 'PW'.str_pad(
            (string) $this->order->id,
            6,
            '0',
            STR_PAD_LEFT
        );

        $subject = match ($this->order->order_status) {
            'confirmed' => "Đơn hàng {$orderCode} đã được xác nhận",
            'cancelled' => "Đơn hàng {$orderCode} đã được hủy",
            'shipping' => "Đơn hàng {$orderCode} đang được giao",
            'completed' => "Đơn hàng {$orderCode} đã giao thành công",
            default => "Cập nhật đơn hàng {$orderCode}",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}