<?php
$pageTitle = 'Hệ thống cửa hàng - ÉCRIN JEWELRY';
include __DIR__ . '/../include/header.php';
?>
    <style>
        .store-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
        }

        .store-sidebar,
        .store-map-area {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgb(0 0 0 / 5%);
        }

        .store-sidebar {
            height: fit-content;
            padding: 24px;
        }

        .store-sidebar h1 {
            margin: 0 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border);
            color: var(--navy);
            font-size: 1.5rem;
        }

        .search-store {
            margin-bottom: 20px;
        }

        .search-store input {
            width: 100%;
            padding: 12px;
            border: 1px solid #eee;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .store-list {
            max-height: 500px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .store-item {
            display: block;
            width: 100%;
            margin-bottom: 12px;
            padding: 15px;
            border: 1px solid #f5f5f5;
            border-radius: 12px;
            background: #fff;
            color: inherit;
            cursor: pointer;
            font: inherit;
            text-align: left;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .store-item:hover,
        .store-item.active {
            border-color: var(--gold);
            background: var(--cream);
        }

        .store-item h3 {
            margin: 0 0 5px;
            color: #1a153a;
            font-size: 1rem;
        }

        .store-item p {
            margin: 0;
            color: #666;
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .store-map-area {
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .map-placeholder {
            min-height: 450px;
        }

        .map-placeholder iframe {
            display: block;
            width: 100%;
            height: 450px;
            border: 0;
        }

        .store-detail-card {
            padding: 24px;
            border-top: 1px solid #eee;
        }

        .store-detail-card h2 {
            margin: 0 0 15px;
            color: var(--gold);
            font-size: 1.35rem;
        }

        .store-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .info-box h3 {
            margin: 0 0 5px;
            color: #999;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .info-box p {
            margin: 0;
            color: #333;
            font-weight: 600;
        }

        .store-empty {
            display: none;
            color: #667085;
            text-align: center;
        }

        @media (max-width: 992px) {
            .store-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <main class="store-container">
        <aside class="store-sidebar">
            <h1>Tìm cửa hàng</h1>
            <div class="search-store">
                <label for="store-search">Tìm theo khu vực</label>
                <input type="search" placeholder="Ví dụ: Cầu Giấy" id="store-search">
            </div>
            <div class="store-list" id="store-list">
                <button class="store-item active" type="button" data-id="1">
                    <h3>ÉCRIN JEWELRY Cầu Giấy</h3>
                    <p>123 Xuân Thủy, Dịch Vọng, Cầu Giấy, Hà Nội</p>
                </button>
                <button class="store-item" type="button" data-id="2">
                    <h3>ÉCRIN JEWELRY Hoàn Kiếm</h3>
                    <p>45 Hàng Đào, Hoàn Kiếm, Hà Nội</p>
                </button>
                <button class="store-item" type="button" data-id="3">
                    <h3>ÉCRIN JEWELRY Quận 1</h3>
                    <p>89 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP. HCM</p>
                </button>
                <button class="store-item" type="button" data-id="4">
                    <h3>ÉCRIN JEWELRY Quận 10</h3>
                    <p>256 Sư Vạn Hạnh, Phường 12, Quận 10, TP. HCM</p>
                </button>
                <button class="store-item" type="button" data-id="5">
                    <h3>ÉCRIN JEWELRY Đà Nẵng</h3>
                    <p>12 Lê Duẩn, Hải Châu, Đà Nẵng</p>
                </button>
                <p class="store-empty" id="store-empty">Không tìm thấy cửa hàng phù hợp.</p>
            </div>
        </aside>

        <section class="store-map-area" aria-label="Bản đồ và thông tin cửa hàng">
            <div class="map-placeholder">
                <iframe id="google-map" title="Bản đồ vị trí cửa hàng Écrin Jewelry"
                    src="https://www.google.com/maps?q=123%20Xu%C3%A2n%20Th%E1%BB%A7y%2C%20C%E1%BA%A7u%20Gi%E1%BA%A5y%2C%20H%C3%A0%20N%E1%BB%99i&output=embed"
                    loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
            <div class="store-detail-card" aria-live="polite">
                <h2 id="detail-name">ÉCRIN JEWELRY Cầu Giấy</h2>
                <div class="store-info-grid">
                    <div class="info-box">
                        <h3>Địa chỉ</h3>
                        <p id="detail-address">123 Xuân Thủy, Dịch Vọng, Cầu Giấy, Hà Nội</p>
                    </div>
                    <div class="info-box">
                        <h3>Điện thoại</h3>
                        <p id="detail-phone">024.1234.5678</p>
                    </div>
                    <div class="info-box">
                        <h3>Giờ mở cửa</h3>
                        <p>08:00 - 22:00 (Hàng ngày)</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        const stores = [
            { id: 1, name: 'ÉCRIN JEWELRY Cầu Giấy', address: '123 Xuân Thủy, Dịch Vọng, Cầu Giấy, Hà Nội', phone: '024.1234.5678' },
            { id: 2, name: 'ÉCRIN JEWELRY Hoàn Kiếm', address: '45 Hàng Đào, Hoàn Kiếm, Hà Nội', phone: '024.8888.9999' },
            { id: 3, name: 'ÉCRIN JEWELRY Quận 1', address: '89 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP. HCM', phone: '028.1111.2222' },
            { id: 4, name: 'ÉCRIN JEWELRY Quận 10', address: '256 Sư Vạn Hạnh, Phường 12, Quận 10, TP. HCM', phone: '028.4444.5555' },
            { id: 5, name: 'ÉCRIN JEWELRY Đà Nẵng', address: '12 Lê Duẩn, Hải Châu, Đà Nẵng', phone: '0236.777.888' }
        ];

        const storeItems = document.querySelectorAll('.store-item');
        const searchInput = document.getElementById('store-search');
        const emptyMessage = document.getElementById('store-empty');

        storeItems.forEach(item => {
            item.addEventListener('click', () => {
                const store = stores.find(location => location.id === Number(item.dataset.id));
                if (!store) return;

                storeItems.forEach(storeItem => storeItem.classList.toggle('active', storeItem === item));
                document.getElementById('detail-name').textContent = store.name;
                document.getElementById('detail-address').textContent = store.address;
                document.getElementById('detail-phone').textContent = store.phone;
                document.getElementById('google-map').src =
                    `https://www.google.com/maps?q=${encodeURIComponent(store.address)}&output=embed`;
            });
        });

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLocaleLowerCase('vi');
            let visibleCount = 0;

            storeItems.forEach(item => {
                const visible = item.textContent.toLocaleLowerCase('vi').includes(query);
                item.hidden = !visible;
                if (visible) visibleCount += 1;
            });

            emptyMessage.style.display = visibleCount === 0 ? 'block' : 'none';
        });
    </script>

<?php include __DIR__ . '/../include/footer.php'; ?>
