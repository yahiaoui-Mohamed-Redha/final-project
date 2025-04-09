<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin'])) {
    header('Location: ../../index.php');
    exit;
}

$rap_num = $_GET['rap_num'];

try {
    // Check if this report exists
    $checkRapport = $conn->prepare("SELECT rap_num FROM rapport WHERE rap_num = :rap_num");
    $checkRapport->execute(['rap_num' => $rap_num]);
    
    if ($checkRapport->rowCount() === 0) {
        $_SESSION['error'] = "Le rapport n'existe pas ou a déjà été supprimé.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Check if this report is connected to any panne records
    $checkConnectedPannes = $conn->prepare("SELECT COUNT(*) as count FROM panne WHERE rap_num = :rap_num AND panne.archived = 0 OR panne.archived IS NULL");
    $checkConnectedPannes->execute(['rap_num' => $rap_num]);
    $panneCount = $checkConnectedPannes->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($panneCount > 0) {
        // This report is connected to panne records and should not be archived
        $_SESSION['error'] = "Ce rapport ne peut pas être supprimée car il est lié à {$panneCount} enregistrement(s) de panne.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // If we reach here, the report is not connected to any panne records, so it's safe to archive
    $archiveRapport = $conn->prepare("UPDATE rapport SET archived = true WHERE rap_num = :rap_num");
    $archiveRapport->execute(['rap_num' => $rap_num]);
    
    $_SESSION['success'] = "Le rapport a été supprimée avec succès.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>