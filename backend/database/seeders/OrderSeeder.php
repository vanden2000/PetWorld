<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\SepayTransaction;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Xóa sạch dữ liệu đơn hàng cũ để reset mới hoàn toàn
        Schema::disableForeignKeyConstraints();
        OrderItem::truncate();
        Shipment::truncate();
        SepayTransaction::truncate();
        Order::truncate();
        Schema::enableForeignKeyConstraints();

        // 2. Danh sách dữ liệu mẫu đơn hàng mới, chuẩn hóa đầy đủ tất cả luồng logic
        $orders = [
            // =========================================================================
            // 1. NHÓM ĐƠN CHỜ XÁC NHẬN (PENDING) - MỚI NGUYÊN CHƯA QUA THAO TÁC NÀO
            // =========================================================================
            [
                'payment_code' => 'PW260920',
                'email' => 'minh.tran@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 35000,
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_status' => 'pending',
                'payment_status' => 'paid',
                'reconciled_at' => Carbon::now(),
                'note' => 'Đơn hàng Chuyển khoản Bank mới đặt - Đã thanh toán tự động thành công qua SePay.',
                'created_at' => Carbon::now(),
                'items' => [
                    ['sku' => 'KONG-CLASSIC-M', 'quantity' => 1],
                    ['sku' => 'PATE-ME-O-CA-NGU-TUI-80G', 'quantity' => 3],
                ],
                'sepay_transaction' => [
                    'sepay_id' => 10007,
                    'gateway' => 'MBBank',
                    'account_number' => '0388999888',
                    'reference_code' => 'FT2624510007',
                ],
            ],
            [
                'payment_code' => 'PW260919',
                'email' => 'lan.le@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 32000,
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'pending',
                'payment_status' => 'unpaid',
                'note' => 'Đơn hàng COD mới đặt (Chưa thao tác gì) - Chờ duyệt và đóng gói.',
                'created_at' => Carbon::now()->subSeconds(30),
                'items' => [
                    ['sku' => 'ROYAL-CANIN-MINI-ADULT-3KG-BAO', 'quantity' => 1],
                    ['sku' => 'BAT-AN-INOX-TRIXIE-M', 'quantity' => 1],
                ],
            ],
            [
                'payment_code' => 'PW260901',
                'email' => 'mai.nguyen@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 31900,
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'pending',
                'payment_status' => 'unpaid',
                'note' => 'Giao vào giờ hành chính, gọi trước khi đến 15 phút.',
                'created_at' => Carbon::now()->subHours(2),
                'items' => [
                    ['sku' => 'PATE-ROYAL-CANIN-MINI-PUPPY-LON-195G', 'quantity' => 2],
                    ['sku' => 'BAT-AN-INOX-TRIXIE-S', 'quantity' => 1],
                ],
            ],
            [
                'payment_code' => 'PW260902',
                'email' => 'hoang.nam@petworld.test',
                'voucher_code' => 'PETWELCOME',
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 35000,
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_status' => 'pending',
                'payment_status' => 'unpaid',
                'expires_at' => Carbon::now()->addMinutes(12),
                'note' => 'Khách đang quét mã VietQR qua SePay.',
                'created_at' => Carbon::now()->subMinutes(3),
                'items' => [
                    ['sku' => 'ROYAL-CANIN-MINI-ADULT-1KG-BAO', 'quantity' => 1],
                    ['sku' => 'XUONG-GAM-CAO-SU-TRIXIE-M', 'quantity' => 2],
                ],
            ],
            [
                'payment_code' => 'PW260903',
                'email' => 'thu.ha@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng tiêu chuẩn',
                'shipping_fee' => 30000,
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_status' => 'pending',
                'payment_status' => 'customer_paid',
                'note' => 'Khách đã quét mã VietQR chuyển khoản thành công, chờ shop duyệt đơn đóng gói.',
                'created_at' => Carbon::now()->subHours(5),
                'items' => [
                    ['sku' => 'WHISKAS-ADULT-VI-CA-BIEN-12KG-BAO', 'quantity' => 2],
                    ['sku' => 'CHUOT-DO-CHOI-LEN-COT-XAM', 'quantity' => 3],
                ],
                'sepay_transaction' => [
                    'sepay_id' => 10005,
                    'gateway' => 'MBBank',
                    'account_number' => '0388999888',
                    'reference_code' => 'FT2624510005',
                ],
            ],

            // =========================================================================
            // 2. NHÓM ĐƠN ĐÃ XÁC NHẬN (CONFIRMED)
            // =========================================================================
            [
                'payment_code' => 'PW260904',
                'email' => 'tuan.anh@petworld.test',
                'voucher_code' => 'FREESHIP99',
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 32000,
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'confirmed',
                'payment_status' => 'unpaid',
                'note' => 'Đã gọi xác nhận với khách, nhân viên đang soạn hàng vào thùng.',
                'created_at' => Carbon::now()->subHours(8),
                'items' => [
                    ['sku' => 'KONG-CLASSIC-S', 'quantity' => 1],
                    ['sku' => 'PEDIGREE-DENTASTIX-GOI-7-THANH', 'quantity' => 2],
                ],
                'shipment' => [
                    'provider' => 'ghn',
                    'tracking_code' => 'GHN8820001',
                    'status' => 'ready_to_pick',
                ],
            ],
            [
                'payment_code' => 'PW260905',
                'email' => 'minh.tran@petworld.test',
                'voucher_code' => 'PETWELCOME',
                'shipping_method' => 'Giao hàng tiêu chuẩn',
                'shipping_fee' => 30000,
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_status' => 'confirmed',
                'payment_status' => 'paid',
                'reconciled_at' => Carbon::now()->subDay()->addHours(2),
                'note' => 'Đã nhận chuyển khoản VietQR tự động qua SePay, kiện hàng sẵn sàng bàn giao.',
                'created_at' => Carbon::now()->subDay(),
                'items' => [
                    ['sku' => 'TUI-VAN-CHUYEN-PHI-HANH-GIA-VANG', 'quantity' => 1],
                    ['sku' => 'DAY-DAT-TRIXIE-PREMIUM-S', 'quantity' => 1],
                ],
                'sepay_transaction' => [
                    'sepay_id' => 10001,
                    'gateway' => 'VietinBank',
                    'account_number' => '108876543210',
                    'reference_code' => 'FT2624510001',
                ],
            ],
            [
                'payment_code' => 'PW260906',
                'email' => 'thanh.huong@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 38000,
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_status' => 'confirmed',
                'payment_status' => 'paid',
                'note' => 'Đã nhận thanh toán VietQR qua SePay. Giao sau 17h chiều khách mới đi làm về.',
                'created_at' => Carbon::now()->subDay()->subHours(3),
                'items' => [
                    ['sku' => 'SUA-TAM-BIOLINE-CHAI-300ML', 'quantity' => 2],
                    ['sku' => 'LUOC-CHAI-LONG-TU-DONG-TRIXIE-TIEU-CHUAN', 'quantity' => 1],
                ],
                'sepay_transaction' => [
                    'sepay_id' => 10006,
                    'gateway' => 'Vietcombank',
                    'account_number' => '0071001234567',
                    'reference_code' => 'FT2624510006',
                ],
            ],

            // =========================================================================
            // 3. NHÓM ĐƠN ĐANG GIAO HÀNG (SHIPPING)
            // =========================================================================
            [
                'payment_code' => 'PW260907',
                'email' => 'phuong.linh@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 25000,
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'shipping',
                'payment_status' => 'unpaid',
                'note' => 'Bưu tá GHN đang đi giao kiện hàng đến địa chỉ người nhận.',
                'created_at' => Carbon::now()->subDays(2),
                'items' => [
                    ['sku' => 'PATE-ME-O-CA-NGU-TUI-80G', 'quantity' => 6],
                    ['sku' => 'CAN-CAU-LONG-VU-MEO-DA-SAC', 'quantity' => 2],
                ],
                'shipment' => [
                    'provider' => 'ghn',
                    'tracking_code' => 'GHN9081231',
                    'status' => 'delivering',
                ],
            ],
            [
                // ĐƠN MẪU: GIAO THẤT BẠI để Admin kiểm tra tính năng "Nhận hoàn hàng & Nhập kho"
                'payment_code' => 'PW260908',
                'email' => 'lan.le@petworld.test',
                'voucher_code' => 'FREESHIP99',
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 30000,
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'shipping',
                'payment_status' => 'unpaid',
                'note' => 'Bưu tá GHN thông báo không liên lạc được với khách hàng sau 3 cuộc gọi.',
                'created_at' => Carbon::now()->subDays(3),
                'items' => [
                    ['sku' => 'ROYAL-CANIN-MINI-ADULT-3KG-BAO', 'quantity' => 1],
                    ['sku' => 'VONG-CO-CHUONG-TRIXIE-HONG', 'quantity' => 1],
                ],
                'shipment' => [
                    'provider' => 'ghn',
                    'tracking_code' => 'GHN9081232',
                    'status' => 'delivery_fail',
                ],
            ],
            [
                'payment_code' => 'PW260909',
                'email' => 'hoang.nam@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 40000,
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_status' => 'shipping',
                'payment_status' => 'paid',
                'reconciled_at' => Carbon::now()->subDays(2)->addHours(1),
                'note' => 'Kiện hàng đang luân chuyển giữa các bưu cục liên tỉnh HN - HCM.',
                'created_at' => Carbon::now()->subDays(2)->subHours(4),
                'items' => [
                    ['sku' => 'BONG-TRIXIE-DENTA-FUN-DO', 'quantity' => 2],
                    ['sku' => 'XIT-KHU-MUI-BIOLINE-CHAI-300ML', 'quantity' => 1],
                ],
                'shipment' => [
                    'provider' => 'ghn',
                    'tracking_code' => 'GHN9081233',
                    'status' => 'transporting',
                ],
                'sepay_transaction' => [
                    'sepay_id' => 10002,
                    'gateway' => 'Vietcombank',
                    'account_number' => '0071001234567',
                    'reference_code' => 'FT2624510002',
                ],
            ],

            // =========================================================================
            // 4. NHÓM ĐƠN HOÀN THÀNH (COMPLETED) & ĐỐI SOÁT COD
            // =========================================================================
            [
                // ĐƠN MẪU: Đã giao thành công, khách đã đưa tiền cho Shipper -> Sẵn sàng chuyển sang Đang đối soát
                'payment_code' => 'PW260910',
                'email' => 'mai.nguyen@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 28000,
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'completed',
                'payment_status' => 'customer_paid',
                'note' => 'Bưu tá GHN đã giao kiện hàng thành công và thu đủ tiền mặt từ khách hàng.',
                'created_at' => Carbon::now()->subDays(2),
                'items' => [
                    ['sku' => 'PATE-ROYAL-CANIN-MINI-PUPPY-LON-195G', 'quantity' => 3],
                    ['sku' => 'PEDIGREE-DENTASTIX-GOI-7-THANH', 'quantity' => 2],
                ],
                'shipment' => [
                    'provider' => 'ghn',
                    'tracking_code' => 'GHN9081234',
                    'status' => 'delivered',
                ],
            ],
            [
                // ĐƠN MẪU: Đang đối soát bảng kê với GHN -> Sẵn sàng bấm Xác nhận đã nhận tiền từ ĐVVC
                'payment_code' => 'PW260911',
                'email' => 'minh.tran@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 35000,
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'completed',
                'payment_status' => 'reconciling',
                'note' => 'Đang đối soát bảng kê thanh toán COD kỳ tuần này với GHN.',
                'created_at' => Carbon::now()->subDays(5),
                'items' => [
                    ['sku' => 'KONG-CLASSIC-S', 'quantity' => 2],
                    ['sku' => 'BAT-AN-INOX-TRIXIE-S', 'quantity' => 1],
                ],
                'shipment' => [
                    'provider' => 'ghn',
                    'tracking_code' => 'GHN9081235',
                    'status' => 'delivered',
                ],
            ],
            [
                // ĐƠN MẪU: Có sai lệch tiền đối soát với GHN
                'payment_code' => 'PW260912',
                'email' => 'lan.le@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 30000,
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'completed',
                'payment_status' => 'discrepancy',
                'reconciliation_note' => 'Bưu cục GHN ghi nhận thu lệch 25.000đ cước vận chuyển vượt cân, shop đang gửi yêu cầu khiếu nại.',
                'note' => 'Giao thành công, xuất hiện chênh lệch cước phí cần khiếu nại.',
                'created_at' => Carbon::now()->subDays(10),
                'items' => [
                    ['sku' => 'WHISKAS-ADULT-VI-CA-BIEN-3KG-BAO', 'quantity' => 1],
                    ['sku' => 'PATE-ME-O-CA-NGU-TUI-80G', 'quantity' => 5],
                ],
                'shipment' => [
                    'provider' => 'ghn',
                    'tracking_code' => 'GHN9081236',
                    'status' => 'delivered',
                ],
            ],
            [
                // ĐƠN MẪU: Hoàn tất toàn bộ chu trình, Shop đã nhận đủ tiền COD từ GHN
                'payment_code' => 'PW260913',
                'email' => 'tuan.anh@petworld.test',
                'voucher_code' => 'PETWELCOME',
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 30000,
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'completed',
                'payment_status' => 'paid',
                'reconciled_at' => Carbon::now()->subDays(8)->addHours(4),
                'note' => 'Đơn hàng hoàn tất và GHN đã chuyển đủ tiền thu hộ COD về tài khoản ngân hàng của Shop.',
                'created_at' => Carbon::now()->subDays(12),
                'items' => [
                    ['sku' => 'TUI-VAN-CHUYEN-PHI-HANH-GIA-VANG', 'quantity' => 1],
                    ['sku' => 'SUA-TAM-BIOLINE-CHAI-300ML', 'quantity' => 1],
                    ['sku' => 'LUOC-CHAI-LONG-TU-DONG-TRIXIE-TIEU-CHUAN', 'quantity' => 1],
                ],
                'shipment' => [
                    'provider' => 'ghn',
                    'tracking_code' => 'GHN9081237',
                    'status' => 'delivered',
                ],
            ],
            [
                // ĐƠN MẪU: Giao hàng tiêu chuẩn chuyển khoản ngân hàng hoàn tất
                'payment_code' => 'PW260914',
                'email' => 'thu.ha@petworld.test',
                'voucher_code' => 'FREESHIP99',
                'shipping_method' => 'Giao hàng tiêu chuẩn',
                'shipping_fee' => 30000,
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_status' => 'completed',
                'payment_status' => 'paid',
                'reconciled_at' => Carbon::now()->subDays(4)->addHours(1),
                'note' => 'Thanh toán VietQR trước qua SePay, đơn vị vận chuyển giao thành công tận tay.',
                'created_at' => Carbon::now()->subDays(4),
                'items' => [
                    ['sku' => 'ROYAL-CANIN-MINI-ADULT-1KG-BAO', 'quantity' => 2],
                    ['sku' => 'DAY-DAT-TRIXIE-PREMIUM-S', 'quantity' => 1],
                ],
                'shipment' => [
                    'provider' => 'petworld',
                    'tracking_code' => 'PW-SHIP-014',
                    'status' => 'delivered',
                ],
                'sepay_transaction' => [
                    'sepay_id' => 10003,
                    'gateway' => 'VietinBank',
                    'account_number' => '108876543210',
                    'reference_code' => 'FT2624510003',
                ],
            ],

            // =========================================================================
            // 5. NHÓM ĐƠN ĐÃ HOÀN VỀ KHO (RETURNED)
            // =========================================================================
            [
                // ĐƠN MẪU: Đơn COD đã hoàn về kho -> Thanh toán hiển thị "Không thu tiền (Đơn hoàn)"
                'payment_code' => 'PW260915',
                'email' => 'mai.nguyen@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 28000,
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'returned',
                'payment_status' => 'unpaid',
                'returned_at' => Carbon::now()->subHours(6),
                'return_reason' => 'Khách không nghe máy / Thuê bao (Giao thất bại 3 lần), bưu tá GHN chuyển hoàn về kho shop.',
                'note' => 'Kiện hàng đã được kiểm đếm và nhập lại kho an toàn.',
                'created_at' => Carbon::now()->subDays(4),
                'items' => [
                    ['sku' => 'PATE-ROYAL-CANIN-MINI-PUPPY-LON-195G', 'quantity' => 2],
                    ['sku' => 'BAT-AN-INOX-TRIXIE-S', 'quantity' => 1],
                ],
                'shipment' => [
                    'provider' => 'ghn',
                    'tracking_code' => 'GHN9081238',
                    'status' => 'returned',
                ],
            ],
            [
                // ĐƠN MẪU: Đơn Chuyển khoản ngân hàng đã hoàn về kho và shop đã chuyển tiền hoàn lại
                'payment_code' => 'PW260916',
                'email' => 'phuong.linh@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 25000,
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_status' => 'returned',
                'payment_status' => 'refunded',
                'returned_at' => Carbon::now()->subDay(),
                'return_reason' => 'Khách từ chối nhận hàng do đi công tác dài ngày, shop đã nhận lại kiện hàng và chuyển khoản hoàn tiền.',
                'note' => 'Đã chuyển tiền hoàn qua số tài khoản ngân hàng của khách.',
                'created_at' => Carbon::now()->subDays(6),
                'items' => [
                    ['sku' => 'BAT-AN-INOX-TRIXIE-S', 'quantity' => 2],
                ],
                'shipment' => [
                    'provider' => 'ghn',
                    'tracking_code' => 'GHN9081239',
                    'status' => 'returned',
                ],
                'sepay_transaction' => [
                    'sepay_id' => 10004,
                    'gateway' => 'VietinBank',
                    'account_number' => '108876543210',
                    'reference_code' => 'FT2624510004',
                ],
            ],

            // =========================================================================
            // 6. NHÓM ĐƠN ĐÃ HỦY (CANCELLED)
            // =========================================================================
            [
                // ĐƠN MẪU: Đơn COD hủy trước khi giao -> Thanh toán hiển thị "Không thu tiền (Đã hủy)"
                'payment_code' => 'PW260917',
                'email' => 'thanh.huong@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng tiêu chuẩn',
                'shipping_fee' => 30000,
                'payment_method' => 'Thanh toán khi nhận hàng',
                'order_status' => 'cancelled',
                'payment_status' => 'unpaid',
                'note' => 'Khách gọi tổng đài yêu cầu hủy đơn do đặt nhầm phân loại sản phẩm.',
                'created_at' => Carbon::now()->subDay()->subHours(6),
                'items' => [
                    ['sku' => 'CHUOT-DO-CHOI-LEN-COT-XAM', 'quantity' => 2],
                    ['sku' => 'VONG-CO-CHUONG-TRIXIE-HONG', 'quantity' => 1],
                ],
            ],
            [
                // ĐƠN MẪU: Đơn chuyển khoản ngân hàng tự hủy do quá thời gian quét mã VietQR SePay
                'payment_code' => 'PW260918',
                'email' => 'hoang.nam@petworld.test',
                'voucher_code' => null,
                'shipping_method' => 'Giao hàng nhanh',
                'shipping_fee' => 35000,
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_status' => 'cancelled',
                'payment_status' => 'failed',
                'expires_at' => Carbon::now()->subDays(2)->addMinutes(15),
                'note' => 'Đơn hàng tự động hủy do quá thời hạn thanh toán mã QR VietQR SePay (15 phút).',
                'created_at' => Carbon::now()->subDays(2),
                'items' => [
                    ['sku' => 'XIT-KHU-MUI-BIOLINE-CHAI-300ML', 'quantity' => 2],
                    ['sku' => 'SUA-TAM-BIOLINE-CHAI-300ML', 'quantity' => 1],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $user = User::where('email', $orderData['email'])->firstOrFail();
            $address = Address::where('user_id', $user->id)->firstOrFail();
            $shippingMethod = ShippingMethod::where('name', $orderData['shipping_method'])->firstOrFail();
            $paymentMethod = PaymentMethod::where('name', $orderData['payment_method'])->firstOrFail();

            $lineTotal = 0;
            $totalWeight = 0;

            $items = collect($orderData['items'])
                ->map(function (array $item) use (&$lineTotal, &$totalWeight): array {
                    if (isset($item['sku'])) {
                        $variant = ProductVariant::with(['product', 'variantValues.variantType'])
                            ->where('sku', $item['sku'])
                            ->where('status', 'active')
                            ->firstOrFail();
                    } else {
                        $variant = ProductVariant::with(['product', 'variantValues.variantType'])
                            ->whereHas('product', fn ($query) => $query->where('slug', $item['variant']))
                            ->where('status', 'active')
                            ->orderBy('id')
                            ->firstOrFail();
                    }

                    $price = $variant->effectivePrice();
                    $lineTotal += $price * $item['quantity'];
                    $totalWeight += (int) ($variant->weight_grams ?? 500) * $item['quantity'];

                    return [
                        'product_variant_id' => $variant->id,
                        'product_name' => $variant->product->name.' - '.$variant->display_name,
                        'quantity' => $item['quantity'],
                        'price' => $price,
                    ];
                });

            $voucher = $orderData['voucher_code']
                ? Voucher::where('code', $orderData['voucher_code'])->first()
                : null;

            if ($voucher && ! $voucher->canBeApplied($lineTotal, at: $orderData['created_at'])) {
                $voucher = null;
            }

            $discountAmount = $voucher ? min((float) $voucher->discount_value, $lineTotal) : 0;
            $shippingFee = (float) ($orderData['shipping_fee'] ?? $shippingMethod->shipping_fee);
            $totalAmount = max($lineTotal + $shippingFee - $discountAmount, 0);

            $order = Order::create([
                'payment_code' => $orderData['payment_code'],
                'voucher_id' => $voucher?->id,
                'shipping_method_id' => $shippingMethod->id,
                'shipping_method_code' => $shippingMethod->code,
                'payment_method_id' => $paymentMethod->id,
                'address_id' => $address->id,
                'user_id' => $user->id,
                'recipient_name' => $address->recipient_name,
                'recipient_phone' => $address->recipient_phone,
                'recipient_address' => "{$address->address_line}, {$address->ward}, {$address->district}, {$address->province}",
                'delivery_area' => $address->province,
                'shipping_fee' => $shippingFee,
                'shipping_weight_grams' => $totalWeight,
                'shipping_fee_original' => $shippingFee,
                'shipping_discount' => 0,
                'discount_amount' => $discountAmount,
                'order_status' => $orderData['order_status'],
                'total_amount' => $totalAmount,
                'payment_status' => $orderData['payment_status'],
                'expires_at' => $orderData['expires_at'] ?? null,
                'note' => $orderData['note'] ?? null,
                'return_reason' => $orderData['return_reason'] ?? null,
                'reconciliation_note' => $orderData['reconciliation_note'] ?? null,
                'reconciled_at' => $orderData['reconciled_at'] ?? null,
                'returned_at' => $orderData['returned_at'] ?? null,
            ]);

            if (isset($orderData['created_at'])) {
                $order->created_at = $orderData['created_at'];
                $order->updated_at = $orderData['updated_at'] ?? $orderData['created_at'];
                $order->saveQuietly();
            }

            // Đồng bộ danh sách mặt hàng OrderItems
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // Đồng bộ bản ghi Vận chuyển (Shipment)
            if (! empty($orderData['shipment'])) {
                $shipData = $orderData['shipment'];
                $codAmount = 0;
                if ($orderData['payment_method'] === 'Thanh toán khi nhận hàng' && ! in_array($orderData['payment_status'], ['paid', 'refunded'])) {
                    $codAmount = $totalAmount;
                }

                $trackingCode = $shipData['tracking_code'] ?? null;
                Shipment::create([
                    'order_id' => $order->id,
                    'provider' => $shipData['provider'] ?? ($shippingMethod->provider ?? 'ghn'),
                    'tracking_code' => $trackingCode,
                    'weight_grams' => $shipData['weight_grams'] ?? $totalWeight,
                    'shipping_fee' => $shippingFee,
                    'cod_amount' => $shipData['cod_amount'] ?? $codAmount,
                    'status' => $shipData['status'] ?? 'pending',
                    'provider_status_code' => $shipData['status'] ?? 'pending',
                    'label_url' => $trackingCode ? "https://order.ghn.vn/order/print-label?code={$trackingCode}" : null,
                ]);
            }

            // Đồng bộ giao dịch SePay nếu có
            if (! empty($orderData['sepay_transaction'])) {
                $tx = $orderData['sepay_transaction'];
                SepayTransaction::create([
                    'sepay_id' => $tx['sepay_id'],
                    'order_id' => $order->id,
                    'gateway' => $tx['gateway'] ?? 'VietinBank',
                    'transaction_date' => $tx['transaction_date'] ?? $order->created_at,
                    'account_number' => $tx['account_number'] ?? '108876543210',
                    'transfer_type' => 'in',
                    'amount' => $tx['amount'] ?? $totalAmount,
                    'content' => $tx['content'] ?? "Thanh toan {$order->payment_code}",
                    'reference_code' => $tx['reference_code'] ?? 'FT'.str_pad((string) $tx['sepay_id'], 8, '0', STR_PAD_LEFT),
                    'raw_payload' => ['gateway' => $tx['gateway'] ?? 'VietinBank', 'simulated' => true],
                ]);
            }
        }
    }
}
