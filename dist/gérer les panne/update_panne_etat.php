<?php
include '../../app/config.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the panne_num and panne_etat from the request
    $panneNum = $_POST['panne_num'];
    $panneEtat = $_POST['panne_etat'];

    // Update the panne_etat in the database
    $stmt = $conn->prepare("UPDATE Panne SET panne_etat = :panne_etat WHERE panne_num = :panne_num");
    $stmt->execute(['panne_etat' => $panneEtat, 'panne_num' => $panneNum]);

    // Return a success message
    echo 'Panne etat updated successfully';
} else {
    // Return an error message for non-POST requests
    echo 'Error updating panne etat';
}
?>