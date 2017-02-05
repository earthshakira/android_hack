var active_device="";
var current="home";
var battery="";
      var color=[
        "#1abc9c",
        "#2ecc71",
        "#3498db",
        "#9b59b6",
        "#34495e",
        "#f1c40f",
        "#e67e22",
        "#e74c3c",
        "#ecf0f1",
        "#95a5a6"
      ];

      var bgcolor = [
        "#16a085",
        "#27ae60",
        "#2980b9",
        "#8e44ad",
        "#2c3e50",
        "#f39c12",
        "#d35400",
        "#c0392b",
        "#bdc3c7",
        "#7f8c8d",
      ];


function loadTodo(){
  if($.cookie("todo")){
    var list=JSON.parse($.cookie("todo"));
    var htmlString="";
    for(var i=0;i<list.length;i++){
        var entry='<li><p ><div onclick="toggleTodo('+i+');" class="icheckbox_flat-green ';
        if(list[i].checked==true)entry+="checked ";
        entry+='" style="position: relative;" ><input  class="flat" style="position: absolute; opacity: 0;" type="checkbox"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255) none repeat scroll 0% 0%; border: 0px none; opacity: 0;"></ins></div> '+list[i].entry+' </p> </li>';
        htmlString+=entry;
        console.log(entry);
    }
    $("#todo_area").html(htmlString);
  }else{
    $("#todo_area").html("<p>No Entries</p>");
  }
}

