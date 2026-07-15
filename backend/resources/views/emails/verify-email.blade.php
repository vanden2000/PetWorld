<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Xác minh tài khoản PetWorld</title>
</head>

<body style="
        margin: 0;
        padding: 0;
        background-color: #f4f6f8;
        font-family: Arial, Helvetica, sans-serif;
        color: #333333;
    ">
    <div style="
            display: none;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
        ">
        Xác minh email để hoàn tất đăng ký tài khoản PetWorld.
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f4f6f8;">
        <tr>
            <td align="center" style="padding: 40px 16px;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="
                        max-width: 620px;
                        background-color: #ffffff;
                        border-radius: 16px;
                        overflow: hidden;
                        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
                    ">
                    <tr>
                        <td align="center" style="
                                padding: 32px 24px;
                                background-color: #f68930;
                            ">
                            <div style="
                                    display: inline-block;
                                    padding: 10px 18px;
                                    background-color: #ffffff;
                                    border-radius: 30px;
                                    color: #f68930;
                                    font-size: 24px;
                                    font-weight: 700;
                                    letter-spacing: 0.5px;
                                ">
                                PetWorld
                            </div>

                            <h1 style="
                                    margin: 24px 0 8px;
                                    color: #ffffff;
                                    font-size: 27px;
                                    line-height: 1.3;
                                ">
                                Xác minh địa chỉ email
                            </h1>

                            <p style="
                                    margin: 0;
                                    color: #fff4ea;
                                    font-size: 15px;
                                    line-height: 1.6;
                                ">
                                Chỉ còn một bước để hoàn tất tài khoản
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 36px 34px;">
                            <p style="
                                    margin: 0 0 20px;
                                    font-size: 18px;
                                    line-height: 1.6;
                                ">
                                Xin chào
                                <strong>
                                    {{ $user->name }}
                                </strong>,
                            </p>

                            <p style="
                                    margin: 0 0 18px;
                                    color: #555555;
                                    font-size: 15px;
                                    line-height: 1.8;
                                ">
                                Cảm ơn bạn đã đăng ký tài khoản tại
                                <strong>PetWorld</strong>.
                                Vui lòng nhấn nút bên dưới để xác minh
                                địa chỉ email của bạn.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="center" style="padding: 18px 0 28px;">
                                        <a href="{{ $verificationUrl }}" style="
                                                display: inline-block;
                                                padding: 15px 34px;
                                                background-color: #f68930;
                                                color: #ffffff;
                                                text-decoration: none;
                                                font-size: 16px;
                                                font-weight: 700;
                                                border-radius: 8px;
                                                box-shadow:
                                                    0 5px 14px
                                                    rgba(246, 137, 48, 0.3);
                                            ">
                                            Xác minh email
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <div style="
                                    padding: 16px 18px;
                                    background-color: #fff7f0;
                                    border-left: 4px solid #f68930;
                                    border-radius: 6px;
                                ">
                                <p style="
                                        margin: 0;
                                        color: #7a4a21;
                                        font-size: 14px;
                                        line-height: 1.7;
                                    ">
                                    Liên kết xác minh sẽ hết hạn sau
                                    <strong>
                                        {{ $expiresInMinutes }} phút
                                    </strong>.
                                </p>
                            </div>

                            <p style="
                                    margin: 25px 0 8px;
                                    color: #666666;
                                    font-size: 14px;
                                    line-height: 1.7;
                                ">
                                Nếu nút phía trên không hoạt động, hãy
                                sao chép đường dẫn sau và mở trong trình
                                duyệt:
                            </p>

                            <p style="
                                    margin: 0;
                                    padding: 12px;
                                    background-color: #f5f5f5;
                                    border-radius: 6px;
                                    color: #777777;
                                    font-size: 12px;
                                    line-height: 1.6;
                                    word-break: break-all;
                                ">
                                {{ $verificationUrl }}
                            </p>

                            <p style="
                                    margin: 25px 0 0;
                                    color: #777777;
                                    font-size: 14px;
                                    line-height: 1.7;
                                ">
                                Nếu bạn không đăng ký tài khoản PetWorld,
                                bạn có thể bỏ qua email này.
                            </p>

                            <p style="
                                    margin: 28px 0 0;
                                    color: #444444;
                                    font-size: 15px;
                                    line-height: 1.7;
                                ">
                                Trân trọng,<br>
                                <strong>Đội ngũ PetWorld</strong>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="
                                padding: 22px 28px;
                                background-color: #f7f7f7;
                                border-top: 1px solid #eeeeee;
                            ">
                            <p style="
                                    margin: 0 0 6px;
                                    color: #777777;
                                    font-size: 13px;
                                ">
                                PetWorld – Thức ăn và phụ kiện thú cưng
                            </p>

                            <p style="
                                    margin: 0;
                                    color: #aaaaaa;
                                    font-size: 12px;
                                ">
                                Đây là email được gửi tự động.
                                Vui lòng không trả lời email này.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>