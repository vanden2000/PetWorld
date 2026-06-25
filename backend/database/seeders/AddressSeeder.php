<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addresses = [
            [
                'email' => 'mai.nguyen@petworld.test',
                'name' => 'Mai Nguyen',
                'phone' => '0901000001',
                'address_line' => '12 Nguyen Trai',
                'ward' => 'Ben Thanh',
                'district' => 'Quan 1',
                'province' => 'Ho Chi Minh',
            ],
            [
                'email' => 'minh.tran@petworld.test',
                'name' => 'Minh Tran',
                'phone' => '0901000002',
                'address_line' => '45 Le Loi',
                'ward' => 'Phu Hoi',
                'district' => 'Hue',
                'province' => 'Thua Thien Hue',
            ],
            [
                'email' => 'lan.le@petworld.test',
                'name' => 'Lan Le',
                'phone' => '0901000003',
                'address_line' => '88 Tran Phu',
                'ward' => 'Loc Tho',
                'district' => 'Nha Trang',
                'province' => 'Khanh Hoa',
            ],
        ];

        foreach ($addresses as $addressData) {
            $user = User::updateOrCreate(
                ['email' => $addressData['email']],
                [
                    'name' => $addressData['name'],
                    'phone' => $addressData['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'status' => 'active',
                ],
            );

            Address::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'address_line' => $addressData['address_line'],
                ],
                [
                    'recipient_name' => $addressData['name'],
                    'recipient_phone' => $addressData['phone'],
                    'ward' => $addressData['ward'],
                    'district' => $addressData['district'],
                    'province' => $addressData['province'],
                    'is_default' => true,
                    'status' => 'active',
                ],
            );
        }
    }
}
