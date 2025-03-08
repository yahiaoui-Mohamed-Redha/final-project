<?php
include '../../app/config.php';

header('Content-Type: application/json');

try {
    // Ensure it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;
    }

    // Validate and sanitize input
    if (!isset($_POST['panne_num'], $_POST['panne_etat'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }

    $panneNum = filter_var($_POST['panne_num'], FILTER_SANITIZE_NUMBER_INT);
    $panneEtat = trim($_POST['panne_etat']);

    // Ensure `panne_num` is a valid number
    if (!is_numeric($panneNum)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid panne number']);
        exit;
    }

    // Define allowed status values
    $validEtats = ['nouveau', 'en cours', 'résolu', 'fermé'];
    if (!in_array($panneEtat, $validEtats, true)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid panne status']);
        exit;
    }

    // Update the panne_etat in the database
    $stmt = $conn->prepare("UPDATE Panne SET panne_etat = :panne_etat WHERE panne_num = :panne_num");
    $stmt->execute([
        'panne_etat' => $panneEtat,
        'panne_num' => $panneNum
    ]);

    // Check if any row was updated
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Panne etat updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No changes made or invalid panne_num']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
