<div class="container-fluid">

    <?php $this->load->view('template_top') ?>

	<div id="list-data">
		<div class="white-box boxshadow">
			<div class="row"> 
					<div class="col-lg-2">
						<div class="form-group">
							<label>Bulan</label>
							<select class="form-control" id="bulan" name="bulan">
								<?php
									$namaBulan = list_month();
									$bln       = date('m');
									/*if ($bln > 1){
										$bln     = $bln - 1;
										$tahun_n = 0;
									} else {
										$bln     = 12;
										$tahun_n = 1;
									}*/
									for ($i=1;$i< count($namaBulan);$i++){
										$selected = ($i==$bln)?"selected":"";
										echo "<option value='".$i."' data-name='".$namaBulan[$i]."' ".$selected." >".$namaBulan[$i]."</option>";
									}
								?>
							</select>
						</div>
					</div>
					<div class="col-lg-2">
						<div class="form-group">
							<label>Tahun</label>
							<select class="form-control" id="tahun" name="tahun">
								<?php 
									$tahun	= date('Y');
									$tAwal	= $tahun - 5;
									$tAkhir	= $tahun;
									for($i=$tAwal; $i<=$tAkhir;$i++){
										$selected	= ($i==$tahun)?"selected":"";
										echo "<option value='".$i."' ".$selected.">".$i."</option>";
									}
								?>
							</select> 
						</div>
					</div>
					<?php $list_pajak = get_daftar_pajak(); ?>
					<?php if(isset($stand_alone)){ ?>
					<div class="col-lg-3">
						<div class="form-group">
							<label>Jenis Pajak</label>
							<select class="form-control" id="namaPajak" name="namaPajak">
								<?php
								foreach ($list_pajak as $key => $value) {

									if($value->JENIS_PAJAK == "PPN MASUKAN"){
										$nama_pajak_cust = "SSP PPN Masa";
									}
									elseif($value->JENIS_PAJAK == "PPN KELUARAN"){
										$nama_pajak_cust = "SPT PPN Masa";
									}
									else{
										$nama_pajak_cust = str_replace("PPH", "PPh", $value->JENIS_PAJAK);
									}

									if(in_array($value->JENIS_PAJAK, $nama_pajak)){
										?>
										<option value="<?php echo $nama_pajak_cust ?>" data-name="<?php echo $nama_pajak_cust ?>"><?php echo $nama_pajak_cust ?></option>
										<?php
									}
								} ?>
								<?php if(in_array("PPN MASUKAN", $nama_pajak) || in_array("PPN KELUARAN", $nama_pajak)){ ?>
								<option value="BPE PPN" data-name="BPE PPN">BPE PPN</option>
								<option value="CSV Kompilasi PPN Lapor" data-name="BPE PPN">CSV Kompilasi PPN Lapor</option>
								<option value="Lampiran Lain" data-name="Lampiran Lain">Lampiran Lain</option>
								<?php } ?>
							</select> 
						</div>
					</div>
					<?php } else{ ?>
					<div class="col-lg-3">
						<div class="form-group">
							<label>Jenis Pajak</label>
							<select class="form-control" id="namaPajak" name="namaPajak">
								<?php
									foreach ($list_pajak as $key => $value) {
										
										if($value->JENIS_PAJAK == "PPN MASUKAN"){
											$nama_pajak_cust = "SSP PPN Masa";
										}
										elseif($value->JENIS_PAJAK == "PPN KELUARAN"){
											$nama_pajak_cust = "SPT PPN Masa";
										}
										else{
											$nama_pajak_cust = str_replace("PPH", "PPh", $value->JENIS_PAJAK);
										}
								?>
								<option value="<?php echo str_replace("PPH", "PPh", $value->JENIS_PAJAK) ?>" data-name="<?php echo str_replace("PPH", "PPh", $value->JENIS_PAJAK) ?>"><?php echo $nama_pajak_cust ?></option>
								<?php 	} ?>
								<option value="BPE PPN" data-name="BPE PPN">BPE PPN</option>
								<option value="CSV Kompilasi PPN Lapor" data-name="CSV Kompilasi PPN Lapor">CSV Kompilasi PPN Lapor</option>
								<option value="Lampiran Lain" data-name="Lampiran Lain">Lampiran Lain</option>
							</select> 
						</div>
					</div>
					<?php } ?>
					<div class="col-lg-3">
						<div class="form-group">
							<label>Pembetulan Ke</label>
							<select class="form-control" id="pembetulanKe" name="pembetulanKe">
								<option value="0" >0</option>
								<option value="1" >1</option>
								<option value="2" >2</option>
								<option value="3" >3</option>
							</select> 
						</div>
					</div>
					<div class="col-lg-2">
						<div class="form-group">
						<label>&nbsp;</label>
							<button id="btnView" class="btn btn-default btn-rounded custom-input-width btn-block" type="button" ><i class="fa fa-bars"></i> <span>VIEW</span></button>
						</div>
					</div>
			</div>
		</div>

		<div class="row"><br></div>
		<div class="row">
					<div class="col-lg-12">
						 <div class="panel-group boxshadow" id="accordion">
							<div class="panel panel-info">
								<div class="panel-heading">
								<div class="row">
									  <div class="col-lg-6">
										Daftar File Arsip Pelaporan Pajak
									  </div>
									  <div class="col-lg-6">
										<div class="navbar-right">
											<button id="btnAdd" class="btn btn-default btn-rounded custom-input-width" type="button" ><i class="fa fa-pencil-square-o"></i> ADD NEW DOC</button>
											<button type="button" id="btnEdit" class="btn btn-rounded btn-default custom-input-width" disabled ><i class="fa fa-pencil"></i>EDIT</button>
											<button type="button" id="btnDelete" class="btn btn-rounded btn-default custom-input-width " disabled ><i class="fa fa-trash-o"></i>DELETE</button>
										</div>
										
										
									  </div>
								</div>
								</div>
								<div id="collapse-data" class="panel-collapse collapse in">
									<div class="panel-body">
										<div class="table-responsive">
											<table width="100%" class="display  cell-border stripe hover small" id="tabledata"> 
												<thead>
													<tr>
														<th class="text-center">NO.</th>
														<th class="text-center">NAMA PAJAK</th>
														<th class="text-center">MASA PAJAK</th>
														<th class="text-center">TAHUN PAJAK</th>
														<th class="text-center">PEMBETULAN KE</th>
														<th class="text-center">NAMA DOKUMEN</th>
														<th class="text-center">URL DOC ID</th>
														<th class="text-center">URL DOC ORI</th>
														<th class="text-center">BULAN PAJAK</th>
														<th class="text-center">NAMA DOKUMEN</th>
													</tr>
												</thead>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>                
		</div>
	</div> <!--end list data-->

