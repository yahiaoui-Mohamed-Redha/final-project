<?php
include 'config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Create technicien account
if (isset($_POST['create_technicien'])) {
    $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
    $prenom = filter_var($_POST['prenom'], FILTER_SANITIZE_STRING);
    $username = strtolower($nom . '_' . $prenom);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $grade = filter_var($_POST['grade'], FILTER_SANITIZE_STRING);

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

        // Insert the technicien into the database
        if ($email !== null) {
            $stmt = $conn->prepare("INSERT INTO Users (username, nom, prenom, email, user_mobile, user_fixe, password, etat_compte, role_id, grade) VALUES (:username, :nom, :prenom, :email, :user_mobile, :user_fixe, :password, TRUE, 2, :grade)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_mobile', $_POST['user_mobile']);
            $stmt->bindParam(':user_fixe', $_POST['user_fixe']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':grade', $grade);
        } else {
            $stmt = $conn->prepare("INSERT INTO Users (username, nom, prenom, user_mobile, user_fixe, password, etat_compte, role_id, grade) VALUES (:username, :nom, :prenom, :user_mobile, :user_fixe, :password, TRUE, 2, :grade)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':user_mobile', $_POST['user_mobile']);
            $stmt->bindParam(':user_fixe', $_POST['user_fixe']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':grade', $grade);
        }

        if ($stmt->execute()) {
            echo "Data inserted successfully";
        } else {
            $error = $stmt->errorInfo();
            echo "Error: " . $error[2];
        }

        // Redirect to the admin page
        header('Location: admin_page.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Technicien</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Create a New Technicien</h1>
    </header>
    <main>
        <form action="" method="post">
            <label for="nom">Nom:</label>
            <input type="text" name="nom" required><br>

            <label for="prenom">Prenom :</label>
            <input type="text" name="prenom" required><br>

            <label for="email">Email:</label>
            <input type="email" name="email">
            <?php if (isset($error)) { echo '<span style="color: red;">' . $error . '</span>'; } ?><br>

            <label for="user_mobile">User  Mobile:</label>
            <input type="text" name="user_mobile"><br>

            <label for="user_fixe">User  Fixe:</label>
            <input type="text" name="user_fixe"><br>

            <label for="password">Password:</label>
            <input type="password" name="password" required><br>

            <label for="grade">Grade:</label>
            <input type="text" name="grade" required><br>

            <input type="submit" name="create_technicien" value="Create Technicien">
        </form>
    </main>
</body>
</html>