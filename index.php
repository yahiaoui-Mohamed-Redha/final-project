<?php
include 'config.php';
session_start();

if(isset($_POST['submit'])){
    $login_input = $_POST['login_input'];
    $login_input = filter_var($login_input, FILTER_SANITIZE_STRING);
    $pass = md5($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    $select = $conn->prepare("SELECT u.*, r.role_nom FROM `Users` u INNER JOIN `Roles` r ON u.role_id = r.role_id WHERE (u.email = ? OR u.username = ?) AND u.password = ?");
    $select->execute([$login_input, $login_input, $pass]);
    $row = $select->fetch(PDO::FETCH_ASSOC);

    if($select->rowCount() > 0){
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['user_role'] = $row['role_nom'];
        // Redirect to the corresponding page based on the user's role
        if($row['role_nom'] == 'admin'){
            header('location:admin_page.php');
        } elseif($row['role_nom'] == 'technicien'){
            header('location:technicien_page.php');
        } elseif($row['role_nom'] == 'receveur'){
            header('location:receveur_page.php');
        } else{
            $message[] = 'no user found!';
        }
    } else{
        $message[] = 'incorrect email or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php
    if(isset($message)){
        foreach($message as $message){
            echo '
            <div class="message">
                <span>'.$message.'</span>
                <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
            </div>
            ';
        }
    }
?>
   
<section class="form-container">

    <form action="" method="post" enctype="multipart/form-data">
        <h3>login now</h3>
        <input type="text" required placeholder="enter your username or email" class="box" name="login_input">
        <input type="password" required placeholder="enter your password" class="box " name="pass">
        <input type="submit" value="login now" class="btn" name="submit">
    </form>

</section>

</body>
</html>