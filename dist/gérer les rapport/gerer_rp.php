<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    // Redirect to the login page or show an error message
    header('location: index.php');
    exit(); // Stop further execution
}

// Fetch all rapport
$stmt_rapport = $conn->prepare("SELECT r.rap_name, r.rap_date, r.description, u.nom, u.prenom 
                                FROM Rapport r 
                                INNER JOIN Users u ON r.user_id = u.user_id");
$stmt_rapport->execute();
$rapports = $stmt_rapport->fetchAll(PDO::FETCH_ASSOC);

// Filter rapport for receveur
if ($_SESSION['user_role'] == 'receveur') {
    $stmt_rapport_receveur = $conn->prepare("SELECT r.rap_name, r.rap_date, r.description, u.nom, u.prenom 
                                             FROM Rapport r 
                                             INNER JOIN Users u ON r.user_id = u.user_id 
                                             WHERE r.user_id = :user_id");
    $stmt_rapport_receveur->execute(['user_id' => $_SESSION['user_id']]);
    $rapports = $stmt_rapport_receveur->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عرض التقارير</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>عرض التقارير</h1>
    <table>
        <tr>
            <th>اسم التقرير</th>
            <th>تاريخ التقرير</th>
            <th>وصف التقرير</th>
            <th>المستلم</th>
        </tr>
        <?php foreach ($rapports as $rapport): ?>
            <tr>
                <td><?php echo htmlspecialchars($rapport['rap_name']); ?></td>
                <td><?php echo $rapport['rap_date']; ?></td>
                <td><?php echo htmlspecialchars($rapport['description']); ?></td>
                <td><?php echo htmlspecialchars($rapport['nom'] . ' ' . $rapport['prenom']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="panne_view.php?admin_id=<?php echo $_SESSION['user_id']; ?>">عرض الأعطال</a></p>
</body>
</html>