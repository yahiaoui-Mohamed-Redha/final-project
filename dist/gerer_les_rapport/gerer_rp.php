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
$cache_file = '../../cache/rapports_cache.json'; // Cache file location
$cache_time = 432000; // 5 days in seconds

// Check if a valid cache file exists
if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
    // Load data from cache
    $rapports = json_decode(file_get_contents($cache_file), true);
} else {
    // Fetch all reports from the database
    $stmt = $conn->prepare("SELECT r.rap_name, r.rap_date, r.description, u.nom, u.prenom 
                            FROM Rapport r 
                            INNER JOIN Users u ON r.user_id = u.user_id");
    $stmt->execute();
    $rapports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Store the data in cache
    file_put_contents($cache_file, json_encode($rapports));
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

?>
<div class="w-full bg-white flex items-center justify-between py-2 px-4 rounded-md mb-2">
    <div class="p-2">
        <ul class="flex gap-4 bg-[#f8f8f8] rounded-md p-1 w-max overflow-hidden relative">
            <!-- Tabs -->
            <li>
                <button id="allTab" class="tab text-[#0455b7] bg-white rounded-lg font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer">
                    Tous les rapport
                </button>
            </li>
            <li>
                <button id="nouveauTab" class="tab text-gray-600 rounded-xl font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer">
                    Nouveau
                </button>
            </li>
            <li>
                <button id="enCoursTab" class="tab text-gray-600 rounded-xl font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer">
                    En Cours
                </button>
            </li>
            <li>
                <button id="resoluTab" class="tab text-gray-600 rounded-xl font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer">
                    Résolu
                </button>
            </li>
            <li>
                <button id="fermeTab" class="tab text-gray-600 rounded-xl font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer">
                    Fermé
                </button>
            </li>
        </ul>
    </div>
    <div class="flex items-center justify-between">


        <a id="new" href="gerer_les_panne/signaler_des_panne.php" class="load-page-link flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                <path d="M12 5l0 14"></path>
                <path d="M5 12l14 0"></path>
            </svg>
            <span class="mx-2 text-sm font-medium">créer rapports</span>
        </a>
    </div>
</div>

    <div class="w-full bg-white flex flex-col items-center py-2 px-4 rounded-md">
    <!-- Filter and search -->
    <div class="w-full py-2 px-4 flex justify-between items-center">
        <div class="flex space-x-2">
            <button class="flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm w-64" placeholder="Search panne...">
            </div>
        </div>
        <button class="flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 transition-colors duration-300 transform hover:bg-[#c8d3f659] hover:text-[#0455b7]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                <path d="M9 15h6"></path>
                <path d="M12.5 17.5l2.5 -2.5l-2.5 -2.5"></path>
            </svg>
            <span class="mx-2 text-sm font-medium">Exporter</span>
        </button>
    </div>
    <!-- /Filter and search -->

    <div class="w-full">
        <table class="w-full divide-y divide-gray-200">
            <tr class="tr-head">
                <th scope="col" class="pl-4 w-[3%]">
                    <input type="checkbox" name="select-rapport" id="select-all">
                </th>
                <th scope="col" class="th-class">اسم التقرير</th>
                <th scope="col" class="th-class">تاريخ التقرير</th>
                <th scope="col" class="th-class">وصف التقرير</th>
                <th scope="col" class="th-class">المستلم</th>
                <th scope="col" class="th-class">Actions</th>

            </tr>
            <?php foreach ($rapports as $rapport): ?>
                <tr class="tr-body">
                    <td class="pl-4">
                        <input type="checkbox" name="select-rapport" id="select-rapport-<?php echo $rapport['rap_name'] ?? ''; ?>">
                    </td>
                    <td class="td-class"><?php echo htmlspecialchars($rapport['rap_name']); ?></td>
                    <td class="td-class"><?php echo $rapport['rap_date']; ?></td>
                    <td class="td-class"><?php echo htmlspecialchars($rapport['description']); ?></td>
                    <td class="td-class"><?php echo htmlspecialchars($rapport['nom'] . ' ' . $rapport['prenom']); ?></td>
                    
                    <!-- <td class="p-3 relative group etat-cell">
                        <div class="cursor-pointer" ondblclick="showEtatList(this)">
                            <span class="date-text"><?php echo htmlspecialchars($rapport['rap_date'] ?? ''); ?></span>
                        </div>
                        
                    </td> -->
                    <td class="px-6 py-4 w-[3%] whitespace-nowrap text-sm font-medium">
                        <button id="dropdownMenuIconButton" data-dropdown-toggle="dropdownDots" class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-900 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-50" type="button">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 4 15">
                                <path d="M3.5 1.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6.041a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.959a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                            </svg>
                        </button>
                        <!-- Dropdown menu -->
                        <div id="dropdownDots" class="absolute z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44">
                            <div class="py-2">
                                <a href="edit_panne.php" class="text-indigo-600 hover:text-indigo-900">تعديل</a>
                            </div>
                            <div class="py-2">
                                <a href="delete_panne.php" class="text-red-600 hover:text-red-900 ml-2">حذف</a>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
    

