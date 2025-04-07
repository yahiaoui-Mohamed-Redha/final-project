<?php
// Include database configuration
include 'config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}

// Check if panne_num is provided
if (!isset($_GET['panne_num'])) {
    die("Error: No panne number provided");
}

$panne_num = $_GET['panne_num'];

// Fetch the panne details
$query = "SELECT 
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
          WHERE p.panne_num = :panne_num";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute(['panne_num' => $panne_num]);
    $panne = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$panne) {
        die("Error: Panne not found");
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de Panne - <?php echo htmlspecialchars($panne['panne_num']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0455b7;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #0455b7;
            margin-bottom: 5px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .section h2 {
            color: #0455b7;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            width: 200px;
            color: #555;
        }
        .info-value {
            flex: 1;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #777;
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
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- Replace with your logo path -->
            <img src="../../assets/image/oie_M5SLKyrEbkDJ.png" alt="Algérie Poste Logo" class="logo">
            <h1>Algérie Poste</h1>
            <p>Rapport de Panne</p>
        </div>
        
        <div class="button-container">
            <a href="javascript:history.back()" class="back-button">Retour</a>
            <button onclick="window.print()" class="print-button">télécharger en PDF</button>
        </div>
        
        <div class="section">
            <h2>Informations de Panne</h2>
            <div class="info-row">
                <div class="info-label">Numéro de Panne:</div>
                <div class="info-value"><?php echo htmlspecialchars($panne['panne_num']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nom de Panne:</div>
                <div class="info-value"><?php echo htmlspecialchars($panne['panne_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de Signalement:</div>
                <div class="info-value"><?php echo htmlspecialchars($panne['date_signalement']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Établissement:</div>
                <div class="info-value"><?php echo htmlspecialchars($panne['etablissement_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Type de Panne:</div>
                <div class="info-value"><?php echo htmlspecialchars($panne['type_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">État:</div>
                <div class="info-value"><?php echo htmlspecialchars($panne['panne_etat']); ?></div>
            </div>
        </div>
        
        <?php if (!empty($panne['rap_num'])): ?>
        <div class="section">
            <h2>Détails du Rapport</h2>
            <div class="info-row">
                <div class="info-label">Numéro de Rapport:</div>
                <div class="info-value"><?php echo htmlspecialchars($panne['rap_num']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nom du Rapport:</div>
                <div class="info-value"><?php echo htmlspecialchars($panne['rap_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date du Rapport:</div>
                <div class="info-value"><?php echo htmlspecialchars($panne['rap_date']); ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Détails du Receveur</h2>
            <div class="info-row">
                <div class="info-label">Receveur:</div>
                <div class="info-value"><?php echo htmlspecialchars($panne['user_nom'] . ' ' . $panne['user_prenom']); ?></div>
            </div>
        </div>
        
        <div class="footer">
            <p>Document généré le <?php echo date('d/m/Y H:i:s'); ?></p>
            <p>UNITE-POSTAL-WILAYA-DE-BOUMERDES</p>
        </div>
    </div>


</body>
</html>
<?php
// End of file
?>