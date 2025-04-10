<?php
// Headers must be at the very top
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// Verify user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Verify backup directory
    $backupDir = '../../backups';
    if (!is_dir($backupDir)) {
        if (!mkdir($backupDir, 0755, true)) {
            throw new Exception("Cannot create backup directory");
        }
    }

    // Verify directory is writable
    if (!is_writable($backupDir)) {
        throw new Exception("Backup directory is not writable");
    }

    $backupFile = $backupDir . '/backup_' . date('Y-m-d_H-i-s') . '.sql';
    $tables = ['RapFichiers', 'Rapport', 'Users', 'Roles']; // Your tables
    
    $output = "";
    
    foreach ($tables as $table) {
        // Get table structure
        $stmt = $conn->query("SHOW CREATE TABLE `$table`");
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
        $output .= "\n\n" . $createTable['Create Table'] . ";\n\n";
        
        // Get table data
        $stmt = $conn->query("SELECT * FROM `$table`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $values = array_map([$conn, 'quote'], array_values($row));
            $output .= "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n";
        }
    }

    if (file_put_contents($backupFile, $output) === false) {
        throw new Exception("Failed to write backup file");
    }

    // Log backup in Rapport table
    $stmt = $conn->prepare("INSERT INTO Rapport (rap_num, date_creation, user_id) VALUES ('backup', NOW(), ?)");
    $stmt->execute([$_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Backup created successfully!',
        'file' => basename($backupFile)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}