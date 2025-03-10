<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    // Redirect to the login page or show an error message
    header('location: index.php');
    exit();
}

// Cache settings
$cache_file = '../../cache/users_cache.json'; // Cache file location
$cache_time = 432000; // 5 days in seconds

// Check if a valid cache file exists
if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
    // Load data from cache
    $users = json_decode(file_get_contents($cache_file), true);
} else {
// Fetch all reports from the database
$stmt = $conn->prepare("SELECT r.rap_name, r.rap_date, r.description, u.nom, u.prenom 
                        FROM Rapports r 
                        INNER JOIN Users u ON r.user_id = u.user_id");
$stmt->execute();
$rapports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Store the data in cache
    file_put_contents($cache_file, json_encode($users));
}

// Fetch admin details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$admin = $select->fetch(PDO::FETCH_ASSOC);

// Fetch all users from the database
$stmt = $conn->prepare("SELECT u.*, e.etablissement_name FROM Users u LEFT JOIN Epost e ON u.postal_code = e.postal_code");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

        <div class="px-6 py-8 mt-20">
            <table>
                <tr>
                    <th>اسم التقرير</th>
                    <th>تاريخ التقرير</th>
                    <th>وصف التقرير</th>
                    <th>المستلم</th>
                </tr>
                <?php foreach ($rapports as $rapport): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rapport['rap_name']); ?></td>
                        <td><?php echo $rapport['rap_date']; ?></td>
                        <td><?php echo htmlspecialchars($rapport['description']); ?></td>
                        <td><?php echo htmlspecialchars($rapport['nom'] . ' ' . $rapport['prenom']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

        </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.tab');
            const tableRows = document.querySelectorAll('table tr:not(:first-child)');

            // Function to handle tab switching
            function switchTab(activeTab) {
                // Remove active styles from all tabs
                tabs.forEach(tab => {
                    tab.classList.remove('text-[#0455b7]', 'bg-white', 'rounded-lg');
                    tab.classList.add('text-gray-600', 'rounded-xl');
                });

                // Add active styles to the clicked tab
                activeTab.classList.remove('text-gray-600', 'rounded-xl');
                activeTab.classList.add('text-[#0455b7]', 'bg-white', 'rounded-lg');

                // Filter table rows based on the selected tab
                tableRows.forEach(row => {
                    const role = row.cells[4].textContent;
                    if (activeTab.id === 'allTab') {
                        row.style.display = 'table-row';
                    } else if (activeTab.id === 'technicianTab' && role === 'Technicien') {
                        row.style.display = 'table-row';
                    } else if (activeTab.id === 'receiverTab' && role === 'Receveur') {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Event listeners for each tab
            tabs.forEach(tab => {
                tab.addEventListener('click', () => switchTab(tab));
            });

            // Initialize the first tab as active
            switchTab(tabs[0]);
        });
    </script>

</body>
</html>