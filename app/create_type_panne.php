<?php
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

    $insert_stmt = $conn->prepare("INSERT INTO Type_panne (type_name, description) VALUES (:type_name, :description)");
    $insert_stmt->bindParam(':type_name', $type_name);
    $insert_stmt->bindParam(':description', $description);
    $insert_stmt->execute();
    
    header('Location: ../manage_type_panne.php?success=New+type+panne+created+successfully');
    exit();
} else {
    header('Location: ../dist/gerer_les_types_des_panne/manage_type_panne.php');
    exit();
}
?>