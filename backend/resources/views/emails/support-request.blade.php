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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    </style>
</head>

<body style="margin:0; padding:0; background-color:#F5F5F5; font-family:'Inter', Arial, Helvetica, sans-serif;">

    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Yêu cầu hỗ trợ mới ({{ $type }}) từ {{ $name }} — cần xử lý trong 24 giờ.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F5F5F5; padding:0 0 30px 0;">

        <!-- {{-- TOP UTILITY BAR --}}
        <tr>
            <td style="background-color:#241A12; padding:8px 0;">
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" align="center" style="max-width:640px;">
                    <tr>
                        <td style="font-size:11px; color:#C9BEB2; font-family:'Inter', Arial, sans-serif;">Trung tâm hỗ trợ khách hàng</td>
                        <td align="right" style="font-size:11px; color:#C9BEB2; font-family:'Inter', Arial, sans-serif;">Hotline: +84 123 456 789</td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- HEADER --}}
        <tr>
            <td style="background-color:#ff782d; padding:16px 0;">
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" align="center" style="max-width:640px;">
                    <tr>
                        <td valign="middle">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:32px; height:32px; background-color:#ffffff; border-radius:8px; text-align:center; vertical-align:middle;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2L4 6v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V6l-8-4z" fill="#ff782d"/></svg>
                                    </td>
                                    <td style="padding-left:10px; font-size:19px; font-weight:800; color:#ffffff; font-family:'Inter', Arial, sans-serif;">PetWorld</td>
                                </tr>
                            </table>
                        </td>
                        <td align="right" valign="middle">
                            <span style="background-color:rgba(255,255,255,0.20); color:#fff; font-size:11px; font-weight:700; padding:6px 12px; border-radius:4px; font-family:'Inter', Arial, sans-serif;">TICKET #{{ $ticket }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr> -->

        {{-- MAIN CARD --}}
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="max-width:640px; background-color:#ffffff;">

                    {{-- ALERT STRIP --}}
                    <tr>
                        <td style="padding:22px 32px; border-bottom:1px solid #EFEFEF;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="40">
                                        <table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="width:34px; height:34px; background-color:#FFF1E8; border-radius:50%; text-align:center; vertical-align:middle;">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M12 3l9 16H3l9-16z" stroke="#ff782d" stroke-width="2" stroke-linejoin="round"/><path d="M12 10v4M12 17h.01" stroke="#ff782d" stroke-width="2" stroke-linecap="round"/></svg>
                                        </td></tr></table>
                                    </td>
                                    <td style="padding-left:12px;" valign="middle">
                                        <div style="font-size:15px; font-weight:700; color:#241A12; font-family:'Inter', Arial, sans-serif;">Có một yêu cầu hỗ trợ mới cần xử lý</div>
                                        <div style="font-size:12px; color:#8C8175; margin-top:2px; font-family:'Inter', Arial, sans-serif;">Nhận lúc {{ $receivedAt }} · Hạn phản hồi: trong 24 giờ</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- TRACKING --}}
                    <tr>
                        <td style="padding:24px 32px 4px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="33%" align="center">
                                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%"><tr>
                                            <td width="50%" style="border-top:2px solid transparent;">&nbsp;</td>
                                            <td style="width:20px; height:20px; background-color:#ff782d; border-radius:50%; text-align:center; vertical-align:middle;"><span style="color:#fff; font-size:12px;">&#10003;</span></td>
                                            <td width="50%" style="border-top:2px solid #ff782d;">&nbsp;</td>
                                        </tr></table>
                                        <div style="font-size:11px; font-weight:700; color:#ff782d; margin-top:8px; font-family:'Inter', Arial, sans-serif;">Đã tiếp nhận</div>
                                    </td>
                                    <td width="33%" align="center">
                                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%"><tr>
                                            <td width="50%" style="border-top:2px solid #ff782d;">&nbsp;</td>
                                            <td style="width:20px; height:20px; background-color:#ffffff; border:2px solid #ff782d; border-radius:50%;">&nbsp;</td>
                                            <td width="50%" style="border-top:2px solid #E5E5E5;">&nbsp;</td>
                                        </tr></table>
                                        <div style="font-size:11px; font-weight:700; color:#241A12; margin-top:8px; font-family:'Inter', Arial, sans-serif;">Đang xử lý</div>
                                    </td>
                                    <td width="33%" align="center">
                                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%"><tr>
                                            <td width="50%" style="border-top:2px solid #E5E5E5;">&nbsp;</td>
                                            <td style="width:20px; height:20px; background-color:#ffffff; border:2px solid #E5E5E5; border-radius:50%;">&nbsp;</td>
                                            <td width="50%" style="border-top:2px solid transparent;">&nbsp;</td>
                                        </tr></table>
                                        <div style="font-size:11px; font-weight:600; color:#B5ACA0; margin-top:8px; font-family:'Inter', Arial, sans-serif;">Hoàn tất</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- INFO TABLE --}}
                    <tr>
                        <td style="padding:26px 32px 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #EFEFEF; border-radius:8px;">
                                <tr>
                                    <td style="padding:13px 18px; background-color:#FAFAFA; width:42%; font-size:12.5px; color:#8C8175; border-bottom:1px solid #EFEFEF; font-family:'Inter', Arial, sans-serif;">Khách hàng</td>
                                    <td style="padding:13px 18px; font-size:13.5px; color:#241A12; font-weight:600; border-bottom:1px solid #EFEFEF; font-family:'Inter', Arial, sans-serif;">{{ $name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:13px 18px; background-color:#FAFAFA; font-size:12.5px; color:#8C8175; border-bottom:1px solid #EFEFEF; font-family:'Inter', Arial, sans-serif;">{{ $isEmail ? 'Email' : 'Số điện thoại' }}</td>
                                    <td style="padding:13px 18px; font-size:13.5px; border-bottom:1px solid #EFEFEF; font-family:'Inter', Arial, sans-serif;">
                                        <a href="{{ $ctaHref }}" style="color:#ff782d; text-decoration:none; font-weight:600;">{{ $contact }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:13px 18px; background-color:#FAFAFA; font-size:12.5px; color:#8C8175; border-bottom:1px solid #EFEFEF; font-family:'Inter', Arial, sans-serif;">Mã đơn hàng</td>
                                    @if ($orderCode)
                                        <td style="padding:13px 18px; font-size:13.5px; color:#241A12; font-weight:600; border-bottom:1px solid #EFEFEF; font-family:'Inter', Arial, sans-serif;">{{ $orderCode }}</td>
                                    @else
                                        <td style="padding:13px 18px; font-size:13.5px; color:#B5ACA0; border-bottom:1px solid #EFEFEF; font-family:'Inter', Arial, sans-serif;">— Chưa liên kết —</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td style="padding:13px 18px; background-color:#FAFAFA; font-size:12.5px; color:#8C8175; border-bottom:1px solid #EFEFEF; font-family:'Inter', Arial, sans-serif;">Loại yêu cầu</td>
                                    <td style="padding:13px 18px; font-size:13.5px; color:#241A12; font-weight:600; border-bottom:1px solid #EFEFEF; font-family:'Inter', Arial, sans-serif;">{{ $type }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:13px 18px; background-color:#FAFAFA; font-size:12.5px; color:#8C8175; font-family:'Inter', Arial, sans-serif;">Mức độ ưu tiên</td>
                                    <td style="padding:13px 18px; font-size:13.5px; font-family:'Inter', Arial, sans-serif;">
                                        @if ($pr === 'Khẩn cấp')
                                            <span style="background-color:#FDECEC; color:#C0392B; font-size:11px; font-weight:700; padding:4px 10px; border-radius:4px;">KHẨN CẤP</span>
                                        @elseif ($pr === 'Trung bình')
                                            <span style="background-color:#FFF3E0; color:#C77700; font-size:11px; font-weight:700; padding:4px 10px; border-radius:4px;">TRUNG BÌNH</span>
                                        @elseif ($pr === 'Thấp')
                                            <span style="background-color:#E9F7EF; color:#1E8E4E; font-size:11px; font-weight:700; padding:4px 10px; border-radius:4px;">THẤP</span>
                                        @else
                                            <span style="background-color:#F0F0F0; color:#555555; font-size:11px; font-weight:700; padding:4px 10px; border-radius:4px;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- MESSAGE --}}
                    <tr>
                        <td style="padding:24px 32px 0 32px;">
                            <div style="font-size:12.5px; font-weight:700; color:#241A12; margin-bottom:10px; font-family:'Inter', Arial, sans-serif;">Nội dung yêu cầu</div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FAFAFA; border-radius:8px; border-left:3px solid #ff782d;">
                                <tr>
                                    <td style="padding:16px 18px; font-size:13.5px; color:#3E362E; line-height:1.7; white-space:pre-line; font-family:'Inter', Arial, sans-serif;">{{ $support['message'] ?? '' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- CTA --}}
                    <tr>
                        <td style="padding:26px 32px 30px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="border-radius:6px; background-color:#ff782d;">
                                        <a href="{{ $ctaHref }}" style="display:block; text-align:center; padding:14px 18px; font-size:13.5px; font-weight:700; color:#ffffff; text-decoration:none; font-family:'Inter', Arial, sans-serif;">{{ $ctaLabel }}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>

        {{-- TRUST BADGES --}}
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="max-width:640px; background-color:#FFF8F2; border-top:1px solid #EFEFEF;">
                    <tr>
                        <td style="padding:20px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="25%" align="center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M3 7h11v8H3z" stroke="#ff782d" stroke-width="1.6"/><path d="M14 10h4l3 3v2h-7z" stroke="#ff782d" stroke-width="1.6"/><circle cx="7" cy="18" r="1.6" fill="#ff782d"/><circle cx="17" cy="18" r="1.6" fill="#ff782d"/></svg>
                                        <div style="font-size:10px; color:#6B6053; font-weight:600; margin-top:5px; font-family:'Inter', Arial, sans-serif;">Freeship 50K</div>
                                    </td>
                                    <td width="25%" align="center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M3 12a9 9 0 1 1 3 6.7" stroke="#ff782d" stroke-width="1.6"/><path d="M3 12v5h5" stroke="#ff782d" stroke-width="1.6"/></svg>
                                        <div style="font-size:10px; color:#6B6053; font-weight:600; margin-top:5px; font-family:'Inter', Arial, sans-serif;">Đổi trả 7 ngày</div>
                                    </td>
                                    <td width="25%" align="center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6l7-3z" stroke="#ff782d" stroke-width="1.6"/></svg>
                                        <div style="font-size:10px; color:#6B6053; font-weight:600; margin-top:5px; font-family:'Inter', Arial, sans-serif;">Thanh toán an toàn</div>
                                    </td>
                                    <td width="25%" align="center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M21 11.5a8.4 8.4 0 0 1-8.4 8.4 8.4 8.4 0 0 1-4-1L3 20l1.2-5.6a8.4 8.4 0 1 1 16.8-3z" stroke="#ff782d" stroke-width="1.6"/></svg>
                                        <div style="font-size:10px; color:#6B6053; font-weight:600; margin-top:5px; font-family:'Inter', Arial, sans-serif;">Hỗ trợ 24/7</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- FOOTER --}}
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="max-width:640px;">
                    <tr>
                        <td style="padding:24px 32px; text-align:center;">
                            <div style="font-size:13px; font-weight:800; color:#241A12; margin-bottom:6px; font-family:'Inter', Arial, sans-serif;">PETWORLD</div>
                            <p style="margin:0; font-size:11px; color:#A79A8C; line-height:1.7; font-family:'Inter', Arial, sans-serif;">
                                Cửa hàng PetWorld · 137 Bình Long, Bình Trị Đông, Bình Tân, TP. Hồ Chí Minh<br />
                                Email tự động từ hệ thống Trung tâm hỗ trợ.
                            </p>
                            <p style="margin:10px 0 0 0; font-size:10.5px; color:#C7BFB3; font-family:'Inter', Arial, sans-serif;">© {{ date('Y') }} PetWorld. Bảo lưu mọi quyền.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>

</body>

</html>
