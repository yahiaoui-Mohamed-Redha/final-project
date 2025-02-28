<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'Admin') {
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
        header('Location: manage_users.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function toggleFields() {
            const accountType = document.querySelector('select[name="account_type"]').value;
            const postalCodeField = document.getElementById('postal_code_field');
            const gradeField = document.getElementById('grade_field');

            if (accountType === 'technicien') {
                postalCodeField.style.display = 'none';
                gradeField.style.display = 'block';
            } else if (accountType === 'receveur') {
                postalCodeField.style.display = 'block';
                gradeField.style.display = 'none';
            } else {
                postalCodeField.style.display = 'none';
                gradeField.style.display = 'none';
            }
        }

        // Call the function on page load and when the account type changes
        window.onload = toggleFields;
        document.querySelector('select[name="account_type"]').addEventListener('change', toggleFields);
    </script>
</head>
<body>
    <header>
        <h1>Create a New Account</h1>
    </header>
    <main>
        <form action="" method="post">
            <label for="account_type">Account Type:</label>
            <select name="account_type" required onchange="toggleFields()">
                <option value="technicien" selected>Technicien</option>
                <option value="receveur">Receveur</option>
            </select><br>

            <label for="nom">Nom:</label>
            <input type="text" name="nom" required><br>

            <label for="prenom">Prenom:</label>
            <input type="text" name="prenom" required><br>

            <label for="email">Email:</label>
            <input type="email" name="email">
            <?php if (isset($error)) { echo '<span style="color: red;">' . $error . '</span>'; } ?><br>

            <label for="user_mobile">User Mobile:</label>
            <input type="text" name="user_mobile"><br>

            <label for="user_fixe">User Fixe:</label>
            <input type="text" name="user_fixe"><br>

            <label for="password">Password:</label>
            <input type="password" name="password" required><br>

            <!-- Postal Code Field (for Receveur) -->
            <div id="postal_code_field" style="display: none;">
                <label for="postal_code">Postal Code:</label>
                <select name="postal_code">
                    <option value="">Select a Postal Code</option>
                    <?php
                    foreach ($postal_codes as $postal) {
                        echo '<option value="' . htmlspecialchars($postal['postal_code']) . '">' . htmlspecialchars($postal['postal_code']) . ' - ' . htmlspecialchars($postal['etablissement_name']) . '</option>';
                    }
                    ?>
                </select><br>
            </div>

            <!-- Grade Field (for Technicien) -->
            <div id="grade_field" style="display: none;">
                <label for="grade">Grade:</label>
                <input type="text" name="grade"><br>
            </div>

            <input type="submit" name="create_account" value="Create Account">
        </form>
    </main>
</body>
</html>