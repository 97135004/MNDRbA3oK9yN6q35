<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url('assets/') ?>plugins/images/favicon.png">
<title><?php echo $message ?></title>
<!-- Bootstrap Core CSS -->
<link href="<?php echo base_url('assets/') ?>bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- animation CSS -->
<link href="<?php echo base_url('assets/') ?>css/animate.css" rel="stylesheet">

<!-- Custom CSS -->
<link href="<?php echo base_url('assets/') ?>css/style.css" rel="stylesheet">
<!-- color CSS -->
<link href="<?php echo base_url('assets/') ?>css/colors/blue.css" id="theme"  rel="stylesheet">
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
<section id="wrapper" class="error-page">
  <div class="error-box">
    <div class="error-body text-center">
      <h1><?php echo $title ?></h1>
      <h3 class="text-uppercase">Page Not Found !</h3>
      <p class="text-muted m-t-30 m-b-30">YOU SEEM TO BE TRYING TO FIND HIS WAY HOME</p>
      <a href="<?php echo base_url('dashboard') ?>" class="btn btn-info btn-rounded waves-effect waves-light m-b-40">Back to home</a> </div>
    <footer class="footer text-center">2016 © Elite Admin.</footer>
  </div>
</section>
<!-- jQuery -->
<script src="<?php echo base_url('assets/') ?>plugins/bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap Core JavaScript -->
<script src="<?php echo base_url('assets/') ?>bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Custom Theme JavaScript -->
<script src="<?php echo base_url('assets/') ?>js/custom.min.js"></script>

</body>
</html>
