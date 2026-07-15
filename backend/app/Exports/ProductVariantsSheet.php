<?php

namespace App\Exports;

use App\Models\ProductVariant;
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

class ProductVariantsSheet implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $rowNumber = 0;

    public function __construct(private readonly Builder $productQuery) {}

    public function query(): Builder
    {
        $productIds = (clone $this->productQuery)
            ->select('products.id')
            ->reorder();

        return ProductVariant::query()
            ->with(['product', 'variantValues.variantType'])
            ->whereIn('product_id', $productIds)
            ->orderBy('product_id')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'STT',
            'ID sản phẩm',
            'Tên sản phẩm',
            'ID biến thể',
            'Thuộc tính biến thể',
            'SKU',
            'Giá gốc',
            'Giá khuyến mãi',
            'Giá thực tế',
            'Tồn kho',
            'Trạng thái',
        ];
    }

    /**
     * @param  ProductVariant  $variant
     */
    public function map($variant): array
    {
        $attributes = $variant->variantValues
            ->sortBy('variant_type_id')
            ->map(function ($value): string {
                $type = $value->variantType?->name;

                return $type ? "{$type}: {$value->value}" : $value->value;
            })
            ->implode(' / ');

        return [
            ++$this->rowNumber,
            $variant->product_id,
            $this->safeText($variant->product?->name ?? ''),
            $variant->id,
            $this->safeText($attributes !== '' ? $attributes : 'Biến thể mặc định'),
            $this->safeText($variant->sku),
            (float) $variant->price,
            $variant->hasValidSalePrice() ? (float) $variant->sale_price : null,
            $variant->effectivePrice(),
            $variant->quantity,
            $variant->status === 'active' ? 'Đang hiển thị' : 'Đã ẩn',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
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
        return 'Biến thể';
    }

    private function safeText(?string $value): string
    {
        $value = trim((string) $value);

        return preg_match('/^[=+\-@]/u', $value) === 1 ? "'{$value}" : $value;
    }
}
