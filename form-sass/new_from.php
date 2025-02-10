<?php
// Include the database configuration file
include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:index.php'); // Redirect to login if not logged in
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formName = $_POST['form_name'];
    $textareaContent = $_POST['textarea_content'];

    // Process textarea content into an array of field names
    $fields = explode("\n", trim($textareaContent));

    // You may want to save the form name and fields to the database here
    // For example, saving the form name
    $stmt = $conn->prepare("INSERT INTO form (form_name, user_id) VALUES (:form_name, :user_id)");
    $stmt->execute(['form_name' => $formName, 'user_id' => $_SESSION['user_id']]);
    $formId = $conn->lastInsertId(); // Get the last inserted form ID

    // Save each field name to the database (assuming you have a fields table)
    foreach ($fields as $field) {
        $field = trim($field);
        if (!empty($field)) {
            $fieldStmt = $conn->prepare("INSERT INTO form_fields (form_id, field_name) VALUES (:form_id, :field_name)");
            $fieldStmt->execute(['form_id' => $formId, 'field_name' => $field]);
        }
    }

    // Redirect to the form view page or another page after saving
    header("Location: Form_View.php?form_id=" . urlencode($formId));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Form</title>
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
            margin-top: 10px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Create a New Form</h1>
    <form method="POST">
        <label for="form_name">Form Name:</label>
        <input type="text" name="form_name" id="form_name" required>
        <br><br>
        <label for="textarea_content">Enter Field Names (one per line):</label>
        <textarea name="textarea_content" id="textarea_content" rows="10" required></textarea>
        <br><br>
        <button type="submit" class="button">Create Form</button>
    </form>
</body>
</html>