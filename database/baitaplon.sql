

CREATE DATABASE IF NOT EXISTS baitaplon
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE baitaplon;
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    role ENUM('customer', 'staff', 'manager') NOT NULL DEFAULT 'customer',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_phone_role (phone, role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(80) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name (name),
    UNIQUE KEY uq_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price_sale DECIMAL(12,2) NOT NULL,
    price_original DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount VARCHAR(50) NULL,
    gift VARCHAR(255) NULL,
    image VARCHAR(500) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY idx_products_category (category_id),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS addresses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    recipient_name VARCHAR(150) NOT NULL,
    address VARCHAR(500) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_addresses_user (user_id),
    CONSTRAINT fk_addresses_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    address_id INT UNSIGNED NULL,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(500) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    status VARCHAR(50) NOT NULL DEFAULT 'Chờ xác nhận',
    payment_status VARCHAR(50) NOT NULL DEFAULT 'Chưa thanh toán',
    payment_method VARCHAR(50) NOT NULL DEFAULT 'COD',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_orders_user (user_id),
    KEY idx_orders_address (address_id),
    KEY idx_orders_created_at (created_at),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_orders_address
        FOREIGN KEY (address_id) REFERENCES addresses (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    image VARCHAR(500) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_order_items_order (order_id),
    KEY idx_order_items_product (product_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A user row represents either a customer or a staff/manager account via users.role.
-- This keeps the ERD's customer/admin accounts in the existing authentication table.

CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_reviews_product_user (product_id, user_id),
    KEY idx_reviews_user (user_id),
    CONSTRAINT fk_reviews_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS carts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_carts_user (user_id),
    CONSTRAINT fk_carts_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cart_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cart_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cart_items_cart_product (cart_id, product_id),
    KEY idx_cart_items_product (product_id),
    CONSTRAINT fk_cart_items_cart
        FOREIGN KEY (cart_id) REFERENCES carts (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Matches the chat_messages columns used by api/chat/chat_helpers.php and chat APIs.
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    username VARCHAR(100) NOT NULL,
    sender ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    message_type ENUM('text', 'image') NOT NULL DEFAULT 'text',
    message LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_chat_messages_user_created (user_id, created_at),
    CONSTRAINT fk_chat_messages_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS banners (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    image VARCHAR(500) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS web_config (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    config_key VARCHAR(150) NOT NULL,
    config_value TEXT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_web_config_key (config_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stores (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    address VARCHAR(500) NOT NULL,
    image VARCHAR(500) NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (name, slug) VALUES
    ('Combo', 'combo'),
    ('Bông tai', 'bong-tai'),
    ('Dây chuyền', 'day-chuyen'),
    ('Nhẫn', 'nhan'),
    ('Vòng tay', 'vong-tay')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name, slug)
VALUES ('Set quà tặng', 'set-qua-tang')
ON DUPLICATE KEY UPDATE name = VALUES(name);

UPDATE products AS p
JOIN categories AS old_category ON old_category.id = p.category_id AND old_category.slug = 'combo'
JOIN categories AS gift_category ON gift_category.slug = 'set-qua-tang'
SET p.category_id = gift_category.id;

-- Sản phẩm mẫu dùng ảnh có sẵn; có thể chạy lại mà không tạo sản phẩm trùng ảnh.
INSERT INTO products (name, price_sale, price_original, discount, gift, image, stock, category_id)
SELECT
    seed.name,
    seed.price_sale,
    0,
    NULL,
    seed.gift,
    seed.image,
    seed.stock,
    c.id
FROM (
    SELECT
        categories.slug AS category_slug,
        CASE categories.slug
            WHEN 'set-qua-tang' THEN CONCAT('Set quà tặng ÉCRIN mẫu ', LPAD(image_numbers.number, 2, '0'))
            WHEN 'bong-tai' THEN CONCAT('Bông tai ÉCRIN mẫu ', LPAD(image_numbers.number, 2, '0'))
            WHEN 'day-chuyen' THEN CONCAT('Dây chuyền ÉCRIN mẫu ', LPAD(image_numbers.number, 2, '0'))
            WHEN 'nhan' THEN CONCAT('Nhẫn ÉCRIN mẫu ', LPAD(image_numbers.number, 2, '0'))
            WHEN 'vong-tay' THEN CONCAT('Vòng tay ÉCRIN mẫu ', LPAD(image_numbers.number, 2, '0'))
        END AS name,
        CASE categories.slug
            WHEN 'set-qua-tang' THEN 990000 + (image_numbers.number - 1) * 60000
            WHEN 'bong-tai' THEN 390000 + (image_numbers.number - 1) * 50000
            WHEN 'day-chuyen' THEN 650000 + (image_numbers.number - 1) * 60000
            WHEN 'nhan' THEN 790000 + (image_numbers.number - 1) * 70000
            WHEN 'vong-tay' THEN 590000 + (image_numbers.number - 1) * 50000
        END AS price_sale,
        IF(categories.slug = 'set-qua-tang', 'Hộp quà ÉCRIN', NULL) AS gift,
        CASE categories.slug
            WHEN 'set-qua-tang' THEN CONCAT('assets/quà/set', image_numbers.number, '.jpg')
            WHEN 'bong-tai' THEN CONCAT('assets/bôngtai/bong', image_numbers.number, '.jpg')
            WHEN 'day-chuyen' THEN CONCAT('assets/dây chuyền/dâychuyen/day', image_numbers.number, '.jpg')
            WHEN 'nhan' THEN CONCAT('assets/nhẫn/nhan', IF(image_numbers.number <= 6, image_numbers.number, image_numbers.number + 1), '.png')
            WHEN 'vong-tay' THEN CONCAT('assets/vòng tay/vong', image_numbers.number, '.png')
        END AS image,
        8 + MOD(image_numbers.number * 7, 18) AS stock
    FROM categories
    CROSS JOIN (
        SELECT 1 AS number UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
        UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
        UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
    ) AS image_numbers
    WHERE categories.slug IN ('set-qua-tang', 'bong-tai', 'day-chuyen', 'nhan', 'vong-tay')
      AND (categories.slug <> 'set-qua-tang' OR image_numbers.number <= 14)
) AS seed
JOIN categories c ON c.slug = seed.category_slug
WHERE NOT EXISTS (
    SELECT 1 FROM products existing WHERE existing.image = seed.image
);

INSERT INTO products (name, price_sale, price_original, image, stock, category_id)
SELECT 'Nhẫn kim cương nhân tạo', 950000, 0,
       'assets/uploads/prod_1794327751_416.webp', 20, c.id
FROM categories c
WHERE c.slug = 'nhan'
  AND NOT EXISTS (
      SELECT 1 FROM products existing
      WHERE existing.image = 'assets/uploads/prod_1794327751_416.webp'
  );

-- -----------------------------------------------------
-- SQL VIEWS TỰ ĐỘNG TÍNH TOÁN DỮ LIỆU SẢN PHẨM & LƯỢT BÁN
-- -----------------------------------------------------

-- 1. View tổng hợp thông tin sản phẩm và tính toán lượt bán thực tế từ đơn hàng
CREATE OR REPLACE VIEW view_product_details AS
SELECT 
    p.id,
    p.name,
    p.price_sale,
    p.price_original,
    p.discount,
    p.gift,
    p.image,
    p.stock,
    p.category_id,
    c.slug AS category,
    c.name AS category_name,
    COALESCE(SUM(oi.quantity), 0) AS sold_count
FROM products p
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN order_items oi ON oi.product_id = p.id
LEFT JOIN orders o ON o.id = oi.order_id AND (o.status IS NULL OR o.status NOT IN ('Đã hủy', 'huy', 'cancelled'))
GROUP BY p.id, c.slug, c.name;

-- 2. View Sản phẩm nổi bật: Hệ thống tự động lọc sản phẩm bán chạy nhất hoặc mới nhất
CREATE OR REPLACE VIEW view_featured_products AS
SELECT * FROM view_product_details
ORDER BY sold_count DESC, id DESC
LIMIT 12;

-- 3. View Gợi ý cho bạn: Hệ thống tự động đề xuất sản phẩm mới nhất
CREATE OR REPLACE VIEW view_suggested_products AS
SELECT * FROM view_product_details
ORDER BY id DESC
LIMIT 12;

-- Tài khoản admin mặc định (username: admin / password: password)
INSERT INTO users (username, password, role) VALUES
    ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager')
ON DUPLICATE KEY UPDATE password = VALUES(password), role = VALUES(role);
