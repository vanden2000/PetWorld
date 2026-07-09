<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">

    <title>Cập nhật đơn hàng</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background: #f5f5f5;
    font-family: Arial, sans-serif;
    color: #333333;
">
    @php
        $orderCode = 'PW'.str_pad(
            (string) $order->id,
            6,
            '0',
            STR_PAD_LEFT
        );
    @endphp

    <div style="padding: 30px 15px;">
        <div style="
            max-width: 620px;
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
                    Cập nhật trạng thái đơn hàng
                </p>
            </div>

            <div style="padding: 30px;">
                <h2>
                    Xin chào {{ $order->recipient_name }}
                </h2>

                @if ($order->order_status === 'confirmed')
                    <p>
                        Đơn hàng của bạn đã được PetWorld xác nhận.
                    </p>

                    <p>
                        Chúng tôi sẽ chuẩn bị hàng và bàn giao cho
                        đơn vị vận chuyển trong thời gian sớm nhất.
                    </p>
                @elseif ($order->order_status === 'cancelled')
                    <p>
                        Đơn hàng của bạn đã được hủy thành công.
                    </p>

                    <p>
                        Sản phẩm trong đơn hàng đã được hoàn lại kho.
                    </p>
                @elseif ($order->order_status === 'shipping')
                    <p>
                        Đơn hàng của bạn đang được vận chuyển.
                    </p>
                @elseif ($order->order_status === 'completed')
                    <p>
                        Đơn hàng đã được giao thành công.
                    </p>
                @else
                    <p>
                        Trạng thái đơn hàng của bạn vừa được cập nhật.
                    </p>
                @endif

                <div style="
                    margin: 20px 0;
                    padding: 16px;
                    background: #fff7f0;
                    border-left: 4px solid #f68930;
                ">
                    <p style="margin: 0 0 8px;">
                        <strong>Mã đơn hàng:</strong>
                        {{ $orderCode }}
                    </p>

                    <p style="margin: 0 0 8px;">
                        <strong>Trạng thái:</strong>

                        @switch($order->order_status)
                            @case('confirmed')
                                Đã xác nhận
                                @break

                            @case('cancelled')
                                Đã hủy
                                @break

                            @case('shipping')
                                Đang giao hàng
                                @break

                            @case('completed')
                                Đã giao thành công
                                @break

                            @default
                                {{ $order->order_status }}
                        @endswitch
                    </p>

                    <p style="margin: 0;">
                        <strong>Tổng tiền:</strong>

                        {{ number_format(
                            $order->total_amount,
                            0,
                            ',',
                            '.'
                        ) }}đ
                    </p>
                </div>

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

                <p style="margin-top: 30px;">
                    Cảm ơn bạn đã sử dụng PetWorld.
                </p>
            </div>
        </div>
    </div>
</body>
</html>