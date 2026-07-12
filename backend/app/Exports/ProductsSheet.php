<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsSheet implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $rowNumber = 0;

    public function __construct(private readonly Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'STT',
            'ID sản phẩm',
            'Tên sản phẩm',
            'Slug',
            'Danh mục',
            'Thương hiệu',
            'Giá thấp nhất',
            'Giá cao nhất',
            'Số biến thể',
            'Tổng tồn kho',
            'Trạng thái',
            'Mô tả ngắn',
        ];
    }

    /**
     * @param  Product  $product
     */
    public function map($product): array
    {
        $prices = $product->variants
            ->map(fn ($variant): float => $variant->effectivePrice())
            ->values();

        return [
            ++$this->rowNumber,
            $product->id,
            $this->safeText($product->name),
            $this->safeText($product->slug),
            $this->safeText($product->category?->name ?? 'Chưa phân loại'),
            $this->safeText($product->brand?->name ?? 'Chưa có thương hiệu'),
            $prices->isEmpty() ? null : $prices->min(),
            $prices->isEmpty() ? null : $prices->max(),
            $product->variants->count(),
            $product->variants->sum('quantity'),
            $product->status === 'active' ? 'Đang hiển thị' : 'Đã ẩn',
            $this->safeText(strip_tags((string) $product->short_description)),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'FF782D'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Sản phẩm';
    }

    private function safeText(?string $value): string
    {
        $value = trim((string) $value);

        return preg_match('/^[=+\-@]/u', $value) === 1 ? "'{$value}" : $value;
    }
}
