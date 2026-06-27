<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BrandSeeder::class,
            CategorySeeder::class,
            VariantTypeSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            ProductImageSeeder::class,
            BannerSeeder::class,
            BlogCategorySeeder::class,
            BlogSeeder::class,
            WishlistSeeder::class,
            VoucherSeeder::class,
            ShippingMethodSeeder::class,
            PaymentMethodSeeder::class,
            AddressSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
