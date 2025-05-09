<?php
// Include database configuration
include 'config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}

// Get the export format from the URL
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';

// Fetch all PN data from the database
$stmt = $conn->prepare("SELECT 
            p.panne_num, 
            p.panne_name, 
            p.date_signalement, 
            COALESCE(e.etablissement_name, 'UNITE-POSTAL-WILAYA-DE-BOUMERDES') AS etablissement_name, 
            t.type_name, 
            p.panne_etat, 
            r.rap_num, 
            r.rap_name, 
            r.rap_date, 
            u.nom AS user_nom, 
            u.prenom AS user_prenom 
          FROM Panne p 
          INNER JOIN Type_panne t ON p.type_id = t.type_id 
          INNER JOIN Users u ON p.receveur_id = u.user_id 
          LEFT JOIN Epost e ON u.postal_code = e.postal_code
          LEFT JOIN Rapport r ON p.rap_num = r.rap_num
          ORDER BY p.date_signalement DESC");
$stmt->execute();
$pannes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export based on the requested format
if ($format === 'excel') {
    // Set proper content type for Excel
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment;filename="liste_pn_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Simple HTML table approach instead of XML (more compatible)
    echo '<!DOCTYPE html>';
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
    echo '<!--[if gte mso 9]><xml>';
    echo '<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
    echo '<x:Name>Liste des pannes</x:Name>';
    echo '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
    echo '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook>';
    echo '</xml><![endif]-->';
    echo '<style>
        table { border-collapse: collapse; }
        table, th, td { border: 1px solid black; }
        th { background-color: #0455b7; color: white; font-weight: bold; }
        td { mso-number-format:\@; } /* Force text format for all cells */
    </style>';
    echo '</head>';
    echo '<body>';
    
    echo '<table>';
    // Header row
    echo '<tr>';
    echo '<th>Panne Num</th>';
    echo '<th>Rap Num</th>';
    echo '<th>Nom de Panne</th>';
    echo '<th>Date</th>';
    echo '<th>Établissement</th>';
    echo '<th>Type</th>';
    echo '<th>Date Rapport</th>';
    echo '<th>État</th>';
    echo '</tr>';
    
    // Data rows
    foreach ($pannes as $panne) {
        echo '<tr>';
        echo '<td>' . $panne['panne_num'] . '</td>';
        echo '<td>' . (isset($panne['rap_num']) ? htmlspecialchars($panne['rap_num']) : '') . '</td>';
        echo '<td>' . htmlspecialchars($panne['panne_name']) . '</td>';
        echo '<td>' . htmlspecialchars($panne['date_signalement']) . '</td>';
        echo '<td>' . htmlspecialchars($panne['etablissement_name']) . '</td>';
        echo '<td>' . htmlspecialchars($panne['type_name']) . '</td>';
        echo '<td>' . (isset($panne['rap_date']) ? htmlspecialchars($panne['rap_date']) : '') . '</td>';
        echo '<td>' . htmlspecialchars($panne['panne_etat']) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</body>';
    echo '</html>';
    exit;
} else {
    // HTML export (for PDF)
    
// Set headers for HTML content
header('Content-Type: text/html; charset=utf-8');
    
// Create HTML content
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Liste des pannes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #0455b7; color: white; font-weight: bold; text-align: left; padding: 8px; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        h1 { color: #0455b7; }
        .header { margin-bottom: 20px; }
        .date { color: #666; }
        @media print {
            body { font-size: 12pt; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            button { display: none; }
        }
            .button-container {
        text-align: center;
        margin: 20px 0;
    }
    .print-button {
        background-color: #0455b7;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .print-button:hover {
        background-color: #033b7e;
    }
    .back-button {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 10px;
        text-decoration: none;
        display: inline-block;
    }
    .back-button:hover {
        background-color: #5a6268;
    }
    
    @media print {
        .button-container {
            display: none;
        }
        .container {
            border: none;
            box-shadow: none;
        }
    </style>
</head>
    <body>
        <div class="header">
            <h1>Liste Des Pannes</h1>
            <div class="date">Généré le ' . date('d/m/Y H:i:s') . '</div>
        </div>
        
        <div class="button-container">
            <a href="javascript:history.back()" class="back-button">Retour</a>
            <button onclick="window.print()" class="print-button">télécharger en PDF</button>
        </div>
        
        
        <table>
            <thead>
                <tr>
                    <th>Panne Num</th>
                    <th>Rap Num</th>
                    <th>Nom de Panne</th>
                    <th>Date</th>
                    <th>Établissement</th>
                    <th>Type</th>
                    <th>État</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($pannes as $panne) {
        echo '<tr>
                <td>' . $panne['panne_num'] . '</td>
                <td>' . (isset($panne['rap_num']) ? htmlspecialchars($panne['rap_num']) : '') . '</td>
                <td>' . htmlspecialchars($panne['panne_name']) . '</td>
                <td>' . htmlspecialchars($panne['date_signalement']) . '</td>
                <td>' . htmlspecialchars($panne['etablissement_name']) . '</td>
                <td>' . htmlspecialchars($panne['type_name']) . '</td>
                <td>' . (isset($panne['rap_date']) ? htmlspecialchars($panne['rap_date']) : '') . '</td>
                <td>' . htmlspecialchars($panne['panne_etat']) . '</td>
            </tr>';
    }
    
    echo '</tbody>
        </table>
                <script>
            // Immediately open print dialog when page loads
            document.addEventListener("DOMContentLoaded", function() {
                // Force immediate print dialog
                window.print();
            });
        </script>
        
    </body>
    </html>';
    exit;
}
?>