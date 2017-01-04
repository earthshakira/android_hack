var baseURL="http://localhost/";

function getBaseUrl(){
  return baseURL;
}

function logout(){
  $.post("./logout.php",{}).done(function(data){
    $.removeCookie("user");
    $.removeCookie("pass");
    window.location.href=baseURL;
  }).fail(function(){
    alert("logout failed check connection");
  });
}
