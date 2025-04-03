<?php
session_start();
include 'config.php';

// تأكد من أن المستخدم مسجل الدخول وله الصلاحيات
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم
$select = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
$select->execute([$user_id]);
$user = $select->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $user_mobile = $_POST['user_mobile'];
    $user_fixe = $_POST['user_fixe'];
    
    // تحقق مما إذا تم إدخال كلمة مرور جديدة
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $update = $conn->prepare("UPDATE Users SET prenom = ?, nom = ?, email = ?, username = ?, user_mobile = ?, user_fixe = ?, password = ? WHERE user_id = ?");
        $update->execute([$prenom, $nom, $email, $username, $user_mobile, $user_fixe, $password, $user_id]);
    } else {
        $update = $conn->prepare("UPDATE Users SET prenom = ?, nom = ?, email = ?, username = ?, user_mobile = ?, user_fixe = ? WHERE user_id = ?");
        $update->execute([$prenom, $nom, $email, $username, $user_mobile, $user_fixe, $user_id]);
    }
    
    header('Location: ../dist/admin_page.php');
    exit();
}
?>
