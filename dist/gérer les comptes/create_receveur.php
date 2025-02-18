<?php
include 'config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Create a cache file to store the postal codes and their corresponding etablissement_name values
$cache_file = 'postal_codes.cache';

// Check if the cache file exists
if (file_exists($cache_file)) {
    // If the cache file exists, load the data from it
    $postal_codes = unserialize(file_get_contents($cache_file));
} else {
    // If the cache file does not exist, fetch the data from the database and store it in the cache file
    $stmt = $conn->prepare("SELECT postal_code, etablissement_name FROM epost");
    $stmt->execute();
    $postal_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents($cache_file, serialize($postal_codes));
}

// Create receveur account
if (isset($_POST['create_receveur'])) {
    $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
    $prenom = filter_var($_POST['prenom'], FILTER_SANITIZE_STRING);
    $username = strtolower($nom . '_' . $prenom);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $postal_code = $_POST['postal_code'];

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

        // Insert the receveur into the database
        if ($email !== null) {
            $stmt = $conn->prepare("INSERT INTO Users (username, nom, prenom, email, user_mobile, user_fixe, password, etat_compte, role_id, grade, postal_code) VALUES (:username, :nom, :prenom, :email, :user_mobile, :user_fixe, :password, TRUE, 3, 'Receveur', :postal_code)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_mobile', $_POST['user_mobile']);
            $stmt->bindParam(':user_fixe', $_POST['user_fixe']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':postal_code', $postal_code);
        } else {
            $stmt = $conn->prepare("INSERT INTO Users (username, nom, prenom, user_mobile, user_fixe, password, etat_compte, role_id, grade, postal_code) VALUES (:username, :nom, :prenom, :user_mobile, :user_fixe, :password, TRUE, 3, 'Receveur', :postal_code)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':user_mobile', $_POST['user_mobile']);
            $stmt->bindParam(':user_fixe', $_POST['user_fixe']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':postal_code', $postal_code);
        }

        if ($stmt->execute()) {
            echo " Data inserted successfully";
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
    <title>Create Receveur</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Create a New Receveur</h1>
    </header>
    <main>
        <form action="" method="post">
            <label for="nom">Nom:</label>
            <input type="text" name="nom" required><br>

            <label for="prenom">Prenom:</label>
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

            <label for="postal_code">Postal Code:</label>
            <select name="postal_code" required>
                <option value="">Select a Postal Code</option>
                <?php
                foreach ($postal_codes as $postal) {
                    echo '<option value="' . htmlspecialchars($postal['postal_code']) . '">' . htmlspecialchars($postal['postal_code']) . ' - ' . htmlspecialchars($postal['etablissement_name']) . '</option>';
                }
                ?>
            </select><br>

            <input type="submit" name="create_receveur" value="Create Receveur">
        </form>
    </main>
</body>
</html>