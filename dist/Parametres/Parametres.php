<?php
session_start();
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
include '../../app/config.php';

// Verify user authorization
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('location: index.php');
    exit();
}

// Fetch admin details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$user = $select->fetch(PDO::FETCH_ASSOC);
?>


    <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-6 h-[80vh]">
        <!-- Left Section - User Info (Fixed) -->
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-lg shadow-md p-6 h-full">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">User Information</h2>
                    <a href="edit_account.php" class="text-blue-600 hover:text-blue-800">
                        <!-- Pencil SVG Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </a>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Full Name</p>
                        <p class="font-medium"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Username</p>
                        <p class="font-medium"><?= htmlspecialchars($user['username']) ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Phone Numbers</p>
                        <p class="font-medium">
                            <?= htmlspecialchars($user['user_mobile']) ?: 'Not set' ?>
                            <?= $user['user_fixe'] ? ' / ' . htmlspecialchars($user['user_fixe']) : '' ?>
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Account Status</p>
                        <p class="font-medium">
                            <?= $user['etat_compte'] ? 
                                '<span class="text-green-600">Active</span>' : 
                                '<span class="text-red-600">Inactive</span>' ?>
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Role</p>
                        <?php
                        $roleStmt = $conn->prepare("SELECT role_nom FROM Roles WHERE role_id = ?");
                        $roleStmt->execute([$user['role_id']]);
                        $role = $roleStmt->fetchColumn();
                        ?>
                        <p class="font-medium"><?= htmlspecialchars($role) ?></p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <a href="edit_account.php" class="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center justify-center gap-2">
                        <!-- User Edit SVG Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            <path fill-rule="evenodd" d="M11.828 11.828a4 4 0 01-5.656 0l-1.172-1.172a4 4 0 010-5.656l.586-.586 6.828 6.828-.586.586z" clip-rule="evenodd" />
                        </svg>
                        Modify Account
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Section - Parameter Rectangles (Scrollable) -->
        <div class="w-full md:w-2/3 space-y-6 overflow-y-auto no-scrollbar">
            <h1 class="text-2xl font-bold text-gray-800 sticky top-0 bg-[#f6f6f6] py-2">Settings</h1>
            
            <!-- First Rectangle (divided into two) -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="grid grid-cols-2 divide-x divide-gray-200">
                    <!-- Left section - Edit your account -->
                    <div class="p-6">
                        <h3 class="font-medium text-lg mb-4 flex items-center gap-2">
                            <!-- User Circle SVG Icon -->
                            Edit your account
                        </h3>
                        <a href="edit_account.php?tab=password" class="block w-full py-2 px-4 rounded-md hover:bg-[#c8d3f659] hover:text-[#0455b7] text-left transition-colors flex items-center gap-2">
                            <!-- Lock SVG Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                            Update password
                        </a>
                        <a href="edit_account.php?tab=devices" class="block w-full py-2 px-4 rounded-md hover:bg-[#c8d3f659] hover:text-[#0455b7] text-left transition-colors flex items-center gap-2">
                            <!-- Device Mobile SVG Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V4a2 2 0 00-2-2H7zm3 14a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                            Manage connected devices
                        </a>
                    </div>
                    
                    <!-- Right section - Clear cache -->
                    <div class="p-6">
                        <h3 class="font-medium text-lg mb-4 flex items-center gap-2">
                            <!-- Refresh SVG Icon -->
                            Clear cache
                        </h3>
                        <a href="clear_cache.php?type=app" class="block w-full py-2 px-4 rounded-md hover:bg-[#c8d3f659] hover:text-[#0455b7] text-left transition-colors flex items-center gap-2">
                            <!-- Chip SVG Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13 7H7v6h6V7z" />
                                <path fill-rule="evenodd" d="M7 4a1 1 0 012 0v1h2V4a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 010-2h1V7a2 2 0 012-2h2V4zM5 7h10v6H5V7z" clip-rule="evenodd" />
                            </svg>
                            Clear application cache
                        </a>
                        <a href="clear_cache.php?type=all" class="block w-full py-2 px-4 rounded-md hover:bg-[#c8d3f659] hover:text-[#0455b7] text-left transition-colors flex items-center gap-2">
                            <!-- Trash SVG Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Clear all cached data
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Second Rectangle - Backup your database -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="font-medium text-lg mb-4 flex items-center gap-2">
                    <!-- Database SVG Icon -->
                    Back up your database
                </h3>
                <form id="backupForm" action="backup_db.php" method="post">
                    <button type="submit" name="backup_type" value="monthly" class="w-full py-2 px-4 rounded-md hover:bg-[#c8d3f659] hover:text-[#0455b7] text-left transition-colors flex items-center gap-2">
                        <!-- Save SVG Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z" />
                        </svg>
                        Create monthly backup
                    </button>
                    <button type="button" onclick="downloadLatestBackup()" class="w-full py-2 px-4 rounded-md hover:bg-[#c8d3f659] hover:text-[#0455b7] text-left transition-colors flex items-center gap-2">
                        <!-- Download SVG Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Download latest backup
                    </button>
                    <div id="backupMessage" class="mt-2 text-sm text-green-600 hidden"></div>
                </form>
            </div>
            
            <!-- Third Rectangle - Install options -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="font-medium text-lg mb-4 flex items-center gap-2">
                    <!-- Desktop Computer SVG Icon -->
                    Installation options
                </h3>
                <a href="install_desktop.php" class="block w-full py-2 px-4 rounded-md hover:bg-[#c8d3f659] hover:text-[#0455b7] text-left transition-colors flex items-center gap-2">
                    <!-- Desktop SVG Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd" />
                    </svg>
                    Install as app on desktop
                </a>
                <button onclick="addToStartMenu()" class="w-full py-2 px-4 rounded-md hover:bg-[#c8d3f659] hover:text-[#0455b7] text-left transition-colors flex items-center gap-2">
                    <!-- Menu SVG Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                    Add to start menu
                </button>
            </div>
        </div>
    </div>

    <script>
        // Handle backup form submission
        document.getElementById('backupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('backupMessage');
                messageDiv.textContent = data.message;
                messageDiv.classList.remove('hidden');
                
                if (data.success) {
                    messageDiv.classList.add('text-green-600');
                    messageDiv.classList.remove('text-red-600');
                } else {
                    messageDiv.classList.add('text-red-600');
                    messageDiv.classList.remove('text-green-600');
                }
                
                setTimeout(() => {
                    messageDiv.classList.add('hidden');
                }, 5000);
            });
        });

        function downloadLatestBackup() {
            fetch('backup_db.php?action=download_latest')
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `backup_${new Date().toISOString().slice(0,10)}.sql`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                });
        }

        function createShortcut() {
            alert('Shortcut creation would be initiated here');
        }

        function addToStartMenu() {
            alert('Adding to start menu would be initiated here');
        }
    </script>