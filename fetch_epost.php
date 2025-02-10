<?php
include 'config.php';

$stmt = $conn->prepare("SELECT postal_code FROM epost");
$stmt->execute();
$eposts = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($eposts);
?>