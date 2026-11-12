<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/chat_helpers.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Vui lòng đăng nhập để sử dụng chat."]);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$message = isset($data['message']) ? trim($data['message']) : '';
$messageType = isset($data['message_type']) && $data['message_type'] === 'image' ? 'image' : 'text';

if ($message === '') {
    echo json_encode(["status" => "error", "message" => "Nội dung tin nhắn không được bỏ trống."]);
    exit;
}

$userId = intval($_SESSION['user_id']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Khách';

ensureChatTable($conn);

$stmt = $conn->prepare("INSERT INTO chat_messages (user_id, username, sender, message_type, message) VALUES (?, ?, 'customer', ?, ?)");
$stmt->bind_param("isss", $userId, $username, $messageType, $message);

if ($stmt->execute()) {
    $insertedId = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("SELECT id, username, sender, message_type, message, created_at FROM chat_messages WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $insertedId);
    $stmt->execute();
    $result = $stmt->get_result();
    $messageData = $result->fetch_assoc();
    $stmt->close();

    echo json_encode(["status" => "success", "message" => $messageData]);
} else {
    echo json_encode(["status" => "error", "message" => "Không thể gửi tin nhắn. Vui lòng thử lại sau."]);
}

$conn->close();
?>