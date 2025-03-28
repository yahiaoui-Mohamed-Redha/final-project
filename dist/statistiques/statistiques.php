<?php
include '../../app/config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}


// Fetch user details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$user = $select->fetch(PDO::FETCH_ASSOC);

// Get timeframe from either cookie, GET parameter, or use default
if (isset($_GET['timeframe'])) {
    $timeframe = $_GET['timeframe'];
    // Set cookie to expire in 30 days
    setcookie('timeframe', $timeframe, time() + (30 * 24 * 60 * 60), '/');
} elseif (isset($_COOKIE['timeframe'])) {
    $timeframe = $_COOKIE['timeframe'];
} else {
    $timeframe = 'Last week'; // Default value
}

// Calculate dates based on selected timeframe
$currentDate = date('Y-m-d');
switch ($timeframe) {
    case 'Today':
        $startDate = date('Y-m-d');
        break;
    case 'Yesterday':
        $startDate = date('Y-m-d', strtotime('-1 day'));
        break;
    case 'Last week':
        $startDate = date('Y-m-d', strtotime('-7 days'));
        break;
    case 'Last month':
        $startDate = date('Y-m-d', strtotime('-1 month'));
        break;
    case 'Last quarter':
        $startDate = date('Y-m-d', strtotime('-3 months'));
        break;
    case 'Last year':
        $startDate = date('Y-m-d', strtotime('-1 year'));
        break;
    default:
        $startDate = date('Y-m-d', strtotime('-7 days'));
        break;
}


// Fetch pannes count by type
$query_pannes = "SELECT tp.type_id, tp.type_name, COUNT(p.panne_num) AS panne_count 
                 FROM Type_panne tp 
                 LEFT JOIN Panne p ON tp.type_id = p.type_id 
                 AND p.date_signalement BETWEEN :startDate AND :currentDate 
                 GROUP BY tp.type_id";
$stmt_pannes = $conn->prepare($query_pannes);
$stmt_pannes->execute(['startDate' => $startDate, 'currentDate' => $currentDate]);
$pannes_by_type = $stmt_pannes->fetchAll(PDO::FETCH_ASSOC);

// Fetch pannes over time for line chart
$query_pannes_over_time = "SELECT DATE(p.date_signalement) AS signalement_date, COUNT(p.panne_num) AS panne_count 
                           FROM Panne p 
                           WHERE p.date_signalement BETWEEN :startDate AND :currentDate 
                           GROUP BY DATE(p.date_signalement) 
                           ORDER BY signalement_date";
$stmt_pannes_over_time = $conn->prepare($query_pannes_over_time);
$stmt_pannes_over_time->execute(['startDate' => $startDate, 'currentDate' => $currentDate]);
$pannes_over_time = $stmt_pannes_over_time->fetchAll(PDO::FETCH_ASSOC);

