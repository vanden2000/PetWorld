<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SupportRequestMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Lấy hòm thư nhận yêu cầu hỗ trợ từ cấu hình env.
     */
    private function getSupportInbox(): string
    {
        return env('ADMIN_EMAIL', env('MAIL_FROM_ADDRESS', 'thegioipetworld@gmail.com'));
    }

    /**
     * Nhận form hỗ trợ từ trang Liên hệ và gửi về email hỗ trợ.
     * Chỉ gửi email, không lưu vào database.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            // Chấp nhận email hoặc số điện thoại Việt Nam.
            'email' => ['required', 'string', 'max:180', function ($attribute, $value, $fail) {
                $isEmail = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                $phone = preg_replace('/[\s.\-()]/', '', $value);
                $isPhone = preg_match('/^(?:\+?84|0)\d{8,10}$/', $phone) === 1;
                if (!$isEmail && !$isPhone) {
                    $fail('Vui lòng nhập email hoặc số điện thoại hợp lệ.');
                }
            }],
            'order_code' => ['nullable', 'string', 'max:60'],
            'type' => ['nullable', 'string', 'max:80'],
            'priority' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email hoặc số điện thoại.',
            'message.required' => 'Vui lòng mô tả vấn đề bạn gặp phải.',
        ]);

        Mail::to($this->getSupportInbox())->send(new SupportRequestMail($data));

        return response()->json([
            'data' => [
                'message' => 'Đã gửi yêu cầu hỗ trợ. PetWorld sẽ phản hồi bạn trong thời gian sớm nhất!',
            ],
        ]);
    }
}
