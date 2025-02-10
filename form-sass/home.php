<?php
// Include the database configuration file
include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:index.php'); // Redirect to login if not logged in
    exit();
}

// Fetch user data if logged in
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch existing forms for the user
$formsStmt = $conn->prepare("SELECT * FROM form WHERE user_id = :user_id");
$formsStmt->execute(['user_id' => $userId]);
$existingForms = $formsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Simple Form Builder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .button {
            padding: 10px 15px;
            cursor: pointer;
            border: none;
            background-color: #007BFF;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Welcome <?php echo isset($userData) ? htmlspecialchars($userData['name']) : 'User  '; ?> to the Simple Form Builder</h1>

    <a href="new_from.php?user_id=<?php echo urlencode($userData['id']); ?>" class="button">Build New Form</a>

    <h2>Your Existing Forms</h2>
    <?php if (count($existingForms) > 0): ?>
        <ul>
            <?php foreach ($existingForms as $form): ?>
                <li>
                    <a href="Form_View.php?form_id=<?php echo urlencode($form['id']); ?>">
                        <?php echo htmlspecialchars($form['form_name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have no existing forms.</p>
    <?php endif; ?>
    
</body>
</html>