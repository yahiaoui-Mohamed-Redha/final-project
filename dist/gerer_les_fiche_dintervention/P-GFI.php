<?php
include '../../app/config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}

// Fetch all fiches with related data
$query = "SELECT 
            fi.fiche_num, 
            fi.fiche_date, 
            fi.compte_rendu, 
            fi.observation,
            fi.archived,
            p.panne_name, 
            p.date_signalement, 
            COALESCE(e.etablissement_name, 'UNITE-POSTAL-WILAYA-DE-BOUMERDES') AS etablissement_name, 
            t.type_name, 
            p.panne_etat, 
            tech.nom AS tech_nom, 
            tech.prenom AS tech_prenom,
            rec.nom AS rec_nom, 
            rec.prenom AS rec_prenom 
          FROM FicheIntervention fi
          INNER JOIN Panne p ON fi.panne_num = p.panne_num
          INNER JOIN Type_panne t ON p.type_id = t.type_id 
          INNER JOIN Users tech ON fi.technicien_id = tech.user_id
          INNER JOIN Users rec ON fi.receveur_id = rec.user_id
          LEFT JOIN Epost e ON rec.postal_code = e.postal_code
          ORDER BY fi.fiche_date DESC";

// Filter fiches for receveur
if ($_SESSION['user_role'] == 'Receveur') {
    $query = "SELECT 
                fi.fiche_num, 
                fi.fiche_date, 
                fi.compte_rendu, 
                fi.observation,
                fi.archived,
                p.panne_name, 
                p.date_signalement, 
                e.etablissement_name,
                t.type_name, 
                p.panne_etat, 
                tech.nom AS tech_nom, 
                tech.prenom AS tech_prenom,
                rec.nom AS rec_nom, 
                rec.prenom AS rec_prenom 
              FROM FicheIntervention fi
              INNER JOIN Panne p ON fi.panne_num = p.panne_num
              INNER JOIN Type_panne t ON p.type_id = t.type_id 
              INNER JOIN Users tech ON fi.technicien_id = tech.user_id
              INNER JOIN Users rec ON fi.receveur_id = rec.user_id
              LEFT JOIN Epost e ON rec.postal_code = e.postal_code
              WHERE fi.receveur_id = :receveur_id
              ORDER BY fi.fiche_date DESC";
}

// Filter fiches for technicien
if ($_SESSION['user_role'] == 'Technicien') {
    $query = "SELECT 
                fi.fiche_num, 
                fi.fiche_date, 
                fi.compte_rendu, 
                fi.observation,
                fi.archived,
                p.panne_name, 
                p.date_signalement, 
                e.etablissement_name,
                t.type_name, 
                p.panne_etat, 
                tech.nom AS tech_nom, 
                tech.prenom AS tech_prenom,
                rec.nom AS rec_nom, 
                rec.prenom AS rec_prenom 
              FROM FicheIntervention fi
              INNER JOIN Panne p ON fi.panne_num = p.panne_num
              INNER JOIN Type_panne t ON p.type_id = t.type_id 
              INNER JOIN Users tech ON fi.technicien_id = tech.user_id
              INNER JOIN Users rec ON fi.receveur_id = rec.user_id
              LEFT JOIN Epost e ON rec.postal_code = e.postal_code
              WHERE fi.technicien_id = :technicien_id
              ORDER BY fi.fiche_date DESC";
}

// Prepare and execute the query
$stmt_fiche = $conn->prepare($query);

if ($_SESSION['user_role'] == 'Receveur') {
    $stmt_fiche->execute(['receveur_id' => $_SESSION['user_id']]);
} elseif ($_SESSION['user_role'] == 'Technicien') {
    $stmt_fiche->execute(['technicien_id' => $_SESSION['user_id']]);
} else {
    $stmt_fiche->execute();
}

$fiches = $stmt_fiche->fetchAll(PDO::FETCH_ASSOC);

