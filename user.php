<?php
  session_start();

  if(!isset($_SESSION['user_name'])){
      header('Location: /index.php');
      exit();
  }
?>

<!DOCTYPE html>
<html>
 <head>
   <meta charset="UTF-8">
   <title><?php echo $_SESSION["user_name"]; ?>'s Home</title>
   <link rel="stylesheet" href="./bootstrap/css/bootstrap.css" >
   <link rel="stylesheet" type="text/css" href="./css/theme.css" >
   <script type="text/javascript" src="js/jquery.js"></script>
   <script type="text/javascript" src="js/jquery.cookie.js"></script>
   <script type="text/javascript" src="js/main.js"></script>
   <script type="text/javascript" src="js/md5.js"></script>
 </head>
 <body class="main-bg">
   <div class="container">
     <div class="jumbotron" style="margin-top:40px">
       <h1>Welcome <?php echo  $_SESSION["user_name"]; ?>!</h1>
       <p>You will see the actual page when it is developed</p>
       <?php if($_SESSION["user_name"]=="super_admin"){
          echo "<a class=\"btn btn-warning\" href=\"./register.php\" >Add New User</a>";
       } ?>
       <button class="btn btn-success" onclick="logout()">Logout</button>
     </div>
   </div>
 </body>
</html>
