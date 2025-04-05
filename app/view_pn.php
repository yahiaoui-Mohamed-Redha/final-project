<?php
include 'config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Panne Details</title>
    <style>
        /* Tailwind-like styles */
        /* Base styles */
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            color: #1f2937;
            line-height: 1.5;
        }
        
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
        
        .bg-white {
            background-color: #ffffff;
        }
        
        .bg-gray-50 {
            background-color: #f9fafb;
        }
        
        .bg-gray-100 {
            background-color: #f3f4f6;
        }
        
        .bg-blue-100 {
            background-color: #dbeafe;
        }
        
        .bg-blue-600 {
            background-color: #2563eb;
        }
        
        .bg-green-600 {
            background-color: #059669;
        }
        
        .bg-indigo-600 {
            background-color: #4f46e5;
        }
        
        .text-white {
            color: #ffffff;
        }
        
        .text-gray-500 {
            color: #6b7280;
        }
        
        .text-gray-700 {
            color: #374151;
        }
        
        .text-gray-800 {
            color: #1f2937;
        }
        
        .text-blue-500 {
            color: #3b82f6;
        }
        
        .text-blue-600 {
            color: #2563eb;
        }
        
        .text-blue-800 {
            color: #1e40af;
        }
        
        /* Typography */
        .text-2xl {
            font-size: 1.5rem;
        }
        
        .text-xl {
            font-size: 1.25rem;
        }
        
        .text-sm {
            font-size: 0.875rem;
        }
        
        .text-xs {
            font-size: 0.75rem;
        }
        
        .font-medium {
            font-weight: 500;
        }
        
        .font-semibold {
            font-weight: 600;
        }
        
        .font-bold {
            font-weight: 700;
        }
        
        .uppercase {
            text-transform: uppercase;
        }
        
        .italic {
            font-style: italic;
        }
        
        .tracking-wider {
            letter-spacing: 0.05em;
        }
        
        /* Spacing */
        .p-3 {
            padding: 0.75rem;
        }
        
        .p-6 {
            padding: 1.5rem;
        }
        
        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .px-6 {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        
        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        
        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        
        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        
        .py-6 {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }
        
        .pt-4 {
            padding-top: 1rem;
        }
        
        .pb-2 {
            padding-bottom: 0.5rem;
        }
        
        .mb-3 {
            margin-bottom: 0.75rem;
        }
        
        .mb-4 {
            margin-bottom: 1rem;
        }
        
        .mb-6 {
            margin-bottom: 1.5rem;
        }
        
        .mr-1 {
            margin-right: 0.25rem;
        }
        
        .mt-4 {
            margin-top: 1rem;
        }
        
        .mt-6 {
            margin-top: 1.5rem;
        }
        
        .mt-8 {
            margin-top: 2rem;
        }
        
        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }
        
        .space-x-4 > * + * {
            margin-left: 1rem;
        }
        
        .space-y-4 > * + * {
            margin-top: 1rem;
        }
        
        /* Flexbox */
        .flex {
            display: flex;
        }
        
        .items-center {
            align-items: center;
        }
        
        .justify-end {
            justify-content: flex-end;
        }
        
        /* Grid */
        .grid {
            display: grid;
        }
        
        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        
        .gap-4 {
            gap: 1rem;
        }
        
        .gap-6 {
            gap: 1.5rem;
        }
        
        /* Borders */
        .border-t {
            border-top-width: 1px;
            border-top-style: solid;
            border-top-color: #e5e7eb;
        }
        
        .border-b {
            border-bottom-width: 1px;
            border-bottom-style: solid;
            border-bottom-color: #e5e7eb;
        }
        
        .divide-y > * + * {
            border-top-width: 1px;
            border-top-style: solid;
            border-top-color: #e5e7eb;
        }
        
        .rounded {
            border-radius: 0.25rem;
        }
        
        .rounded-lg {
            border-radius: 0.5rem;
        }
        
        .rounded-full {
            border-radius: 9999px;
        }
        
        /* Shadow */
        .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .min-w-full {
            min-width: 100%;
        }
        
        .whitespace-nowrap {
            white-space: nowrap;
        }
        
        .overflow-x-auto {
            overflow-x: auto;
        }
        
        /* Hover effects */
        .hover\:bg-blue-700:hover {
            background-color: #0D47A1;
        }
        
        .hover\:bg-green-700:hover {
            background-color: #0455b7;
        }
        
        .hover\:bg-indigo-700:hover {
            background-color: #0D47A1;
        }
        
        .hover\:text-blue-800:hover {
            color: #1e40af;
        }
        .bg-blue-800{
            background-color:#0455b7
        }
        
        /* Icons */
        .h-5, .w-5 {
            height: 1.25rem;
            width: 1.25rem;
        }
        
        .h-6, .w-6 {
            height: 1.5rem;
            width: 1.5rem;
        }
        
        .no-underline {
            text-decoration: none;
        }
        /* Responsive */
        @media (min-width: 768px) {
            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
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
                        <p class="text-sm text-gray-500">Receveur</p>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-8 pt-4 border-t flex justify-end space-x-4">
            <a href="gerer_les_panne/panne_edit.php?panne_num=<?php echo $panne['panne_num']; ?>" class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-700 no-underline">
                Edit Panne
            </a>
            <?php if (empty($panne['rap_num']) && ($_SESSION['user_role'] == 'Admin' || $_SESSION['user_role'] == 'Technicien')): ?>
            <a href="create_rapport.php?panne_num=<?php echo $panne['panne_num']; ?>" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-blue-700 no-underline">
                Create Rapport
            </a>
            <?php endif; ?>
            <a href="../app/panne_export.php?panne_num=<?php echo $panne['panne_num']; ?>" class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-indigo-700 no-underline">
                Export Panne
            </a>
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

    <!-- Include your JavaScript files -->
    <!-- <script src="../../js/jquery.min.js"></script> -->
    <!-- <script src="../../js/script.js"></script> -->
</body>
</html>