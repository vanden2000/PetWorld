<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu giao hàng {{ $order->payment_code ?: 'PW' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}
    </title>
    <style>
        @page {
            size: A5 portrait;
            margin: 9mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0 auto;
            color: #1f2937;
            font: 12px/1.45 Arial, sans-serif;
        }

        .receipt {
            width: 100%;
            max-width: 130mm;
            margin: 0 auto;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin: 0 0 12px;
        }

        .toolbar a,
        .toolbar button {
            border: 0;
            border-radius: 6px;
            padding: 8px 12px;
            background: #ff782d;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .toolbar a {
            background: #e5e7eb;
            color: #374151;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 0 0 10px;
            border-bottom: 2px solid #ff782d;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ff782d;
            font-size: 21px;
            font-weight: 800;
            letter-spacing: -.5px;
        }

        .brand img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }

        .brand small {
            display: block;
            margin-top: 2px;
            color: #6b7280;
            font-size: 10px;
            font-weight: 400;
            letter-spacing: 0;
        }

        .document {
            text-align: right;
        }

        .document strong {
            display: block;
            font-size: 15px;
        }

        .document span {
            color: #6b7280;
            font-size: 10px;
        }

        .section {
            margin-top: 12px;
        }

        .section-title {
            margin: 0 0 5px;
            color: #ff782d;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .45px;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .card {
            min-width: 0;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
        }

        .card p {
            margin: 1px 0;
            word-break: break-word;
        }

        .muted {
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 6px 4px;
            border-bottom: 1px solid #9ca3af;
            color: #4b5563;
            font-size: 9px;
            text-align: left;
            text-transform: uppercase;
        }

        td {
            padding: 6px 4px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        th:first-child,
        td:first-child {
            padding-left: 0;
        }

        th:last-child,
        td:last-child {
            padding-right: 0;
        }

        .right {
            text-align: right;
            white-space: nowrap;
        }

        .product-name {
            font-weight: 700;
        }

        .variant {
            color: #6b7280;
            font-size: 10px;
        }

        .summary {
            width: 56%;
            margin: 10px 0 0 auto;
        }

        .summary div {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 3px 0;
        }

        .summary .total {
            margin-top: 3px;
            padding-top: 7px;
            border-top: 1px solid #9ca3af;
            color: #e9661c;
            font-size: 14px;
            font-weight: 800;
        }

        .note {
            padding: 7px 8px;
            border-left: 3px solid #ff782d;
            background: #fff4ed;
        }

        .footer {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px dashed #9ca3af;
            color: #6b7280;
            font-size: 10px;
            text-align: center;
        }

        @media print {
            .toolbar {
                display: none;
            }

            .receipt {
                max-width: none;
            }
        }
    </style>
</head>

<body>
    @php
        $orderCode = $order->payment_code ?: 'PW' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
        $subtotal = $order->items->sum(fn($item) => (float) $item->price * $item->quantity);
    @endphp
    <main class="receipt">
        <div class="toolbar">
            <a href="{{ route('admin.orders.show', $order->id) }}">← Chi tiết đơn</a>
            <button type="button" onclick="window.print()">In / Lưu PDF</button>
        </div>

        <header class="header">
            <div class="brand">
                <img src="{{ asset('image/logo/logo.png') }}" alt="PetWorld">
                <div>PetWorld <small>PHIẾU GIAO HÀNG / HÓA ĐƠN BÁN HÀNG</small></div>
            </div>
            <div class="document"><strong>{{ $orderCode }}</strong><span>Ngày đặt:
                    {{ $order->created_at?->format('d/m/Y H:i') }}</span></div>
        </header>

        <section class="section">
            <div class="details">
                <div class="card">
                    <h2 class="section-title">Người nhận</h2>
                    <p><strong>{{ $order->recipient_name }}</strong></p>
                    <p>{{ $order->recipient_phone }}</p>
                    <p>{{ $order->recipient_address }}</p>
                    <p class="muted">{{ $order->delivery_area }}</p>
                </div>
                <div class="card">
                    <h2 class="section-title">Thanh toán & vận chuyển</h2>
                    <p>{{ $order->paymentMethod?->name ?? 'Chưa xác định' }}</p>
                    <p class="muted">{{ $paymentStatuses[$order->payment_status] ?? $order->payment_status }}</p>
                    <p>{{ $order->shippingMethod?->name ?? 'Chưa xác định' }}</p>
                    <p class="muted">{{ $orderStatuses[$order->order_status] ?? $order->order_status }}</p>
                </div>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">Sản phẩm</h2>
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="right">SL</th>
                        <th class="right">Đơn giá</th>
                        <th class="right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div class="product-name">{{ $item->product_name }}</div>
                                <div class="variant">{{ $item->productVariant?->display_name }}</div>
                            </td>
                            <td class="right">{{ $item->quantity }}</td>
                            <td class="right">{{ number_format((float) $item->price, 0, ',', '.') }}đ</td>
                            <td class="right">{{ number_format((float) $item->price * $item->quantity, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="summary">
                <div><span>Tạm tính</span><strong>{{ number_format($subtotal, 0, ',', '.') }}đ</strong></div>
                <div><span>Phí vận
                        chuyển</span><strong>{{ number_format((float) $order->shipping_fee, 0, ',', '.') }}đ</strong>
                </div>
                <div><span>Giảm
                        giá{{ $order->voucher?->code ? ' (' . $order->voucher->code . ')' : '' }}</span><strong>-{{ number_format((float) $order->discount_amount, 0, ',', '.') }}đ</strong>
                </div>
                <div class="total"><span>Tổng
                        tiền</span><span>{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</span></div>
            </div>
        </section>

        @if($order->note)
            <section class="section">
                <h2 class="section-title">Ghi chú</h2>
                <div class="note">{{ $order->note }}</div>
            </section>
        @endif
        <footer class="footer">Cảm ơn quý khách đã mua sắm tại PetWorld.</footer>
    </main>
</body>

</html>