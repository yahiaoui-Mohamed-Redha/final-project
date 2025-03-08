<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('location:login.php');
    exit;
}

// Fetch all techniciens
$techniciens = $conn->query("SELECT u.user_id, u.username, u.nom, u.prenom, r.role_nom FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE r.role_nom = 'technicien'")->fetchAll(PDO::FETCH_ASSOC);

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Order Mission Create</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <section class="form-container">
        <h1>Create Order Mission</h1>
        <form action="" method="post">
            <label for="direction">Direction:</label>
            <input type="text" id="direction" name="direction" required>

            <label for="destination">Destination:</label>
            <input type="text" id="destination" name="destination" required>

            <label for="motif">Motif:</label>
            <textarea id="motif" name="motif" required></textarea>

            <label for="moyen_tr">Moyen de transport:</label>
            <input type="text" id="moyen_tr" name="moyen_tr" required>

            <label for="date_depart">Date de depart:</label>
            <input type="date" id="date_depart" name="date_depart" required>

            <label for="date_retour">Date de retour:</label>
            <input type="date" id="date_retour" name="date_retour" required>

            <label for="technicien_id">Technicien:</label>
            <select id="technicien_id" name="technicien_id" required>
                <?php foreach ($techniciens as $technicien) : ?>
                    <option value="<?php echo $technicien['user_id']; ?>"><?php echo $technicien['nom'] . ' ' . $technicien['prenom']; ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Create Order Mission</button>
        </form>
    </section>

</body>
</html>