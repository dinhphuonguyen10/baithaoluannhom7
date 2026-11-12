<?php
function isAdminAreaRole(?string $role): bool
{
    return in_array($role, ['admin', 'manager', 'staff'], true);
}

function isManagerRole(?string $role): bool
{
    return in_array($role, ['admin', 'manager'], true);
}

function isStaffRole(?string $role): bool
{
    return $role === 'staff';
}

// Tác dụng: Yêu cầu vai trò nhân viên để tiếp tục thao tác trên trang nghiệp vụ
function requireStaffRole(): void
{
    $currentRole = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? null;
    if (!isStaffRole($currentRole)) {
        header('Location: ' . adminRoleHomeUrl($currentRole ?? ''));
        exit;
    }
}

// Tác dụng: Yêu cầu vai trò quản lý (manager/admin) đối với các tính năng quản trị cao cấp
function requireManagerRole(): void
{
    $currentRole = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? null;
    if (!isManagerRole($currentRole)) {
        header('Location: ' . adminRoleHomeUrl($currentRole ?? ''));
        exit;
    }
}

function applicationBaseUrl(): string
{
    $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $appBaseUrl = preg_replace('~/(?:admin|api)(?:/.*)?$~', '', $scriptDirectory);

    return rtrim($appBaseUrl, '/');
}

function adminRoleHomeUrl(string $role): string
{
    $dashboard = isAdminAreaRole($role)
        ? '/admin/index.php'
        : '/admin/login.php';

    return applicationBaseUrl() . $dashboard;
}
