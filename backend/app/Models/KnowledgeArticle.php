<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeArticle extends Model
{
    /**
     * Nhóm kiến thức (value => nhãn tiếng Việt).
     * Là nguồn sự thật duy nhất cho category: validation, chatbot tool,
     * admin views và API đều tham chiếu qua array_keys(self::CATEGORIES).
     */
    public const CATEGORIES = [
        'shipping' => 'Giao hàng',
        'payment' => 'Thanh toán',
        'returns' => 'Đổi trả',
        'voucher' => 'Voucher',
        'contact' => 'Liên hệ',
        'terms' => 'Điều khoản sử dụng',
        'privacy' => 'Chính sách bảo mật',
    ];

    protected $fillable = ['title', 'slug', 'summary', 'category', 'content', 'questions', 'status', 'version', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
        'questions' => 'array',
    ];

    /** Danh sách category value (dùng cho validation / enum). */
    public static function categoryKeys(): array
    {
        return array_keys(self::CATEGORIES);
    }
}
