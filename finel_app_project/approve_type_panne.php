<?php
include 'config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Handle form submission for approving a proposed Type_panne
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_type'])) {
    $propos_id = $_POST['propos_id'];

    // Fetch the proposed type from the database
    $stmt_proposed = $conn->prepare("SELECT * FROM propos_type WHERE propos_id = ?");
    $stmt_proposed->execute([$propos_id]);
    $proposed_type = $stmt_proposed->fetch(PDO::FETCH_ASSOC);

    // Insert the proposed type into the Type_panne table
    $insert_stmt = $conn->prepare("INSERT INTO Type_panne (type_name, description) VALUES (:type_name, :description)");
    $insert_stmt->bindParam(':type_name', $proposed_type['type_name']);
    $insert_stmt->bindParam(':description', $proposed_type['description']);
    $insert_stmt->execute();

    // Update the status of the proposed type to 'approved'
    $update_stmt = $conn->prepare("UPDATE propos_type SET status = 'approved' WHERE propos_id = ?");
    $update_stmt->execute([$propos_id]);

    // Display a success message
    $success_message = "The proposed type has been approved.";
}

// Fetch all proposed types from the database
$stmt_proposed = $conn->prepare("SELECT * FROM propos_type WHERE status = 'pending'");
$stmt_proposed->execute();
$proposed_types = $stmt_proposed->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Type_panne</title>
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
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 20px;
            width: 300px;
        }
        .card h2 {
            margin-top: 0;
        }
        .card p {
            margin-bottom: 20px;
        }
        .actions {
            display: flex;
            justify-content: space-between;
        }
        .actions a {
            color: #007bff;
            text-decoration: none;
            margin-right: 10px;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .actions a.delete {
            color: #dc3545;
        }
        .success-message {
            color: #28a745;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Approve Type_panne</h1>
    </header>
    <main>
        <!-- Display success message if a type was approved -->
        <?php if (isset($success_message)) { ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php } ?>

        <!-- Display all proposed types -->
        <div class="card-container">
            <?php foreach ($proposed_types as $proposed_type) { ?>
            <?php
            $receveur_id = $proposed_type['receveur_id'];
            $stmt_receveur = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt_receveur->execute([$receveur_id]);
            $receveur = $stmt_receveur->fetch(PDO::FETCH_ASSOC);
            ?>
            <div class="card">
                <h2><?php echo htmlspecialchars($proposed_type['type_name']); ?></h2>
                <p><?php echo htmlspecialchars($proposed_type['description']); ?></p>
                <p>Proposed By: <?php echo htmlspecialchars($receveur['username']); ?></p>
                <div class="actions">
                    <form method="POST">
                        <input type="hidden" name="propos_id" value="<?php echo $proposed_type['propos_id']; ?>">
                        <button type="submit" name="approve_type">Approve</button>
                    </form>
                    <a href="manage_type_panne.php?reject_id=<?php echo $proposed_type['propos_id']; ?>" class="delete" onclick="return confirm('Are you sure you want to reject this proposed type?');">Reject</a>
                </div>
            </div>
            <?php } ?>
        </div>
    </main>
</body>
</html>