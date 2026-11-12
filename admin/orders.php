<?php
require_once __DIR__ . '/../include/admin_guard.php';
$appBaseUrl = applicationBaseUrl();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
// Tác dụng: Xác thực phiên và cấp quyền xử lý đơn hàng cho quản trị viên
$isManager  = isManagerRole($_SESSION['admin_role'] ?? $_SESSION['role'] ?? null);
$roleLabel  = $isManager ? 'Quản lý' : 'Nhân viên';
$adminUsername = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? '';
$adminCurrentPage = 'orders.php';

$orders = [
    ['id' => '#1001', 'customer' => 'Nguyễn Thị Ánh', 'phone' => '0912.345.678', 'total' => '890.000đ', 'status' => 'Chờ xác nhận', 'status_class' => 'status-pending', 'date' => '10/11/2026 14:30'],
    ['id' => '#1002', 'customer' => 'Trần Văn Bình',  'phone' => '0988.112.233', 'total' => '1.250.000đ', 'status' => 'Đang giao',   'status_class' => 'status-shipping', 'date' => '10/11/2026 11:15'],
    ['id' => '#1003', 'customer' => 'Lê Hoàng Yến',   'phone' => '0905.778.899', 'total' => '520.000đ',   'status' => 'Đã giao',     'status_class' => 'status-completed', 'date' => '09/11/2026 16:40'],
    ['id' => '#1004', 'customer' => 'Phạm Minh Đức',  'phone' => '0934.556.677', 'total' => '2.100.000đ', 'status' => 'Đã giao',     'status_class' => 'status-completed', 'date' => '08/11/2026 09:20'],
    ['id' => '#1005', 'customer' => 'Vũ Thu Trang',   'phone' => '0971.223.344', 'total' => '450.000đ',   'status' => 'Đã hủy',      'status_class' => 'status-cancelled', 'date' => '07/11/2026 18:05'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý đơn hàng - ÉCRIN Jewelry</title>
    <link rel="stylesheet" href="<?php echo $escape($appBaseUrl . '/admin/style.css'); ?>">
    <style>
        .proto-panel { margin: 20px 0; padding: 22px; border: 1px solid var(--line); border-radius: 12px; background: #fff; }
        .proto-panel h2 { margin-top: 0; font-size: 1.1rem; }
        .proto-actions { display: flex; flex-wrap: wrap; gap: 10px; margin: 14px 0; }
        .proto-actions button, .proto-panel button { padding: 9px 14px; border: 0; border-radius: 7px; background: var(--navy); color: #fff; font: inherit; font-weight: 600; cursor: pointer; }
        .proto-actions button:disabled, .proto-panel button:disabled { background: #d7dce2; color: #657180; cursor: not-allowed; }
        .proto-panel input, .proto-panel select { padding: 9px; border: 1px solid #d5dbe2; border-radius: 7px; font: inherit; }
        .proto-table-wrap { overflow-x: auto; }
        .proto-table { width: 100%; border-collapse: collapse; }
        .proto-table th, .proto-table td { padding: 12px; border-bottom: 1px solid var(--line); text-align: left; }
        .proto-table th { color: var(--muted); font-size: .85rem; }

        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-pending   { background: #fef0c7; color: #b54708; }
        .status-shipping  { background: #e0f2fe; color: #026aa2; }
        .status-completed { background: #ecfdf3; color: #027a48; }
        .status-cancelled { background: #fee4e2; color: #b42318; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../include/admin_sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="breadcrumb">Trang chủ <span>/</span> <strong>Quản lý đơn hàng</strong></div>
            <div class="admin-profile">
                <div class="avatar"><?php echo $isManager ? 'QL' : 'NV'; ?></div>
                <div class="admin-info"><strong><?php echo $escape($adminUsername); ?></strong><small><?php echo $escape($roleLabel); ?></small></div>
            </div>
        </header>
        <section class="page-content">
            <div class="welcome-row">
                <div>
                    <h1>Quản lý đơn hàng</h1>
                    <p class="page-subtitle">Theo dõi, xác nhận và xử lý đơn hàng của khách.</p>
                </div>
            </div>
            <section class="proto-panel" aria-labelledby="orders-title">
                <div class="proto-actions">
                    <input type="search" placeholder="Tìm mã đơn, tên khách..." disabled>
                    <select disabled>
                        <option>Tất cả trạng thái</option>
                        <option>Chờ xác nhận</option>
                        <option>Đang giao</option>
                        <option>Đã giao</option>
                        <option>Đã hủy</option>
                    </select>
                </div>
                <div class="proto-table-wrap">
                    <table class="proto-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Số điện thoại</th>
                                <th>Tổng tiền</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><strong><?php echo $escape($o['id']); ?></strong></td>
                                <td><?php echo $escape($o['customer']); ?></td>
                                <td><?php echo $escape($o['phone']); ?></td>
                                <td><strong><?php echo $escape($o['total']); ?></strong></td>
                                <td style="color: var(--muted); font-size: 13px;"><?php echo $escape($o['date']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $o['status_class']; ?>">
                                        <?php echo $escape($o['status']); ?>
                                    </span>
                                </td>
                                <td><button type="button" disabled>Chi tiết</button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </main>
</div>
</body>
</html>
