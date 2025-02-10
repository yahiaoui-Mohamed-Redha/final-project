<?php

include 'config.php';




if (isset($_POST['submit'])) {
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = md5($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
   $select->execute([$email]);

   if (isset($_POST['submit'])) {

      $select = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
      $select->execute([$email]);

      if ($select->rowCount() > 0) {
         $message[] = 'user already exist!';
      } else {
         if ($pass != $cpass) {
            $message[] = 'confirm password not matched!';
         } else {
            // Generate a unique 6-digit ID
            $user_id = rand(100000, 999999);

            $insert = $conn->prepare("INSERT INTO `users`(id, name, email, password) VALUES(?,?,?,?)");
            $insert->execute([$user_id, $name, $email, $cpass,]);

            if ($insert) {
                // Start the session
                session_start();
                
                // Store user information in session variables
                $_SESSION['user_id'] = $user_id; // or any other user identifier
                $_SESSION['user_name'] = $name; // Store the user's name if needed
             
                $message[] = 'registered successfully!';
                header('location:home.php'); // Redirect to home.php
                exit(); // Always good to call exit after a header redirect
            }
         }
      }
   }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>



   <?php
   if (isset($message)) {
      foreach ($message as $message) {
         echo '
         <div class="message">
            <span>' . $message . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
   ?>

   <section class="form-container">

      <form action="" method="post" enctype="multipart/form-data">
         <h3>register now</h3>
         <input type="text" required placeholder="enter your username" class="box" name="name">
         <input type="email" required placeholder="enter your email" class="box" name="email">
         <input type="password" required placeholder="enter your password" class="box" name="pass">
         <input type="password" required placeholder="confirm your password" class="box" name="cpass">
         <p>already have an account? <a href="index.php">login now</a></p>
         <input type="submit" value="register now" class="btn" name="submit">
      </form>

   </section>

</body>

</html>