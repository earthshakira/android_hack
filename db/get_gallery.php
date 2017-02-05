<?php
require("connect.php");
require("add_device.php");

header('Content-Type: application/json');
session_start();
$user = $_SESSION['user_name'];

if(!isset($_GET['device_id'])){
  die("[]");
}
$dev=($_GET['device_id']);
$did=get_did($dev);
$sql="SELECT id,path,folder,cached FROM file_system WHERE did=$did";
$json=[];
$result=$conn->query($sql);
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    array_push($json,$row);
  }
}else{
}
echo json_encode($json);
 ?>
