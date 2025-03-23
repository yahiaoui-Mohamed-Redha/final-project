<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
include '../../app/config.php';

// Start session
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin'])) {
    header('Location: login.php');
    exit;
}

// Get the logged-in user's ID
$userId = $_SESSION['user_id'];

// Fetch issue types from the Type_panne table
$stmt_type_panne = $conn->prepare("SELECT * FROM Type_panne");
$stmt_type_panne->execute();
$type_pannes = $stmt_type_panne->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الإبلاغ عن الأعطال</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-50 min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-4">Signalement des pannes</h1>
        
        <?php if (isset($success_message)) echo "<div class='mb-4 p-3 bg-green-100 text-green-700 rounded-md'>$success_message</div>"; ?>
        <?php if (isset($error_message)) echo "<div class='mb-4 p-3 bg-red-100 text-red-700 rounded-md'>$error_message</div>"; ?>
        
        <form action="../app/signaler_des_panne.php" method="POST">
            <div id="panne-container" class="space-y-6">
                <div class="panne-item bg-gray-50 p-5 rounded-lg border border-gray-200">
                    <div class="space-y-6">
                        <!-- Nom de la panne -->
                        <div>
                            <label for="panne_name_0" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom de la panne
                            </label>
                            <input 
                                type="text" 
                                id="panne_name_0" 
                                name="pannes[0][panne_name]"  
                                autocomplete="panne_name" 
                                class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                required
                            >
                        </div>

                        <!-- Date du signalement -->
                        <div>
                            <label for="date_signalement_0" class="block text-sm font-medium text-gray-700 mb-1">
                            📅 Date du signalement
                            </label>
                            <input 
                                type="date" 
                                id="date_signalement_0"
                                name="pannes[0][date_signalement]" 
                                value="<?php echo date('Y-m-d'); ?>" 
                                required
                                class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm cursor-pointer"
                            >
                        </div>

                        <!-- Description de la panne -->
                        <div>
                            <label for="description_0" class="block text-sm font-medium text-gray-700 mb-1">
                                Description de la panne
                            </label>
                            <textarea 
                                id="description_0" 
                                name="pannes[0][description]" 
                                rows="3" 
                                class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            ></textarea>
                        </div>

                        <!-- Type de panne -->
                        <div>
                            <label for="type_id_0" class="block text-sm font-medium text-gray-700 mb-1">
                                Choix du type de panne
                            </label>
                            <div class="relative">
                                <select 
                                    id="type_id_0" 
                                    name="pannes[0][type_id]" 
                                    autocomplete="type_id" 
                                    class="block w-full rounded-md border-gray-300 bg-white py-2 pl-3 pr-10 text-gray-700 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm appearance-none"
                                >
                                    <?php foreach ($type_pannes as $type): ?>
                                        <option value="<?php echo $type['type_id']; ?>">
                                            <?php echo htmlspecialchars($type['type_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0   flex items-center px-1 text-gray-500">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8   flex items-center justify-end gap-x-4">
                <button 
                    type="button" 
                    id="add-panne" 
                    class="inline-flex items-center gap-x-1.5 rounded-md mr-2 bg-white px-5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 cursor-pointer"
                >
                    <svg class="-ml-0.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Ajout d'une autre panne
                </button>
                <button 
                    type="submit" 
                    class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 cursor-pointer" 
                    name="signaler_panne"
                >
                    Signaler
                </button>
            </div>
        </form>
    </div>
    
    <script>
        // JavaScript to dynamically add more panne fields
        document.getElementById('add-panne').addEventListener('click', function() {
            const container = document.getElementById('panne-container');
            const index = container.children.length;
            
            const newPanne = document.createElement('div');
            newPanne.className = 'panne-item bg-gray-50 p-5 rounded-lg border border-gray-200';
            newPanne.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Panne ${index + 1}</h3>
                    <button type="button" class="remove-panne text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-6">
                    <!-- Nom de la panne -->
                    <div>
                        <label for="panne_name_${index}" class="block text-sm font-medium text-gray-700 mb-1">
                            Nom de la panne
                        </label>
                        <input 
                            type="text" 
                            id="panne_name_${index}" 
                            name="pannes[${index}][panne_name]"  
                            autocomplete="panne_name" 
                            class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>

                    <!-- Date du signalement -->
                    <div>
                        <label for="date_signalement_${index}" class="block text-sm font-medium text-gray-700 mb-1">
                            Date du signalement
                        </label>
                        <input 
                            type="date" 
                            id="date_signalement_${index}"
                            name="pannes[${index}][date_signalement]" 
                            value="<?php echo date('Y-m-d'); ?>" 
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>

                    <!-- Description de la panne -->
                    <div>
                        <label for="description_${index}" class="block text-sm font-medium text-gray-700 mb-1">
                            Description de la panne
                        </label>
                        <textarea 
                            id="description_${index}" 
                            name="pannes[${index}][description]" 
                            rows="3" 
                            class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        ></textarea>
                    </div>

                    <!-- Type de panne -->
                    <div>
                        <label for="type_id_${index}" class="block text-sm font-medium text-gray-700 mb-1">
                            Choix du type de panne
                        </label>
                        <div class="relative">
                            <select 
                                id="type_id_${index}" 
                                name="pannes[${index}][type_id]" 
                                autocomplete="type_id" 
                                class="block w-full rounded-md border-gray-300 bg-white py-2 pl-3 pr-10 text-gray-700 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm appearance-none"
                            >
                                <?php foreach ($type_pannes as $type): ?>
                                    <option value="<?php echo $type['type_id']; ?>">
                                        <?php echo htmlspecialchars($type['type_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newPanne);
            
            // Add event listener to the remove button
            newPanne.querySelector('.remove-panne').addEventListener('click', function() {
                newPanne.remove();
            });
        });
    </script>
</body>
</html>