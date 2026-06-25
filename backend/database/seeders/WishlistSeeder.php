<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WishlistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'mai.nguyen@petworld.test',
                'name' => 'Mai Nguyen',
                'phone' => '0901000001',
                'products' => [
                    'royal-canin-mini-adult',
                    'day-dat-trixie-premium',
                    'kong-classic',
                ],
            ],
            [
                'email' => 'minh.tran@petworld.test',
                'name' => 'Minh Tran',
                'phone' => '0901000002',
                'products' => [
                    'whiskas-adult-vi-ca-bien',
                    'pate-me-o-ca-ngu',
                    'bong-trixie-denta-fun',
                ],
            ],
            [
                'email' => 'lan.le@petworld.test',
                'name' => 'Lan Le',
                'phone' => '0901000003',
                'products' => [
                    'smartheart-creamy-treat',
                    'bat-an-inox-trixie',
                    'sua-tam-bioline',
                ],
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'phone' => $userData['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'status' => 'active',
                ],
            );

            foreach ($userData['products'] as $productSlug) {
                $product = Product::where('slug', $productSlug)->first();

                if (! $product) {
                    continue;
                }

                Wishlist::updateOrCreate([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}
