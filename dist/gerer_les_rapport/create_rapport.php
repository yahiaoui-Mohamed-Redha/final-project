<?php
// Include database configuration
include '../../app/config.php';

// Verify user authorization
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Receveur', 'Admin', 'Technicien'])) {
    header('Location:../../index.php');
    exit;
}

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
    $postal_code ='UPWB';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_rapport'])) {
    try {
        // Start a transaction
        $conn->beginTransaction();

        // Fetch the last rap_num from the rapport table
        $stmt_last_rap_num = $conn->prepare("SELECT rap_num FROM rapport ORDER BY rap_num DESC LIMIT 1");
        $stmt_last_rap_num->execute();
        $last_rap_num = $stmt_last_rap_num->fetchColumn();

        // If no rap_num exists, start from 01RP-postal_code
        if (empty($last_rap_num)) {
            $next_rap_num='01RP-'.$postal_code;
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
            $next_rap_num =$formatted_number.'RP-'.$postal_code; // Format as numberRP-postal_code
        }
        // Debugging: Log the generated rap_num
        error_log("Generated rap_num: ".$next_rap_num);

        // Check if the rap_num already exists
        $stmt_check_rap_num = $conn->prepare("SELECT COUNT(*) FROM rapport WHERE rap_num = :rap_num");
        $stmt_check_rap_num->execute(['rap_num' => $next_rap_num]);
        $count = $stmt_check_rap_num->fetchColumn();

        if ($count > 0) {
            throw new Exception("Duplicate rap_num detected: ".$next_rap_num);
        }

        // Get rapport details from form
        $rapport_name = $_POST['rap_name'];
        $rapport_description = $_POST['description'];
        $rapport_date = date('Y-m-d'); // Default to today

        // If a specific date was provided, use that instead
        if (!empty($_POST['rap_date'])) {
            $rapport_date = $_POST['rap_date'];
        }

        // Create a new report in the rapport table
        $stmt_rapport = $conn->prepare("INSERT INTO rapport (rap_num, rap_name, rap_date, description, user_id) 
                                      VALUES (:rap_num, :rap_name, :rap_date, :description, :user_id)");
        $stmt_rapport->execute([
            'rap_num' => $next_rap_num,
            'rap_name' => htmlspecialchars($rapport_name),
            'rap_date' => $rapport_date,
            'description' => htmlspecialchars($rapport_description),
            'user_id' => $userId
        ]);
        
        // Handle file uploads
        if (!empty($_FILES['files']['name'][0])) {
            $uploadDir = '../uploads/rapports/';
            
            // Create upload directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Process each uploaded file
            for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
                if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                    $fileName = basename($_FILES['files']['name'][$i]);
                    $fileTmp = $_FILES['files']['tmp_name'][$i];
                    $fileSize = $_FILES['files']['size'][$i];
                    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Validate file type
                    $allowedTypes = ['pdf', 'png', 'jpeg', 'jpg', 'mp4', 'mp3', 'docx', 'sql'];
                    if (!in_array($fileType, $allowedTypes)) {
                        throw new Exception("Le type de fichier $fileType n'est pas autorisé.");
                    }
                    
                    // Validate file size (10MB max)
                    if ($fileSize > 10 * 1024 * 1024) {
                        throw new Exception("Le fichier $fileName dépasse la taille maximale de 10MB.");
                    }
                    
                    // Generate unique filename
                    $uniqueName = uniqid().'_'.$fileName;
                    $uploadPath = $uploadDir.$uniqueName;
                    
                    // Move uploaded file
                    if (move_uploaded_file($fileTmp, $uploadPath)) {
                        // Insert file info into RapFichiers table
                        $stmt_file = $conn->prepare("INSERT INTO RapFichiers (rap_num, nom_fichier, chemin_fichier, taille_fichier, type_fichier) 
                                                   VALUES (:rap_num, :nom_fichier, :chemin_fichier, :taille_fichier, :type_fichier)");
                        $stmt_file->execute([
                            'rap_num' => $next_rap_num,
                            'nom_fichier' => $fileName,
                            'chemin_fichier' => $uploadPath,
                            'taille_fichier' => $fileSize,
                            'type_fichier' => $fileType
                        ]);
                    } else {
                        throw new Exception("Erreur lors du téléchargement du fichier $fileName.");
                    }
                }
            }
        }

        // Commit the transaction
        $conn->commit();
        $_SESSION['success'] = "  le rapport a été créé avec succès!"; // Success message 
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (PDOException $e) {
        // Rollback the transaction on error
        $conn->rollBack();
        $_SESSION['error'] = " Erreur lors de la création du rapport: " . $e->getMessage(); // Error message 
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollBack();
        $_SESSION['error'] = " Erreur lors de la création du rapport: " . $e->getMessage(); // Error message 
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un nouveau rapport</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Include Tailwind CSS -->
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
</head>
<body class="bg-gray-50 min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-4">Créer un nouveau rapport</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 space-y-6">
                <!-- Titre du rapport -->
                <div>
                    <label for="rap_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nom du rapport:
                    </label>
                    <input 
                        type="text" 
                        id="rap_name" 
                        name="rap_name" 
                        class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required
                    >
                </div>

                <!-- Date du rapport -->
                <div>
                    <label for="rap_date" class="block text-sm font-medium text-gray-700 mb-1">
                        📅 Date du rapport:
                    </label>
                    <input 
                        type="date" 
                        id="rap_date" 
                        name="rap_date" 
                        value="<?php echo date('Y-m-d'); ?>"
                        class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm cursor-pointer"
                    >
                </div>

                <!-- Description du rapport -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description du rapport:
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="5" 
                        class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required
                    ></textarea>
                </div>

                <!-- Fichiers joints -->
                <div>
                    <label for="files" class="block text-sm font-medium text-gray-700 mb-1">
                        Joindre des fichiers (facultatif):
                    </label>
                    <input 
                        type="file" 
                        id="files" 
                        name="files[]" 
                        multiple
                        class=" cursor-pointer block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    >
                    <small class="text-xs text-gray-500 mt-1 block">
                        الملفات المسموحة: pdf, png, jpeg, jpg, mp4, mp3, docx, sql. الحد الأقصى للحجم: 10 ميجابايت.
                    </small>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-x-4">
                <a 
                    href="javascript:history.back()" 
                    class=" back-button inline-flex items-center gap-x-1.5 rounded-md bg-white px-5 py-3 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                >
                    Annuler
                </a>
                <button 
                    type="submit" 
                    name="create_rapport" 
                    class="text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900 font-medium rounded-lg text-sm px-6 py-3 text-center  cursor-pointer"
                >
                    Créer rapport
                </button>
            </div>
        </form>
    </div>

    <script>
        // Optional: Add client-side validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                const rapName = document.getElementById('rap_name').value;
                const description = document.getElementById('description').value;
                
                if (!rapName.trim() || !description.trim()) {
                    event.preventDefault();
                    alert('يرجى ملء جميع الحقول المطلوبة');
                }
            });
        });
    </script>
</body>
</html>