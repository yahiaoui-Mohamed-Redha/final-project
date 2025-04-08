<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'Admin') {
    header('Location: login.php');
    exit;
}

// Create a cache file to store the postal codes and their corresponding etablissement_name values
$cache_file = 'postal_codes.cache';

// Check if the cache file exists
if (file_exists($cache_file)) {
    // If the cache file exists, load the data from it
    $postal_codes = unserialize(file_get_contents($cache_file));
} else {
    // If the cache file does not exist, fetch the data from the database and store it in the cache file
    $stmt = $conn->prepare("SELECT postal_code, etablissement_name FROM epost");
    $stmt->execute();
    $postal_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents($cache_file, serialize($postal_codes));
}


// manage_users.php
function execute_javascript() {
    ?>
    <script>
        function toggleFields() {
            const accountType = document.querySelector('select[name="account_type"]').value;
            const postalCodeField = document.getElementById('postal_code_field');
            const gradeField = document.getElementById('grade_field');

            if (accountType === 'technicien') {
                postalCodeField.style.display = 'none';
                gradeField.style.display = 'block';
            } else if (accountType === 'receveur') {
                postalCodeField.style.display = 'block';
                gradeField.style.display = 'none';
            } else {
                postalCodeField.style.display = 'none';
                gradeField.style.display = 'none';
            }
        }

        // Call the function on page load and when the account type changes
        window.onload = toggleFields;
        document.querySelector('select[name="account_type"]').addEventListener('change', toggleFields);
    </script>
    <?php
}

// Call the function in your PHP file
execute_javascript();



?>
<body class="bg-gray-100">
<!-- Success Alert -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm shadow-sm transition-all duration-300 ease-in-out border-l-4 border-green-500 flex items-center"  role="alert">
        <div class="flex-shrink-0 mr-3">
            <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="flex-1 text-green-800 font-medium">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
        <button type="button" class="ml-auto inline-flex text-green-600 hover:text-green-800 focus:outline-none" onclick="this.parentElement.style.display='none';">
            <svg class="h-4 w-4 cursor-pointer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
<?php endif; ?>

<!-- Error Alert -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm shadow-sm transition-all duration-300 ease-in-out border-l-4 border-red-500 flex items-center" role="alert">
        <div class="flex-shrink-0 mr-3">
            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="flex-1 text-red-800 font-medium">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
        <button type="button" class="ml-auto inline-flex text-red-600 hover:text-red-800 focus:outline-none" onclick="this.parentElement.style.display='none';">
            <svg class="h-4 w-4 cursor-pointer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
<?php endif; ?>
<main>
    <div class="bg-white border-gray-200 border-2 rounded-lg shadow-sm mx-0 ">
        <!-- Title Form -->
        <div class="flex items-start justify-between p-5 border-b">
            <h3 class="text-xl font-bold text-gray-700">Créer un Nouveau Compte</h3>
            <!-- <img src="../../assets/image/oie_M5SLKyrEbkDJ.png" alt="Logo" class="h-10 w-auto "> -->
        </div>

        <div class="p-6 space-y-6">
            <form action="../app/create_users.php"  id="createAccountForm" method="post">
                <div class="grid grid-cols-6 gap-5">
                    
                    <!-- selected account -->
                    <div class="col-span-6 sm:col-span-3">
                        <label for="account_type" class="text-sm font-medium text-gray-900 block mb-2">Type de Compte</label>
                        <select name="account_type" required onchange="toggleFields()" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5">
                            <option disabled  value="">Sélectionnez le type</option>
                            <option value="technicien">Technicien</option>
                            <option value="receveur">Receveur</option>
                        </select>
                    </div>

                    <!-- Postal Code Field (for Receveur) -->
                    <div id="postal_code_field" style="display: none;" class="col-span-6 sm:col-span-3" >
                        <label for="postal_code" class="text-sm font-medium text-gray-900 block mb-2">Postal Code:</label>
                        <select name="postal_code" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5">
                            <option value="">Select a Postal Code</option>
                            <?php
                                foreach ($postal_codes as $postal) {
                                    echo '<option value="' . htmlspecialchars($postal['postal_code']) . '">' . htmlspecialchars($postal['postal_code']) . ' - ' . htmlspecialchars($postal['etablissement_name']) . '</option>';
                                }
                            ?>
                        </select>
                    </div>

                    <!-- Grade Field (for Technicien) -->
                    <div id="grade_field" class="col-span-6 sm:col-span-3" >
                        <label for="grade" class="text-sm font-medium text-gray-900 block mb-2">Grade:</label>
                        <input type="text" name="grade" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-600 w-full p-2.5">
                    </div>

                    <!-- First Name -->
                    <div class="col-span-6 sm:col-span-3">
                        <label for="nom" class="text-sm font-medium text-gray-900 block mb-2">Nom</label>
                        <input type="text" name="nom" required class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" placeholder="Nom">
                    </div>

                    <!-- Last Name -->
                    <div class="col-span-6 sm:col-span-3">
                        <label for="prenom" class="text-sm font-medium text-gray-900 block mb-2">Prenom</label>
                        <input type="text" name="prenom" required class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" placeholder="Prenom">
                    </div>

                    <!-- the e-mail address -->
                    <div class="col-span-6 sm:col-span-3">
                        <label for="email" class="text-sm font-medium text-gray-900 block mb-2">E-mail</label>
                        <input type="email" name="email" required class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" placeholder="e-mail">
                        <?php if (isset($error)) { echo '<span style="color: red;">' . $error . '</span>'; } ?>
                    </div>

                    <!-- Mobile phone number -->
                    <div class="col-span-6 sm:col-span-3">
                        <label for="user_mobile" class="text-sm font-medium text-gray-900 block mb-2">Utilisateur Mobile</label>
                        <input type="text" name="user_mobile" required class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" placeholder="Utilisateur Mobile">
                    </div>

                    <!-- telephone Fixe-->
                    <div class="col-span-6 sm:col-span-3">
                        <label for="user_fixe" class="text-sm font-medium text-gray-900 block mb-2">Utilisateur Fixe</label>
                        <input type="text" name="user_fixe" required class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" placeholder="Utilisateur Fixe">
                    </div>

                    <!-- Password -->
                    <div class="col-span-6 sm:col-span-3">
                        <label for="password" class="text-sm font-medium text-gray-900 block mb-2">Mot de Passe</label>
                        <input type="password" name="password" required class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" placeholder="Mot de Passe">
                    </div>

                </div>

                <!-- Button Create Account-->
                <div class="p-6 pb-0 order-gray-200 rounded-b">
                    <input type="submit" name="create_account" value="Créer un Compte" class="text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900 font-medium rounded-lg text-sm px-5 py-3 text-center cursor-pointer">
                </div>
            </form>
        </div>
    </div>
</main>

</body>