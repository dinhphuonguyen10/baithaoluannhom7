<?php
require_once __DIR__ . '/../include/admin_guard.php';
requireManagerRole();
// Tác dụng: Xác thực phiên và cấp quyền xem thống kê, báo cáo cho quản lý
$appBaseUrl = applicationBaseUrl();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$adminUsername = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? '';
$isManager  = true; // only managers reach here
$roleLabel  = 'Quản lý';
$adminCurrentPage = 'reports.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Báo cáo - ÉCRIN Jewelry</title>
    <link rel="stylesheet" href="<?php echo $escape($appBaseUrl . '/admin/style.css'); ?>">
    <style>
        .proto-panel { margin: 20px 0; padding: 22px; border: 1px solid var(--line); border-radius: 12px; background: #fff; }
        .proto-panel h2 { margin-top: 0; }
        .proto-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
        .proto-card { padding: 18px; border-radius: 10px; background: var(--background); }
        .proto-card span, .proto-card strong { display: block; }
        .proto-card span { color: var(--muted); }
        .proto-card strong { margin-top: 8px; color: var(--navy); font-size: 1.35rem; }
        .proto-panel select, .proto-panel button { padding: 9px 12px; border: 1px solid #d5dbe2; border-radius: 7px; background: #eef0f3; color: #657180; cursor: not-allowed; font: inherit; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../include/admin_sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="breadcrumb">Trang chủ <span>/</span> <strong>Báo cáo và thống kê</strong></div>
            <div class="admin-profile">
                <div class="avatar">QL</div>
                <div class="admin-info"><strong><?php echo $escape($adminUsername); ?></strong><small>Quản lý</small></div>
            </div>
        </header>
        <section class="page-content">
            <div class="welcome-row"><div><h1>Báo cáo và thống kê</h1></div></div>
            <section class="proto-panel">
                <h2>Bộ lọc báo cáo</h2>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <select disabled><option>Tháng này</option><option>Tháng trước</option></select>
                    <select disabled><option>Tất cả danh mục</option></select>
                    <button type="button" disabled>Xuất báo cáo</button>
                </div>
            </section>
            <section class="proto-panel">
                <h2>Thống kê tổng quan</h2>
                <div class="proto-cards">
                    <div class="proto-card"><span>Doanh thu</span><strong>—</strong></div>
                    <div class="proto-card"><span>Đơn hàng</span><strong>—</strong></div>
                    <div class="proto-card"><span>Khách hàng mới</span><strong>—</strong></div>
                    <div class="proto-card"><span>Sản phẩm bán chạy</span><strong>—</strong></div>
                </div>
            </section>
        </section>
    </main>
</div>
</body>
</html>