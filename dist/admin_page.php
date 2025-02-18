<?php
session_start();

// Include database configuration
include '../app/config.php';

// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect to the login page or show an error message
    header('location: login.php');
    exit(); // Stop further execution
}

// Fetch admin details (optional)
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
$select->execute([$user_id]);
$admin = $select->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="../src/output.css">
</head>
<!-- <body class=" bg-amber-400">
    <header>
        <h1 class=" text-amber-200">Welcome to the Admin Dashboard</h1>
        <p>Hello, <?php echo htmlspecialchars($admin['username']); ?>!</p>
        <a href="../app/logout.php">Logout</a>
    </header>

    <main>
        <div class="dashboard-container">
            <h2>Admin Controls</h2>
            <ul>
                <li><a href="manage_users.php?admin_id=<?php echo $user_id; ?>">Manage Users</a></li>
                <li><a href="create_users.php?admin_id=<?php echo $user_id; ?>">Create Users</a></li>
                <li><a href="view_reports.php?admin_id=<?php echo $user_id; ?>">View Reports</a></li>
                <li><a href="settings.php?admin_id=<?php echo $user_id; ?>">Settings</a></li>
                <li><a href="manage_type_panne.php?admin_id=<?php echo $user_id; ?>">Manage Type Panne</a></li>
                <li><a href="approve_type_panne.php?admin_id=<?php echo $user_id; ?>">Approve Type Panne</a></li>
                <li><a href="signaler_des_panne.php">signaler des panne</a></li>
                <li><a href="panne_view.php?admin_id=<?php echo $user_id; ?>">View Pannes</a></li>
                <li><a href="rapport_view.php?admin_id=<?php echo $user_id; ?>">View Rapports</a></li>
                <li><a href="admin_order_mission_create.php?admin_id=<?php echo $user_id; ?>">Create Order Mission</a></li>
                <li><a href="order_mission.php?admin_id=<?php echo $user_id; ?>">View Order Missions</a></li>
            </ul>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
    </footer>

</body> -->
</html>