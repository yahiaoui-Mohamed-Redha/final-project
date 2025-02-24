<?php
include '../../app/config.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $stmt = $conn->prepare("UPDATE Users SET etat_compte = 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);

    header('location: manage_users.php');
    exit();
} else {
    header('location: manage_users.php');
    exit();
}
?>