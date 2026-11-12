<?php
// File: huy_don_hang.php - Xử lý hủy đơn hàng
// hủy đơn hàng
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/admin_roles.php';

header('Content-Type: application/json; charset=utf-8');
// Đặt header để trả về JSON với mã UTF-8

// Phải đăng nhập
// Kiểm tra người dùng đã đăng nhập chưa, nếu chưa trả về lỗi
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Vui lòng đăng nhập để thực hiện hành động này"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'customer';
if (isAdminAreaRole($role)) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Chức năng này chỉ dành cho tài khoản khách hàng."]);
    exit;
}
// Lấy ID và vai trò của người dùng hiện tại từ session

$input = file_get_contents('php://input');
$data = json_decode($input, true);
// Đọc dữ liệu JSON gửi từ client và giải mã thành mảng PHP

if (!$data || !isset($data['order_id'])) {
    echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ"]);
    exit;
}
// Kiểm tra dữ liệu nhận được có chứa order_id hay không
$order_id = intval($data['order_id']);

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT user_id, status FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
// Truy vấn để lấy người dùng và trạng thái hiện tại của đơn
if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Không tìm thấy đơn hàng"]);
    exit;
}
$order = $res->fetch_assoc();
$stmt->close();

if (intval($order['user_id']) !== intval($user_id)) {
    echo json_encode(["status" => "error", "message" => "Bạn không có quyền hủy đơn này"]);
    exit;
}

// Kiểm tra trạng thái hiện tại
$status = $order['status'];
if ($status === 'Đã hủy' || $status === 'Canceled') {
    echo json_encode(["status" => "error", "message" => "Đơn đã được hủy trước đó"]);
    exit;
}
// Ngăn việc hủy lại nếu đơn đã ở trạng thái đã hủy

// Khách hàng chỉ được hủy khi đơn đang chờ xác nhận.
$allowed = ['Chờ xác nhận', 'Pending', '', null];
if (!in_array($status, $allowed, true)) {
    echo json_encode(["status" => "error", "message" => "Chỉ có thể hủy khi đơn ở trạng thái 'Chờ xác nhận'"]);
    exit;
}
// Cho phép hủy chỉ khi trạng thái cho phép (chưa xử lý)

// Thực hiện huỷ
$newStatus = 'Đã hủy';

$conn->begin_transaction();
try {
    $item_stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $item_stmt->bind_param("i", $order_id);
    $item_stmt->execute();
    $items = $item_stmt->get_result();

    $restock_stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    while ($item = $items->fetch_assoc()) {
        if ($item['product_id'] === null) {
            continue;
        }
        $quantity = (int) $item['quantity'];
        $product_id = (int) $item['product_id'];
        $restock_stmt->bind_param("ii", $quantity, $product_id);
        $restock_stmt->execute();
    }

    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND status = ?");
    $update_stmt->bind_param("sis", $newStatus, $order_id, $status);
    $update_stmt->execute();
    if ($update_stmt->affected_rows !== 1) {
        throw new RuntimeException("Trạng thái đơn hàng đã được thay đổi.");
    }

    $item_stmt->close();
    $restock_stmt->close();
    $update_stmt->close();
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Đã hủy đơn hàng thành công"]);
} catch (Throwable $e) {
   $conn->rollback();
   error_log('Order cancellation failed: ' . $e->getMessage());
   echo json_encode(["status" => "error", "message" => "Không thể hủy đơn hàng lúc này."]);
}

$conn->close();
?>
