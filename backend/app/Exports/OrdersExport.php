<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OrdersExport implements WithMultipleSheets
{
    public function __construct(private readonly Builder $ordersQuery) {}

    public function sheets(): array
    {
        return [new OrdersSheet(clone $this->ordersQuery), new OrderItemsSheet(clone $this->ordersQuery)];
    }
}
