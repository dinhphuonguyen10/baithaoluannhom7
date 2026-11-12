<?php
require_once __DIR__ . '/../include/admin_guard.php';
require_once __DIR__ . '/../config/db.php';

$appBaseUrl = applicationBaseUrl();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
// Tác dụng: Xác thực phiên và cấp quyền hạn thao tác sản phẩm cho quản trị viên
$isManager = isManagerRole($_SESSION['admin_role'] ?? $_SESSION['role'] ?? null);
$roleLabel = $isManager ? 'Quản lý' : 'Nhân viên';
$adminUsername = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? '';
$adminCurrentPage = 'products.php';

$message = '';
$messageType = '';

// Thư mục upload ảnh
$uploadDir = __DIR__ . '/../assets/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Xử lý Form Thêm / Sửa / Xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. THÊM SẢN PHẨM
    if ($action === 'add') {
        $name        = trim($_POST['name'] ?? '');
        $categoryId  = (int)($_POST['category_id'] ?? 0);
        $price       = (float)($_POST['price'] ?? 0);
        $stock       = max(0, (int)($_POST['stock'] ?? 0));
        $imagePath   = 'assets/banner/logo.png'; // Ảnh mặc định

        if ($name === '' || $categoryId <= 0) {
            $message = 'Vui lòng nhập tên sản phẩm và chọn danh mục!';
            $messageType = 'error';
        } else {
            // Xử lý upload ảnh nếu có
            if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (in_array($ext, $allowed, true)) {
                    $newFileName = 'prod_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
                    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $newFileName)) {
                        $imagePath = 'assets/uploads/' . $newFileName;
                    }
                }
            } elseif (!empty($_POST['image_url'])) {
                $imagePath = trim($_POST['image_url']);
            }

            $stmt = $conn->prepare("INSERT INTO products (name, category_id, price_sale, stock, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sidis", $name, $categoryId, $price, $stock, $imagePath);
            if ($stmt->execute()) {
                $message = "Đã thêm sản phẩm '{$name}' thành công!";
                $messageType = 'success';
            } else {
                $message = 'Lỗi hệ thống khi thêm sản phẩm: ' . $conn->error;
                $messageType = 'error';
            }
            $stmt->close();
        }
    }

    // 2. SỬA SẢN PHẨM
    elseif ($action === 'edit') {
        $id          = (int)($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $categoryId  = (int)($_POST['category_id'] ?? 0);
        $price       = (float)($_POST['price'] ?? 0);
        $stock       = max(0, (int)($_POST['stock'] ?? 0));

        if ($id <= 0 || $name === '' || $categoryId <= 0) {
            $message = 'Dữ liệu chỉnh sửa không hợp lệ!';
            $messageType = 'error';
        } else {
            // Kiểm tra có tải ảnh mới lên không
            $hasNewImage = false;
            $newImagePath = '';
            if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (in_array($ext, $allowed, true)) {
                    $newFileName = 'prod_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
                    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $newFileName)) {
                        $newImagePath = 'assets/uploads/' . $newFileName;
                        $hasNewImage = true;
                    }
                }
            } elseif (!empty($_POST['image_url_new'])) {
                $newImagePath = trim($_POST['image_url_new']);
                $hasNewImage = true;
            }

            if ($hasNewImage) {
                $stmt = $conn->prepare("UPDATE products SET name = ?, category_id = ?, price_sale = ?, stock = ?, image = ? WHERE id = ?");
                $stmt->bind_param("sidisi", $name, $categoryId, $price, $stock, $newImagePath, $id);
            } else {
                $stmt = $conn->prepare("UPDATE products SET name = ?, category_id = ?, price_sale = ?, stock = ? WHERE id = ?");
                $stmt->bind_param("sidii", $name, $categoryId, $price, $stock, $id);
            }

            if ($stmt->execute()) {
                $message = "Đã cập nhật thông tin sản phẩm thành công!";
                $messageType = 'success';
            } else {
                $message = 'Lỗi cập nhật sản phẩm: ' . $conn->error;
                $messageType = 'error';
            }
            $stmt->close();
        }
    }

    // 3. XÓA SẢN PHẨM
    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Đã xóa sản phẩm thành công!";
                $messageType = 'success';
            } else {
                $message = 'Lỗi khi xóa sản phẩm: ' . $conn->error;
                $messageType = 'error';
            }
            $stmt->close();
        }
    }
}

