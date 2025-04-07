<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location: ../../index.php');
    exit;
}

$rap_num =$_GET['rap_num'];

try {
    // check if this item is existing
    $checkPanne = $conn->prepare("SELECT rap_num FROM rapport WHERE rap_num = :rap_num");
    $checkPanne->execute(['rap_num' => $rap_num]);
    
    if ($checkPanne->rowCount() === 0) {
        $_SESSION['error'] = "Le rapport n'existe pas ou a déjà été supprimée.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // delete it ...
    $deletePanne = $conn->prepare("DELETE FROM rapport WHERE rap_num = :rap_num");
    $deletePanne->execute(['rap_num' => $rap_num]);

    $_SESSION['success'] = "Le rapport a été supprimée avec succès.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;

} catch (PDOException $e) {
    // of an error in the database
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
