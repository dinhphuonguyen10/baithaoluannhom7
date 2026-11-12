<?php
require_once __DIR__ . '/../include/admin_guard.php';
$appBaseUrl = applicationBaseUrl();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
// Tác dụng: Xác thực phiên và cấp quyền quản lý giao dịch thanh toán
$isManager  = isManagerRole($_SESSION['admin_role'] ?? $_SESSION['role'] ?? null);
$roleLabel  = $isManager ? 'Quản lý' : 'Nhân viên';
$adminUsername = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? '';
$adminCurrentPage = 'payments.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý thanh toán - ÉCRIN Jewelry</title>
    <link rel="stylesheet" href="<?php echo $escape($appBaseUrl . '/admin/style.css'); ?>">
    <style>
        .proto-panel { margin: 20px 0; padding: 22px; border: 1px solid var(--line); border-radius: 12px; background: #fff; }
        .proto-panel h2 { margin-top: 0; }
        .proto-table { width: 100%; border-collapse: collapse; }
        .proto-table th, .proto-table td { padding: 12px; border-bottom: 1px solid var(--line); text-align: left; }
        .proto-table th { color: var(--muted); font-size: .85rem; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../include/admin_sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="breadcrumb">Trang chủ <span>/</span> <strong>Quản lý thanh toán</strong></div>
            <div class="admin-profile">
                <div class="avatar"><?php echo $isManager ? 'QL' : 'NV'; ?></div>
                <div class="admin-info"><strong><?php echo $escape($adminUsername); ?></strong><small><?php echo $escape($roleLabel); ?></small></div>
            </div>
        </header>
        <section class="page-content">
            <div class="welcome-row"><div><h1>Quản lý thanh toán</h1></div></div>
            <section class="proto-panel">
                <h2>Danh sách giao dịch</h2>
                <div style="overflow-x:auto">
                    <table class="proto-table">
                        <thead><tr><th>Mã GD</th><th>Khách hàng</th><th>Số tiền</th><th>Phương thức</th><th>Trạng thái</th><th>Ngày</th></tr></thead>
                        <tbody>
                            <tr><td colspan="6" style="text-align:center;color:var(--muted);">Chưa có dữ liệu giao dịch.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </main>
</div>
</body>
</html>