// Lấy danh sách danh mục (cho dropdown chọn)
$categories = [];
$catRes = $conn->query("SELECT id, name FROM categories ORDER BY id ASC");
if ($catRes) {
    while ($row = $catRes->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Lấy danh sách sản phẩm từ SQL VIEW (đã tự động tính tên danh mục & lượt bán)
$products = [];
$prodRes = $conn->query("SELECT * FROM view_product_details ORDER BY id DESC");
if ($prodRes) {
    while ($row = $prodRes->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý sản phẩm - ÉCRIN Jewelry</title>
    <link rel="stylesheet" href="<?php echo $escape($appBaseUrl . '/admin/style.css'); ?>">
    <style>
        .prod-panel { margin: 20px 0; padding: 22px; border: 1px solid var(--line); border-radius: 12px; background: #fff; box-shadow: var(--shadow); }
        .prod-toolbar { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .toolbar-left { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
        .toolbar-left input, .toolbar-left select { padding: 9px 14px; border: 1px solid #cbd4df; border-radius: 8px; font: inherit; outline: none; }
        .toolbar-left input:focus, .toolbar-left select:focus { border-color: var(--navy); }
        .toolbar-left input { min-width: 240px; }

        .btn-add { padding: 10px 18px; border: 0; border-radius: 8px; background: var(--navy); color: #fff; font: 600 14px inherit; cursor: pointer; transition: .2s; display: inline-flex; align-items: center; gap: 6px; }
        .btn-add:hover { background: #1a3c68; }

        .prod-table { width: 100%; border-collapse: collapse; }
        .prod-table th, .prod-table td { padding: 12px 14px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: middle; }
        .prod-table th { background: #fafbfc; color: var(--muted); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .prod-table tr:hover { background: #fafbfc; }

        .thumb-img { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; border: 1px solid var(--line); background: #f7f9fb; display: block; }
        .cat-badge { display: inline-block; padding: 3px 10px; background: #eef2f6; color: var(--navy); border-radius: 20px; font-size: 12px; font-weight: 600; }
        .stock-tag { font-weight: 600; font-size: 13px; }
        .stock-ok   { color: #027a48; }
        .stock-low  { color: #b54708; }
        .stock-out  { color: #b42318; }

        .btn-sm { padding: 6px 14px; border: 0; border-radius: 6px; font: 600 13px inherit; cursor: pointer; transition: .2s; }
        .btn-edit { background: #e0f2fe; color: #026aa2; }
        .btn-edit:hover { background: #b9e6fe; }
        .btn-delete { background: #fee4e2; color: #b42318; margin-left: 6px; }
        .btn-delete:hover { background: #fecdca; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #ecfdf3; color: #027a48; border: 1px solid #a6f4c5; }
        .alert-error { background: #fef3f2; color: #b42318; border: 1px solid #fecdca; }

        /* Modal popup */
        .modal { display: none; position: fixed; inset: 0; background: rgba(15, 39, 71, .55); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(3px); }
        .modal.is-open { display: flex; }
        .modal-box { width: min(540px, 94%); max-height: 90vh; overflow-y: auto; background: #fff; border-radius: 14px; padding: 26px; box-shadow: 0 16px 40px rgba(0,0,0,.2); }
        .modal-box h3 { margin: 0 0 18px; color: var(--navy); font-size: 1.25rem; border-bottom: 1px solid var(--line); padding-bottom: 10px; }
        .form-grid { display: grid; gap: 14px; }
        .form-field { display: grid; gap: 6px; }
        .form-field label { font-weight: 600; font-size: 13px; color: var(--navy); }
        .form-field input, .form-field select { padding: 9px 12px; border: 1px solid #cbd4df; border-radius: 8px; font: inherit; outline: none; }
        .form-field input:focus, .form-field select:focus { border-color: var(--navy); }
        .form-field small { color: var(--muted); font-size: 11px; }

        /* Khung nhập thêm hàng */
        .stock-adjust-box { background: #f4f7fb; border: 1px dashed #b8c7db; border-radius: 8px; padding: 12px; margin-top: 4px; }
        .stock-adjust-box strong { color: var(--navy); font-size: 13px; }
        .stock-calc-preview { font-size: 12px; color: #026aa2; margin-top: 4px; font-weight: 600; }

        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--line); }
        .btn-cancel { padding: 9px 16px; border: 1px solid #cbd4df; border-radius: 8px; background: #fff; cursor: pointer; font: inherit; }
        .current-img-preview { width: 64px; height: 64px; object-fit: cover; border-radius: 8px; border: 1px solid var(--line); margin-bottom: 6px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../include/admin_sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="breadcrumb">Trang chủ <span>/</span> <strong>Quản lý sản phẩm</strong></div>
            <div class="admin-profile">
                <div class="avatar"><?php echo $isManager ? 'QL' : 'NV'; ?></div>
                <div class="admin-info"><strong><?php echo $escape($adminUsername); ?></strong><small><?php echo $escape($roleLabel); ?></small></div>
            </div>
        </header>

        <section class="page-content">
            <div class="welcome-row">
                <div>
                    <h1>Quản lý sản phẩm</h1>
                    <p class="page-subtitle">Danh sách sản phẩm trang sức ÉCRIN.</p>
                </div>
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo $messageType; ?>"><?php echo $escape($message); ?></div>
            <?php endif; ?>

            <section class="prod-panel">
                <!-- Thanh công cụ tìm kiếm và lọc danh mục -->
                <div class="prod-toolbar">
                    <div class="toolbar-left">
                        <input type="search" id="search-input" placeholder="Tìm sản phẩm...">
                        <select id="category-filter">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo (int)$cat['id']; ?>"><?php echo $escape($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" class="btn-add" onclick="openAddModal()">+ Thêm sản phẩm</button>
                </div>

                <!-- Bảng danh sách sản phẩm -->
                <div style="overflow-x:auto">
                    <table class="prod-table" id="products-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Tồn kho</th>
                                <th style="width: 160px; text-align: center;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr id="empty-row"><td colspan="6" style="text-align:center; color: var(--muted); padding: 30px;">Chưa có sản phẩm nào.</td></tr>
                            <?php else: ?>
                                <?php foreach ($products as $p): ?>
                                    <?php
                                        $stockNum = (int)$p['stock'];
                                        if ($stockNum > 10) {
                                            $stockText = 'Còn hàng (' . $stockNum . ')';
                                            $stockClass = 'stock-ok';
                                        } elseif ($stockNum > 0) {
                                            $stockText = 'Sắp hết (' . $stockNum . ')';
                                            $stockClass = 'stock-low';
                                        } else {
                                            $stockText = 'Hết hàng (0)';
                                            $stockClass = 'stock-out';
                                        }
                                        $imgSrc = (str_starts_with($p['image'], 'http://') || str_starts_with($p['image'], 'https://'))
                                            ? $p['image']
                                            : $appBaseUrl . '/' . ltrim($p['image'], '/');
                                    ?>
                                    <tr class="product-row"
                                        data-name="<?php echo strtolower($escape($p['name'])); ?>"
                                        data-cat-id="<?php echo (int)$p['category_id']; ?>">
                                        <td>
                                            <img src="<?php echo $escape($imgSrc); ?>" alt="" class="thumb-img" onerror="this.src='<?php echo $escape($appBaseUrl . '/assets/banner/logo.png'); ?>'">
                                        </td>
                                        <td><strong><?php echo $escape($p['name']); ?></strong></td>
                                        <td><span class="cat-badge"><?php echo $escape($p['category_name']); ?></span></td>
                                        <td><strong><?php echo number_format((float)$p['price_sale'], 0, ',', '.'); ?>đ</strong></td>
                                        <td>
                                            <span class="stock-tag <?php echo $stockClass; ?>"><?php echo $escape($stockText); ?></span>
                                            <?php if ((int)($p['sold_count'] ?? 0) > 0): ?>
                                                <div style="font-size: 0.8rem; color: #e67e22; font-weight: 500; margin-top: 4px;">Đã bán: <?php echo (int)$p['sold_count']; ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-sm btn-edit"
                                                onclick='openEditModal(<?php echo json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>, "<?php echo $escape($imgSrc); ?>")'>
                                                Sửa
                                            </button>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm \'<?php echo $escape($p['name']); ?>\' không?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                                <button type="submit" class="btn-sm btn-delete">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr id="no-search-results" style="display: none;">
                                <td colspan="6" style="text-align:center; color: var(--muted); padding: 30px;">Không tìm thấy sản phẩm phù hợp.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </main>
</div>

<!-- MODAL THÊM SẢN PHẨM -->
<div class="modal" id="addModal">
    <div class="modal-box">
        <h3>+ Thêm sản phẩm mới</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-field">
                    <label>Tên sản phẩm (*):</label>
                    <input type="text" name="name" placeholder="Ví dụ: Bông tai ÉCRIN ánh vàng" required>
                </div>

                <div class="form-field">
                    <label>Danh mục sản phẩm (*):</label>
                    <select name="category_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>"><?php echo $escape($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-field">
                    <label>Giá bán (VNĐ) (*):</label>
                    <input type="number" name="price" placeholder="Ví dụ: 450000" min="0" step="1000" required>
                </div>

                <div class="form-field">
                    <label>Số lượng tồn kho ban đầu (*):</label>
                    <input type="number" name="stock" value="10" min="0" required>
                </div>

                <div class="form-field">
                    <label>Ảnh sản phẩm:</label>
                    <input type="file" name="image_file" accept="image/*">
                    <small>Hoặc nhập link ảnh trực tiếp:</small>
                    <input type="text" name="image_url" placeholder="assets/bôngtai/bong1.jpg hoặc link URL">
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Hủy</button>
                <button type="submit" class="btn-add">Thêm sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SỬA SẢN PHẨM -->
<div class="modal" id="editModal">
    <div class="modal-box">
        <h3>Chỉnh sửa sản phẩm</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">

            <div class="form-grid">
                <div class="form-field">
                    <label>Tên sản phẩm (*):</label>
                    <input type="text" name="name" id="edit-name" required>
                </div>

                <div class="form-field">
                    <label>Danh mục (*):</label>
                    <select name="category_id" id="edit-category" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>"><?php echo $escape($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-field">
                    <label>Giá bán (VNĐ) (*):</label>
                    <input type="number" name="price" id="edit-price" min="0" step="1000" required>
                </div>

                <!-- KHU VỰC QUẢN LÝ SỐ LƯỢNG / NHẬP THÊM HÀNG -->
                <div class="form-field">
                    <label>Số lượng tồn kho:</label>
                    <input type="number" name="stock" id="edit-stock" min="0" required>
                    
                    <div class="stock-adjust-box">
                        <strong>📦 Nhập thêm hàng về kho:</strong>
                        <div style="display: flex; gap: 8px; margin-top: 6px; align-items: center;">
                            <input type="number" id="add-stock-input" placeholder="Số lượng nhập thêm (VD: 5)" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd4df; border-radius: 6px;">
                            <button type="button" class="btn-sm btn-edit" style="white-space: nowrap;" onclick="applyStockAdd()">+ Cộng kho</button>
                        </div>
                        <div class="stock-calc-preview" id="stock-calc-hint"></div>
                    </div>
                </div>

                <div class="form-field">
                    <label>Ảnh sản phẩm hiện tại:</label>
                    <div><img id="edit-img-preview" src="" alt="" class="current-img-preview"></div>
                    <label>Đổi ảnh mới (nếu muốn):</label>
                    <input type="file" name="image_file" accept="image/*">
                    <input type="text" name="image_url_new" id="edit-img-url" placeholder="Hoặc nhập đường dẫn ảnh mới..." style="margin-top: 6px;">
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Hủy</button>
                <button type="submit" class="btn-add">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Thêm sản phẩm
function openAddModal() {
    document.getElementById('addModal').classList.add('is-open');
}
function closeAddModal() {
    document.getElementById('addModal').classList.remove('is-open');
}

// Modal Sửa sản phẩm
let originalStock = 0;

function openEditModal(prod, imgSrc) {
    document.getElementById('edit-id').value       = prod.id;
    document.getElementById('edit-name').value     = prod.name;
    document.getElementById('edit-category').value = prod.category_id;
    document.getElementById('edit-price').value    = prod.price_sale;
    document.getElementById('edit-stock').value    = prod.stock;
    originalStock = parseInt(prod.stock, 10) || 0;

    // Reset ô nhập thêm hàng
    const addStockInput = document.getElementById('add-stock-input');
    addStockInput.value = '';
    document.getElementById('stock-calc-hint').textContent = 'Hiện tại đang có ' + originalStock + ' sản phẩm trong kho.';

    document.getElementById('edit-img-preview').src = imgSrc;
    document.getElementById('edit-img-url').value   = '';
    document.getElementById('editModal').classList.add('is-open');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('is-open');
}

// Tính năng tự động cộng kho khi nhập thêm hàng
document.getElementById('add-stock-input').addEventListener('input', function() {
    const addVal = parseInt(this.value, 10);
    const hint = document.getElementById('stock-calc-hint');
    if (!isNaN(addVal) && addVal > 0) {
        const newTotal = originalStock + addVal;
        hint.textContent = '➡ Kho hiện tại ' + originalStock + ' + nhập thêm ' + addVal + ' = ' + newTotal + ' sản phẩm.';
    } else {
        hint.textContent = 'Hiện tại đang có ' + originalStock + ' sản phẩm trong kho.';
    }
});

function applyStockAdd() {
    const addVal = parseInt(document.getElementById('add-stock-input').value, 10);
    if (!isNaN(addVal) && addVal > 0) {
        const newTotal = originalStock + addVal;
        document.getElementById('edit-stock').value = newTotal;
        originalStock = newTotal;
        document.getElementById('add-stock-input').value = '';
        document.getElementById('stock-calc-hint').textContent = '✔ Đã cập nhật tồn kho thành: ' + newTotal;
    }
}

// Đóng modal khi bấm ra ngoài
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('is-open');
    }
});

// TÍNH NĂNG TÌM KIẾM & LỌC DANH MỤC REALTIME (Ảnh 3)
const searchInput = document.getElementById('search-input');
const categoryFilter = document.getElementById('category-filter');
const rows = document.querySelectorAll('.product-row');
const noResults = document.getElementById('no-search-results');

function filterProducts() {
    const query = searchInput.value.trim().toLowerCase();
    const selectedCat = categoryFilter.value;
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const catId = row.getAttribute('data-cat-id');

        const matchName = query === '' || name.includes(query);
        const matchCat = selectedCat === '' || catId === selectedCat;

        if (matchName && matchCat) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    if (noResults) {
        noResults.style.display = visibleCount === 0 && rows.length > 0 ? '' : 'none';
    }
}

searchInput.addEventListener('input', filterProducts);
categoryFilter.addEventListener('change', filterProducts);
</script>
</body>
</html>
