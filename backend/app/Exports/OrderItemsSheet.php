<?php

namespace App\Exports;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderItemsSheet implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $rowNumber = 0;
    public function __construct(private readonly Builder $ordersQuery) {}
    public function query(): Builder { $orderIds = (clone $this->ordersQuery)->select('orders.id')->reorder(); return OrderItem::query()->with(['order:id,payment_code,created_at', 'productVariant:id,sku', 'productVariant.variantValues.variantType'])->whereIn('order_id', $orderIds)->orderBy('order_id')->orderBy('id'); }
    public function headings(): array { return ['STT', 'Mã đơn', 'Ngày đặt', 'Tên sản phẩm', 'Phân loại', 'SKU', 'Số lượng', 'Đơn giá', 'Thành tiền']; }
    /** @param OrderItem $item */
    public function map($item): array { return [++$this->rowNumber, $this->text($item->order?->payment_code ?: 'PW' . str_pad((string) $item->order_id, 6, '0', STR_PAD_LEFT)), $item->order?->created_at?->format('d/m/Y H:i'), $this->text($item->product_name), $this->text($item->productVariant?->display_name ?? ''), $this->text($item->productVariant?->sku ?? ''), $item->quantity, (float) $item->price, (float) $item->price * $item->quantity]; }
    public function columnFormats(): array { return ['G' => '#,##0', 'H' => '#,##0', 'I' => '#,##0']; }
    public function styles(Worksheet $sheet): array { $sheet->freezePane('A2'); $sheet->setAutoFilter($sheet->calculateWorksheetDimension()); return [1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FF782D']]]]; }
    public function title(): string { return 'Chi tiết sản phẩm'; }
    private function text(?string $value): string { $value = trim((string) $value); return preg_match('/^[=+\-@]/u', $value) === 1 ? "'{$value}" : $value; }
}
