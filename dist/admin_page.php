<?php
session_start();
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
include '../app/config.php';

// Verify user authorization
// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    // Redirect to the login page or show an error message
    header('location: index.php');
    exit(); // Stop further execution
}

// Fetch admin details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$user = $select->fetch(PDO::FETCH_ASSOC);

// Fetch notifications for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the contentpage parameter from the URL
$contentpage = isset($_GET['contentpage']) ? $_GET['contentpage'] : 'statistiques/statistiques.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="../src/output.css">
    <style>
        .active {
            background-color: #c8d3f659;
            color: #0455b7;
        }
            /* Loading Animation */
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Content Page Opacity */
        #contentpage {
            transition: opacity 0.5s ease-in-out;
        }

        #contentpage.loaded {
            opacity: 1;
        }
        /* Add this to your existing styles */
        #notificationDiv {
            display: none; /* Hidden by default */
            transition: opacity 0.3s ease-in-out;
        }

        #contentpage.blur {
            opacity: 0.5;
            transition: opacity 0.3s ease-in-out;
        }

        .notification-item {
            transition: background-color 0.2s ease-in-out;
        }

        .notification-item:hover {
            background-color: #f3f4f6;
        }

        /* Modal overlay styles */
        #modal-overlay {
            transition: opacity 0.3s ease;
        }

        /* Modal styles */
        #modal {
            transition: transform 0.3s ease;
            transform: translateX(-100%);
        }

        /* Slide-in animation */
        #modal.slide-in {
            transform: translateX(0);
        }
    </style>
</head>

