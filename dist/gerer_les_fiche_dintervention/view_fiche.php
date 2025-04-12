<?php
include '../../app/config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}

// Check if fiche_num is provided
if (!isset($_GET['fiche_num']) || empty($_GET['fiche_num'])) {
    $_SESSION['error'] = "Numéro de fiche invalide.";
    header('Location: ../index.php');
    exit;
}

$fiche_num = $_GET['fiche_num'];

// Fetch fiche details with related data
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
            e.postal_code,
            t.type_name, 
            p.panne_etat, 
            tech.user_id AS tech_id,
            tech.nom AS tech_nom, 
            tech.prenom AS tech_prenom,
            rec.user_id AS rec_id,
            rec.nom AS rec_nom, 
            rec.prenom AS rec_prenom 
          FROM FicheIntervention fi
          INNER JOIN Panne p ON fi.panne_num = p.panne_num
          INNER JOIN Type_panne t ON p.type_id = t.type_id 
          INNER JOIN Users tech ON fi.technicien_id = tech.user_id
          INNER JOIN Users rec ON fi.receveur_id = rec.user_id
          LEFT JOIN Epost e ON rec.postal_code = e.postal_code
          WHERE fi.fiche_num = :fiche_num";

$stmt = $conn->prepare($query);
$stmt->bindParam(':fiche_num', $fiche_num, PDO::PARAM_INT);
$stmt->execute();

$fiche = $stmt->fetch(PDO::FETCH_ASSOC);

// If fiche not found
if (!$fiche) {
    $_SESSION['error'] = "Fiche d'intervention introuvable.";
    header('Location: ../index.php');
    exit;
}

// Get user details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$currentUser = $select->fetch(PDO::FETCH_ASSOC);

// Format date for display
$formattedFicheDate = date('d/m/Y H:i', strtotime($fiche['fiche_date']));
$formattedSignalementDate = date('d/m/Y H:i', strtotime($fiche['date_signalement']));

// Determine status text and classes
$statusText = $fiche['archived'] == 1 ? 'Archivée' : 'Active';
$statusClass = $fiche['archived'] == 1 ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800';

