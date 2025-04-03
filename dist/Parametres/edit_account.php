<?php
session_start();
include '../../app/config.php';

// تأكد من أن المستخدم مسجل الدخول وله الصلاحيات
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('location: ../../../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم
$select = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
$select->execute([$user_id]);
$user = $select->fetch(PDO::FETCH_ASSOC);

?>


    <div class="max-w-2xl mx-auto bg-white p-8 mt-10 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">تعديل الحساب</h2>
        <form action="../app/modifuserinf.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo isset($user['user_id']) ? htmlspecialchars($user['user_id']) : ''; ?>">
            
            <label class="block mb-2">nom</label>
            <input type="text" name="nom" value="<?php echo isset($user['nom']) ? htmlspecialchars($user['nom']) : ''; ?>" class="w-full p-2 border rounded" required>
            
            <label class="block mt-4 mb-2">prenom</label>
            <input type="text" name="prenom" value="<?php echo isset($user['prenom']) ? htmlspecialchars($user['prenom']) : ''; ?>" class="w-full p-2 border rounded" required>

            <label class="block mt-4 mb-2">username</label>
            <input type="text" name="username" value="<?php echo isset($user['username']) ? htmlspecialchars($user['username']) : ''; ?>" class="w-full p-2 border rounded" required>
            
            <label class="block mt-4 mb-2">email</label>
            <input type="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" class="w-full p-2 border rounded" required>
            
            <label class="block mt-4 mb-2">mobile</label>
            <input type="text" name="user_mobile" value="<?php echo isset($user['user_mobile']) ? htmlspecialchars($user['user_mobile']) : ''; ?>" class="w-full p-2 border rounded" >
            
            <label class="block mt-4 mb-2">password</label>
            <input type="password" name="password" class="w-full p-2 border rounded" >
            
            <button type="submit" class="mt-6 w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">حفظ التعديلات</button>
        </form>
    </div>
