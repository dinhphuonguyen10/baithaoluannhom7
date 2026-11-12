<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';

function respond(int $code, array $body): void {
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['status' => 'error', 'message' => 'Phương thức không được hỗ trợ.']);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    respond(400, ['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.']);
}

$action   = $data['action']   ?? '';
$username = trim($data['username'] ?? '');
$phone    = trim($data['phone']    ?? '');

// Chuẩn hoá SĐT: +84xxx → 0xxx
$phone = preg_replace('/[\s().-]/', '', $phone);
if (str_starts_with($phone, '+84')) {
    $phone = '0' . substr($phone, 3);
}
if ($phone !== '' && !preg_match('/^0[0-9]{9}$/', $phone)) {
    respond(400, ['status' => 'error', 'message' => 'Số điện thoại không hợp lệ (phải đúng 10 số, ví dụ: 0901234567).']);
}

// =============================================
// BƯỚC 1: Xác minh username + SĐT
// =============================================
if ($action === 'verify') {
    if ($username === '' || $phone === '') {
        respond(400, ['status' => 'error', 'message' => 'Vui lòng nhập đầy đủ thông tin.']);
    }

    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE username = ? AND phone = ? AND role = 'customer' LIMIT 1"
    );
    if (!$stmt) {
        respond(500, ['status' => 'error', 'message' => 'Lỗi hệ thống.']);
    }
    $stmt->bind_param('ss', $username, $phone);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        // Trả về lỗi chung, không tiết lộ thông tin nào đúng/sai
        respond(404, ['status' => 'error', 'message' => 'Tên đăng nhập hoặc số điện thoại không chính xác.']);
    }

    respond(200, ['status' => 'success', 'message' => 'Xác minh thành công.']);
}

// =============================================
// BƯỚC 2: Đặt lại mật khẩu
// =============================================
if ($action === 'reset') {
    $newPassword     = $data['password']         ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';

    if ($username === '' || $phone === '') {
        respond(400, ['status' => 'error', 'message' => 'Thiếu thông tin tài khoản.']);
    }
    if (
        strlen($newPassword) !== 8 ||
        !preg_match('/[a-z]/', $newPassword) ||
        !preg_match('/[A-Z]/', $newPassword) ||
        !preg_match('/[0-9]/', $newPassword) ||
        !preg_match('/[^a-zA-Z0-9]/', $newPassword)
    ) {
        respond(400, ['status' => 'error', 'message' => 'Mật khẩu mới phải đúng 8 ký tự, gồm chữ thường, chữ in hoa, số và ký tự đặc biệt (ví dụ: Abc@1234).']);
    }
    if ($newPassword !== $confirmPassword) {
        respond(400, ['status' => 'error', 'message' => 'Mật khẩu xác nhận không khớp.']);
    }

    // Kiểm tra lại tài khoản trước khi đổi
    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE username = ? AND phone = ? AND role = 'customer' LIMIT 1"
    );
    if (!$stmt) {
        respond(500, ['status' => 'error', 'message' => 'Lỗi hệ thống.']);
    }
    $stmt->bind_param('ss', $username, $phone);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        respond(404, ['status' => 'error', 'message' => 'Tài khoản không tồn tại.']);
    }

    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        "UPDATE users SET password = ? WHERE id = ?"
    );
    if (!$stmt) {
        respond(500, ['status' => 'error', 'message' => 'Lỗi hệ thống.']);
    }
    $stmt->bind_param('si', $hashed, $user['id']);
    $stmt->execute();
    $stmt->close();

    respond(200, ['status' => 'success', 'message' => 'Đặt lại mật khẩu thành công!']);
}

respond(400, ['status' => 'error', 'message' => 'Thao tác không hợp lệ.']);
