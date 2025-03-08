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
        <button class="flex items-center p-1.5 border rounded-lg text-gray-600 border-gray-200 transition-colors duration-300 transform mr-2 hover:bg-[#c8d3f659] hover:text-[#0455b7]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                <path d="M9 15h6"></path>
                <path d="M12.5 17.5l2.5 -2.5l-2.5 -2.5"></path>
            </svg>
            <span class="mx-2 text-sm font-medium">Exporter</span>
        </button>
        <button id="new" onclick="window.location.href='gerer_les_comptes/create_users.php'" class="flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                <path d="M12 5l0 14"></path>
                <path d="M5 12l14 0"></path>
            </svg>
            <span class="mx-2 text-sm font-medium">Créer un compte</span>
        </button>
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
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" class="pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm w-64" placeholder="Search user...">
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
                <th scope="col" class="th-class whitespace-nowrap w-[12%]">Etat Compte</th>
                <th scope="col" class="th-class w-[13%]">Actions</th>
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
                <td class="td-class">
                    <?php if ($user['etat_compte'] == 1) { ?>
                        <span class="px-4 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Active
                        </span>
                    <?php } else { ?>
                        <span class="px-4 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Désactivé
                        </span>
                    <?php } ?>
                </td>
                <td class="td-class">
                    <?php if ($user['etat_compte'] == 1) { ?>
                        <a class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-[#0455b7] rounded-lg" 
                        href="gerer_les_comptes/disable_user.php?id=<?php echo htmlspecialchars($user['user_id']); ?>">
                            Disable
                        </a>
                    <?php } else { ?>                        
                        <a class="bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg text-[#0455b7]" 
                        href="gerer_les_comptes/enable_user.php?id=<?php echo htmlspecialchars($user['user_id']); ?>">
                            Enable
                        </a>                                    
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

</div>
