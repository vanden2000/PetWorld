<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class OrderController extends Controller
{
    private const ORDER_STATUSES = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao hàng',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    private const PAYMENT_STATUSES = [
        'unpaid' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán lỗi',
        'refunded' => 'Đã hoàn tiền',
    ];

    private const ORDER_STATUS_CLASS = [
        'pending' => 'pending',
        'confirmed' => 'processing',
        'shipping' => 'shipping',
        'completed' => 'delivered',
        'cancelled' => 'cancelled',
    ];

    private const PAYMENT_STATUS_CLASS = [
        'unpaid' => 'pending',
        'paid' => 'paid',
        'failed' => 'failed',
        'refunded' => 'refunded',
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'payment_status' => ['nullable', Rule::in(array_keys(self::PAYMENT_STATUSES))],
            'order_status' => ['nullable', Rule::in(array_keys(self::ORDER_STATUSES))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $orders = Order::query()
            ->with(['user:id,name,email', 'paymentMethod:id,name', 'shippingMethod:id,name'])
            ->withCount('items')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $numericId = preg_replace('/\D/', '', $search);
                $query->where(function ($nested) use ($search, $numericId): void {
                    if ($numericId !== '') {
                        $nested->where('id', (int) $numericId);
                    }

                    $nested->orWhere('payment_code', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhere('recipient_phone', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($filters['payment_status'] ?? null, fn ($query, string $status) => $query->where('payment_status', $status))
            ->when($filters['order_status'] ?? null, fn ($query, string $status) => $query->where('order_status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'orderStatuses' => self::ORDER_STATUSES,
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'orderStatusClasses' => self::ORDER_STATUS_CLASS,
            'paymentStatusClasses' => self::PAYMENT_STATUS_CLASS,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'scope' => ['nullable', Rule::in(['filtered', 'all'])],
            'search' => ['nullable', 'string', 'max:80'],
            'payment_status' => ['nullable', Rule::in(array_keys(self::PAYMENT_STATUSES))],
            'order_status' => ['nullable', Rule::in(array_keys(self::ORDER_STATUSES))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = $this->exportQuery(($filters['scope'] ?? 'filtered') === 'all' ? [] : $filters);

        if (! (clone $query)->exists()) {
            return redirect()
                ->route('admin.orders', $request->only(['search', 'payment_status', 'order_status', 'date_from', 'date_to']))
                ->with('error', 'Không có đơn hàng phù hợp để xuất.');
        }

        return Excel::download(
            new OrdersExport($query),
            'don-hang-petworld_' . now()->format('Ymd-Hi') . '.xlsx',
        );
    }

    public function show($id)
    {
        $order = $this->findOrderWithDetails($id);

        return view('admin.orders.show', [
            'order' => $order,
            'orderStatuses' => self::ORDER_STATUSES,
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'orderStatusClasses' => self::ORDER_STATUS_CLASS,
            'paymentStatusClasses' => self::PAYMENT_STATUS_CLASS,
            'nextOrderStatuses' => $this->nextOrderStatuses($order->order_status),
            'nextPaymentStatuses' => $this->nextPaymentStatuses($order->payment_status),
        ]);
    }

    /** Display a print-ready A5 sales receipt for an order. */
    public function invoice($id)
    {
        $order = $this->findOrderWithDetails($id);

        return view('admin.orders.invoice', [
            'order' => $order,
            'orderStatuses' => self::ORDER_STATUSES,
            'paymentStatuses' => self::PAYMENT_STATUSES,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'order_status' => ['nullable', Rule::in(array_keys(self::ORDER_STATUSES))],
            'payment_status' => ['nullable', Rule::in(array_keys(self::PAYMENT_STATUSES))],
        ]);

        if (! isset($data['order_status']) && ! isset($data['payment_status'])) {
            return back()->with('error', 'Vui lòng chọn trạng thái cần cập nhật.');
        }

        $updatedOrder = DB::transaction(function () use ($order, $data): Order {
            $lockedOrder = Order::query()
                ->with('items')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->order_status === 'cancelled') {
                throw ValidationException::withMessages([
                    'order_status' => 'Đơn hàng đã hủy nên không thể cập nhật trạng thái.',
                ]);
            }

            $updates = [];

            if (isset($data['order_status']) && $data['order_status'] !== $lockedOrder->order_status) {
                if (! in_array($data['order_status'], $this->nextOrderStatuses($lockedOrder->order_status), true)) {
                    throw ValidationException::withMessages([
                        'order_status' => 'Trạng thái đơn hàng chỉ được đi tiếp, không được lùi bước.',
                    ]);
                }

                if ($data['order_status'] === 'cancelled') {
                    $this->restoreStock($lockedOrder);
                }

                $updates['order_status'] = $data['order_status'];
            }

            if (isset($data['payment_status']) && $data['payment_status'] !== $lockedOrder->payment_status) {
                if (! in_array($data['payment_status'], $this->nextPaymentStatuses($lockedOrder->payment_status), true)) {
                    throw ValidationException::withMessages([
                        'payment_status' => 'Trạng thái thanh toán không hợp lệ.',
                    ]);
                }

                $updates['payment_status'] = $data['payment_status'];
            }

            if ($updates !== []) {
                $lockedOrder->update($updates);
            }

            return $lockedOrder->refresh();
        });

        $updatedOrder->load('user');

        try {
            if ($updatedOrder->user?->email) {
                Mail::to($updatedOrder->user->email)->send(new OrderStatusMail($updatedOrder));
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }

    private function nextOrderStatuses(string $current): array
    {
        return match ($current) {
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['shipping', 'cancelled'],
            'shipping' => ['completed'],
            default => [],
        };
    }

    private function nextPaymentStatuses(string $current): array
    {
        return match ($current) {
            'unpaid' => ['paid', 'failed'],
            'paid' => ['refunded'],
            default => [],
        };
    }

    private function restoreStock(Order $order): void
    {
        $variantIds = $order->items
            ->pluck('product_variant_id')
            ->filter()
            ->unique()
            ->values();

        $variants = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $variants->get($item->product_variant_id)?->increment('quantity', $item->quantity);
        }
    }

    private function exportQuery(array $filters): Builder
    {
        return Order::query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $numericId = preg_replace('/\D/', '', $search);
                $query->where(function (Builder $nested) use ($search, $numericId): void {
                    if ($numericId !== '') {
                        $nested->where('id', (int) $numericId);
                    }

                    $nested->orWhere('payment_code', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhere('recipient_phone', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($filters['payment_status'] ?? null, fn (Builder $query, string $status) => $query->where('payment_status', $status))
            ->when($filters['order_status'] ?? null, fn (Builder $query, string $status) => $query->where('order_status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date));
    }

    private function findOrderWithDetails($id): Order
    {
        return Order::query()
            ->with([
                'user:id,name,email',
                'paymentMethod:id,name',
                'shippingMethod:id,name',
                'voucher:id,code,discount_value',
                'items.productVariant.product.primaryImage',
                'items.productVariant.variantValues.variantType',
                'sepayTransactions' => fn ($query) => $query->latest(),
            ])
            ->findOrFail($id);
    }
}
