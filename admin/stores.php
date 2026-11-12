<?php
require_once __DIR__ . '/../include/admin_guard.php';
$appBaseUrl = applicationBaseUrl();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
// Tác dụng: Xác thực phiên và cấp quyền quản trị hệ thống cửa hàng
$isManager  = isManagerRole($_SESSION['admin_role'] ?? $_SESSION['role'] ?? null);
$roleLabel  = $isManager ? 'Quản lý' : 'Nhân viên';
$adminUsername = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? '';
$adminCurrentPage = 'stores.php';

// Dữ liệu cửa hàng (có thể kết nối database sau)
$stores = [
    ['id' => 1, 'name' => 'ÉCRIN JEWELRY Cầu Giấy',  'address' => '123 Xuân Thủy, Dịch Vọng, Cầu Giấy, Hà Nội',           'phone' => '024.1234.5678', 'hours' => '08:00 - 22:00', 'status' => 'active'],
    ['id' => 2, 'name' => 'ÉCRIN JEWELRY Hoàn Kiếm', 'address' => '45 Hàng Đào, Hoàn Kiếm, Hà Nội',                        'phone' => '024.8888.9999', 'hours' => '08:00 - 22:00', 'status' => 'active'],
    ['id' => 3, 'name' => 'ÉCRIN JEWELRY Quận 1',    'address' => '89 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP. HCM',       'phone' => '028.1111.2222', 'hours' => '08:00 - 22:00', 'status' => 'active'],
    ['id' => 4, 'name' => 'ÉCRIN JEWELRY Quận 10',   'address' => '256 Sư Vạn Hạnh, Phường 12, Quận 10, TP. HCM',          'phone' => '028.4444.5555', 'hours' => '08:00 - 22:00', 'status' => 'active'],
    ['id' => 5, 'name' => 'ÉCRIN JEWELRY Đà Nẵng',   'address' => '12 Lê Duẩn, Hải Châu, Đà Nẵng',                         'phone' => '0236.777.888',  'hours' => '08:00 - 22:00', 'status' => 'active'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cửa hàng - ÉCRIN Jewelry</title>
    <link rel="stylesheet" href="<?php echo $escape($appBaseUrl . '/admin/style.css'); ?>">
    <style>
        /* Tab navigation */
        .store-tabs { display: flex; gap: 4px; margin-bottom: 24px; border-bottom: 2px solid var(--line); }
        .store-tab { padding: 10px 20px; border: 0; border-bottom: 2px solid transparent; margin-bottom: -2px; background: none; color: var(--muted); font: 600 14px inherit; cursor: pointer; transition: .2s; }
        .store-tab:hover { color: var(--navy); }
        .store-tab.active { border-bottom-color: var(--navy); color: var(--navy); }

        /* Store preview (client view) */
        .store-preview-wrap { border: 1px solid var(--line); border-radius: 12px; overflow: hidden; background: #fff; }
        .store-container-admin { display: grid; grid-template-columns: 320px 1fr; min-height: 560px; }
        .store-sidebar-admin { border-right: 1px solid var(--line); padding: 20px; display: flex; flex-direction: column; gap: 12px; }
        .store-sidebar-admin h2 { margin: 0 0 10px; color: var(--navy); font-size: 1.1rem; }
        .store-search-admin input { width: 100%; padding: 9px 12px; border: 1px solid #d5dbe2; border-radius: 8px; font: inherit; }
        .store-list-admin { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; max-height: 440px; }
        .store-btn { display: block; width: 100%; padding: 13px 15px; border: 1px solid var(--line); border-radius: 10px; background: #fff; color: inherit; cursor: pointer; text-align: left; font: inherit; transition: .2s; }
        .store-btn:hover, .store-btn.active { border-color: #B09060; background: #FBF8F3; }
        .store-btn h3 { margin: 0 0 4px; color: var(--navy); font-size: 13px; }
        .store-btn p  { margin: 0; color: var(--muted); font-size: 12px; line-height: 1.4; }
        .store-empty-msg { color: var(--muted); text-align: center; font-size: 13px; display: none; }
        .store-map-col { display: flex; flex-direction: column; }
        .store-map-frame iframe { display: block; width: 100%; height: 380px; border: 0; }
        .store-detail-info { padding: 18px 22px; border-top: 1px solid var(--line); background: #FAFBFC; }
        .store-detail-info h2 { margin: 0 0 12px; color: #B09060; font-size: 1.1rem; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; }
        .info-box h3 { margin: 0 0 4px; color: var(--muted); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .info-box p  { margin: 0; color: var(--navy); font-weight: 600; font-size: 13px; }

        /* Management table */
        .mgmt-panel { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 22px; }
        .mgmt-panel h2 { margin: 0 0 16px; font-size: 1.1rem; color: var(--navy); }
        .mgmt-actions { display: flex; gap: 10px; margin-bottom: 16px; }
        .mgmt-actions button { padding: 9px 16px; border: 0; border-radius: 7px; background: var(--navy); color: #fff; font: 600 13px inherit; cursor: not-allowed; opacity: .7; }
        .mgmt-table { width: 100%; border-collapse: collapse; }
        .mgmt-table th, .mgmt-table td { padding: 11px 14px; border-bottom: 1px solid var(--line); text-align: left; font-size: 13px; }
        .mgmt-table th { color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .5px; background: #FAFBFC; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .status-active { background: #EDF6F1; color: #027a48; }
        .mgmt-table button { padding: 6px 12px; border: 0; border-radius: 6px; background: #d7dce2; color: #657180; font: inherit; cursor: not-allowed; font-size: 12px; }
        @media (max-width: 820px) { .store-container-admin { grid-template-columns: 1fr; } .store-sidebar-admin { border-right: 0; border-bottom: 1px solid var(--line); } }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../include/admin_sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="breadcrumb">Trang chủ <span>/</span> <strong>Cửa hàng</strong></div>
            <div class="admin-profile">
                <div class="avatar"><?php echo $isManager ? 'QL' : 'NV'; ?></div>
                <div class="admin-info"><strong><?php echo $escape($adminUsername); ?></strong><small><?php echo $escape($roleLabel); ?></small></div>
            </div>
        </header>
        <section class="page-content">
            <div class="welcome-row"><div>
                <h1>Hệ thống cửa hàng</h1>
                <p class="page-subtitle">Xem và quản lý thông tin các cửa hàng ÉCRIN Jewelry.</p>
            </div></div>

            <!-- Tabs -->
            <div class="store-tabs">
                <button class="store-tab active" type="button" onclick="switchTab('preview', this)">🗺 Bản đồ cửa hàng</button>
                <button class="store-tab" type="button" onclick="switchTab('manage', this)">⚙ Quản lý cửa hàng</button>
            </div>

            <!-- Tab: Bản đồ (giống client) -->
            <div id="tab-preview">
                <div class="store-preview-wrap">
                    <div class="store-container-admin">
                        <aside class="store-sidebar-admin">
                            <h2>Tìm cửa hàng</h2>
                            <div class="store-search-admin">
                                <input type="search" id="store-search" placeholder="Ví dụ: Cầu Giấy">
                            </div>
                            <div class="store-list-admin" id="store-list">
                                <?php foreach ($stores as $i => $s): ?>
                                <button class="store-btn<?php echo $i === 0 ? ' active' : ''; ?>" type="button"
                                    data-id="<?php echo $s['id']; ?>"
                                    data-name="<?php echo $escape($s['name']); ?>"
                                    data-address="<?php echo $escape($s['address']); ?>"
                                    data-phone="<?php echo $escape($s['phone']); ?>"
                                    data-hours="<?php echo $escape($s['hours']); ?>">
                                    <h3><?php echo $escape($s['name']); ?></h3>
                                    <p><?php echo $escape($s['address']); ?></p>
                                </button>
                                <?php endforeach; ?>
                                <p class="store-empty-msg" id="store-empty">Không tìm thấy cửa hàng phù hợp.</p>
                            </div>
                        </aside>
                        <div class="store-map-col">
                            <div class="store-map-frame">
                                <iframe id="google-map" title="Bản đồ cửa hàng"
                                    src="https://www.google.com/maps?q=123%20Xu%C3%A2n%20Th%E1%BB%A7y%2C%20C%E1%BA%A7u%20Gi%E1%BA%A5y%2C%20H%C3%A0%20N%E1%BB%99i&output=embed"
                                    loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                            </div>
                            <div class="store-detail-info" aria-live="polite">
                                <h2 id="detail-name"><?php echo $escape($stores[0]['name']); ?></h2>
                                <div class="info-grid">
                                    <div class="info-box"><h3>Địa chỉ</h3><p id="detail-address"><?php echo $escape($stores[0]['address']); ?></p></div>
                                    <div class="info-box"><h3>Điện thoại</h3><p id="detail-phone"><?php echo $escape($stores[0]['phone']); ?></p></div>
                                    <div class="info-box"><h3>Giờ mở cửa</h3><p id="detail-hours"><?php echo $escape($stores[0]['hours']); ?></p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Quản lý -->
            <div id="tab-manage" hidden>
                <div class="mgmt-panel">
                    <h2>Danh sách cửa hàng</h2>
                    <div class="mgmt-actions">
                        <button type="button" disabled>+ Thêm cửa hàng</button>
                    </div>
                    <div style="overflow-x:auto">
                        <table class="mgmt-table">
                            <thead>
                                <tr><th>#</th><th>Tên cửa hàng</th><th>Địa chỉ</th><th>Điện thoại</th><th>Giờ mở cửa</th><th>Trạng thái</th><th>Thao tác</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stores as $s): ?>
                                <tr>
                                    <td><?php echo (int)$s['id']; ?></td>
                                    <td><strong><?php echo $escape($s['name']); ?></strong></td>
                                    <td><?php echo $escape($s['address']); ?></td>
                                    <td><?php echo $escape($s['phone']); ?></td>
                                    <td><?php echo $escape($s['hours']); ?></td>
                                    <td><span class="status-badge status-active">● Hoạt động</span></td>
                                    <td><button type="button" disabled>Sửa</button> <button type="button" disabled>Xóa</button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
<script>
function switchTab(tab, btn) {
    document.getElementById('tab-preview').hidden = (tab !== 'preview');
    document.getElementById('tab-manage').hidden  = (tab !== 'manage');
    document.querySelectorAll('.store-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
}

// Store map interaction
const storeBtns = document.querySelectorAll('.store-btn');
const searchInput = document.getElementById('store-search');
const emptyMsg = document.getElementById('store-empty');

storeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        storeBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('detail-name').textContent    = btn.dataset.name;
        document.getElementById('detail-address').textContent = btn.dataset.address;
        document.getElementById('detail-phone').textContent   = btn.dataset.phone;
        document.getElementById('detail-hours').textContent   = btn.dataset.hours;
        document.getElementById('google-map').src =
            'https://www.google.com/maps?q=' + encodeURIComponent(btn.dataset.address) + '&output=embed';
    });
});

searchInput.addEventListener('input', () => {
    const q = searchInput.value.trim().toLocaleLowerCase('vi');
    let count = 0;
    storeBtns.forEach(btn => {
        const match = btn.textContent.toLocaleLowerCase('vi').includes(q);
        btn.hidden = !match;
        if (match) count++;
    });
    emptyMsg.style.display = count === 0 ? 'block' : 'none';
});
</script>
</body>
</html>