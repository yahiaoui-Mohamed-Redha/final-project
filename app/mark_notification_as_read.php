<?php
session_start();
include '../app/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$notificationId = $data['notification_id'];

// Update the notification status to "read"
$stmt = $conn->prepare("UPDATE notifications SET notification_status = 'read' WHERE id = ? AND user_id = ?");
$stmt->execute([$notificationId, $_SESSION['user_id']]);

echo json_encode(['success' => true]);
?>