<body class="bg-[#f6f6f6]">

    <aside class=" z-[97] fixed flex flex-col justify-start top-0 left-0 min-w-[16.3rem] h-screen pt-2 overflow-hidden bg-white transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
        <div class="flex items-center justify-start pl-5 pr-5">
            <!-- Logo -->
            <img src="../assets/image/logo-head.png" alt="Logo" class="h-12 w-12 mr-3 mt-3">
            <div>
                <h1 class="text-xl font-bold text-gray-700">System Name</h1>
                <p class="text-xs font-semibold text-gray-400">By Algérie Poste <?php echo date("Y"); ?></p>
            </div>
        </div>
        <div class="mt-6 pl-5 pr-5 pb-4 overflow-y-auto
            [&::-webkit-scrollbar]:w-2
            [&::-webkit-scrollbar-track]:bg-gray-100
            [&::-webkit-scrollbar-thumb]:bg-gray-300">
            <nav class="w-full pr-5 nav fixed-on-h632 -mx-3 bottom-6 top-[90px] flex flex-col flex-1 justify-between space-y-4">

                <div class="space-y-4">
                    <div class="space-y-2.5 ">
                        <label class="px-3 text-xs font-semibold text-gray-500 uppercase">Statistiques</label>

                        <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-[#c8d3f659] hover:text-[#0455b7]" href="statistiques/statistiques.php?admin_id=<?php echo $user_id; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                            </svg>
                            <span class="mx-2 text-sm font-medium">Tableau de bord</span>
                        </a>

                        <button class="w-full flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-[#c8d3f659] hover:text-[#0455b7]" onclick="toggleNotification()">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                                <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"></path>
                                <path d="M9 17v1a3 3 0 0 0 6 0v-1"></path>
                            </svg>
                            <span class="mx-2 text-sm font-medium">Notification</span>
                        </button>
                    </div>

                    <div class="space-y-2.5 ">
                        <label class="px-3 text-xs font-semibold text-gray-500 uppercase">Contrôle</label>

                        <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-[#c8d3f659] hover:text-[#0455b7]" href="gerer_les_comptes/manage_users.php?admin_id=<?php echo $user_id; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                                <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path>
                                <path d="M6 21v-2a4 4 0 0 1 4 -4h2.5"></path>
                                <path d="M19.001 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                <path d="M19.001 15.5v1.5"></path>
                                <path d="M19.001 21v1.5"></path>
                                <path d="M22.032 17.25l-1.299 .75"></path>
                                <path d="M17.27 20l-1.3 .75"></path>
                                <path d="M15.97 17.25l1.3 .75"></path>
                                <path d="M20.733 20l1.3 .75"></path>
                            </svg>
                            <span class="mx-2 text-sm font-medium">Gérer les comptes</span>
                        </a>

                        <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-[#c8d3f659] hover:text-[#0455b7]" href="gerer_les_panne/gerer_pn.php?admin_id=<?php echo $user_id; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                            </svg>
                            <span class="mx-2 text-sm font-medium">Gérer les pannes</span>
                        </a>

                        <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-[#c8d3f659] hover:text-[#0455b7]" href="gerer_les_rapport/gerer_rp.php?admin_id=<?php echo $user_id; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>

                            <span class="mx-2 text-sm font-medium">Gérer les Rapports</span>
                        </a>

                        <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-[#c8d3f659] hover:text-[#0455b7]" href="gerer_les_ordres_des_missions/gerer_ord.php?admin_id=<?php echo $user_id; ?>">
                            <svg class="flex-shrink-0 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                                <path d="M3 15m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z"></path>
                                <path d="M10 15m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z"></path>
                                <path d="M17 15m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z"></path>
                                <path d="M5 11v-3a3 3 0 0 1 3 -3h8a3 3 0 0 1 3 3v3"></path>
                                <path d="M16.5 8.5l2.5 2.5l2.5 -2.5"></path>
                            </svg>

                            <span class="mx-2 text-sm font-medium">Gérer les orders de mission</span>
                        </a>

                        <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-[#c8d3f659] hover:text-[#0455b7]" href="gerer_les_fiche_dintervention/P-GFI.php?admin_id=<?php echo $user_id; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                            </svg>

                            <span class="mx-2 text-sm font-medium">Gérer fiche d'intervention</span>
                        </a>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <label class="px-3 text-xs font-semibold text-gray-500 uppercase">Pour toi</label>

                    <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-[#c8d3f659] hover:text-[#0455b7]" href="settings_page.php">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="mx-2 text-sm font-medium">Paramètres</span>
                    </a>

                    <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-[#f4acbf47] hover:text-[#f60347]" href="#" onclick="logout()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                            <path d="M10 8v-2a2 2 0 0 1 2 -2h7a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2"></path>
                            <path d="M15 12h-12l3 -3"></path>
                            <path d="M6 15l-3 -3"></path>
                        </svg>
                        <span class="mx-2 text-sm font-medium">Déconnecter</span>
                    </a>
                </div>
            </nav>
        </div>
    </aside>

    <div class="content sm:ml-[17rem] w-[calc(100%-16.3rem)] float-right">
        <header class=" fixed w-[calc(100%-16.3rem)] float-right z-50 bg-white shadow-md p-4 flex justify-between items-center">
            <h1 class="font-medium text-gray-700 text-xl text-left">Tableau de bord</h1>
            <div class="flex items-center space-x-9">
                <!-- User Information -->
                <div class="flex items-center">
                    <img src="../assets/image/download.jpg" alt="User" class="h-10 w-10 rounded-lg mr-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?></p>
                        <p id="role" class="text-xs text-gray-500"><?php echo $user['role_name']; ?></p>
                    </div>
                </div>

                <!-- Language Selector -->
                <select class="p-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="en">English</option>
                    <option value="fr" selected>French</option>
                    <option value="ar">Arabic</option>
                </select>
            </div>
        </header>
        <!-- Notification Div -->
        <div id="notificationDiv" class="hidden fixed top-16 left-64 w-64 bg-white shadow-lg z-50 max-h-80 overflow-y-auto">
            <div class="p-4">
                <h2 class="text-lg font-semibold">Notifications</h2>
                <div id="notificationList">
                    <?php if (empty($notifications)): ?>
                        <p class="text-gray-500">No new notifications.</p>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item p-2 border-b border-gray-200 hover:bg-gray-50 cursor-pointer"
                                data-notification-id="<?php echo $notification['id']; ?>"
                                data-notification-link="<?php echo $notification['notification_link']; ?>">
                                <p class="text-sm font-medium"><?php echo htmlspecialchars($notification['notification_message']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo $notification['created_at']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="px-6 py-8 mt-20" id="contentpage"></div>
    </div>

    <script>
        const labels = <?= $labels_js ?>;
        const data = <?= $data_js ?>;
    </script>

    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../apexcharts/dist/apexcharts.min.js"></script>
    
    <script>
        function loadPage(page) {
            // Show loading animation
            $("#contentpage").html("<div class='loader'></div>");

            // Save the current page in sessionStorage
            sessionStorage.setItem("currentPage", page);

            // Simulate a delay for the loading animation (optional)
            setTimeout(function() {
                $.ajax({
                    type: "GET",
                    url: page,
                    success: function(data) {
                        // Add content to the contentpage element
                        $("#contentpage").html(data);

                        // Set the active link in the sidebar
                        setActiveLink();

                        // Push the new state to the browser's history
                        history.pushState({ page: page }, "", "admin_page.php?contentpage=" + page);
                        const currentPage = window.location.href;

                        // Load and execute JavaScript specific to the loaded page
                        if (window.location.href.includes('manage_users.php')) {
                            $.getScript('js/manage_users.js', function() {
                                console.log('manage_users.js loaded and executed');
                            });
                        }

                        if (window.location.href.includes('gerer_pn.php')) {
                            $.getScript('js/gerer_pn.js', function() {
                                console.log('gerer_pn.js loaded and executed');
                            });
                        }

                        if (window.location.href.includes('gerer_rp.php')) {
                            $.getScript('js/gerer_rp.js', function() {
                                console.log('gerer_rp.js loaded and executed');
                            });
                        }

                        if (window.location.href.includes('gerer_ord.php')) {
                            $.getScript('js/gerer_ord.js', function() {
                                console.log('gerer_ord.js loaded and executed');
                            });
                        }

                        if (window.location.href.includes('statistiques.php')) {
                            $.getScript('js/statistiques.js', function() {
                                console.log('statistiques.js loaded and executed');
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("An error occurred while loading the page: " + error);
                    }
                });
            }, 500); // Adjust the delay time (in milliseconds) as needed
        }

        // Set the active link in the sidebar
        function setActiveLink() {
            var currentPage = sessionStorage.getItem("currentPage") || 'statistiques/statistiques.php?admin_id=<?php echo $user_id; ?>';

            // إزالة النشاط من جميع الروابط
            $("aside a").removeClass("active");

            // تحديد الرابط النشط بناءً على الصفحة الحالية
            if (currentPage.includes("gerer_les_panne")) {
                // إذا كانت الصفحة الحالية تحتوي على "gerer_les_panne"، فعِّل رابط "Gerer les pannes"
                $("aside a[href*='gerer_les_panne']").addClass("active");
            } else {
                // إذا لم تكن، فعِّل الرابط المناسب
                $("aside a[href='" + currentPage + "']").addClass("active");
            }

            // تحديد الرابط النشط بناءً على الصفحة الحالية
            if (currentPage.includes("gerer_les_comptes")) {
                // إذا كانت الصفحة الحالية تحتوي على "gerer_les_panne"، فعِّل رابط "Gerer les pannes"
                $("aside a[href*='gerer_les_comptes']").addClass("active");
            } else {
                // إذا لم تكن، فعِّل الرابط المناسب
                $("aside a[href='" + currentPage + "']").addClass("active");
            }

            // تحديد الرابط النشط بناءً على الصفحة الحالية
            if (currentPage.includes("gerer_les_ordres_des_missions")) {
                // إذا كانت الصفحة الحالية تحتوي على "gerer_les_panne"، فعِّل رابط "Gerer les pannes"
                $("aside a[href*='gerer_les_ordres_des_missions']").addClass("active");
            } else {
                // إذا لم تكن، فعِّل الرابط المناسب
                $("aside a[href='" + currentPage + "']").addClass("active");
            }
        }

        // Logout
        function logout() {
            if (confirm("Are you sure you want to log out?")) {
                sessionStorage.clear();
                window.location.href = "../app/logout.php";
            }
        }

        // Load the current page when the document is ready
        $(document).ready(function() {
            // Load the current page from sessionStorage or default to 'statistiques.php'
            var currentPage = sessionStorage.getItem("currentPage");
            if (currentPage) {
                loadPage(currentPage);
            } else {
                loadPage('statistiques/statistiques.php?admin_id=<?php echo $user_id; ?>'); // Default page
            }

            // التعامل مع النقر على الروابط التي تحتوي على class="load-page-link"
            $(document).on("click", "a.load-page-link", function(event) {
                event.preventDefault(); // منع التحميل التقليدي للصفحة
                var page = $(this).attr("href"); // الحصول على رابط الصفحة
                loadPage(page); // تحميل الصفحة داخل #contentpage
            });

            // Delegate events to all links in the sidebar
            $("aside").on("click", "a[href]", function(event) {
                event.preventDefault();
                var page = $(this).attr("href");
                if (page !== "#") {
                    loadPage(page);
                }
            });

            // Manage language selector
            $("select[name='language']").on("change", function() {
                var language = $(this).val();
                localStorage.setItem("selectedLanguage", language);
                alert("Language changed to " + language);
            });

            // Handle form submission using AJAX
            $('#createAccountForm').on('submit', function(e) {
                e.preventDefault(); // Prevent the default form submission

                // Show a loading indicator (optional)
                $('#createAccountForm').html('<div class="loader"></div>');

                // Send the form data using AJAX
                $.ajax({
                    url: 'create_users.php', // The PHP file that processes the form
                    type: 'POST', // Use POST method
                    data: $(this).serialize(), // Serialize the form data
                    success: function(response) {
                        // Handle the response from the server
                        if (response === "Data inserted successfully") {
                            alert("Account created successfully!");
                            // Redirect to the manage_users.php page
                            window.location.href = 'admin_page.php?contentpage=gerer_les_comptes/manage_users.php';
                        } else {
                            // Display the error message
                            alert("Error: " + response);
                            // Reload the form to allow the user to try again
                            $('#createAccountForm').load('create_users.php #createAccountForm', function() {
                                toggleFields(); // Reinitialize the toggleFields function
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle AJAX errors
                        alert("An error occurred while submitting the form: " + error);
                        // Reload the form to allow the user to try again
                        $('#createAccountForm').load('create_users.php #createAccountForm', function() {
                            toggleFields(); // Reinitialize the toggleFields function
                        });
                    }
                });
            });

            $('#panneForm').on('submit', function(e) {
                e.preventDefault(); // Prevent the default form submission

                // Show a loading indicator (optional)
                $('#panneForm').html('<div class="loader"></div>');

                // Send the form data using AJAX
                $.ajax({
                    url: 'gerer_pn/signaler_des_panne.php', // The PHP file that processes the form
                    type: 'POST', // Use POST method
                    data: $(this).serialize(), // Serialize the form data
                    success: function(response) {
                        // Handle the response from the server
                        if (response === "Data inserted successfully") {
                            alert("Account created successfully!");
                            // Redirect to the manage_users.php page
                            window.location.href = 'admin_page.php?contentpage=gerer_pn/signaler_des_panne.php';
                        } else {
                            // Display the error message
                            alert("Error: " + response);
                            // Reload the form to allow the user to try again
                            $('#createAccountForm').load('gerer_pn/signaler_des_panne.php #panneForm', function() {
                                toggleFields(); // Reinitialize the toggleFields function
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle AJAX errors
                        alert("An error occurred while submitting the form: " + error);
                        // Reload the form to allow the user to try again
                        $('#panneForm').load('gerer_pn/signaler_des_panne.php #panneForm', function() {
                            toggleFields(); // Reinitialize the toggleFields function
                        });
                    }
                });
            });

        });

        // Handle the popstate event for back/forward navigation
        $(window).on("popstate", function(event) {
            if (event.originalEvent.state) {
                var page = event.originalEvent.state.page;
                loadPage(page);
            }
        });

        // Function to toggle the notification div
        function toggleNotification() {
            var notificationDiv = document.getElementById('notificationDiv');
            var contentPage = document.getElementById('contentpage');

            if (notificationDiv.style.display === "none" || notificationDiv.style.display === "") {
                notificationDiv.style.display = "block";
                contentPage.classList.add("blur"); // Optional blur effect
            } else {
                notificationDiv.style.display = "none";
                contentPage.classList.remove("blur");
            }
        }

        function closeNotification(event) {
            const notificationDiv = document.getElementById('notificationDiv');
            const contentPage = document.getElementById('contentpage');

            if (!notificationDiv.contains(event.target)) {
                notificationDiv.classList.add('hidden');
                contentPage.classList.remove('blur');
            }
        }

        document.querySelector('button[onclick="toggleNotification()"]').addEventListener('click', function(event) {
            event.stopPropagation();
            toggleNotification();
        });

        document.addEventListener('click', closeNotification);

        document.getElementById('notificationDiv').addEventListener('click', function(event) {
            event.stopPropagation();
        });

        // Handle notification item clicks
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.getAttribute('data-notification-id');
                const notificationLink = this.getAttribute('data-notification-link');

                // Mark the notification as read (via AJAX)
                markNotificationAsRead(notificationId);

                // Redirect to the notification link if it exists
                if (notificationLink) {
                    window.location.href = notificationLink;
                }
            });
        });

        // Function to mark a notification as read
        function markNotificationAsRead(notificationId) {
            fetch('../app/mark_notification_as_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Optionally, update the UI to reflect the notification as read
                    const notificationItem = document.querySelector(`.notification-item[data-notification-id="${notificationId}"]`);
                    if (notificationItem) {
                        notificationItem.style.opacity = '0.6'; // Example: Dim the notification
                    }
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
        }

    </script>
</body>
</html>