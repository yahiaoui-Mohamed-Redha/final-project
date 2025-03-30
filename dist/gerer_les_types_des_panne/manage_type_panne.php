<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('location: index.php');
    exit();
}

// Fetch all proposed types from the database
$stmt_proposed = $conn->prepare("SELECT * FROM propos_type WHERE status = 'pending'");
$stmt_proposed->execute();
$proposed_types = $stmt_proposed->fetchAll(PDO::FETCH_ASSOC);

// Fetch all Type_panne from the database
$stmt = $conn->prepare("SELECT * FROM Type_panne");
$stmt->execute();
$type_pannes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for success message from redirect
$success_message = $_GET['success'] ?? null;
?>

<div class="container mx-auto px-4 py-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Manage Type_panne</h1>
    </header>

    <!-- Success message -->
    <?php if ($success_message) { ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php } ?>

    <!-- Table at the top -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Type Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($type_pannes as $type_panne) { ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($type_panne['type_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($type_panne['description']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="space-x-4">
                                <a href="modifier_type_panne.php?id=<?php echo $type_panne['type_id']; ?>" class="text-indigo-600 hover:text-indigo-900">Modify</a>
                                <a href="../app/delete_type_panne.php?id=<?php echo $type_panne['type_id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this type?');">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Two-column layout for forms -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Left column - Create new type form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Create New Type_panne</h2>
            <form method="POST" action="../app/create_type_panne.php" class="space-y-4">
                <div>
                    <label for="type_name" class="block text-sm font-medium text-gray-700 mb-1">Type Name:</label>
                    <input type="text" name="type_name" id="type_name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description:</label>
                    <textarea name="description" id="description" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                
                <button type="submit" name="create_type" 
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Create
                </button>
            </form>
        </div>

        <!-- Right column - Proposed types list -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Proposed Type_panne</h2>
            <div class="space-y-4">
                <?php if (empty($proposed_types)): ?>
                    <p class="text-gray-500">No proposed types pending approval.</p>
                <?php else: ?>
                    <?php foreach ($proposed_types as $proposed_type): ?>
                    <?php
                    $receveur_id = $proposed_type['receveur_id'];
                    $stmt_receveur = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                    $stmt_receveur->execute([$receveur_id]);
                    $receveur = $stmt_receveur->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($proposed_type['type_name']); ?></h3>
                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($proposed_type['description']); ?></p>
                        <p class="text-xs text-gray-500 mt-2">Proposed by: <?php echo htmlspecialchars($receveur['username']); ?></p>
                        <div class="flex space-x-2 mt-3">
                            <a href="../app/approve_type_panne.php?id=<?php echo $proposed_type['propos_id']; ?>" 
                               class="flex-1 py-1 px-3 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 text-center">
                                Approve
                            </a>
                            <a href="../app/reject_type_panne.php?id=<?php echo $proposed_type['propos_id']; ?>" 
                               class="flex-1 py-1 px-3 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 text-center"
                               onclick="return confirm('Are you sure you want to reject this proposed type?');">
                                Reject
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>