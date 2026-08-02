<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Models\Address;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Services\SepayPaymentReconciler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;
use App\Mail\OrderStatusMail;

class OrderController extends Controller
{
    private const HISTORY_PER_PAGE = 3;

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:completed,processing,cancelled'],
            'search' => ['nullable', 'string', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $orders = $request->user()->orders()
            ->withCount('items')
            ->when(($data['status'] ?? null) === 'completed', fn($query) => $query->where('order_status', 'completed'))
            ->when(($data['status'] ?? null) === 'processing', fn($query) => $query->whereIn('order_status', ['pending', 'confirmed', 'shipping']))
            ->when(($data['status'] ?? null) === 'cancelled', fn($query) => $query->where('order_status', 'cancelled'))
            ->when($data['search'] ?? null, function ($query, string $search): void {
                $numericId = preg_replace('/\D/', '', $search);
                $query->where(function ($nested) use ($search, $numericId): void {
                    if ($numericId !== '') {
                        $nested->where('id', (int) $numericId);
                    }
                    $nested->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhere('recipient_phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(self::HISTORY_PER_PAGE);

        return response()->json([
            'data' => [
                'orders' => collect($orders->items())->map(fn($order) => [
                    'id' => $order->id,
                    'code' => 'PW' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                    'created_at' => $order->created_at?->toIso8601String(),
                    'status' => $order->order_status,
                    'payment_status' => $order->payment_status,
                    'total_amount' => (float) $order->total_amount,
                    'items_count' => $order->items_count,
                ])->values(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ],
            ]
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        $order->load([
            'shippingMethod:id,name',
            'paymentMethod:id,name',
            'items.productVariant.product.primaryImage',
            'items.productVariant.variantValues.variantType',
            'items.review:id,order_item_id,rating,comment',
        ]);

        $subtotal = $order->items->sum(fn($item) => (float) $item->price * $item->quantity);

        return response()->json([
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'code' => 'PW' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                    'payment_code' => $order->payment_code,
                    'status' => $order->order_status,
                    'payment_status' => $order->payment_status,
                    'expires_at' => $order->expires_at?->toIso8601String(),
                    'created_at' => $order->created_at?->toIso8601String(),
                    'updated_at' => $order->updated_at?->toIso8601String(),
                    'recipient' => [
                        'name' => $order->recipient_name,
                        'phone' => $order->recipient_phone,
                        'address' => $order->recipient_address,
                    ],
                    'shipping' => [
                        'method' => $order->shippingMethod?->name,
                        'fee' => (float) $order->shipping_fee,
                        'tracking_code' => 'PW-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                    ],
                    'payment' => [
                        'method' => $order->paymentMethod?->name,
                        'subtotal' => $subtotal,
                        'discount' => (float) $order->discount_amount,
                        'total' => (float) $order->total_amount,
                    ],
                    'note' => $order->note,
                    'items' => $order->items->map(fn($item) => [
                        'id' => $item->id,
                        'name' => $item->product_name,
                        'variant' => $item->productVariant?->display_name,
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'slug' => $item->productVariant?->product?->slug,
                        'image' => $item->productVariant?->product?->primaryImage?->image_url,
                        'review' => $item->review ? [
                            'id' => $item->review->id,
                            'rating' => $item->review->rating,
                            'comment' => $item->review->comment,
                        ] : null,
                    ])->values(),
                ]
            ]
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        $cancelledOrder = DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->order_status !== 'pending') {
                abort(409, 'Chỉ có thể hủy đơn hàng đang chờ xác nhận.');
            }

            if ($lockedOrder->payment_status === 'paid') {
                abort(409, 'Đơn hàng đã thanh toán không thể tự hủy. Vui lòng liên hệ PetWorld.');
            }

            // Hoàn kho + mở lại voucher rồi đánh dấu hủy (logic dùng chung với command tự hủy hết hạn).
            $lockedOrder->restockAndMarkCancelled();

            return $lockedOrder->refresh();
        });
        // lấy user sở hữu đơn hàng
        $cancelledOrder->load('user');

        // Gửi sau khi đã trả response: SMTP mất ~4s, để trong request thì khách
        // bấm hủy phải ngồi chờ (và server dev đơn tiến trình bị chặn theo).
        dispatch(function () use ($cancelledOrder): void {
            try {
                Mail::to($cancelledOrder->user->email)
                    ->send(new OrderStatusMail($cancelledOrder));
            } catch (Throwable $exception) {
                report($exception);
            }
        })->afterResponse();

        return response()->json([
            'message' => 'Đã hủy đơn hàng thành công.',
            'data' => [
                'order' => [
                    'id' => $cancelledOrder->id,
                    'status' => $cancelledOrder->order_status,
                    'updated_at' => $cancelledOrder->updated_at?->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * Tạo lại mã QR: gia hạn hiệu lực thanh toán cho đơn chuyển khoản còn đang chờ.
     * Giữ nguyên mã PW{id} (khách vẫn chuyển đúng nội dung cũ), chỉ đẩy lại expires_at.
     * Đơn đã bị hủy (hết hạn) không mở lại được — kho đã hoàn, khách cần đặt đơn mới.
     */
    public function renewPayment(Request $request, Order $order): JsonResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        $renewed = DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->payment_status === 'paid') {
                abort(409, 'Đơn hàng đã được thanh toán.');
            }

            if ($lockedOrder->order_status !== 'pending') {
                abort(409, 'Đơn hàng đã hết hạn thanh toán. Vui lòng đặt lại đơn mới.');
            }

            $lockedOrder->update([
                'expires_at' => now()->addMinutes(Order::PAYMENT_WINDOW_MINUTES),
            ]);

            return $lockedOrder->refresh();
        });

        return response()->json([
            'message' => 'Đã gia hạn thời gian thanh toán.',
            'data' => [
                'order' => [
                    'id' => $renewed->id,
                    'payment_code' => $renewed->payment_code,
                    'payment_status' => $renewed->payment_status,
                    'status' => $renewed->order_status,
                    'expires_at' => $renewed->expires_at?->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * Đọc nhanh trạng thái thanh toán từ DB, KHÔNG gọi API SePay.
     *
     * Dùng cho vòng poll dày của màn chờ thanh toán: webhook SePay mới là đường
     * xác nhận chính, còn đối soát qua API (checkSepayPayment) chỉ chạy thưa để
     * vá trường hợp webhook không tới. Endpoint này chỉ đọc, không ghi.
     */
    public function paymentStatus(Request $request, Order $order): JsonResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        return response()->json([
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'payment_code' => $order->payment_code,
                    'payment_status' => $order->payment_status,
                    'status' => $order->order_status,
                    'expires_at' => $order->expires_at?->toIso8601String(),
                    'updated_at' => $order->updated_at?->toIso8601String(),
                ],
            ],
        ]);
    }

    public function checkSepayPayment(Request $request, Order $order, SepayPaymentReconciler $reconciler): JsonResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        try {
            $checkedOrder = $reconciler->reconcile($order, requireConfigured: true);
        } catch (RuntimeException $exception) {
            abort($reconciler->hasApiCredentials() ? 502 : 422, $exception->getMessage());
        }

        return response()->json([
            'data' => [
                'order' => [
                    'id' => $checkedOrder->id,
                    'payment_code' => $checkedOrder->payment_code,
                    'payment_status' => $checkedOrder->payment_status,
                    'status' => $checkedOrder->order_status,
                    'expires_at' => $checkedOrder->expires_at?->toIso8601String(),
                    'updated_at' => $checkedOrder->updated_at?->toIso8601String(),
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
            'voucher_id' => ['nullable', 'integer', 'exists:vouchers,id'],
        ], [
            'address_id.required' => 'Vui lòng chọn địa chỉ giao hàng.',
            'shipping_method_id.required' => 'Vui lòng chọn phương thức vận chuyển.',
            'payment_method_id.required' => 'Vui lòng chọn phương thức thanh toán.',
            'items.required' => 'Giỏ hàng đang trống.',
        ]);

        // Địa chỉ phải thuộc về user và còn hiệu lực.
        $address = $request->user()->addresses()
            ->where('status', 'active')
            ->findOrFail($data['address_id']);

        $shippingMethod = ShippingMethod::query()
            ->where('status', 'active')
            ->findOrFail($data['shipping_method_id']);

        // Đơn chuyển khoản cần hạn thanh toán (quá hạn sẽ bị command tự hủy + hoàn kho).
        $paymentMethod = PaymentMethod::findOrFail($data['payment_method_id']);
        $isBankTransfer = str_contains(mb_strtolower($paymentMethod->name), 'chuyển khoản');

        $order = DB::transaction(function () use ($request, $data, $address, $shippingMethod, $isBankTransfer): Order {
            $quantities = [];
            foreach ($data['items'] as $item) {
                // Gộp số lượng nếu client gửi trùng variant.
                $quantities[$item['variant_id']] = ($quantities[$item['variant_id']] ?? 0) + $item['quantity'];
            }

            // Khoá biến thể để vừa kiểm tra vừa trừ kho an toàn khi nhiều đơn cùng lúc.
            $variants = ProductVariant::query()
                ->with('product')
                ->where('status', 'active')
                ->whereIn('id', array_keys($quantities))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $orderItems = [];

            foreach ($quantities as $variantId => $quantity) {
                $variant = $variants->get($variantId);

                if ($variant === null) {
                    throw ValidationException::withMessages([
                        'items' => ['Một sản phẩm trong giỏ không còn được bán.'],
                    ]);
                }

                if ($variant->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Sản phẩm \"{$variant->product->name}\" chỉ còn {$variant->quantity} trong kho."],
                    ]);
                }

                // Giá luôn lấy lại từ DB, không tin giá client gửi lên.
                $price = $variant->effectivePrice();
                $subtotal += $price * $quantity;

                $variant->decrement('quantity', $quantity);

                $name = $variant->display_name
                    ? "{$variant->product->name} - {$variant->display_name}"
                    : $variant->product->name;

                $orderItems[] = [
                    'product_variant_id' => $variant->id,
                    'product_name' => $name,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }

            // Xử lý Voucher nếu có
            $voucher = null;
            $voucherId = null;
            $discountAmount = 0.0;
            if ($request->filled('voucher_id')) {
                $voucher = \App\Models\Voucher::query()
                    ->where('status', 'active')
                    ->lockForUpdate() // Khóa để tránh race condition khi đếm lượt sử dụng
                    ->findOrFail($data['voucher_id']);

                if (!$voucher->canBeApplied($subtotal)) {
                    throw ValidationException::withMessages([
                        'voucher_id' => ['Voucher không đủ điều kiện sử dụng hoặc đã hết lượt.'],
                    ]);
                }

                $voucherId = $voucher->id;
                // Số tiền giảm tối đa không vượt quá tổng phụ đơn hàng
                $discountAmount = min((float) $voucher->discount_value, $subtotal);
            }

            $shippingFee = (float) $shippingMethod->shipping_fee;

            $order = $request->user()->orders()->create([
                'voucher_id' => $voucherId,
                'shipping_method_id' => $shippingMethod->id,
                'payment_method_id' => $data['payment_method_id'],
                'address_id' => $address->id,
                'recipient_name' => $address->recipient_name,
                'recipient_phone' => $address->recipient_phone,
                'recipient_address' => $this->composeAddress($address),
                'delivery_area' => $address->province,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discountAmount,
                'order_status' => 'pending',
                'total_amount' => max(0.0, $subtotal + $shippingFee - $discountAmount),
                'payment_status' => 'unpaid',
                'expires_at' => $isBankTransfer ? now()->addMinutes(Order::PAYMENT_WINDOW_MINUTES) : null,
                'note' => $data['note'] ?? null,
            ]);

            $order->items()->createMany($orderItems);

            // Mã đối soát chuyển khoản, sinh sau khi có id để đảm bảo duy nhất.
            $order->update(['payment_code' => 'PW' . $order->id]);

            // Cập nhật trạng thái voucher thành 'expired' nếu đã đạt giới hạn sử dụng
            if ($voucher && (int) $voucher->usage_limit > 0) {
                $usedOrders = $voucher->orders()
                    ->where('order_status', '<>', 'cancelled')
                    ->count();
                if ($usedOrders >= (int) $voucher->usage_limit) {
                    $voucher->update(['status' => 'expired']);
                }
            }

            return $order;
        });
        $order->load([
            'items',
            'shippingMethod:id,name',
            'paymentMethod:id,name',
        ]);
        try {
            // Gửi email xác nhận đơn hàng cho khách hàng
            Mail::to($request->user()->email)
                ->send(new OrderConfirmationMail($order));

            // Gửi email thông báo đơn hàng mới cho admin/chủ cửa hàng qua env
            $adminEmail = env('ADMIN_EMAIL', env('MAIL_FROM_ADDRESS'));
            if ($adminEmail) {
                Mail::to($adminEmail)
                    ->send(new OrderConfirmationMail($order));
            }
        } catch (Throwable $exception) {
            report($exception);
        }
        return response()->json([
            'message' => 'Đặt hàng thành công.',
            'data' => $this->format($order->load('items')),
        ], 201);
    }

    private function composeAddress(Address $address): string
    {
        return implode(', ', array_filter([
            $address->address_line,
            $address->ward,
            $address->district,
            $address->province,
        ]));
    }

    private function format(Order $order): array
    {
        return [
            'id' => $order->id,
            'payment_code' => $order->payment_code,
            'recipient_name' => $order->recipient_name,
            'recipient_phone' => $order->recipient_phone,
            'recipient_address' => $order->recipient_address,
            'shipping_fee' => (float) $order->shipping_fee,
            'discount_amount' => (float) $order->discount_amount,
            'total_amount' => (float) $order->total_amount,
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'expires_at' => $order->expires_at?->toIso8601String(),
            'note' => $order->note,
            'created_at' => $order->created_at?->toDateTimeString(),
            'items' => $order->items->map(fn($item): array => [
                'id' => $item->id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
            ])->all(),
        ];
    }
}