<!-------------------------------------->
<div id="tambah-data">
	<form role="form" id="form-wp">
	<div class="white-box boxshadow">
	 	
		<div class="row">
			<div class="col-lg-12 align-center">
				<h2 id="capAdd" class="text-center">Tambah Data</h2>
			</div>
		</div>
		
		<div class="row">
			<hr>
		</div>
		<div class="row">
			  <div class="col-lg-6">
						<div class="form-group">
							<input type="hidden" class="form-control" id="isNewRecord" name="isNewRecord">
							<input type="hidden" class="form-control" id="UrlDocId" name="UrlDocId">
							<label>Jenis Pajak</label>
							<select class="form-control" id="inpnamapajak" name="inpnamapajak">
								<?php
								foreach ($list_pajak as $key => $value) {

									if($value->JENIS_PAJAK == "PPN MASUKAN"){
										$nama_pajak_cust = "SSP PPN Masa";
									}
									elseif($value->JENIS_PAJAK == "PPN KELUARAN"){
										$nama_pajak_cust = "SPT PPN Masa";
									}
									else{
										$nama_pajak_cust = str_replace("PPH", "PPh", $value->JENIS_PAJAK);
									}

									if(in_array($value->JENIS_PAJAK, $nama_pajak)){
										?>
										<option value="<?php echo $nama_pajak_cust ?>" data-name="<?php echo $nama_pajak_cust ?>"><?php echo $nama_pajak_cust ?></option>
										<?php
									}
								} ?>
								<?php if(in_array("PPN MASUKAN", $nama_pajak) || in_array("PPN KELUARAN", $nama_pajak)){ ?>
								<option value="BPE PPN" data-name="BPE PPN">BPE PPN</option>
								<option value="CSV Kompilasi PPN Lapor" data-name="BPE PPN">CSV Kompilasi PPN Lapor</option>
								<option value="Lampiran Lain" data-name="Lampiran Lain">Lampiran Lain</option>
								<?php } ?>
							</select> 
						</div>
					</div>
				<div class="col-lg-6">
				<div class="form-group">
					<label>Tahun Pajak</label>
						<select class="form-control" id="inpTahun" name="inpTahun">
							<?php 
								$tahun	= date('Y');
								$tAwal	= $tahun - 5;
								$tAkhir	= $tahun;
								for($i=$tAwal; $i<=$tAkhir;$i++){
									$selected	= ($i==$tahun)?"selected":"";
									echo "<option value='".$i."' ".$selected.">".$i."</option>";
								}
							?>
						</select>
				</div>
			 </div>
		</div>
		<div class="row">
			  <div class="col-lg-6">
				<div class="form-group">
					<label>Masa Pajak</label>
						<select class="form-control" id="inpMasa" name="inpMasa">
							<option value="1" data-name="Januari" >Januari</option>
							<option value="2" data-name="Februari" >Februari</option>
							<option value="3" data-name="Maret" >Maret</option>
							<option value="4" data-name="April" >April</option>
							<option value="5" data-name="Mei" >Mei</option>
							<option value="6" data-name="Juni" >Juni</option>
							<option value="7" data-name="Juli" >Juli</option>
							<option value="8" data-name="Agustus" >Agustus</option>
							<option value="9" data-name="September" >September</option>
							<option value="10" data-name="Oktober" >Oktober</option>
							<option value="11" data-name="November" >November</option>
							<option value="12" data-name="Desember" >Desember</option>
						</select> 
				</div>
			 </div>
			<div class="row">
				<div class="col-lg-6">
					<div class="form-group">
						<label>Pembetulan Ke</label>
						<select class="form-control" id="inpPembetulanKe" name="inpPembetulanKe">
							<option value="0" >0</option>
							<option value="1" >1</option>
							<option value="2" >2</option>
							<option value="3" >3</option>
						</select> 
					</div>
				</div>
			</div>
			 
			<div class="row">
			  <div class="col-lg-6">
				<div class="form-group">
					<label>Nama File *</label>
					<input class="form-control" id="inpFile" name="inpFile" placeholder="Nama File" type="text" maxlength="240">
				</div>
			 </div>
			</div>
			
			<div class="row">
			  <div class="col-lg-6">
				<div class="form-group">
					<label>URL File *</label>
					<input type="text" class="form-control" id="inpURL" name="inpURL" placeholder="Url" >
				</div>
			 </div>
			</div>

		</div>
		
		<div class="white-box boxshadow">
			<div class="row">
			   <div class="col-lg-12">
					 <div class="form-group">
						   <div class="navbar-right">
							<button type="reset" class="btn btn-default"><i class="fa fa-trash-o"></i> RESET</button>
							<button type="button" class="btn btn-danger waves-effect" id="btnBack"><i class="fa fa-reply"></i> BACK</button>
							<button type="button" class="btn btn-info waves-effect" id="btnSave"><i class="fa fa-save"></i> SAVE</button>
						  </div>
					 </div>
				</div>
			</div>
		</div>
	</div>
	</form>
