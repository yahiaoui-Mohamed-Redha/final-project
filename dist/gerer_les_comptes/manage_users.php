<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    // Redirect to the login page or show an error message
    header('location: index.php');
    exit();
}

// Cache settings
$cache_file = '../../cache/users_cache.json'; // Cache file location
$cache_time = 432000; // 5 days in seconds

// Check if a valid cache file exists
if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
    // Load data from cache
    $users = json_decode(file_get_contents($cache_file), true);
} else {
    // Fetch data from the database if cache is outdated or missing
    $stmt = $conn->prepare("SELECT u.username, u.nom, u.prenom, e.etablissement_name, u.role_id, u.etat_compte 
                            FROM Users u 
                            LEFT JOIN Epost e ON u.postal_code = e.postal_code");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Store the data in cache
    file_put_contents($cache_file, json_encode($users));
}

// Fetch admin details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$admin = $select->fetch(PDO::FETCH_ASSOC);

// Fetch all users from the database
$stmt = $conn->prepare("SELECT u.*, e.etablissement_name FROM Users u LEFT JOIN Epost e ON u.postal_code = e.postal_code");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine the current content page
$contentPage = basename($_SERVER['PHP_SELF']);
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

    <div id="modal-overlay-M" class="hidden fixed w-full h-full flex items-center justify-center inset-0 bg-[#0000007a] backdrop-opacity-10 z-[99]">
        <div id="modal" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-lg shadow-lg w-1/3">
            <h2 class="text-xl font-semibold mb-4">Modifier l'utilisateur</h2>
            <form action="../app/modifying_users.php" method="post" id="modify-user-form">
                <!-- Hidden User ID -->
                <input type="hidden" name="user_id" id="user_id">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                    <input type="text" name="nom" id="nom" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Prénom</label>
                    <input type="text" name="prenom" id="prenom" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Type de compte</label>
                    <p class="mt-1 px-3 py-2 text-gray-900 font-semibold" id="account_type">Type de compte est : </p>
                    <!-- Hidden input to store role ID -->
                    <input type="hidden" name="role_id" id="role_id">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Mot de passe (Laissez vide pour ne pas changer)</label>
                    <input type="password" name="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" id="close-modal" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Fermer</button>
                    <button type="submit" name="update_account" class="ml-2 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>


<div class="w-full bg-white flex items-center justify-between py-2 px-4 rounded-md mb-2">
    <div class="p-2">
        <ul class="flex gap-4 bg-[#f8f8f8] rounded-md p-1 w-max overflow-hidden relative">
            <!-- Tabs -->
            <li>
                <button data-role="all" class="tab text-[#0455b7] bg-white rounded-lg font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer" aria-selected="true">
                    Tous les comptes
                </button>
            </li>
            <li>
                <button data-role="technicien" class="tab text-gray-600 rounded-xl font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer" aria-selected="false">
                    Les Techniciennes
                </button>
            </li>
            <li>
                <button data-role="receveur" class="tab text-gray-600 rounded-xl font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer" aria-selected="false">
                    Les Receveurs
                </button>
            </li>
        </ul>
    </div>
    <div class="flex items-center justify-between">
        <div class="relative">
            <button id="export-button" class="flex items-center p-1.5 border rounded-lg text-gray-600 border-gray-200 transition-colors duration-300 transform mr-2 hover:bg-[#c8d3f659] hover:text-[#0455b7] cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                    <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                    <path d="M9 15h6"></path>
                    <path d="M12.5 17.5l2.5 -2.5l-2.5 -2.5"></path>
                </svg>
                <span class="mx-2 text-sm font-medium cursor-pointer">Exporter</span>
            </button>
            <!-- Update with absolute URLs -->
            <div id="export-dropdown" class="hidden absolute z-10 mt-2 w-48 bg-white rounded-md shadow-lg py-1">
                <a href="../app/export_users_table.php?format=pdf" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Exporter en PDF
                    </div>
                </a>
                <a href="../app/export_users_table.php?format=excel" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Exporter en Excel
                    </div>
                </a>
            </div>
        </div>
        <a id="new" href="gerer_les_comptes/create_users.php" class="load-page-link flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                <path d="M12 5l0 14"></path>
                <path d="M5 12l14 0"></path>
            </svg>
            <span class="mx-2 text-sm font-medium">Créer un compte</span>
        </a>
    </div>
</div>

<div class="w-full bg-white flex flex-col items-center py-2 px-4 rounded-md">
    <!--  filter and search -->
    <div class="w-full p-4 flex justify-between items-center ">
        <div class="flex space-x-2">
            <button class="flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-lin²²²ejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
            <div class="relative search-bar">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="search-input" class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm w-64" placeholder="Search user...">
                <button type="submit" id="search-button" class="text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900 font-medium rounded-lg text-sm px-6 py-2 text-center  cursor-pointer">Search</button>
            </div>
        </div>
    </div>
    <!-- /filter and search -->

    <div class="w-full">
        <table class="w-full divide-y divide-gray-200">
            <tr class="tr-head">
                <th scope="col" class="pl-4 w-[3%]">
                    <input type="checkbox" name="select-users" id="select-all">
                </th>
                <th scope="col" class="th-class w-[17%]">Username</th>
                <th scope="col" class="th-class w-[11%] uppercase">Nom</th>
                <th scope="col" class="th-class w-[11%]">Prenom</th>
                <th scope="col" class="th-class w-[18%] break-all">Etablissement</th>
                <th scope="col" class="th-class w-[11%]">Role</th>
                <th scope="col" class="th-class whitespace-nowrap text-center w-[12%]">Etat Compte</th>
                <th scope="col" class="th-class text-center w-[13%]">Actions</th>
            </tr>
            <?php foreach ($users as $user) { ?>
                <tr class="tr-body">
                    <td class="pl-4">
                        <input type="checkbox" name="select-user" id="select-user-<?php echo $user['user_id']; ?>">
                    </td>
                    <td class="td-class">
                        <?php echo htmlspecialchars($user['username']); ?>
                        <div class="text-xs text-gray-400">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </div>
                    </td>
                    <td class="td-class"><?php echo htmlspecialchars($user['nom']); ?></td>
                    <td class="td-class"><?php echo htmlspecialchars($user['prenom']); ?></td>
                    <td class="td-class w-[18%]"><?php echo htmlspecialchars($user['etablissement_name'] ?? 'UPW Boumerdes'); ?></td>
                    <td class="td-class">
                        <?php echo ($user['role_id'] == 1 ? 'Admin' : ($user['role_id'] == 2 ? 'Technicien' : 'Receveur')); ?>
                    </td>
                    <td class="td-class text-center">
                        <div class="flex p-2 rounded-sm hover:bg-gray-100">
                            <label class="inline-flex items-center w-full cursor-pointer">
                                <input type="checkbox" id="account-status-<?php echo $user['user_id']; ?>" class="sr-only peer"
                                    data-user-id="<?php echo htmlspecialchars($user['user_id']); ?>"
                                    <?php echo ($user['etat_compte'] == 1 ? 'checked' : ''); ?>>
                                <div class="relative w-9 h-5 bg-<?php echo ($user['etat_compte'] == 1 ? 'green' : 'red'); ?>-500 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:translate-x-[-100%] peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500 peer-default:bg-red-500">
                                </div>
                                <span id="status-text-<?php echo $user['user_id']; ?>" class="ms-3 text-sm font-medium text-gray-900">
                                    <?php echo ($user['etat_compte'] == 1 ? 'Activé' : 'Désactivé'); ?>
                                </span>
                            </label>
                        </div>
                    </td>
                    <td class="td-class text-center">
                        <button
                            class="modify-user-btn cursor-pointer flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900"
                            data-user-id="<?php echo $user['user_id']; ?>"
                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                            data-nom="<?php echo htmlspecialchars($user['nom']); ?>"
                            data-prenom="<?php echo htmlspecialchars($user['prenom']); ?>"
                            data-email="<?php echo htmlspecialchars($user['email']); ?>"
                            data-role-id="<?php echo htmlspecialchars($user['role_id']); ?>">
                            Modifier
                        </button>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>