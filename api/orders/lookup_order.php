<?php
// api/orders/lookup_order.php - Truy vấn và trả về thông tin đơn hàng dựa trên ID hoặc số điện thoại
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$query_val = trim((string) ($_GET['query'] ?? ''));

if (empty($query_val)) {
    echo json_encode(["status" => "error", "message" => "Vui lòng nhập mã đơn hàng hoặc số điện thoại"]);
    exit;
}

// Tìm tất cả đơn hàng khớp với ID hoặc Số điện thoại
$sql = "SELECT * FROM orders WHERE id = ? OR phone = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $query_val, $query_val);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $order_id = $row['id'];
    
    // Lấy chi tiết sản phẩm cho từng đơn hàng
    $sql_items = "SELECT * FROM order_items WHERE order_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    
    $items = [];
    while ($item_row = $result_items->fetch_assoc()) {
        $items[] = $item_row;
    }
    
    $row['items'] = $items;
    $orders[] = $row;
}

if (!empty($orders)) {
    echo json_encode([
        "status" => "success",
        "orders" => $orders
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Không tìm thấy đơn hàng nào phù hợp!"]);
}

$conn->close();
?>
