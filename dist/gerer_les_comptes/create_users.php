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


// manage_users.php
function execute_javascript() {
    ?>
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
    <?php
}

// Call the function in your PHP file
execute_javascript();



?>

    <main>
        <form action="../app/create_users.php"  id="createAccountForm" method="post">
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