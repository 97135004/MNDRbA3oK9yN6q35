<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url(); ?>assets/plugins/images/favicon.png">

    <title><?php echo $this->config->item('default_title')." - ".$title; ?></title>
    <!-- Bootstrap Core CSS -->
    <link href="<?php echo base_url(); ?>assets/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Asli v 3.3.6-->
	 <!-- Menu CSS -->
    <link href="<?php echo base_url(); ?>assets/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.css" rel="stylesheet">
	
    <!-- <link href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
    <link href="<?php echo base_url('assets/plugins/') ?>bower_components/multiselect/css/multi-select.css" rel="stylesheet" type="text/css" />
	  <!-- Animation CSS -->
    <link href="<?php echo base_url(); ?>assets/css/animate.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo base_url(); ?>assets/css/style.css?id=<?php echo date("d-m-Y", time()) ?>" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/plugins/bower_components/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url() ;?>assets/plugins/bower_components/datatables-plugins/buttons/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url(); ?>assets/vendor/fixed/fixedColumns.dataTables.min.css" rel="stylesheet">
    <link href="<?php echo base_url() ;?>assets/plugins/bower_components/toast-master/css/jquery.toast.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/plugins/bower_components/jquery-asColorPicker-master/css/asColorPicker.css" rel="stylesheet">
    <!-- Date picker plugins css -->
    <link href="<?php echo base_url(); ?>assets/plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
    <!-- color CSS you can use different color css from css/colors folder -->
    <!-- We have chosen the skin-blue (blue.css) for this starter
          page. However, you can choose any other skin from folder css / colors .-->
    <link href="<?php echo base_url(); ?>assets/css/colors/gray.css" id="theme" rel="stylesheet">
	
	<!-- awal tambahan ganti css==============================-->
    <!-- DataTables Responsive CSS -->
    <link href="<?php echo base_url(); ?>assets/vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
	<!--<link href="<?php echo base_url(); ?>assets/vendor/datatables/css/select.bootstrap.min.css" rel="stylesheet"> ASA-->
	<link href="<?php echo base_url(); ?>assets/vendor/pace/css/pace.min.css" rel="stylesheet">	
	<link href="<?php echo base_url(); ?>assets/vendor/bootstrap/css/bootstrap-toggle.min.css" rel="stylesheet">	
	<link href="<?php echo base_url(); ?>assets/vendor/bootstrap-select22/css/style.select2.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>assets/vendor/qrcode/css/style.qrcode.css" rel="stylesheet">
	<!--  akhir tambahan -->

    <script>
        var baseURL = '<?php echo base_url() ?>';
        var errorTables = [];
    </script>
	
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/jquery/dist/jquery.min.js"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
	
	 <!-- Define Base URL -->
     <style>
         table.dataTable td{
            color: #000;
            font-weight: 400;
         }
         table.dataTable:not(.small) td{
            font-size: 12px;
         }
         a.link_datatables{
            color: #cf451c;
            font-weight: bold;
         }
         a.link_datatables:hover{
            color: #cf451c;
            font-weight: bold;
         }

         #page-wrapper {
            min-height: 1000px !important;
         }

         .footer {
            position: fixed;
            bottom: 0;
            right: 0;
         }

     </style>

</head>

<body>
    <!-- Preloader -->
 <!--    <div class="preloader">
     <div class="cssload-speeding-wheel"></div>
 </div> -->
    <div id="wrapper">
        <!-- Top Navigation -->
        <nav class="navbar navbar-default navbar-static-top m-b-0">
            <div class="navbar-header"> <a class="navbar-toggle hidden-sm hidden-md hidden-lg " href="javascript:void(0)" data-toggle="collapse" data-target=".navbar-collapse"><i class="ti-menu"></i></a>
                <!-- Logo -->
                <div class="top-left-part">
                    <a class="logo" href="<?php echo base_url('dashboard') ?>">
                        <!-- Logo icon image, you can use font-icon also --><b><img src="<?php echo base_url('assets/plugins/') ?>images/ipc-logo.png" alt="home" /></b>
                        <!-- Logo text image you can use text also --><span class="hidden-xs">
						<!--<img src="<?php echo base_url('assets/plugins/') ?>images/eliteadmin-text.png" alt="home" /> -->
						<?php echo $this->config->item('default_title') ?>
						</span> </a>
                </div>
                <!-- /Logo -->
                <!-- This is for mobile view search and menu icon -->
                <ul class="nav navbar-top-links navbar-left hidden-xs">
                    <li><a href="javascript:void(0)" class="open-close hidden-xs waves-effect waves-light"><i class="icon-arrow-left-circle ti-menu"></i></a></li>
                </ul>
                <!-- This is the message dropdown -->
                <ul class="nav navbar-top-links navbar-right pull-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="javascript:void(0)" style="padding: 0 15px;border-left:1px solid #fff"> <img src="<?php echo base_url(); ?>assets/plugins/images/users/people.png" alt="user-img" width="28" class="img-circle"><b class="hidden-xs"><?php echo (isset($this->session->userdata['display_name'])) ? $this->session->userdata['display_name'] : 'qrcode USER' ?> </b></a>
                        <ul class="dropdown-menu dropdown-user animated fadeInDown" style="width: 100%;">
                            <li><a href="<?php echo base_url('profil') ?>"><i class="ti-user"></i> Profile</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="<?php echo base_url('logout') ?>"><i class="fa fa-power-off"></i> Logout</a></li>
                        </ul>
                        <!-- /.dropdown-user -->
                    </li>
                    <!-- <li class="right-side-toggle"> <a class="waves-effect waves-light" href="javascript:void(0)"><i class="ti-settings"></i></a></li> -->
                    <!-- /.dropdown -->
                </ul>
            </div>
            <!-- /.navbar-header -->
            <!-- /.navbar-top-links -->
            <!-- /.navbar-static-side -->
        </nav>
        <!-- End Top Navigation -->
        <!-- Left navbar-header -->
        
        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse slimscrollsidebar" style="padding-bottom: 60px;">
                <ul class="nav" id="side-menu">
                    <?php
                        $activepage = (isset($activepage)) ? $activepage : '';
                    ?>
                    <li><a id="home_link" href="<?php echo base_url('dashboard') ?>"<?php echo ($activepage == "dashboard") ? ' class="active"' : '' ?>><i class="fa fa-home fa-fw" data-icon="v"></i> <span class="hide-menu">Home</span></a></li>
                    <?php
                        $menuList = (isset($this->session->userdata['menu_id'])) ? $this->session->userdata['menu_id'] : array(1);
                    ?>
                    <?php echo $this->dynamic_menu->build_menu($activepage, $menuList);?>
                </ul>
            </div>
        </div>

        <!-- Left navbar-header end -->

        <!-- Page Wrapper -->
        <div id="page-wrapper">
            <?php echo $contents; 
				$this->load->view('arrow-set.php');
			?>
            <!-- .right-sidebar -->
            <?php // $this->load->view('sidebar-right'); ?>
            <!-- /.right-sidebar -->
            <footer class="footer text-center"><?php echo date('Y') ?> &copy; qrcode </footer>
        </div>
        <!-- Page Wrapper end-->
    </div>
    <!-- /#wrapper -->   
	
