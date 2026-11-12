<?php
require_once __DIR__ . '/../include/admin_guard.php';
require_once __DIR__ . '/../config/db.php';

$appBaseUrl = applicationBaseUrl();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
// Tác dụng: Xác thực phiên và cấp quyền hạn thao tác danh mục cho quản trị viên
$isManager = isManagerRole($_SESSION['admin_role'] ?? $_SESSION['role'] ?? null);
$roleLabel = $isManager ? 'Quản lý' : 'Nhân viên';
$adminUsername = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? '';
$adminCurrentPage = 'categories.php';

$message = '';
$messageType = '';

// Hàm tự động tạo slug từ tên tiếng Việt (để đảm bảo cột slug trong DB luôn hợp lệ)
function makeSlug(string $str): string {
    $search = [
        'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
        'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
        'ì','í','ị','ỉ','ĩ',
        'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
        'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
        'ỳ','ý','ỵ','ỷ','ỹ','đ',
        'À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ',
        'È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ',
        'Ì','Í','Ị','Ỉ','Ĩ',
        'Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ',
        'Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ',
        'Ỳ','Ý','Ỵ','Ỷ','Ỹ','Đ'
    ];
    $replace = [
        'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
        'e','e','e','e','e','e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
        'u','u','u','u','u','u','u','u','u','u','u',
        'y','y','y','y','y','d',
        'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
        'e','e','e','e','e','e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
        'u','u','u','u','u','u','u','u','u','u','u',
        'y','y','y','y','y','d'
    ];
    $str = str_replace($search, $replace, $str);
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $str), '-'));
    return $slug !== '' ? $slug : 'cat-' . time();
}

// Xử lý Form Thêm / Sửa / Xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Thêm danh mục mới
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $message = 'Vui lòng nhập tên danh mục.';
            $messageType = 'error';
        } else {
            // Kiểm tra trùng tên
            $check = $conn->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
            $check->bind_param("s", $name);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $message = 'Tên danh mục này đã tồn tại!';
                $messageType = 'error';
            } else {
                $slug = makeSlug($name);
                // Đảm bảo slug duy nhất
                $slugCheck = $conn->prepare("SELECT id FROM categories WHERE slug = ? LIMIT 1");
                $slugCheck->bind_param("s", $slug);
                $slugCheck->execute();
                if ($slugCheck->get_result()->num_rows > 0) {
                    $slug .= '-' . time();
                }
                $slugCheck->close();

                $stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $slug);
                if ($stmt->execute()) {
                    $message = "Đã thêm danh mục '{$name}' thành công!";
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi hệ thống khi thêm danh mục.';
                    $messageType = 'error';
                }
                $stmt->close();
            }
            $check->close();
        }
    }

    // 2. Sửa danh mục
    elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id <= 0 || $name === '') {
            $message = 'Dữ liệu sửa không hợp lệ.';
            $messageType = 'error';
        } else {
            $check = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ? LIMIT 1");
            $check->bind_param("si", $name, $id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $message = 'Tên danh mục này đã được sử dụng!';
                $messageType = 'error';
            } else {
                $slug = makeSlug($name);
                $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $slug, $id);
                if ($stmt->execute()) {
                    $message = "Đã cập nhật danh mục thành công!";
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi cập nhật danh mục.';
                    $messageType = 'error';
                }
                $stmt->close();
            }
            $check->close();
        }
    }

    // 3. Xóa danh mục
    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Kiểm tra xem có sản phẩm thuộc danh mục này không
            $chkProd = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE category_id = ?");
            $chkProd->bind_param("i", $id);
            $chkProd->execute();
            $resProd = $chkProd->get_result()->fetch_assoc();
            $chkProd->close();

            if (!empty($resProd['total']) && $resProd['total'] > 0) {
                $message = "Không thể xóa: Danh mục này đang chứa {$resProd['total']} sản phẩm!";
                $messageType = 'error';
            } else {
                $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $message = "Đã xóa danh mục thành công!";
                    $messageType = 'success';
                } else {
                    $message = 'Không thể xóa danh mục này.';
                    $messageType = 'error';
                }
                $stmt->close();
            }
        }
    }
}

