<?php
// remove all session variables
session_start();
echo "hello:".$_SESSION["user_name"]."\n";
if(!isset($_SESSION["user_name"])){
  die();
}
session_unset();
session_destroy();
?>
