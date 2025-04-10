<?php
include '../../app/config.php';
session_start(); // Start the session for using $_SESSION variables

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    try {
        $stmt = $conn->prepare("UPDATE Users SET etat_compte = 0 WHERE user_id = ?");
        $result = $stmt->execute([$user_id]);
        
        if ($result && $stmt->rowCount() > 0) {
            // Success - account deactivated
            $_SESSION['success'] = "Le compte a été désactivé avec succès!";
        } else {
            // No rows affected
            $_SESSION['error'] = "Aucun changement effectué. Le compte n'a pas été trouvé ou était déjà désactivé.";
        }
    } catch (PDOException $e) {
        // Database error
        $_SESSION['error'] = "Une erreur est survenue lors de la désactivation du compte: " . $e->getMessage();
    }
    
    header('location: ../admin_page.php?contentpage=gerer_les_comptes/manage_users.php');
    exit();
} else {
    // No ID provided
    $_SESSION['error'] = "Identifiant de l'utilisateur non spécifié.";
    header('location: ../admin_page.php?contentpage=gerer_les_comptes/manage_users.php');
    exit();
}
?>