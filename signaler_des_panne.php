<?php
include 'config.php';
session_start();

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['receveur', 'admin'])) {
    header('Location: login.php');
    exit;
}

// Fetch issue types
$stmt_type_panne = $conn->prepare("SELECT * FROM Type_panne");
$stmt_type_panne->execute();
$type_pannes = $stmt_type_panne->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signaler_panne'])) {
    try {
        $conn->beginTransaction();
        $userId = $_SESSION['user_id']; // Logged-in user's ID

        // Create a new report
        $stmt_rapport = $conn->prepare("INSERT INTO Rapport (rap_name, rap_date, description, user_id) 
                                        VALUES (:rap_name, :rap_date, :description, :user_id)");
        $stmt_rapport->execute([
            'rap_name' => 'تقرير الأعطال ' . date('Y-m-d H:i:s'),
            'rap_date' => date('Y-m-d'),
            'description' => 'تقرير تم إنشاؤه تلقائيًا مع الأعطال',
            'user_id' => $userId
        ]);
        $rapportId = $conn->lastInsertId(); // Get the report ID

        // Insert issues and link them to the report
        foreach ($_POST['pannes'] as $panne) {
            $stmt_panne = $conn->prepare("INSERT INTO Panne (panne_name, date_signalement, description, type_id, receveur_id, rap_num) 
                                          VALUES (:panne_name, :date_signalement, :description, :type_id, :receveur_id, :rap_num)");
            $stmt_panne->execute([
                'panne_name' => htmlspecialchars($panne['panne_name']),
                'date_signalement' => $panne['date_signalement'],
                'description' => htmlspecialchars($panne['description']),
                'type_id' => $panne['type_id'],
                'receveur_id' => $userId,
                'rap_num' => $rapportId
            ]);
        }

        $conn->commit();
        $success_message = "تم الإبلاغ عن الأعطال وإنشاء التقرير بنجاح!";
    } catch (PDOException $e) {
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
        <button type="button" id="add-panne">إضافة عطل آخر</button>
        <button type="submit" name="signaler_panne">إبلاغ</button>
    </form>

    <script>
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
