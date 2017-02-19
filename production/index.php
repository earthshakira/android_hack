<?php
  session_start();

  if(!isset($_SESSION['user_name'])){
      header('Location: ../index.php');
      exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Android Exploit Tool </title>
    <link rel="shortcut icon" href="../img/favicon.ico">
    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">

    <!-- bootstrap-progressbar -->
    <link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- ion range slider -->
    <link href="../vendors/normalize-css/normalize.css" rel="stylesheet">
    <link href="../vendors/ion.rangeSlider/css/ion.rangeSlider.css" rel="stylesheet">
    <link href="../vendors/ion.rangeSlider/css/ion.rangeSlider.skinFlat.css" rel="stylesheet">
    <!-- JQVMap -->
    <link href="../vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
    <!-- bootstrap-daterangepicker -->
    <link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

    <!-- Data tables-->
    <link href="../vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="../build/css/custom.min.css" rel="stylesheet">
  </head>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="index.php" class="site_title"><i class="fa fa-bug"></i><span>Andr-Exp-Tool</span></a>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
            <div class="profile clearfix">
              <div class="profile_pic">
                <img src="images/user.png" alt="..." class="img-circle profile_img" />
              </div>
              <div class="profile_info">
                <span>Welcome,</span>
                <h2><?php echo $_SESSION['user_name']; ?></h2>
              </div>
            </div>
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>General</h3>
                <ul class="nav side-menu">
                  <li><a><i class="fa fa-home"></i> Home <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li class="sidebar-menu-btn" data-page="home" data-active="true"><a>Dashboard</a></li>
                      <li class="sidebar-menu-btn" data-page="help" data-active="false"><a>Help</a></li>
                    </ul>
                  </li>
                  <li><a><i class="fa fa-tasks"></i> Devices <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li class="sidebar-menu-btn" data-page="device-stats" data-active="false"><a >General Stats</a></li>
                      <li class="sidebar-menu-btn" data-page="advanced-stats" data-active="false"><a>Advanced Management</a></li>
                    </ul>
                  </li>
                  <?php if($_SESSION["user_name"]=="super_admin"){
                     ?>
                     <li><a><i class="fa fa-users"></i> User Management <span class="fa fa-chevron-down"></span></a>
                       <ul class="nav child_menu">
                         <li><a href="../register.php">Add User</a></li>
                         <li><a href="../unregister.php">Remove User</a></li>
                       </ul>
                     </li>
                     <?php
                  } ?>
                  </ul>
                </div>
            </div>
            <!-- /sidebar menu -->

            <!-- /menu footer buttons -->
            <div class="sidebar-footer hidden-small">
              <a data-toggle="tooltip" data-placement="top" title="Settings">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" onclick="fullscreen()" title="FullScreen">
                <span class="glyphicon glyphicon-fullscreen"  aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" onclick="$('#menu_toggle').click();" title="Hide">
                <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Logout">
                <span class="glyphicon glyphicon-off" aria-hidden="true" onclick="logout()" ></span>
              </a>
            </div>
            <!-- /menu footer buttons -->
          </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <a id="menu_toggle" ><i class="fa fa-bars"></i></a>
              </div>

              <ul class="nav navbar-nav navbar-right">
                <li class="">
                  <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-android"></i>&nbsp;<?php echo $_SESSION['user_name'];?>
                    <span class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <li><a href="javascript:;"> Profile</a></li>
                    <li>
                      <a href="javascript:;">
                        <span class="badge bg-red pull-right">50%</span>
                        <span>Settings</span>
                      </a>
                    </li>
                    <li><a href="javascript:;">Help</a></li>
                    <li><a onclick="logout()"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
                  </ul>
                </li>

                <li role="presentation" class="dropdown" id="notif_clicker">
                  <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-envelope-o"></i>
                    <span class="badge bg-green" id="notif_counter"></span>
                  </a>
                  <ul id="notif_menu" class="dropdown-menu list-unstyled msg_list" role="menu">
                    <li>
                      <a>
                        <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                        <span>
                          <span></span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    
                    <li>
                      <a>
                        <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <a>
                        <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <div class="text-center">
                        <a>
                          <strong>See All Alerts</strong>
                          <i class="fa fa-angle-right"></i>
                        </a>
                      </div>
                    </li>
                  </ul>
                </li>
              </ul>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main" style="overflow:hidden">
          <div id="home" class="container" class="base-page" >
            <div class="row">
                <h1 class="txt txt-default"><i class="fa fa-home"></i>&nbsp; Welcome to Home <a href="../data/<?php echo md5($_SESSION["user_name"]).".apk"; ?>"><button class="btn btn-primary">Download Payload</button></a></h1>
            </div>
            <div class="row">
                <!-- Start of Activities -->
              <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Recent Activities <small>Sessions</small></h2>
                    <ul class="nav navbar-right panel_toolbox">
                      <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                      </li>
                      <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
                        <ul class="dropdown-menu" role="menu">
                          <li><a href="#">Settings 1</a>
                          </li>
                          <li><a href="#">Settings 2</a>
                          </li>
                        </ul>
                      </li>
                      <li><a class="close-link"><i class="fa fa-close"></i></a>
                      </li>
                    </ul>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <div class="dashboard-widget-content">

                      <ul class="list-unstyled timeline widget">
                        <li>
                          <div class="block">
                            <div class="block_content">
                              <h2 class="title">
                                                <a>Who Needs Sundance When You’ve Got&nbsp;Crowdfunding?</a>
                                            </h2>
                              <div class="byline">
                                <span>13 hours ago</span> by <a>Jane Smith</a>
                              </div>
                              <p class="excerpt">Film festivals used to be do-or-die moments for movie makers. They were where you met the producers that could fund your project, and if the buyers liked your flick, they’d pay to Fast-forward and… <a>Read&nbsp;More</a>
                              </p>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <!--end of device Activities -->
              <!--start of device History -->
              <div class="col-md-4 col-sm-8 col-xs-12">
                <div class="x_panel tile fixed_height_320 overflow_hidden">
                  <div class="x_title">
                    <h2>Device Distribution</h2>
                    <ul class="nav navbar-right panel_toolbox">
                      <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                      </li>
                      <li><a class="close-link"><i class="fa fa-close"></i></a>
                      </li>
                    </ul>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <table class="" style="width:100%">
                      <tr>
                        <th style="width:37%;">
                          <p></p>
                        </th>
                        <th>
                          <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7">
                            <p class="">OS</p>
                          </div>
                          <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5">
                            <p class="">Percentage</p>
                          </div>
                        </th>

                      </tr>
                      <tr>
                        <td>
                          <canvas class="canvasDoughnut" height="140" width="140" style="margin: 15px 10px 10px 0"></canvas>
                        </td>
                        <td>

                        <table class="tile_info">
                          <tbody id="device_distribution_table">
                          </tbody></table>

                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
              <!--end of device History -->
                <!-- Start to do list -->
                <div class="col-md-4 col-sm-8 col-xs-12">
                  <div class="x_panel">
                    <div class="x_title">
                      <h2>To Do List <small>uses Cookies</small></h2>
                      <ul class="nav navbar-right panel_toolbox">
                        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                        </li>
                        <li><a id="todo_reset"><i class="fa fa-recycle"></i></a>
                        </li>
                        <li class="dropdown">
                          <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-plus"></i></a>
                          <ul class="dropdown-menu" role="menu">
                            <li>Enter Task:
                            </li>
                            <li>
                              <textarea id="todo_add_text"></textarea>
                            </li>
                            <li>
                              <br>
                              <button type="button" id="todo_add_btn" class="btn btn-default btn-xs pull-right">Add Task</button>
                            </li>
                          </ul>
                        </li>
                        <li><a class="close-link"><i class="fa fa-close"></i></a>
                        </li>
                      </ul>
                      <div class="clearfix"></div>
                    </div>
                    <div class="x_content">

                      <div class="">
                        <ul class="to_do" id="todo_area">

                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
          </div>
          <div id="help" class="base-page">
            <div class="container">
              <div class="row">
                  <h1 class="txt txt-default"><i class="fa fa-file-text"></i>&nbsp; Help</h1>
              </div>
              <div class="row">
                <div class="col-xs-12">
                  <div class="x_panel">
                    <div class="x_title">
                      <h2>Some Guidelines <small>handle with care</small></h2>
                      <ul class="nav navbar-right panel_toolbox">
                        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                        </li>
                        <li><a class="close-link"><i class="fa fa-close"></i></a>
                        </li>
                      </ul>
                      <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                      <div class="dashboard-widget-content">

                        <ul class="list-unstyled timeline widget">
                          <li>
                            <div class="block">
                              <div class="block_content">
                                <h2 class="title">
                                    <a>The Phone Gallery Fetch is dangerous</a>
                                </h2>
                                <p class="excerpt">
                                  The Gallery Fetch operation is extremely slow and please dont try to mess too much with it.<br>
                                  While the files are not that big a deal but getting the whole database takes a lot of time like hell lot of time<br>
                                  so tread carefully
                                </p>
                                <span class="label label-danger">Very serious</span>
                              </div>
                            </div>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div id="device-stats" class="base-page">
            <div class="container">
              <div class="row">
                  <h1 class="txt txt-default"><i class="fa fa-superpowers"></i>&nbsp;Devices Menu</h1>
              </div>
              <div id="device-stats-dynamic">
              </div>
            </div>
          </div>
          <div id="advanced-stats" class="base-page">
            <div class="container">
              <div class="row">
                  <h1 class="txt txt-default"><i class="fa fa-microchip"></i>&nbsp; Advanced Menu</h1>
              </div>
              <div id="advanced-menu-dynamic" class="container" style="display:none">
                <div class="row">
                  <div class="col-md-8 col-sm-12 col-xs-12">
                    <div class="dashboard_graph">
                      <div class="row x_title">
                        <div class="col-md-12 col-xs-12">
                          <h3>Battery and Activity <small>upto last 100 logs</small></h3>
                        </div>
                      </div>
                      <div class="col-md-12 col-sm-12 col-xs-12">
                        <div id="battery_chart_plot" class="demo-placeholder" style="padding: 0px; position: relative;"><canvas class="flot-base" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 973px; height: 280px;" width="973" height="280"></canvas><div class="flot-text" style="position: absolute; top: 0px; left: 0px; bottom: 0px; right: 0px; font-size: smaller; color: rgb(84, 84, 84);"><div class="flot-x-axis flot-x1-axis xAxis x1Axis" style="position: absolute; top: 0px; left: 0px; bottom: 0px; right: 0px; display: block;"><div style="position: absolute; max-width: 121px; top: 264px; left: 38px; text-align: center;" class="flot-tick-label tickLabel">Jan 01</div><div style="position: absolute; max-width: 121px; top: 264px; left: 194px; text-align: center;" class="flot-tick-label tickLabel">Jan 02</div><div style="position: absolute; max-width: 121px; top: 264px; left: 350px; text-align: center;" class="flot-tick-label tickLabel">Jan 03</div><div style="position: absolute; max-width: 121px; top: 264px; left: 507px; text-align: center;" class="flot-tick-label tickLabel">Jan 04</div><div style="position: absolute; max-width: 121px; top: 264px; left: 663px; text-align: center;" class="flot-tick-label tickLabel">Jan 05</div><div style="position: absolute; max-width: 121px; top: 264px; left: 819px; text-align: center;" class="flot-tick-label tickLabel">Jan 06</div></div><div class="flot-y-axis flot-y1-axis yAxis y1Axis" style="position: absolute; top: 0px; left: 0px; bottom: 0px; right: 0px; display: block;"><div style="position: absolute; top: 241px; left: 7px; text-align: right;" class="flot-tick-label tickLabel">0</div><div style="position: absolute; top: 215px; left: 1px; text-align: right;" class="flot-tick-label tickLabel">10</div><div style="position: absolute; top: 188px; left: 1px; text-align: right;" class="flot-tick-label tickLabel">20</div><div style="position: absolute; top: 161px; left: 1px; text-align: right;" class="flot-tick-label tickLabel">30</div><div style="position: absolute; top: 134px; left: 1px; text-align: right;" class="flot-tick-label tickLabel">40</div><div style="position: absolute; top: 108px; left: 1px; text-align: right;" class="flot-tick-label tickLabel">50</div><div style="position: absolute; top: 81px; left: 1px; text-align: right;" class="flot-tick-label tickLabel">60</div><div style="position: absolute; top: 54px; left: 1px; text-align: right;" class="flot-tick-label tickLabel">70</div><div style="position: absolute; top: 27px; left: 1px; text-align: right;" class="flot-tick-label tickLabel">80</div><div style="position: absolute; top: 1px; left: 1px; text-align: right;" class="flot-tick-label tickLabel">90</div></div></div><canvas class="flot-overlay" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 973px; height: 280px;" width="973" height="280"></canvas></div>
                      </div>
                      <div class="clearfix"></div>
                    </div>
                  </div>
                  <!-- start of Activity Chart -->
                  <div class="col-md-4 col-sm-12 col-xs-12">
                      <div class="x_panel">
                        <div class="x_title">
                          <h2>Device Info <small>descriptive</small></h2>
                          <ul class="nav navbar-right panel_toolbox">
                            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                            </li>
                            <li><a class="close-link"><i class="fa fa-close"></i></a>
                            </li>
                          </ul>
                          <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                          <div class="dashboard-widget-content">

                            <ul class="list-unstyled timeline widget">
                              <li>
                                <div class="block">
                                  <div class="block_content">
                                    <h2 class="title" id='info_window_device_id'></h2>
                                  </div>
                                </div>
                              </li>
                              <li>
                                <div class="block">
                                  <div class="block_content">
                                    <h2 class="title" id='info_window_devname'></h2>
                                  </div>
                                </div>
                              </li>
                              <li>
                                <div class="block">
                                  <div class="block_content">
                                    <h2 class="title" id='info_window_devuser'></h2>
                                  </div>
                                </div>
                              </li>
                              <li>
                                <div class="block">
                                  <div class="block_content">
                                    <h2 class="title" id='info_window_dev_api'></h2>
                                  </div>
                                </div>
                              </li>
                              <li>
                                <div class="block">
                                  <div class="block_content">
                                    <h2 class="title" id='info_window_last_seen'></h2>
                                  </div>
                                </div>
                              </li>
                              <li>
                                <div class="block">
                                  <div class="block_content">
                                    <h2 class="title" id='info_window_current_status'></h2>
                                  </div>
                                </div>
                              </li>
                              <li>
                                <div class="block">
                                  <div class="block_content">
                                    <h2 class="title" id='info_window_whatsapp'></h2>
                                  </div>
                                </div>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                  <!--end of ativity -->
                </div>
                <div class="row">
                  <br>
                  <div class="col-md-7 col-sm-12 col-xs-12">
                    <div class="x_panel">
                      <div class="x_title">
                        <h2>Call Logs  <small></small></h2>
                        <ul class="nav navbar-right panel_toolbox">
                          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                          </li>
                          <li><button type="button" onclick="callLog()" class="btn btn-warning btn-xs">Pull Callog</button>
                          </li>
                          <li><a class="close-link"><i class="fa fa-close"></i></a>
                          </li>
                        </ul>
                        <div class="clearfix"></div>
                      </div>
                      <div class="x_content">
                        <div id="calllog_fetch">

                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-5 col-sm-12 col-xs-12">
                    <div class="x_panel">
                      <div class="x_title">
                        <h2>Contacts List <small>contains emails too..</small></h2>
                        <ul class="nav navbar-right panel_toolbox">
                          <li id="contacts_loader"></li>
                          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                          </li>
                          <li><button type="button" onclick="contacts()" class="btn btn-danger btn-xs">Pull Contacts</button>
                          </li>
                          <li><a class="close-link"><i class="fa fa-close"></i></a>
                          </li>
                        </ul>
                        <div class="clearfix"></div>
                      </div>
                      <div class="x_content">
                        <div id="contacts_fetch"></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <br>
                  <div class="col-md-7 col-sm-12 col-xs-12">
                    <div class="x_panel">
                      <div class="x_title">
                        <h2>Gallery <small></small></h2>
                        <ul class="nav navbar-right panel_toolbox">
                          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                          </li>
                          <li><button type="button" onclick="gallery()" class="btn btn-warning btn-xs">Pull Gallery </button>
                          </li>
                          <li><a class="close-link"><i class="fa fa-close"></i></a>
                          </li>
                        </ul>
                        <div class="clearfix"></div>
                      </div>
                      <div class="x_content">
                        <div id="gallery_fetch" ></div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-5 col-sm-12 col-xs-12">
                    <div class="x_panel">
                      <div class="x_title">
                        <h2>Preview Window <small></small></h2>
                        <ul class="nav navbar-right panel_toolbox">
                          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                          </li>
                          <li id="camera_button_holder">
                          </li>
                          <li><a class="close-link"><i class="fa fa-close"></i></a>
                          </li>
                        </ul>
                        <div class="clearfix"></div>
                      </div>
                      <div class="x_content" id="preview_window">
                        <input id="frame-slider"  />
                        <img class="img img-responsive" id="preview_image"/>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-7 col-sm-12 col-xs-12">
                    <div class="x_panel">
                      <div class="x_title">
                        <h2>Browser History <small>Default Browser History</small></h2>
                        <ul class="nav navbar-right panel_toolbox">
                          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                          </li>
                          <li><button type="button" onclick="browserhistory()" class="btn btn-info btn-xs">Pull Browser History </button>
                          </li>
                          <li><a class="close-link"><i class="fa fa-close"></i></a>
                          </li>
                        </ul>
                        <div class="clearfix"></div>
                      </div>
                      <div class="x_content">
                        <div id="browserhistory_fetch" ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row" id="no-dev-placeholder">
                  <div class="jumbotron">
                    <h1>No Device Selected </h1>
                    <p> This menu is only usable if you have a device selected because then you would be able to interact with the device here</p>
                  </div>
              </div>
            </div>
          </div>
          <!-- /top tiles -->
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            Gentelella - Bootstrap Admin Template by <a href="https://colorlib.com">Colorlib</a>
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>

    <!-- jQuery -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="../vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="../vendors/nprogress/nprogress.js"></script>
    <!-- Chart.js -->
    <script src="../vendors/Chart.js/dist/Chart.min.js"></script>
    <!-- gauge.js -->
    <script src="../vendors/gauge.js/dist/gauge.min.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- iCheck -->
    <script src="../vendors/iCheck/icheck.min.js"></script>
    <!-- Skycons -->
    <script src="../vendors/skycons/skycons.js"></script>
    <!-- Flot -->
    <script src="../vendors/Flot/jquery.flot.js"></script>
    <script src="../vendors/Flot/jquery.flot.pie.js"></script>
    <script src="../vendors/Flot/jquery.flot.time.js"></script>
    <script src="../vendors/Flot/jquery.flot.stack.js"></script>
    <script src="../vendors/Flot/jquery.flot.resize.js"></script>
    <!-- Flot plugins -->
    <script src="../vendors/flot.orderbars/js/jquery.flot.orderBars.js"></script>
    <script src="../vendors/flot-spline/js/jquery.flot.spline.min.js"></script>
    <script src="../vendors/flot.curvedlines/curvedLines.js"></script>
    <!-- DateJS -->
    <script src="../vendors/DateJS/build/date.js"></script>
    <!-- JQVMap -->
    <script src="../vendors/jqvmap/dist/jquery.vmap.js"></script>
    <script src="../vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script>
    <script src="../vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="../vendors/moment/min/moment.min.js"></script>
    <script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

    <!-- Data tables-->
    <script src="http://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
    <script src="../vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="../vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="../vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
    <script src="../vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="../vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="../vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
    <script src="../vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
    <script src="../vendors/jszip/dist/jszip.min.js"></script>
    <script src="../vendors/pdfmake/build/pdfmake.min.js"></script>
    <script src="../vendors/pdfmake/build/vfs_fonts.js"></script>
    <!-- Ion.RangeSlider -->
    <script src="../vendors/ion.rangeSlider/js/ion.rangeSlider.min.js"></script>
    <!-- Custom Theme Scripts -->
    <script type="text/javascript" src="../build/js/custom.js"></script>
    <script type="text/javascript" src="../js/websocket.js"></script>
    <script type="text/javascript" src="../js/jquery.cookie.js"></script>
    <script type="text/javascript" src="../js/main.js"></script>
    <script type="text/javascript" src="../js/md5.js"></script>
    <script type="text/javascript" src="./js/main.js"></script>
    <script type="text/javascript" src="./js/webs.js"></script>
  </body>
</html>
