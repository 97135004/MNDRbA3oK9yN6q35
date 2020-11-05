<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
            	<div class="error-body text-center">
					<h1><?php echo $title ?></h1>
					<h3 class="text-uppercase"><?php echo $message ?></h3>
					<p class="text-muted m-t-30 m-b-30">Halaman <a href="javascript:void(0)"><i> <?php echo current_url() ?> </i></a> tidak tersedia</p>
					<a href="<?php echo $_SERVER['HTTP_REFERER'] ?>" class="btn btn-info btn-rounded waves-effect waves-light m-b-40">Kembali </a>
				</div>
			</div>
        </div>
    </div>
</div>