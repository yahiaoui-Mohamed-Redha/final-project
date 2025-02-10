<?php
session_start();

// Include database configuration
include 'config.php';

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
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 24px;
        }
        header p {
            margin: 5px 0 0;
            font-size: 16px;
        }
        header a {
            color: #fff;
            text-decoration: none;
            background-color: #dc3545;
            padding: 8px 16px;
            border-radius: 4px;
            margin-top: 10px;
            display: inline-block;
        }
        header a:hover {
            background-color: #c82333;
        }
        main {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .dashboard-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .dashboard-container h2 {
            margin-top: 0;
            font-size: 22px;
            color: #333;
        }
        .dashboard-container ul {
            list-style-type: none;
            padding: 0;
        }
        .dashboard-container ul li {
            margin-bottom: 10px;
        }
        .dashboard-container ul li a {
            display: block;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        .dashboard-container ul li a:hover {
            background-color: #0056b3;
        }
        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        footer p {
            margin: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to the Admin Dashboard</h1>
        <p>Hello, <?php echo htmlspecialchars($admin['username']); ?>!</p>
        <a href="logout.php">Logout</a>
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
            </ul>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
    </footer>

</body>
</html>