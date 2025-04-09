<?php
// print_rapport.php
// Include database configuration
include 'config.php';

// Start session
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}

// Get rapport ID from URL parameter
if (!isset($_GET['rap_num']) || empty($_GET['rap_num'])) {
    $_SESSION['error'] = "Numéro de rapport non spécifié.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

$rap_num = $_GET['rap_num'];

try {
    // Fetch rapport details
    $stmt_rapport = $conn->prepare("SELECT r.*, u.username, rol.role_nom
    FROM rapport r
    LEFT JOIN Users u ON r.user_id = u.user_id
    LEFT JOIN roles rol ON u.role_id = rol.role_id
    WHERE r.rap_num = :rap_num");
    $stmt_rapport->execute(['rap_num' => $rap_num]);
    $rapport = $stmt_rapport->fetch(PDO::FETCH_ASSOC);

    if (!$rapport) {
        $_SESSION['error'] = "Rapport non trouvé.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Fetch attached files
    $stmt_files = $conn->prepare("SELECT * FROM RapFichiers WHERE rap_num = :rap_num");
    $stmt_files->execute(['rap_num' => $rap_num]);
    $files = $stmt_files->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des détails du rapport: " . $e->getMessage();
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Get current date for the PDF footer
$current_date = date('d/m/Y H:i');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport #<?php echo htmlspecialchars($rapport['rap_num']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0455b7;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            color: #0455b7;
            margin: 0;
            font-size: 24px;
        }
        h2 {
            color: #0455b7;
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .section {
            margin-bottom: 25px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 2px;
        }
        .info-value {
            font-weight: 500;
        }
        .description {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .user-icon {
            width: 40px;
            height: 40px;
            background: #e6f0ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .files-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .file-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            font-size: 13px;
        }
        .file-name {
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 5px;
        }
        .file-details {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 12px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-break-inside {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with logo and title -->
        <div class="header">
            <!-- If you have a logo, uncomment this line and add your logo path -->
            <!-- <img src="/path/to/your/logo.png" alt="Logo" class="logo"> -->
            <h1>Rapport #<?php echo htmlspecialchars($rapport['rap_num']); ?></h1>
            <p><?php echo htmlspecialchars($rapport['rap_name']); ?></p>
        </div>
        
        <!-- Report Information Section -->
        <div class="section no-break-inside">
            <h2>Informations du Rapport</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Numéro de rapport</div>
                    <div class="info-value"><?php echo htmlspecialchars($rapport['rap_num']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nom du rapport</div>
                    <div class="info-value"><?php echo htmlspecialchars($rapport['rap_name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date du rapport</div>
                    <div class="info-value"><?php echo date('d/m/Y', strtotime($rapport['rap_date'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date de création</div>
                    <div class="info-value"><?php echo isset($rapport['rap_date']) ? date('d/m/Y', strtotime($rapport['rap_date'])) : 'Non disponible'; ?></div>
                </div>
            </div>
            
            <?php if (!empty($rapport['description'])): ?>
            <div class="description">
                <div class="info-label">Description</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($rapport['description'])); ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- User Information Section -->
        <div class="section no-break-inside">
            <h2>Information Utilisateur</h2>
            <div class="user-info">
                <div class="user-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0455b7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div>
                    <div class="info-value"><?php echo htmlspecialchars($rapport['username'] ?? 'Utilisateur inconnu'); ?></div>
                    <div class="info-label">Créateur du rapport (<?php echo htmlspecialchars($rapport['role_nom']); ?>)</div>
                </div>
            </div>
        </div>
        
        <!-- Attached Files Section -->
        <div class="section no-break-inside">
            <h2>Fichiers Joints (<?php echo count($files); ?>)</h2>
            
            <?php if (empty($files)): ?>
                <p>Aucun fichier n'est joint à ce rapport</p>
            <?php else: ?>
                <div class="files-list">
                    <?php foreach ($files as $file): ?>
                        <?php 
                        $fileType = strtolower($file['type_fichier']);
                        $fileSize = round($file['taille_fichier'] / 1024, 2);
                        $fileSizeUnit = 'Ko';
                        
                        if ($fileSize > 1024) {
                            $fileSize = round($fileSize / 1024, 2);
                            $fileSizeUnit = 'Mo';
                        }
                        ?>
                        <div class="file-item">
                            <div class="file-name"><?php echo htmlspecialchars($file['nom_fichier']); ?></div>
                            <div class="file-details">
                                <span><?php echo strtoupper($fileType); ?></span>
                                <span><?php echo $fileSize . ' ' . $fileSizeUnit; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Document généré le <?php echo $current_date; ?></p>
            <p>© <?php echo date('Y'); ?> Système de Gestion des Rapports</p>
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