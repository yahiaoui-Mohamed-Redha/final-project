<?php 
// delete_type_panne.php
include 'config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    $_SESSION['error'] = "Unauthorized access. Please login with admin privileges.";
    header('location: ../../index.php');
    exit();
}

if (isset($_GET['id'])) {
    try {
        $delete_stmt = $conn->prepare("DELETE FROM Type_panne WHERE type_id = :id");
        $delete_stmt->bindParam(':id', $_GET['id']);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Type de panne supprimé avec succès!";
        } else {
            $_SESSION['error'] = "Échec de la suppression du type de panne.";
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
    }
    
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
} else {
    $_SESSION['error'] = "ID du type de panne non spécifié.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
?>