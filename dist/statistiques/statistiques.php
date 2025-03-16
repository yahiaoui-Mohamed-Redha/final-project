<?php
include '../../app/config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}

// Fetch pannes count by type and timeframe
$timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : 'Last week'; // Default to 'Last week'
$currentDate = date('Y-m-d');

switch ($timeframe) {
    case 'Last week':
        $startDate = date('Y-m-d', strtotime('-7 days')); // Last 7 days
        break;
    case 'Last month':
        $startDate = date('Y-m-d', strtotime('-30 days')); // Last 30 days
        break;
    case 'Last year':
        $startDate = date('Y-m-d', strtotime('-360 days')); // Last 360 days
        break;
    default:
        $startDate = date('Y-m-d', strtotime('-7 days')); // Default to last 7 days
        break;
}

// Fetch admin details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$admin = $select->fetch(PDO::FETCH_ASSOC);

// Query to get pannes count by type for the selected timeframe
$query_pannes = "SELECT tp.type_id, tp.type_name, COUNT(p.panne_num) AS panne_count 
                 FROM Type_panne tp 
                 LEFT JOIN Panne p ON tp.type_id = p.type_id 
                 AND p.date_signalement BETWEEN :startDate AND :currentDate 
                 GROUP BY tp.type_id";
$stmt_pannes = $conn->prepare($query_pannes);
$stmt_pannes->execute(['startDate' => $startDate, 'currentDate' => $currentDate]);
$pannes_by_type = $stmt_pannes->fetchAll(PDO::FETCH_ASSOC);


// Prepare data for the Donut chart
$labels_js = json_encode(array_column($pannes_by_type, 'type_name'));
$data_js = json_encode(array_column($pannes_by_type, 'panne_count'));

$query_pannes_over_time = "SELECT DATE(p.date_signalement) AS signalement_date, COUNT(p.panne_num) AS panne_count 
                           FROM Panne p 
                           WHERE p.date_signalement BETWEEN :startDate AND :currentDate 
                           GROUP BY DATE(p.date_signalement) 
                           ORDER BY signalement_date";
$stmt_pannes_over_time = $conn->prepare($query_pannes_over_time);
$stmt_pannes_over_time->execute(['startDate' => $startDate, 'currentDate' => $currentDate]);
$pannes_over_time = $stmt_pannes_over_time->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the Line chart
$line_labels_js = json_encode(array_column($pannes_over_time, 'signalement_date'));
$line_data_js = json_encode(array_column($pannes_over_time, 'panne_count'));

?>


    <div class="flex justify-between md:items-center">
        <div>
            <p class="block antialiased font-sans text-base leading-relaxed text-inherit font-semibold">
                Overall Performance
            </p>
            <p class="block antialiased font-sans text-sm leading-normal font-normal text-gray-600 md:w-full w-4/5">
                Upward arrow indicating an increase in revenue compared to the previous period.
            </p>
        </div>
        <!-- select dropdown -->
        <div class="shrink-0">
            <!-- Select List -->
            <div class="relative mt-2">
                <!-- Button -->
                <button
                    type="button"
                    id="select-button"
                    class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                    aria-haspopup="listbox"
                    aria-expanded="true"
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

                <!-- Options List -->
                <ul
                    id="select-options"
                    class="absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm hidden"
                    tabindex="-1"
                    role="listbox"
                    aria-labelledby="listbox-label">
                    <li
                        class="relative cursor-default select-none py-2 pl-3 pr-5 text-gray-900 hover:bg-[#c8d3f659] hover:text-[#1004b7]"
                        role="option"
                        data-value="Last week">
                        <div class="flex items-center">
                            <span class="ml-3 block truncate font-normal">Last week</span>
                        </div>
                    </li>
                    <li
                        class="relative cursor-default select-none py-2 pl-3 pr-5 text-gray-900 hover:bg-[#c8d3f659] hover:text-[#1004b7]"
                        role="option"
                        data-value="Last month">
                        <div class="flex items-center">
                            <span class="ml-3 block truncate font-normal">Last month</span>
                        </div>
                    </li>
                    <li
                        class="relative cursor-default select-none py-2 pl-3 pr-5 text-gray-900 hover:bg-[#c8d3f659] hover:text-[#1004b7]"
                        role="option"
                        data-value="Last year">
                        <div class="flex items-center">
                            <span class="ml-3 block truncate font-normal">Last year</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- shows all types statisques -->
    <div class="mt-6 grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 items-center md:gap-2.5 gap-4">
        <?php foreach ($pannes_by_type as $type): ?>
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md shadow-sm border border-gray-200 !rounded-lg">
                <div class="p-6 p-4">
                    <div class="flex justify-between items-center">
                        <p class="block antialiased font-sans text-base font-light leading-relaxed text-inherit !font-medium !text-xs text-gray-600">
                            <?= htmlspecialchars($type['type_name']) ?>
                        </p>
                        <div class="flex items-center gap-1">
                            <!-- Example: Add an icon or percentage here if needed -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="4"
                                stroke="currentColor"
                                aria-hidden="true"
                                class="w-3 h-3 text-green-500">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path>
                            </svg>
                            <p class="block antialiased font-sans text-base font-light leading-relaxed text-green-500 font-medium !text-xs">
                                12% <!-- Replace with dynamic percentage if needed -->
                            </p>
                        </div>
                    </div>
                    <p class="block antialiased font-sans text-base font-light leading-relaxed text-blue-gray-900 mt-1 font-bold text-2xl">
                        Total panne : <span><?= $type['panne_count'] ?></span>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="flex items-center justify-between mt-5 py-5 space-x-5">
        <!-- line-chart -->
        <div class="w-full h-[28rem] relative flex flex-col justify-center rounded-xl bg-white bg-clip-border text-gray-700 shadow-md">
            <div class="relative mx-4 mt-4 flex flex-col gap-4 overflow-hidden rounded-none bg-transparent bg-clip-border text-gray-700 shadow-none md:flex-row md:items-center">
                <div>
                    <h6 class="block font-sans text-base font-semibold leading-relaxed tracking-normal text-blue-gray-900 antialiased">
                        Line Chart
                    </h6>
                    <p class="block max-w-sm font-sans text-sm font-normal leading-normal text-gray-700 antialiased">
                        Visualize the number of pannes over time.
                    </p>
                </div>
            </div>
            <div class="pt-6 px-2 pb-0">
                <div id="line-chart"></div>
            </div>
        </div>
        <!-- Donut chart -->
        <div class="max-w-sm h-[28rem] bg-white rounded-lg shadow-sm p-4 md:p-6 float-left">
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

            <!-- Donut Chart -->
            <div class="py-6" id="donut-chart"></div>
        </div>
    </div>


<script>
    const labels = <?= $labels_js ?>;
    const data = <?= $data_js ?>;
    const lineLabels = <?= $line_labels_js ?>;
    const lineData = <?= $line_data_js ?>;
</script>

<script src="../../apexcharts/dist/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>