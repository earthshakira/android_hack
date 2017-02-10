<?php
  $user_name=$_GET["a"];
  exec("php strings.php $user_name > ../App/network/app/src/main/res/values/strings.xml");
  $op = system("bash ./gradlebuild.sh");
 ?>
