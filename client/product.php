<?php
require_once __DIR__ . '/../config/db.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;

if ($productId !== false && $productId !== null && $productId > 0) {
    $stmt = $conn->prepare('SELECT * FROM view_product_details WHERE id = ? LIMIT 1');
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị truy vấn chi tiết sản phẩm: ' . $conn->error);
    }

    $stmt->bind_param('i', $productId);
    if (!$stmt->execute()) {
        throw new RuntimeException('Không thể tải chi tiết sản phẩm: ' . $stmt->error);
    }
    $product = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
}

if ($product === null) {
    http_response_code(404);
}

$pageTitle = $product
    ? $product['name'] . ' - ÉCRIN JEWELRY'
    : 'Không tìm thấy sản phẩm - ÉCRIN JEWELRY';
include __DIR__ . '/../include/header.php';

$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$descriptionByCategory = [
    'bong-tai' => 'Một thiết kế bông tai thanh lịch, dễ kết hợp cùng trang phục thường ngày hoặc những dịp đặc biệt.',
    'day-chuyen' => 'Mẫu dây chuyền tinh tế giúp hoàn thiện phong cách và là lựa chọn ý nghĩa để dành tặng người thân.',
    'nhan' => 'Thiết kế nhẫn trang nhã, phù hợp để tạo điểm nhấn cho phong cách riêng hoặc làm quà tặng.',
    'vong-tay' => 'Mẫu vòng tay duyên dáng, dễ phối cùng nhiều phong cách và thích hợp làm món quà tinh tế.',
    'set-qua-tang' => 'Set quà tặng được chuẩn bị chỉn chu, phù hợp dành tặng người thân yêu trong những dịp đáng nhớ.',
];
?>

<main class="product-detail-page">
    <div class="breadcrumb-container">
        <a href="<?php echo $escape($appBaseUrl . '/index.php'); ?>">Trang chủ</a>
        <span class="separator">&gt;</span>
        <?php if ($product): ?>
            <a href="<?php echo $escape($appBaseUrl . '/client/category.php?slug=' . rawurlencode($product['category'])); ?>">
                <?php echo $escape($product['category_name']); ?>
            </a>
            <span class="separator">&gt;</span>
            <strong class="current"><?php echo $escape($product['name']); ?></strong>
        <?php else: ?>
            <strong class="current">Không tìm thấy sản phẩm</strong>
        <?php endif; ?>
    </div>

    <?php if ($product): ?>
        <?php
        $imagePath = (string) $product['image'];
        $imageUrl = preg_match('~^https?://~i', $imagePath)
            ? $imagePath
            : $appBaseUrl . '/' . ltrim($imagePath, '/');
        $stock = (int) $product['stock'];
        $description = $descriptionByCategory[$product['category']] ?? 'Một thiết kế được ÉCRIN tuyển chọn để bạn dễ dàng tìm thấy món trang sức phù hợp với phong cách của mình.';
        ?>
        <section class="product-detail-card product-card" data-db-id="<?php echo (int) $product['id']; ?>" aria-labelledby="product-name">
            <div class="product-detail-image-wrap">
                <img
                    class="product-detail-image product-image"
                    src="<?php echo $escape($imageUrl); ?>"
                    alt="<?php echo $escape($product['name']); ?>"
                    onerror="this.src='<?php echo $escape($appBaseUrl . '/assets/banner/logo.png'); ?>';"
                >
            </div>
            <div class="product-detail-info">
                <p class="product-detail-eyebrow"><?php echo $escape($product['category_name']); ?> · ÉCRIN JEWELRY</p>
                <h1 id="product-name"><?php echo $escape($product['name']); ?></h1>
                <div class="product-detail-price product-price">
                    <span class="price-sale"><?php echo number_format((float) $product['price_sale'], 0, ',', '.'); ?><sup>đ</sup></span>
                    <?php if ((float) $product['price_original'] > 0): ?>
                        <span class="price-original"><?php echo number_format((float) $product['price_original'], 0, ',', '.'); ?><sup>đ</sup></span>
                    <?php endif; ?>
                </div>

                <p class="product-detail-description"><?php echo $escape($description); ?></p>

                <dl class="product-detail-facts">
                    <div>
                        <dt>Mã sản phẩm</dt>
                        <dd>EC-<?php echo str_pad((string) $product['id'], 5, '0', STR_PAD_LEFT); ?></dd>
                    </div>
                    <div>
                        <dt>Danh mục</dt>
                        <dd><?php echo $escape($product['category_name']); ?></dd>
                    </div>
                    <div>
                        <dt>Tình trạng</dt>
                        <dd class="<?php echo $stock > 0 ? 'product-detail-stock' : 'product-detail-out-of-stock'; ?>">
                            <?php echo $stock > 0 ? 'Còn hàng (' . $stock . ')' : 'Tạm hết hàng'; ?>
                        </dd>
                    </div>
                    <?php if ((int) $product['sold_count'] > 0): ?>
                        <div>
                            <dt>Đã bán</dt>
                            <dd><?php echo (int) $product['sold_count']; ?> sản phẩm</dd>
                        </div>
                    <?php endif; ?>
                </dl>

                <?php if (!empty($product['gift'])): ?>
                    <p class="product-detail-gift">🎁 <?php echo $escape($product['gift']); ?></p>
                <?php endif; ?>

                <div class="product-detail-actions">
                    <button class="add-to-cart" <?php echo $stock <= 0 ? 'disabled' : ''; ?>>
                        <?php echo $stock > 0 ? 'Thêm vào giỏ hàng' : 'Tạm hết hàng'; ?>
                    </button>
                    <a class="product-detail-back" href="<?php echo $escape($appBaseUrl . '/client/category.php?slug=' . rawurlencode($product['category'])); ?>">
                        Tiếp tục xem danh mục
                    </a>
                </div>

                <div class="product-detail-note">
                    <strong>ÉCRIN đồng hành cùng bạn</strong>
                    <p>Liên hệ cửa hàng nếu bạn cần tư vấn thêm về sản phẩm hoặc đơn hàng.</p>
                    <a href="<?php echo $escape($appBaseUrl . '/client/chat.php'); ?>">Chat với cửa hàng</a>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="product-detail-not-found">
            <h1>Không tìm thấy sản phẩm</h1>
            <p>Sản phẩm có thể đã được gỡ khỏi danh mục hoặc đường dẫn không chính xác.</p>
            <a class="view-all-btn" href="<?php echo $escape($appBaseUrl . '/index.php'); ?>">Quay lại trang chủ</a>
        </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../include/footer.php'; ?>