// Fetch pannes by status
$query_pannes_status = "SELECT 
                        SUM(CASE WHEN panne_etat = 'nouveau' THEN 1 ELSE 0 END) as nouveau,
                        SUM(CASE WHEN panne_etat = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
                        SUM(CASE WHEN panne_etat = 'résolu' THEN 1 ELSE 0 END) as resolu,
                        SUM(CASE WHEN panne_etat = 'fermé' THEN 1 ELSE 0 END) as ferme,
                        COUNT(*) as total
                        FROM Panne 
                        WHERE date_signalement BETWEEN :startDate AND :currentDate";
$stmt_pannes_status = $conn->prepare($query_pannes_status);
$stmt_pannes_status->execute(['startDate' => $startDate, 'currentDate' => $currentDate]);
$pannes_status = $stmt_pannes_status->fetch(PDO::FETCH_ASSOC);

// Fetch top technicians by resolved pannes
$query_top_techs = "SELECT u.user_id, u.nom, u.prenom, COUNT(fi.fiche_num) as resolved_count
                    FROM FicheIntervention fi
                    JOIN Users u ON fi.technicien_id = u.user_id
                    WHERE fi.fiche_date BETWEEN :startDate AND :currentDate
                    GROUP BY u.user_id
                    ORDER BY resolved_count DESC
                    LIMIT 5";
$stmt_top_techs = $conn->prepare($query_top_techs);
$stmt_top_techs->execute(['startDate' => $startDate, 'currentDate' => $currentDate]);
$top_technicians = $stmt_top_techs->fetchAll(PDO::FETCH_ASSOC);

// Fetch pannes by establishment
$query_pannes_etab = "SELECT e.etablissement_name, COUNT(p.panne_num) as panne_count
                      FROM Panne p
                      JOIN Users u ON p.receveur_id = u.user_id
                      JOIN Epost e ON u.postal_code = e.postal_code
                      WHERE p.date_signalement BETWEEN :startDate AND :currentDate
                      GROUP BY e.etablissement_name
                      ORDER BY panne_count DESC
                      LIMIT 5";
$stmt_pannes_etab = $conn->prepare($query_pannes_etab);
$stmt_pannes_etab->execute(['startDate' => $startDate, 'currentDate' => $currentDate]);
$pannes_by_etab = $stmt_pannes_etab->fetchAll(PDO::FETCH_ASSOC);

// Fetch average resolution time
$query_avg_resolution = "SELECT AVG(DATEDIFF(fi.fiche_date, p.date_signalement)) as avg_days
                         FROM FicheIntervention fi
                         JOIN Panne p ON fi.panne_num = p.panne_num
                         WHERE fi.fiche_date BETWEEN :startDate AND :currentDate
                         AND p.panne_etat IN ('résolu', 'fermé')";
$stmt_avg_resolution = $conn->prepare($query_avg_resolution);
$stmt_avg_resolution->execute(['startDate' => $startDate, 'currentDate' => $currentDate]);
$avg_resolution = $stmt_avg_resolution->fetch(PDO::FETCH_ASSOC);

// Prepare data for charts
$labels_js = json_encode(array_column($pannes_by_type, 'type_name'));
$data_js = json_encode(array_column($pannes_by_type, 'panne_count'));
$line_labels_js = json_encode(array_column($pannes_over_time, 'signalement_date'));
$line_data_js = json_encode(array_column($pannes_over_time, 'panne_count'));
$status_data_js = json_encode([
    $pannes_status['nouveau'] ?? 0,
    $pannes_status['en_cours'] ?? 0,
    $pannes_status['resolu'] ?? 0,
    $pannes_status['ferme'] ?? 0
]);
$top_techs_labels = json_encode(array_map(function($tech) { return $tech['nom'] . ' ' . $tech['prenom']; }, $top_technicians));
$top_techs_data = json_encode(array_column($top_technicians, 'resolved_count'));
$pannes_etab_labels = json_encode(array_column($pannes_by_etab, 'etablissement_name'));
$pannes_etab_data = json_encode(array_column($pannes_by_etab, 'panne_count'));
?>

<div class="flex justify-between md:items-center">
    <div>
        <p class="block antialiased font-sans text-base leading-relaxed text-inherit font-semibold">
            Maintenance Statistics Dashboard
        </p>
        <p class="block antialiased font-sans text-sm leading-normal font-normal text-gray-600 md:w-full w-4/5">
            Comprehensive overview of maintenance activities and performance metrics.
        </p>
    </div>
    
    <!-- Timeframe selector -->
    <div class="shrink-0">
        <div class="relative mt-2">
            <button
                type="button"
                id="select-button"
                class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-labelledby="listbox-label">
                <span class="col-start-1 row-start-1 flex items-center gap-3 pr-6 mr-6">
                    <span id="selected-value" class="block truncate"><?= htmlspecialchars($timeframe) ?></span>
                </span>
                <svg
                    class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4"
                    viewBox="0 0 16 16"
                    fill="currentColor"
                    aria-hidden="true"
                    data-slot="icon">
                    <path
                        fill-rule="evenodd"
                        d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z"
                        clip-rule="evenodd" />
                </svg>
            </button>

            <ul
                id="select-options"
                class="absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm hidden"
                tabindex="-1"
                role="listbox"
                aria-labelledby="listbox-label">
                <li class="relative cursor-default select-none py-2 pl-3 pr-5 text-gray-900 hover:bg-[#c8d3f659] hover:text-[#1004b7]" role="option" data-value="Today">
                    <div class="flex items-center">
                        <span class="ml-3 block truncate font-normal">Today</span>
                    </div>
                </li>
                <li class="relative cursor-default select-none py-2 pl-3 pr-5 text-gray-900 hover:bg-[#c8d3f659] hover:text-[#1004b7]" role="option" data-value="Yesterday">
                    <div class="flex items-center">
                        <span class="ml-3 block truncate font-normal">Yesterday</span>
                    </div>
                </li>
                <li class="relative cursor-default select-none py-2 pl-3 pr-5 text-gray-900 hover:bg-[#c8d3f659] hover:text-[#1004b7]" role="option" data-value="Last week">
                    <div class="flex items-center">
                        <span class="ml-3 block truncate font-normal">Last week</span>
                    </div>
                </li>
                <li class="relative cursor-default select-none py-2 pl-3 pr-5 text-gray-900 hover:bg-[#c8d3f659] hover:text-[#1004b7]" role="option" data-value="Last month">
                    <div class="flex items-center">
                        <span class="ml-3 block truncate font-normal">Last month</span>
                    </div>
                </li>
                <li class="relative cursor-default select-none py-2 pl-3 pr-5 text-gray-900 hover:bg-[#c8d3f659] hover:text-[#1004b7]" role="option" data-value="Last quarter">
                    <div class="flex items-center">
                        <span class="ml-3 block truncate font-normal">Last quarter</span>
                    </div>
                </li>
                <li class="relative cursor-default select-none py-2 pl-3 pr-5 text-gray-900 hover:bg-[#c8d3f659] hover:text-[#1004b7]" role="option" data-value="Last year">
                    <div class="flex items-center">
                        <span class="ml-3 block truncate font-normal">Last year</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>

</div>

<!-- Summary Cards -->
<div class="mt-6 grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 items-center md:gap-2.5 gap-4">
    <!-- Total Pannes Card -->
    <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md shadow-sm border border-gray-200 !rounded-lg">
        <div class="p-6 p-4">
            <div class="flex justify-between items-center">
                <p class="block antialiased font-sans text-base font-light leading-relaxed text-inherit !font-medium !text-xs text-gray-600">
                    Total Pannes
                </p>
                <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <p class="block antialiased font-sans text-base font-light leading-relaxed text-blue-gray-900 mt-1 font-bold text-2xl">
                <?= $pannes_status['total'] ?? 0 ?>
            </p>
            <p class="block antialiased font-sans text-xs leading-normal font-normal text-gray-600 mt-1">
                <?= $timeframe ?>
            </p>
        </div>
    </div>

    <!-- Average Resolution Time Card -->
    <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md shadow-sm border border-gray-200 !rounded-lg">
        <div class="p-6 p-4">
            <div class="flex justify-between items-center">
                <p class="block antialiased font-sans text-base font-light leading-relaxed text-inherit !font-medium !text-xs text-gray-600">
                    Avg. Resolution Time
                </p>
                <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="block antialiased font-sans text-base font-light leading-relaxed text-blue-gray-900 mt-1 font-bold text-2xl">
                <?= round($avg_resolution['avg_days'] ?? 0, 1) ?> days
            </p>
            <p class="block antialiased font-sans text-xs leading-normal font-normal text-gray-600 mt-1">
                For resolved pannes
            </p>
        </div>
    </div>

    <!-- New Pannes Card -->
    <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md shadow-sm border border-gray-200 !rounded-lg">
        <div class="p-6 p-4">
            <div class="flex justify-between items-center">
                <p class="block antialiased font-sans text-base font-light leading-relaxed text-inherit !font-medium !text-xs text-gray-600">
                    New Pannes
                </p>
                <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <p class="block antialiased font-sans text-base font-light leading-relaxed text-blue-gray-900 mt-1 font-bold text-2xl">
                <?= $pannes_status['nouveau'] ?? 0 ?>
            </p>
            <p class="block antialiased font-sans text-xs leading-normal font-normal text-gray-600 mt-1">
                Waiting for action
            </p>
        </div>
    </div>

    <!-- Resolved Pannes Card -->
    <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md shadow-sm border border-gray-200 !rounded-lg">
        <div class="p-6 p-4">
            <div class="flex justify-between items-center">
                <p class="block antialiased font-sans text-base font-light leading-relaxed text-inherit !font-medium !text-xs text-gray-600">
                    Resolved Pannes
                </p>
                <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            <p class="block antialiased font-sans text-base font-light leading-relaxed text-blue-gray-900 mt-1 font-bold text-2xl">
                <?= ($pannes_status['resolu'] ?? 0) + ($pannes_status['ferme'] ?? 0) ?>
            </p>
            <p class="block antialiased font-sans text-xs leading-normal font-normal text-gray-600 mt-1">
                Completed successfully
            </p>
        </div>
    </div>
</div>

<!-- Main Charts Section -->
<div class="mt-5 grid lg:grid-cols-2 md:grid-cols-1 grid-cols-1 gap-5">
    <!-- Line Chart - Pannes Over Time -->
    <div class="h-[28rem] relative flex flex-col justify-center rounded-xl bg-white bg-clip-border text-gray-700 shadow-md">
        <div class="relative mx-4 mt-4 flex flex-col gap-4 overflow-hidden rounded-none bg-transparent bg-clip-border text-gray-700 shadow-none md:flex-row md:items-center">
            <div>
                <h6 class="block font-sans text-base font-semibold leading-relaxed tracking-normal text-blue-gray-900 antialiased">
                    Pannes Over Time
                </h6>
                <p class="block max-w-sm font-sans text-sm font-normal leading-normal text-gray-700 antialiased">
                    Trend of pannes reported during the selected period.
                </p>
            </div>
        </div>
        <div class="pt-6 px-2 pb-0">
            <div id="line-chart"></div>
        </div>
    </div>

    <!-- Donut Chart - Pannes by Type -->
    <div class="h-[28rem] bg-white rounded-lg shadow-sm p-4 md:p-6">
        <div class="flex justify-between mb-3">
            <div class="flex justify-center items-center">
                <h5 class="text-xl font-bold leading-none text-gray-900 pe-1">Pannes by Type</h5>
                <svg data-popover-target="chart-info" data-popover-placement="bottom" class="w-3.5 h-3.5 text-gray-500 hover:text-gray-900 cursor-pointer ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm0 16a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Zm1-5.034V12a1 1 0 0 1-2 0v-1.418a1 1 0 0 1 1.038-.999 1.436 1.436 0 0 0 1.488-1.441 1.501 1.501 0 1 0-3-.116.986.986 0 0 1-1.037.961 1 1 0 0 1-.96-1.037A3.5 3.5 0 1 1 11 11.466Z"/>
                </svg>
                <div data-popover id="chart-info" role="tooltip" class="absolute z-10 invisible inline-block text-sm text-gray-500 transition-opacity duration-300 bg-white border border-gray-200 rounded-lg shadow-xs opacity-0 w-72">
                    <div class="p-3 space-y-2">
                        <h3 class="font-semibold text-gray-900">Pannes Distribution</h3>
                        <p>This chart shows the distribution of pannes by type for the selected timeframe.</p>
                    </div>
                    <div data-popper-arrow></div>
                </div>
            </div>
        </div>
        <div class="py-6" id="donut-chart"></div>
    </div>
</div>

<!-- Additional Statistics Section -->
<div class="mt-5 grid lg:grid-cols-3 md:grid-cols-1 grid-cols-1 gap-5">
    <!-- Pannes Status Chart -->
    <div class="h-[28rem] bg-white rounded-lg shadow-sm p-4 md:p-6">
        <div class="flex justify-between mb-3">
            <div class="flex justify-center items-center">
                <h5 class="text-xl font-bold leading-none text-gray-900 pe-1">Pannes by Status</h5>
            </div>
        </div>
        <div class="py-6" id="status-chart"></div>
    </div>

    <!-- Top Technicians Chart -->
    <div class="h-[28rem] bg-white rounded-lg shadow-sm p-4 md:p-6">
        <div class="flex justify-between mb-3">
            <div class="flex justify-center items-center">
                <h5 class="text-xl font-bold leading-none text-gray-900 pe-1">Top Technicians</h5>
                <svg data-popover-target="tech-info" data-popover-placement="bottom" class="w-3.5 h-3.5 text-gray-500 hover:text-gray-900 cursor-pointer ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm0 16a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Zm1-5.034V12a1 1 0 0 1-2 0v-1.418a1 1 0 0 1 1.038-.999 1.436 1.436 0 0 0 1.488-1.441 1.501 1.501 0 1 0-3-.116.986.986 0 0 1-1.037.961 1 1 0 0 1-.96-1.037A3.5 3.5 0 1 1 11 11.466Z"/>
                </svg>
                <div data-popover id="tech-info" role="tooltip" class="absolute z-10 invisible inline-block text-sm text-gray-500 transition-opacity duration-300 bg-white border border-gray-200 rounded-lg shadow-xs opacity-0 w-72">
                    <div class="p-3 space-y-2">
                        <h3 class="font-semibold text-gray-900">Top Performers</h3>
                        <p>Technicians with the highest number of resolved pannes during the selected period.</p>
                    </div>
                    <div data-popper-arrow></div>
                </div>
            </div>
        </div>
        <div class="py-6" id="tech-chart"></div>
    </div>

    <!-- Pannes by Establishment Chart -->
    <div class="h-[28rem] bg-white rounded-lg shadow-sm p-4 md:p-6">
        <div class="flex justify-between mb-3">
            <div class="flex justify-center items-center">
                <h5 class="text-xl font-bold leading-none text-gray-900 pe-1">Pannes by Establishment</h5>
                <svg data-popover-target="etab-info" data-popover-placement="bottom" class="w-3.5 h-3.5 text-gray-500 hover:text-gray-900 cursor-pointer ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm0 16a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Zm1-5.034V12a1 1 0 0 1-2 0v-1.418a1 1 0 0 1 1.038-.999 1.436 1.436 0 0 0 1.488-1.441 1.501 1.501 0 1 0-3-.116.986.986 0 0 1-1.037.961 1 1 0 0 1-.96-1.037A3.5 3.5 0 1 1 11 11.466Z"/>
                </svg>
                <div data-popover id="etab-info" role="tooltip" class="absolute z-10 invisible inline-block text-sm text-gray-500 transition-opacity duration-300 bg-white border border-gray-200 rounded-lg shadow-xs opacity-0 w-72">
                    <div class="p-3 space-y-2">
                        <h3 class="font-semibold text-gray-900">Establishment Analysis</h3>
                        <p>Establishments with the highest number of reported pannes during the selected period.</p>
                    </div>
                    <div data-popper-arrow></div>
                </div>
            </div>
        </div>
        <div class="py-6" id="etab-chart"></div>
    </div>
</div>

<script>
    // Pass PHP data to JavaScript
    const labels = <?= $labels_js ?>;
    const data = <?= $data_js ?>;
    const lineLabels = <?= $line_labels_js ?>;
    const lineData = <?= $line_data_js ?>;
    const statusData = <?= $status_data_js ?>;
    const topTechsLabels = <?= $top_techs_labels ?>;
    const topTechsData = <?= $top_techs_data ?>;
    const pannesEtabLabels = <?= $pannes_etab_labels ?>;
    const pannesEtabData = <?= $pannes_etab_data ?>;
    const timeframe = "<?= $timeframe ?>";
</script>

<script src="../../apexcharts/dist/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>