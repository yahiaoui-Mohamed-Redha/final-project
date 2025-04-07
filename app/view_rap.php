<?php
include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location: index.php');
    exit();
}

// Check if rapport number is provided
if (!isset($_GET['rap_num']) || empty($_GET['rap_num'])) {
    $_SESSION['error'] = "Numéro de rapport non spécifié";
    header('location: ../admin/rapports.php');
    exit();
}

$rap_num = $_GET['rap_num'];

// Fetch rapport details
$stmt = $conn->prepare("SELECT r.*, u.nom, u.prenom, u.email, u.user_mobile 
                        FROM Rapport r 
                        INNER JOIN Users u ON r.user_id = u.user_id 
                        WHERE r.rap_num = ?");
$stmt->execute([$rap_num]);
$rapport = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if rapport exists
if (!$rapport) {
    $_SESSION['error'] = "Rapport non trouvé";
    header('Location: ' . $_SERVER['HTTP_REFERER']);;
    exit();
}

// Get current user details
$user_id = $_SESSION['user_id'];
$select = $conn->prepare("SELECT u.*, r.role_nom AS role_name FROM Users u INNER JOIN Roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$select->execute([$user_id]);
$user = $select->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Rapport</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Détails du Rapport</h1>
            <a href="javascript:history.back()"  class=" back-button px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">Retour</a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between border-b pb-4 mb-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($rapport['rap_name']); ?></h2>
                    <p class="text-sm text-gray-500">Rapport #<?php echo htmlspecialchars($rapport['rap_num']); ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">date de création: <?php echo htmlspecialchars($rapport['rap_date']); ?></p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Informations du Rapport</h3>
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="mb-3">
                            <p class="text-sm text-gray-500">Description:</p>
                            <p class="font-medium"><?php echo nl2br(htmlspecialchars($rapport['description'])); ?></p>
                        </div>

                        <?php if (isset($rapport['status'])): ?>
                        <div class="mb-3">
                            <p class="text-sm text-gray-500">Statut:</p>
                            <p class="font-medium">
                                <?php 
                                $status_class = '';
                                switch($rapport['status']) {
                                    case 'Nouveau':
                                        $status_class = 'bg-blue-100 text-blue-800';
                                        break;
                                    case 'En cours':
                                        $status_class = 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'Complété':
                                        $status_class = 'bg-green-100 text-green-800';
                                        break;
                                    default:
                                        $status_class = 'bg-gray-100 text-gray-800';
                                }
                                ?>
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($rapport['status']); ?>
                                </span>
                            </p>
                        </div>
                        <?php endif; ?>

                        
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Information de l'Utilisateur</h3>
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="mb-3">
                            <p class="text-sm text-gray-500">Nom:</p>
                            <p class="font-medium"><?php echo htmlspecialchars($rapport['nom'] . ' ' . $rapport['prenom']); ?></p>
                        </div>
                        <?php if (isset($rapport['email'])): ?>
                        <div class="mb-3">
                            <p class="text-sm text-gray-500">Email:</p>
                            <p class="font-medium"><?php echo htmlspecialchars($rapport['email']); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($rapport['phone'])): ?>
                        <div>
                            <p class="text-sm text-gray-500">Téléphone:</p>
                            <p class="font-medium"><?php echo htmlspecialchars($rapport['user_mobile']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($_SESSION['user_role'] === 'Admin'): ?>
            <div class="mt-6 flex gap-3">
                <a href="../app/edit_rap.php?rap_num=<?php echo $rapport['rap_num']; ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Modifier</a>
                <a href="../app/delete_rap.php?rap_num=<?php echo $rapport['rap_num']; ?>" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce rapport?')">Supprimer</a>
                <a href="../app/rapport_export.php?rap_num=<?php echo $rapport['rap_num']; ?>" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Exporter</a>
            </div>
            <?php endif; ?>
        </div>

        <?php if (isset($rapport['notes']) && !empty($rapport['notes'])): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-700 mb-3">Notes supplémentaires</h3>
            <div class="bg-gray-50 p-4 rounded">
                <?php echo nl2br(htmlspecialchars($rapport['notes'])); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Additional JavaScript for interactions if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Any client-side functionality can be added here
        });
    </script>
</body>
</html>