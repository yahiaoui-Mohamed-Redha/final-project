<?php
include 'config.php';
session_start();

// Check if panne_num is provided
if (!isset($_GET['panne_num'])) {
    header('Location: ../../error.php'); // Redirect to an error page or back to the list
    exit;
}

$panne_num = $_GET['panne_num'];

// Prepare the delete query
$query = "DELETE FROM Panne WHERE panne_num = :panne_num";
$stmt = $conn->prepare($query);

try {
    $stmt->execute(['panne_num' => $panne_num]);

    // Check if the deletion was successful
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Panne deleted successfully.";
    } else {
        $_SESSION['error_message'] = "No panne found with the provided ID.";
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}

// Redirect back to the panne list page
header('Location: ../dist/admin_page.php?contentpage=gerer_les_panne/gerer_pn.php');
exit;

?>