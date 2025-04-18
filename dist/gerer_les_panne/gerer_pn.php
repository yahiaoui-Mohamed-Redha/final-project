<?php
include '../../app/config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}
// Store the current user's role for later use
$current_user_role = $_SESSION['user_role'];

// Fetch all pannes with rapport data
$query = "SELECT 
            p.panne_num, 
            p.panne_name, 
            p.date_signalement, 
            COALESCE(e.etablissement_name, 'UNITE-POSTAL-WILAYA-DE-BOUMERDES') AS etablissement_name, 
            t.type_name, 
            p.panne_etat, 
            r.rap_num, 
            r.rap_name, 
            r.rap_date, 
            u.nom AS user_nom, 
            u.prenom AS user_prenom 
          FROM Panne p 
          INNER JOIN Type_panne t ON p.type_id = t.type_id 
          INNER JOIN Users u ON p.receveur_id = u.user_id 
          LEFT JOIN Epost e ON u.postal_code = e.postal_code
          LEFT JOIN Rapport r ON p.rap_num = r.rap_num
          WHERE p.archived = 0 OR p.archived IS NULL
          ORDER BY p.date_signalement DESC";

// Filter pannes for receveur
if ($_SESSION['user_role'] == 'Receveur') {
    $query = "SELECT 
                p.panne_num, 
                p.panne_name, 
                p.date_signalement, 
                e.etablissement_name,
                t.type_name, 
                p.panne_etat, 
                r.rap_num, 
                r.rap_name, 
                r.rap_date, 
                u.nom AS user_nom, 
                u.prenom AS user_prenom 
                FROM Panne p 
                INNER JOIN Type_panne t ON p.type_id = t.type_id 
                INNER JOIN Users u ON p.receveur_id = u.user_id 
                LEFT JOIN Epost e ON u.postal_code = e.postal_code
                LEFT JOIN Rapport r ON p.rap_num = r.rap_num
              WHERE p.receveur_id = :receveur_id AND p.archived = 0 OR p.archived IS NULL
              ORDER BY p.date_signalement DESC"; // Order by date
}

