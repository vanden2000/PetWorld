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
                'ghn_district_id' => 1442,
                'ghn_ward_code' => '20102',
            ],
            [
                'email' => 'minh.tran@petworld.test',
                'name' => 'Minh Trần',
                'phone' => '0901000002',
                'address_line' => '45 Nguyễn Huệ',
                'legacy_address_line' => '45 Nguyen Hue',
                'ward' => 'Bến Nghé',
                'district' => 'Quận 1',
                'province' => 'Hồ Chí Minh',
                'ghn_district_id' => 1442,
                'ghn_ward_code' => '20101',
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
                'ghn_district_id' => 1548,
                'ghn_ward_code' => '410101',
            ],
            [
                'email' => 'hoang.nam@petworld.test',
                'name' => 'Hoàng Nam',
                'phone' => '0901000004',
                'address_line' => '25 Tràng Tiền',
                'legacy_address_line' => '25 Trang Tien',
                'ward' => 'Tràng Tiền',
                'district' => 'Hoàn Kiếm',
                'province' => 'Hà Nội',
                'ghn_district_id' => 1482,
                'ghn_ward_code' => '1A0110',
            ],
            [
                'email' => 'thu.ha@petworld.test',
                'name' => 'Thu Hà',
                'phone' => '0901000005',
                'address_line' => '102 Nguyễn Văn Linh',
                'legacy_address_line' => '102 Nguyen Van Linh',
                'ward' => 'Nam Dương',
                'district' => 'Hải Châu',
                'province' => 'Đà Nẵng',
                'ghn_district_id' => 1526,
                'ghn_ward_code' => '40109',
            ],
            [
                'email' => 'tuan.anh@petworld.test',
                'name' => 'Tuấn Anh',
                'phone' => '0901000006',
                'address_line' => '68 Đại lộ Hòa Bình',
                'legacy_address_line' => '68 Dai lo Hoa Binh',
                'ward' => 'Tân An',
                'district' => 'Ninh Kiều',
                'province' => 'Cần Thơ',
                'ghn_district_id' => 1572,
                'ghn_ward_code' => '550111',
            ],
            [
                'email' => 'thanh.huong@petworld.test',
                'name' => 'Thanh Hương',
                'phone' => '0901000007',
                'address_line' => '52 Lạch Tray',
                'legacy_address_line' => '52 Lach Tray',
                'ward' => 'Lạch Tray',
                'district' => 'Ngô Quyền',
                'province' => 'Hải Phòng',
                'ghn_district_id' => 1587,
                'ghn_ward_code' => '30308',
            ],
            [
                'email' => 'phuong.linh@petworld.test',
                'name' => 'Phương Linh',
                'phone' => '0901000008',
                'address_line' => '150 Hai Bà Trưng',
                'legacy_address_line' => '150 Hai Ba Trung',
                'ward' => 'Phường 6',
                'district' => 'Quận 3',
                'province' => 'Hồ Chí Minh',
                'ghn_district_id' => 1444,
                'ghn_ward_code' => '20306',
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
                'ghn_district_id' => $addressData['ghn_district_id'] ?? null,
                'ghn_ward_code' => $addressData['ghn_ward_code'] ?? null,
                'is_default' => true,
                'status' => 'active',
            ])->save();
        }
    }
}
