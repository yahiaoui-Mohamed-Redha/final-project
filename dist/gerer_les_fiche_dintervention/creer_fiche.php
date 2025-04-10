<?php
include '../../app/config.php';
session_start();

// Verify user authorization - only Receveurs can create fiches
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Receveur') {
    header('Location:../../index.php');
    exit;
}

// Fetch all available technicians
$techniciens = [];
try {
    $stmt = $conn->prepare("SELECT user_id, nom, prenom FROM Users WHERE role_id = (SELECT role_id FROM Roles WHERE role_nom = 'Technicien')");
    $stmt->execute();
    $techniciens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des techniciens: " . $e->getMessage();
}

// Fetch all pannes for the current receveur
$pannes = [];
try {
    $stmt = $conn->prepare("SELECT panne_num, panne_name FROM Panne WHERE receveur_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $pannes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des pannes: " . $e->getMessage();
}
?>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple {
            min-height: 42px;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #4f46e5;
            border-color: #4f46e5;
            color: white;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 5px;
        }
        .compte-rendu-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .suggestion-tag {
            display: inline-block;
            background-color: #e0e7ff;
            color: #4f46e5;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            cursor: pointer;
        }
        .suggestion-tag:hover {
            background-color: #c7d2fe;
        }
    </style>
    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-4">Créer une fiche d'intervention</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md flex justify-between items-center">
                <span><?= $_SESSION['success_message'] ?></span>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    &times;
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md flex justify-between items-center">
                <span><?= $_SESSION['error_message'] ?></span>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    &times;
                </button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <form method="POST" action="../app/creer_fiche.php" class="space-y-6">
            <input type="hidden" name="receveur_id" value="<?= $_SESSION['user_id'] ?>">
            
            <!-- Panne associée -->
            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                <label for="panne_num" class="block text-sm font-medium text-gray-700 mb-2">Panne associée *</label>
                <select name="panne_num" id="panne_num" class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <option value="">Sélectionner une panne</option>
                    <?php foreach ($pannes as $panne): ?>
                        <option value="<?= $panne['panne_num'] ?>"><?= htmlspecialchars($panne['panne_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Techniciens assignés -->
            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                <label for="techniciens" class="block text-sm font-medium text-gray-700 mb-2">Techniciens assignés *</label>
                <select name="techniciens[]" id="techniciens" class="block w-full" multiple required>
                    <?php foreach ($techniciens as $technicien): ?>
                        <option value="<?= $technicien['user_id'] ?>">
                            <?= htmlspecialchars($technicien['nom'] . ' ' . $technicien['prenom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-sm text-gray-500 mt-2">Maintenez Ctrl (Windows) ou Cmd (Mac) pour sélectionner plusieurs techniciens</p>
            </div>
            
            <!-- Compte rendu -->
            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                <label for="compte_rendu" class="block text-sm font-medium text-gray-700 mb-2">Compte rendu *</label>
                <div class="compte-rendu-suggestions">
                    <span class="suggestion-tag" onclick="insertSuggestion('compte_rendu', 'Réparation effectuée')">Réparation effectuée</span>
                    <span class="suggestion-tag" onclick="insertSuggestion('compte_rendu', 'Diagnostic complet')">Diagnostic complet</span>
                    <span class="suggestion-tag" onclick="insertSuggestion('compte_rendu', 'Pièce remplacée')">Pièce remplacée</span>
                    <span class="suggestion-tag" onclick="insertSuggestion('compte_rendu', 'Nettoyage effectué')">Nettoyage effectué</span>
                </div>
                <textarea name="compte_rendu" id="compte_rendu" class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" rows="4" required></textarea>
            </div>
            
            <!-- Observations -->
            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                <label for="observation" class="block text-sm font-medium text-gray-700 mb-2">Observations</label>
                <div class="compte-rendu-suggestions">
                    <span class="suggestion-tag" onclick="insertSuggestion('observation', 'Matériel usagé')">Matériel usagé</span>
                    <span class="suggestion-tag" onclick="insertSuggestion('observation', 'Besoin de pièces détachées')">Besoin de pièces détachées</span>
                    <span class="suggestion-tag" onclick="insertSuggestion('observation', 'Problème récurrent')">Problème récurrent</span>
                </div>
                <textarea name="observation" id="observation" class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" rows="3"></textarea>
            </div>
            
            <div class="flex justify-end gap-x-4 pt-4">
                <a href="../fiches_intervention.php" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Annuler
                </a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Créer la fiche
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize Select2 for technicians
        $('#techniciens').select2({
            placeholder: "Sélectionnez un ou plusieurs techniciens",
            width: '100%',
            closeOnSelect: false
        });

        // Function to insert suggestion into textarea
        function insertSuggestion(fieldId, suggestion) {
            const textarea = document.getElementById(fieldId);
            const currentValue = textarea.value;
            
            if (currentValue.trim() === '') {
                textarea.value = suggestion;
            } else {
                textarea.value = currentValue + (currentValue.endsWith('.') ? ' ' : '. ') + suggestion;
            }
            
            textarea.focus();
        }

        // Form validation
        $('form').on('submit', function(e) {
            let isValid = true;
            
            // Check required fields
            $('[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('border-red-500');
                    $(this).closest('.bg-gray-50').addClass('border-red-500');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires');
                $('html, body').animate({
                    scrollTop: $('.border-red-500').first().offset().top - 100
                }, 500);
            }
        });
    });
    </script>