<?php
include 'config.php';

// Function to create database backup
function createBackup($conn) {
    // Set backup filename with timestamp
    $backupFileName = 'backup_rapfichiers_' . date('Y-m-d_H-i-s') . '.sql';
    $backupFilePath = 'backups/' . $backupFileName;
    
    // Create backups directory if it doesn't exist
    if (!file_exists('backups')) {
        mkdir('backups', 0755, true);
    }
    
    // Get all data from RapFichiers table
    $query = "SELECT * INTO OUTFILE '$backupFilePath'
              FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'
              LINES TERMINATED BY '\n'
              FROM RapFichiers";
    
    try {
        // Execute the backup query
        $result = $conn->query($query);
        
        // Insert backup record into Rapport table with rap_num = 'backup'
        $insertQuery = "INSERT INTO Rapport (rap_num, other_fields...) VALUES ('backup', ...)";
        $conn->query($insertQuery);
        
        return $backupFileName;
    } catch (Exception $e) {
        // Log error
        error_log("Backup failed: " . $e->getMessage());
        return false;
    }
}

// Create backup
$backupFile = createBackup($conn);

if ($backupFile) {
    echo "Backup created successfully: $backupFile";
} else {
    echo "Backup failed";
}

?>