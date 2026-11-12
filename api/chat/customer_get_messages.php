<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/chat_helpers.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Vui lòng đăng nhập để sử dụng chat."]);
    exit;
}

$userId = intval($_SESSION['user_id']);

ensureChatTable($conn);

$stmt = $conn->prepare("SELECT id, username, sender, message_type, message, created_at FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode(["status" => "success", "messages" => $messages]);

$stmt->close();
$conn->close();
?>