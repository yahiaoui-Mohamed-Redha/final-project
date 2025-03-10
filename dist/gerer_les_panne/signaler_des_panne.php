<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
include '../../app/config.php';

// Start session
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin'])) {
    header('Location: login.php');
    exit;
}

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

        // Fetch the last panne_num from the panne table
        $stmt_last_panne_num = $conn->prepare("SELECT panne_num FROM panne ORDER BY panne_num DESC LIMIT 1");
        $stmt_last_panne_num->execute();
        $last_panne_num = $stmt_last_panne_num->fetchColumn();

        // If no panne_num exists, start from 0001
        if (empty($last_panne_num)) {
            $next_number = 1;
        } else {
            // Extract the numeric part of the last panne_num and increment it
            $last_number = intval(substr($last_panne_num, 0, 4)); // Extract the first 4 digits
            $next_number = $last_number + 1;

            // Ensure the number is always 4 digits (e.g., 0001, 0002, etc.)
            if ($next_number > 9999) {
                throw new Exception("Maximum number of pannes reached for this postal code.");
            }
            $formatted_number = str_pad($next_number, 4, '0', STR_PAD_LEFT);
        }

        // Insert each panne into the panne table
        foreach ($_POST['pannes'] as $panne) {
            // Format the number to always be 4 digits
            $formatted_number = str_pad($next_number, 4, '0', STR_PAD_LEFT); // 0001, 0002, etc.

            // Create a new panne_num
            $panne_num = $formatted_number . '-' . $postal_code; // Combine with postal_code

            // Debugging: Log the generated panne_num
            error_log("Generated panne_num: " . $panne_num);

            // Check if the panne_num already exists
            $stmt_check_panne_num = $conn->prepare("SELECT COUNT(*) FROM panne WHERE panne_num = :panne_num");
            $stmt_check_panne_num->execute(['panne_num' => $panne_num]);
            $count = $stmt_check_panne_num->fetchColumn();

            if ($count > 0) {
                throw new Exception("Duplicate panne_num detected: " . $panne_num);
            }

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

            // Increment the number for the next panne
            $next_number++;
        }

        // Commit the transaction
        $conn->commit();
        $success_message = "تم الإبلاغ عن الأعطال وإنشاء التقرير بنجاح!";
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

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الإبلاغ عن الأعطال</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>الإبلاغ عن الأعطال</h1>
    <?php if (isset($success_message)) echo "<p style='color: green;'>$success_message</p>"; ?>
    <?php if (isset($error_message)) echo "<p style='color: red;'>$error_message</p>"; ?>
    <form method="POST">
        <div id="panne-container">
            <div class="panne-item">
                <input type="text" name="pannes[0][panne_name]" placeholder="اسم العطل" required>
                <input type="date" name="pannes[0][date_signalement]" value="<?php echo date('Y-m-d'); ?>" required>
                <textarea name="pannes[0][description]" placeholder="وصف العطل" required></textarea>
                <select name="pannes[0][type_id]" required>
                    <option value="">اختيار نوع العطل</option>
                    <?php foreach ($type_pannes as $type): ?>
                        <option value="<?php echo $type['type_id']; ?>">
                            <?php echo htmlspecialchars($type['type_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div>
            <button type="button" id="add-panne">إضافة عطل آخر</button>
            <button type="submit" name="signaler_panne">إبلاغ</button>
        </div>
    </form>

    <script>
        // JavaScript to dynamically add more panne fields
        document.getElementById('add-panne').addEventListener('click', function() {
            const container = document.getElementById('panne-container');
            const index = container.children.length;
            const newPanne = `
                <div class="panne-item">
                    <input type="text" name="pannes[${index}][panne_name]" placeholder="اسم العطل" required>
                    <input type="date" name="pannes[${index}][date_signalement]" value="<?php echo date('Y-m-d'); ?>" required>
                    <textarea name="pannes[${index}][description]" placeholder="وصف العطل" required></textarea>
                    <select name="pannes[${index}][type_id]" required>
                        <option value="">اختيار نوع العطل</option>
                        <?php foreach ($type_pannes as $type): ?>
                            <option value="<?php echo $type['type_id']; ?>">
                                <?php echo htmlspecialchars($type['type_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newPanne);
        });
    </script>
</body>
</html>