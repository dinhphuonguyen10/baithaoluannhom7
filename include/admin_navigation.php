<?php
// Tác dụng: Xây dựng menu điều hướng quản trị dựa trên phiên và quyền hạn của quản trị viên hiện tại
$adminBaseUrl = applicationBaseUrl();
$adminRole = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? '';
$adminUsername = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? '';
$adminPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$adminIsManager = isManagerRole($adminRole);
$adminHome = '/admin/index.php';
$adminLinks = [
    ['index.php', 'Tổng quan', '/admin/index.php', false],
    ['products.php', 'Quản lý sản phẩm', '/admin/products.php', false],
    ['categories.php', 'Quản lý danh mục', '/admin/categories.php', false],
    ['users.php', 'Quản lý tài khoản nhân viên', '/admin/users.php', true],
    ['orders.php', 'Quản lý đơn hàng', '/admin/orders.php', false],
    ['chat.php', 'Quản lý chat', '/admin/chat.php', false],
    ['payments.php', 'Quản lý thanh toán', '/admin/payments.php', false],
    ['reports.php', 'Báo cáo và thống kê', '/admin/reports.php', true],
];
?>
<header class="admin-topbar">
    <a class="admin-brand" href="<?php echo htmlspecialchars($adminBaseUrl . $adminHome, ENT_QUOTES, 'UTF-8'); ?>">
        <img src="<?php echo htmlspecialchars($adminBaseUrl . '/assets/banner/logo.png', ENT_QUOTES, 'UTF-8'); ?>" alt="">
        <span>ÉCRIN <small>ADMINISTRATION</small></span>
    </a>
    <div class="admin-account">
        <span><?php echo htmlspecialchars($adminUsername, ENT_QUOTES, 'UTF-8'); ?> · <?php echo isManagerRole($adminRole) ? 'Quản lí' : 'Nhân viên'; ?></span>
        <a href="<?php echo htmlspecialchars($adminBaseUrl . '/index.php', ENT_QUOTES, 'UTF-8'); ?>">Xem cửa hàng</a>
        <a href="<?php echo htmlspecialchars($adminBaseUrl . '/admin/logout.php', ENT_QUOTES, 'UTF-8'); ?>">Đăng xuất</a>
    </div>
</header>
<nav class="admin-nav" aria-label="Điều hướng quản trị">
    <?php foreach ($adminLinks as [$file, $label, $path, $managerOnly]): ?>
        <?php $disabled = $managerOnly && !$adminIsManager; ?>
        <?php if ($disabled): ?>
            <span class="is-disabled" aria-disabled="true"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php else: ?>
            <a href="<?php echo htmlspecialchars($adminBaseUrl . $path, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $adminPage === $file ? ' aria-current="page"' : ''; ?>>
                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
