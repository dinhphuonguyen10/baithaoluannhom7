<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';
function respond(int $statusCode, array $body): void
{
    http_response_code($statusCode);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['status' => 'error', 'message' => 'Phương thức yêu cầu không hợp lệ.']);
}
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    respond(400, ['status' => 'error', 'message' => 'Dữ liệu đăng ký không hợp lệ.']);
}
// Lấy và kiểm tra thông tin đầu vào
$username = isset($data['username']) && is_string($data['username'])
    ? trim($data['username'])
    : '';
$password = isset($data['password']) && is_string($data['password'])
    ? $data['password']
    : '';
$phone = isset($data['phone']) && is_string($data['phone'])
    ? trim($data['phone'])
    : '';
// Chuẩn hoá SĐT: +84xxx → 0xxx
$phone = preg_replace('/[\s().-]/', '', $phone);
if ($phone !== '' && !preg_match('/^0[0-9]{9}$/', $phone)) {
    respond(400, ['status' => 'error', 'message' => 'Số điện thoại không hợp lệ (phải đúng 10 số, ví dụ: 0901234567).']);
}
// Validate
if ($username === '' || strlen($username) > 100) {
    respond(400, ['status' => 'error', 'message' => 'Tên tài khoản không hợp lệ (tối đa 100 ký tự).']);
}
if (!preg_match('/^0[0-9]{9}$/', $phone)) {
    respond(400, ['status' => 'error', 'message' => 'Số điện thoại không hợp lệ (phải đúng 10 số, ví dụ: 0901234567).']);
}
if (
    strlen($password) !== 8 ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[^a-zA-Z0-9]/', $password)
) {
    respond(400, ['status' => 'error', 'message' => 'Mật khẩu phải đúng 8 ký tự, gồm chữ thường, chữ in hoa, số và ký tự đặc biệt (ví dụ: Abc@1234).']);
}
// Kiểm tra SĐT đã được dùng chưa
$stmt = $conn->prepare("SELECT id FROM users WHERE phone = ? AND role = 'customer' LIMIT 1");
if (!$stmt) {
    respond(500, ['status' => 'error', 'message' => 'Không thể xử lý yêu cầu lúc này.']);
}
$stmt->bind_param('s', $phone);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    $stmt->close();
    respond(409, ['status' => 'error', 'message' => 'Số điện thoại đã được liên kết với tài khoản khác.']);
}
$stmt->close();
// Tạo tài khoản
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, password, phone, role) VALUES (?, ?, ?, 'customer')");
if (!$stmt) {
    error_log('Customer registration: failed to prepare insert: ' . $conn->error);
    respond(500, ['status' => 'error', 'message' => 'Không thể tạo tài khoản lúc này.']);
}
$stmt->bind_param('sss', $username, $passwordHash, $phone);
if (!$stmt->execute()) {
    $duplicate = $stmt->errno === 1062;
    error_log('Customer registration failed: ' . $stmt->error);
    $stmt->close();
    if ($duplicate) {
        respond(409, ['status' => 'error', 'message' => 'Tên tài khoản đã được sử dụng.']);
    }
    respond(500, ['status' => 'error', 'message' => 'Không thể tạo tài khoản lúc này.']);
}
$stmt->close();
$conn->close();
respond(201, ['status' => 'success', 'message' => 'Đăng ký tài khoản thành công. Vui lòng đăng nhập.']);