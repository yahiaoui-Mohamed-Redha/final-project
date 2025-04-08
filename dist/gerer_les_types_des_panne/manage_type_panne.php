<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('location: index.php');
    exit();
}

// Fetch all proposed types from the database
$stmt_proposed = $conn->prepare("SELECT * FROM propos_type WHERE status = 'pending'");
$stmt_proposed->execute();
$proposed_types = $stmt_proposed->fetchAll(PDO::FETCH_ASSOC);

// Fetch all Type_panne from the database
$stmt = $conn->prepare("SELECT * FROM Type_panne");
$stmt->execute();
$type_pannes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for success message from redirect
$success_message = $_GET['success'] ?? null;
?>

<html>
    <body>
        
    <!-- Success Alert -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm shadow-sm transition-all duration-300 ease-in-out border-l-4 border-green-500 flex items-center"  role="alert">
        <div class="flex-shrink-0 mr-3">
            <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="flex-1 text-green-800 font-medium">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
        <button type="button" class="ml-auto inline-flex text-green-600 hover:text-green-800 focus:outline-none" onclick="this.parentElement.style.display='none';">
            <svg class="h-4 w-4 cursor-pointer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
<?php endif; ?>

<!-- Error Alert -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm shadow-sm transition-all duration-300 ease-in-out border-l-4 border-red-500 flex items-center" role="alert">
        <div class="flex-shrink-0 mr-3">
            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="flex-1 text-red-800 font-medium">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
        <button type="button" class="ml-auto inline-flex text-red-600 hover:text-red-800 focus:outline-none" onclick="this.parentElement.style.display='none';">
            <svg class="h-4 w-4 cursor-pointer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
<?php endif; ?>

<div class="container mx-auto px-4 py-8">

               <!-- Table to display all Type_panne -->
               <div class="w-full shadow-md mb-1 bg-white flex flex-col items-start py-4 px-4 rounded-md">
                <h2 class="text-2xl font-bold text-gray-800   mb-4">Tous les Types de pannes</h2>

            <div class="w-full">
            <table class="w-full divide-y divide-gray-200 ">
                <tr class="tr-head">
                    <th scope="col" class="pl-4 w-[3%]">
                        <input type="checkbox" name="select-type" id="select-all">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Type Nom</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Description</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>

                </tr>
                <?php foreach ($type_pannes as $type_panne): ?>
                    <tr class="tr-body">
                        <td class="pl-4">
                            <input type="checkbox" name="select-rapport" id="select-rapport-<?php echo $type_panne['type_name'] ?? ''; ?>">
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-700 font-medium "><?php echo htmlspecialchars($type_panne['type_name']); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-700 font-medium "><?php echo htmlspecialchars($type_panne['description']); ?></td>

                        
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                <div class="flex justify-center space-x-2">
                    <a href="modifier_type_panne.php?id=<?php echo $type_panne['type_id']; ?>" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-[#0455b7] hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Modifier
                    </a>
                    <a href="../app/delete_type_panne.php?id=<?php echo $type_panne['type_id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce type?');" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-red-700 hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Supprimer
                    </a>
                </div>
                </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- Two-column layout for forms -->
    <div class="grid grid-cols-1 mt-5 md:grid-cols-2 gap-8">
        <!-- Left column - Create new type form -->
            
        <div class="form-container max-w-md mx-left bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-4">Créer Un Nouveau Type_panne</h1>
        <!-- Form to create a new Type_panne -->
        <form action="../app/create_type_panne.php" method="POST">
            <div id="panne-container" class="space-y-6">
                <div class="type_name bg-gray-50 p-5 rounded-lg border border-gray-200">
                    <div class="space-y-6">
                        <!-- Nom de  type -->
                        <div>
                            <label for="type_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nom du Type:
                            </label>
                            <input 
                                type="text" 
                                id="type_name" 
                                name="type_name"  
                                autocomplete="panne_name" 
                                class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                required
                            >
                        </div>

                        <!-- Description de type_panne -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description  type de panne
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="3" 
                                class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-x-4">
                <button 
                    type="submit" 
                    class="rounded-md text-white bg-[#0455b7] hover:bg-blue-900 focus:outline-none focus:ring-2 px-5 py-3 text-sm font-semibold  shadow-sm   focus-visible:outline-offset-2 focus-visible:outline-indigo-600 cursor-pointer" 
                    name="create_type"
                >
                créer un type
                </button>
            </div>
        </form>
    </div>

        <!-- Right column - Proposed types list -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Proposer Type_panne</h2>
            <div class="space-y-4">
                <?php if (empty($proposed_types)): ?>
                    <p class="text-gray-500">Aucun type proposé en attente d'approbation!.</p>
                <?php else: ?>
                    <?php foreach ($proposed_types as $proposed_type): ?>
                    <?php
                    $receveur_id = $proposed_type['receveur_id'];
                    $stmt_receveur = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                    $stmt_receveur->execute([$receveur_id]);
                    $receveur = $stmt_receveur->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($proposed_type['type_name']); ?></h3>
                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($proposed_type['description']); ?></p>
                        <p class="text-xs text-gray-500 mt-2">Proposed by: <?php echo htmlspecialchars($receveur['username']); ?></p>
                        <div class="flex space-x-2 mt-3">
                            <a href="../app/approve_type_panne.php?id=<?php echo $proposed_type['propos_id']; ?>" 
                               class="flex-1 py-1 px-3 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 text-center">
                                Approve
                            </a>
                            <a href="../app/reject_type_panne.php?id=<?php echo $proposed_type['propos_id']; ?>" 
                               class="flex-1 py-1 px-3 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 text-center"
                               onclick="return confirm('Are you sure you want to reject this proposed type?');">
                                Reject
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>