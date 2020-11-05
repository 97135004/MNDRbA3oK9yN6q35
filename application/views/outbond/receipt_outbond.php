<div class="container-fluid">

	<?php $this->load->view('template_top') ?>

	<div id="list-data">
		<div class="row">
			<div class="col-lg-12">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="row">
							<div class="col-lg-6">
								DAFTAR RECEIPT OUTBOUND
							</div>
							<div class="col-lg-6">
								<div class="navbar-right">
									<!-- <button id="btnTambah" class="btn btn-default btn-rounded custom-input-width" data-toggle="modal" data-target="#modal-tambah" type="button"><i class="fa fa-pencil-square-o"></i> ADD</button>
									<button type="button" id="btnEdit" class="btn btn-rounded btn-default custom-input-width" disabled data-toggle="modal" data-target="#modal-wp"><i class="fa fa-pencil"></i> EDIT</button>
									<button type="button" id="btnHapus" class="btn btn-rounded btn-default custom-input-width " disabled data-toggle="modal" data-target="#modal-hapus"><i class="fa fa-trash-o"></i> DELETE</button> -->
								</div>
							</div>
						</div>
					</div>
					<!-- /.panel-heading -->
					<style>
						table.dataTable tr th.select-checkbox.selected::after {
							content: "✔";
							margin-top: -11px;
							margin-left: -4px;
							text-align: center;
							text-shadow: rgb(176, 190, 217) 1px 1px, rgb(176, 190, 217) -1px -1px, rgb(176, 190, 217) 1px -1px, rgb(176, 190, 217) -1px 1px;
						}
					</style>
					<div class="panel-body">
						<div class="table-responsive">
							<table width="100%" class="display cell-border stripe hover small" id="tabledata">
								<thead>
									<tr>
										<th></th>
										<th class="text-center">ITEM NUMBER</th>
										<th class="text-center">DESCRIPTION</th>
										<th class="text-center">SPEC</th>
										<th class="text-center">LOCATOR</th>
										<th class="text-center">RECEIPT NUMBER</th>
										<th class="text-center">RECEIPT DATE</th>
										<th class="text-center">PO NUMBER</th>
										<th class="text-center">QUANTITY</th>
										<th class="text-center">UOM</th>
										<th class="text-center">ACTIONS</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!--Form Tambah-->
		<!-- <div id="edit-data">
		<div id="error-add" class="alert alert-danger alert-dismissable hidden"></div>
		<div id="error-edit" class="alert alert-danger alert-dismissable hidden"></div>
		<form role="form" id="form-cs-tambah">
			<div class="white-box boxshadow">
				<div class="row">
					<div class="col-lg-12 align-center">
						<h2 id="capAdd" class="text-center">Data Pelanggan</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>NAMA PELANGGAN</label>
							<input type="hidden" class="form-control" id="customer_id" name="customer_id" placeholder="customer ID (Number) * (Tidak Boleh Kosong)">
							<input type="hidden" class="form-control" id="isNewRecord" name="isNewRecord">
							<input type="hidden" class="form-control" id="customer_site_id" name="customer_site_id" placeholder="customer ID (Number) * (Tidak Boleh Kosong)">
							<input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Nama Customer *(Tidak Boleh Kosong)">
						</div>
					</div>



					<div class="col-lg-6">
						<div class="form-group">
							<label>ALIAS PELANGGAN</label>
							<input type="text" class="form-control" id="alias_customer" name="alias_customer" placeholder="alias Customer ">
						</div>
					</div>

					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>NOMOR PELANGGAN</label>
								<input type="text" class="form-control" id="customer_number" name="customer_number" placeholder="Nomor Customer">
							</div>
						</div>

						<div class="col-lg-6">
							<div class="form-group">
								<label>NPWP </label>
								<input type="text" class="form-control" id="npwp" name="npwp" placeholder="NPWP *(Tidak Boleh Kosong)">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>OPERATING UNIT</label>
								<input type="text" class="form-control" id="operating_unit" name="operating_unit" placeholder="OPERATING UNIT">
							</div>
						</div>


						<div class="col-lg-6">
							<div class="form-group">
								<label>CUSTOMER SITE NAME</label>
								<input type="text" class="form-control" id="customer_site_name" name="customer_site_name" placeholder="CUSTOMER SITE NAME">
							</div>
						</div>
					</div>


					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>ALAMAT LINE1</label>
								<input type="text" class="form-control" id="address_line1" name="address_line1" placeholder="ADDRESS LINE1 *(Tidak Boleh Kosong)">
							</div>
						</div>

						<div class="col-lg-6">
							<div class="form-group">
								<label>ALAMAT LINE2</label>
								<input type="text" class="form-control" id="address_line2" name="address_line2" placeholder="ADDRESS LINE2">
							</div>
						</div>
					</div>


					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>ALAMAT LINE3</label>
								<input type="text" class="form-control" id="address_line3" name="address_line3" placeholder="ADDRESS LINE3">
							</div>
						</div>

						<div class="col-lg-6">
							<div class="form-group">
								<label>NAMA KOTA </label>
								<input type="text" class="form-control" id="city" name="city" placeholder="CITY">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>PROPINSI</label>
								<input type="text" class="form-control" id="province" name="province" placeholder="PROVINCE">
							</div>
						</div>



						<div class="col-lg-6">
							<div class="form-group">
								<label>NEGARA </label>
								<input type="text" class="form-control" id="country" name="country" placeholder="COUNTRY * (Tidak Boleh Kosong)">
							</div>
						</div>
					</div>

					<div class="col-lg-6">
						<div class="form-group">
							<label>KODE POS</label>
							<input type="text" class="form-control" id="zip" name="zip" placeholder="ZIP">
						</div>
					</div>
				</div>


				<div class="white-box boxshadow">
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<div class="navbar-right">
									<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal" id="btnBack"><i class="fa fa-times-circle"></i> CANCEL</button>
									<button type="button" class="btn btn-info waves-effect" id="btnSave"><i class="fa fa-save"></i> SAVE</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div> -->

		<script>
			$(document).ready(function() {
				var table = "",
					vstock_receipt_id = "",
					vitem_number = "",
					vstock_item_description = "",
					vstock_item_spec = "",
					voem_model_no = "",
					voem_part_no = "",
					vsubinventory = "",
					vlocator = "",
					vlot_number = "",
					vreceipt_number = "",
					vreceipt_date = "",
					vpo_number = "",
					vquantity = "",
					vuom = "",
					vlocator_segment1 = "",
					vlocator_segment2 = "",
					vlocator_segment3 = "",
					vlocator_segment4 = "",
					vlocator_segment5 = "",
					vlocator_segment6 = "",
					vlocator_segment7 = "",
					vlocator_segment8 = "";

				$("#btnHapus").hide();
				$("#edit-data").hide();

				Pace.track(function() {
					$('#tabledata').removeAttr('width').DataTable({
						"serverSide": true,
						"processing": true,
						"pageLength": 100,
						"lengthMenu": [[100, 250, 500, 1000],[100, 250, 500, 1000]],
						"ajax": {
							"url": "<?php echo site_url('outbond/load_rec_out'); ?>",
							"type": "POST",
							"beforeSend": function() {

							}
						},
						"language": {
							"emptyTable": "Data not found!",
							"infoEmpty": "Empty Data",
							"processing": ' <img src="<?php echo base_url(); ?>assets/vendor/qrcode/css/images/loading2.gif">',
							"search": "_INPUT_"
						},
						"columns": [
							{ "orderable": "false", "class": "select-checkbox" },
							{ "data": "item_number", "class": "text-left", "width": "60px" },
							{ "data": "stock_item_description" },
							{ "data": "stock_item_spec" },
							{ "data": "locator" },
							{ "data": "receipt_number" },
							{ "data": "receipt_date" },
							{ "data": "po_number" },
							{ "data": "quantity" },
							{ "data": "uom" },
							{ "data": "display_name",
							  "width": "80px",
							  "class": "text-center",
							  "render": function(data) {
							return '<a href="javascript:void(0)" class="action-edit" title="Click to print ' + data + '" style="color:#ff6436"><i class="fa fa-edit" aria-hidden="true"></i></a>';}
							  }
							
						],
						"createdRow": function(row, data, dataIndex) {

						},
						"columnDefs": [{
							"searchable": false,
							"orderable": false,
							"targets" : 0
						}],
						//"fixedColumns"		: true,			
			/* fixedColumns:   {
						leftColumns: 1,
						//rightColumns: 1
        },	 */
						"select": [{
							"style":"os",
							"selector":"td:first-child"
						}],
						"scrollY": 360,
						"scrollCollapse": true,
						"scrollX": true,
						"ordering": false
					});
				});

				table = $('#tabledata').DataTable();

				$("input[type=search]").addClear();
				$('.dataTables_filter input[type="search"]').attr('placeholder', 'Search item number / description / spec ...').css({
					'width': '230px',
					'display': 'inline-block'
				}).addClass('form-control input-sm');

				$("#tabledata_filter .add-clear-x").on('click', function() {
					table.search('').column().search('').draw();
				});

				table.on('draw', function() {
					$("#btnEdit,#btnHapus").attr("disabled", true);
				});

				$('#tabledata tbody').on('click', 'tr', function() {
					if ($(this).hasClass('selected')) {
						$(this).removeClass('selected');
						vstock_receipt_id = "";
						vitem_number = "";
						vstock_item_description = "";
						vstock_item_spec = "";
						voem_model_no = "";
						voem_part_no = "";
						vsubinventory = "";
						vlocator = "";
						vlot_number = "";
						vreceipt_number = "";
						vreceipt_date = "";
						vpo_number = "";
						vquantity = "";
						vuom = "";
						vlocator_segment1 = "";
						vlocator_segment2 = "";
						vlocator_segment3 = "";
						vlocator_segment4 = "";
						vlocator_segment5 = "";
						vlocator_segment6 = "";
						vlocator_segment7 = "";
						vlocator_segment8 = "";
						$("#btnEdit,#btnHapus").attr("disabled", true);
					} else {
						table.$('tr.selected').removeClass('selected');
						$(this).addClass('selected');
						var d = table.row(this).data();

						vstock_receipt_id = d.stock_receipt_id;
						vitem_number = d.item_number;
						vstock_item_description = d.stock_item_description;
						vstock_item_spec = d.stock_item_spec;
						voem_model_no = d.oem_model_no;
						voem_part_no = d.oem_part_no;
						vsubinventory = d.subinventory;
						vlocator = d.locator;
						vlot_number = d.lot_number;
						vreceipt_number = d.receipt_number;
						vreceipt_date = d.receipt_date;
						vpo_number = d.po_number;
						vquantity = d.quantity;
						vuom = d.uom;
						vlocator_segment1 = d.locator_segment1;
						vlocator_segment2 = d.locator_segment2;
						vlocator_segment3 = d.locator_segment3;
						vlocator_segment4 = d.locator_segment4;
						vlocator_segment5 = d.locator_segment5;
						vlocator_segment6 = d.locator_segment6;
						vlocator_segment7 = d.locator_segment7;
						vlocator_segment8 = d.locator_segment8;
						$("#btnEdit,#btnHapus").removeAttr('disabled');
						valueGrid();
					}

				}).on("dblclick", "tr", function() {
					table.$('tr.selected').removeClass('selected');
					$(this).addClass('selected');
					var d = table.row(this).data();
				});

				$('.modal').on('shown.bs.modal', function() {
					$('#stock_receipt_id').trigger('focus')
				})

				$("#btnEdit").click(function() {
					$("#list-data").slideUp(700);
					$("#edit-data").slideDown(700);
					$("#isNewRecord").val("0");
					$("#capAdd").html("<span class='label label-danger'>Edit Data Stock Receipt</span>");
					valueGrid();
				});

				$("#btnTambah").click(function() {
					$("#list-data").slideUp(700);
					$("#edit-data").slideDown(700);
					$("#isNewRecord").val("1");
					$("#capAdd").html("<span class='label label-danger'>Tambah Data Stock Receipt</span>");
					emptyGrid();
				});

				$("#btnBack").click(function() {
					$("#list-data").slideDown(700);
					$("#edit-data").slideUp(700);
					emptyGrid();
				});

				function valueGrid() {
					$("#stock_receipt_id").val(vstock_receipt_id);
					$("#item_number").val(vitem_number);
					$("#stock_item_description").val(vstock_item_description);
					$("#stock_item_spec").val(vstock_item_spec);
					$("#oem_model_no").val(voem_model_no);
					$("#oem_part_no").val(voem_part_no);
					$("#subinventory").val(vsubinventory);
					$("#locator").val(vlocator);
					$("#lot_number").val(vlot_number);
					$("#receipt_number").val(vreceipt_number);
					$("#receipt_date").val(vreceipt_date);
					$("#po_number").val(vpo_number);
					$("#quantity").val(vquantity);
					$("#uom").val(vuom);
					$("#locator_segment1").val(vlocator_segment1);
					$("#locator_segment2").val(vlocator_segment2);
				}

				function emptyGrid() {
					$("#stock_receipt_id").val("");
					$("#item_number").val("");
					$("#stock_item_description").val("");
					$("#stock_item_spec").val("");
					$("#oem_model_no").val("");
					$("#oem_part_no").val("");
					$("#subinventory").val("");
					$("#locator").val("");
					$("#lot_number").val("");
					$("#receipt_number").val("");
					$("#receipt_date").val("");
					$("#po_number").val("");
					$("#quantity").val("");
					$("#uom").val("");
					$("#locator_segment1").val("");
					$("#locator_segment2").val("");
				}
			});
		</script>