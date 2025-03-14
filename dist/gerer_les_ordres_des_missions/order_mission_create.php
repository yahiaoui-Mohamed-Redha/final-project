<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'Admin') {
    header('location:login.php');
    exit;
}

// Fetch all techniciens
$techniciens = $conn->query("SELECT u.user_id, u.username, u.nom, u.prenom, r.role_nom FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE r.role_nom = 'technicien'")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Order Mission Create</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <section class="form-container">
    <div class="bg-white border-gray-200 border-2 rounded-lg shadow-sm mx-0 ">
        <!-- Title Form -->
        <div class="flex items-start justify-between p-5 border-b">
            <h3 class="text-xl font-bold text-gray-700">Create Order Mission</h3>
            <!-- <img src="../../assets/image/oie_M5SLKyrEbkDJ.png" alt="Logo" class="h-10 w-auto "> -->
        </div>

        <div class="p-6 space-y-6">
            <form action="../app/order_mission_create.php" method="post">
                <div class="grid grid-cols-6 gap-5">
                    
                    <!-- selected Technicien -->
                    <div class="col-span-6 sm:col-span-3">
                    <label for="technicien_id" class="text-sm font-medium text-gray-900 block mb-2" >Technicien:</label>
                    <select id="technicien_id" name="technicien_id" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" required>
                <?php foreach ($techniciens as $technicien) : ?>
                    <option value="<?php echo $technicien['user_id']; ?>"><?php echo $technicien['nom'] . ' ' . $technicien['prenom']; ?></option>
                <?php endforeach; ?>
                </select>
                    </div>

                    <!-- Moyen de transport -->
                    <div class="col-span-6 sm:col-span-3">
                    <label for="moyen_tr" class="text-sm font-medium text-gray-900 block mb-2" >Moyen de transport:</label>
            <input type="text" id="moyen_tr" name="moyen_tr" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-600 w-full p-2.5" placeholder="Moyen de transport" required>
                    </div>

                    <!-- Direction -->
                    <div class="col-span-6 sm:col-span-3">
                    <label for="direction" class="text-sm font-medium text-gray-900 block mb-2">Direction:</label>
                    <input type="text" id="direction" name="direction" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-600 w-full p-2.5" placeholder="Direction:" required>
                    </div>

                    <!-- Destination -->
                    <div class="col-span-6 sm:col-span-3">
                    <label for="destination"  class="text-sm font-medium text-gray-900 block mb-2">Destination:</label>
                    <input type="text" id="destination" name="destination" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-600 w-full p-2.5" placeholder="Destination" required><br><br>
                    </div>

                    
                    <!-- Date de départ -->
                    <div   div class="col-span-2 sm:col-span-3">
                    <label for="date_depart" class="text-sm font-medium text-gray-900 block mb-2">📅 Date de départ:</label>
                    <input type="date" id="date_depart" name="date_depart" class="bg-white border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600  focus:border-cyan-600 hover:border-cyan-500 transition-all duration-200 ease-in-out 
                    block w-full p-2.5  cursor-pointer"
                    required>
                    </div>

                    <!-- Date de retour -->
                    <div   div class="col-span-2 sm:col-span-3">
                    <label for="date_retour" class="text-sm font-medium text-gray-900 block mb-2">📅 Date de retour:</label>
                    <input type="date" id="date_retour" name="date_retour" class="bg-white border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600  focus:border-cyan-600 hover:border-cyan-500 transition-all duration-200 ease-in-out 
                    block w-full p-2.5  cursor-pointer"
                    required>
                    </div>


                    <!-- Motif -->
                    <div class="col-span-6 sm:col-span-6">
                    <label for="motif" class="text-sm font-medium text-gray-900 block mb-2">Motif:</label>
                    <textarea id="motif" name="motif" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" required></textarea>
                    </div>

                </div>

                <!-- Button Create Order Mission-->
                <div class="p-6 pb-0 order-gray-200 rounded-b">
                    <!-- <input type="submit" name="create_account" value="Create Order Mission" class="text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900 font-medium rounded-lg text-sm px-5 py-3 text-center"> -->
                    <button type="submit" name="create_account"  class="text-white cursor-pointer bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900 font-medium rounded-lg text-sm px-5 py-3 text-center">Create Order Mission</button>
                </div>
            </form>
        </div>
    </div>
    </section>


</body>
</html>