function toggleTodo(i){
  var list=JSON.parse($.cookie("todo"));
  list[i].checked=!(list[i].checked);
  $.cookie("todo",JSON.stringify(list),{ expires: year});
  loadTodo();
}
function addTodo(entry){

  if($.cookie("todo")){
    var list=JSON.parse($.cookie("todo"));
    var row={"entry":entry,"checked":false};
    list.push(row);
    $.cookie("todo",JSON.stringify(list),{ expires: year});
  }else{
    var entries=[{"entry":entry,"checked":false}];
    $.cookie("todo",JSON.stringify(entries),{ expires: year});
  }

  loadTodo();
}

  function get_API_name(x){
    switch(x){
      case 1:return "Alpha";
      case 2:return "Beta";
      case 3:return "Cupcake";
      case 4:return "Donut";
      case 5:
      case 6:
      case 7:return "Eclair";
      case 8:return "Froyo";
      case 9:
      case 10:return "Gingerbread";
      case 11:case 12:case 13:return "Honeycomb";
      case 14:case 15:return "Ice Cream Sandwich";
      case 16:case 17:case 18:return "Jelly Bean";
      case 19:case 20:return "KitKat";
      case 21:case 22:return "Lollipop";
      case 23:return "Marshmallow";
      case 24:case 25:return "Nougat";
    }
  }
	function init_API_doughnut(){

		if( typeof (Chart) === 'undefined'){ return; }

		console.log('init_chart_doughnut');

		if ($('.canvasDoughnut').length){

      var mp = new Map();
      var indi=0;
      for(var i=0;i<devices.length;i++){
        var name = get_API_name(parseInt(devices[i].device_api));
        if(mp.has(name)){
          mp.set(name,mp.get(name)+1);
        }else{
          mp.set(name,1);
          indi++;
        }
      }
      var lbl=[];
      var tdata=[];
      var clr=[]
      var bgclr=[];
      var i=0;
      var tot = devices.length;
      var htmlStr="";
      var tot_perc=0;
      for (var [key, value] of mp) {
        lbl.push(key);
        tdata.push(value);
        clr.push(color[i]);
        bgclr.push(bgcolor[i]);
        var perc=Math.ceil((value*100.00)/tot);
        if(i==indi-1)
          perc=100-tot_perc;
        htmlStr+='<tr><td><p><i class="fa fa-square" style="color:'+color[i]+'"></i>'+key+' </p></td><td>'+perc+'%</td></tr>';
        tot_perc+=perc;
        i++;
      }
      $("#device_distribution_table").html(htmlStr);
      var chart_doughnut_settings = {
  				type: 'doughnut',
  				tooltipFillColor: "rgba(51, 51, 51, 0.55)",
  				data: {
  					labels:lbl,
  					datasets: [{
  						data:tdata,
  						backgroundColor: clr,
  						hoverBackgroundColor: bgclr
  					}]
  				},
  				options: {
  					legend: false,
  					responsive: false
  				}
  			}

			$('.canvasDoughnut').each(function(){

				var chart_element = $(this);
				var chart_doughnut = new Chart( chart_element, chart_doughnut_settings);

			});
		}
	}


  	function init_battery_chart(){

  		if( typeof ($.plot) === 'undefined'){ return; }

  		console.log('init_battery_chart');
          $.getJSON("../db/get_battery.php?device_id="+active_device.device_id,function(data){
            console.log(data);
            battery_data=[];


            for(var i=data.length-1;i>=0;i-=1){
              var tupple=[];
              tupple.push((parseInt(data[i].time)+19800)*1000);
              tupple.push(parseInt(data[i].perc));
              battery_data.push(tupple);
            }
            var timediff=parseInt(data[0].time)-parseInt(data[data.length-1].time);
            timediff/=540;
            timediff=Math.ceil(timediff);
            console.log(timediff);
            var chart_plot_01_settings = {
                  series: {
                    lines: {
                      show: false,
                      fill: true
                    },
                    splines: {
                      show: true,
                      tension: 0.4,
                      lineWidth: 1,
                      fill: 0.4
                    },
                    points: {
                      radius: 1,
                      show: true
                    },
                    shadowSize: 2
                  },
                  grid: {
                    verticalLines: true,
                    hoverable: true,
                    clickable: true,
                    tickColor: "#d5d5d5",
                    borderWidth: 1,
                    color: '#fff'
                  },
                  colors: ["rgba(38, 185, 154, 0.38)", "rgba(3, 88, 106, 0.38)"],
                  xaxis: {
                    show:true,
                    tickColor: "rgba(51, 51, 51, 0.06)",
                    mode: "time",
                    monthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    timeformat: " %e%b %I:%M%p ",
                    tickSize: [timediff, "minute"],
                    tickLength: 10,
                    axisLabel: "Time",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 10 ,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 10
                  },
                  yaxis: {
                    show:true,
                    ticks: 8,
                    tickColor: "rgba(51, 51, 51, 0.06)",
                    tickLength: 10,
                    axisLabel: "Battery Percentage",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 10 ,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 10,
                  },
                  tooltip: true
                }
            if ($("#battery_chart_plot").length){
              console.log('Plot1');
              $.plot( $("#battery_chart_plot"), [ battery_data],  chart_plot_01_settings );
            }

            });
  	}

  function get_elapsed_time(ts){
    var min=Math.floor(ts/60) ;
    var hrs=Math.floor(min/60);
    var days=Math.floor(hrs/24);
    var weeks=Math.floor(days/7);
    var months=Math.floor(weeks/4);
    var years=Math.floor(months/12);
      if(ts<60){
        name_trail=ts+" second(s)  ";
      }else if(min<60){
        name_trail=min+" minute(s)  ";
        ts=ts%60;
        if(ts!=0)
          name_trail+=ts+" second(s)  ";
      }else if(hrs<24){
        name_trail=hrs+" hour(s)  ";
        min=min%60;
        if(min!=0)
          name_trail+=min+" minute(s) ";
      }else if(days<7){
        name_trail=days+" day(s) ";
        hrs=hrs%24;
        if(hrs!=0)name_trail+=hrs+"hour(s) ";
      }else if(weeks<4){
        name_trail+=weeks+" week(s) ";
        days=days%7;
        if(days!=0)
          name_trail+=days+" day(s) ";
      }else if(months<12){
        name_trail=months+" month(s) ";
        weeks=weeks%4;
        if(weeks!=0)
          name_trail+=weeks+" week(s) ";
      }else{
        name_trail=years+" year(s) ";
        months=months%12;
        if(months!=0)
        name_trail+=months+" month(s) ";
      }
      name_trail+="ago";
      return name_trail;
    }
  function init_device_menu(){
    var htmlStr="";
    htmlStr+="<table id='device-stats-table' class='table table-striped table-bordered bulk_action'><thead><tr><th>Device ID</th><th>Model</th><th>Account</th><th>API</th><th>Activity</th><th>Cache Data</th><th>Action(s)</th></tr></thead><tbody>";
    for(var i=0;i<devices.length;i++){
      var ts=Math.floor(devices[i].last_seen);
      var name_trail="";
      var min=Math.floor(ts/60) ;
      var hrs=Math.floor(min/60);
      var days=Math.floor(hrs/24);
      var weeks=Math.floor(days/7);
      var months=Math.floor(weeks/4);
      var years=Math.floor(months/12);
      tag='<span class="label label-';
      var action='<button type="button" onclick="load_advance(this)" data-index="'+i+'"class="btn btn-primary btn-xs">Go Advance<i class="fa fa-chevron-right"></i></button>';
      var alive=0,cach=1;
      if(parseInt(devices[i].active)>0){
          name_trail='success">Active Now';
          alive=1;
        }else{
        if(ts<60){
          name_trail=ts+" second(s)  ";
          tag+='info">';
        }else if(min<60){
          name_trail=min+" minute(s)  ";
          ts=ts%60;
          if(ts!=0)
            name_trail+=ts+" second(s)  ";
          tag+='primary">';
        }else if(hrs<24){
          name_trail=hrs+" hour(s)  ";
          min=min%60;
          if(min!=0)
            name_trail+=min+" minute(s) ";
          tag+='default">';
        }else if(days<7){
          name_trail=days+" day(s) ";
          hrs=hrs%24;
          if(hrs!=0)name_trail+=hrs+"hour(s) ";
          tag+='warning">';
        }else if(weeks<4){

          name_trail+=weeks+" week(s) ";
          days=days%7;
          if(days!=0)
            name_trail+=days+" day(s) ";
          tag+='danger">';
        }else if(months<12){
          name_trail=months+" month(s) ";
          weeks=weeks%4;
          if(weeks!=0)
            name_trail+=weeks+" week(s) ";
          tag+='danger">';
        }else{
          tag+='danger">';
          name_trail=years+" year(s) ";
          months=months%12;
          if(months!=0)
          name_trail+=months+" month(s) ";
        }
        name_trail+="ago";
      }
      tag+=name_trail+'</span>';
      var cache="";
      if(devices[i].saved_contacts!=null){
            cache+='<span class="label label-success">Contacts</span>';
      }
      if(devices[i].saved_calllog!=null){
            cache+='&nbsp;<span class="label label-success">Call Log</span>';
      }
      if(devices[i].saved_whatsapp!=null){
            cache+='&nbsp;<span class="label label-success">Whats App</span>';
      }
      if(devices[i].saved_gallery!=null){
            cache+='&nbsp;<span class="label label-success">Gallery</span>';
      }
      if(cache.length==0){
        cache="--";
        cach=0;
      }
      if(alive==0 && cach == 0)
        action = "-";
      htmlStr+="<tr><td>"+devices[i].device_id+"</td><td>"+devices[i].device_name+"</td><td>"+devices[i].device_account+"</td><td>"+get_API_name(parseInt(devices[i].device_api))+"</td><td>"+tag+"</td><td>"+cache+"</td><td>"+action+"</td></tr>";
    }
    htmlStr+="</tbody></table>";
    $("#device-stats-dynamic").html(htmlStr);

    $("#device-stats-table").DataTable();
  }

  function all_init(){
    init_API_doughnut();
    init_device_menu();
    $("#help").hide();
    $("#device-stats").hide();
    $("#advanced-stats").hide();
  }

