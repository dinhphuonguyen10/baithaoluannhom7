<?php
$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$appBaseUrl = preg_replace('~/(?:client|admin|api)(?:/.*)?$~', '', $scriptDirectory);
$appBaseUrl = rtrim($appBaseUrl, '/');
$bodyClass = isset($bodyClass) && is_string($bodyClass) ? $bodyClass : '';

// Lấy danh mục sản phẩm từ CSDL để đồng nhất với Admin
$navCategories = [];
if (file_exists(__DIR__ . '/../config/db.php')) {
    require_once __DIR__ . '/../config/db.php';
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $navCatRes = $conn->query("SELECT id, name, slug FROM categories ORDER BY id ASC");
        if ($navCatRes) {
            while ($cRow = $navCatRes->fetch_assoc()) {
                $navCategories[] = $cRow;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : "ÉCRIN JEWELRY"; ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($appBaseUrl . '/assets/style.css?v=' . filemtime(__DIR__ . '/../assets/style.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <script>window.APP_BASE_URL = <?php echo json_encode($appBaseUrl, JSON_UNESCAPED_SLASHES); ?>;</script>
</head>

<body class="<?php echo htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8'); ?>">

    <!-- HEADER -->
    <header class="header">

        <!-- Logo ảnh -->
        <div class="logo">
            <a href="<?php echo htmlspecialchars($appBaseUrl . '/index.php', ENT_QUOTES, 'UTF-8'); ?>" aria-label="Écrin Jewelry - Trang chủ">
                <img src="<?php echo htmlspecialchars($appBaseUrl . '/assets/banner/logo.png', ENT_QUOTES, 'UTF-8'); ?>" alt="Écrin Jewelry" class="logo-img">
                <span class="logo-wordmark"><strong>ÉCRIN</strong><small>JEWELRY</small></span>
            </a>
        </div>
        <!-- Ô tìm kiếm -->
        <input type="search" placeholder="Bạn đang tìm món trang sức nào?" class="search" aria-label="Tìm kiếm sản phẩm">

        <!-- Đăng nhập / đăng ký -->
        <div class="auth-cart-wrapper" style="display: flex; align-items: center; gap: 15px;">
            <div class="auth" id="auth-container">
                <span class="auth-btn register-trigger" style="cursor: pointer;">Đăng ký</span> /
                <span class="auth-btn login-trigger" style="cursor: pointer;">Đăng nhập</span>
            </div>

            <div class="cart-wrapper">
                <button class="cart-button" aria-label="Giỏ hàng">
                    <svg class="cart-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.1 10.2a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 1.9-1.4L22 8H6" /><circle cx="10" cy="20" r="1.3" /><circle cx="18" cy="20" r="1.3" /></svg>
                    <span class="cart-text">Giỏ hàng</span>
                    <span class="cart-count">0</span>
                </button>
            </div>
        </div>


    </header>

    <div class="cart-overlay"></div>
    <aside class="cart-panel" aria-label="Giỏ hàng">
        <div class="cart-panel-header">
            <h3>Giỏ hàng của bạn</h3>
            <button class="cart-close" aria-label="Đóng giỏ hàng">×</button>
        </div>
        <div class="cart-items">
            <p class="empty-cart">Giỏ hàng đang trống. Thêm sản phẩm nào đó nhé!</p>
        </div>
        <div class="cart-summary">
            <div class="cart-summary-info">
                <span>Tổng số:</span>
                <span class="cart-total-count">0 sản phẩm</span>
            </div>
            <div class="cart-summary-price">
                <span>Tổng cộng:</span>
                <strong class="cart-total-price">0 đ</strong>
            </div>
            <button class="checkout-btn">Thanh toán</button>
        </div>
    </aside>

    <!-- MENU -->
    <nav class="menu" aria-label="Điều hướng chính">
        <a href="<?php echo htmlspecialchars($appBaseUrl . '/index.php', ENT_QUOTES, 'UTF-8'); ?>" style="font-weight: bold; color:#0d1b2a">Trang chủ</a>
        <div class="dropdown">
            <a href="javascript:void(0)" class="dropdown-toggle">Danh mục sản phẩm ▾</a>
            <div class="dropdown-menu">
                <?php if (!empty($navCategories)): ?>
                    <?php 
                    $pageMap = [
                        'bong-tai' => '/client/bong_tai.php',
                        'day-chuyen' => '/client/day_chuyen.php',
                        'nhan' => '/client/nhan.php',
                        'vong-tay' => '/client/vong_tay.php',
                        'set-qua-tang' => '/client/category.php?slug=set-qua-tang',
                    ];
                    foreach ($navCategories as $catItem): 
                        if (in_array($catItem['slug'], ['combo', 'suggest', 'featured'], true)) continue;
                        $catHref = isset($pageMap[$catItem['slug']]) 
                            ? ($appBaseUrl . $pageMap[$catItem['slug']])
                            : ($appBaseUrl . '/client/category.php?slug=' . urlencode($catItem['slug']));
                    ?>
                        <div class="dropdown-item">
                            <a href="<?php echo htmlspecialchars($catHref, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($catItem['name'], ENT_QUOTES, 'UTF-8'); ?></a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="dropdown-item">
                        <a href="<?php echo htmlspecialchars($appBaseUrl . '/client/bong_tai.php', ENT_QUOTES, 'UTF-8'); ?>">Bông tai</a>
                    </div>
                    <div class="dropdown-item">
                        <a href="<?php echo htmlspecialchars($appBaseUrl . '/client/day_chuyen.php', ENT_QUOTES, 'UTF-8'); ?>">Dây chuyền</a>
                    </div>
                    <div class="dropdown-item">
                        <a href="<?php echo htmlspecialchars($appBaseUrl . '/client/nhan.php', ENT_QUOTES, 'UTF-8'); ?>">Nhẫn</a>
                    </div>
                     <div class="dropdown-item">
                        <a href="<?php echo htmlspecialchars($appBaseUrl . '/client/vong_tay.php', ENT_QUOTES, 'UTF-8'); ?>">Vòng tay</a>
                    </div>
                     <div class="dropdown-item">
                         <a href="<?php echo htmlspecialchars($appBaseUrl . '/client/category.php?slug=set-qua-tang', ENT_QUOTES, 'UTF-8'); ?>">Set quà tặng</a>
                     </div>
                <?php endif; ?>
            </div>
        </div>

        <a href="<?php echo htmlspecialchars($appBaseUrl . '/client/tracuudh.php', ENT_QUOTES, 'UTF-8'); ?>">Tra cứu đơn hàng</a>
        <a href="<?php echo htmlspecialchars($appBaseUrl . '/client/htch.php', ENT_QUOTES, 'UTF-8'); ?>">Hệ thống cửa hàng</a>
        <a href="<?php echo htmlspecialchars($appBaseUrl . '/client/chat.php', ENT_QUOTES, 'UTF-8'); ?>">Chat với cửa hàng</a>
    </nav>
