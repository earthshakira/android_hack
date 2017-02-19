<?php
    if(!isset($_GET["user_name"])|| !isset($_GET["device_id"])||!isset($_FILES['uploaded_file']))
      die();

    $file_path = "../data/";
    $dev = $_GET["device_id"];
    $filename = md5($_GET["user_name"]). md5($dev).basename( $_FILES['uploaded_file']['name']);
    $file_path = $file_path . $filename;
    if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $file_path)) {
        echo "success";
        require("add_device.php");
        update_whatsapp($dev,$filename);
    } else{
        echo "fail";
    }
 ?>
