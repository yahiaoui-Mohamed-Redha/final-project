<?php
// export_order_mission.php
// Start session at the beginning of the file
session_start();

// Include configuration file
include 'config.php';

// Verify user authorization (add your roles as needed)
if (!isset($_SESSION['user_id'])) {
    header('Location:../../index.php');
    exit;
}

// Check if order_num is provided
if (!isset($_GET['order_num']) || empty($_GET['order_num'])) {
    die("Error: Numéro de l'ordre de mission manquant.");
}

$order_num = $_GET['order_num'];

try {
    // Fetch order mission details
    $stmt = $conn->prepare("SELECT om.*, u.nom, u.prenom 
                           FROM OrderMission om 
                           JOIN Users u ON om.technicien_id = u.user_id 
                           WHERE om.order_num = :order_num");
    $stmt->execute(['order_num' => $order_num]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        die("Error: Ordre de mission introuvable.");
    }
    
    // Format dates for display
    $date_depart = date('d/m/Y', strtotime($order['date_depart']));
    $date_retour = date('d/m/Y', strtotime($order['date_retour']));
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordre de Mission - <?php echo htmlspecialchars($order_num); ?></title>
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
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 70px;
            margin-bottom: 10px;
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
            <p>Ordre de Mission N°: <?php echo htmlspecialchars($order_num); ?></p>
        </div>
        
        <div class="button-container">
            <a href="javascript:history.back()" class="back-button">Retour</a>
            <button onclick="window.print()" class="print-button">Enregistrer en PDF</button>
        </div>
        
        <div class="section">
            <h2>Informations du Technicien</h2>
            <div class="info-row">
                <div class="info-label">Nom et Prénom:</div>
                <div class="info-value"><?php echo htmlspecialchars($order['prenom'] . ' ' . $order['nom']); ?></div>
            </div>
        </div>
        
        <div class="section">
            <h2>Détails de la Mission</h2>
            <div class="info-row">
                <div class="info-label">Direction:</div>
                <div class="info-value"><?php echo htmlspecialchars($order['direction']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Destination:</div>
                <div class="info-value"><?php echo htmlspecialchars($order['destination']); ?></div>
            </div>
            <div class="info-row">
            <div class="info-label">Date de Départ:</div>
            <div class="info-value"><?php echo $date_depart; ?></div>
                
            </div>
            <div class="info-row">
            <div class="info-label">Date de Retour:</div>
            <div class="info-value"><?php echo $date_retour; ?></div>
                
            </div>
            <div class="info-row">
                <div class="info-label">Moyen de Transport:</div>
                <div class="info-value"><?php echo htmlspecialchars($order['moyen_tr']); ?></div>
            </div>
            <div class="info-row">
            <div class="info-label">Motif:</div>
            <div class="info-value"><?php echo htmlspecialchars($order['motif']); ?></div>
            </div>
        </div>
        
        <div class="section">
            <p>Cet ordre de mission est valable pour la période indiquée ci-dessus.</p>
            <p>Le technicien est autorisé à effectuer cette mission conformément aux règles et procédures de l'entreprise.</p>
            
            <div class="signature-section">
                <div class="signature-box">
                    <p>Le Responsable</p>
                    <div class="signature-line"></div>
                    <p>Nom et cachet</p>
                </div>
                
                <div class="signature-box">
                    <p>Le Technicien</p>
                    <div class="signature-line"></div>
                    <p><?php echo htmlspecialchars($order['prenom'] . ' ' . $order['nom']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <!-- <p>Document généré le <?php echo date('d/m/Y H:i:s'); ?></p> -->
            <!-- <p>VOTRE ENTREPRISE - Adresse - Contact</p> -->
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