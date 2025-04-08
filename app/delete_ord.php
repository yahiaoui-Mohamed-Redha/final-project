<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location: ../../index.php');
    exit;
}

$order_num =$_GET['order_num'];

try {
    // check if this item is existing
    $checkPanne = $conn->prepare("SELECT order_num FROM ordermission WHERE order_num = :order_num");
    $checkPanne->execute(['order_num' => $order_num]);
    
    if ($checkPanne->rowCount() === 0) {
        $_SESSION['error'] = "Le order mission n'existe pas ou a déjà été supprimée.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // delete it ...
    $deletePanne = $conn->prepare("DELETE FROM ordermission WHERE order_num = :order_num");
    $deletePanne->execute(['order_num' => $order_num]);

    $_SESSION['success'] = "Le order mission a été supprimée avec succès.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;

} catch (PDOException $e) {
    // of an error in the database
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
