<?php
// Tác dụng: Điều hướng người dùng cấp quản lý về bảng điều khiển tổng quan
require_once __DIR__ . '/../../include/admin_guard.php';
requireManagerRole();

$role = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'manager';
header('Location: ' . adminRoleHomeUrl($role));
exit;
