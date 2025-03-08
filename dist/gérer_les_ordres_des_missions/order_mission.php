<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit;
}

// Fetch order missions
if ($_SESSION['user_role'] == 'admin') {
    $order_missions = $conn->query("SELECT * FROM OrderMission")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $user_id = $_SESSION['user_id'];
    $order_missions = $conn->query("SELECT * FROM OrderMission WHERE technicien_id = '$user_id'")->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Mission Page</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <section class="form-container">
        <h1>Order Missions</h1>
        <table>
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Direction</th>
                    <th>Destination</th>
                    <th>Motif</th>
                    <th>Moyen de transport</th>
                    <th>Date de depart</th>
                    <th>Date de retour</th>
                    <th>Technicien</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_missions as $order_mission) : ?>
                    <tr>
                        <td><?php echo $order_mission['order_num']; ?></td>
                        <td><?php echo $order_mission['direction']; ?></td>
                        <td><?php echo $order_mission['destination']; ?></td>
                        <td><?php echo $order_mission['motif']; ?></td>
                        <td><?php echo $order_mission['moyen_tr']; ?></td>
                        <td><?php echo $order_mission['date_depart']; ?></td>
                        <td><?php echo $order_mission['date_retour']; ?></td>
                        <td>
                            <?php
                            $technicien_id = $order_mission['technicien_id'];
                            $select = $conn->prepare("SELECT nom, prenom FROM Users WHERE user_id = ?");
                            $select->execute([$technicien_id]);
                            $technicien = $select->fetch(PDO::FETCH_ASSOC);
                            echo $technicien['nom'] . ' ' . $technicien['prenom'];
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

</body>
</html>