<?php
// Tác dụng: Điều hướng người dùng cấp nhân viên về bảng điều khiển tổng quan
require_once __DIR__ . '/../../include/admin_guard.php';
requireStaffRole();
header('Location: ' . applicationBaseUrl() . '/admin/index.php');
exit;
