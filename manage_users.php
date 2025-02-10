<?php
include 'config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch all users from the database
$stmt = $conn->prepare("SELECT * FROM Users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Manage Users</h1>
    </header>
    <main>
        <table>
            <tr>
                <th>Username</th>
                <th>Nom</th>
                <th>Prenom</th>
                <th>Email</th>
                <th>Role</th>
                <th>Etat Compte</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user) { ?>
            <tr>
                <td><?php echo $user['username']; ?></td>
                <td><?php echo $user['nom']; ?></td>
                <td><?php echo $user['prenom']; ?></td>
                <td><?php echo $user['role_id'] == 1 ? 'Admin' : ($user['role_id'] == 2 ? 'Technicien' : 'Receveur'); ?></td>
                <td><?php echo $user['etat_compte'] == 1 ? 'Active' : 'Disabled'; ?></td>
                <td>
                    <?php if ($user['etat_compte'] == 1) { ?>
                    <a href="disable_user.php?id=<?php echo $user['user_id']; ?>">Disable</a>
                    <?php } else { ?>
                    <a href="enable_user.php?id=<?php echo $user['user_id']; ?>">Enable</a>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </main>
</body>
</html>