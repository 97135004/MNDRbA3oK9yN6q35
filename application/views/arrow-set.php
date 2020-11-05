<!--SCROLL-TO-TOP-->
<style type="text/css">
	#asa-scroll{
			display: none;
			width: 30px;
			height: 30px;
			position: fixed;
			right: 81px;
			bottom: 40px;
			border: 0px;
			border-radius: 10%;
			z-index: 1500;
		}
</style>
<script type="text/javascript">
	$(document).ready(function(){
		$(window).scroll(function(){
			if($(this).scrollTop() > 200){
				$('#asa-scroll').fadeIn();
			}else{
				$('#asa-scroll').fadeOut();
			}
		});
		$('#asa-scroll').click(function(){
			$('html, body').animate({scrollTop:0},600);
			return false;
		});
	});
</script>
<a id="asa-scroll" href="#"><img src="<?php echo base_url();?>assets/vendor/qrcode/css/images/up.png" height="auto" width="60"/></a>
<!--END SCROLL-TO-TOP-->