// Fetch details
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
                    Toutes les fiches
                </button>
            </li>
            <li>
                <button id="archivedTab" class="tab text-gray-600 rounded-xl font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer">
                    Archivées
                </button>
            </li>
            <li>
                <button id="activeTab" class="tab text-gray-600 rounded-xl font-semibold text-center text-sm py-2 px-4 tracking-wide cursor-pointer">
                    Actives
                </button>
            </li>
        </ul>
    </div>
    <div class="flex items-center justify-between">
        <?php if (in_array($_SESSION['user_role'], ['Technicien', 'Receveur'])): ?>
            <a id="new" href="gerer_les_fiche_dintervention/creer_fiche.php" class="load-page-link flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                    <path d="M12 5l0 14"></path>
                    <path d="M5 12l14 0"></path>
                </svg>
                <span class="mx-2 text-sm font-medium">Créer une fiche</span>
            </a>
        <?php endif; ?>
    </div>
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
                <input type="text" id="search-input" class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm w-64" placeholder="Rechercher une fiche...">
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
                <a href="../app/export_fiches_table.php?format=pdf" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Exporter en PDF
                    </div>
                </a>
                <a href="../app/export_fiches_table.php?format=excel" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
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
                    <input type="checkbox" name="select-fiche" id="select-all">
                </th>
                <th scope="col" class="th-class">Fiche Num</th>
                <th scope="col" class="th-class">Panne associée</th>
                <th scope="col" class="th-class">Date</th>
                <th scope="col" class="th-class">Établissement</th>
                <th scope="col" class="th-class">Type</th>
                <th scope="col" class="th-class">Technicien</th>
                <th scope="col" class="th-class">Statut</th>
                <th scope="col" class="th-class">Actions</th>
            </tr>
            <?php foreach ($fiches as $fiche): ?>
                <tr class="tr-body"
                    data-fiche-num="<?php echo htmlspecialchars($fiche['fiche_num'] ?? ''); ?>" 
                    data-fiche-date="<?php echo htmlspecialchars($fiche['fiche_date'] ?? ''); ?>" 
                    data-compte-rendu="<?php echo htmlspecialchars($fiche['compte_rendu'] ?? ''); ?>" 
                    data-observation="<?php echo htmlspecialchars($fiche['observation'] ?? ''); ?>" 
                    data-panne-name="<?php echo htmlspecialchars($fiche['panne_name'] ?? ''); ?>" 
                    data-date-signalement="<?php echo htmlspecialchars($fiche['date_signalement'] ?? ''); ?>" 
                    data-etablissement-name="<?php echo htmlspecialchars($fiche['etablissement_name'] ?? ''); ?>" 
                    data-type-name="<?php echo htmlspecialchars($fiche['type_name'] ?? ''); ?>" 
                    data-panne-etat="<?php echo htmlspecialchars($fiche['panne_etat'] ?? ''); ?>" 
                    data-tech-nom="<?php echo htmlspecialchars($fiche['tech_nom'] ?? ''); ?>" 
                    data-tech-prenom="<?php echo htmlspecialchars($fiche['tech_prenom'] ?? ''); ?>"
                    data-rec-nom="<?php echo htmlspecialchars($fiche['rec_nom'] ?? ''); ?>" 
                    data-rec-prenom="<?php echo htmlspecialchars($fiche['rec_prenom'] ?? ''); ?>">

                    <td class="pl-4">
                        <input type="checkbox" name="select-fiche" id="select-fiche-<?php echo $fiche['fiche_num'] ?? ''; ?>">
                    </td>
                    <td class="td-class"><?php echo htmlspecialchars($fiche['fiche_num'] ?? ''); ?></td>
                    <td class="td-class"><?php echo htmlspecialchars($fiche['panne_name'] ?? ''); ?></td>
                    <td class="td-class"><?php echo $fiche['fiche_date'] ?? ''; ?></td>
                    <td class="td-class w-[22%]"><?php echo htmlspecialchars($fiche['etablissement_name'] ?? ''); ?></td>
                    <td class="td-class"><?php echo htmlspecialchars($fiche['type_name'] ?? ''); ?></td>
                    <td class="td-class"><?php echo htmlspecialchars($fiche['tech_nom'] ?? '') . ' ' . htmlspecialchars($fiche['tech_prenom'] ?? ''); ?></td>
                    
                    <!-- hidden -->
                    <td class="td-class hidden"><?php echo htmlspecialchars($fiche['compte_rendu'] ?? ''); ?></td>
                    <td class="td-class hidden"><?php echo htmlspecialchars($fiche['observation'] ?? ''); ?></td>
                    <td class="td-class hidden"><?php echo htmlspecialchars($fiche['date_signalement'] ?? ''); ?></td>
                    <td class="td-class hidden"><?php echo htmlspecialchars($fiche['rec_nom'] ?? ''); ?></td>
                    <td class="td-class hidden"><?php echo htmlspecialchars($fiche['rec_prenom'] ?? ''); ?></td>
                    <!-- /hidden -->
                    
                    <td class="p-3 relative group etat-cell">
                        <div class="cursor-pointer" ondblclick="showArchiveStatus(this)">
                            <span class="archive-status"><?php echo ($fiche['archived'] == 1) ? 'Archivée' : 'Active'; ?></span>
                        </div>
                        <div class="archive-list absolute z-50 bg-white border border-gray-200 shadow-lg p-2 hidden transform">
                            <select class="w-full p-1 border rounded">
                                <option value="0">Active</option>
                                <option value="1">Archivée</option>
                            </select>
                            <div class="flex justify-end space-x-2 mt-2">
                                <button class="save-archive px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Enregistrer</button>
                                <button class="cancel-archive px-2 py-1 bg-gray-500 text-white rounded hover:bg-gray-600">Annuler</button>
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
                                <a href="../app/view_fiche.php?fiche_num=<?php echo $fiche['fiche_num']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Voir</a>
                            </div>
                            <div class="py-2">
                                <a href="gerer_les_fiche_dintervention/fiche_edit.php?fiche_num=<?php echo $fiche['fiche_num']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Modifier</a>
                            </div>
                            <div class="py-2">
                                <a href="../app/delete_fiche.php?fiche_num=<?php echo $fiche['fiche_num']; ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette fiche ? Cette action est irréversible');">Supprimer</a>
                            </div>
                            <div class="py-2">
                                <a href="../app/fiche_export.php?fiche_num=<?php echo $fiche['fiche_num']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Exporter la fiche</a>
                            </div>
                        </div>
                        <!-- /Dropdown menu -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>