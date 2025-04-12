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

// Fetch all fiches with related data
$query = "SELECT 
            fi.fiche_num, 
            fi.fiche_date, 
            fi.compte_rendu, 
            fi.observation,
            fi.archived,
            p.panne_name, 
            p.date_signalement, 
            COALESCE(e.etablissement_name, 'UNITE-POSTAL-WILAYA-DE-BOUMERDES') AS etablissement_name, 
            t.type_name, 
            p.panne_etat, 
            tech.nom AS tech_nom, 
            tech.prenom AS tech_prenom,
            rec.nom AS rec_nom, 
            rec.prenom AS rec_prenom 
          FROM FicheIntervention fi
          INNER JOIN Panne p ON fi.panne_num = p.panne_num
          INNER JOIN Type_panne t ON p.type_id = t.type_id 
          INNER JOIN Users tech ON fi.technicien_id = tech.user_id
          INNER JOIN Users rec ON fi.receveur_id = rec.user_id
          LEFT JOIN Epost e ON rec.postal_code = e.postal_code
          ORDER BY fi.fiche_date DESC";

// Filter fiches for receveur
if ($_SESSION['user_role'] == 'Receveur') {
    $query = "SELECT 
                fi.fiche_num, 
                fi.fiche_date, 
                fi.compte_rendu, 
                fi.observation,
                fi.archived,
                p.panne_name, 
                p.date_signalement, 
                e.etablissement_name,
                t.type_name, 
                p.panne_etat, 
                tech.nom AS tech_nom, 
                tech.prenom AS tech_prenom,
                rec.nom AS rec_nom, 
                rec.prenom AS rec_prenom 
              FROM FicheIntervention fi
              INNER JOIN Panne p ON fi.panne_num = p.panne_num
              INNER JOIN Type_panne t ON p.type_id = t.type_id 
              INNER JOIN Users tech ON fi.technicien_id = tech.user_id
              INNER JOIN Users rec ON fi.receveur_id = rec.user_id
              LEFT JOIN Epost e ON rec.postal_code = e.postal_code
              WHERE fi.receveur_id = :receveur_id
              ORDER BY fi.fiche_date DESC";
}

// Filter fiches for technicien
if ($_SESSION['user_role'] == 'Technicien') {
    $query = "SELECT 
                fi.fiche_num, 
                fi.fiche_date, 
                fi.compte_rendu, 
                fi.observation,
                fi.archived,
                p.panne_name, 
                p.date_signalement, 
                e.etablissement_name,
                t.type_name, 
                p.panne_etat, 
                tech.nom AS tech_nom, 
                tech.prenom AS tech_prenom,
                rec.nom AS rec_nom, 
                rec.prenom AS rec_prenom 
              FROM FicheIntervention fi
              INNER JOIN Panne p ON fi.panne_num = p.panne_num
              INNER JOIN Type_panne t ON p.type_id = t.type_id 
              INNER JOIN Users tech ON fi.technicien_id = tech.user_id
              INNER JOIN Users rec ON fi.receveur_id = rec.user_id
              LEFT JOIN Epost e ON rec.postal_code = e.postal_code
              WHERE fi.technicien_id = :technicien_id
              ORDER BY fi.fiche_date DESC";
}

// Prepare and execute the query
$stmt_fiche = $conn->prepare($query);

if ($_SESSION['user_role'] == 'Receveur') {
    $stmt_fiche->execute(['receveur_id' => $_SESSION['user_id']]);
} elseif ($_SESSION['user_role'] == 'Technicien') {
    $stmt_fiche->execute(['technicien_id' => $_SESSION['user_id']]);
} else {
    $stmt_fiche->execute();
}

$fiches = $stmt_fiche->fetchAll(PDO::FETCH_ASSOC);

// Export based on the requested format
if ($format === 'excel') {
    // Set proper content type for Excel
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment;filename="liste_fiches_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Simple HTML table approach for Excel
    echo '<!DOCTYPE html>';
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
    echo '<!--[if gte mso 9]><xml>';
    echo '<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
    echo '<x:Name>Liste des fiches</x:Name>';
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
    echo '<th>Fiche Num</th>';
    echo '<th>Date</th>';
    echo '<th>Panne associée</th>';
    echo '<th>Date signalement</th>';
    echo '<th>Établissement</th>';
    echo '<th>Type</th>';
    echo '<th>Technicien</th>';
    echo '<th>Receveur</th>';
    echo '<th>Compte rendu</th>';
    echo '<th>Observation</th>';
    echo '<th>Statut</th>';
    echo '</tr>';
    
    // Data rows
    foreach ($fiches as $fiche) {
        echo '<tr>';
        echo '<td>' . $fiche['fiche_num'] . '</td>';
        echo '<td>' . htmlspecialchars($fiche['fiche_date']) . '</td>';
        echo '<td>' . htmlspecialchars($fiche['panne_name']) . '</td>';
        echo '<td>' . htmlspecialchars($fiche['date_signalement']) . '</td>';
        echo '<td>' . htmlspecialchars($fiche['etablissement_name']) . '</td>';
        echo '<td>' . htmlspecialchars($fiche['type_name']) . '</td>';
        echo '<td>' . htmlspecialchars($fiche['tech_nom'] . ' ' . $fiche['tech_prenom']) . '</td>';
        echo '<td>' . htmlspecialchars($fiche['rec_nom'] . ' ' . $fiche['rec_prenom']) . '</td>';
        echo '<td>' . htmlspecialchars($fiche['compte_rendu']) . '</td>';
        echo '<td>' . htmlspecialchars($fiche['observation']) . '</td>';
        echo '<td>' . ($fiche['archived'] == 1 ? 'Archivée' : 'Active') . '</td>';
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
        <title>Liste des fiches d\'intervention</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background-color: #0455b7; color: white; font-weight: bold; text-align: left; padding: 8px; }
            td { border: 1px solid #ddd; padding: 8px; }
            tr:nth-child(even) { background-color: #f2f2f2; }
            h1 { color: #0455b7; }
            .header { margin-bottom: 20px; }
            .date { color: #666; }
            .archived { color: #856404; background-color: #fff3cd; }
            .active { color: #155724; background-color: #d4edda; }
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
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Liste Des Fiches d\'Intervention</h1>
            <div class="date">Généré le ' . date('d/m/Y H:i:s') . '</div>
        </div>
        
        <div class="button-container">
            <a href="javascript:history.back()" class="back-button">Retour</a>
            <button onclick="window.print()" class="print-button">Enregistrer en PDF</button>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Fiche Num</th>
                    <th>Date</th>
                    <th>Panne associée</th>
                    <th>Établissement</th>
                    <th>Type</th>
                    <th>Technicien</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($fiches as $fiche) {
        $statusClass = $fiche['archived'] == 1 ? 'archived' : 'active';
        echo '<tr class="' . $statusClass . '">
                <td>' . $fiche['fiche_num'] . '</td>
                <td>' . htmlspecialchars($fiche['fiche_date']) . '</td>
                <td>' . htmlspecialchars($fiche['panne_name']) . '</td>
                <td>' . htmlspecialchars($fiche['etablissement_name']) . '</td>
                <td>' . htmlspecialchars($fiche['type_name']) . '</td>
                <td>' . htmlspecialchars($fiche['tech_nom'] . ' ' . $fiche['tech_prenom']) . '</td>
                <td>' . ($fiche['archived'] == 1 ? 'Archivée' : 'Active') . '</td>
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