<?php
function ensureChatTable($conn) {
    $createTableSql = "CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        username VARCHAR(100) NOT NULL,
        sender ENUM('customer','admin') NOT NULL DEFAULT 'customer',
        message_type ENUM('text','image') NOT NULL DEFAULT 'text',
        message LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->query($createTableSql);
    $conn->query("ALTER TABLE chat_messages MODIFY COLUMN message LONGTEXT NOT NULL");
    $conn->query("ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS message_type ENUM('text','image') NOT NULL DEFAULT 'text'");
}
?>