<?php
$categoryTitle = "Nhẫn";
$categoryKey = "nhan";
$pageTitle = $categoryTitle . " - ÉCRIN JEWELRY";
include __DIR__ . '/../include/header.php';
?>

<main class="category-page">
    <div class="breadcrumb-container">
        <a href="../index.php">Trang chủ</a> <span class="separator">></span>
        <a href="javascript:void(0)">Danh mục sản phẩm</a> <span class="separator">></span>
        <strong class="current"><?php echo htmlspecialchars($categoryTitle, ENT_QUOTES, 'UTF-8'); ?></strong>
    </div>
    <div class="category-layout">
        <aside class="filter-sidebar">
            <div class="filter-group">
                <h3>KHOẢNG GIÁ (VNĐ)</h3>
                <div class="price-filter">
                    <input type="number" placeholder="Từ" min="0" aria-label="Giá thấp nhất">
                    <span>-</span>
                    <input type="number" placeholder="Đến" min="0" aria-label="Giá cao nhất">
                </div>
                <button class="apply-filter-btn">Áp dụng</button>
            </div>
            <div class="filter-group">
                <h3>GỢI Ý CHỌN NHẪN</h3>
                <p>Kiểm tra kỹ kích thước nhẫn và thông tin chất liệu trước khi đặt hàng.</p>
            </div>
        </aside>
        <section class="category-main">
            <div class="category-header">
                <h1 class="page-title-left">NHẪN</h1>
                <div class="sorting-options">
                    <label for="product-sort">Sắp xếp:</label>
                    <select id="product-sort">
                        <option>Phổ biến nhất</option>
                        <option>Mới cập nhật</option>
                        <option>Bán chạy nhất</option>
                        <option>Giá từ thấp đến cao</option>
                        <option>Giá từ cao đến thấp</option>
                    </select>
                </div>
            </div>
            <div class="product-grid category-grid" id="category-products-container" data-db-category="<?php echo htmlspecialchars($categoryKey, ENT_QUOTES, 'UTF-8'); ?>"></div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../include/footer.php'; ?>
