<?php
// view_rap.php
// Include database configuration
include 'config.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: ../login.php');
    exit;
}

// Get the logged-in user's ID
$userId = $_SESSION['user_id'];

// Get rapport ID from URL parameter
if (!isset($_GET['rap_num']) || empty($_GET['rap_num'])) {
    $_SESSION['error'] = "Numéro de rapport non spécifié.";
    header('Location: list_rapports.php');
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
    header('Location: list_rapports.php');
    exit;
}
?>

    <div class="container mx-auto px-4 py-6">
        <!-- Back button -->
        <a href="javascript:history.back()" class="back-button flex items-center text-blue-600 hover:text-blue-800 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour à la Liste des Rapports
        </a>
        
        <!-- Main content card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Détails du Rapport #<?php echo htmlspecialchars($rapport['rap_num']); ?></h1>
            
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
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Rapport Information Section -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-700 border-b pb-2">Informations du Rapport</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Numéro de rapport</p>
                            <p class="font-medium"><?php echo htmlspecialchars($rapport['rap_num']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Titre du rapport</p>
                            <p class="font-medium"><?php echo htmlspecialchars($rapport['rap_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date du rapport</p>
                            <p class="font-medium"><?php echo date('d/m/Y', strtotime($rapport['rap_date'])); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date de création</p>
                            <p class="font-medium"><?php echo isset($rapport['rap_date']) ? date('d/m/Y H:i', strtotime($rapport['rap_date'])) : 'Non disponible'; ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($rapport['description'])): ?>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Description</p>
                        <p class="font-medium bg-gray-50 p-3 rounded"><?php echo nl2br(htmlspecialchars($rapport['description'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- User Information Section -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-700 border-b pb-2">Information Utilisateur</h2>
                    
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-100 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium"><?php echo htmlspecialchars($rapport['username'] ?? 'Utilisateur inconnu'); ?></p>
                            <p class="text-sm text-gray-500">Créateur du rapport</p><br>
                            <p class="font-medium"><?php echo htmlspecialchars($rapport['role_nom']); ?></p>
                            <p class="text-sm text-gray-500">Créateur role</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attached Files Section -->
            <div class="mt-6 pt-4 border-t">
                <h2 class="text-xl font-semibold text-gray-700 mb-3">Fichiers Joints</h2>
                <p class="text-sm text-gray-500 mb-4"><?php echo count($files); ?> fichiers joints à ce rapport</p>
                
                <?php if (empty($files)): ?>
                    <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun fichier</h3>
                        <p class="mt-1 text-sm text-gray-500">Aucun fichier n'est joint à ce rapport</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
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
                            <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden transition-shadow duration-300 hover:shadow-md">
                                <div class="h-40 bg-gray-100 flex items-center justify-center overflow-hidden">
                                    <?php if (in_array($fileType, ['jpg', 'jpeg', 'png'])): ?>
                                        <img src="<?php echo htmlspecialchars($file['chemin_fichier']); ?>" alt="<?php echo htmlspecialchars($file['nom_fichier']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <?php if ($fileType === 'pdf'): ?>
                                            <div class="text-red-500 text-5xl">📄</div>
                                        <?php elseif (in_array($fileType, ['mp4', 'mov'])): ?>
                                            <div class="text-blue-500 text-5xl">🎬</div>
                                        <?php elseif (in_array($fileType, ['mp3', 'wav'])): ?>
                                            <div class="text-green-500 text-5xl">🎵</div>
                                        <?php elseif ($fileType === 'docx'): ?>
                                            <div class="text-blue-700 text-5xl">📝</div>
                                        <?php elseif ($fileType === 'sql'): ?>
                                            <div class="text-gray-700 text-5xl">💾</div>
                                        <?php else: ?>
                                            <div class="text-gray-500 text-5xl">📎</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <div class="font-medium truncate" title="<?php echo htmlspecialchars($file['nom_fichier']); ?>">
                                        <?php echo htmlspecialchars($file['nom_fichier']); ?>
                                    </div>
                                    <div class="flex items-center mt-2 text-xs text-gray-500">
                                        <span class="bg-gray-100 px-2 py-1 rounded-full font-medium"><?php echo strtoupper($fileType); ?></span>
                                        <span class="ml-2"><?php echo $fileSize . ' ' . $fileSizeUnit; ?></span>
                                    </div>
                                    <div class="mt-4">
                                        <a href="<?php echo htmlspecialchars($file['chemin_fichier']); ?>" download="<?php echo htmlspecialchars($file['nom_fichier']); ?>" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                                            Télécharger
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-8 pt-4 border-t flex flex-wrap justify-end gap-4">
                <button onclick="printToPDF()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-5 h-5 mr-2 border-gray-300  text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
                
                <?php if ($rapport['user_id'] == $userId): ?>
                
                <button onclick="confirmDelete('<?php echo htmlspecialchars($rapport['rap_num']); ?>')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Supprimer
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer Details -->
        <div class="text-center text-gray-500 text-xs mt-8">
            © <?php echo date('Y'); ?> Système de Gestion des Rapports
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-lg font-medium text-gray-900">Supprimer le rapport</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Êtes-vous sûr de vouloir supprimer ce rapport ? Cette action est irréversible.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button id="cancelDelete" type="button" class="inline-flex justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Annuler
                </button>
                <form id="deleteForm" method="post" action="delete_rapport.php">
                    <input type="hidden" id="deleteRapId" name="rap_num" value="">
                    <button type="submit" class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#0455b7] hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Supprimer
                    </button>
                </form>
            </div>
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
        // Function to show the delete confirmation modal
        function confirmDelete(rapNum) {
            document.getElementById('deleteRapId').value = rapNum;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        // Event listener for the cancel button
        document.getElementById('cancelDelete').addEventListener('click', function() {
            document.getElementById('deleteModal').classList.add('hidden');
        });
        
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