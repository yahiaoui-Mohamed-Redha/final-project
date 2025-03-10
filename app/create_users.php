<?php
include 'config.php';
// Handle form submission
if (isset($_POST['create_account'])) {
    $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
    $prenom = filter_var($_POST['prenom'], FILTER_SANITIZE_STRING);
    $username = strtolower($nom . '_' . $prenom);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $account_type = $_POST['account_type']; // Get the selected account type

    // Initialize postal_code and grade
    $postal_code = ($account_type == 'receveur') ? $_POST['postal_code'] : null;
    $grade = ($account_type == 'technicien') ? $_POST['grade'] : null;

    if (empty($email)) {
        $email = null;
    }

    // Check if the email already exists
    if ($email !== null) {
        $stmt = $conn->prepare("SELECT * FROM Users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $error = "Email already exists. Please choose a different email address.";
        }
    }

    if (!isset($error)) {
        // Check if the username already exists
        $stmt = $conn->prepare("SELECT * FROM Users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // If the username already exists, modify it
        if ($row) {
            $i = 1;
            while (true) {
                $new_username = $username . $i;
                $stmt = $conn->prepare("SELECT * FROM Users WHERE username = :username");
                $stmt->bindParam(':username', $new_username);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    $username = $new_username;
                    break;
                }
                $i++;
            }
        }

        // Hash the password
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Determine the role and grade based on the selected account type
        $role_id = ($account_type == 'technicien') ? 2 : 3; // Assuming 2 is for technicien and 3 for receveur
        $grade = ($account_type == 'technicien') ? $_POST['grade'] : 'Receveur';

        // Insert the user into the database
        if ($email !== null) {
            $stmt = $conn->prepare("INSERT INTO Users (username, nom, prenom, email, user_mobile, user_fixe, password, etat_compte, role_id, grade, postal_code) VALUES (:username, :nom, :prenom, :email, :user_mobile, :user_fixe, :password, TRUE, :role_id, :grade, :postal_code)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_mobile', $_POST['user_mobile']);
            $stmt->bindParam(':user_fixe', $_POST['user_fixe']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role_id', $role_id);
            $stmt->bindParam(':grade', $grade);
            $stmt->bindParam(':postal_code', $postal_code);
        } else {
            $stmt = $conn->prepare("INSERT INTO Users (username, nom, prenom, user_mobile, user_fixe, password, etat_compte, role_id, grade, postal_code) VALUES (:username, :nom, :prenom, :user_mobile, :user_fixe, :password, TRUE, :role_id, :grade, :postal_code)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':user_mobile', $_POST['user_mobile']);
            $stmt->bindParam(':user_fixe', $_POST['user_fixe']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role_id', $role_id);
            $stmt->bindParam(':grade', $grade);
            $stmt->bindParam(':postal_code', $postal_code);
        }

        if ($stmt->execute()) {
            echo "Data inserted successfully";
        } else {
            $error = $stmt->errorInfo();
            echo "Error: " . $error[2];
        }

        // Redirect to the admin page
        header('Location: ../dist/admin_page.php');
        exit;
    }
}
?>