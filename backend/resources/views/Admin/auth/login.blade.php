<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetWorld Admin - Login</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        

        body {
            font-family: var(--font-main);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            background: var(--bg-blur);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.8);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-content {
            padding: 40px 40px 30px;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background-color: var(--primary-light);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 75, 62, 0.1);
        }

        .brand-name {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .brand-sub {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .forgot-link {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .forgot-link:hover {
            color: var(--primary-hover);
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.95rem;
        }

        .input-field {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1px solid var(--border-color);
            background-color: #ffffff;
            border-radius: 8px;
            font-family: var(--font-main);
            font-size: 0.9rem;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s ease;
        }

        .input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 75, 62, 0.08);
        }

        .input-field::placeholder {
            color: #9ca3af;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            transition: color 0.15s ease;
            background: none;
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
        }

        .password-toggle:hover {
            color: var(--text-main);
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }

        .checkbox-input {
            width: 16px;
            height: 16px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            margin-right: 8px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .checkbox-label {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: var(--primary);
            color: #ffffff;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-family: var(--font-main);
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 75, 62, 0.15);
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 12px -2px rgba(0, 75, 62, 0.2);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Error/Alert box styling */
        .error-alert {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .error-alert i {
            color: #ef4444;
            font-size: 1rem;
            margin-top: 1px;
        }

        .error-alert span {
            font-name: var(--font-main);
            font-size: 0.85rem;
            color: #991b1b;
            font-weight: 500;
            line-height: 1.4;
        }

        /* Footer links area inside card */
        .login-footer {
            background-color: #f9fafb;
            padding: 20px 40px;
            border-top: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .footer-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .version-text {
            font-size: 0.75rem;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .footer-links {
            display: flex;
            gap: 12px;
        }

        .footer-link {
            font-size: 0.75rem;
            color: #6b7280;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .footer-link:hover {
            color: var(--text-main);
        }

        .status-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 4px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            color: #10b981;
            font-weight: 500;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            background-color: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .system-version {
            font-size: 0.75rem;
            color: #9ca3af;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-content">
            <div class="brand-header">
                <div class="brand-icon">
                    <i class="fa-solid fa-paw"></i>
                </div>
                <h1 class="brand-name">PetWorld Admin</h1>
                <p class="brand-sub">Truy Cập Cổng Quản Lý</p>
            </div>

            @if ($errors->any())
                <div class="error-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </span>
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">Địa chỉ Email</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-regular fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="input-field" 
                               placeholder="admin@petworld.com" value="{{ old('email', 'admin@petworld.com') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="password" class="form-label">Mật khẩu</label>
                    </div>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="input-field" 
                               placeholder="••••••••" value="admin123" required style="padding-right: 40px;">
                        <button type="button" class="password-toggle" id="toggle-password" tabindex="-1">
                            <i class="fa-regular fa-eye-slash" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="remember" name="remember" class="checkbox-input" checked>
                    <label for="remember" class="checkbox-label">Ghi nhớ</label>
                </div>

                <button type="submit" class="btn-submit">
                    Đăng nhập <i class="fa-solid fa-arrow-right-long"></i>
                </button>
            </form>
        </div>

        <div class="login-footer">
            <div class="footer-top">
                <span class="version-text">Bản cập nhật 2.4.0</span>
                <div class="footer-links">
                    <a href="#" class="footer-link">Hỗ trợ</a>
                    <a href="#" class="footer-link">Pháp lý</a>
                    <a href="#" class="footer-link">Điều khoản</a>
                </div>
            </div>
            <div class="status-bar">
                <div class="status-indicator">
                    <span class="status-dot"></span>
                    <span>Vận hành hệ thốngl</span>
                </div>
                <span class="system-version">v.2024.12</span>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePasswordButton = document.getElementById('toggle-password');
        const eyeIcon = document.getElementById('eye-icon');

        togglePasswordButton.addEventListener('click', function () {
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle the icon
            if (type === 'password') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
