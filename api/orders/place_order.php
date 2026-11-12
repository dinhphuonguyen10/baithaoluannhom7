<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function respondWithError($message, $statusCode = 400)
{
    http_response_code($statusCode);
    echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondWithError('Phương thức yêu cầu không hợp lệ.', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$customer = is_array($data['customer'] ?? null) ? $data['customer'] : [];
$cart = $data['cart'] ?? null;

if (!is_array($cart) || $cart === []) {
    respondWithError('Giỏ hàng trống hoặc không hợp lệ.');
}

$full_name = trim((string) ($customer['name'] ?? ''));
$phone = trim((string) ($customer['phone'] ?? ''));
$address = trim((string) ($customer['address'] ?? ''));

if ($full_name === '' || $phone === '' || $address === '') {
    respondWithError('Vui lòng cung cấp đầy đủ thông tin giao hàng.');
}

$quantities = [];
foreach ($cart as $item) {
    if (!is_array($item)) {
        respondWithError('Dữ liệu sản phẩm trong giỏ hàng không hợp lệ.');
    }

    $product_id = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
    if ($product_id === false || $product_id < 1 || $quantity === false || $quantity < 1 || $quantity > 99) {
        respondWithError('Một sản phẩm trong giỏ hàng không hợp lệ. Vui lòng xóa sản phẩm cũ và thêm lại từ danh sách.');
    }

    $quantities[$product_id] = ($quantities[$product_id] ?? 0) + $quantity;
}

try {
    $conn->begin_transaction();

    $product_stmt = $conn->prepare(
        'SELECT id, name, price_sale, image, stock FROM products WHERE id = ? FOR UPDATE'
    );
    $stock_stmt = $conn->prepare(
        'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?'
    );
    $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    $address_id = null;
    if ($user_id !== null) {
        $address_stmt = $conn->prepare(
            'SELECT id FROM addresses WHERE user_id = ? AND recipient_name = ? AND address = ? AND phone = ? LIMIT 1'
        );
        $address_stmt->bind_param('isss', $user_id, $full_name, $address, $phone);
        $address_stmt->execute();
        $saved_address = $address_stmt->get_result()->fetch_assoc();
        if ($saved_address) {
            $address_id = (int) $saved_address['id'];
        }
        $address_stmt->close();
    }

    $order_stmt = $conn->prepare(
        "INSERT INTO orders (user_id, address_id, full_name, phone, address, total_price, payment_method)
         VALUES (?, ?, ?, ?, ?, ?, 'COD')"
    );
    $item_stmt = $conn->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, quantity, price, image)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    $products = [];
    $total_price = 0;
    foreach ($quantities as $product_id => $quantity) {
        $product_stmt->bind_param('i', $product_id);
        $product_stmt->execute();
        $product = $product_stmt->get_result()->fetch_assoc();

        if (!$product) {
            throw new DomainException('Không tìm thấy sản phẩm trong giỏ hàng.');
        }
        if ((int) $product['stock'] < $quantity) {
            throw new DomainException('Sản phẩm "' . $product['name'] . '" không đủ số lượng tồn kho.');
        }

        $product['quantity'] = $quantity;
        $products[] = $product;
        $total_price += (float) $product['price_sale'] * $quantity;
    }

    $total_price = round($total_price, 2);
    $order_stmt->bind_param('iisssd', $user_id, $address_id, $full_name, $phone, $address, $total_price);
    $order_stmt->execute();
    $order_id = $conn->insert_id;

    foreach ($products as $product) {
        $product_id = (int) $product['id'];
        $quantity = (int) $product['quantity'];
        $price = (float) $product['price_sale'];
        $product_name = $product['name'];
        $image = $product['image'];

        $stock_stmt->bind_param('iii', $quantity, $product_id, $quantity);
        $stock_stmt->execute();
        if ($stock_stmt->affected_rows !== 1) {
            throw new DomainException('Số lượng tồn kho vừa thay đổi. Vui lòng thử đặt hàng lại.');
        }

        $item_stmt->bind_param('iisids', $order_id, $product_id, $product_name, $quantity, $price, $image);
        $item_stmt->execute();
    }

    $conn->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'Đặt hàng thành công!',
        'order_id' => $order_id
    ], JSON_UNESCAPED_UNICODE);
} catch (DomainException $e) {
    $conn->rollback();
    respondWithError($e->getMessage());
} catch (Throwable $e) {
    $conn->rollback();
    error_log('Order placement failed: ' . $e->getMessage());
    respondWithError('Không thể xử lý đơn hàng lúc này. Vui lòng thử lại sau.', 500);
}

$conn->close();
?>
