<?php
session_start();
include '../app/config.php';

// Check if the user is logged in and has the 'receveur' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'receveur') {
    header('Location: login.php');
    exit;
}

// Fetch the logged-in user's details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$select->execute([$user_id]);
$receveur = $select->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receveur Dashboard</title>
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
        main {
            padding: 20px;
        }
        .dashboard {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .dashboard h1 {
            margin-top: 0;
        }
        .options {
            margin-top: 20px;
        }
        .options ul {
            list-style-type: none;
            padding: 0;
        }
        .options ul li {
            margin-bottom: 10px;
        }
        .options ul li a {
            display: block;
            padding: 10px;
            background-color: #28a745;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
        }
        .options ul li a:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <header>
        <h1>Receveur Dashboard</h1>
    </header>
    <main>
        <div class="dashboard">
            <h1>Welcome, <?php echo htmlspecialchars($receveur['username']); ?>!</h1>
            <p>You are logged in as a Receveur.</p>

            <!-- Link to propose a new Type_panne -->
            <div class="options">
                <h2>Your Options:</h2>
                <ul>
                    <li><a href="propos_type.php">Propose a New Type_panne</a></li>
                    <li><a href="view_requests.php">View Requests</a></li>
                    <li><a href="profile.php">Edit Profile</a></li>
                    <li><a href="signaler_des_panne.php">signaler des panne</a></li>
                    <li><a href="panne_view.php?receveur_id=<?php echo $user_id; ?>">View Pannes</a></li>
                    <li><a href="rapport_view.php?receveur_id=<?php echo $user_id; ?>">View Rapports</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </main>
</body>
</html>