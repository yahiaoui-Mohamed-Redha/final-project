<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin'])) {
    header('Location: ../../index.php');
    exit;
}

$fiche_num =$_GET['fiche_num'];

try {
    // check if this item is existing
    $checkPanne = $conn->prepare("SELECT fiche_num FROM ficheintervention WHERE fiche_num = :fiche_num");
    $checkPanne->execute(['fiche_num' => $fiche_num]);
    
    if ($checkPanne->rowCount() === 0) {
        $_SESSION['error'] = "La panne n'existe pas ou a déjà été supprimée.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Instead of deleting, update the archived flag to true/1
    $archivePanne = $conn->prepare("UPDATE ficheintervention SET archived = true WHERE fiche_num = :fiche_num");
    $archivePanne->execute(['fiche_num' => $fiche_num]);

    $_SESSION['success'] = "La panne a été supprimée avec succès.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;

} catch (PDOException $e) {
    // of an error in the database
    $_SESSION['error'] = "Erreur lors de la suppression :N" . $e->getMessage();
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
