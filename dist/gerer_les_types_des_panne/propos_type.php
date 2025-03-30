<?php
session_start();
include '../../app/config.php';

// Check if the user is logged in and has the 'Technicien' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Receveur') {
    // Redirect to the login page or show an error message
    header('location: index.php');
    exit(); // Stop further execution
}

// Handle form submission for proposing a new Type_panne
if ($_SERVER ['REQUEST_METHOD'] == 'POST' && isset($_POST['propose_type'])) {
    $type_name = $_POST['type_name'];
    $description = $_POST['description'];
    $receveur_id = $_SESSION['user_id']; // Capture the logged-in user's ID

    // Insert the proposed type into the propos_type table
    $insert_stmt = $conn->prepare("INSERT INTO propos_type (type_name, description, receveur_id) VALUES (:type_name, :description, :receveur_id)");
    $insert_stmt->bindParam(':type_name', $type_name);
    $insert_stmt->bindParam(':description', $description);
    $insert_stmt->bindParam(':receveur_id', $receveur_id);
    $insert_stmt->execute();

    // Display a success message
    $success_message = "Your proposed type has been submitted for review.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Propose New Type_panne</title>
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
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .form-container h2 {
            margin-top: 0;
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-container input[type="text"],
        .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-container button {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #218838;
        }
        .success-message {
            color: #28a745;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Propose a New Type_panne</h1>
    </header>
    <main>
        <!-- Display success message if a type was proposed -->
        <?php if (isset($success_message)) { ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php } ?>

        <!-- Form to propose a new Type_panne -->
        <div class="form-container">
            <h2>Propose a New Type_panne</h2>
            <form method="POST">
                <label for="type_name">Type Name:</label>
                <input type="text" name="type_name" id="type_name" required>

                <label for="description">Description:</label>
                <textarea name="description" id="description" required></textarea>

                <button type="submit" name="propose_type">Submit Proposal</button>
            </form>
        </div>
    </main>
</body>
</html>