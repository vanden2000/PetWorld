<?php

namespace App\Queries;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class AdminProductQuery
{
    /**
     * Build the shared admin product query used by the list and exports.
     *
     * @param  array{search?: mixed, category_id?: mixed, status?: mixed}  $filters
     */
    public function build(array $filters = []): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $categoryId = $filters['category_id'] ?? null;
        $status = $filters['status'] ?? 'all';

        return Product::query()
            ->with([
                'category',
                'brand',
                'variants.variantValues.variantType',
                'images',
                'primaryImage',
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('variants', function (Builder $variantQuery) use ($search): void {
                            $variantQuery->where('sku', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                $categoryId && $categoryId !== 'all',
                fn (Builder $query) => $query->where('category_id', $categoryId),
            )
            ->when(
                in_array($status, ['active', 'inactive'], true),
                fn (Builder $query) => $query->where('status', $status),
            );
    }
}
