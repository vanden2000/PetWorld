<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã xác nhận quên mật khẩu</title>
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
        Mã OTP xác nhận yêu cầu quên mật khẩu của bạn tại PetWorld.
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
                                Quên mật khẩu
                            </h1>

                            <p style="
                                    margin: 0;
                                    color: #fff4ea;
                                    font-size: 15px;
                                    line-height: 1.6;
                                ">
                                Mã xác nhận để thiết lập lại mật khẩu của bạn
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
                                    {{ $name }}
                                </strong>,
                            </p>

                            <p style="
                                    margin: 0 0 18px;
                                    color: #555555;
                                    font-size: 15px;
                                    line-height: 1.8;
                                ">
                                Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại
                                <strong>PetWorld</strong>. Dưới đây là mã xác nhận (OTP) của bạn:
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="center" style="padding: 18px 0 28px;">
                                        <div style="
                                                display: inline-block;
                                                padding: 20px 40px;
                                                background-color: #f68930;
                                                color: #ffffff;
                                                font-size: 36px;
                                                font-weight: 700;
                                                letter-spacing: 8px;
                                                border-radius: 12px;
                                                box-shadow: 0 5px 14px rgba(246, 137, 48, 0.3);
                                            ">
                                            {{ $otp }}
                                        </div>
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
                                    Mã OTP này sẽ hết hạn sau
                                    <strong>10 phút</strong>. Vui lòng không chia sẻ mã này cho bất kỳ ai.
                                </p>
                            </div>

                            <p style="
                                    margin: 25px 0 0;
                                    color: #777777;
                                    font-size: 14px;
                                    line-height: 1.7;
                                ">
                                Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này hoặc liên hệ với bộ phận hỗ trợ nếu bạn nghi ngờ có truy cập trái phép.
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
