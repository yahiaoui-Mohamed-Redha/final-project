<?php
include 'config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
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
            text-align: center;
        }

        .user-options {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }

        .user-option {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            width: 30%;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .user-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .user-option h3 {
            margin: 0 0 10px;
            font-size: 1.5em;
        }

        .user-option p {
            margin: 0;
            font-size: 1em;
            color: #666;
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
    </style>
</head>
<body>
    <header>
        <h1>Create a New User</h1>
    </header>

    <main>
        <h2>Choose the type of user to create:</h2>
        <div class="user-options">
            <!-- Option to create Admin -->
            <a href="create_admin.php" class="user-option">
                <h3>Create Admin</h3>
                <p>Create a new admin user with full access to the system.</p>
            </a>

            <!-- Option to create Technicien -->
            <a href="create_technicien.php" class="user-option">
                <h3>Create Technicien</h3>
                <p>Create a new technicien user with limited access.</p>
            </a>

            <!-- Option to create Receveur -->
            <a href="create_receveur.php" class="user-option">
                <h3>Create Receveur</h3>
                <p>Create a new receveur user with access to specific postal codes.</p>
            </a>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
    </footer>
</body>
</html>