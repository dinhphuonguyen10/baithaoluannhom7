<?php
// Tác dụng: Kiểm soát quyền truy cập khu vực quản trị, ngăn chặn người dùng chưa xác thực hoặc không có thẩm quyền
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/admin_roles.php';

// Tác dụng: Xác minh phiên làm việc quản trị độc lập (admin/manager/staff)
if (!isset($_SESSION['admin_user_id']) || !isAdminAreaRole($_SESSION['admin_role'] ?? null)) {
    header('Location: ' . applicationBaseUrl() . '/admin/login.php');
    exit;
}

// Tác dụng: Đồng bộ vai trò admin sang cấp quản lý (manager) theo phân cấp hệ thống
if (($_SESSION['admin_role'] ?? null) === 'admin') {
    $_SESSION['admin_role'] = 'manager';
}
