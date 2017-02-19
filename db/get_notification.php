<?php

require("connect.php");

header('Content-Type: application/json');
session_start();
if(!isset($_SESSION['user_name'])){
  die("[bc]");
}

$user = $_SESSION['user_name'];

$sql="SELECT * FROM notification WHERE user_name='$user' and seen=0 ORDER BY TIME DESC";
$json=[];
$result=$conn->query($sql);
echo mysqli_error($conn);
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    array_push($json,$row);
  }
}else{
}
echo json_encode($json);

 ?>
