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

// Fetch issue types from the Type_panne table
$stmt_type_panne = $conn->prepare("SELECT * FROM Type_panne");
$stmt_type_panne->execute();
$type_pannes = $stmt_type_panne->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الإبلاغ عن الأعطال</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Signalement des pannes</h1>
    <?php if (isset($success_message)) echo "<p style='color: green;'>$success_message</p>"; ?>
    <?php if (isset($error_message)) echo "<p style='color: red;'>$error_message</p>"; ?>
    <form action="../app/signaler_des_panne.php" method="POST">
        <div id="panne-container">
            <div class="panne-item">
                <div class="space-y-12">
                        <div class="sm:col-span-4">
                            <label for="email" class="block text-sm/6 font-medium text-gray-900">Nom de la panne</label>
                            <div class="mt-2">
                                <input id="text" id="panne_name" name="pannes[0][panne_name]"  autocomplete="panne_name" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                            </div>
                        </div>

                        <input type="date" name="pannes[0][date_signalement]" value="<?php echo date('Y-m-d'); ?>" required>
                        <div>
                            <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                <div class="col-span-full">
                                    <label for="about" class="block text-sm/6 font-medium text-gray-900">Description de la panne</label>
                                    <div class="mt-2">
                                        <textarea name="pannes[0][description]" id="description" rows="3" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="country" class="block text-sm/6 font-medium text-gray-900">Choix du type de panne</label>
                            <div class="mt-2 grid grid-cols-1">
                                <select id="type_id" name="pannes[0][type_id]" autocomplete="type_id" class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                <?php foreach ($type_pannes as $type): ?>
                                    <option value="<?php echo $type['type_id']; ?>">
                                        <?php echo htmlspecialchars($type['type_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                                </select>
                                <svg class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-x-6">
            <button type="button" class="text-sm/6 font-semibold text-gray-900" id="add-panne" >Ajout d'une autre panne</button>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600" name="signaler_panne">Signaler</button>
        </div>  
    </form>
    <script>
        // JavaScript to dynamically add more panne fields
        document.getElementById('add-panne').addEventListener('click', function() {
            const container = document.getElementById('panne-container');
            const index = container.children.length;
            const newPanne = `
                <div class="panne-item">
                    <div class="space-y-12">
                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm/6 font-medium text-gray-900">Nom de la panne</label>
                                <div class="mt-2">
                                    <input id="text" id="panne_name" name="pannes[0][panne_name]"  autocomplete="panne_name" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                </div>
                            </div>

                            <input type="date" name="pannes[0][date_signalement]" value="<?php echo date('Y-m-d'); ?>" required>
                            <div>
                                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                    <div class="col-span-full">
                                        <label for="about" class="block text-sm/6 font-medium text-gray-900">Description de la panne</label>
                                        <div class="mt-2">
                                            <textarea name="pannes[0][description]" id="description" rows="3" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="country" class="block text-sm/6 font-medium text-gray-900">Choix du type de panne</label>
                                <div class="mt-2 grid grid-cols-1">
                                    <select id="type_id" name="pannes[0][type_id]" autocomplete="type_id" class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                    <?php foreach ($type_pannes as $type): ?>
                                        <option value="<?php echo $type['type_id']; ?>">
                                            <?php echo htmlspecialchars($type['type_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    </select>
                                    <svg class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                                    <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newPanne);
        });
    </script>
</body>
</html>