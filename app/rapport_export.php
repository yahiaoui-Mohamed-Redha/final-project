<?php
include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location: index.php');
    exit();
}

// Check if rapport number is provided
if (!isset($_GET['rap_num'])) {
    $_SESSION['error'] = "Numéro de rapport non spécifié";
    header('location: ../../admin/gerer_les_rapports.php');
    exit();
}

$rap_num = $_GET['rap_num'];

// Fetch the rapport details
$stmt = $conn->prepare("SELECT r.*, u.nom, u.prenom 
                        FROM Rapport r 
                        INNER JOIN Users u ON r.user_id = u.user_id 
                        WHERE r.rap_num = ?");
$stmt->execute([$rap_num]);
$rapport = $stmt->fetch(PDO::FETCH_ASSOC);

// If rapport doesn't exist, redirect with error
if (!$rapport) {
    $_SESSION['error'] = "Rapport non trouvé";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Get current date
$current_date = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport #<?php echo htmlspecialchars($rapport['rap_num']); ?></title>
    <style>
        @media print {
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
            .header {
                border-bottom: 1px solid #ddd;
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
            .logo {
                font-size: 24px;
                font-weight: bold;
                color: #0455b7;
            }
            .report-info {
                margin: 20px 0;
            }
            .report-info table {
                width: 100%;
                border-collapse: collapse;
            }
            .report-info table td {
                padding: 8px;
                border: 1px solid #ddd;
            }
            .report-info table tr td:first-child {
                font-weight: bold;
                width: 30%;
                background-color: #f8f8f8;
            }
            .description {
                margin: 20px 0;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #f9f9f9;
            }
            .footer {
                margin-top: 50px;
                text-align: center;
                font-size: 12px;
                color: #777;
            }
        }
        
        /* Screen styles (for preview) */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0455b7;
        }
        .report-info {
            margin: 20px 0;
        }
        .report-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-info table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .report-info table tr td:first-child {
            font-weight: bold;
            width: 30%;
            background-color: #f8f8f8;
        }
        .description {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .print-btn {
            background-color: #0455b7;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-btn:hover {
            background-color: #03408d;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .back-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Rapport #<?php echo htmlspecialchars($rapport['rap_num']); ?></div>
        <div class="actions no-print">
            <button onclick="window.print()" class="print-btn">Imprimer le rapport</button>
            <a href="javascript:history.back()" class="back-btn">Retour</a>
        </div>
    </div>
    
    <div class="report-info">
        <table>
            <tr>
                <td>Numéro de rapport</td>
                <td><?php echo htmlspecialchars($rapport['rap_num']); ?></td>
            </tr>
            <tr>
                <td>Nom du rapport</td>
                <td><?php echo htmlspecialchars($rapport['rap_name']); ?></td>
            </tr>
            <tr>
                <td>Date du rapport</td>
                <td><?php echo htmlspecialchars($rapport['rap_date']); ?></td>
            </tr>
            <tr>
                <td>Créé par</td>
                <td><?php echo htmlspecialchars($rapport['nom'] . ' ' . $rapport['prenom']); ?></td>
            </tr>
            <tr>
                <td>Date d'impression</td>
                <td><?php echo $current_date; ?></td>
            </tr>
        </table>
    </div>
    
    <h3>Description</h3>
    <div class="description">
        <?php echo nl2br(htmlspecialchars($rapport['description'])); ?>
    </div>
    
    <div class="footer">
        Ce rapport a été généré le <?php echo $current_date; ?>.
        Document confidentiel - Ne pas distribuer sans autorisation.
    </div>
    
    <script>
        // Auto-print when the page loads (optional - uncomment if you want this behavior)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>