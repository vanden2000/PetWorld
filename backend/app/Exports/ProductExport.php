<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductExport implements WithMultipleSheets
{
    public function __construct(
        private readonly Builder $productQuery,
        private readonly bool $includeVariants = true,
    ) {}

    public function sheets(): array
    {
        $sheets = [
            new ProductsSheet(clone $this->productQuery),
        ];

        if ($this->includeVariants) {
            $sheets[] = new ProductVariantsSheet(clone $this->productQuery);
        }

        return $sheets;
    }
}
