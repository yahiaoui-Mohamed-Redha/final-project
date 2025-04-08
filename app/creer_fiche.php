<?php
include 'config.php';
session_start();

// Verify user authorization - only Receveurs can create fiches
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Receveur') {
    header('Location:../../index.php');
    exit;
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
            $stmt = $conn->prepare("INSERT INTO FicheIntervention 
                                  (fiche_num, fiche_date, compte_rendu, observation, panne_num, technicien_id, receveur_id) 
                                  VALUES (NULL, NOW(), ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['compte_rendu'],
                $_POST['observation'],
                $_POST['panne_num'],
                $technicien_id,
                $_SESSION['user_id']
            ]);
            
            $fiche_num = $conn->lastInsertId();
            
            // Send notification
            sendNotificationToTechnician($technicien_id, $fiche_num);
            
            // Optionally create an order mission to track the notification
            createNotificationOrderMission($technicien_id, $fiche_num);
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Fiche(s) d'intervention créée(s) avec succès et techniciens notifiés!";
        header('Location:  ../dist/receveur_page.php?contentpage=gerer_les_fiche_dintervention/creer_fiche.php');
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: ../dist/receveur_page.php?contentpage=gerer_les_fiche_dintervention/creer_fiche.php');
        exit;
    }
}

function sendNotificationToTechnician($technicien_id, $fiche_num) {
    // Store notification in session (temporary)
    if (!isset($_SESSION['notifications'])) {
        $_SESSION['notifications'] = [];
    }
    
    $_SESSION['notifications'][$technicien_id][] = [
        'fiche_num' => $fiche_num,
        'message' => "Nouvelle fiche d'intervention #$fiche_num assignée",
        'timestamp' => time()
    ];
    
    // In a real application, you might also:
    // 1. Send an email
    // 2. Trigger a real-time notification
}

function createNotificationOrderMission($technicien_id, $fiche_num) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO OrderMission 
                              (direction, destination, motif, moyen_tr, date_depart, date_retour, technicien_id) 
                              VALUES (?, ?, ?, ?, NOW(), NOW(), ?)");
        $stmt->execute([
            'Notification',
            'Fiche #' . $fiche_num,
            'Nouvelle fiche d\'intervention assignée',
            'Système',
            $technicien_id
        ]);
    } catch (PDOException $e) {
        // Log error but don't stop the process
        error_log("Error creating order mission: " . $e->getMessage());
    }
}