if (isset($_GET['rap_num'])) {
    $rap_num = $_GET['rap_num'];

    // Fetch the specific rapport data from the database
    $query = "SELECT 
                p.panne_num, 
                p.panne_name, 
                p.date_signalement, 
                COALESCE(e.etablissement_name, 'UNITE-POSTAL-WILAYA-DE-BOUMERDES') AS etablissement_name, 
                t.type_name, 
                p.panne_etat, 
                r.rap_num, 
                r.rap_name, 
                r.rap_date, 
                u.nom AS user_nom, 
                u.prenom AS user_prenom 
              FROM Panne p 
              INNER JOIN Type_panne t ON p.type_id = t.type_id 
              INNER JOIN Users u ON p.receveur_id = u.user_id 
              LEFT JOIN Epost e ON u.postal_code = e.postal_code
              LEFT JOIN Rapport r ON p.rap_num = r.rap_num
              WHERE r.rap_num = :rap_num AND p.archived = 0 OR p.archived IS NULL";

    try {
        $stmt = $conn->prepare($query);
        $stmt->execute(['rap_num' => $rap_num]);
        $rapport = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rapport) {
            // Return the data as JSON
            header('Content-Type: application/json');
            echo json_encode($rapport);
        } else {
            // No data found
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No data found for the given rap_num']);
        }
    } catch (PDOException $e) {
        // Database error
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Prepare and execute the query
$stmt_panne = $conn->prepare($query);

if ($_SESSION['user_role'] == 'Receveur') {
    $stmt_panne->execute(['receveur_id' => $_SESSION['user_id']]);
} else {
    $stmt_panne->execute();
}

$pannes = $stmt_panne->fetchAll(PDO::FETCH_ASSOC);

// Debugging: Log the fetched pannes
error_log(print_r($pannes, true));
error_log("Fetched pannes: " . print_r($pannes, true));

// Fetch admin details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$admin = $select->fetch(PDO::FETCH_ASSOC);

// Determine the current content page
$contentPage = basename($_SERVER['PHP_SELF']);

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

?>

        
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

<div class="w-full bg-white flex items-center justify-between py-2 px-4 rounded-md mb-2">
    <div class="p-2">
        <ul class="flex gap-4 bg-[#f6f6f6] rounded-md p-1 w-max overflow-hidden relative">
            <!-- Tabs -->
            <li>
                <button id="allTab" class="tab text-[#0455b7] bg-white rounded-lg font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer">
                    Tous les pannes
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
    <?php if (!in_array($current_user_role, ['Technicien'])): ?>
    <div class="flex items-center justify-between">

        <?php if ($current_user_role == 'Receveur'): ?>
            <a id="new" href="gerer_les_types_des_panne/propos_type.php" class="load-page-link flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform mr-2 hover:bg-blue-900">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                    <path d="M5.931 6.936l1.275 4.249m5.607 5.609l4.251 1.275"></path>
                    <path d="M11.683 12.317l5.759 -5.759"></path>
                    <path d="M5.5 5.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0"></path>
                    <path d="M18.5 5.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0"></path>
                    <path d="M18.5 18.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0"></path>
                    <path d="M8.5 15.5m-4.5 0a4.5 4.5 0 1 0 9 0a4.5 4.5 0 1 0 -9 0"></path>
                </svg>
                <span class="mx-2 text-sm font-medium">Proposer un type</span>
            </a>
        <?php elseif ($current_user_role == 'Admin'): ?>
            <a id="new" href="gerer_les_types_des_panne/manage_type_panne.php" class="load-page-link flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform mr-2 hover:bg-blue-900">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                    <path d="M5.931 6.936l1.275 4.249m5.607 5.609l4.251 1.275"></path>
                    <path d="M11.683 12.317l5.759 -5.759"></path>
                    <path d="M5.5 5.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0"></path>
                    <path d="M18.5 5.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0"></path>
                    <path d="M18.5 18.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0"></path>
                    <path d="M8.5 15.5m-4.5 0a4.5 4.5 0 1 0 9 0a4.5 4.5 0 1 0 -9 0"></path>
                </svg>
                <span class="mx-2 text-sm font-medium">Gérer les types</span>
            </a>
        <?php endif; ?>
        <a id="new" href="gerer_les_panne/signaler_des_panne.php" class="load-page-link flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                <path d="M12 5l0 14"></path>
                <path d="M5 12l14 0"></path>
            </svg>
            <span class="mx-2 text-sm font-medium">Signaler des panne</span>
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="w-full bg-white flex flex-col items-center py-2 px-4 rounded-md">
    <!-- Filter and search -->
    <div class="w-full py-3 px-2 flex justify-between items-center">
        <div class="flex items-center justify-center space-x-2">
            <button class="flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
            <div class="relative search-bar">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="search-input" class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm w-64" placeholder="Rechercher panne...">
                <button type="submit" id="search-button" class="text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900 font-medium rounded-lg text-sm px-6 py-2 text-center  cursor-pointer">Rechercher</button>
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
                <a href="../app/export_pn_table.php?format=pdf" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Exporter en PDF
                    </div>
                </a>
                <a href="../app/export_pn_table.php?format=excel" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Exporter en Excel
                    </div>
                </a>
            </div>
            <button class="flex items-center border p-1 border-gray-300 rounded-md text-xs text-gray-600 transition-colors duration-300 transform hover:bg-black hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5"> 
                    <path d="M3 9l4 -4l4 4m-4 -4v14"></path> 
                    <path d="M21 15l-4 4l-4 -4m4 4v-14"></path> 
                </svg> 
            </button>
        </div>
    </div>
    <!-- /Filter and search -->

    <div class="w-full">
        <table class="w-full divide-y divide-gray-200">
            <tr class="tr-head">
                <th scope="col" class="pl-4 w-[3%]">
                    <input type="checkbox" name="select-panne" id="select-all">
                </th>
                <th scope="col" class="th-class">Panne Num</th>
                <th scope="col" class="th-class">Rap Num</th>
                <th scope="col" class="th-class">Nom de Panne</th>
                <th scope="col" class="th-class">Date</th>
                <th scope="col" class="th-class">Établissement</th>
                <th scope="col" class="th-class">Type</th>
                <th scope="col" class="th-class">État</th>
                <th scope="col" class="th-class">Actions</th>
            </tr>
            <?php foreach ($pannes as $panne): ?>
                <tr class="tr-body"
                data-panne-num="<?php echo htmlspecialchars($panne['panne_num'] ?? ''); ?>" 
                        data-panne-name="<?php echo htmlspecialchars($panne['panne_name'] ?? ''); ?>" 
                        data-date-signalement="<?php echo htmlspecialchars($panne['date_signalement'] ?? ''); ?>" 
                        data-etablissement-name="<?php echo htmlspecialchars($panne['etablissement_name'] ?? ''); ?>" 
                        data-type-name="<?php echo htmlspecialchars($panne['type_name'] ?? ''); ?>" 
                        data-panne-etat="<?php echo htmlspecialchars($panne['panne_etat'] ?? ''); ?>" 
                        data-rap-num="<?php echo htmlspecialchars($panne['rap_num'] ?? ''); ?>" 
                        data-rap-date="<?php echo htmlspecialchars($panne['rap_date'] ?? ''); ?>" 
                        data-user-nom="<?php echo htmlspecialchars($panne['user_nom'] ?? ''); ?>" 
                        data-user-prenom="<?php echo htmlspecialchars($panne['user_prenom'] ?? ''); ?>">

                    <td class="pl-4">
                        <input type="checkbox" name="select-panne" id="select-panne-<?php echo $panne['panne_num'] ?? ''; ?>">
                    </td>
                    <a id="open-rp" class="open-rp" href="dqdsds">
                        <td class="td-class"><?php echo htmlspecialchars($panne['panne_num'] ?? ''); ?></td>
                        <td class="td-class cursor-pointer hover:text-blue-500 hover:underline">
                            <?php echo htmlspecialchars($panne['rap_num'] ?? ''); ?>
                        </td>

                        <!-- Define the popover content element -->
                        <div id="popover-top" class="absolute z-10 invisible inline-block w-64 text-sm text-gray-500 transition-opacity duration-300 bg-white border border-gray-200 rounded-lg shadow-xs opacity-0 dark:text-gray-400 dark:border-gray-600 dark:bg-gray-800">
                            <div class="px-3 py-2">
                                <p>And here's some amazing content. It's very engaging. Right?</p>
                            </div>
                            <div data-popper-arrow></div>
                        </div>
                        <td class="td-class"><?php echo htmlspecialchars($panne['panne_name'] ?? ''); ?></td>
                        <td class="td-class"><?php echo $panne['date_signalement'] ?? ''; ?></td>
                        <td class="td-class w-[22%]"><?php echo htmlspecialchars($panne['etablissement_name'] ?? ''); ?></td>
                        <td class="td-class"><?php echo htmlspecialchars($panne['type_name'] ?? ''); ?></td>
                    </a>
                    <!-- hidden -->
                    <td class="td-class hidden"><?php echo htmlspecialchars($panne['rap_date'] ?? ''); ?></td>
                    <td class="td-class hidden"><?php echo htmlspecialchars($panne['user_nom'] ?? ''); ?></td>
                    <td class="td-class hidden"><?php echo htmlspecialchars($panne['user_prenom'] ?? ''); ?></td>
                    <!-- /hidden -->
                    <td class="p-3 relative group etat-cell">
                        <div class="cursor-pointer" ondblclick="showEtatList(this)">
                            <span class="etat-text"><?php echo htmlspecialchars($panne['panne_etat'] ?? ''); ?></span>
                        </div>
                        <div class="etat-list absolute z-50 bg-white border border-gray-200 shadow-lg p-2 hidden transform">
                            <select class="w-full p-1 border rounded">
                                <option value="nouveau">جديد</option>
                                <option value="en cours">قيد المعالجة</option>
                                <option value="résolu">تم الحل</option>
                                <option value="fermé">مغلق</option>
                            </select>
                            <div class="flex justify-end space-x-2 mt-2">
                                <button class="save-etat px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">حفظ</button>
                                <button class="cancel-etat px-2 py-1 bg-gray-500 text-white rounded hover:bg-gray-600">إلغاء</button>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 w-[3%] whitespace-nowrap text-sm font-medium">
                            <button data-dropdown-toggle="dropdownDots" class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-900 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-50 cursor-pointer" type="button">
                                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 4 15">
                                    <path d="M3.5 1.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6.041a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.959a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                                </svg>
                            </button>
                            <!-- Dropdown menu for action -->
                            <div class="absolute z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44">
                                <div class="py-2">
                                    <a href="gerer_les_panne/view_pn.php?panne_num=<?php echo $panne['panne_num']; ?>" class="load-page-link block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">vue</a>
                                </div>
                                <?php if (!in_array($current_user_role, ['Receveur'])): ?>
                                <div class="py-2">
                                    <a href="gerer_les_panne/panne_edit.php?panne_num=<?php echo $panne['panne_num']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">modifier</a>
                                </div>
                                <?php endif; ?>
                                <?php if (!in_array($current_user_role, ['Receveur', 'Technicien'])): ?>
                                <div class="py-2">
                                <a href="../app/delete_pn.php?panne_num=<?php echo $panne['panne_num']; ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce panne ? Cette action est irréversible');">Supprimer</a>
                                </div>
                                <?php endif; ?>
                                <div class="py-2">
                                    <a href="../app/panne_export.php?panne_num=<?php echo $panne['panne_num']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Exporter la panne</a>
                                </div>
                            </div>
                            <!-- /Dropdown menu -->
                        </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>