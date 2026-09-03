<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_CUSTOMER_PAID = 'customer_paid';
    public const PAYMENT_STATUS_RECONCILING = 'reconciling';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_DISCREPANCY = 'discrepancy';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'payment_code',
        'voucher_id',
        'shipping_voucher_id',
        'shipping_method_id',
        'shipping_method_code',
        'payment_method_id',
        'address_id',
        'user_id',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'delivery_area',
        'shipping_fee',
        'shipping_weight_grams',
        'shipping_fee_original',
        'shipping_discount',
        'discount_amount',
        'order_status',
        'total_amount',
        'payment_status',
        'expires_at',
        'note',
        'reconciliation_note',
        'reconciled_at',
        'returned_at',
        'return_reason',
        'return_proof_image',
        'refunded_at',
        'refund_bank_name',
        'refund_account_number',
        'refund_account_name',
        'refund_amount',
        'refund_proof_image',
        'refund_note',
    ];

    protected $casts = [
        'shipping_fee' => 'decimal:2',
        'shipping_weight_grams' => 'integer',
        'shipping_fee_original' => 'decimal:2',
        'shipping_discount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'reconciled_at' => 'datetime',
        'returned_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refund_amount' => 'decimal:2',
    ];

    /**
     * Số phút hiệu lực của mã QR chuyển khoản. Khớp với đồng hồ đếm ngược ở frontend.
     */
    public const PAYMENT_WINDOW_MINUTES = 15;

    /**
     * Đơn thanh toán bằng chuyển khoản (mới cần webhook SePay + hạn thanh toán).
     * Nhận diện qua tên phương thức giống hệt frontend ("chuyển khoản").
     */
    public function isBankTransfer(): bool
    {
        $name = mb_strtolower($this->paymentMethod?->name ?? '');

        return str_contains($name, 'chuyển khoản');
    }

    public function isCod(): bool
    {
        $name = mb_strtolower($this->paymentMethod?->name ?? '');

        return str_contains($name, 'cod') || str_contains($name, 'nhận hàng') || ! $this->isBankTransfer();
    }

    public function isReconciled(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isCustomerPaid(): bool
    {
        return in_array($this->payment_status, [
            self::PAYMENT_STATUS_CUSTOMER_PAID,
            self::PAYMENT_STATUS_RECONCILING,
            self::PAYMENT_STATUS_PAID,
        ], true);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function shippingVoucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'shipping_voucher_id');
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function sepayTransactions(): HasMany
    {
        return $this->hasMany(SepayTransaction::class);
    }

    /**
     * Hủy đơn và hoàn lại kho + voucher đã giữ chỗ lúc đặt hàng.
     * PHẢI gọi bên trong DB::transaction và đơn đã được lockForUpdate,
     * để tránh trừ/hoàn kho trùng khi nhiều tiến trình chạy song song.
     * Dùng chung cho hủy tay (OrderController@cancel) và tự hủy hết hạn (command).
     */
    public function restockAndMarkCancelled(): void
    {
        $items = $this->items()->get();
        $variantIds = $items->pluck('product_variant_id')->filter()->unique()->values();

        $variants = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $variants->get($item->product_variant_id)?->increment('quantity', $item->quantity);
        }

        $this->update(['order_status' => 'cancelled']);

        // Mở lại voucher về 'active' nếu trước đó bị khóa 'expired' mà nay còn lượt dùng.
        if ($this->voucher_id) {
            $voucher = Voucher::query()
                ->lockForUpdate()
                ->find($this->voucher_id);
            if ($voucher && $voucher->status === 'expired' && (int) $voucher->usage_limit > 0) {
                $usedOrders = $voucher->orders()
                    ->where('order_status', '<>', 'cancelled')
                    ->whereKeyNot($this->id)
                    ->count();
                if ($usedOrders < (int) $voucher->usage_limit) {
                    $voucher->update(['status' => 'active']);
                }
            }
        }
    }
}
