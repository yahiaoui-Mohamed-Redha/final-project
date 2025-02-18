<?php
include '../../app/config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['receveur', 'admin', 'technicien'])) {
    header('Location: login.php');
    exit;
}

// Fetch all panne
$stmt_panne = $conn->prepare("SELECT p.panne_name, p.date_signalement, p.description, t.type_name, u.nom, u.prenom 
                              FROM Panne p 
                              INNER JOIN Type_panne t ON p.type_id = t.type_id 
                              INNER JOIN Users u ON p.receveur_id = u.user_id");
$stmt_panne->execute();
$pannes = $stmt_panne->fetchAll(PDO::FETCH_ASSOC);

// Filter panne for receveur
if ($_SESSION['user_role'] == 'receveur') {
    $stmt_panne_receveur = $conn->prepare("SELECT p.panne_name, p.date_signalement, p.description, t.type_name, u.nom, u.prenom 
                                           FROM Panne p 
                                           INNER JOIN Type_panne t ON p.type_id = t.type_id 
                                           INNER JOIN Users u ON p.receveur_id = u.user_id 
                                           WHERE p.receveur_id = :receveur_id");
    $stmt_panne_receveur->execute(['receveur_id' => $_SESSION['user_id']]);
    $pannes = $stmt_panne_receveur->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عرض الأعطال</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>عرض الأعطال</h1>
    <table>
        <tr>
            <th>اسم العطل</th>
            <th>تاريخ الإبلاغ</th>
            <th>وصف العطل</th>
            <th>نوع العطل</th>
            <th>المستلم</th>
        </tr>
        <?php foreach ($pannes as $panne): ?>
            <tr>
                <td><?php echo htmlspecialchars($panne['panne_name']); ?></td>
                <td><?php echo $panne['date_signalement']; ?></td>
                <td><?php echo htmlspecialchars($panne['description']); ?></td>
                <td><?php echo htmlspecialchars($panne['type_name']); ?></td>
                <td><?php echo htmlspecialchars($panne['nom'] . ' ' . $panne['prenom']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="rapport_view.php?admin_id=<?php echo $_SESSION['user_id']; ?>">عرض التقارير</a></p>
</body>
</html>