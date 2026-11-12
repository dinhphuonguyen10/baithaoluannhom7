<?php
// Tác dụng: Xóa bỏ phiên đăng nhập của quản trị viên và điều hướng về trang đăng nhập admin mà không làm mất phiên duyệt web của khách hàng
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

unset($_SESSION['admin_user_id'], $_SESSION['admin_username'], $_SESSION['admin_role'], $_SESSION['staff_management_csrf']);

require_once __DIR__ . '/../include/admin_roles.php';
header('Location: ' . applicationBaseUrl() . '/admin/login.php');
exit;
