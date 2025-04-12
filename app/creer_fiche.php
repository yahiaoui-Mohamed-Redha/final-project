<?php
include 'config.php';
session_start();

// Verify user authorization - only Receveurs can create fiches
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Receveur') {
    header('Location:../../index.php');
    exit;
}
// Function to generate a unique fiche_num
function generateUniqueFicheNum($conn, $prefix = 'FIC') {
    // Fetch the last fiche_num from the FicheIntervention table
    $stmt_last_fiche_num = $conn->prepare("SELECT fiche_num FROM FicheIntervention ORDER BY fiche_num DESC LIMIT 1");
    $stmt_last_fiche_num->execute();
    $last_fiche_num = $stmt_last_fiche_num->fetchColumn();

    // If no fiche_num exists, start from 0001
    if (empty($last_fiche_num)) {
        $next_fiche_num = 1;
    } else {
        // Extract the numeric part of the last fiche_num and increment it
        $parts = explode('-', $last_fiche_num);
        $last_number = intval(end($parts));
        $next_fiche_num = $last_number + 1;
    }

    // Ensure the number is always 4 digits (e.g., 0001, 0002, etc.)
    $formatted_number = str_pad($next_fiche_num, 4, '0', STR_PAD_LEFT);
    $fiche_num = $prefix . '-' . $formatted_number;

    return $fiche_num;
}

// Function to generate a unique panne_num
function generateUniquePanneNum($conn, $postal_code) {
    // Fetch the last panne_num from the panne table
    $stmt_last_panne_num = $conn->prepare("SELECT panne_num FROM panne ORDER BY panne_num DESC LIMIT 1");
    $stmt_last_panne_num->execute();
    $last_panne_num = $stmt_last_panne_num->fetchColumn();

    // If no panne_num exists, start from 0001
    if (empty($last_panne_num)) {
        $next_pan_num = 1;
    } else {
        // Extract the numeric part of the last panne_num and increment it
        $last_number = intval(substr($last_panne_num, 0, 4));
        $next_pan_num = $last_number + 1;
    }

    // Ensure the number is always 4 digits
    while (true) {
        $formatted_number = str_pad($next_pan_num, 4, '0', STR_PAD_LEFT);
        $panne_num = $formatted_number . '-' . $postal_code;

        // Check if the panne_num already exists
        $stmt_check_panne_num = $conn->prepare("SELECT COUNT(*) FROM panne WHERE panne_num = :panne_num");
        $stmt_check_panne_num->execute(['panne_num' => $panne_num]);
        $count = $stmt_check_panne_num->fetchColumn();

        if ($count == 0) {
            return $panne_num;
        }

        $next_pan_num++;
    }
}

function sendNotificationToTechnician($technicien_id, $fiche_num) {
    if (!isset($_SESSION['notifications'])) {
        $_SESSION['notifications'] = [];
    }
    
    $_SESSION['notifications'][$technicien_id][] = [
        'fiche_num' => $fiche_num,
        'message' => "Nouvelle fiche d'intervention $fiche_num assignée",
        'timestamp' => time()
    ];
}



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Validate required fields
        if (empty($_POST['techniciens']) || empty($_POST['panne_num']) || empty($_POST['compte_rendu'])) {
            throw new Exception("Tous les champs obligatoires doivent être remplis");
        }
        
        // Create the fiche for each selected technician
        foreach ($_POST['techniciens'] as $technicien_id) {
            // Generate unique fiche_num for each technician
            $fiche_num = generateUniqueFicheNum($conn);
            
            $stmt = $conn->prepare("INSERT INTO FicheIntervention 
                                  (fiche_num, fiche_date, compte_rendu, observation, panne_num, technicien_id, receveur_id) 
                                  VALUES (?, NOW(), ?, ?, ?, ?, ?)");
            $stmt->execute([
                $fiche_num,
                $_POST['compte_rendu'],
                $_POST['observation'],
                $_POST['panne_num'],
                $technicien_id,
                $_SESSION['user_id']
            ]);
            
            // Send notification
            sendNotificationToTechnician($technicien_id, $fiche_num);
            
            // Create order mission
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Fiche(s) d'intervention créée(s) avec succès et techniciens notifiés!";
        header('Location: ../dist/receveur_page.php?contentpage=gerer_les_fiche_dintervention/creer_fiche.php');
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: ../dist/receveur_page.php?contentpage=gerer_les_fiche_dintervention/creer_fiche.php');
        exit;
    }
}