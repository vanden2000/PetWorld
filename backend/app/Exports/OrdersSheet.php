<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersSheet implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $rowNumber = 0;
    public function __construct(private readonly Builder $query) {}
    public function query(): Builder { return $this->query->with(['user:id,name,email', 'paymentMethod:id,name', 'shippingMethod:id,name', 'items:id,order_id,quantity,price']); }
    public function headings(): array { return ['STT', 'Mã đơn', 'Ngày đặt', 'Khách hàng', 'Người nhận', 'SĐT', 'Địa chỉ giao hàng', 'Khu vực', 'Vận chuyển', 'Thanh toán', 'TT thanh toán', 'TT đơn hàng', 'Tạm tính', 'Phí vận chuyển', 'Giảm giá', 'Tổng tiền', 'Ghi chú']; }
    /** @param Order $order */
    public function map($order): array
    {
        return [++$this->rowNumber, $this->text($order->payment_code ?: 'PW' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT)), $order->created_at?->format('d/m/Y H:i'), $this->text($order->user?->name ?? $order->user?->email ?? ''), $this->text($order->recipient_name), $this->text($order->recipient_phone), $this->text($order->recipient_address), $this->text($order->delivery_area), $this->text($order->shippingMethod?->name ?? ''), $this->text($order->paymentMethod?->name ?? ''), $this->paymentStatus($order->payment_status), $this->orderStatus($order->order_status), (float) $order->items->sum(fn ($item) => (float) $item->price * $item->quantity), (float) $order->shipping_fee, (float) $order->discount_amount, (float) $order->total_amount, $this->text($order->note)];
    }
    public function columnFormats(): array { return ['M' => '#,##0', 'N' => '#,##0', 'O' => '#,##0', 'P' => '#,##0']; }
    public function styles(Worksheet $sheet): array { $sheet->freezePane('A2'); $sheet->setAutoFilter($sheet->calculateWorksheetDimension()); return [1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FF782D']]]]; }
    public function title(): string { return 'Đơn hàng'; }
    private function text(?string $value): string { $value = trim((string) $value); return preg_match('/^[=+\-@]/u', $value) === 1 ? "'{$value}" : $value; }
    private function orderStatus(string $status): string { return ['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao hàng', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'][$status] ?? $status; }
    private function paymentStatus(string $status): string { return ['unpaid' => 'Chờ thanh toán', 'paid' => 'Đã thanh toán', 'failed' => 'Thanh toán lỗi', 'refunded' => 'Đã hoàn tiền'][$status] ?? $status; }
}
