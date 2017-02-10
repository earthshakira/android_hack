var baseURL;
var year=365*24*3600*1000;
function getBaseUrl(){
  baseURL=window.location.href;
  split=baseURL.split('/');
  baseURL=split[0]+"//"+split[2]+"/";
  return baseURL;
}

function logout(){
  $.post("../logout.php",{}).done(function(data){
    $.removeCookie("user");
    $.removeCookie("pass");
    window.location.href=getBaseUrl();
  }).fail(function(){
    alert("logout failed check connection");
  });
}

function fullscreen(){
    var el = document.documentElement
      , rfs = // for newer Webkit and Firefox
             el.requestFullScreen
          || el.webkitRequestFullScreen
          || el.mozRequestFullScreen
          || el.msRequestFullscreen
  ;
  if(typeof rfs!="undefined" && rfs){
    rfs.call(el);
  } else if(typeof window.ActiveXObject!="undefined"){
    // for Internet Explorer
    var wscript = new ActiveXObject("WScript.Shell");
    if (wscript!=null) {
       wscript.SendKeys("{F11}");
    }
  }
}
