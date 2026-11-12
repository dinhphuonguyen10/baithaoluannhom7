<?php
require_once __DIR__ . '/../include/admin_guard.php';
requireManagerRole();
require_once __DIR__ . '/../config/db.php';
$appBaseUrl = applicationBaseUrl();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$error = '';
$success = '';

if (empty($_SESSION['staff_management_csrf'])) {
    $_SESSION['staff_management_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? 'create';

    if (!is_string($token) || !hash_equals($_SESSION['staff_management_csrf'], $token)) {
        $error = 'Phiên làm việc không hợp lệ. Vui lòng tải lại trang.';
    } elseif ($action === 'reset_password') {
        // Đổi mật khẩu cho nhân viên
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $newPassword  = isset($_POST['new_password']) && is_string($_POST['new_password']) ? $_POST['new_password'] : '';

        if ($targetUserId <= 0) {
            $error = 'Vui lòng chọn tài khoản cần đổi mật khẩu.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Mật khẩu mới phải có tối thiểu 8 ký tự.';
        } else {
            $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ? AND role IN ('admin', 'manager', 'staff') LIMIT 1");
            $stmt->bind_param('i', $targetUserId);
            $stmt->execute();
            $targetUser = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$targetUser) {
                $error = 'Không tìm thấy tài khoản nhân viên.';
            } else {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param('si', $passwordHash, $targetUserId);
                if ($stmt->execute()) {
                    $success = "Đã cập nhật mật khẩu mới cho tài khoản '{$targetUser['username']}' thành công!";
                } else {
                    $error = 'Không thể cập nhật mật khẩu lúc này.';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'create') {
        // Tạo tài khoản mới
        $username = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
        $role     = isset($_POST['role']) && in_array($_POST['role'], ['staff', 'manager'], true) ? $_POST['role'] : 'staff';

        if ($username === '' || strlen($username) < 3 || strlen($username) > 100) {
            $error = 'Tên đăng nhập phải từ 3 đến 100 ký tự.';
        } elseif (strlen($password) < 8) {
            $error = 'Mật khẩu phải có tối thiểu 8 ký tự.';
        } else {
            $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($exists) {
                $error = 'Tên đăng nhập đã được sử dụng.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO users (username, password, phone, role) VALUES (?, ?, NULL, ?)');
                $stmt->bind_param('sss', $username, $passwordHash, $role);
                if ($stmt->execute()) {
                    $roleLabel = $role === 'manager' ? 'Quản lý (Manager)' : 'Nhân viên (Staff)';
                    $success = "Đã tạo tài khoản {$roleLabel} '{$username}' thành công.";
                } else {
                    $error = 'Không thể tạo tài khoản lúc này.';
                }
                $stmt->close();
            }
        }
    }
}

$staffResult = $conn->query("SELECT id, username, role, created_at FROM users WHERE role IN ('admin', 'manager', 'staff') ORDER BY id DESC");

// Tác dụng: Phân quyền quản trị tài khoản nội bộ cho cấp quản lý từ phiên admin
$isManager = isManagerRole($_SESSION['admin_role'] ?? $_SESSION['role'] ?? null);
$adminUsername = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? '';
$adminCurrentPage = 'users.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý tài khoản nhân viên - ÉCRIN Jewelry</title>
    <link rel="stylesheet" href="<?php echo $escape($appBaseUrl . '/admin/style.css'); ?>">
    <style>
        .prototype-panel { margin: 20px 0; padding: 22px; border: 1px solid var(--line); border-radius: 12px; background: #fff; }
        .prototype-panel h2 { margin-top: 0; font-size: 1.25rem; }
        .prototype-form { display: flex; flex-wrap: wrap; align-items: end; gap: 12px; }
        .prototype-form label { display: grid; gap: 6px; font-weight: 700; font-size: 0.9rem; }
        .prototype-form input, .prototype-form select { padding: 9px 12px; border: 1px solid #d5dbe2; border-radius: 7px; font: inherit; }
        .prototype-form button { padding: 9px 16px; border: 0; border-radius: 7px; background: var(--admin-navy, #0f2747); color: #fff; font-weight: bold; cursor: pointer; }
        .prototype-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .prototype-table th, .prototype-table td { padding: 12px; border-bottom: 1px solid #edf0f3; text-align: left; vertical-align: middle; }
        .notice { margin: 18px 0; padding: 12px 16px; border-radius: 8px; }
        .notice.error { background: #fff0ef; color: #b42318; border: 1px solid #fda29b; }
        .notice.success { background: #ecfdf3; color: #027a48; border: 1px solid #a6f4c5; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; }
        .badge-admin { background: #fef0c7; color: #b54708; }
        .badge-manager { background: #e0f2fe; color: #0369a1; }
        .badge-staff { background: #f3f4f6; color: #374151; }

        .btn-reset-pwd {
            padding: 6px 12px;
            background: #fff;
            border: 1px solid #c4a45a;
            color: #8c6e26;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-reset-pwd:hover {
            background: #c4a45a;
            color: #fff;
        }

        /* Modal đổi mật khẩu */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(11, 32, 51, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #fff;
            border-radius: 14px;
            padding: 28px;
            width: min(440px, 92%);
            box-shadow: 0 20px 48px rgba(0,0,0,0.25);
            position: relative;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .modal-header h3 { margin: 0; color: #0b2033; font-size: 1.2rem; }
        .close-modal { font-size: 1.5rem; cursor: pointer; border: none; background: transparent; color: #888; }
        .modal-field { margin-bottom: 16px; display: grid; gap: 6px; }
        .modal-field label { font-weight: 700; font-size: 0.9rem; color: #333; }
        .modal-field input { padding: 10px 12px; border: 1px solid #cbd0d7; border-radius: 8px; font: inherit; }
        .modal-field input:focus { outline: 2px solid #c4a45a; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .btn-cancel { padding: 9px 16px; border: 1px solid #ccc; background: #fff; border-radius: 7px; cursor: pointer; }
        .btn-submit-reset { padding: 9px 18px; border: 0; background: #0f2747; color: #fff; font-weight: bold; border-radius: 7px; cursor: pointer; }
        .btn-submit-reset:hover { background: #1a3c68; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../include/admin_sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="breadcrumb">Trang chủ <span>/</span> <strong>Quản lý tài khoản nhân viên</strong></div>
            <div class="admin-profile">
                <div class="avatar"><?php echo $isManager ? 'QL' : 'NV'; ?></div>
                <div class="admin-info"><strong><?php echo $escape($adminUsername); ?></strong><small><?php echo $escape($roleLabel); ?></small></div>
            </div>
        </header>
        <section class="page-content">
        <h1>Quản lý tài khoản quản trị & nhân viên</h1>
        <?php if ($error !== ''): ?><p class="notice error" role="alert"><?php echo $escape($error); ?></p><?php endif; ?>
        <?php if ($success !== ''): ?><p class="notice success" role="status"><?php echo $escape($success); ?></p><?php endif; ?>

        <!-- Form tạo tài khoản mới -->
        <section class="prototype-panel">
            <h2>+ Tạo tài khoản mới</h2>
            <form method="post" class="prototype-form">
                <input type="hidden" name="csrf_token" value="<?php echo $escape($_SESSION['staff_management_csrf']); ?>">
                <input type="hidden" name="action" value="create">
                <label>Tên đăng nhập
                    <input type="text" name="username" placeholder="Ví dụ: nhanvien_a" minlength="3" maxlength="100" required>
                </label>
                <label>Mật khẩu ban đầu
                    <input type="password" name="password" placeholder="Tối thiểu 8 ký tự" minlength="8" required>
                </label>
                <label>Vai trò
                    <select name="role">
                        <option value="staff">Nhân viên (staff)</option>
                        <option value="manager">Quản lý (manager)</option>
                    </select>
                </label>
                <button type="submit">+ Tạo tài khoản</button>
            </form>
        </section>

        <!-- Danh sách tài khoản & Đổi mật khẩu -->
        <section class="prototype-panel">
            <h2>Danh sách tài khoản nội bộ & Đổi mật khẩu</h2>
            <p style="color: #667085; font-size: 0.9rem; margin-top: -6px; margin-bottom: 16px;">
                💡 Khi nhân viên quên mật khẩu, quản trị viên có thể bấm <strong>"🔑 Đổi mật khẩu"</strong> để cấp lại mật khẩu mới ngay lập tức.
            </p>
            <table class="prototype-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Vai trò</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($staffResult && $staffResult->num_rows > 0): ?>
                    <?php while ($staff = $staffResult->fetch_assoc()): ?>
                        <?php
                            $roleClass = match($staff['role']) {
                                'admin' => 'badge-admin',
                                'manager' => 'badge-manager',
                                default => 'badge-staff'
                            };
                        ?>
                        <tr>
                            <td>#<?php echo $escape($staff['id']); ?></td>
                            <td><strong><?php echo $escape($staff['username']); ?></strong></td>
                            <td><span class="badge <?php echo $roleClass; ?>"><?php echo $escape($staff['role']); ?></span></td>
                            <td><?php echo $escape($staff['created_at']); ?></td>
                            <td style="color: #027a48; font-weight: 600;">✓ Hoạt động</td>
                            <td style="text-align: right;">
                                <button type="button" class="btn-reset-pwd"
                                    onclick="openResetModal(<?php echo (int)$staff['id']; ?>, '<?php echo $escape($staff['username']); ?>')">
                                    🔑 Đổi mật khẩu
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">Chưa có tài khoản.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </section></main></div>

    <!-- Modal Đổi mật khẩu -->
    <div id="reset-password-modal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>🔑 Đổi mật khẩu nhân viên</h3>
                <button type="button" class="close-modal" onclick="closeResetModal()">&times;</button>
            </div>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $escape($_SESSION['staff_management_csrf']); ?>">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="target_user_id" id="modal-target-user-id" value="">

                <div class="modal-field">
                    <label>Tài khoản nhân viên:</label>
                    <input type="text" id="modal-target-username" readonly style="background: #f4f5f7; font-weight: bold; color: #0f2747;">
                </div>

                <div class="modal-field">
                    <label for="modal-new-password">Mật khẩu mới:</label>
                    <input type="text" name="new_password" id="modal-new-password" placeholder="Nhập mật khẩu mới (tối thiểu 8 ký tự)" minlength="8" required>
                    <small style="color: #667085; font-size: 0.8rem;">* Mật khẩu hiển thị rõ để Admin dễ dàng đọc hoặc gửi lại cho nhân viên.</small>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeResetModal()">Hủy</button>
                    <button type="submit" class="btn-submit-reset">Lưu mật khẩu mới</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openResetModal(userId, username) {
        document.getElementById('modal-target-user-id').value = userId;
        document.getElementById('modal-target-username').value = username;
        const pwdInput = document.getElementById('modal-new-password');
        pwdInput.value = '';
        document.getElementById('reset-password-modal').classList.add('active');
        pwdInput.focus();
    }

    function closeResetModal() {
        document.getElementById('reset-password-modal').classList.remove('active');
    }

    // Đóng modal khi bấm ra ngoài vùng box
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('reset-password-modal');
        if (e.target === modal) {
            closeResetModal();
        }
    });
    </script>
        </section>
    </section></main></div>
</div>
</body>
</html>
SESSION['username'] ?? ''); ?></strong><small><?php echo $escape($roleLabel); ?></small></div>
            </div>
        </header>
        <section class="page-content">
    <main>
        <h1>Quản lý tài khoản quản trị & nhân viên</h1>
        <?php if ($error !== ''): ?><p class="notice error" role="alert"><?php echo $escape($error); ?></p><?php endif; ?>
        <?php if ($success !== ''): ?><p class="notice success" role="status"><?php echo $escape($success); ?></p><?php endif; ?>

        <!-- Form tạo tài khoản mới -->
        <section class="prototype-panel">
            <h2>+ Tạo tài khoản mới</h2>
            <form method="post" class="prototype-form">
                <input type="hidden" name="csrf_token" value="<?php echo $escape($_SESSION['staff_management_csrf']); ?>">
                <input type="hidden" name="action" value="create">
                <label>Tên đăng nhập
                    <input type="text" name="username" placeholder="Ví dụ: nhanvien_a" minlength="3" maxlength="100" required>
                </label>
                <label>Mật khẩu ban đầu
                    <input type="password" name="password" placeholder="Tối thiểu 8 ký tự" minlength="8" required>
                </label>
                <label>Vai trò
                    <select name="role">
                        <option value="staff">Nhân viên (staff)</option>
                        <option value="manager">Quản lý (manager)</option>
                    </select>
                </label>
                <button type="submit">+ Tạo tài khoản</button>
            </form>
        </section>

        <!-- Danh sách tài khoản & Đổi mật khẩu -->
        <section class="prototype-panel">
            <h2>Danh sách tài khoản nội bộ & Đổi mật khẩu</h2>
            <p style="color: #667085; font-size: 0.9rem; margin-top: -6px; margin-bottom: 16px;">
                💡 Khi nhân viên quên mật khẩu, quản trị viên có thể bấm <strong>"🔑 Đổi mật khẩu"</strong> để cấp lại mật khẩu mới ngay lập tức.
            </p>
            <table class="prototype-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Vai trò</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($staffResult && $staffResult->num_rows > 0): ?>
                    <?php while ($staff = $staffResult->fetch_assoc()): ?>
                        <?php
                            $roleClass = match($staff['role']) {
                                'admin' => 'badge-admin',
                                'manager' => 'badge-manager',
                                default => 'badge-staff'
                            };
                        ?>
                        <tr>
                            <td>#<?php echo $escape($staff['id']); ?></td>
                            <td><strong><?php echo $escape($staff['username']); ?></strong></td>
                            <td><span class="badge <?php echo $roleClass; ?>"><?php echo $escape($staff['role']); ?></span></td>
                            <td><?php echo $escape($staff['created_at']); ?></td>
                            <td style="color: #027a48; font-weight: 600;">✓ Hoạt động</td>
                            <td style="text-align: right;">
                                <button type="button" class="btn-reset-pwd"
                                    onclick="openResetModal(<?php echo (int)$staff['id']; ?>, '<?php echo $escape($staff['username']); ?>')">
                                    🔑 Đổi mật khẩu
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">Chưa có tài khoản.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </section></main></div>

    <!-- Modal Đổi mật khẩu -->
    <div id="reset-password-modal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>🔑 Đổi mật khẩu nhân viên</h3>
                <button type="button" class="close-modal" onclick="closeResetModal()">&times;</button>
            </div>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $escape($_SESSION['staff_management_csrf']); ?>">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="target_user_id" id="modal-target-user-id" value="">

                <div class="modal-field">
                    <label>Tài khoản nhân viên:</label>
                    <input type="text" id="modal-target-username" readonly style="background: #f4f5f7; font-weight: bold; color: #0f2747;">
                </div>

                <div class="modal-field">
                    <label for="modal-new-password">Mật khẩu mới:</label>
                    <input type="text" name="new_password" id="modal-new-password" placeholder="Nhập mật khẩu mới (tối thiểu 8 ký tự)" minlength="8" required>
                    <small style="color: #667085; font-size: 0.8rem;">* Mật khẩu hiển thị rõ để Admin dễ dàng đọc hoặc gửi lại cho nhân viên.</small>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeResetModal()">Hủy</button>
                    <button type="submit" class="btn-submit-reset">Lưu mật khẩu mới</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openResetModal(userId, username) {
        document.getElementById('modal-target-user-id').value = userId;
        document.getElementById('modal-target-username').value = username;
        const pwdInput = document.getElementById('modal-new-password');
        pwdInput.value = '';
        document.getElementById('reset-password-modal').classList.add('active');
        pwdInput.focus();
    }

    function closeResetModal() {
        document.getElementById('reset-password-modal').classList.remove('active');
    }

    // Đóng modal khi bấm ra ngoài vùng box
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('reset-password-modal');
        if (e.target === modal) {
            closeResetModal();
        }
    });
    </script>
        </section>
    </section></main></div>
</div>

</body>
</html>
