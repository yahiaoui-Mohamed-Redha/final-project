<?php
include '../app/config.php';
session_start();

// Check if the user is logged in and has the technicien role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'Technicien') {
    header('location:login.php');
    exit;
}

// Fetch technicien details (optional)
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
$select->execute([$user_id]);
$technicien = $select->fetch(PDO::FETCH_ASSOC);

// Fetch notifications for technicien
$notifications = $conn->query("SELECT * FROM Notifications WHERE user_id = '$user_id'")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technicien Page</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <section class="form-container">
        <h1>Welcome, Technicien!</h1>
        <p>You are logged in as a technicien.</p>
        <li><a href="panne_view.php?technicien_id=<?php echo $user_id; ?>">View Pannes</a></li>
        <li><a href="rapport_view.php?technicien_id=<?php echo $user_id; ?>">View Rapports</a></li>
        <li><a href="order_mission.php?technicien_id=<?php echo $user_id; ?>">View Order Missions</a></li>
        <div class="notifications">
            <h2>Notifications</h2>
            <ul>
                <?php foreach ($notifications as $notification) : ?>
                    <li>
                        <span><?php echo $notification['notification_message']; ?></span>
                        <?php if ($notification['notification_link']) : ?>
                            <a href="<?php echo $notification['notification_link']; ?>">View</a>
                        <?php endif; ?>
                        <span class="status <?php echo $notification['notification_status']; ?>"><?php echo $notification['notification_status']; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="logout.php" class="btn">Logout</a>
    </section>

</body>
</html>