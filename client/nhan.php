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
        <!-- SIDEBAR BỘ LỌC PHONG CÁCH HIỆN ĐẠI -->
        <aside class="filter-sidebar" aria-label="Bộ lọc tìm kiếm sản phẩm">
            <div class="filter-header">
                <svg class="filter-header-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                <h2 class="filter-header-title">BỘ LỌC TÌM KIẾM</h2>
            </div>

            <!-- NHÓM 1: KHOẢNG GIÁ -->
            <div class="filter-group">
                <h3 class="filter-group-title">KHOẢNG GIÁ</h3>
                <div class="price-input-wrapper">
                    <div class="price-input-field">
                        <span class="currency-tag">₫</span>
                        <input type="text" id="filter-price-from" placeholder="TỪ" inputmode="numeric" aria-label="Giá thấp nhất">
                    </div>
                    <span class="price-dash">-</span>
                    <div class="price-input-field">
                        <span class="currency-tag">₫</span>
                        <input type="text" id="filter-price-to" placeholder="ĐẾN" inputmode="numeric" aria-label="Giá cao nhất">
                    </div>
                </div>
                <button type="button" class="btn-apply-filter" id="btn-apply-filter">ÁP DỤNG</button>

                <!-- CHIPS KHOẢNG GIÁ PHỔ BIẾN -->
                <div class="price-chips-grid" aria-label="Các khoảng giá gợi ý">
                    <button type="button" class="price-chip" data-min="0" data-max="500000">Dưới 500.000đ</button>
                    <button type="button" class="price-chip" data-min="500000" data-max="1000000">500.000đ - 1.000.000đ</button>
                    <button type="button" class="price-chip" data-min="1000000" data-max="2000000">1.000.000đ - 2.000.000đ</button>
                    <button type="button" class="price-chip" data-min="2000000" data-max="">Trên 2.000.000đ</button>
                </div>
            </div>

            <!-- NHÓM 2: TÌNH TRẠNG & ƯU ĐÃI -->
            <div class="filter-group">
                <h3 class="filter-group-title">TÌNH TRẠNG &amp; ƯU ĐÃI</h3>
                <ul class="filter-custom-list">
                    <li>
                        <label class="custom-check-item">
                            <input type="checkbox" id="filter-in-stock" class="filter-checkbox" value="in_stock">
                            <span class="check-box-custom"></span>
                            <span class="check-box-text">Chỉ xem sản phẩm còn hàng</span>
                        </label>
                    </li>
                    <li>
                        <label class="custom-check-item">
                            <input type="checkbox" id="filter-has-discount" class="filter-checkbox" value="has_discount">
                            <span class="check-box-custom"></span>
                            <span class="check-box-text">Đang giảm giá</span>
                        </label>
                    </li>
                    <li>
                        <label class="custom-check-item">
                            <input type="checkbox" id="filter-has-gift" class="filter-checkbox" value="has_gift">
                            <span class="check-box-custom"></span>
                            <span class="check-box-text">Có quà tặng kèm</span>
                        </label>
                    </li>
                </ul>
            </div>

            <!-- NÚT XÓA TẤT CẢ -->
            <button type="button" class="btn-reset-all-filters" id="btn-reset-all-filters">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                    <path d="M3 3v5h5"></path>
                </svg>
                XÓA TẤT CẢ
            </button>
        </aside>

        <!-- NỘI DUNG CHÍNH -->
        <section class="category-main">
            <!-- THANH TIÊU ĐỀ & SẮP XẾP CHUẨN SHOPEE -->
            <div class="category-header-shopee">
                <h1 class="page-title-left"><?php echo mb_strtoupper($categoryTitle, 'UTF-8'); ?></h1>

                <div class="shopee-sort-bar">
                    <span class="sort-bar-label">Sắp xếp theo</span>
                    <div class="sort-actions">
                        <button type="button" class="shopee-sort-btn active" data-sort="popular">Phổ biến</button>
                        <button type="button" class="shopee-sort-btn" data-sort="newest">Mới nhất</button>
                        <button type="button" class="shopee-sort-btn" data-sort="best_selling">Bán chạy</button>

                        <div class="shopee-sort-price-dropdown" id="shopee-sort-price-dropdown">
                            <button type="button" class="shopee-sort-btn price-dropdown-toggle" id="price-dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                                <span class="price-dropdown-label">Giá</span>
                                <span class="price-dropdown-arrow">▾</span>
                            </button>
                            <div class="price-dropdown-menu">
                                <button type="button" class="price-dropdown-item" data-sort="price_asc">Giá: Thấp đến Cao</button>
                                <button type="button" class="price-dropdown-item" data-sort="price_desc">Giá: Cao đến Thấp</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- THANH TIÊU CHÍ ĐANG LỌC (ACTIVE TAGS) -->
            <div class="active-filter-bar" id="active-filter-bar" style="display: none;">
                <span class="active-filter-label">Đang lọc:</span>
                <div class="active-filter-tags-list" id="active-filter-tags-list"></div>
                <button type="button" class="btn-clear-tags" id="btn-clear-tags">Xóa tất cả</button>
            </div>

            <!-- LƯỚI SẢN PHẨM -->
            <div class="product-grid category-grid" id="category-products-container" data-db-category="<?php echo htmlspecialchars($categoryKey, ENT_QUOTES, 'UTF-8'); ?>"></div>

            <!-- KHỐI THÔNG BÁO KHI KHÔNG CÓ SẢN PHẨM PHÙ HỢP (EMPTY STATE) -->
            <div class="filter-empty-state" id="filter-empty-state" style="display: none;">
                <div class="empty-icon-wrap">🔍</div>
                <h3>Không tìm thấy sản phẩm phù hợp</h3>
                <p>Hãy thử điều chỉnh lại khoảng giá hoặc xóa bớt tiêu chí lọc nhé!</p>
                <button type="button" class="btn-empty-reset" id="btn-empty-reset">Thiết lập lại bộ lọc</button>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../include/footer.php'; ?>
