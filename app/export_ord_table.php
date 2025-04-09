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

// Fetch order missions based on user role
if ($_SESSION['user_role'] == 'Admin') {
    // Admin can see all order missions
    $stmt = $conn->prepare("SELECT om.*, u.nom, u.prenom 
                          FROM OrderMission om 
                          LEFT JOIN Users u ON om.technicien_id = u.user_id
                          ORDER BY om.date_depart DESC");
    $stmt->execute();
    $order_missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($_SESSION['user_role'] == 'Technicien') {
    // Technicien can only see their assigned order missions
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT om.*, u.nom, u.prenom 
                          FROM OrderMission om 
                          LEFT JOIN Users u ON om.technicien_id = u.user_id 
                          WHERE om.technicien_id = ?
                          ORDER BY om.date_depart DESC");
    $stmt->execute([$user_id]);
    $order_missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Default case
    $order_missions = [];
}

// Export based on the requested format
if ($format === 'excel') {
    // Set proper content type for Excel
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment;filename="orders_mission_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Simple HTML table approach for Excel
    echo '<!DOCTYPE html>';
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
    echo '<!--[if gte mso 9]><xml>';
    echo '<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
    echo '<x:Name>Orders Mission</x:Name>';
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
    echo '<th>Order Number</th>';
    echo '<th>Direction</th>';
    echo '<th>Destination</th>';
    echo '<th>Motif</th>';
    echo '<th>Moyen de transport</th>';
    echo '<th>Date de depart</th>';
    echo '<th>Date de retour</th>';
    echo '<th>Technicien</th>';
    echo '</tr>';
    
    // Data rows
    foreach ($order_missions as $mission) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($mission['order_num']) . '</td>';
        echo '<td>' . htmlspecialchars($mission['direction']) . '</td>';
        echo '<td>' . htmlspecialchars($mission['destination']) . '</td>';
        echo '<td>' . htmlspecialchars($mission['motif']) . '</td>';
        echo '<td>' . htmlspecialchars($mission['moyen_tr']) . '</td>';
        echo '<td>' . htmlspecialchars($mission['date_depart']) . '</td>';
        echo '<td>' . htmlspecialchars($mission['date_retour']) . '</td>';
        echo '<td>' . htmlspecialchars($mission['nom'] . ' ' . $mission['prenom']) . '</td>';
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
        <title>Ordres de Mission</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background-color: #0455b7; color: white; font-weight: bold; text-align: left; padding: 8px; }
            td { border: 1px solid #ddd; padding: 8px; }
            tr:nth-child(even) { background-color: #f2f2f2; }
            h1 { color: #0455b7; }
            .header { margin-bottom: 20px; }
            .date { color: #666; }
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
                body { font-size: 12pt; }
                table { page-break-inside: auto; }
                tr { page-break-inside: avoid; page-break-after: auto; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Liste des Ordres de Mission</h1>
            <div class="date">Généré le ' . date('d/m/Y H:i:s') . '</div>
        </div>
        
        <div class="button-container">
            <a href="javascript:history.back()" class="back-button">Retour</a>
            <button onclick="window.print()" class="print-button">Enregistrer en PDF</button>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Direction</th>
                    <th>Destination</th>
                    <th>Motif</th>
                    <th>Moyen de transport</th>
                    <th>Date de depart</th>
                    <th>Date de retour</th>
                    <th>Technicien</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($order_missions as $mission) {
        echo '<tr>
                <td>' . htmlspecialchars($mission['order_num']) . '</td>
                <td>' . htmlspecialchars($mission['direction']) . '</td>
                <td>' . htmlspecialchars($mission['destination']) . '</td>
                <td>' . htmlspecialchars($mission['motif']) . '</td>
                <td>' . htmlspecialchars($mission['moyen_tr']) . '</td>
                <td>' . htmlspecialchars($mission['date_depart']) . '</td>
                <td>' . htmlspecialchars($mission['date_retour']) . '</td>
                <td>' . htmlspecialchars($mission['nom'] . ' ' . $mission['prenom']) . '</td>
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