@php
    $contact = $support['email'] ?? '';
    $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL) !== false;
    $type = ($support['type'] ?? '') !== '' ? $support['type'] : 'Yêu cầu hỗ trợ';
    $orderCode = ($support['order_code'] ?? '') !== '' ? $support['order_code'] : null;
    $name = ($support['name'] ?? '') !== '' ? $support['name'] : '—';

    $pr = $support['priority'] ?? '';

    $ticket = 'PW-' . now()->format('Ymd') . '-' . strtoupper(substr(md5($contact . ($support['message'] ?? '') . now()->timestamp), 0, 4));
    $receivedAt = now()->format('H:i, d/m/Y');

    $ctaLabel = $isEmail ? 'TRẢ LỜI KHÁCH HÀNG' : 'GỌI KHÁCH HÀNG';
    $ctaHref = $isEmail ? ('mailto:' . $contact) : ('tel:' . preg_replace('/[\s.\-()]/', '', $contact));
@endphp
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetWorld - Yêu cầu hỗ trợ mới</title>
</head>

<body style="margin: 0; padding: 0; background-color: #faf9f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #211c17; -webkit-font-smoothing: antialiased;">

    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Yêu cầu hỗ trợ mới ({{ $type }}) từ {{ $name }} — cần xử lý trong 24 giờ.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #faf9f6; padding: 40px 15px;">
        <tr>
            <td align="center">
                <!-- Outer Container -->
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%;">
                    
                    <!-- Logo / Header -->
                    <tr>
                        <td style="padding-bottom: 24px; text-align: center;">
                            <span style="font-size: 24px; font-weight: 800; color: #ff782d; letter-spacing: -0.5px;">PetWorld</span>
                            <span style="display: block; font-size: 12px; color: #a69e90; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; font-weight: 600;">Hệ thống chăm sóc khách hàng</span>
                        </td>
                    </tr>
                    
                    <!-- Main Card -->
                    <tr>
                        <td style="background-color: #ffffff; border-radius: 16px; border: 1px solid #ece5d8; box-shadow: 0 10px 30px rgba(33, 28, 23, 0.04); overflow: hidden;">
                            
                            <!-- Header Strip -->
                            <div style="background: linear-gradient(135deg, #ff782d, #e9661c); padding: 32px 32px 28px 32px; color: #ffffff;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <span style="display: inline-block; background-color: rgba(255, 255, 255, 0.2); font-size: 10.5px; font-weight: 700; padding: 4px 10px; border-radius: 20px; letter-spacing: 0.5px; text-transform: uppercase;">Ticket #{{ $ticket }}</span>
                                            <h1 style="margin: 12px 0 6px 0; font-size: 20px; font-weight: 800; line-height: 1.3; letter-spacing: -0.2px;">Yêu cầu hỗ trợ mới</h1>
                                            <p style="margin: 0; font-size: 13px; color: rgba(255, 255, 255, 0.85);">Nhận lúc {{ $receivedAt }} • Cần xử lý trong 24 giờ</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Content Area -->
                            <div style="padding: 32px;">
                                
                                <!-- Info Table -->
                                <h3 style="margin: 0 0 16px 0; font-size: 12px; font-weight: 700; color: #a69e90; text-transform: uppercase; letter-spacing: 0.5px;">Thông tin khách hàng</h3>
                                
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 28px;">
                                    <!-- Khách hàng -->
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f3ede2; font-size: 13.5px; color: #6b6459; width: 35%;">Khách hàng</td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f3ede2; font-size: 14px; color: #211c17; font-weight: 600;">{{ $name }}</td>
                                    </tr>
                                    <!-- Liên hệ -->
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f3ede2; font-size: 13.5px; color: #6b6459;">{{ $isEmail ? 'Email' : 'Số điện thoại' }}</td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f3ede2; font-size: 14px; font-weight: 600;">
                                            <a href="{{ $ctaHref }}" style="color: #2f6fed; text-decoration: none;">{{ $contact }}</a>
                                        </td>
                                    </tr>
                                    <!-- Mã đơn hàng -->
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f3ede2; font-size: 13.5px; color: #6b6459;">Mã đơn hàng</td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f3ede2; font-size: 14px; color: #211c17; font-weight: 600;">
                                            @if ($orderCode)
                                                <span style="color: #ff782d;">{{ $orderCode }}</span>
                                            @else
                                                <span style="color: #a69e90; font-weight: 400; font-style: italic;">— Chưa liên kết —</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <!-- Loại yêu cầu -->
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f3ede2; font-size: 13.5px; color: #6b6459;">Loại yêu cầu</td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f3ede2; font-size: 14px; color: #211c17; font-weight: 600;">{{ $type }}</td>
                                    </tr>
                                    <!-- Mức độ ưu tiên -->
                                    <tr>
                                        <td style="padding: 12px 0; font-size: 13.5px; color: #6b6459;">Độ ưu tiên</td>
                                        <td style="padding: 12px 0; font-size: 13.5px;">
                                            @if ($pr === 'Khẩn cấp')
                                                <span style="background-color: #fdecec; color: #c0392b; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 12px; display: inline-block;">KHẨN CẤP</span>
                                            @elseif ($pr === 'Trung bình')
                                                <span style="background-color: #fff3e0; color: #c77700; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 12px; display: inline-block;">TRUNG BÌNH</span>
                                            @elseif ($pr === 'Thấp')
                                                <span style="background-color: #e9f7ef; color: #1e8e4e; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 12px; display: inline-block;">THẤP</span>
                                            @else
                                                <span style="background-color: #f5f5f5; color: #6b6459; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 12px; display: inline-block;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Message Section -->
                                <h3 style="margin: 0 0 12px 0; font-size: 12px; font-weight: 700; color: #a69e90; text-transform: uppercase; letter-spacing: 0.5px;">Nội dung lời nhắn</h3>
                                <div style="background-color: #fffaf5; border-left: 4px solid #ff782d; border-radius: 4px; padding: 18px 20px; margin-bottom: 32px;">
                                    <p style="margin: 0; font-size: 14px; color: #3e362e; line-height: 1.7; white-space: pre-line; font-style: italic;">
                                        "{{ $support['message'] ?? '' }}"
                                    </p>
                                </div>
                                
                                <!-- Action Button -->
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ $ctaHref }}" style="display: inline-block; background-color: #ff782d; color: #ffffff; text-decoration: none; padding: 14px 36px; font-size: 14px; font-weight: 700; border-radius: 50px; box-shadow: 0 6px 18px rgba(255, 120, 45, 0.3); transition: all 0.2s ease;">
                                                {{ $ctaLabel }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 32px 24px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #a69e90; line-height: 1.6;">
                                Cửa hàng PetWorld • 137 Bình Long, Phường Bình Trị Đông, Quận Bình Tân, TP. HCM<br>
                                Hotline: 0332 477 689 • Email: petworldshopvv@gmail.com
                            </p>
                            <p style="margin: 12px 0 0 0; font-size: 11px; color: #c7bfb3;">
                                © {{ date('Y') }} PetWorld. Tất cả các quyền được bảo lưu.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>

</body>

</html>
