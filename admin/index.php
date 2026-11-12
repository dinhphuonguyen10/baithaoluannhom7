<?php
require_once __DIR__ . '/../include/admin_guard.php';

// Tác dụng: Lấy thông tin tài khoản và vai trò từ phiên quản trị độc lập
$isManager = isManagerRole($_SESSION['admin_role'] ?? $_SESSION['role'] ?? null);
$roleLabel = $isManager ? 'Quản lý' : 'Nhân viên';
$adminUsername = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? '';
$appBaseUrl = applicationBaseUrl();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$adminCurrentPage = 'index.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ÉCRIN Jewelry | Giao diện quản trị</title>
    <link rel="stylesheet" href="<?php echo $escape($appBaseUrl . '/admin/style.css'); ?>">
    <style>
        .dash-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .dash-card { padding: 20px; border: 1px solid var(--line); border-radius: 12px; background: #fff; box-shadow: var(--shadow); }
        .dash-card span { display: block; color: var(--muted); font-size: 13px; }
        .dash-card strong { display: block; margin-top: 12px; color: var(--navy); font-size: 1.5rem; }
        .quick-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-top: 18px; }
        .quick-link { padding: 18px; border: 1px solid var(--line); border-radius: 10px; background: #fff; transition: .2s; }
        .quick-link:hover { border-color: #B09060; background: #FBF8F3; }
        .quick-link strong { display: block; margin-bottom: 7px; color: var(--navy); }
        .quick-link span { color: var(--muted); line-height: 1.5; font-size: 13px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../include/admin_sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="breadcrumb">Trang chủ <span>/</span> <strong>Tổng quan</strong></div>
            <div class="admin-profile">
                <div class="avatar"><?php echo $isManager ? 'QL' : 'NV'; ?></div>
                <div class="admin-info"><strong><?php echo $escape($adminUsername); ?></strong><small><?php echo $escape($roleLabel); ?></small></div>
            </div>
        </header>
        <section class="page-content">
            <div class="welcome-row">
                <div>
                    <h1>Tổng quan</h1>
                    <p class="page-subtitle">Bảng điều khiển quản trị ÉCRIN Jewelry.</p>
                </div>
            </div>
            <section class="dash-grid" aria-label="Thẻ thống kê">
                <article class="dash-card"><span>Doanh thu hôm nay</span><strong>—</strong></article>
                <article class="dash-card"><span>Đơn hàng</span><strong>—</strong></article>
                <article class="dash-card"><span>Sản phẩm</span><strong>—</strong></article>
                <article class="dash-card"><span>Khách hàng</span><strong>—</strong></article>
            </section>
            <h2>Các khu vực quản lý</h2>
            <div class="quick-links">
                <a class="quick-link" href="<?php echo $escape($appBaseUrl . '/admin/products.php'); ?>"><strong>Quản lý sản phẩm</strong><span>Xem danh sách và thêm mới sản phẩm.</span></a>
                <a class="quick-link" href="<?php echo $escape($appBaseUrl . '/admin/orders.php'); ?>"><strong>Quản lý đơn hàng</strong><span>Theo dõi và xử lý đơn hàng của khách.</span></a>
                <a class="quick-link" href="<?php echo $escape($appBaseUrl . '/admin/chat.php'); ?>"><strong>Chat khách hàng</strong><span>Xem và trả lời hội thoại khách hàng.</span></a>
                <a class="quick-link" href="<?php echo $escape($appBaseUrl . '/admin/stores.php'); ?>"><strong>Cửa hàng</strong><span>Xem bản đồ và quản lý hệ thống cửa hàng.</span></a>
                <a class="quick-link" href="<?php echo $escape($appBaseUrl . '/admin/payments.php'); ?>"><strong>Thanh toán</strong><span>Tra cứu và quản lý giao dịch thanh toán.</span></a>
            </div>
        </section>
    </main>
</div>
</body>
</html>
