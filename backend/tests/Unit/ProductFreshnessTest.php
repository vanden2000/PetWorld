<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProductFreshnessTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_a_product_is_new_for_thirty_days_including_the_boundary(): void
    {
        Carbon::setTestNow('2026-09-02 12:00:00');

        $recentProduct = (new Product)->forceFill(['created_at' => now()->subDays(30)]);
        $oldProduct = (new Product)->forceFill(['created_at' => now()->subDays(30)->subSecond()]);

        $this->assertTrue($recentProduct->isNew());
        $this->assertFalse($oldProduct->isNew());
    }
}
