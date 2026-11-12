<?php
// config/db.php - Kết nối MySQL, thiết lập charset utf8mb4
$host = "localhost";
$user = "root";       // User mặc định của XAMPP
$pass = "";           // Pass mặc định của XAMPP thường để trống
$dbname = "baitaplon"; // Tên database bạn cần tạo trong phpMyAdmin

// Kết nối database
$conn = new mysqli($host, $user, $pass, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Kết nối DB thất bại: " . $conn->connect_error]));
}

// Set charset để không lỗi tiếng Việt
$conn->set_charset("utf8mb4");
?>
