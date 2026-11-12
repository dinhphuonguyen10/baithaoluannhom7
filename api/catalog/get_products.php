<?php
// api/catalog/get_products.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';

$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// 1. Sản phẩm nổi bật: Hệ thống tự động tính toán từ SQL VIEW (lượt bán cao nhất / mới nhất)
if ($category === 'featured') {
    $result = $conn->query("SELECT * FROM view_featured_products");
}
// 2. Gợi ý cho bạn: Hệ thống tự động đề xuất từ SQL VIEW
elseif ($category === 'suggest') {
    $result = $conn->query("SELECT * FROM view_suggested_products");
}
// 3. Danh mục sản phẩm cụ thể (Combo, Nhẫn, Bông tai, Dây chuyền, v.v.)
elseif ($category !== '') {
    $stmt = $conn->prepare("SELECT * FROM view_product_details WHERE category = ? OR category_name = ? OR CAST(category_id AS CHAR) = ? ORDER BY id DESC");
    $stmt->bind_param("sss", $category, $category, $category);
    $stmt->execute();
    $result = $stmt->get_result();
}
// 4. Mặc định lấy tất cả sản phẩm
else {
    $result = $conn->query("SELECT * FROM view_product_details ORDER BY id DESC");
}

$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['stock'] = (int)($row['stock'] ?? 0);
        $row['sold_count'] = (int)($row['sold_count'] ?? 0);
        $row['price_sale_raw'] = (float)$row['price_sale'];
        $row['price_original_raw'] = (float)($row['price_original'] ?? 0);

        // Định dạng giá hiển thị
        $row['price_sale'] = number_format((float)$row['price_sale'], 0, ',', '.');
        $row['price_original'] = $row['price_original_raw'] > 0 ? number_format($row['price_original_raw'], 0, ',', '.') : null;

        $products[] = $row;
    }
}

echo json_encode($products, JSON_UNESCAPED_UNICODE);
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
