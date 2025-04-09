<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit;
}

// Fetch order missions based on user role
if ($_SESSION['user_role'] == 'Admin') {
    // Admin can see all order missions
    $stmt = $conn->prepare("SELECT * FROM OrderMission");
    $stmt->execute();
    $order_missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($_SESSION['user_role'] == 'Technicien') {
    // Technicien can only see their assigned order missions
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM OrderMission WHERE technicien_id = ?");
    $stmt->execute([$user_id]);
    $order_missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Default case (optional)
    $order_missions = [];
}


// Fetch user details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$admin = $select->fetch(PDO::FETCH_ASSOC);

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

<section class="form-container">
    <div class="w-full bg-white flex items-center justify-between py-2 px-4 rounded-md mb-2">
    <div class="p-2">
        <ul class="flex gap-4 bg-[#f8f8f8] rounded-md p-1 w-max overflow-hidden relative">
            <!-- Tabs -->
            <li>
                <button id="allTab" class="tab text-[#0455b7] bg-white rounded-lg font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer">
                    Tous les order misson
                </button>
            </li>
            <li>
                <button id="nouveauTab" class="tab text-gray-600 rounded-xl font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer">
                    Nouveau
                </button>
            </li>
           
        </ul>
    </div>
    <div class="flex items-center justify-between">


    <a id="new" href="gerer_les_ordres_des_missions/order_mission_create.php" class="load-page-link flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
            <path d="M12 5l0 14"></path>
            <path d="M5 12l14 0"></path>
        </svg>
        <span class="mx-2 text-sm font-medium">create un order de misson</span>
    </a>
    </div>
</div>

    <div class="w-full bg-white flex flex-col items-center py-2 px-4 rounded-md">
    <!-- search bar-->
    <div class="w-full py-2 px-4 flex justify-between items-center">
        <div class="flex space-x-2">
            <div class="relative search-bar">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="search-input" class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm w-64" placeholder="Search order missions...">
                <button type="submit" id="search-button" class="text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900 font-medium rounded-lg text-sm px-6 py-2 text-center  cursor-pointer">Search</button>
            </div>
        </div>
        <div class="flex items-center justify-center space-x-2">
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
            <div id="export-dropdown" class="hidden absolute z-50 mt-20 w-48 bg-white rounded-md shadow-lg py-1">
                <a href="../app/export_ord_table.php?format=pdf" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Exporter en PDF
                    </div>
                </a>
                <a href="../app/export_ord_table.php?format=excel" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Exporter en Excel
                    </div>
                </a>
            </div>
            
        </div>
    </div>
    <!-- /  search bar -->
    <div class="w-full">
        <table  class="w-full divide-y divide-gray-200">
        <tr class="tr-head">
                <th scope="col" class="pl-4 w-[3%]">
                    <input type="checkbox" name="select-rapport" id="select-all">
                </th>
                
                <th cope="col" class="th-class" >Order Number</th>
                <th cope="col" class="th-class" >Direction</th>
                <th cope="col" class="th-class" >Destination</th>
                <th cope="col" class="th-class" >Motif</th>
                <th cope="col" class="th-class" >Moyen de transport</th>
                <th cope="col" class="th-class" >Date de depart</th>
                <th cope="col" class="th-class" >Date de retour</th>
                <th cope="col" class="th-class" >Technicien</th>
                <th cope="col" class="th-class" >Actions</th>
            </tr>
            <div id="order-missions-table">
            <?php if (!empty($order_missions)) : ?>
                <?php foreach ($order_missions as $order_mission) : ?>
                    <tr class="tr-body">
                    <td class="pl-4">
                        <input type="checkbox" name="select-order" id="select-order-<?php echo htmlspecialchars($order_mission['order_num'])?? '';  ?>">
                    </td>
                        <td class="td-class"><?php echo htmlspecialchars($order_mission['order_num']); ?></td>
                        <td class="td-class"><?php echo htmlspecialchars($order_mission['direction']); ?></td>
                        <td class="td-class"><?php echo htmlspecialchars($order_mission['destination']); ?></td>
                        <td class="td-class"><?php echo htmlspecialchars($order_mission['motif']); ?></td>
                        <td class="td-class"><?php echo htmlspecialchars($order_mission['moyen_tr']); ?></td>
                        <td class="td-class"><?php echo htmlspecialchars($order_mission['date_depart']); ?></td>
                        <td class="td-class"><?php echo htmlspecialchars($order_mission['date_retour']); ?></td>
                        <td class="td-class">
                            <?php
                            $technicien_id = $order_mission['technicien_id'];
                            $select = $conn->prepare("SELECT nom, prenom FROM Users WHERE user_id = ?");
                            $select->execute([$technicien_id]);
                            $technicien = $select->fetch(PDO::FETCH_ASSOC);
                            echo htmlspecialchars($technicien['nom'] . ' ' . $technicien['prenom']);
                            ?>
                        </td>

                        <td class="px-6 py-4 w-[3%] whitespace-nowrap text-sm font-medium">
                        <button id="dropdownMenuIconButton" data-dropdown-toggle="dropdownDots" class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-900 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-50 cursor-pointer" type="button">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 4 15">
                                <path d="M3.5 1.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6.041a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.959a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                            </svg>
                        </button>
                        <!-- Dropdown menu -->
                        <div id="dropdownDots" class="absolute z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44">
                            <div class="py-2">
                            <a href="../app/view_ord.php?order_num=<?php echo $order_mission['order_num']; ?>" class="load-page-link block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Vue</a>
                            </div>
                            <div class="py-2">
                            <a href="../appt/edit_ord.php?order_num=<?php echo $order_mission['order_num']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Modifier</a>
                            </div>
                            <div class="py-2">
                            <a href="../app/delete_ord.php?order_num=<?php echo $order_mission['order_num']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce order mission ? Cette action est irréversible');">Supprimer</a>
                            </div>
                            <div class="py-2">
                                    <a href="../app/order_export.php?order_num=<?php echo $order_mission['order_num']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export rapport</a>
                            </div>
                        </div>
                        <!-- /Dropdown menu -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="9" class="text-center">No order missions found.</td>
                </tr>
            <?php endif; ?>
        </div>
        </table>
    </div>
</div>
</section>
</body>
</html>