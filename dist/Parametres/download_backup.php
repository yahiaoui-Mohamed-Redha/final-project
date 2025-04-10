<?php
session_start();
include '../../app/config.php';

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}

?>


<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-8 text-blue-600">Database Backups</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Available Backups</h2>
            
            <?php
            // Scan backups directory
            $backupFiles = [];
            if (file_exists('backups')) {
                $backupFiles = array_diff(scandir('backups'), array('..', '.'));
            }
            
            if (empty($backupFiles)) {
                echo '<p class="text-gray-600">No backups available.</p>';
            } else {
                echo '<ul class="divide-y divide-gray-200">';
                foreach ($backupFiles as $file) {
                    $filePath = 'backups/' . $file;
                    $fileSize = filesize($filePath);
                    $formattedSize = round($fileSize / (1024 * 1024), 2) . ' MB';
                    $fileDate = date('F j, Y, g:i a', filemtime($filePath));
                    
                    echo '
                    <li class="py-4 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium">' . htmlspecialchars($file) . '</h3>
                            <p class="text-sm text-gray-500">' . $fileDate . ' • ' . $formattedSize . '</p>
                        </div>
                        <a href="download.php?file=' . urlencode($file) . '" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Download</a>
                    </li>';
                }
                echo '</ul>';
            }
            ?>
        </div>
    </div>