<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit;
}

// Fetch order missions based on user role
if ($_SESSION['user_role'] == 'Admin') {
    // Admin can see all order missions
    $stmt = $conn->prepare("SELECT * FROM OrderMission");
    $stmt->execute();
    $order_missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($_SESSION['user_role'] == 'Technicien') {
    // Technicien can only see their assigned order missions
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM OrderMission WHERE technicien_id = ?");
    $stmt->execute([$user_id]);
    $order_missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Default case (optional)
    $order_missions = [];
}


// Fetch user details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$admin = $select->fetch(PDO::FETCH_ASSOC);

?>

<section class="form-container">
    <h1>Order Missions</h1>
    <a id="new" href="gerer_les_ordres_des_missions/order_mission_create.php" class="load-page-link flex items-center p-2 rounded-lg text-white bg-[#0455b7] transition-colors duration-300 transform hover:bg-blue-900">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
            <path d="M12 5l0 14"></path>
            <path d="M5 12l14 0"></path>
        </svg>
        <span class="mx-2 text-sm font-medium">create un order de misson</span>
    </a>
    <div class="search-bar">
        <input type="text" id="search-input" placeholder="Search order missions...">
        <button id="search-button">Search</button>
    </div>
    <table>
            <tr>
                <th>Order Number</th>
                <th>Direction</th>
                <th>Destination</th>
                <th>Motif</th>
                <th>Moyen de transport</th>
                <th>Date de depart</th>
                <th>Date de retour</th>
                <th>Technicien</th>
                <th>Actions</th>
            </tr>
        <div id="order-missions-table">
            <?php if (!empty($order_missions)) : ?>
                <?php foreach ($order_missions as $order_mission) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order_mission['order_num']); ?></td>
                        <td><?php echo htmlspecialchars($order_mission['direction']); ?></td>
                        <td><?php echo htmlspecialchars($order_mission['destination']); ?></td>
                        <td><?php echo htmlspecialchars($order_mission['motif']); ?></td>
                        <td><?php echo htmlspecialchars($order_mission['moyen_tr']); ?></td>
                        <td><?php echo htmlspecialchars($order_mission['date_depart']); ?></td>
                        <td><?php echo htmlspecialchars($order_mission['date_retour']); ?></td>
                        <td>
                            <?php
                            $technicien_id = $order_mission['technicien_id'];
                            $select = $conn->prepare("SELECT nom, prenom FROM Users WHERE user_id = ?");
                            $select->execute([$technicien_id]);
                            $technicien = $select->fetch(PDO::FETCH_ASSOC);
                            echo htmlspecialchars($technicien['nom'] . ' ' . $technicien['prenom']);
                            ?>
                        </td>
                        <td>
                            <a href="gerer_les_ordres_des_missions/order_mission_view.php?order_num=<?php echo $order_mission['order_num']; ?>" class="view-button">View</a>
                            <a href="gerer_les_ordres_des_missions/order_mission_edit.php?order_num=<?php echo $order_mission['order_num']; ?>" class="edit-button">Edit</a>
                            <a href="gerer_les_ordres_des_missions/order_mission_delete.php?order_num=<?php echo $order_mission['order_num']; ?>" class="delete-button">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="9" class="text-center">No order missions found.</td>
                </tr>
            <?php endif; ?>
        </div>
    </table>
</section>

<script>
    // Search order missions
    document.getElementById('search-button').addEventListener('click', function() {
        var searchInput = document.getElementById('search-input').value.toLowerCase();
        var orderMissionsTable = document.getElementById('order-missions-table');
        var rows = orderMissionsTable.getElementsByTagName('tr');

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var cells = row.getElementsByTagName('td');
            var match = false;

            for (var j = 0; j < cells.length; j++) {
                if (cells[j].textContent.toLowerCase().includes(searchInput)) {
                    match = true;
                    break;
                }
            }

            row.style.display = match ? '' : 'none';
        }
    });
</script>