// Lấy danh sách danh mục từ Database
$categories = [];
$res = $conn->query("SELECT id, name FROM categories ORDER BY id ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý danh mục - ÉCRIN Jewelry</title>
    <link rel="stylesheet" href="<?php echo $escape($appBaseUrl . '/admin/style.css'); ?>">
    <style>
        .cat-panel { margin: 20px 0; padding: 22px; border: 1px solid var(--line); border-radius: 12px; background: #fff; box-shadow: var(--shadow); }
        .cat-panel h2 { margin: 0 0 16px; font-size: 1.15rem; color: var(--navy); }
        .cat-form { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; }
        .cat-form label { display: grid; gap: 6px; font-weight: 600; font-size: 14px; color: var(--navy); flex: 1; max-width: 400px; }
        .cat-form input { padding: 10px 14px; border: 1px solid #cbd4df; border-radius: 8px; font: inherit; outline: none; }
        .cat-form input:focus { border-color: var(--navy); }
        .btn-primary { padding: 10px 20px; border: 0; border-radius: 8px; background: var(--navy); color: #fff; font: 600 14px inherit; cursor: pointer; transition: .2s; }
        .btn-primary:hover { background: #1a3c68; }

        .cat-table { width: 100%; border-collapse: collapse; }
        .cat-table th, .cat-table td { padding: 14px; border-bottom: 1px solid var(--line); text-align: left; }
        .cat-table th { background: #fafbfc; color: var(--muted); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .cat-table tr:hover { background: #fafbfc; }
        .btn-sm { padding: 6px 14px; border: 0; border-radius: 6px; font: 600 13px inherit; cursor: pointer; transition: .2s; }
        .btn-edit { background: #e0f2fe; color: #026aa2; }
        .btn-edit:hover { background: #b9e6fe; }
        .btn-delete { background: #fee4e2; color: #b42318; margin-left: 6px; }
        .btn-delete:hover { background: #fecdca; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #ecfdf3; color: #027a48; border: 1px solid #a6f4c5; }
        .alert-error { background: #fef3f2; color: #b42318; border: 1px solid #fecdca; }

        /* Modal Sửa */
        .modal { display: none; position: fixed; inset: 0; background: rgba(15, 39, 71, .5); z-index: 100; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
        .modal.is-open { display: flex; }
        .modal-box { width: min(440px, 92%); background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,.15); }
        .modal-box h3 { margin: 0 0 16px; color: var(--navy); font-size: 1.2rem; }
        .modal-box label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: var(--navy); }
        .modal-box input { width: 100%; padding: 10px 12px; border: 1px solid #cbd4df; border-radius: 8px; margin-bottom: 18px; font: inherit; }
        .modal-box .actions { display: flex; justify-content: flex-end; gap: 10px; }
        .btn-cancel { padding: 9px 16px; border: 1px solid #cbd4df; border-radius: 8px; background: #fff; cursor: pointer; font: inherit; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../include/admin_sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="breadcrumb">Trang chủ <span>/</span> <strong>Quản lý danh mục</strong></div>
            <div class="admin-profile">
                <div class="avatar"><?php echo $isManager ? 'QL' : 'NV'; ?></div>
                <div class="admin-info"><strong><?php echo $escape($adminUsername); ?></strong><small><?php echo $escape($roleLabel); ?></small></div>
            </div>
        </header>

        <section class="page-content">
            <div class="welcome-row">
                <div>
                    <h1>Quản lý danh mục</h1>
                    <p class="page-subtitle">Thêm, chỉnh sửa và quản lý danh mục sản phẩm ÉCRIN.</p>
                </div>
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo $messageType; ?>"><?php echo $escape($message); ?></div>
            <?php endif; ?>

            <!-- Form Thêm danh mục -->
            <section class="cat-panel">
                <h2>Thêm danh mục</h2>
                <form method="post" class="cat-form">
                    <input type="hidden" name="action" value="add">
                    <label>
                        Tên danh mục
                        <input type="text" name="name" placeholder="Nhập tên danh mục mới..." required>
                    </label>
                    <button type="submit" class="btn-primary">Lưu danh mục</button>
                </form>
            </section>

            <!-- Bảng danh sách danh mục (Chỉ gồm: Tên danh mục và Thao tác) -->
            <section class="cat-panel">
                <h2>Danh sách danh mục</h2>
                <div style="overflow-x:auto">
                    <table class="cat-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">STT</th>
                                <th>Tên danh mục</th>
                                <th style="width: 170px; text-align: center;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr><td colspan="3" style="text-align:center; color: var(--muted);">Chưa có danh mục nào.</td></tr>
                            <?php else: ?>
                                <?php foreach ($categories as $index => $cat): ?>
                                <tr>
                                    <td><strong>#<?php echo $index + 1; ?></strong></td>
                                    <td><strong><?php echo $escape($cat['name']); ?></strong></td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-sm btn-edit" onclick="openEditModal(<?php echo (int)$cat['id']; ?>, '<?php echo $escape($cat['name']); ?>')">Sửa</button>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục \'<?php echo $escape($cat['name']); ?>\' không?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int)$cat['id']; ?>">
                                            <button type="submit" class="btn-sm btn-delete">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </main>
</div>

<!-- Modal Sửa danh mục -->
<div class="modal" id="editModal">
    <div class="modal-box">
        <h3>Chỉnh sửa danh mục</h3>
        <form method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <label for="editName">Tên danh mục:</label>
            <input type="text" name="name" id="editName" required>
            <div class="actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Hủy</button>
                <button type="submit" class="btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editModal').classList.add('is-open');
    document.getElementById('editName').focus();
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('is-open');
}

// Bấm ra ngoài modal để đóng
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>
</body>
</html>
