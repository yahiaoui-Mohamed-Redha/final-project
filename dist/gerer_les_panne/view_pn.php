<?php
include '../../app/config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}
$current_user_role = $_SESSION['user_role'];
if (!isset($_GET['panne_num'])) {
    $_SESSION['error'] = "Panne number is required";
    header('Location: gerer_les_panne.php');
    exit;
}

$panne_num = $_GET['panne_num'];

// Fetch the panne details with all associated information
$query = "SELECT 
            p.panne_num, 
            p.panne_name, 
            p.date_signalement, 
            p.description,
            roles.role_nom,
            COALESCE(e.etablissement_name, 'UNITE-POSTAL-WILAYA-DE-BOUMERDES') AS etablissement_name, 
            t.type_name, 
            p.panne_etat, 
            r.rap_num, 
            r.rap_name, 
            r.rap_date,
            r.description AS rap_description,
            u.nom AS user_nom, 
            u.prenom AS user_prenom 
          FROM Panne p 
          INNER JOIN Type_panne t ON p.type_id = t.type_id 
          INNER JOIN Users u ON p.receveur_id = u.user_id 
          LEFT JOIN Epost e ON u.postal_code = e.postal_code
          LEFT JOIN Rapport r ON p.rap_num = r.rap_num
          LEFT JOIN roles ON roles.role_id = u.role_id
          WHERE p.panne_num = :panne_num";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute(['panne_num' => $panne_num]);
    $panne = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$panne) {
        $_SESSION['error'] = "Panne not found";
        header('Location: gerer_les_panne.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header('Location: gerer_les_panne.php');
    exit;
}

// Fetch related history/logs if you have a history table
$history_query = "SELECT * FROM Panne_History WHERE panne_num = :panne_num ORDER BY date_updated DESC";
try {
    $history_stmt = $conn->prepare($history_query);
    $history_stmt->execute(['panne_num' => $panne_num]);
    $history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Just log the error, we'll continue without history
    error_log("Error fetching panne history: " . $e->getMessage());
    $history = [];
}

// Fetch admin details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$admin = $select->fetch(PDO::FETCH_ASSOC);

?>
    <style>
        
        /* Container and layout */
        .container {
            width: 100%;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }
    
        /* Responsive */
        @media (min-width: 768px) {
            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- Back button -->
        <a href="javascript:history.back()" class="flex items-center text-blue-600 hover:text-blue-800 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour à la Liste des Pannes
        </a>

        <!-- Main content card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Panne Détails #<?php echo htmlspecialchars($panne['panne_num']); ?></h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Panne Information Section -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-700 border-b pb-2">Panne Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Panne Number</p>
                            <p class="font-medium"><?php echo htmlspecialchars($panne['panne_num']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Panne Name</p>
                            <p class="font-medium"><?php echo htmlspecialchars($panne['panne_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date Signalement</p>
                            <p class="font-medium"><?php echo htmlspecialchars($panne['date_signalement']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">État</p>
                            <p class="font-medium"><?php echo htmlspecialchars($panne['panne_etat']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Type</p>
                            <p class="font-medium"><?php echo htmlspecialchars($panne['type_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Établissement</p>
                            <p class="font-medium"><?php echo htmlspecialchars($panne['etablissement_name']); ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($panne['description'])): ?>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Description</p>
                        <p class="font-medium bg-gray-50 p-3 rounded"><?php echo nl2br(htmlspecialchars($panne['description'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Rapport Information Section -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-700 border-b pb-2">Informations sur le Rapport</h2>
                    
                    <?php if (!empty($panne['rap_num'])): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Rapport Number</p>
                            <p class="font-medium"><?php echo htmlspecialchars($panne['rap_num']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Rapport Name</p>
                            <p class="font-medium"><?php echo htmlspecialchars($panne['rap_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Rapport Date</p>
                            <p class="font-medium"><?php echo htmlspecialchars($panne['rap_date']); ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($panne['rap_description'])): ?>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Rapport Description</p>
                        <p class="font-medium bg-gray-50 p-3 rounded"><?php echo nl2br(htmlspecialchars($panne['rap_description'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <p class="text-gray-500 italic">No rapport attached to this panne.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- User Information Section -->
            <div class="mt-6 pt-4 border-t">
                <h2 class="text-xl font-semibold text-gray-700 mb-3">User Information</h2>
                
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium"><?php echo htmlspecialchars($panne['user_prenom'] . ' ' . $panne['user_nom']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo $panne['role_nom']; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-8 pt-4 border-t flex justify-end space-x-4">
        
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

                <!-- <button onclick="confirmDelete('<?php echo htmlspecialchars($order_mission['order_num']); ?>')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Supprimer
                </button> -->
        </div>
        </div>
        
        <!-- History/Logs Section (if available) -->
        <?php if (!empty($history)): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Panne History</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Previous State</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New State</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($history as $log): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($log['date_updated']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($log['action']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($log['previous_state']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($log['new_state']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($log['updated_by']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
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