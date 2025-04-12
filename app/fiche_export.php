<?php
// fiche_intervention_export.php
// Include database configuration
include 'config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}

// Check if fiche_num is provided
if (!isset($_GET['fiche_num'])) {
    die("Error: No fiche d'intervention number provided");
}

$fiche_num = $_GET['fiche_num'];

// Fetch the fiche details
$query = "SELECT 
            fi.fiche_num, 
            fi.fiche_date, 
            fi.compte_rendu, 
            fi.observation,
            fi.archived,
            p.panne_num,
            p.panne_name, 
            p.date_signalement, 
            p.description AS panne_description,
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
          WHERE fi.fiche_num = :fiche_num";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute(['fiche_num' => $fiche_num]);
    $fiche = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fiche) {
        die("Error: Fiche d'intervention not found");
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
    <title>Fiche d'Intervention - <?php echo htmlspecialchars($fiche['fiche_num']); ?></title>
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
            <h1>Algérie Poste</h1>
            <p>Fiche d'Intervention</p>
        </div>
        
        <div class="button-container">
            <a href="javascript:history.back()" class="back-button">Retour</a>
            <button onclick="window.print()" class="print-button">Enregistrer en PDF</button>
        </div>
        
        <div class="section">
            <h2>Informations d'Intervention</h2>
            <div class="info-row">
                <div class="info-label">Numéro de Fiche:</div>
                <div class="info-value"><?php echo htmlspecialchars($fiche['fiche_num']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date d'Intervention:</div>
                <div class="info-value"><?php echo htmlspecialchars(date('d/m/Y', strtotime($fiche['fiche_date']))); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Établissement:</div>
                <div class="info-value"><?php echo htmlspecialchars($fiche['etablissement_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">État:</div>
                <div class="info-value"><?php echo $fiche['archived'] ? 'Archivée' : 'Active'; ?></div>
            </div>
        </div>
        
        <div class="section">
            <h2>Détails de la Panne</h2>
            <div class="info-row">
                <div class="info-label">Numéro de Panne:</div>
                <div class="info-value"><?php echo htmlspecialchars($fiche['panne_num']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nom de la Panne:</div>
                <div class="info-value"><?php echo htmlspecialchars($fiche['panne_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de Signalement:</div>
                <div class="info-value"><?php echo htmlspecialchars(date('d/m/Y', strtotime($fiche['date_signalement']))); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Type de Panne:</div>
                <div class="info-value"><?php echo htmlspecialchars($fiche['type_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">État de la Panne:</div>
                <div class="info-value"><?php echo htmlspecialchars($fiche['panne_etat']); ?></div>
            </div>
            <?php if (!empty($fiche['panne_description'])): ?>
            <div class="info-row">
                <div class="info-label">Description:</div>
                <div class="info-value"><?php echo htmlspecialchars($fiche['panne_description']); ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Compte Rendu d'Intervention</h2>
            <div class="info-row">
                <div class="info-value"><?php echo nl2br(htmlspecialchars($fiche['compte_rendu'])); ?></div>
            </div>
        </div>
        
        <?php if (!empty($fiche['observation'])): ?>
        <div class="section">
            <h2>Observations</h2>
            <div class="info-row">
                <div class="info-value"><?php echo nl2br(htmlspecialchars($fiche['observation'])); ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Intervenants</h2>
            <div class="info-row">
                <div class="info-label">Technicien:</div>
                <div class="info-value"><?php echo htmlspecialchars($fiche['tech_nom'] . ' ' . $fiche['tech_prenom']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Receveur:</div>
                <div class="info-value"><?php echo htmlspecialchars($fiche['rec_nom'] . ' ' . $fiche['rec_prenom']); ?></div>
            </div>
        </div>
        
        <div class="footer">
            <p>Document généré le <?php echo date('d/m/Y H:i:s'); ?></p>
            <p>UNITE-POSTAL-WILAYA-DE-BOUMERDES</p>
        </div>
    </div>

    <script>
        // Auto print when the page loads
        window.onload = function() {
            window.print();
            // Optional: You can add a timeout to close this window or redirect back
            // setTimeout(function() { window.close(); }, 1000);
        };
    </script>
</body>
</html>
<?php
// End of file
?>