<?php
include '../../app/config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}

// Fetch all pannes
$stmt_panne = $conn->prepare("SELECT p.panne_num, p.panne_name, p.date_signalement, e.etablissement_name, t.type_name, p.panne_etat 
                              FROM Panne p 
                              INNER JOIN Type_panne t ON p.type_id = t.type_id 
                              INNER JOIN Users u ON p.receveur_id = u.user_id 
                              INNER JOIN Epost e ON u.postal_code = e.postal_code");
$stmt_panne->execute();
$pannes = $stmt_panne->fetchAll(PDO::FETCH_ASSOC);

// Filter pannes for receveur
if ($_SESSION['user_role'] == 'Receveur') {
    $stmt_panne_receveur = $conn->prepare("SELECT p.panne_name, p.date_signalement, p.description, t.type_name, u.nom, u.prenom 
                                           FROM Panne p 
                                           INNER JOIN Type_panne t ON p.type_id = t.type_id 
                                           INNER JOIN Users u ON p.receveur_id = u.user_id 
                                           WHERE p.receveur_id = :receveur_id");
    $stmt_panne_receveur->execute(['receveur_id' => $_SESSION['user_id']]);
    $pannes = $stmt_panne_receveur->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch admin details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$admin = $select->fetch(PDO::FETCH_ASSOC);

// Determine the current content page
$contentPage = basename($_SERVER['PHP_SELF']);

?>

    <div class="w-full bg-white flex items-center justify-between py-2 px-4 rounded-md mb-2">
        <div class="p-2">
            <ul class="flex gap-4 bg-[#f8f8f8] rounded-md p-1 w-max overflow-hidden relative">
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
        <div class="flex items-center justify-between">

            <button id="new" onclick="window.location.href='create_panne.php'" class="flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform mr-2 hover:bg-blue-900">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                    <path d="M5.931 6.936l1.275 4.249m5.607 5.609l4.251 1.275"></path>
                    <path d="M11.683 12.317l5.759 -5.759"></path>
                    <path d="M5.5 5.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0"></path>
                    <path d="M18.5 5.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0"></path>
                    <path d="M18.5 18.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0"></path>
                    <path d="M8.5 15.5m-4.5 0a4.5 4.5 0 1 0 9 0a4.5 4.5 0 1 0 -9 0"></path>
                </svg>
                <span class="mx-2 text-sm font-medium">Gérer les type</span>
            </button>

            <a id="new" href="gerer_les_panne/signaler_des_panne.php" class="load-page-link flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                    <path d="M12 5l0 14"></path>
                    <path d="M5 12l14 0"></path>
                </svg>
                <span class="mx-2 text-sm font-medium">Signaler des panne</span>
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
                        <input type="checkbox" name="select-panne" id="select-all">
                    </th>
                    <th scope="col" class="th-class">Panne Num</th>
                    <th scope="col" class="th-class">Nom de Panne</th>
                    <th scope="col" class="th-class">Date</th>
                    <th scope="col" class="th-class">Établissement</th>
                    <th scope="col" class="th-class">Type</th>
                    <th scope="col" class="th-class">État</th>
                    <th scope="col" class="th-class">Actions</th>
                </tr>
                <?php foreach ($pannes as $panne): ?>
                    <tr class="tr-body">
                        <td class="pl-4">
                            <input type="checkbox" name="select-panne" id="select-panne-<?php echo $panne['panne_num']; ?>">
                        </td>
                        <td class="td-class"><?php echo htmlspecialchars($panne['panne_num']); ?></td>
                        <td class="td-class"><?php echo htmlspecialchars($panne['panne_name']); ?></td>
                        <td class="td-class"><?php echo $panne['date_signalement']; ?></td>
                        <td class="td-class"><?php echo htmlspecialchars($panne['etablissement_name']); ?></td>
                        <td class="td-class"><?php echo htmlspecialchars($panne['type_name']); ?></td>
                        <td class="p-3 relative group etat-cell">
                            <div class="cursor-pointer" ondblclick="showEtatList(this)">
                                <span class="etat-text"><?php echo htmlspecialchars($panne['panne_etat']); ?></span>
                            </div>
                            <div class="etat-list absolute z-50 bg-white border border-gray-200 shadow-lg p-2 hidden top-0 left-0 transform translate-y-8">
                                <select class="w-full p-1 border rounded">
                                    <option value="nouveau">Nouveau</option>
                                    <option value="en cours">En Cours</option>
                                    <option value="résolu">Résolu</option>
                                    <option value="fermé">Fermé</option>
                                </select>
                                <div class="flex justify-end space-x-2 mt-2">
                                    <button class="save-etat px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Save</button>
                                    <button class="cancel-etat px-2 py-1 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</button>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="edit_panne.php?panne_id=<?php echo $panne['panne_num']; ?>" class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                            <a href="delete_panne.php?panne_id=<?php echo $panne['panne_num']; ?>" class="text-red-600 hover:text-red-900 ml-2">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>