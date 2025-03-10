<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
include 'config.php';
echo "Database connection successful!<br>"; // Debugging

// Check if the user ID is provided via POST
if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    echo "User ID: " . $user_id . "<br>"; // Debugging

    // Fetch the user's current data from the database
    $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    if ($stmt->execute()) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            die("User not found.");
        }
    } else {
        die("Query execution failed.");
    }
} else {
    die("User ID not provided.");
}

// Handle form submission for updating user data
if (isset($_POST['update_account'])) {
    echo "Form submitted!<br>"; // Debugging

    $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
    $prenom = filter_var($_POST['prenom'], FILTER_SANITIZE_STRING);
    $username = strtolower($nom . '_' . $prenom);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $role_id = $_POST['role_id']; // Get the role ID

    // Debugging: Display the data being passed
    echo "Username: " . $username . "<br>"; // Debugging
    echo "Nom: " . $nom . "<br>"; // Debugging
    echo "Prenom: " . $prenom . "<br>"; // Debugging
    echo "Email: " . $email . "<br>"; // Debugging
    echo "Role ID: " . $role_id . "<br>"; // Debugging
    echo "User ID: " . $user_id . "<br>"; // Debugging

    // Update the user in the database
    $stmt = $conn->prepare("UPDATE Users SET username = :username, nom = :nom, prenom = :prenom, email = :email, role_id = :role_id WHERE user_id = :user_id");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':prenom', $prenom);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role_id', $role_id);
    $stmt->bindParam(':user_id', $user_id);

    if ($stmt->execute()) {
        echo "User updated successfully.<br>"; // Debugging
        // Redirect to the admin page
        header('Location: ../dist/admin_page.php');
        exit;
    } else {
        $error = $stmt->errorInfo();
        echo "Error: " . $error[2]; // Debugging
    }
}
?>