</div>
<!---end tambah data----------------------------------->

<!-- LOV Region : Nama Pajak-->
<div id="modal-namapajak" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="myLargeModalLabel">Daftar Nama Pajak</h4> </div>
			<div class="modal-body">
				<div class="table-responsive">
					<table width="100%" class="display cell-border stripe hover small animated slideInDown" id="tabledata-namapajak"> 
						<thead>
							<tr>
								<th>NO</th>
								<th>NAMA PAJAK</th>
							</tr>
						</thead>
					</table>
				</div>  
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal" id="btnCancel"><i class="fa fa-times-circle"></i>  CANCEL</button>
				<button type="button" class="btn btn-info waves-effect" id="btnChoice" disabled ><i class="fa fa-plus-circle"></i> SELECT</button>
			</div>
		</div>
	</div>
</div>
<!-- end LOV Region : Nama Pajak-->
	
</div>

<script>
$(document).ready(function() {
		var table	= "", 
			l_url_doc_id="";
		var l_nama_pajak 	= "",
			l_masa_pajak 	= "",
			l_bulan_pajak	= "",
			l_tahun_pajak 	= "",
			l_nama_doc 		= "",
			l_pembetulan_ke	= "",
			l_url_doc 		= "";

		$("#tambah-data").hide();
		
		$('#modal-namapajak').modal({
			keyboard: true,
			backdrop: "static",
			show:false,
		});
		
		//untuk list
		Pace.track(	function(){
		$('#tabledata').DataTable({
		    "serverSide"	: true,
			"processing"	: true,
			"ajax"			: {
								 "url"  		: "<?php echo site_url('pph_badan/load_url_doc'); ?>",
								 "type" 		: "POST",
								 "data"	: function ( d ) {
									 	d._searchBulan 	= $('#bulan').val();
										d._searchTahun 	= $('#tahun').val();
										d._searchNama 	= $('#namaPajak').val();
										d._searchKe 	= $('#pembetulanKe').val();
								 },								
							  },
			 "language"		: {
					"emptyTable"	: "Data not found!",	
					"infoEmpty"		: "Empty Data",
					"processing"	:' <img src="<?php echo base_url(); ?>assets/vendor/qrcode/css/images/loading2.gif">',
					"search"		: "_INPUT_"
				},			 
			   "columns": [
					{ "data": "no", "class":"text-center" },
					{ "data": "nama_pajak", "class":"text-left"},
					{ "data": "masa_pajak" },
					{ "data": "tahun_pajak" },
					{ "data": "pembetulan_ke"},
					{ "data": "nama_doc"},
					{ "data": "url_doc_id"},
					{ "data": "url_doc_ori"},
					{ "data": "bulan_pajak"},
					{ "data": "url_doc" }
				],			
			"columnDefs": [ 
				 {
					"targets": [ 5, 6, 7, 8 ],
					"visible": false
				}
			],			
			"fixedColumns"		: false,
			 "select"			: true,
			 "scrollY"			: 360, 
			 "scrollCollapse"	: true, 
			 "scrollX"			: true,
			 "ordering"			: false
			});
		});
		
		table = $('#tabledata').DataTable();
				
		$('#tabledata tbody').on( 'click', 'tr', function () {
			if ( $(this).hasClass('selected') ) {
				$(this).removeClass('selected');
				emptyVar();
				$("#btnEdit, #btnDelete").attr("disabled",true);
			} else {
				table.$('tr.selected').removeClass('selected');
				$(this).addClass('selected');

				var d			= table.row( this ).data();
				//l_url_doc_id	= d.url_doc_id;
				if (d.url_doc_id > 0) {
					$("#UrlDocId").val(d.url_doc_id);
					l_nama_pajak 	= d.nama_pajak;
					l_masa_pajak 	= d.masa_pajak;
					l_tahun_pajak 	= d.tahun_pajak;
					l_nama_doc 		= d.nama_doc;
					l_url_doc 		= d.url_doc_ori;
					l_bulan_pajak 	= d.bulan_pajak;
					l_pembetulan_ke = d.pembetulan_ke;
					
					$("#btnEdit, #btnDelete").removeAttr('disabled');
				} else {
					emptyVar();
					$("#btnEdit, #btnDelete").attr("disabled",true);
				}
			}
		} ).on("dblclick", "tr", function () {
			table.$('tr.selected').removeClass('selected');
			$(this).addClass('selected');
		} );

		$("#btnView").click(function (){
			table.ajax.reload();
		});

	$("#btnAdd").on("click", function(){
		$("#isNewRecord").val("1");
		$("#list-data").slideUp(700);
		$("#tambah-data").slideDown(700);
		$("#capAdd").html("<span class='label label-danger'>Tambah File Dokumen Pelaporan</span>");
			
	});
	
	$("#btnEdit").on("click", function(){
		$("#isNewRecord").val("0");
		$("#list-data").slideUp(700);
		$("#tambah-data").slideDown(700);
		$("#capAdd").html("<span class='label label-danger'>Update File Dokumen Pelaporan</span>");
		valueGrid();
	});

	$("#btnBack").on("click", function(){
		$("#tambah-data").slideUp(700);
		$("#list-data").slideDown(700);
		emptyVar();
	});

	function valueGrid()
	{
		$("#inpnamapajak").val(l_nama_pajak);
		$("#inpMasa").val(l_bulan_pajak);
		$("#inpTahun").val(l_tahun_pajak);
		$("#inpFile").val(l_nama_doc);
		$("#inpURL").val(l_url_doc);
		$("#inpPembetulanKe").val(l_pembetulan_ke);
	}

	function emptyVar()
	{
		l_nama_pajak 	= "";
		l_masa_pajak 	= "";
		l_tahun_pajak 	= "";
		l_nama_doc 		= "";
		l_url_doc 		= "";
		l_pembetulan_ke = "";
		
		$("#inpFile").val("");
		$("#inpURL").val("");
	}
//end untuk list
		
	function batal()
	{
		vnama	        = "";
	}	
		
//end call LOV page utk Nama Pajak

// call save tambah data
	$("#btnSave").click(function(){
		$.ajax({
			url		: baseURL + 'pph_badan/save_url_doc',
			type	: "POST",
			data	: $('#form-wp').serialize(),
			beforeSend	: function(){
				 $("body").addClass("loading");
			},
			success	: function(result){
				if (result==1) {
					 table.draw();
					 $("body").removeClass("loading");
					 $("#list-data").slideDown(700);
					 $("#tambah-data").slideUp(700);
					 flashnotif('Sukses','Data Berhasil di Simpan!','success' );
					 emptyVar();
				} else {
					 $("body").removeClass("loading");
					 flashnotif('Error',result,'error' );
				}
			}
		});
	});
// end of save tambah data	

// call delete data
	$("#btnDelete").click(function(){
		  bootbox.confirm({
			title: "Hapus data <span class='label label-danger'>"+l_nama_doc+"</span> ?",
			message: "Apakah anda ingin melanjutkan?",
			buttons: {
				cancel: {
					label: '<i class="fa fa-times-circle"></i> CANCEL'
				},
				confirm: {
					label: '<i class="fa fa-check-circle"></i> YES'
				}
			},
			callback: function (result) {
				if(result) {
					$.ajax({
						url		: baseURL + 'pph_badan/delete_url_doc',
						type	: "POST",
						data	: $('#form-wp').serialize(),
						beforeSend	: function(){
							 $("body").addClass("loading");
							},
						success	: function(result){
							if (result==1) {
								 $("body").removeClass("loading");
								 table.draw();
								 flashnotif('Sukses','Data Berhasil di Hapus!','success' );
							} else {
								 $("body").removeClass("loading");
								 flashnotif('Error','Data Gagal di Hapus!','error' );
							}
							
						}
					});
				}
			}
		});
	});
// end delete data	
 });
</script>