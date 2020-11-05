<div class="container-fluid">
    
    <?php $this->load->view('template_top') ?>
	<div class="white-box">
		<div class="row">
			<div class="col-lg-12 col-md-12 text-center">
					<h2>Selamat Datang <span class='label label-info'><?php echo $this->session->userdata('identity'); ?></span></h2> 	
					
			</div>
		</div>
		<div class="row">
			<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
				<div class="white-box">
					<div class="user-bg">
						<div class="overlay-box text-purple m-b-15">
							<div class="user-content">
								<a href="<?php echo base_url() ?>pph/show_rekonsiliasi"><img alt="img" class="thumb-lg img-circle" src="<?php echo base_url(); ?>assets/plugins/images/pajak/outbond.png"></a>
								<h4 class="text-white">Outbound Interface</h4>
								<h5 class="text-white">Ambil data dari eBIS ke QR Code Inventory</h5> </div>
						</div>
					</div>					
				</div>
			</div>
			<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
				<div class="white-box">
					<div class="user-bg">
						<div class="overlay-box bg-info m-b-15">
							<div class="user-content">
								<a href="<?php echo base_url() ?>pph21/show_rekonsiliasi"><img alt="img" class="thumb-lg img-circle" src="<?php echo base_url(); ?>assets/plugins/images/pajak/inbond.png"></a>
								<h4 class="text-white">Inbound Interface</h4>
								<h5 class="text-white">Kirim data dari QR Code Inventory ke eBIS </h5> </div>
						</div>
					</div>					
				</div>
			</div>
			<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
				<div class="white-box">
					<div class="user-bg">
						<div class="overlay-box bg-theme-dark m-b-15">
							<div class="user-content">
								<a href="<?php echo base_url() ?>pph_badan/upd_bupot_lain"><img alt="img" class="thumb-lg img-circle" src="<?php echo base_url(); ?>assets/plugins/images/pajak/Reporting.png"></a>
								<h4 class="text-white">Laporan</h4>
								<h5 class="text-white"></h5> </div>
						</div>
					</div>					
				</div>
			</div>			
		</div>
		
		<div class="row">
			<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
				<div class="white-box">
					<div class="user-bg">
						<div class="overlay-box text-purple m-b-15">
							<div class="user-content">
								<a href="<?php echo base_url() ?>ppn_masa/rekonsiliasi_masukan"><img alt="img" class="thumb-lg img-circle" src="<?php echo base_url(); ?>assets/plugins/images/pajak/ScanQR.png"></a>
								<h4 class="text-white">Scan QR Code</h4>
								<h5 class="text-white"></h5> </div>
						</div>
					</div>					
				</div>
			</div>
			<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
				<div class="white-box">
					<div class="user-bg">
						<div class="overlay-box bg-info m-b-15">
							<div class="user-content">
								<a href="<?php echo base_url() ?>ppn_wapu/show_rekonsiliasi"><img alt="img" class="thumb-lg img-circle" src="<?php echo base_url(); ?>assets/plugins/images/pajak/PrintQR.png"></a>
								<h4 class="text-white">Cetak QR Code</h4>
								<h5 class="text-white"></h5> </div>
						</div>
					</div>					
				</div>
			</div>	
			<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
				<div class="white-box">
					<div class="user-bg">
						<div class="overlay-box bg-theme-dark m-b-15">
							<div class="user-content">
								<a href="<?php echo base_url(); ?>uploads/manual_guide/<?php echo ($this->session->userdata('kd_cabang') == '000') ? "IPC_User_Manual_qrcode_II_v1.1.pdf" : "IPC_User_Manual_Cabang_qrcode_II_v1.1.pdf" ?>" target="_blank"><img alt="img" class="thumb-lg img-circle" src="<?php echo base_url(); ?>assets/plugins/images/pajak/UsMan.png"></a>
								<h4 class="text-white">Manual Guide</h4>
								<h5 class="text-white"></h5> </div>
						</div>
					</div>					
				</div>
			</div>			
		</div>
		
    </div>
</div>

<script>
/* $(document).ready(function() {	
	 $('.vcarousel').carousel({
				interval: 3000
			 })
	$(".counter").counterUp({
			delay: 100,
			time: 1200
		});
}); */
</script>
