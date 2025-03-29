<?php
session_start();
include '../app/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$userId = $_SESSION['user_id'];
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND notification_status = 'unread'");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['count' => (int)$result['unread_count']]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}
?>