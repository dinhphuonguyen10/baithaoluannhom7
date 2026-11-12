<?php
require_once __DIR__ . '/../include/admin_guard.php';
$appBaseUrl = applicationBaseUrl();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
// Tác dụng: Xác thực phiên và cấp quyền chăm sóc khách hàng cho nhân viên/quản lý
$isManager  = isManagerRole($_SESSION['admin_role'] ?? $_SESSION['role'] ?? null);
$roleLabel  = $isManager ? 'Quản lý' : 'Nhân viên';
$adminUsername = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? '';
$adminCurrentPage = 'chat.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat khách hàng - ÉCRIN Jewelry</title>
    <link rel="stylesheet" href="<?php echo $escape($appBaseUrl . '/admin/style.css'); ?>">
    <style>
        .chat-preview { display: grid; grid-template-columns: minmax(220px, 320px) 1fr; min-height: 460px; overflow: hidden; border: 1px solid var(--line); border-radius: 14px; background: #fff; }
        .chat-list, .chat-thread { padding: 20px; }
        .chat-list { border-right: 1px solid var(--line); }
        .chat-thread { display: flex; flex-direction: column; }
        .chat-empty { display: grid; flex: 1; place-items: center; color: var(--muted); text-align: center; }
        .chat-preview textarea { width: 100%; min-height: 80px; padding: 10px; border: 1px solid var(--line); border-radius: 8px; font: inherit; }
        .chat-preview button { margin-top: 8px; padding: 9px 14px; border: 0; border-radius: 7px; background: #d7dce2; color: #657180; cursor: not-allowed; font: inherit; }
        @media (max-width: 680px) { .chat-preview { grid-template-columns: 1fr; } .chat-list { border-right: 0; border-bottom: 1px solid var(--line); } }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../include/admin_sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="breadcrumb">Trang chủ <span>/</span> <strong>Quản lý chat</strong></div>
            <div class="admin-profile">
                <div class="avatar"><?php echo $isManager ? 'QL' : 'NV'; ?></div>
                <div class="admin-info"><strong><?php echo $escape($adminUsername); ?></strong><small><?php echo $escape($roleLabel); ?></small></div>
            </div>
        </header>
        <section class="page-content">
            <div class="welcome-row"><div><h1>Chat với khách hàng</h1></div></div>
            <section class="chat-preview" aria-label="Giao diện chat">
                <aside class="chat-list"><h2>Cuộc trò chuyện</h2><p>Chưa có dữ liệu hội thoại.</p></aside>
                <div class="chat-thread">
                    <h2>Khung hội thoại</h2>
                    <div class="chat-empty">Chọn một cuộc trò chuyện để xem tin nhắn.</div>
                    <label for="chat-message">Nội dung trả lời</label>
                    <textarea id="chat-message" placeholder="Nhập tin nhắn trả lời..." disabled></textarea>
                    <button type="button" disabled>Gửi trả lời</button>
                </div>
            </section>
        </section>
    </main>
</div>
</body>
</html>