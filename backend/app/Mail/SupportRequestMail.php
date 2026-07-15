<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Dữ liệu form hỗ trợ: name, email, order_code, type, priority, message.
     */
    public array $support;

    public function __construct(array $support)
    {
        $this->support = $support;
    }

    public function envelope()
    {
        $type = $this->support['type'] ?? 'Yêu cầu hỗ trợ';
        $name = $this->support['name'] ?? '';
        $contact = $this->support['email'] ?? '';

        // Chỉ gán Reply-To khi khách nhập email hợp lệ (nếu là SĐT thì bỏ qua).
        $replyTo = [];
        if (filter_var($contact, FILTER_VALIDATE_EMAIL) !== false) {
            $replyTo[] = new Address($contact, $name);
        }

        return new Envelope(
            subject: '[Hỗ trợ PetWorld] ' . $type . ' - ' . $name,
            replyTo: $replyTo,
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.support-request',
            with: ['support' => $this->support],
        );
    }

    public function attachments()
    {
        return [];
    }
}
