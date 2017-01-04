<?php
  //script to redirect an already logged in user
  session_start();
  if(isset($_SESSION["user_name"])){
    header("location: user.php");
    exit();
  }
 ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Android Exploit Tool</title>
    <link rel="stylesheet" href="./bootstrap/css/bootstrap.css" >
    <link rel="stylesheet" type="text/css" href="./css/theme.css" >
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.cookie.js"></script>
    <script type="text/javascript" src="js/main.js"></script>
    <script type="text/javascript" src="js/md5.js"></script>
  </head>
  <body class="main-bg">
    <script>
      if(($.cookie("user")) && ($.cookie("pass"))){
          var formdata={"user":$.cookie("user"),"md5_pass":$.cookie("pass")};
          $.post( "./db/check_user.php",formdata).done(function(data){
            console.log(data);
            if(data.status==1)
              window.location.href=getBaseUrl()+"user.php";
          });
      }
    </script>
    <p class="text-primary" style="float:right">earthshakira</p>
    <div class="container main-bg">
      <div class="well login">
        <h3 class="text-default text-center">Android Exploit Tool</h3>
        <img src="./img/logo.jpg" class="main-logo"/>
        <div class="form-group">
        <label>Username</label>
          <input type="email" class="form-control" placeholder="Username" id="user_name">
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" class="form-control" placeholder="Password" id="password">
        </div>
        <div class="checkbox">
          <label>
            <input type="checkbox" id="remember_me"> Remember Me
          </label>
        </div>
        <div class="alert alert-success" style="display:none;margin:20px" id="response">
          <p>
            <strong id="status">Done</strong>
            <span id="message">Added</span>
          </p>
        </div>
        <button type="submit" class="btn btn-primary center" id="submit">Submit</button>
      </div>
      <script type="text/javascript">


            var cname="";

              $(document).ready(function(){

                $("#submit").click(function(){
                  var user=$("#user_name").val();
                  var pass=$("#password").val();
                  $("#response").removeClass(cname);
                  $("#response").addClass("alert-warning");
                  cname="alert-warning";
                  $("#status").html("Missing");
                  if(user.length==0){
                    $("#message").html("Please enter a Username");
                    $("#response").slideDown('fast');
                    return;
                  }
                  if(pass.length==0){
                    $("#message").html("Please enter a Password");
                    $("#response").slideDown('fast');
                    return;
                  }
                  pass=md5(pass);
                  $("html").css("cursor", "progress");
                  var formdata={"user":user,"md5_pass":pass};
                  $("#response").removeClass(cname);
                  $.post( "./db/check_user.php",formdata)
                      .done(function(data) {
                        console.log(data);
                        switch(data.status){
                          case 1://success
                          $("#response").addClass("alert-success");
                          cname="alert-success";
                          $("#status").html("Success");
                          $("#user_name").val("");
                          $("#password").val("");
                          if($("#remember_me").prop("checked")){
                            $.cookie("user",user, { expires: 24*3600*1000});
                            $.cookie("pass",pass, { expires: 24*3600*1000});
                          }
                          window.location.href=getBaseUrl()+"user.php";
                          break;
                          case 2://Error
                          $("#response").addClass("alert-warning");
                          cname="alert-warning";
                          $("#status").html("Error");
                          break;
                          case 3://Fatal Error
                          $("#response").addClass("alert-danger");
                          cname="alert-danger";
                          $("#status").html("Fatal Error");
                          break;
                        }
                        $("#message").html(data.message);
                      })
                      .fail(function() {
                        $("#response").addClass("alert-danger");
                        cname="alert-danger";
                        $("#status").html("Fatal Error:");
                        $("#message").html("Please check Connection");
                      })
                      .always(function() {
                        $("#response").slideDown('fast');
                        $("html").css("cursor", "default");
                      });
                });

                $(".form-group").click(function(){
                  $("#response").slideUp('fast');
                });

              });

      </script>
    </div>
  </body>
</html>
