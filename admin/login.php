<?php
session_start();
require_once __DIR__ . '/../include/admin_roles.php';

// Tác dụng: Kiểm tra nếu quản trị viên đã đăng nhập từ trước thì chuyển hướng ngay về trang tổng quan
if (isset($_SESSION['admin_user_id']) && isAdminAreaRole($_SESSION['admin_role'] ?? null)) {
    header('Location: ' . adminRoleHomeUrl($_SESSION['admin_role']));
    exit;
}

if (empty($_SESSION['admin_login_csrf'])) {
    $_SESSION['admin_login_csrf'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/db.php';

    $token = $_POST['csrf_token'] ?? '';
    $username = isset($_POST['username']) && is_string($_POST['username'])
        ? trim($_POST['username'])
        : '';
    $password = isset($_POST['password']) && is_string($_POST['password'])
        ? $_POST['password']
        : '';

    if (!is_string($token) || !hash_equals($_SESSION['admin_login_csrf'], $token)) {
        $error = 'Phiên đăng nhập không hợp lệ. Vui lòng tải lại trang và thử lại.';
    } elseif ($username === '' || $password === '') {
        $error = 'Vui lòng nhập tên đăng nhập và mật khẩu.';
    } else {
        $stmt = $conn->prepare('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');

        if (!$stmt) {
            $error = 'Không thể đăng nhập lúc này. Vui lòng thử lại sau.';
        } else {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $conn->close();

            if (!$user || !isAdminAreaRole($user['role'] ?? null) || !password_verify($password, $user['password'])) {
                $error = 'Thông tin đăng nhập không đúng hoặc tài khoản không có quyền quản trị.';
            } else {
                $role = $user['role'] === 'admin' ? 'manager' : $user['role'];
                session_regenerate_id(true);

                // Tác dụng: Thiết lập phiên đăng nhập quản trị độc lập, tách biệt hoàn toàn với phiên mua sắm của khách hàng
                $_SESSION['admin_user_id']  = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role']     = $role;
                unset($_SESSION['admin_login_csrf']);

                header('Location: ' . adminRoleHomeUrl($role));
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập quản trị - ÉCRIN JEWELRY</title>
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            margin: 0;
            padding: 20px;
            background: #f7f6f2;
            color: #0b2033;
            font-family: Arial, sans-serif;
        }
        .login-card {
            width: min(420px, 100%);
            padding: 32px;
            border: 1px solid #e4e3df;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 12px 36px rgb(11 32 51 / 10%);
        }
        h1 { margin: 0 0 8px; font-size: 1.6rem; }
        .description { margin: 0 0 24px; color: #667085; }
        label { display: block; margin: 16px 0 7px; font-weight: 700; }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd0d7;
            border-radius: 8px;
            font: inherit;
        }
        input:focus { outline: 2px solid #c4a45a; border-color: transparent; }
        button {
            width: 100%;
            margin-top: 22px;
            padding: 12px;
            border: 0;
            border-radius: 8px;
            background: #0b2033;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        button:hover { background: #172f45; }
        .error {
            margin: 0 0 16px;
            padding: 11px;
            border-radius: 8px;
            background: #fff0ef;
            color: #b42318;
        }

    </style>
</head>
<body>
    <main class="login-card">
        <h1>Đăng nhập quản trị</h1>
        <p class="description">Cổng quản trị dành riêng cho tài khoản Nhân viên và Quản lí.</p>

        <?php if ($error !== ''): ?>
            <p class="error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['admin_login_csrf'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="username">Tên đăng nhập</label>
            <input id="username" name="username" type="text" autocomplete="username" required autofocus>

            <label for="password">Mật khẩu</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>

            <button type="submit">Đăng nhập</button>
        </form>


    </main>
</body>
</html>
