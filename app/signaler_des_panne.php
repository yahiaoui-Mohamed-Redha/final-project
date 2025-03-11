<?php
// Include database configuration
include 'config.php';

// Start session
session_start();
// Get the logged-in user's ID
$userId = $_SESSION['user_id'];

// Fetch the user's postal code
$stmt_users = $conn->prepare("SELECT postal_code FROM Users WHERE user_id = :user_id");
$stmt_users->execute(['user_id' => $userId]);
$postal_code = $stmt_users->fetchColumn();

// If postal_code is null or empty, use 'UPWB' as the default
if (empty($postal_code)) {
    $postal_code = 'UPWB';
}

// Fetch issue types from the Type_panne table
$stmt_type_panne = $conn->prepare("SELECT * FROM Type_panne");
$stmt_type_panne->execute();
$type_pannes = $stmt_type_panne->fetchAll(PDO::FETCH_ASSOC);

// Function to generate a unique panne_num
function generateUniquePanneNum($conn, $postal_code) {
    // Fetch the last panne_num from the panne table
    $stmt_last_panne_num = $conn->prepare("SELECT panne_num FROM panne ORDER BY panne_num DESC LIMIT 1");
    $stmt_last_panne_num->execute();
    $last_panne_num = $stmt_last_panne_num->fetchColumn();

    // If no panne_num exists, start from 0001
    if (empty($last_panne_num)) {
        $next_pan_num = 1;
    } else {
        // Extract the numeric part of the last panne_num and increment it
        $last_number = intval(substr($last_panne_num, 0, 4)); // Extract the first 4 digits
        $next_pan_num = $last_number + 1;
    }

    // Ensure the number is always 4 digits (e.g., 0001, 0002, etc.)
    while (true) {
        $formatted_number = str_pad($next_pan_num, 4, '0', STR_PAD_LEFT); // Format as 0001, 0002, etc.
        $panne_num = $formatted_number . '-' . $postal_code; // Combine with postal_code, e.g., 0001-UPWB

        // Check if the panne_num already exists
        $stmt_check_panne_num = $conn->prepare("SELECT COUNT(*) FROM panne WHERE panne_num = :panne_num");
        $stmt_check_panne_num->execute(['panne_num' => $panne_num]);
        $count = $stmt_check_panne_num->fetchColumn();

        // If the panne_num is unique, return it
        if ($count == 0) {
            return $panne_num;
        }

        // If the panne_num already exists, increment the number and try again
        $next_pan_num++;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signaler_panne'])) {
    try {
        // Start a transaction
        $conn->beginTransaction();

        // Fetch the last rap_num from the rapport table
        $stmt_last_rap_num = $conn->prepare("SELECT rap_num FROM rapport ORDER BY rap_num DESC LIMIT 1");
        $stmt_last_rap_num->execute();
        $last_rap_num = $stmt_last_rap_num->fetchColumn();

        // If no rap_num exists, start from 01RP-postal_code
        if (empty($last_rap_num)) {
            $next_rap_num = '01RP-' . $postal_code;
        } else {
            // Extract the numeric part of the last rap_num
            $last_number = intval(substr($last_rap_num, 0, 2)); // Extract the first 2 digits
            $next_number = $last_number + 1; // Increment the numeric part

            // Ensure the number is always 2 digits (e.g., 01, 02, etc.)
            if ($next_number > 99) {
                throw new Exception("Maximum number of reports reached for this postal code.");
            }
            $formatted_number = str_pad($next_number, 2, '0', STR_PAD_LEFT);

            // Create the new rap_num
            $next_rap_num = $formatted_number . 'RP-' . $postal_code; // Format as numberRP-postal_code
        }

        // Debugging: Log the generated rap_num
        error_log("Generated rap_num: " . $next_rap_num);

        // Check if the rap_num already exists
        $stmt_check_rap_num = $conn->prepare("SELECT COUNT(*) FROM rapport WHERE rap_num = :rap_num");
        $stmt_check_rap_num->execute(['rap_num' => $next_rap_num]);
        $count = $stmt_check_rap_num->fetchColumn();

        if ($count > 0) {
            throw new Exception("Duplicate rap_num detected: " . $next_rap_num);
        }

        // Create a new report in the rapport table
        $stmt_rapport = $conn->prepare("INSERT INTO rapport (rap_num, rap_name, rap_date, description, user_id) 
                                      VALUES (:rap_num, :rap_name, :rap_date, :description, :user_id)");
        $stmt_rapport->execute([
            'rap_num' => $next_rap_num, // Use the generated rap_num
            'rap_name' => 'تقرير الأعطال ' . date('Y-m-d H:i:s'),
            'rap_date' => date('Y-m-d'),
            'description' => 'تقرير تم إنشاؤه تلقائيًا مع الأعطال',
            'user_id' => $userId
        ]);
        $rapportId = $next_rap_num; // Use the generated rap_num as the rapportId

        // Insert each panne into the panne table
        foreach ($_POST['pannes'] as $panne) {
            // Generate a unique panne_num
            $panne_num = generateUniquePanneNum($conn, $postal_code);

            // Insert the panne
            $stmt_panne = $conn->prepare("INSERT INTO panne (panne_num, panne_name, date_signalement, description, type_id, receveur_id, rap_num) 
                                        VALUES (:panne_num, :panne_name, :date_signalement, :description, :type_id, :receveur_id, :rap_num)");
            $stmt_panne->execute([
                'panne_num' => $panne_num,
                'panne_name' => htmlspecialchars($panne['panne_name']),
                'date_signalement' => $panne['date_signalement'],
                'description' => htmlspecialchars($panne['description']),
                'type_id' => $panne['type_id'],
                'receveur_id' => $userId,
                'rap_num' => $rapportId // Link the panne to the rapport
            ]);
        }

        // Commit the transaction
        $conn->commit();
        $success_message = "تم الإبلاغ عن الأعطال وإنشاء التقرير بنجاح!";
        header('Location: ../dist/admin_page.php?contentpage=gerer_les_panne/gerer_pn.php');
        exit;
    } catch (PDOException $e) {
        // Rollback the transaction on error
        $conn->rollBack();
        $error_message = "خطأ أثناء الإبلاغ عن الأعطال: " . $e->getMessage();
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollBack();
        $error_message = "خطأ أثناء الإبلاغ عن الأعطال: " . $e->getMessage();
    }
}
?>