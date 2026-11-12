<?php
/**
 * Admin Sidebar Layout - shared sidebar navigation component
 * Usage: include this file after starting the HTML body in each admin page.
 * The calling page should define $adminCurrentPage (e.g. 'products.php') before including.
 */
if (!isset($appBaseUrl)) {
    $appBaseUrl = applicationBaseUrl();
}
if (!isset($escape)) {
    $escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
// Tác dụng: Xác định cấp bậc quản trị viên từ phiên admin riêng biệt để hiển thị các menu được phân quyền
$isManager = isManagerRole($_SESSION['admin_role'] ?? $_SESSION['role'] ?? null);
$roleLabel  = $isManager ? 'Quản lý' : 'Nhân viên';
$currentScript = $adminCurrentPage ?? basename($_SERVER['SCRIPT_NAME'] ?? '');

$sideMenu = [
    ['icon' => '▦', 'label' => 'Tổng quan',                    'href' => '/admin/index.php',     'file' => 'index.php'],
    ['icon' => '◇', 'label' => 'Quản lý sản phẩm',             'href' => '/admin/products.php',  'file' => 'products.php'],
    ['icon' => '▧', 'label' => 'Quản lý danh mục',             'href' => '/admin/categories.php','file' => 'categories.php'],
    ['icon' => '♙', 'label' => 'Quản lý tài khoản nhân viên', 'href' => '/admin/users.php',     'file' => 'users.php', 'manager_only' => true],
    ['icon' => '▤', 'label' => 'Quản lý đơn hàng',             'href' => '/admin/orders.php',    'file' => 'orders.php'],
    ['icon' => '☏', 'label' => 'Quản lý chat',                 'href' => '/admin/chat.php',      'file' => 'chat.php'],
    ['icon' => '▣', 'label' => 'Quản lý thanh toán',           'href' => '/admin/payments.php',  'file' => 'payments.php'],
    ['icon' => '▥', 'label' => 'Báo cáo và thống kê',          'href' => '/admin/reports.php',   'file' => 'reports.php', 'manager_only' => true],
    ['icon' => '⌖', 'label' => 'Cửa hàng',                     'href' => '/admin/stores.php',    'file' => 'stores.php'],
];
?>
<aside class="sidebar" id="sidebar">
    <a class="brand" href="<?php echo $escape($appBaseUrl . '/index.php'); ?>" aria-label="ÉCRIN trang chủ">
        <img class="brand-logo" src="<?php echo $escape($appBaseUrl . '/admin/assets/ecrin-logo.png'); ?>" alt="ÉCRIN Jewelry">
    </a>
    <nav class="side-nav" aria-label="Điều hướng giao diện quản trị">
        <?php foreach ($sideMenu as $item): ?>
            <?php $disabled = !empty($item['manager_only']) && !$isManager; ?>
            <?php $active   = ($currentScript === $item['file']); ?>
            <?php if ($disabled): ?>
                <span class="nav-item is-disabled" aria-disabled="true">
            <?php else: ?>
                <a href="<?php echo $escape($appBaseUrl . $item['href']); ?>" class="nav-item<?php echo $active ? ' active' : ''; ?>">
            <?php endif; ?>
                <span class="nav-icon"><?php echo $escape($item['icon']); ?></span>
                <span><?php echo $escape($item['label']); ?></span>
                <?php if ($active): ?><span class="active-dot"></span><?php endif; ?>
            <?php echo $disabled ? '</span>' : '</a>'; ?>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-bottom">
        <a href="<?php echo $escape($appBaseUrl . '/admin/logout.php'); ?>" class="logout"><span>⇥</span> Đăng xuất</a>
    </div>
</aside>
