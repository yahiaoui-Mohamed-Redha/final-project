<?php
include '../../app/config.php';
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    // Redirect to the login page or show an error message
    header('location: index.php');
    exit(); // Stop further execution
}

// Handle form submission for creating a new Type_panne
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_type'])) {
    $type_name = $_POST['type_name'];
    $description = $_POST['description'];

    $insert_stmt = $conn->prepare("INSERT INTO Type_panne (type_name, description) VALUES (:type_name, :description)");
    $insert_stmt->bindParam(':type_name', $type_name);
    $insert_stmt->bindParam(':description', $description);
    $insert_stmt->execute();
}

// Handle deletion of a Type_panne
if (isset($_GET['delete_id'])) {
    $delete_stmt = $conn->prepare("DELETE FROM Type_panne WHERE type_id = :id");
    $delete_stmt->bindParam(':id', $_GET['delete_id']);
    $delete_stmt->execute();
    header('Location: manage_type_panne.php');
    exit;
}

// Handle approval of a proposed Type_panne
if (isset($_GET['approve_id'])) {
    $approve_stmt = $conn->prepare("SELECT * FROM propos_type WHERE propos_id = :id");
    $approve_stmt->bindParam(':id', $_GET['approve_id']);
    $approve_stmt->execute();
    $proposed_type = $approve_stmt->fetch(PDO::FETCH_ASSOC);

    if ($proposed_type) {
        $insert_stmt = $conn->prepare("INSERT INTO Type_panne (type_name, description) VALUES (:type_name, :description)");
        $insert_stmt->bindParam(':type_name', $proposed_type['type_name']);
        $insert_stmt->bindParam(':description', $proposed_type['description']);
        $insert_stmt->execute();

        $update_stmt = $conn->prepare("UPDATE propos_type SET status = 'approved' WHERE propos_id = :id");
        $update_stmt->bindParam(':id', $_GET['approve_id']);
        $update_stmt->execute();
    }
    header('Location: manage_type_panne.php');
    exit;
}

// Handle rejection of a proposed Type_panne
if (isset($_GET['reject_id'])) {
    $delete_stmt = $conn->prepare("DELETE FROM propos_type WHERE propos_id = :id");
    $delete_stmt->bindParam(':id', $_GET['reject_id']);
    $delete_stmt->execute();
    header('Location: manage_type_panne.php');
    exit;
}

// Fetch all Type_panne from the database
$stmt = $conn->prepare("SELECT * FROM Type_panne");
$stmt->execute();
$type_pannes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
    <style>
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .form-container h2 {
            margin-top: 0;
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-container input[type="text"],
        .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        
        .form-container button {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #333;
            color: #fff;
        }
        table tr:hover {
            background-color: #f5f5f5;
        }
        .actions a {
            color: #007bff;
            text-decoration: none;
            margin-right: 10px;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .actions a.delete {
            color: #dc3545;
        }
    </style>

    <header>
        <h1>Manage Type_panne</h1>
    </header>
    <main>
        <!-- Form to create a new Type_panne -->
        <div class="form-container">
            <h2>Create New Type_panne</h2>
            <form method="POST">
                <label for="type_name">Type Name:</label>
                <input type="text" name="type_name" id="type_name" required>

                <label for="description">Description:</label>
                <textarea name="description" id="description" required></textarea>

                <button type="submit" name="create_type">Create</button>
            </form>
        </div>

        <!-- Table to display all Type_panne -->
        <h2>All Type_panne</h2>
        <table>
            <tr>
                <th>Type Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($type_pannes as $type_panne) { ?>
            <tr>
                <td><?php echo htmlspecialchars($type_panne['type_name']); ?></td>
                <td><?php echo htmlspecialchars($type_panne['description']); ?></td>
                <td class="actions">
                    <a href="modifier_type_panne.php?id=<?php echo $type_panne['type_id']; ?>">Modify</a>
                    <a href="manage_type_panne.php?delete_id=<?php echo $type_panne['type_id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this type?');">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </table>

        <!-- Table to display proposed Type_panne -->
        <h2>Proposed Type_panne</h2>
        <a href="approve_type_panne.php">View Proposed Types</a>
    </main>