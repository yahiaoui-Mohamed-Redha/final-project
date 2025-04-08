<?php
// create_type_panne
include 'config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('location: ../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_type'])) {
    $type_name = $_POST['type_name'];
    $description = $_POST['description'];
    
    // Validate input
    if (empty($type_name)) {
        $_SESSION['error'] = "Le nom du type ne peut pas être vide!";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
    try {
        $insert_stmt = $conn->prepare("INSERT INTO Type_panne (type_name, description) VALUES (:type_name, :description)");
        $insert_stmt->bindParam(':type_name', $type_name);
        $insert_stmt->bindParam(':description', $description);
        $insert_stmt->execute();
        
        $_SESSION['success'] = "Nouveau type panne créé avec succès";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la création du type panne: " . $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
?>