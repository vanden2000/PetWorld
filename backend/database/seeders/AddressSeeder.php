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
                'name' => 'Mai Nguyễn',
                'phone' => '0901000001',
                'address_line' => '12 Nguyễn Trãi',
                'legacy_address_line' => '12 Nguyen Trai',
                'ward' => 'Bến Thành',
                'district' => 'Quận 1',
                'province' => 'Hồ Chí Minh',
            ],
            [
                'email' => 'minh.tran@petworld.test',
                'name' => 'Minh Trần',
                'phone' => '0901000002',
                'address_line' => '45 Lê Lợi',
                'legacy_address_line' => '45 Le Loi',
                'ward' => 'Phú Hội',
                'district' => 'Huế',
                'province' => 'Thừa Thiên Huế',
            ],
            [
                'email' => 'lan.le@petworld.test',
                'name' => 'Lan Lê',
                'phone' => '0901000003',
                'address_line' => '88 Trần Phú',
                'legacy_address_line' => '88 Tran Phu',
                'ward' => 'Lộc Thọ',
                'district' => 'Nha Trang',
                'province' => 'Khánh Hòa',
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

            // Tái sử dụng địa chỉ không dấu cũ để chuẩn hóa dữ liệu mà không tạo bản ghi trùng.
            $address = Address::query()
                ->where('user_id', $user->id)
                ->whereIn('address_line', [$addressData['address_line'], $addressData['legacy_address_line']])
                ->first() ?? new Address(['user_id' => $user->id]);

            $address->fill([
                'address_line' => $addressData['address_line'],
                'recipient_name' => $addressData['name'],
                'recipient_phone' => $addressData['phone'],
                'ward' => $addressData['ward'],
                'district' => $addressData['district'],
                'province' => $addressData['province'],
                'is_default' => true,
                'status' => 'active',
            ])->save();
        }
    }
}
