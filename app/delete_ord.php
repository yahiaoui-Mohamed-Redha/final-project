<?php 
include 'config.php'; 
session_start(); 

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin'])) {
    header('Location: ../../index.php');
    exit;
}

$order_num = $_GET['order_num'];

try {
    // check if this item exists
    $checkPanne = $conn->prepare("SELECT order_num FROM ordermission WHERE order_num = :order_num");
    $checkPanne->execute(['order_num' => $order_num]);
    
    if ($checkPanne->rowCount() === 0) {
        $_SESSION['error'] = "Le Ordre de mission n'existe pas ou a déjà été supprimée.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Instead of deleting, update the archived flag to true/1
    $archivePanne = $conn->prepare("UPDATE ordermission SET archived = true WHERE order_num = :order_num");
    $archivePanne->execute(['order_num' => $order_num]);
    
    $_SESSION['success'] = "Le Ordre de mission a été supprimée avec succès.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>