function load_advance(id){
    var index=$(id).attr("data-index");
    active_device=devices[index];
    if(devices[index].saved_contacts!=null){
        build_contacts_table(devices[index].saved_contacts);
    }
    if(devices[index].saved_calllog!=null){
        build_call_log_table(devices[index].saved_calllog);
    }
    if(devices[index].saved_whatsapp!=null){
    }
    if(devices[index].saved_gallery!=null){

        console.log("calling build gallery");
        build_gallery_fetcher();
    }
    $('#info_window_device_id').html("Device Id : "+devices[index].device_id);
    $("#info_window_devname").html("Model Name : " + devices[index].device_name);
    $("#info_window_devuser").html("Primary Account : " + devices[index].device_account);
    $("#info_window_dev_api").html("SDK Version : API " + devices[index].device_api+" "+get_API_name(parseInt(devices[index].device_api)));
    $("#info_window_last_seen").html("Last Activity: " + get_elapsed_time(parseInt(devices[index].last_seen)));
    if(devices[index].active==0)
    $("#info_window_current_status").html("Current Status : " + '<span class="label label-default">Inactive</span>');
    else
    $("#info_window_current_status").html("Current Status : " + '<span class="label label-success">Active</span>');
    active_device=devices[index];
    current="advanced-stats";
    init_battery_chart();
    $("#device-stats").slideUp();
    $("#no-dev-placeholder").hide();
    $("#advanced-menu-dynamic ").show();
    $("#advanced-stats").slideDown();
  }
$(document).ready(function(){
  loadTodo();
  $.getJSON("../db/get_devices.php",function(data){
    devices=data;
    all_init();
  });
  setInterval(function(){
    $.getJSON("../db/get_devices.php",function(data){
      devices=data;
    });
  },1500);
  $("#todo_add_btn").click(function(){
      addTodo($("#todo_add_text").val());
  });
  $("#todo_reset").click(function(){
      $.removeCookie("todo");
      loadTodo();
  });

  $(".sidebar-menu-btn").click(function(){
      var next=$(this).attr("data-page");
      if(next==current)return;
      $("#"+current).slideUp("slow");
      $("#"+current).fadeOut("slow");
      $("#"+next).slideDown("slow");
      $("#"+next).fadeIn("slow");
      current=next;
      $(".sidebar-menu-btn").removeClass("active");
      $(this).addClass("active");
  });
});
