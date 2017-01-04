<?php
  header('Content-Type: application/json');
  require("connect.php");
  $data=["status"=>1,"message"=>""];

  if($conn->connect_error){
    $data["status"]=3;
    $data["message"]="Database Connection Failed";
    die(json_encode($data));
  }

  if(!isset($_POST['user']) || !isset($_POST['md5_pass'])){
    die("BAD CALL");
  }

  $name=$_POST["user"];
  $pass=$_POST["md5_pass"];

  if(strpos($name,"'")!==false || strpos($pass,"'")!==false){
    $data["status"]=3;
    $data["message"]="possible SQL Injection";
    die(json_encode($data));
  }

  $sql="SELECT user_name FROM users WHERE user_name='".$name."' AND md5_password='".$pass."' LIMIT 1";
  $result=$conn->query($sql);
  if($result->num_rows > 0){
    $data["status"]=1;
    $data["message"]="Login Successful";
    session_start();
    $_SESSION["user_name"]=$name;
  }else{
    $data["status"]=2;
    $data["message"]="Invalid User/Password";
  }
  echo json_encode($data);
 ?>
