<div class="container-fluid">
    <?php $this->load->view('template_top') ?>
<div class="row">  
        <div class="col-lg-12"> 
            <div class="panel panel-info boxshadow animated slideInDown">
                <div class="panel-heading">
                    <div class="row">
                      <div class="col-sm-3">
                            <?php echo $subtitle ?>
                      </div>
                      <div class="col-sm-9">
                        <div class="navbar-right">
                            <button type="button" id="btnAdd" class="btn btn-rounded btn-default custom-input-width" ><i class="fa fa-plus"></i> ADD </button>
                            <button type="button" id="btnEdit" class="btn btn-rounded btn-default custom-input-width" disabled ><i class="fa fa-pencil"></i> EDIT </button>
                            <button type="button" id="btnDel" class="btn btn-rounded btn-default custom-input-width" disabled ><i class="fa fa-trash"></i> DELETE </button>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="panel-body"> 
                    <div class="table-responsive">
                        <table width="100%" class="display cell-border stripe hover small" id="tabledata">
                            <thead>
                                <tr>
                                    <th class="text-center">NO.</th>
                                    <th class="text-center">ID.</th>
                                    <th class="text-center">BULAN PAJAK</th>
                                    <th class="text-center">NAMA PAJAK</th>
                                    <th class="text-center">MASA PAJAK</th>
                                    <th class="text-center">TAHUN PAJAK</th>
                                    <th class="text-center">PEMBETULAN</th>
                                    <th class="text-center">CABANG</th>
                                    <th class="text-center">FILE NAME</th>
                                    <th class="text-center">KETERANGAN</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="container">

    <div class="modal fade" id="modal-edit" role="dialog" aria-labelledby="modal-edit">
        <div class="modal-dialog modal-md">
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
                                <label class="form-control-label">Jenis Pajak</label>
                                <select name="nama_pajak" id="nama_pajak" class="form-control">
                                    <option value="">-- Pilih Jenis Pajak --</option>
                                    <?php
                                    foreach ($jenis_pajak as $key => $value) {
                                        if($value == "PPN MASUKAN"){
                                            $nama_pajak = "PPN IMPOR";
                                        }
                                        elseif($value == "PPN KELUARAN"){
                                            $nama_pajak = "PPN MASA";
                                        }
                                        else{
                                            $nama_pajak = $value;
                                        }
                                    ?>
                                    <option value="<?php echo $nama_pajak ?>"> <?php echo $nama_pajak ?> </option>
                                    <?php
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Masa Pajak</label>
                                <select id="masa_pajak" name="masa_pajak" class="form-control">
                                    <option value="">-- Pilih Masa Pajak --</option>
                                    <?php
                                        $namaBulan = list_month();
                                        for ($i=1;$i< count($namaBulan);$i++){
                                            echo "<option value='".$i."' data-name='".$namaBulan[$i]."'>".$namaBulan[$i]."</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Tahun Pajak</label>
                                <select id="tahun_pajak" name="tahun_pajak" class="form-control">
                                    <option value="">-- Pilih Tahun Pajak --</option>
                                    <?php
                                        $tahun    = date('Y');
                                        $tAwal    = $tahun - 5;
                                        $tAkhir   = $tahun;
                                        $selected = "";
                                        for($i=$tAwal; $i<=$tAkhir; $i++){
                                            $selected = ($i == $tahun) ? "selected" : "";
                                            echo "<option value='".$i."' ".$selected.">".$i."</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Pembetulan Ke</label>
                                <select id="pembetulan_ke" name="pembetulan_ke" class="form-control">
                                    <option value="0" selected="selected">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                              <label class="form-control-label">File ID BIlling</label>
                              <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                  <div class="form-control" data-trigger="fileinput"> <i class="glyphicon glyphicon-file fileinput-exists"></i> <span class="fileinput-filename"></span></div> <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file</span> <span class="fileinput-exists">Change</span>
                                  <input class="data_billing" id="editBilling" type="file" name="data_billing" accept=".pdf"> </span> <a href="#" id="billing_remove" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                              </div>
                              <span style="color:grey;font-size: 12px">Extention required is .pdf, max 4 mb</span>
                              <div class="help-block with-errors"></div>
                              <div id="file_billing"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-control-label">Keterangan</label>
                                <textarea id="keterangan" name="keterangan" class="form-control" rows="3"></textarea>
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
</div><!-- ./container fluid -->

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
                             "url"          : baseURL + 'billings/get_billing',
                             "type"         : "POST"
                            },

         "language"     : {
                "emptyTable"    : "<span class='label label-danger'>Data Tidak Ditemukan!</span>",  
                "infoEmpty"     : "Data Kosong",
                "processing"    :' <img src="' + baseURL + 'assets/vendor/qrcode/css/images/loading2.gif">',
                "search"        : "_INPUT_"
            },
           "columns": [
                { "data": "no", "width":"10px", "class":"text-center"},
                { "data": "id", "width":"10px", "class":"text-center"},
                { "data": "bulan_pajak", "width":"100px"},
                { "data": "nama_pajak", "width":"200px", "class":"text-center"},
                { "data": "masa_pajak", "width":"200px", "class":"text-center"},
                { "data": "tahun_pajak", "width":"100px"},
                { "data": "pembetulan_ke", "width":"100px"},
                { "data": "nama_cabang", "width":"100px"},
                { "data": "file_name", "width":"100px"},
                { "data": "keterangan", "width":"100px"}
            ],
        "columnDefs": [
            {
                "targets": [1,2],
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
        
    $("#tabledata input[type=search]").addClear();
    $('#tabledata .dataTables_filter input[type="search"]').attr('placeholder','Search...').css({'width':'230px','display':'inline-block'}).addClass('form-control input-sm');
    
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
            val_id_billing    = data.id;
            val_nama_pajak    = data.nama_pajak;
            val_kode_cabang   = data.kode_cabang;
            val_bulan_pajak   = data.bulan_pajak;
            val_pembetulan_ke = data.pembetulan_ke;
            val_tahun_pajak   = data.tahun_pajak;
            val_file_name     = data.file_name;
            val_keterangan    = data.keterangan;
            valueGrid();
            $("#btnEdit").removeAttr('disabled');
            $("#btnDel").removeAttr('disabled');
        }

    } ).on("dblclick", "tr", function () {
        table.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var data           = table.row( this ).data();
        id                 = data.id;
        val_id_billing    = data.id;
        val_nama_pajak    = data.nama_pajak;
        val_kode_cabang   = data.kode_cabang;
        val_bulan_pajak   = data.bulan_pajak;
        val_pembetulan_ke = data.pembetulan_ke;
        val_tahun_pajak   = data.tahun_pajak;
        val_file_name     = data.file_name;
        val_keterangan    = data.keterangan;

        valueGrid();
        $("#btnEdit").removeAttr('disabled');
        $("#btnDel").removeAttr('disabled');
        $("#btnEdit").trigger('click');

    } );


    $('#editBilling').bind('change', function() {
        fileSize        = this.files[0].size;
        extension_allow = ['pdf'];
        extension       = this.files[0].name.split('.').pop().toLowerCase();
        if (extension_allow.indexOf(extension) < 0) {
            alert('Tipe file tidak diizinkan!');
            $(".data_billing").val('');
        }
        if(fileSize > 2000000){
            alert('File tidak boleh lebih dari 2 mb');
            $(".data_billing").val('');
        }
    });

    function empty()
    {
        isNewRecord = $("#isNewRecord").val();
        editBilling = $("#editBilling").val();
        if ($('#form-edit').find('.has-error, .has-danger').length > 0) {
            $(".help-block").html('');
            $('.has-error').removeClass('has-error');
        }
        if(isNewRecord == 1){
            $("#nama_pajak").val('');
            $("#kode_cabang").val('');
            $("#masa_pajak").val('');
            $("#tahun_pajak").val('');
            $("#keterangan").val('');
            $("#file_billing").html('');
            $("#editBilling").attr("data-toggle","validator");
            $("#editBilling").attr("data-error", "Please fill File Billing");
            $("#editBilling").attr("required", "");
        }
        else{
            $("#editBilling").removeAttr("data-toggle");
            $("#editBilling").removeAttr("data-error");
            $("#editBilling").removeAttr("required");
        }
        if(editBilling != ""){
            $('#billing_remove').trigger('click');
        }

    }

    $(window).error(function(e){
        e.preventDefault();
        if(editBilling != ""){
            $('#billing_remove').click();
        }
    });

    function valueGrid()
    {
        $("#id").val(val_id_billing);
        $("#id-delete").val(val_id_billing);
        $("#nama_pajak").val(val_nama_pajak);
        $("#masa_pajak").val(val_bulan_pajak);
        $("#pembetulan_ke").val(val_pembetulan_ke);
        $("#tahun_pajak").val(val_tahun_pajak);
        $("#kode_cabang").val(val_kode_cabang);
        $("#file_billing").html(val_file_name);
        $("#keterangan").val(val_keterangan);
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
            url: baseURL + 'billings/save_billing/' + id,
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
            url     : baseURL + 'billings/delete_billing/' +id,
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

<script src="<?php echo base_url('assets/') ?>js/jasny-bootstrap.js"></script>