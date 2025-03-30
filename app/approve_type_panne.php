<?php
include 'config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('location: ../../index.php');
    exit();
}

if (isset($_GET['id'])) {
    $approve_stmt = $conn->prepare("SELECT * FROM propos_type WHERE propos_id = :id");
    $approve_stmt->bindParam(':id', $_GET['id']);
    $approve_stmt->execute();
    $proposed_type = $approve_stmt->fetch(PDO::FETCH_ASSOC);

    if ($proposed_type) {
        $insert_stmt = $conn->prepare("INSERT INTO Type_panne (type_name, description) VALUES (:type_name, :description)");
        $insert_stmt->bindParam(':type_name', $proposed_type['type_name']);
        $insert_stmt->bindParam(':description', $proposed_type['description']);
        $insert_stmt->execute();

        $update_stmt = $conn->prepare("UPDATE propos_type SET status = 'approved' WHERE propos_id = :id");
        $update_stmt->bindParam(':id', $_GET['id']);
        $update_stmt->execute();
    }
    
    header('Location: ../dist/gerer_les_types_des_panne/manage_type_panne.phps?success=The+proposed+type+has+been+approved');
    exit();
} else {
    header('Location: ../dist/gerer_les_types_des_panne/manage_type_panne.php');
    exit();
}
?>