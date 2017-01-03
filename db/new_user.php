<?php
  /*****New User Function*****
  * This function is meant to add a new user
  * it takes 3 parameters user name,password and admin key
  * returns a json object as:
  * {
      status:integer,
      message:string
    }
  * status is an enumnumerated integer that has 3 values
  * 1=success
  * 2=error
  * 3=fatal error
  */
  header('Content-Type: application/json');
  require("connect.php");
  $data=["status"=>1,"message"=>"User Created Successfully"];
  if($conn->connect_error){
    $data["status"]=3;
    $data["message"]="Database Connection Failed";
    die(json_encode($data));
  }
  if(!(isset($_POST["key"]) && isset($_POST["user"]) && isset($_POST["pass"]) ) ){ //is some values are not sent by client
    die("NOT A PROPER CILENT");
  }

  $key=$_POST["key"];
  $name=$_POST["user"];
  $pass=$_POST["pass"];

  if(strpos($key,"'")!==false || strpos($name,"'")!==false || strpos($pass,"'")!==false){
    $data["status"]=3;
    $data["message"]="possible SQL Injection";
    die(json_encode($data));
  }

  $query="SELECT * FROM admin_key where md5_key='".$key."';";
  $result = $conn->query($query);

  if ($result->num_rows == 0) {
    $data["status"]=2;
    $data["message"]="Admin Key Doesnt Exist";
    die(json_encode($data));
  }

  $query="SELECT * FROM users where user_name='".$name."' limit 1;";
  $result = $conn->query($query);

  if ($result->num_rows != 0) {
    $data["status"]=2;
    $data["message"]="Username Exists";
    die(json_encode($data));
  }

  $query="INSERT INTO users(user_name,md5_password) values('".$name."','".$pass."')";
  if(! $conn->query($query)){
    $data["status"]=2;
    $data["message"]="SQL Error";
    die(json_encode($data));
  }
  echo json_encode($data);
?>
