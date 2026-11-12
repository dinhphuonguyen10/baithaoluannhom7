<?php
$pageTitle = "ÉCRIN JEWELRY - Trang chủ";
$bodyClass = 'home-page';
include __DIR__ . '/include/header.php';
?>

    <!-- BANNER -->
    <section class="banner" aria-label="Bộ sưu tập Écrin Jewelry">
        <div class="banner-left">
            <div class="slideshow">
                <img src="assets/banner/baner1.png" alt="Bộ sưu tập trang sức mới Écrin Jewelry" class="slide active">
                <img src="assets/banner/baner2.png" alt="Trang sức Écrin trên nền lụa xanh thanh lịch" class="slide">
                <img src="assets/banner/baner3.png" alt="Bộ trang sức hoa Écrin Jewelry" class="slide">
                <img src="assets/banner/baner4.png" alt="Nhẫn và bông tai Écrin Jewelry" class="slide">
            </div>
            <button type="button" class="banner-arrow prev" aria-label="Ảnh trước">‹</button>
            <button type="button" class="banner-arrow next" aria-label="Ảnh tiếp theo">›</button>
            <div class="banner-dots" aria-label="Chọn ảnh">
                <button type="button" class="dot active" aria-label="Ảnh 1" aria-current="true"></button>
                <button type="button" class="dot" aria-label="Ảnh 2"></button>
                <button type="button" class="dot" aria-label="Ảnh 3"></button>
                <button type="button" class="dot" aria-label="Ảnh 4"></button>
            </div>
        </div>
    </section>

    <!-- CATEGORY -->
    <section class="category home-categories" aria-label="Danh mục sản phẩm">
        <?php
        $categoryIcons = [
            'bong-tai' => 'assets/bôngtai/bong1.jpg',
            'day-chuyen' => 'assets/dây chuyền/dâychuyen/day1.jpg',
            'nhan' => 'assets/nhẫn/nhan1.png',
            'vong-tay' => 'assets/vòng tay/vong1.png',
            'set-qua-tang' => 'assets/quà/set1.jpg',
        ];
        $categoryLinks = [
            'bong-tai' => 'client/bong_tai.php',
            'day-chuyen' => 'client/day_chuyen.php',
            'nhan' => 'client/nhan.php',
            'vong-tay' => 'client/vong_tay.php',
        ];

        $displayCats = [];
        if (!empty($navCategories)) {
            foreach ($navCategories as $cat) {
                if (!in_array($cat['slug'], ['combo', 'suggest', 'featured'], true)) {
                    $displayCats[] = $cat;
                }
            }
        }

        if (!empty($displayCats)):
            foreach ($displayCats as $cat):
                $cSlug = $cat['slug'];
                $cName = $cat['name'];
                $cImg = $categoryIcons[$cSlug] ?? 'assets/banner/logo.png';
                $cLink = $categoryLinks[$cSlug] ?? ('client/category.php?slug=' . urlencode($cSlug));
        ?>
            <a href="<?php echo htmlspecialchars($cLink, ENT_QUOTES, 'UTF-8'); ?>" class="item">
                <img src="<?php echo htmlspecialchars($cImg, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($cName, ENT_QUOTES, 'UTF-8'); ?>" class="item-icon">
                <span><?php echo htmlspecialchars($cName, ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
        <?php 
            endforeach;
        else: 
        ?>
            <a href="client/bong_tai.php" class="item">
                <img src="assets/bôngtai/bong1.jpg" alt="Bông tai" class="item-icon">
                <span>Bông tai</span>
            </a>
            <a href="client/day_chuyen.php" class="item">
                <img src="assets/dây chuyền/dâychuyen/day1.jpg" alt="Dây chuyền" class="item-icon">
                <span>Dây chuyền</span>
            </a>
            <a href="client/nhan.php" class="item">
                <img src="assets/nhẫn/nhan1.png" alt="Nhẫn" class="item-icon">
                <span>Nhẫn</span>
            </a>
            <a href="client/vong_tay.php" class="item">
                <img src="assets/vòng tay/vong1.png" alt="Vòng tay" class="item-icon">
                <span>Vòng tay</span>
            </a>
            <a href="client/category.php?slug=set-qua-tang" class="item">
                <img src="assets/quà/set1.jpg" alt="Set quà tặng" class="item-icon">
                <span>Set quà tặng</span>
            </a>
        <?php endif; ?>
    </section>

    <section class="category-page" id="search-results-section" hidden>
        <div class="category-header">
            <h1 class="page-title-left">KẾT QUẢ TÌM KIẾM</h1>
        </div>
        <div class="product-grid category-grid" id="category-products-container" data-db-category=""></div>
    </section>

    <!-- SẢN PHẨM NỔI BẬT-->
    <section class="combo-section">
        <div class="combo-container">
            <div class="home-product-heading">
                <div><p class="section-label">KẾT HỢP TINH TẾ</p><h2>Combo được yêu thích</h2></div>
                <a href="client/bong_tai.php">Xem bộ sưu tập <span aria-hidden="true">→</span></a>
            </div>
            <div class="combo-slider">
                <button class="slider-btn prev" id="combo-prev">❮</button>

                <div class="combo-items" id="combo-items-container">
                    <!-- Sản phẩm sẽ được render từ JavaScript (script.js) -->
                </div>

                <button class="slider-btn next" id="combo-next"
                    style="width: auto; padding: 0 16px; border-radius: 20px; font-size: 1rem; font-weight: 600; white-space: nowrap;">Xem
                    thêm ❯</button>
            </div>
        </div>
    </section>

    <!-- MẪU ĐƯỢC YÊU THÍCH NHẤT-->
    <section class="combo-section combo-section--alt">
        <div class="combo-container">
            <div class="home-product-heading">
                <div><p class="section-label">DÀNH RIÊNG CHO BẠN</p><h2>Gợi ý cho bạn</h2></div>
                <a href="client/nhan.php">Khám phá nhẫn <span aria-hidden="true">→</span></a>
            </div>
            <div class="combo-slider">
                <button class="slider-btn prev" id="suggest-prev">❮</button>

                <div class="combo-items" id="suggest-items-container">
                    <!-- Sản phẩm sẽ được render từ JavaScript (script.js) -->
                </div>

                <button class="slider-btn next" id="suggest-next"
                    style="width: auto; padding: 0 16px; border-radius: 20px; font-size: 1rem; font-weight: 600; white-space: nowrap;">Xem
                    thêm ❯</button>
            </div>
        </div>
    </section>

    <!-- SẢN PHẨM MỚI -->
    <section class="combo-section">
        <div class="combo-container">
            <div class="home-product-heading">
                <div><p class="section-label">ĐƯỢC TUYỂN CHỌN</p><h2>Sản phẩm nổi bật</h2></div>
                <a href="client/bong_tai.php">Khám phá bộ sưu tập <span aria-hidden="true">→</span></a>
            </div>
            <div class="combo-slider">
                <button class="slider-btn prev" id="featured-prev">❮</button>

                <div class="combo-items" id="featured-items-container">
                    <!-- Sản phẩm sẽ được render từ JavaScript (script.js) -->
                </div>

                <button class="slider-btn next" id="featured-next"
                    style="width: auto; padding: 0 16px; border-radius: 20px; font-size: 1rem; font-weight: 600; white-space: nowrap;">Xem
                    thêm ❯</button>
            </div>
        </div>
    </section>

<?php
include __DIR__ . '/include/footer.php';
?>
