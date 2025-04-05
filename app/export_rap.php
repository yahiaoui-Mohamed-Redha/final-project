<?php
include 'config.php';
session_start();

// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    // Redirect to the login page or show an error message
    header('location: index.php');
    exit();
}

// Get requested format
$format = isset($_GET['format']) ? $_GET['format'] : '';

// Fetch all reports from the database
$stmt = $conn->prepare("SELECT r.rap_num, r.rap_name, r.rap_date, r.description, u.nom, u.prenom 
                        FROM Rapport r 
                        INNER JOIN Users u ON r.user_id = u.user_id");
$stmt->execute();
$rapports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export based on the requested format
if ($format === 'excel') {
    // Set proper content type for Excel
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment;filename="liste_rapport_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Simple HTML table approach instead of XML (more compatible)
    echo '<!DOCTYPE html>';
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
    echo '<!--[if gte mso 9]><xml>';
    echo '<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
    echo '<x:Name>Liste des rapport</x:Name>';
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
    echo '<th>Rap Num</th>';
    echo '<th>Nom Rapport</th>';
    echo '<th>Date</th>';
    echo '<th>Description</th>';
    echo '<th>Reçu par</th>';
    echo '</tr>';
    
    // Data rows
    foreach ($rapports as $rapport) {
        echo '<tr>';
        // echo '<td>' . $panne['panne_num'] . '</td>';
        // echo '<td>' . (isset($panne['rap_num']) ? htmlspecialchars($panne['rap_num']) : '') . '</td>';
        echo '<td>' . htmlspecialchars($rapport['rap_num']) . '</td>';
        echo '<td>' . htmlspecialchars($rapport['rap_name']) . '</td>';
        echo '<td>' . htmlspecialchars($rapport['rap_date']) . '</td>';
        echo '<td>' . htmlspecialchars($rapport['description']) . '</td>';
        // echo '<td>' . (isset($panne['rap_date']) ? htmlspecialchars($panne['rap_date']) : '') . '</td>';
        echo '<td>' . htmlspecialchars($rapport['nom'] . ' ' . $rapport['prenom']) . '</td>';
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
    <title>Liste des rapports</title>
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
            <h1>Liste Des rapport</h1>
            <div class="date">Généré le ' . date('d/m/Y H:i:s') . '</div>
        </div>
        
        <div class="button-container">
            <a href="javascript:history.back()" class="back-button">Retour</a>
            <button onclick="window.print()" class="print-button">Imprimer en PDF</button>
        </div>
        
        
        <table>
            <thead>
                <tr>
                    <th>Rap Num</th>
                    <th>Nom Rapport</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Reçu par</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($rapports as $rapport) {
        echo '<tr>
                <td>' . htmlspecialchars($rapport['rap_num']) . '</td>
                <td>' . htmlspecialchars($rapport['rap_name']) . '</td>
                <td>' . htmlspecialchars($rapport['rap_date']) . '</td>
                <td>' . htmlspecialchars($rapport['description']) . '</td>
                <td>' . htmlspecialchars($rapport['nom'] . ' ' . $rapport['prenom']) . '</td>
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