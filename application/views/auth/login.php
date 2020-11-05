<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url('assets/plugins/') ?>images/favicon.png">
<title><?php echo $this->config->item('default_title') ?> Login Page</title>
<!-- Bootstrap Core CSS -->
<link href="<?php echo base_url('assets/bootstrap/') ?>dist/css/bootstrap.min.css" rel="stylesheet">
<!-- animation CSS -->
<link href="<?php echo base_url('assets/css/') ?>animate.css" rel="stylesheet">
<!-- Custom CSS -->
<link href="<?php echo base_url('assets/css/') ?>style.css" rel="stylesheet">
<!-- color CSS -->
<link href="<?php echo base_url('assets/css/') ?>colors/gray.css" id="theme"  rel="stylesheet">
<link href="<?php echo base_url(); ?>assets/vendor/qrcode/css/style.qrcode.css" rel="stylesheet">
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<!-- Preloader -->
<div class="preloader">
  <div class="cssload-speeding-wheel"></div>
</div>

<section id="wrapper" class="login-register">
  <div class="login-box" style="background-color:rgba(255,255,255,.9) !important; border-radius: 5px;">
    <div class="white-box" style="background-color:transparent !important">
      <!-- <?php echo form_open_multipart("", array("id" => "loginform", "class" => "form-horizontal form-material", "data-toggle" => "validator"));?> -->
      <form role="form" id="loginform" class="form-horizontal form-material" data-toggle="validator">
        <h3 class="box-title m-b-20 text-center">QRCODE LOGIN</h3>
        <p id="errorPlace" class="bold text-center p-0" style="color:red;font-weight: 400; display: none"></p>
        <div class="form-group ">
          <div class="col-xs-12">
            <input class="form-control" type="text" name="identity" placeholder="Username" autocomplete="off" data-toggle="validator" data-error="Pleasee fill Username" required>
            <div class="help-block with-errors"></div>
          </div>
        </div>
        <div class="form-group">
          <div class="col-xs-12">
            <input class="form-control" type="password" name="password" placeholder="Password" autocomplete="new-password" data-toggle="validator" data-error="Pleasee fill Password" required>
            <div class="help-block with-errors"></div>
          </div>
        </div>
        <!-- <div class="form-group">
          <div class="col-md-12">
            <div class="checkbox checkbox-primary pull-left p-t-0">
              <input id="checkbox-signup" type="checkbox">
              <label for="checkbox-signup"> Remember me </label>
            </div>
          </div>
        </div> -->
        <div class="form-group text-center m-t-20">
          <div class="col-xs-12">
            <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Log In</button>
          </div>
        </div>
        <!-- <div class="form-group m-b-0">
          <div class="col-sm-12 text-center">
            <p>Don't have an account? <a href="register.html" class="text-primary m-l-5"><b>Sign Up</b></a></p>
          </div>
        </div> -->
      <?php echo form_close();?>
    </div>
  </div>
</section>
<div class="login-loading">
  <div id="message" class="message"></div>
</div>
<!-- jQuery -->
<script src="<?php echo base_url('assets/plugins/') ?>bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap Core JavaScript -->
<script src="<?php echo base_url('assets/bootstrap/') ?>dist/js/bootstrap.min.js"></script>
<!-- Menu Plugin JavaScript -->
<script src="<?php echo base_url('assets/plugins/') ?>bower_components/sidebar-nav/dist/sidebar-nav.min.js"></script>

<!--slimscroll JavaScript -->
<script src="<?php echo base_url('assets/js/') ?>jquery.slimscroll.js"></script>
<!--Wave Effects -->
<script src="<?php echo base_url('assets/js/') ?>waves.js"></script>
<!-- Custom Theme JavaScript -->
<script src="<?php echo base_url('assets/js/') ?>custom.min.js"></script>
<script src="<?php echo base_url('assets/js/') ?>validator.js"></script>
<!--Style Switcher -->
<script src="<?php echo base_url('assets/plugins/') ?>bower_components/styleswitcher/jQuery.style.switcher.js"></script>
<script>
  
    $('#loginform').validator().on('submit', function(e) {
      if (e.isDefaultPrevented()) {
      }
      else {
        $.ajax({
          url      : '<?php echo $this->config->item('login_hash_url') ?>',
          type     : "POST",
          dataType :"json", 
          data     : $('#loginform').serialize(),
          beforeSend  : function(){
             $("body").addClass("loading");
             },
          success : function(result){
            if (result.status == true) {
                setTimeout(function(){
                  $("#message").html(result.description);
                  setTimeout(function(){
                    $("body").removeClass("loading");
                    window.location.href = '<?php echo base_url('dashboard') ?>';
                  }, 200);
                }, 400);
            } else {
                setTimeout(function(){
                  $("body").removeClass("loading");
                  $("#errorPlace").css("display", "block");
                  $("#errorPlace").html(result.description);
                }, 500);
            }
          }
        });
      }
      e.preventDefault();
    });
</script>
</body>
</html>
