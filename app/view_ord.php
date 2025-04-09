<?php
// view_ord.php
include 'config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'Admin') {
    header('location:login.php');
    exit;
}
// Check if order_num is provided
if (!isset($_GET['order_num'])) {
    $_SESSION['error'] = "No order mission specified";
    header('location:../../pages/index.php');
    exit;
}

$order_num = $_GET['order_num'];

// Fetch order mission details
$stmt = $conn->prepare("SELECT * FROM OrderMission WHERE order_num = ?");
$stmt->execute([$order_num]);
$order_mission = $stmt->fetch(PDO::FETCH_ASSOC);

// If order mission doesn't exist
if (!$order_mission) {
    $_SESSION['error'] = "Order mission not found";
    header('location:../../pages/index.php');
    exit;
}

// Check user permission - only admin or assigned technician can view
if ($_SESSION['user_role'] != 'Admin' && $_SESSION['user_id'] != $order_mission['technicien_id']) {
    $_SESSION['error'] = "You don't have permission to view this order mission";
    header('location:../../pages/index.php');
    exit;
}

// Get technician details
$technicien_id = $order_mission['technicien_id'];
$select = $conn->prepare("SELECT nom, prenom FROM Users WHERE user_id = ?");
$select->execute([$technicien_id]);
$technicien = $select->fetch(PDO::FETCH_ASSOC);

// Fetch user details (for sidebar/navigation)
$user_id = $_SESSION['user_id'];
$selectUser = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$selectUser->execute([$user_id]);
$admin = $selectUser->fetch(PDO::FETCH_ASSOC);
?>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <div class="container mx-auto px-4 py-6">
        <!-- Back button -->
        <a href="javascript:history.back()" class="back-button flex items-center text-blue-600 hover:text-blue-800 mb-4 no-print">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour à la Liste des Ordres de Mission
        </a>
        
        <!-- Main content card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Détails de l'Ordre de Mission #<?php echo htmlspecialchars($order_mission['order_num']); ?></h1>
            
            <!-- Success Alert -->
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

            <!-- Error Alert -->
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
                <!-- Order Information Section -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-700 border-b pb-2">Informations de la Mission</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Numéro d'ordre</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order_mission['order_num']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Direction</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order_mission['direction']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Destination</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order_mission['destination']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Motif</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order_mission['motif']); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Travel Information Section -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-700 border-b pb-2">Détails du Voyage</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Moyen de Transport</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order_mission['moyen_tr']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date de Départ</p>
                            <p class="font-medium"><?php echo date('d/m/Y', strtotime($order_mission['date_depart'])); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date de Retour</p>
                            <p class="font-medium"><?php echo date('d/m/Y', strtotime($order_mission['date_retour'])); ?></p>
                        </div>
                        <div>
                            <p class=" mb-1  text-sm text-gray-500">Durée de mission</p>
                            <span class="text-gray-800 bg-blue-100 px-3 py-1 font-medium  rounded-full ">
                                    <?php 
                                        $date1 = new DateTime($order_mission['date_depart']);
                                        $date2 = new DateTime($order_mission['date_retour']);
                                        $diff = $date2->diff($date1);
                                        echo $diff->days + 1;
                                    ?> jours
                                </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Technicien Information Section -->
            <div class="mt-6 pt-4 border-t">
                <h2 class="text-xl font-semibold text-gray-700 mb-3">Information Technicien</h2>
                
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium"><?php echo htmlspecialchars($technicien['nom'] . ' ' . $technicien['prenom']); ?></p>
                        <p class="text-sm text-gray-500">Technicien assigné</p>
                    </div>
                </div>
            </div>
            
            <!-- Observations Section -->
            <?php if (isset($order_mission['observations']) && !empty($order_mission['observations'])): ?>
            <div class="mt-6 pt-4 border-t">
                <h2 class="text-xl font-semibold text-gray-700 mb-3">Observations</h2>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($order_mission['observations'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="mt-8 pt-4 border-t flex flex-wrap justify-end gap-4 no-print">
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2 border-gray-300  text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Enregistrer en PDF
                </button>
                
                <a href="javascript:history.back()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour à la liste
                </a>
                
                <?php if ($_SESSION['user_role'] == 'Admin' || $_SESSION['user_id'] == $order_mission['technicien_id']): ?>
                
                
                <button onclick="confirmDelete('<?php echo htmlspecialchars($order_mission['order_num']); ?>')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
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
            © <?php echo date('Y'); ?> Système de Gestion des Ordres de Mission
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
                        <h3 class="text-lg font-medium text-gray-900">Supprimer l'ordre de mission</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Êtes-vous sûr de vouloir supprimer cet ordre de mission ? Cette action est irréversible.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button id="cancelDelete" type="button" class="inline-flex justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Annuler
                </button>
                <form id="deleteForm" method="post" action="delete_ord.php">
                    <input type="hidden" id="deleteOrderId" name="order_num" value="">
                    <button type="submit" class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Function to show the delete confirmation modal
        function confirmDelete(orderNum) {
            document.getElementById('deleteOrderId').value = orderNum;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        // Event listener for the cancel button
        document.getElementById('cancelDelete').addEventListener('click', function() {
            document.getElementById('deleteModal').classList.add('hidden');
        });
    </script>