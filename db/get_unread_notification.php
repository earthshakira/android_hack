<?php

require("connect.php");

header('Content-Type: application/json');
session_start();
if(!isset($_SESSION['user_name'])){
  die("[]");
}

$user = $_SESSION['user_name'];
$query="UPDATE notification SET seen=1 WHERE user_name='$user' and seen = 0";
if(! $conn->query($query)){
  echo "notification update failed";
}
 ?>
