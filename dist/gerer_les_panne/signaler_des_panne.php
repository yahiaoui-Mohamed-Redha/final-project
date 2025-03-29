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

// Common panne description suggestions
$description_suggestions = [
    "Souris ne fonctionne pas",
    "Écran noir",
    "Problème d'impression",
    "Clavier ne répond pas",
    "Système lent",
    "Pas de connexion Internet",
    "Logiciel ne s'ouvre pas",
    "Bruit anormal",
    "Surchauffe",
    "Fichiers corrompus"
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الإبلاغ عن الأعطال</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .suggestion-tag {
            display: inline-block;
            background-color: #e0e7ff;
            color: #4f46e5;
            padding: 2px 8px;
            margin: 2px;
            border-radius: 4px;
            font-size: 0.9em;
            cursor: pointer;
        }
        .suggestion-tag:hover {
            background-color: #c7d2fe;
        }
        .file-upload-container {
            border: 2px dashed #cbd5e1;
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        .file-upload-container.drag-over {
            border-color: #4f46e5;
            background-color: #eef2ff;
        }
        .file-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .file-preview-item {
            background-color: #f1f5f9;
            padding: 0.5rem;
            border-radius: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .remove-file {
            color: #ef4444;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-4">Signalement des pannes</h1>
        
        <?php if (isset($success_message)): ?>
            <div class='mb-4 p-3 bg-green-100 text-green-700 rounded-md'><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class='mb-4 p-3 bg-red-100 text-red-700 rounded-md'><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form action="../app/signaler_des_panne.php" method="POST" enctype="multipart/form-data">
            <div id="panne-container" class="space-y-6">
                <div class="panne-item bg-gray-50 p-5 rounded-lg border border-gray-200">
                    <div class="space-y-6">
                        <!-- Nom de la panne -->
                        <div>
                            <label for="panne_name_0" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom de la panne
                            </label>
                            <input 
                                type="text" 
                                id="panne_name_0" 
                                name="pannes[0][panne_name]"  
                                autocomplete="panne_name" 
                                class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                required
                            >
                        </div>

                        <!-- Date du signalement -->
                        <div>
                            <label for="date_signalement_0" class="block text-sm font-medium text-gray-700 mb-1">
                            📅 Date du signalement
                            </label>
                            <input 
                                type="date" 
                                id="date_signalement_0"
                                name="pannes[0][date_signalement]" 
                                value="<?php echo date('Y-m-d'); ?>" 
                                required
                                class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm cursor-pointer"
                            >
                        </div>

                        <!-- Description de la panne -->
                        <div>
                            <label for="description_0" class="block text-sm font-medium text-gray-700 mb-1">
                                Description de la panne
                            </label>
                            <div class="mb-2">
                                <span class="text-sm text-gray-500">Suggestions:</span>
                                <div id="suggestions-container-0" class="mt-1">
                                    <?php foreach ($description_suggestions as $suggestion): ?>
                                        <span class="suggestion-tag" onclick="insertSuggestion(0, '<?php echo htmlspecialchars($suggestion); ?>')">
                                            <?php echo htmlspecialchars($suggestion); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <textarea 
                                id="description_0" 
                                name="pannes[0][description]" 
                                rows="3" 
                                class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            ></textarea>
                        </div>

                        <!-- Type de panne -->
                        <div>
                            <label for="type_id_0" class="block text-sm font-medium text-gray-700 mb-1">
                                Choix du type de panne
                            </label>
                            <div class="relative">
                                <select 
                                    id="type_id_0" 
                                    name="pannes[0][type_id]" 
                                    autocomplete="type_id" 
                                    class="block w-full rounded-md border-gray-300 bg-white py-2 pl-3 pr-10 text-gray-700 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm appearance-none"
                                >
                                    <?php foreach ($type_pannes as $type): ?>
                                        <option value="<?php echo $type['type_id']; ?>">
                                            <?php echo htmlspecialchars($type['type_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-1 text-gray-500">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rapport Description -->
            <div class="mt-6 bg-gray-50 p-5 rounded-lg border border-gray-200">
                <label for="rapport_description" class="block text-sm font-medium text-gray-700 mb-1">
                    وصف التقرير (اختياري)
                </label>
                <textarea 
                    id="rapport_description" 
                    name="rapport_description" 
                    rows="3" 
                    class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="يمكنك إضافة وصف إضافي للتقرير هنا..."
                ></textarea>
            </div>

            <!-- File Upload -->
            <div class="mt-6 bg-gray-50 p-5 rounded-lg border border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    تحميل الملفات (اختياري)
                </label>
                <div class="file-upload-container" id="file-upload-container">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <div class="mt-4 flex text-sm text-gray-600">
                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                <span>تحميل ملف</span>
                                <input id="file-upload" name="files[]" type="file" multiple class="sr-only" accept=".pdf,.png,.jpeg,.jpg,.mp4,.mp3,.docx,.sql">
                            </label>
                            <p class="pl-1">أو اسحب وأفلت هنا</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">PDF, PNG, JPEG, MP4, MP3, DOCX, SQL حتى 10MB</p>
                    </div>
                </div>
                <div class="file-preview" id="file-preview"></div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-x-4">
                <button 
                    type="button" 
                    id="add-panne" 
                    class="inline-flex items-center gap-x-1.5 rounded-md mr-2 bg-white px-5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 cursor-pointer"
                >
                    <svg class="-ml-0.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Ajout d'une autre panne
                </button>
                <button 
                    type="submit" 
                    class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 cursor-pointer" 
                    name="signaler_panne"
                >
                    Signaler
                </button>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        // Function to insert suggestion into textarea
        function insertSuggestion(index, suggestion) {
            const textarea = document.getElementById(`description_${index}`);
            const currentValue = textarea.value;
            
            // If textarea is empty, just insert the suggestion
            if (currentValue.trim() === '') {
                textarea.value = suggestion;
            } else {
                // Otherwise, append the suggestion with a comma if needed
                textarea.value = currentValue + (currentValue.endsWith(',') ? ' ' : ', ') + suggestion;
            }
            
            // Focus the textarea
            textarea.focus();
        }

        // JavaScript to dynamically add more panne fields
        document.getElementById('add-panne').addEventListener('click', function() {
            const container = document.getElementById('panne-container');
            const index = container.children.length;
            
            const newPanne = document.createElement('div');
            newPanne.className = 'panne-item bg-gray-50 p-5 rounded-lg border border-gray-200';
            newPanne.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Panne ${index + 1}</h3>
                    <button type="button" class="remove-panne text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-6">
                    <!-- Nom de la panne -->
                    <div>
                        <label for="panne_name_${index}" class="block text-sm font-medium text-gray-700 mb-1">
                            Nom de la panne
                        </label>
                        <input 
                            type="text" 
                            id="panne_name_${index}" 
                            name="pannes[${index}][panne_name]"  
                            autocomplete="panne_name" 
                            class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required
                        >
                    </div>

                    <!-- Date du signalement -->
                    <div>
                        <label for="date_signalement_${index}" class="block text-sm font-medium text-gray-700 mb-1">
                            📅 Date du signalement
                        </label>
                        <input 
                            type="date" 
                            id="date_signalement_${index}"
                            name="pannes[${index}][date_signalement]" 
                            value="<?php echo date('Y-m-d'); ?>" 
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>

                    <!-- Description de la panne -->
                    <div>
                        <label for="description_${index}" class="block text-sm font-medium text-gray-700 mb-1">
                            Description de la panne
                        </label>
                        <div class="mb-2">
                            <span class="text-sm text-gray-500">Suggestions:</span>
                            <div id="suggestions-container-${index}" class="mt-1">
                                <?php foreach ($description_suggestions as $suggestion): ?>
                                    <span class="suggestion-tag" onclick="insertSuggestion(${index}, '<?php echo htmlspecialchars($suggestion); ?>')">
                                        <?php echo htmlspecialchars($suggestion); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <textarea 
                            id="description_${index}" 
                            name="pannes[${index}][description]" 
                            rows="3" 
                            class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        ></textarea>
                    </div>

                    <!-- Type de panne -->
                    <div>
                        <label for="type_id_${index}" class="block text-sm font-medium text-gray-700 mb-1">
                            Choix du type de panne
                        </label>
                        <div class="relative">
                            <select 
                                id="type_id_${index}" 
                                name="pannes[${index}][type_id]" 
                                autocomplete="type_id" 
                                class="block w-full rounded-md border-gray-300 bg-white py-2 pl-3 pr-10 text-gray-700 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm appearance-none"
                            >
                                <?php foreach ($type_pannes as $type): ?>
                                    <option value="<?php echo $type['type_id']; ?>">
                                        <?php echo htmlspecialchars($type['type_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-1 text-gray-500">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newPanne);
            
            // Add event listener to the remove button
            newPanne.querySelector('.remove-panne').addEventListener('click', function() {
                newPanne.remove();
            });
        });

        // File upload handling
        const fileUploadContainer = document.getElementById('file-upload-container');
        const fileInput = document.getElementById('file-upload');
        const filePreview = document.getElementById('file-preview');
        const maxFileSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['pdf', 'png', 'jpeg', 'jpg', 'mp4', 'mp3', 'docx', 'sql'];

        // Handle drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadContainer.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadContainer.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadContainer.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            fileUploadContainer.classList.add('drag-over');
        }

        function unhighlight() {
            fileUploadContainer.classList.remove('drag-over');
        }

        fileUploadContainer.addEventListener('drop', handleDrop, false);
        fileInput.addEventListener('change', handleFiles, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            handleFiles({ target: fileInput });
        }

        function handleFiles(e) {
            const files = e.target.files;
            filePreview.innerHTML = '';
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const fileType = file.name.split('.').pop().toLowerCase();
                
                if (!allowedTypes.includes(fileType)) {
                    alert(`Le type de fichier ${fileType} n'est pas autorisé.`);
                    continue;
                }
                
                if (file.size > maxFileSize) {
                    alert(`Le fichier ${file.name} dépasse la taille maximale de 10MB.`);
                    continue;
                }
                
                const filePreviewItem = document.createElement('div');
                filePreviewItem.className = 'file-preview-item';
                filePreviewItem.innerHTML = `
                    <span>${file.name}</span>
                    <span class="remove-file" data-index="${i}">×</span>
                `;
                filePreview.appendChild(filePreviewItem);
                
                // Add remove event
                filePreviewItem.querySelector('.remove-file').addEventListener('click', function() {
                    removeFile(i);
                });
            }
        }

        function removeFile(index) {
            const dt = new DataTransfer();
            const files = fileInput.files;
            
            for (let i = 0; i < files.length; i++) {
                if (i !== index) {
                    dt.items.add(files[i]);
                }
            }
            
            fileInput.files = dt.files;
            handleFiles({ target: fileInput });
        }
    </script>
