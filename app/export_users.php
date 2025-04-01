<?php
// Include database configuration
include 'config.php';
session_start();

// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    // Redirect to the login page or show an error message
    header('location: ../dist/index.php');
    exit();
}

// Get the export format from the URL
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';

// Fetch all users from the database
$stmt = $conn->prepare("SELECT u.user_id, u.username, u.nom, u.prenom, u.email, e.etablissement_name, 
                        CASE 
                            WHEN u.role_id = 1 THEN 'Admin' 
                            WHEN u.role_id = 2 THEN 'Technicien' 
                            ELSE 'Receveur' 
                        END AS role_name,
                        CASE 
                            WHEN u.etat_compte = 1 THEN 'Activé' 
                            ELSE 'Désactivé' 
                        END AS etat_compte_text
                        FROM Users u 
                        LEFT JOIN Epost e ON u.postal_code = e.postal_code
                        ORDER BY u.user_id");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export based on the requested format
if ($format === 'excel') {
    // Excel export without Composer
    // Set headers for Excel file
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="liste_utilisateurs_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Create Excel XML content
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
    echo '<Worksheet ss:Name="Liste des Utilisateurs">';
    echo '<Table>';
    
    // Header row
    echo '<Row>';
    echo '<Cell><Data ss:Type="String">ID</Data></Cell>';
    echo '<Cell><Data ss:Type="String">Username</Data></Cell>';
    echo '<Cell><Data ss:Type="String">Nom</Data></Cell>';
    echo '<Cell><Data ss:Type="String">Prénom</Data></Cell>';
    echo '<Cell><Data ss:Type="String">Email</Data></Cell>';
    echo '<Cell><Data ss:Type="String">Établissement</Data></Cell>';
    echo '<Cell><Data ss:Type="String">Rôle</Data></Cell>';
    echo '<Cell><Data ss:Type="String">État du compte</Data></Cell>';
    echo '</Row>';
    
    // Data rows
    foreach ($users as $user) {
        echo '<Row>';
        echo '<Cell><Data ss:Type="Number">' . $user['user_id'] . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($user['username']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($user['nom']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($user['prenom']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($user['email']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($user['etablissement_name'] ?? 'UPW Boumerdes') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . $user['role_name'] . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . $user['etat_compte_text'] . '</Data></Cell>';
        echo '</Row>';
    }
    
    echo '</Table>';
    echo '</Worksheet>';
    echo '</Workbook>';
    exit;
    
} else {
    // HTML export (previously PDF)
    
    // Set headers to force download of an HTML file
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="liste_utilisateurs_' . date('Y-m-d') . '.html"');
    
    // Create HTML content
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Liste des Utilisateurs</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background-color: #0455b7; color: white; font-weight: bold; text-align: left; padding: 8px; }
            td { border: 1px solid #ddd; padding: 8px; }
            tr:nth-child(even) { background-color: #f2f2f2; }
            h1 { color: #0455b7; }
            .header { margin-bottom: 20px; }
            .date { color: #666; }
            .print-button { 
                display: block; 
                margin: 20px auto; 
                padding: 10px 20px; 
                background-color: #0455b7; 
                color: white; 
                border: none; 
                border-radius: 4px; 
                cursor: pointer; 
            }
            @media print {
                .print-button { display: none; }
                body { font-size: 12pt; }
                table { page-break-inside: auto; }
                tr { page-break-inside: avoid; page-break-after: auto; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Liste des Utilisateurs</h1>
            <div class="date">Généré le ' . date('d/m/Y H:i:s') . '</div>
        </div>
        
        <button class="print-button" onclick="window.print()">Imprimer en PDF</button>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Établissement</th>
                    <th>Rôle</th>
                    <th>État</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($users as $user) {
        echo '<tr>
                <td>' . $user['user_id'] . '</td>
                <td>' . htmlspecialchars($user['username']) . '</td>
                <td>' . htmlspecialchars($user['nom']) . '</td>
                <td>' . htmlspecialchars($user['prenom']) . '</td>
                <td>' . htmlspecialchars($user['email']) . '</td>
                <td>' . htmlspecialchars($user['etablissement_name'] ?? 'UPW Boumerdes') . '</td>
                <td>' . $user['role_name'] . '</td>
                <td>' . $user['etat_compte_text'] . '</td>
            </tr>';
    }
    
    echo '</tbody>
        </table>
    </body>
    </html>';
    exit;
}
?>