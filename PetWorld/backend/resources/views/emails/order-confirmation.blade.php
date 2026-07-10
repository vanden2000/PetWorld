<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Xác nhận đơn hàng PetWorld</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background: #f5f5f5;
    font-family: Arial, sans-serif;
    color: #333333;
">
    <div style="padding: 30px 15px;">
        <div style="
            max-width: 680px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        ">
            <div style="
                padding: 24px;
                background: #f68930;
                color: #ffffff;
                text-align: center;
            ">
                <h1 style="margin: 0;">
                    PetWorld
                </h1>

                <p style="margin: 8px 0 0;">
                    Xác nhận đơn hàng
                </p>
            </div>

            <div style="padding: 30px;">
                <h2 style="margin-top: 0;">
                    Xin chào {{ $order->recipient_name }}
                </h2>

                <p>
                    PetWorld đã nhận được đơn hàng của bạn.
                </p>

                <div style="
                    margin: 20px 0;
                    padding: 16px;
                    background: #fff6ef;
                    border-left: 4px solid #f68930;
                ">
                    <p style="margin: 0 0 8px;">
                        <strong>Mã đơn hàng:</strong>

                        PW{{ str_pad(
    (string) $order->id,
    6,
    '0',
    STR_PAD_LEFT
) }}
                    </p>

                    <p style="margin: 0 0 8px;">
                        <strong>Mã thanh toán:</strong>

                        {{ $order->payment_code }}
                    </p>

                    <p style="margin: 0 0 8px;">
                        <strong>Trạng thái đơn hàng:</strong>

                        {{ $order->order_status }}
                        {{ $order->order_status === 'pending' ? 'Chờ xử lý' : '' }}
                        {{ $order->order_status === 'confirmed' ? 'Đã xác nhận' : '' }}
                        {{ $order->order_status === 'cancelled' ? 'Đã hủy' : '' }}
                        {{ $order->order_status === 'shipping' ? 'Đang giao hàng' : '' }}
                        {{ $order->order_status === 'completed' ? 'Đã giao hàng' : '' }}
                    </p>

                    <p style="margin: 0;">
                        <strong>Ngày đặt:</strong>

                        {{ $order->created_at?->format('d/m/Y H:i') }}
                    </p>
                </div>

                <h3>Thông tin nhận hàng</h3>

                <p>
                    <strong>Người nhận:</strong>
                    {{ $order->recipient_name }}
                </p>

                <p>
                    <strong>Số điện thoại:</strong>
                    {{ $order->recipient_phone }}
                </p>

                <p>
                    <strong>Địa chỉ:</strong>
                    {{ $order->recipient_address }}
                </p>

                <h3>Sản phẩm</h3>

                <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th align="left" style="border: 1px solid #dddddd;">
                                Sản phẩm
                            </th>

                            <th align="center" style="border: 1px solid #dddddd;">
                                SL
                            </th>

                            <th align="right" style="border: 1px solid #dddddd;">
                                Đơn giá
                            </th>

                            <th align="right" style="border: 1px solid #dddddd;">
                                Thành tiền
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($order->items as $item)
                                                <tr>
                                                    <td style="border: 1px solid #dddddd;">
                                                        {{ $item->product_name }}
                                                    </td>

                                                    <td align="center" style="border: 1px solid #dddddd;">
                                                        {{ $item->quantity }}
                                                    </td>

                                                    <td align="right" style="border: 1px solid #dddddd;">
                                                        {{ number_format(
                                $item->price,
                                0,
                                ',',
                                '.'
                            ) }}đ
                                                    </td>

                                                    <td align="right" style="border: 1px solid #dddddd;">
                                                        {{ number_format(
                                $item->price * $item->quantity,
                                0,
                                ',',
                                '.'
                            ) }}đ
                                                    </td>
                                                </tr>
                        @endforeach
                    </tbody>
                </table>

                @php
                    $subtotal = $order->items->sum(
                        fn($item) => $item->price * $item->quantity
                    );
                @endphp

                <div style="margin-top: 24px;">
                    <p style="text-align: right;">
                        Tạm tính:

                        <strong>
                            {{ number_format(
    $subtotal,
    0,
    ',',
    '.'
) }}đ
                        </strong>
                    </p>

                    <p style="text-align: right;">
                        Phí vận chuyển:

                        <strong>
                            {{ number_format(
    $order->shipping_fee,
    0,
    ',',
    '.'
) }}đ
                        </strong>
                    </p>

                    @if ((float) $order->discount_amount > 0)
                                        <p style="text-align: right;">
                                            Giảm giá:

                                            <strong>
                                                -{{ number_format(
                            $order->discount_amount,
                            0,
                            ',',
                            '.'
                        ) }}đ
                                            </strong>
                                        </p>
                    @endif

                    <p style="
                        text-align: right;
                        font-size: 20px;
                        color: #f68930;
                    ">
                        Tổng thanh toán:

                        <strong>
                            {{ number_format(
    $order->total_amount,
    0,
    ',',
    '.'
) }}đ
                        </strong>
                    </p>
                </div>

                @if ($order->note)
                    <div style="
                                                    margin-top: 20px;
                                                    padding: 14px;
                                                    background: #f8f8f8;
                                                ">
                        <strong>Ghi chú:</strong>

                        {{ $order->note }}
                    </div>
                @endif

                <p style="margin-top: 30px;">
                    PetWorld sẽ tiếp tục thông báo khi trạng thái đơn hàng
                    thay đổi.
                </p>

                <p>
                    Cảm ơn bạn đã mua sắm tại PetWorld.
                </p>
            </div>

            <div style="
                padding: 18px;
                background: #eeeeee;
                text-align: center;
                font-size: 13px;
                color: #666666;
            ">
                Đây là email tự động, vui lòng không trả lời email này.
            </div>
        </div>
    </div>
</body>

</html>