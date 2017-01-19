<?php
require("connect.php");
header('Content-Type: application/json');
if($conn->connect_error)
  echo "db error";
else echo "db connected";
function add_device($uname,$device_id,$device_name,$device_user,$socket){
  require("connect.php");
  $query="INSERT INTO devices values('".$uname."','".$device_id."','".$device_name."','".$device_user."','0')";
  if(! $conn->query($query)){
    echo "device error:".mysqli_error($conn);
  }else{
    echo "new device added";
  }
  $query="INSERT INTO active values('".$device_id."','".$socket."')";
  echo "adding at $socket";
  if(! $conn->query($query)){
    echo "sql error for making device active";
  }else{
    echo "device listed active";
  }
}

function disconnect_device($dev,$socket){
  require("connect.php");
  $query="DELETE FROM active WHERE socket='".$socket."';";
  if(! $conn->query($query)){
    echo "sql error for removing device";
  }else{
    echo "device removed from active";
  }
  $time=time();
  $query="update devices set last_seen='$time' WHERE device_id='".$dev."';";
  if(! $conn->query($query)){
    echo "sql error for removing device";
  }else{
    echo "time updated to $time";
  }
}

function truncate(){
  require("connect.php");
  $query="truncate active;";
  if(! $conn->query($query)){
    echo "Error for truncating active";
  }else{
    echo "Active Successful truncated";
  }
}
 ?>