// Check if current user can edit this fiche
$canEdit = false;
if ($_SESSION['user_role'] == 'Admin' || 
    ($_SESSION['user_role'] == 'Technicien' && $_SESSION['user_id'] == $fiche['tech_id']) ||
    ($_SESSION['user_role'] == 'Receveur' && $_SESSION['user_id'] == $fiche['rec_id'])) {
    $canEdit = true;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Fiche d'Intervention</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Back button and actions -->
        <div class="flex items-center justify-between mb-6">
            <a href="../index.php" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
            </a>
            <div class="flex space-x-3">
                <?php if ($canEdit): ?>
                <a href="gerer_les_fiches/fiche_edit.php?fiche_num=<?php echo $fiche['fiche_num']; ?>" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Modifier
                </a>
                <?php endif; ?>
                <a href="../app/fiche_export.php?fiche_num=<?php echo $fiche['fiche_num']; ?>" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Exporter
                </a>
            </div>
        </div>

        <!-- Alert messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm shadow-sm border-l-4 border-green-500 flex items-center" role="alert">
            <div class="flex-shrink-0 mr-3">
                <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-1 text-green-800 font-medium">
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm shadow-sm border-l-4 border-red-500 flex items-center" role="alert">
            <div class="flex-shrink-0 mr-3">
                <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-1 text-red-800 font-medium">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fiche header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    Fiche d'Intervention N° <?php echo htmlspecialchars($fiche['fiche_num']); ?>
                </h1>
                <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $statusClass; ?>">
                    <?php echo $statusText; ?>
                </span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">Informations Générales</h2>
                    <div class="space-y-2">
                        <div class="flex">
                            <span class="w-40 text-gray-600">Date de création:</span>
                            <span class="font-medium"><?php echo $formattedFicheDate; ?></span>
                        </div>
                        <div class="flex">
                            <span class="w-40 text-gray-600">Établissement:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($fiche['etablissement_name']); ?></span>
                        </div>
                        <div class="flex">
                            <span class="w-40 text-gray-600">Code postal:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($fiche['postal_code'] ?? 'Non spécifié'); ?></span>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">Personnel</h2>
                    <div class="space-y-2">
                        <div class="flex">
                            <span class="w-40 text-gray-600">Technicien:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($fiche['tech_nom'] . ' ' . $fiche['tech_prenom']); ?></span>
                        </div>
                        <div class="flex">
                            <span class="w-40 text-gray-600">Receveur:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($fiche['rec_nom'] . ' ' . $fiche['rec_prenom']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panne associée -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Panne Associée</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div class="space-y-2">
                    <div class="flex">
                        <span class="w-40 text-gray-600">Numéro de panne:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($fiche['panne_num']); ?></span>
                    </div>
                    <div class="flex">
                        <span class="w-40 text-gray-600">Nom:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($fiche['panne_name']); ?></span>
                    </div>
                    <div class="flex">
                        <span class="w-40 text-gray-600">Type:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($fiche['type_name']); ?></span>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <div class="flex">
                        <span class="w-40 text-gray-600">Date signalement:</span>
                        <span class="font-medium"><?php echo $formattedSignalementDate; ?></span>
                    </div>
                    <div class="flex">
                        <span class="w-40 text-gray-600">État:</span>
                        <span class="font-medium">
                            <?php 
                            $etatClass = '';
                            switch($fiche['panne_etat']) {
                                case 'En attente':
                                    $etatClass = 'text-yellow-600';
                                    break;
                                case 'En cours':
                                    $etatClass = 'text-blue-600';
                                    break;
                                case 'Résolue':
                                    $etatClass = 'text-green-600';
                                    break;
                                case 'Non résolue':
                                    $etatClass = 'text-red-600';
                                    break;
                                default:
                                    $etatClass = 'text-gray-600';
                            }
                            ?>
                            <span class="<?php echo $etatClass; ?>"><?php echo htmlspecialchars($fiche['panne_etat']); ?></span>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <h3 class="font-medium text-gray-700 mb-2">Description de la panne</h3>
                <div class="bg-gray-50 p-4 rounded-md">
                    <?php echo !empty($fiche['panne_description']) ? nl2br(htmlspecialchars($fiche['panne_description'])) : '<em class="text-gray-500">Aucune description disponible</em>'; ?>
                </div>
            </div>
        </div>

        <!-- Compte rendu et observations -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Compte-rendu d'intervention</h2>
                <div class="bg-gray-50 p-4 rounded-md min-h-[150px]">
                    <?php echo !empty($fiche['compte_rendu']) ? nl2br(htmlspecialchars($fiche['compte_rendu'])) : '<em class="text-gray-500">Aucun compte-rendu disponible</em>'; ?>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Observations</h2>
                <div class="bg-gray-50 p-4 rounded-md min-h-[150px]">
                    <?php echo !empty($fiche['observation']) ? nl2br(htmlspecialchars($fiche['observation'])) : '<em class="text-gray-500">Aucune observation disponible</em>'; ?>
                </div>
            </div>
        </div>
        
        <!-- Footer actions -->
        <div class="flex justify-between mt-6">
            <a href="../index.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour à la liste
            </a>
            
            <div class="flex space-x-3">
                <?php if ($canEdit): ?>
                <a href="gerer_les_fiches/fiche_edit.php?fiche_num=<?php echo $fiche['fiche_num']; ?>" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Modifier
                </a>
                <?php endif; ?>
                
                <a href="../app/fiche_export.php?fiche_num=<?php echo $fiche['fiche_num']; ?>" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Exporter en PDF
                </a>
            </div>
        </div>
    </div>

    <script>
        // Add any needed JavaScript here
    </script>
</body>
</html>