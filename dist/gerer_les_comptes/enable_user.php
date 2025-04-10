<?php
include '../../app/config.php';
session_start(); // Start the session to use $_SESSION variables

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    try {
        $stmt = $conn->prepare("UPDATE Users SET etat_compte = 1 WHERE user_id = ?");
        $result = $stmt->execute([$user_id]);
        
        if ($result && $stmt->rowCount() > 0) {
            // Success - account activated
            $_SESSION['success'] = "Le compte a été activé avec succès!";
        } else {
            // Error - no rows updated
            $_SESSION['error'] = "Aucun changement effectué. Le compte n'a pas été trouvé ou était déjà activé.";
        }
    } catch (PDOException $e) {
        // Database error
        $_SESSION['error'] = "Une erreur est survenue lors de l'activation du compte: " . $e->getMessage();
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