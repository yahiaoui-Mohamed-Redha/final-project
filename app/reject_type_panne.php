<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('location: ../../index.php');
    exit();
}

if (isset($_GET['id'])) {
    $delete_stmt = $conn->prepare("DELETE FROM propos_type WHERE propos_id = :id");
    $delete_stmt->bindParam(':id', $_GET['id']);
    $delete_stmt->execute();
    
    header('Location: ../dist/gerer_les_types_des_panne/manage_type_panne.php?success=The+proposed+type+has+been+rejected');
    exit();
} else {
    header('Location: ../dist/gerer_les_types_des_panne/manage_type_panne.php');
    exit();
}
?>