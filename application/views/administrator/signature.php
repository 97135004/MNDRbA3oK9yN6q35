<div class="container-fluid">
    <?php $this->load->view('template_top') ?>

    <div id="table-signature" class="row">  
        <div class="col-lg-12"> 
            <div class="panel panel-info boxshadow animated slideInDown">
                <div class="panel-heading">
                    <div class="row">
                      <div class="col-sm-3">
                        DAFTAR PENANDA TANGAN
                      </div>
                      <div class="col-sm-9">
                        <div class="navbar-right">
                            <button type="button" id="btnAdd" class="btn btn-rounded btn-default custom-input-width" ><i class="fa fa-plus"></i> ADD </button>
                            <button type="button" id="btnEdit" class="btn btn-rounded btn-default custom-input-width" disabled ><i class="fa fa-pencil"></i> EDIT </button>
                            <!-- <button type="button" id="btnDel" class="btn btn-rounded btn-default custom-input-width" disabled ><i class="fa fa-trash"></i> DELETE </button> -->
                        </div>
                      </div>
                    </div>
                </div>
                <div class="panel-body"> 
                    <div class="table-responsive">
                        <table width="100%" class="display cell-border stripe hover small" id="tabledata">
                            <thead>
                                <tr>
                                    <th class="text-center">ID.</th>
                                    <th class="text-center">NO.</th>
                                    <th class="text-center">JENIS DOKUMEN</th>
                                    <th class="text-center">NAMA PEMOTONG</th>
                                    <th class="text-center">NPWP PEMOTONG</th>
                                    <th class="text-center">NPWP PETUGAS</th>
                                    <th class="text-center">ALAMAT PEMOTONG</th>
                                    <th class="text-center">NAMA PETUGAS</th>
                                    <th class="text-center">JABATAN PETUGAS</th>
                                    <th class="text-center">TANGGAL AKTIF</th>
                                    <th class="text-center">TANGGAL NON AKTIF</th>
                                    <th class="text-center">NAMA PAJAK</th>
                                    <th class="text-center">NAMA KPP</th>
                                    <th class="text-center">TTD CAP PETUGAS</th>
                                    <th class="text-center">FILE PENUGASAN</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-edit" role="dialog" aria-labelledby="modal-edit">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="title_form"></h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="error-edit" class="alert alert-danger alert-dismissable hidden"></div>
                    <form role="form" id="form-edit" data-toggle="validator">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="hidden" class="form-control" id="isNewRecord" name="isNewRecord">
                                    <input type="hidden" class="form-control" id="id" name="id">
                                    <label class="form-control-label">Nama Pemotong</label>
                                    <input name="nama_pemotong" id="editNamaPemotong" type="text" class="form-control" placeholder="Nama Pemotong" data-toggle="validator" data-error="Please fill Nama Pemotong" required>
                                    <div class="help-block with-errors"></div> 
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">NPWP Pemotong</label>
                                    <input name="npwp_pemotong" id="editNpwpPemotong" type="text" class="form-control" placeholder="NPWP Pemotong" data-toggle="validator" data-error="Please fill NPWP Pemotong" required>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-control-label">Alamat Pemotong</label>
                                    <textarea name="alamat_pemotong" id="editAlamatPemotong" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Nama Petugas</label>
                                    <input name="nama_petugas" id="editNamaPetugas" type="text" class="form-control" placeholder="Nama Petugas" data-toggle="validator" data-error="Please fill Nama Petugas" required>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Jabatan Petugas</label>
                                    <input name="jabatan_petugas" id="editJabatanPetugas" type="text" class="form-control" placeholder="Jabatan Petugas" data-toggle="validator" data-error="Please fill Jabatan Petugas" required>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">NPWP Petugas</label>
                                    <input name="npwp_petugas" id="editNpwpPetugas" type="text" class="form-control" placeholder="NPWP Petugas">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Jenis Pajak</label>
                                    <select id="editNamaPajak" name="inputNamaPajak" class="form-control" autocomplete="off" data-toggle="validator" data-error="Please fill Jenis Pajak" required>
                                        <option value=""> Choose Jenis Pajak</option>
                                        <?php
                                        foreach ($jenis_pajak as $key => $value) {
                                            if($value->JENIS_PAJAK == "PPN MASUKAN"){
                                                $nama_pajak_cust = "SSP PPN Masa";
                                            }
                                            elseif($value->JENIS_PAJAK == "PPN KELUARAN"){
                                                $nama_pajak_cust = "SPT PPN Masa";
                                            }
                                            else{
                                                $nama_pajak_cust = $value->JENIS_PAJAK;
                                            }
                                        ?>
                                        <option value="<?php echo $nama_pajak_cust ?>"> <?php echo $nama_pajak_cust ?> </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Nama Kantor Pelayanan Pajak</label>
                                    <input name="inputNamaKPP" id="editNamaKPP" type="text" class="form-control" placeholder="Nama Kantor Pelayanan Pajak"> 
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Jenis Dokumen</label>
                                    <select id="document_type" name="document_type" class="form-control" autocomplete="off" data-toggle="validator" data-error="Mohon isi Jenis Dokumen" required>
                                        <option value=""> Pilih Jenis Dokumen</option>
                                        <option value="Bukti Potong">Bukti Potong</option>
                                        <!-- <option value="Daftar Bupot">Daftar Bupot</option> -->
                                        <option value="Ekualisasi">Ekualisasi</option>
                                        <option value="Monthly Recap">Monthly Recap</option>
                                        <option value="Anual Recap">Anual Recap</option>
                                        <option value="SPT Summary">SPT Summary</option>
                                        <option value="1721A1">1721A1</option>
                                    </select>
                                    <div class="help-block with-errors"></div>
                                </div>  
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                  <div class="form-group">
                                  <label class="form-control-label">Cap Tanda Tangan</label>
                                  <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                      <div class="form-control" data-trigger="fileinput"> <i class="glyphicon glyphicon-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file</span> <span class="fileinput-exists">Change</span>
                                      <input class="cap_tandatangan" id="editCap" type="file" name="cap_tandatangan" accept=".jpg,.jpeg,.png"> </span> <a href="#" id="cap_remove" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                                  </div>
                                  <span style="color:grey;font-size: 12px">Extention required is (.jpg, jpeg, .png), max 2 mb</span>
                                  <div class="help-block with-errors"></div>
                                  <div id="file_cap"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                  <label class="form-control-label">File Penugasan</label>
                                  <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                      <div class="form-control" data-trigger="fileinput"> <i class="glyphicon glyphicon-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file</span> <span class="fileinput-exists">Change</span>
                                      <input class="file_penugasan" id="editFile" type="file" name="file_penugasan" accept=".pdf"> </span> <a href="#" id="file_remove" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                                  </div>
                                  <span style="color:grey;font-size: 12px">Extention required is pdf, ukuran maks 2 mb</span>
                                  <div class="help-block with-errors"></div>
                                  <div id="file_tugas"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Tanggal Aktif</label>
                                    <div class="input-group">
                                        <input name="tgl_aktif" type="text" id="editTglAktif" class="form-control datepicker" placeholder="dd-mm-yyyy" data-toggle="validator" data-error="Mohon isi Tanggal Aktif" required> <span class="input-group-addon"><i class="icon-calender"></i></span>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Tanggal Non Aktif</label>
                                    <div class="input-group">
                                        <input name="tgl_inaktif" type="text" id="editTglInaktif" class="form-control datepicker" placeholder="dd-mm-yyyy" data-toggle="validator" data-error="Mohon isi Tanggal Non Aktif" required> <span class="input-group-addon"><i class="icon-calender"></i></span>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="reset" class="btn btn-default"><i class="fa fa-trash-o"></i> RESET</button>
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal"><i class="fa fa-reply"></i> CANCEL</button>
                        <button type="submit" class="btn btn-info waves-effect" id="btnSave"><i class="fa fa-save"></i> SAVE</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-delete" role="dialog" aria-labelledby="modal-delete">
      <div class="modal-dialog">
        <div class="modal-content">
           <form role="form" id="form-delete">
                <input type="hidden" class="form-control" id="id-delete" name="id">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Konfirmasi</h4>
                </div>
                <div class="modal-body">
                    Apakah anda ingin melanjutkan?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">CANCEL</button>
                    <button type="submit" class="btn btn-info" id="btnConfirm" name="confirm" value="Yes">YES</button>
                </div>
            </form>
        </div>
      </div>
    </div>

</div><!-- ./container -->
</div><!-- ./container -->

<script>
$(document).ready(function() {
    $('#modal-add').modal({
        keyboard: true,
        backdrop: "static",
        show:false,
    });

   Pace.track(function(){
           $('#tabledata').DataTable({
            "serverSide"    : true,
            "processing"    : true,
            "ajax"          : {
                                 "url"          : baseURL + 'signature/get_signature',
                                 "type"         : "POST"
                                },

             "language"     : {
                    "emptyTable"    : "<span class='label label-danger'>Data Tidak Ditemukan!</span>",  
                    "infoEmpty"     : "Data Kosong",
                    "processing"    :' <img src="' + baseURL + 'assets/vendor/qrcode/css/images/loading2.gif">',
                    "search"        : "_INPUT_"
                },
               "columns": [
                    { "data": "id", "width":"10px", "class":"text-center"},
                    { "data": "no", "width":"10px", "class":"text-center"},
                    { "data": "document_type", "width":"200px", "class":"text-center"},
                    { "data": "nama_pemotong", "width":"200px", "class":"text-center"},
                    { "data": "npwp_pemotong", "width":"200px", "class":"text-center"},
                    { "data": "npwp_petugas", "width":"200px", "class":"text-center"},
                    { "data": "alamat_pemotong", "width":"100px"},
                    { "data": "nama_petugas", "width":"100px"},
                    { "data": "jabatan_petugas", "width":"100px"},
                    { "data": "start_effective_date", "width":"100px"},
                    { "data": "end_effective_date", "width":"100px"},
                    { "data": "nama_pajak", "width":"100px"},
                    { "data": "nama_kpp", "width":"100px"},
                    { "data": "link_cap", "width":"100px"},
                    { "data": "link_file", "width":"100px"},
                ],
            "columnDefs": [
                {
                    "targets": [0],
                    "visible": false,
                }
            ],
             "select"           : true,
             "scrollY"          : 400, 
             "scrollCollapse"   : true, 
             "scrollX"          : true,
             "pageLength"       : 10,
             "ordering"         : false,
             "bAutoWidth" : false
            });
         });

        table = $('#tabledata').DataTable();
        
        $("#table-signature input[type=search]").addClear();

        $('#table-signature .dataTables_filter input[type="search"]').attr({'placeholder':'Search Jenis Dokumen, Nama, NPWP, Nama Petugas, Jabatan', 'title':'Search Jenis Dokumen, Nama, NPWP, Nama Petugas, Jabatan'}).css({'width':'300px','display':'inline-block'}).addClass('form-control input-sm');
        $("#tabledata_filter .add-clear-x").on('click',function(){
            table.search('').column().search('').draw();
        });

        table.on( 'draw', function () {
            $("#btnEdit").attr("disabled",true);
        });

        $('#tabledata tbody').on( 'click', 'tr', function () {
            if ( $(this).hasClass('selected') ) {
                $(this).removeClass('selected');
                empty();
            } else {
                table.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
                var data           = table.row( this ).data();
                id                 = data.id;
                editNamaPemotong   = data.nama_pemotong;
                editNpwpPemotong   = data.npwp_pemotong;
                editAlamatPemotong = data.alamat_pemotong;
                editNamaPetugas    = data.nama_petugas;
                editJabatanPetugas = data.jabatan_petugas;
                file_cap           = data.link_cap;
                file_tugas         = data.link_file;
                editTglAktif       = data.start_effective_date;
                editTglInaktif     = data.end_effective_date;
                editNamaPajak      = data.nama_pajak;
                editNamaKPP        = data.nama_kpp;
                document_type      = data.document_type;
                editNpwpPetugas    = data.npwp_petugas;
                valueGrid();
                $("#btnEdit").removeAttr('disabled');
                $("#btnDel").removeAttr('disabled');
            }

        } ).on("dblclick", "tr", function () {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var data           = table.row( this ).data();
            id                 = data.id;
            editNamaPemotong   = data.nama_pemotong;
            editNpwpPemotong   = data.npwp_pemotong;
            editAlamatPemotong = data.alamat_pemotong;
            editNamaPetugas    = data.nama_petugas;
            editJabatanPetugas = data.jabatan_petugas;
            file_cap           = data.link_cap;
            file_tugas         = data.link_file;
            editTglAktif       = data.start_effective_date;
            editTglInaktif     = data.end_effective_date;
            editNamaPajak      = data.nama_pajak;
            editNamaKPP        = data.nama_kpp;
            document_type      = data.document_type;
            editNpwpPetugas    = data.npwp_petugas;

            valueGrid();
            $("#btnEdit").removeAttr('disabled');
            $("#btnDel").removeAttr('disabled');
            $("#btnEdit").trigger('click');

        } );

    /*$('#modal-edit').on('shown.bs.modal', function () {
        empty();
    });*/
    /*$('#modal-edit').on('hide.bs.modal', function(){
        empty();
    });*/

    $('#editFile').bind('change', function() {
        fileSize        = this.files[0].size;
        extension_allow = ['pdf'];
        extension       = this.files[0].name.split('.').pop().toLowerCase();
        if (extension_allow.indexOf(extension) < 0) {
            alert('Tipe file harus gambar PDF');
            $(".file_penugasan").val('');
        }
        if(fileSize > 2000000){
            alert('File tidak boleh lebih dari 2 mb');
            $(".file_penugasan").val('');
        }
    });
    $('#editCap').bind('change', function() {
        fileSize        = this.files[0].size;
        extension_allow = ['jpg', 'jpeg', 'png'];
        extension       = this.files[0].name.split('.').pop().toLowerCase();
        if (extension_allow.indexOf(extension) < 0) {
            alert('Tipe file harus gambar PDF');
            $(".cap_tandatangan").val('');
        }
        if(fileSize > 2000000){
            alert('File tidak boleh lebih dari 2 mb');
            $(".cap_tandatangan").val('');
        }
    });

    function empty()
    {
        isNewRecord = $("#isNewRecord").val();
        editCap     = $("#editCap").val();
        editFile    = $("#editFile").val();
        if ($('#form-edit').find('.has-error, .has-danger').length > 0) {
            $(".help-block").html('');
            $('.has-error').removeClass('has-error');
        }
        if(isNewRecord == 1){
            $("#document_type").val('');
            $("#editNamaPemotong").val('');
            $("#editNpwpPemotong").val('');
            $("#editAlamatPemotong").val('');
            $("#editNamaPetugas").val('');
            $("#editJabatanPetugas").val('');
            $("#editNamaPajak").val('');
            $("#editNamaKPP").val('');
            $("#editTglInaktif").val('');
            $("#editTglAktif").val('');
            $("#editNpwpPetugas").val('');
            $("#file_cap").html('');
            $("#file_tugas").html('');
            $("#editCap").attr("data-toggle","validator");
            $("#editCap").attr("data-error", "Please fill Cap Tanda Tangan");
            $("#editCap").attr("required", "");
            $("#editFile").attr("data-toggle","validator");
            $("#editFile").attr("data-error", "Please fill File Penugasan");
            $("#editFile").attr("required", "");
        }
        else{
            $("#editCap").removeAttr("data-toggle");
            $("#editCap").removeAttr("data-error");
            $("#editCap").removeAttr("required");
            $("#editFile").removeAttr("data-toggle");
            $("#editFile").removeAttr("data-error");
            $("#editFile").removeAttr("required");
        }
        if(editCap != "" && editFile != ""){
            $('#cap_remove').trigger('click');
        }
        else if(editCap != "" && editFile == ""){
            $('#cap_remove').click();
        }
        else if(editCap == "" && editFile != ""){
            $('#file_remove').click();
        }
    }

    $(window).error(function(e){
        e.preventDefault();
        if(editCap != "" && editFile != ""){
            $('#file_remove').click();
        }
    });

    function valueGrid()
    {
        $("#id").val(id);
        $("#id-delete").val(id);
        $("#document_type").val(document_type);
        $("#editNamaPemotong").val(editNamaPemotong);
        $("#editNpwpPemotong").val(editNpwpPemotong);
        $("#editAlamatPemotong").val(editAlamatPemotong);
        $("#editNamaPetugas").val(editNamaPetugas);
        $("#editJabatanPetugas").val(editJabatanPetugas);
        $("#editTglAktif").val(editTglAktif);
        $("#editTglInaktif").val(editTglInaktif);
        $("#editNamaPajak").val(editNamaPajak);
        $("#editNamaKPP").val(editNamaKPP);
        $("#file_cap").html(file_cap);
        $("#file_tugas").html(file_tugas);
        $("#editNpwpPetugas").val(editNpwpPetugas);
    }

    $("#btnAdd").click(function (){
        $("#isNewRecord").val('1');
        $("#title_form").html('Add New Record');
        empty();
        $("#modal-edit").modal('show');
    });
    $("#btnEdit").click(function (){
        $("#isNewRecord").val('0');
        $("#title_form").html('Edit Record');
        empty();
        $("#modal-edit").modal('show');
    });
    $("#btnDel").click(function (){
        $("#modal-delete").modal('show');
    });

    $('#form-edit').validator().on('submit', function(e) {
      if (e.isDefaultPrevented()) {
        console.log('tidak valid');
      }
      else {
        console.log('submit');
        var form = $('#form-edit')[0];
        var data = new FormData(form);
        var id   = '';
        if(isNewRecord == 0){
            id = $("#id").val();
            success = 'Record updated';
            error   = 'Failed to update record'
        }
        else{
            success = 'Record added';
            error   = 'Failed to add new record'
        }
        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: baseURL + 'signature/save_signature/' + id,
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success : function(result){
                console.log(result);
                if (result==1) {
                    table.ajax.reload(null, false);
                    $("#modal-edit").modal('hide');
                    flashnotif('Success', success, 'success');
                } else {
                    flashnotif('Error', error, 'error');
                }
            }
        }); 
        return false;
      }

      e.preventDefault();

    });

    // Form Delete
    $("#btnConfirm").click(function(){
        var id = $("#id-delete").val();
        $.ajax({
            url     : baseURL + 'signature/delete_signature/'+ id,
            type    : "POST",
            data    : $('#form-delete').serialize(),
            success : function(result){
                if (result==1) {
                    table.ajax.reload(null, false);
                    flashnotif('Success','Record deleted','success');
                    $("#modal-delete").modal('hide');
                } else {
                    flashnotif('Error','Failed to delete record!','error');
                }
            }
        }); 
        return false;
    });

});

</script>

<!-- Date Picker Plugin JavaScript -->
<script src="<?php echo base_url('assets/plugins/') ?>bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
<script src="<?php echo base_url('assets/') ?>js/jasny-bootstrap.js"></script>

<script>
    jQuery('.datepicker').datepicker({
        autoclose: true
        , todayHighlight: true
        , format: 'dd/mm/yyyy'
        //, startDate: "dateToday"
    });
</script>