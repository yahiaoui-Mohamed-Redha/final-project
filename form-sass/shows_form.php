<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loaded Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .draggable {
            margin-bottom: 10px;
 padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

    <h2>Form Preview</h2>
    <div id="formPreview" class="form-preview">
        <?php
            if (isset($_SESSION['form_elements'])) {
                foreach ($_SESSION['form_elements'] as $element) {
                    echo $element;
                }
            } else {
                echo "<p>No form elements found. Please go back and create a form.</p>";
            }
        ?>
    </div>

    <button onclick="window.location.href='index.php'">Back to Form Builder</button>
    <button onclick="window.location.href='logout.php'">Logout</button>
</body>
</html>