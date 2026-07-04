<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Trang quản trị PetWorld</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            padding: 24px;
            color: white;
            background: #f68930;
        }

        .content {
            flex: 1;
            padding: 32px;
        }

        .card {
            padding: 24px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <h2>PetWorld Admin</h2>

            <nav>
                <p>Dashboard</p>
                <p>Sản phẩm</p>
                <p>Danh mục</p>
                <p>Đơn hàng</p>
                <p>Khách hàng</p>
            </nav>
        </aside>

        <main class="content">
            <div class="card">
                <h1>@yield('eyebrow', 'Quan tri')</h1>
                <p>@yield('page_title', 'Dashboard')</p>
            </div>
        </main>
    </div>
</body>
</html>