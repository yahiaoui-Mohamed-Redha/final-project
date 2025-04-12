<?php
// view_fiche
include 'config.php';
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
    <div class="container mx-auto px-4 py-6">
        <!-- Back button -->
        <a href="javascript:history.back()" class="back-button flex items-center text-blue-600 hover:text-blue-800 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour à la Liste des Fiches
        </a>
        
        <!-- Main content card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800">
                    Détails de la Fiche d'Intervention #<?php echo htmlspecialchars($fiche['fiche_num']); ?>
                </h1>
                <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $statusClass; ?>">
                    <?php echo $statusText; ?>
                </span>
            </div>
            
            <!-- Alert messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-50 border-l-4 border-green-600 p-4 mb-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Fiche Information Section -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-700 border-b pb-2">Informations Générales</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Numéro de fiche</p>
                            <p class="font-medium"><?php echo htmlspecialchars($fiche['fiche_num']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date de création</p>
                            <p class="font-medium"><?php echo $formattedFicheDate; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Établissement</p>
                            <p class="font-medium"><?php echo htmlspecialchars($fiche['etablissement_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Code postal</p>
                            <p class="font-medium"><?php echo htmlspecialchars($fiche['postal_code'] ?? 'Non spécifié'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Personnel Section -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-700 border-b pb-2">Personnel</h2>
                    
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="bg-blue-100 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium"><?php echo htmlspecialchars($fiche['tech_nom'] . ' ' . $fiche['tech_prenom']); ?></p>
                            <p class="text-sm text-gray-500">Technicien</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <div class="bg-green-100 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium"><?php echo htmlspecialchars($fiche['rec_nom'] . ' ' . $fiche['rec_prenom']); ?></p>
                            <p class="text-sm text-gray-500">Receveur</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Panne Information Section -->
            <div class="mt-8 pt-4 border-t">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Panne Associée</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Numéro de panne</p>
                                <p class="font-medium"><?php echo htmlspecialchars($fiche['panne_num']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Nom</p>
                                <p class="font-medium"><?php echo htmlspecialchars($fiche['panne_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Type</p>
                                <p class="font-medium"><?php echo htmlspecialchars($fiche['type_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Date signalement</p>
                                <p class="font-medium"><?php echo $formattedSignalementDate; ?></p>
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">État</p>
                            <?php 
                            $etatClass = '';
                            switch($fiche['panne_etat']) {
                                case 'En attente':
                                    $etatClass = 'bg-yellow-100 text-yellow-800';
                                    break;
                                case 'En cours':
                                    $etatClass = 'bg-blue-100 text-blue-800';
                                    break;
                                case 'Résolue':
                                    $etatClass = 'bg-green-100 text-green-800';
                                    break;
                                case 'Non résolue':
                                    $etatClass = 'bg-red-100 text-red-800';
                                    break;
                                default:
                                    $etatClass = 'bg-gray-100 text-gray-800';
                            }
                            ?>
                            <span class="<?php echo $etatClass; ?> px-3 py-1 rounded-full text-sm font-medium">
                                <?php echo htmlspecialchars($fiche['panne_etat']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Description de la panne</p>
                        <div class="bg-gray-50 p-4 rounded-md min-h-[100px]">
                            <?php echo !empty($fiche['panne_description']) ? nl2br(htmlspecialchars($fiche['panne_description'])) : '<em class="text-gray-500">Aucune description disponible</em>'; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Compte rendu et observations -->
            <div class="mt-8 pt-4 border-t">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Détails de l'Intervention</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Compte-rendu d'intervention</p>
                        <div class="bg-gray-50 p-4 rounded-md min-h-[150px]">
                            <?php echo !empty($fiche['compte_rendu']) ? nl2br(htmlspecialchars($fiche['compte_rendu'])) : '<em class="text-gray-500">Aucun compte-rendu disponible</em>'; ?>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Observations</p>
                        <div class="bg-gray-50 p-4 rounded-md min-h-[150px]">
                            <?php echo !empty($fiche['observation']) ? nl2br(htmlspecialchars($fiche['observation'])) : '<em class="text-gray-500">Aucune observation disponible</em>'; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-8 pt-4 border-t flex flex-wrap justify-end gap-4">
                <button onclick="printToPDF()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Enregistrer en PDF
                </button>
                
                <a href="javascript:history.back()" class="back-button inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour à la liste
                </a>
                
                
            </div>
        </div>
        
        <!-- Footer Details -->
        <div class="text-center text-gray-500 text-xs mt-8">
            © <?php echo date('Y'); ?> Système de Gestion des Fiches d'Intervention
        </div>
    </div>

    <!-- Print-specific stylesheet that will only apply when printing -->
    <style type="text/css" media="print">
        /* Hide elements not needed in print version */
        .no-print, .no-print * {
            display: none !important;
        }
        
        /* Make sure the content takes the full page */
        body {
            padding: 0;
            margin: 0;
        }
        
        /* Add page break styles if needed */
        .page-break {
            page-break-before: always;
        }
    </style>
    
    <script>
        // Function to handle PDF printing
        function printToPDF() {
            // Add 'no-print' class to elements we don't want in the PDF
            const actionButtons = document.querySelector('.mt-8.pt-4.border-t.flex');
            actionButtons.classList.add('no-print');
            
            // Print the page
            window.print();
            
            // Remove the class after printing
            setTimeout(() => {
                actionButtons.classList.remove('no-print');
            }, 500);
        }
    </script>
</body>
</html>