<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
$user_id = (int) $_SESSION['user_id'];
$message = '';
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $recipient_name = trim($_POST['recipient_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $is_default = isset($_POST['is_default']) ? 1 : 0;

        if ($recipient_name === '' || $phone === '' || $address === '') {
            $message = 'Vui lòng nhập đầy đủ tên người nhận, số điện thoại và địa chỉ.';
            $message_type = 'error';
        } else {
            try {
                $conn->begin_transaction();
                if ($is_default) {
                    $clear_default = $conn->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = ?');
                    $clear_default->bind_param('i', $user_id);
                    $clear_default->execute();
                    $clear_default->close();
                }

                $insert = $conn->prepare(
                    'INSERT INTO addresses (user_id, recipient_name, address, phone, is_default) VALUES (?, ?, ?, ?, ?)'
                );
                $insert->bind_param('isssi', $user_id, $recipient_name, $address, $phone, $is_default);
                $insert->execute();
                $insert->close();
                $conn->commit();
                header('Location: diachi.php?status=added');
                exit;
            } catch (Throwable $e) {
                $conn->rollback();
                error_log('Address creation failed: ' . $e->getMessage());
                $message = 'Không thể lưu địa chỉ lúc này. Vui lòng thử lại.';
                $message_type = 'error';
            }
        }
    } elseif ($action === 'default' || $action === 'delete') {
        $address_id = filter_var($_POST['address_id'] ?? null, FILTER_VALIDATE_INT);
        if ($address_id === false || $address_id < 1) {
            $message = 'Địa chỉ không hợp lệ.';
            $message_type = 'error';
        } elseif ($action === 'default') {
            try {
                $conn->begin_transaction();
                $clear_default = $conn->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = ?');
                $clear_default->bind_param('i', $user_id);
                $clear_default->execute();
                $clear_default->close();

                $set_default = $conn->prepare(
                    'UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?'
                );
                $set_default->bind_param('ii', $address_id, $user_id);
                $set_default->execute();
                if ($set_default->affected_rows !== 1) {
                    throw new RuntimeException('Không tìm thấy địa chỉ thuộc tài khoản này.');
                }
                $set_default->close();
                $conn->commit();
                header('Location: diachi.php?status=default');
                exit;
            } catch (Throwable $e) {
                $conn->rollback();
                error_log('Default address update failed: ' . $e->getMessage());
                $message = 'Không thể cập nhật địa chỉ mặc định.';
                $message_type = 'error';
            }
        } else {
            $delete = $conn->prepare('DELETE FROM addresses WHERE id = ? AND user_id = ?');
            $delete->bind_param('ii', $address_id, $user_id);
            if ($delete->execute() && $delete->affected_rows === 1) {
                header('Location: diachi.php?status=deleted');
                exit;
            }
            $message = 'Không tìm thấy địa chỉ thuộc tài khoản này.';
            $message_type = 'error';
            $delete->close();
        }
    }
}

if (isset($_GET['status'])) {
    $messages = [
        'added' => 'Đã thêm địa chỉ.',
        'default' => 'Đã cập nhật địa chỉ mặc định.',
        'deleted' => 'Đã xóa địa chỉ.'
    ];
    $message = $messages[$_GET['status']] ?? '';
}

$addresses_stmt = $conn->prepare(
    'SELECT id, recipient_name, address, phone, is_default FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC'
);
$addresses_stmt->bind_param('i', $user_id);
$addresses_stmt->execute();
$addresses = $addresses_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Địa chỉ nhận hàng</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <main>
        <h1>Địa chỉ nhận hàng</h1>
        <?php if ($message !== ''): ?>
            <p role="status" class="<?php echo $message_type === 'error' ? 'error-message' : 'success-message'; ?>">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <section aria-labelledby="saved-addresses-title">
            <h2 id="saved-addresses-title">Địa chỉ đã lưu</h2>
            <?php if ($addresses->num_rows === 0): ?>
                <p>Bạn chưa lưu địa chỉ nhận hàng nào.</p>
            <?php else: ?>
                <?php while ($address_row = $addresses->fetch_assoc()): ?>
                    <article>
                        <h3><?php echo htmlspecialchars($address_row['recipient_name'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php if ((int) $address_row['is_default'] === 1): ?><small>(Mặc định)</small><?php endif; ?>
                        </h3>
                        <p><?php echo htmlspecialchars($address_row['phone'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><?php echo htmlspecialchars($address_row['address'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if ((int) $address_row['is_default'] !== 1): ?>
                            <form method="post">
                                <input type="hidden" name="action" value="default">
                                <input type="hidden" name="address_id" value="<?php echo (int) $address_row['id']; ?>">
                                <button type="submit">Đặt làm mặc định</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="address_id" value="<?php echo (int) $address_row['id']; ?>">
                            <button type="submit">Xóa địa chỉ</button>
                        </form>
                    </article>
                <?php endwhile; ?>
            <?php endif; ?>
        </section>

        <section aria-labelledby="add-address-title">
            <h2 id="add-address-title">Thêm địa chỉ mới</h2>
            <form method="post">
                <input type="hidden" name="action" value="add">
                <label for="recipient_name">Tên người nhận</label>
                <input id="recipient_name" name="recipient_name" maxlength="150" required>
                <label for="phone">Số điện thoại</label>
                <input id="phone" name="phone" type="tel" maxlength="20" required>
                <label for="address">Địa chỉ</label>
                <textarea id="address" name="address" maxlength="500" required></textarea>
                <label>
                    <input type="checkbox" name="is_default" value="1">
                    Đặt làm địa chỉ mặc định
                </label>
                <button type="submit">Lưu địa chỉ</button>
            </form>
        </section>
    </main>
</body>
</html>
<?php
$addresses_stmt->close();
$conn->close();
?>