<div class="modal-loading"></div>

<div class="download-loading">
  <div id="message" class="message"></div>
</div>

    <!-- Bootstrap Core JavaScript -->
    <script src="<?php echo base_url(); ?>assets/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Menu Plugin JavaScript -->
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js"></script>
    <!--slimscroll JavaScript -->
    <script src="<?php echo base_url(); ?>assets/js/jquery.slimscroll.js"></script>
    <!--Wave Effects -->
    <script src="<?php echo base_url(); ?>assets/js/waves.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="<?php echo base_url(); ?>assets/js/custom.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/validator.js"></script>
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/datatables/jquery.dataTables.min.js"></script> <!--v 1.10.10 -->
    
    <script src="<?php echo base_url(); ?>assets/vendor/fixed/dataTables.fixedColumns.min.js"></script>
    <!-- start - This is for export functionality only -->
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/datatables-plugins/buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/datatables-plugins/buttons/js/buttons.flash.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/datatables-plugins/buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/datatables-plugins/buttons/js/buttons.print.min.js"></script>
    <!--Style Switcher -->
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/styleswitcher/jQuery.style.switcher.js">
    </script>
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/toast-master/js/jquery.toast.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/toastr.js"></script>
    
    <!-- Color Picker Plugin JavaScript -->
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/jquery-asColorPicker-master/libs/jquery-asColor.js"></script>
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/jquery-asColorPicker-master/libs/jquery-asGradient.js"></script>
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js"></script>
    <!-- Date Picker Plugin JavaScript -->
    <script src="<?php echo base_url(); ?>assets/plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    
    <!--  awal tambahan -->
    <!-- DataTables JavaScript -->  
    <script src="<?php echo base_url(); ?>assets/vendor/datatables-responsive/dataTables.responsive.js"></script>
    <!--<script src="<?php echo base_url(); ?>assets/vendor/datatables/js/dataTables.select.min.js"></script> ASA-->
    <script src="<?php echo base_url(); ?>assets/vendor/bootstrap-notify/js/bootstrap-notify.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendor/bootstrap-notify/js/bootstrap-notify.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendor/bootstrap-notify/js/bootstrap-notify.asa.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendor/bootstrap/js/bootbox.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendor/bootstrap/js/bootstrap-add-clear.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendor/bootstrap/js/bootstrap-add-clear.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendor/pace/js/pace.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendor/bootstrap/js/bootstrap2-toggle.min.js"></script>    
    <script src="<?php echo base_url(); ?>assets/vendor/bootstrap-select22/js/jquery.plugin.select2.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendor/bootstrap-select22/js/jquery.plugin.select2.id.js"></script>
	<script src="<?php echo base_url(); ?>assets/vendor/qrcode/js/bootstrap-confirmation.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/jasny-bootstrap.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendor/qrcode/js/jquery.number.js"></script>   
    <script src="<?php echo base_url(); ?>assets/vendor/qrcode/js/jquery.qrcode.js"></script>

    <script type="text/javascript" src="<?php echo base_url('assets/plugins/') ?>bower_components/multiselect/js/jquery.multi-select.js"></script>
    
    <!--  akhir tambahan -->
    <script>
         $(document).ready(function() {
            $.fn.dataTable.ext.errMode = 'none';
            <?php if($activepage != "dashboard"): ?>
                setTimeout(function(){
                     $("#home_link").removeClass("active");
                }, 50);
            <?php endif; ?>
         });

        function pushTableError (param){
           errorTables.push(param);
        }

        function errorCheck(){
            setTimeout(function(){
                if(errorTables.length > 0){
                    lastArr = errorTables[errorTables.length-1];
                    errorTables.splice(-1,1);
                    listTableError = errorTables.join(", ");
                    errorString = 'Mohon maaf terjadi kesalahan pada table ' + listTableError + ' & ' + lastArr;
                    alert(errorString);
                    errorTables = [];
                }
            }, 5000);
        }

        errorCheck();
    </script>
</body>
</html>