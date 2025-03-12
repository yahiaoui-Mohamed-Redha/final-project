<?php
include 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $direction = $_POST['direction'];
    $destination = $_POST['destination'];
    $motif = $_POST['motif'];
    $moyen_tr = $_POST['moyen_tr'];
    $date_depart = $_POST['date_depart'];
    $date_retour = $_POST['date_retour'];
    $technicien_id = $_POST['technicien_id'];

    // Insert order mission into database
    $insert = $conn->prepare("INSERT INTO OrderMission (direction, destination, motif, moyen_tr, date_depart, date_retour, technicien_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert->execute([$direction, $destination, $motif, $moyen_tr, $date_depart, $date_retour, $technicien_id]);

    // Send notification to technicien
    $technicien = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
    $technicien->execute([$technicien_id]);
    $technicien = $technicien->fetch(PDO::FETCH_ASSOC);
    $notification_type = "new_order";
    $notification_message = "New order mission created for you. Please check your dashboard for details.";
    $notification_link = null;
    $notification_status = "unread";

    $insert_notification = $conn->prepare("INSERT INTO Notifications (user_id, notification_type, notification_message, notification_link, notification_status) VALUES (?, ?, ?, ?, ?)");
    $insert_notification->execute([$technicien_id, $notification_type, $notification_message, $notification_link, $notification_status]);
    // Send notification via email coming soom ?

    header('location:order_mission_create.php?success=1');
    exit;
}

?>