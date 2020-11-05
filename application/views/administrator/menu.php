<style>
.disabled-showorder {
    cursor: not-allowed;
} 
</style>

<div class="container-fluid">

    <?php $this->load->view('template_top') ?>

<div id="table-menu" class="row">
        <div class="col-lg-12"> 
            <div class="panel panel-info boxshadow animated slideInDown">
                <div class="panel-heading">
                    <div class="row">
                      <div class="col-sm-6">
                         <?php echo $subtitle ?>
                      </div>
                      <div class="col-sm-6">
                        <div class="navbar-right">
                            <button type="button" id="btnAdd" class="btn btn-rounded btn-default custom-input-width" data-toggle="modal" data-target="#modal-add"><i class="fa fa-plus"></i> ADD</button>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="panel-body"> 
                    <div class="table-responsive">
                        <table width="100%" class="table  cell-border dataTable stripe table-bordered w-full small" id="tabledata">
                            <thead>
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th class="text-center">ID.</th>
                                    <th class="text-center">PARENT</th>
                                    <th class="text-center">PARENT ID</th>
                                    <th class="text-center">CATEGORY</th>
                                    <th class="text-center">TITLE</th>
                                    <th class="text-center">URL</th>
                                    <th class="text-center">STYLE</th>
                                    <th class="text-center">SHOWORDER</th>
                                    <th class="text-center">SHOWORDER AFTER</th>
                                    <th class="text-center">ACTIONS</th>
                                    <th class="text-center">SWAP ORDER</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="container">
    <div class="modal fade" id="modal-add" role="dialog" aria-labelledby="modal-add">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" >Add New Menu</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="error-add" class="alert alert-danger alert-dismissable hidden"></div>
                    <form role="form" id="form-add" autocomplete="off">
                        <div class="form-group">
                            <label class="form-control-label">Kategori Menu</label>
                            <select class="form-control category" name="category">
                                <option value="parent">Parent</option>
                                <option value="single">Single Link</option>
                                <option value="child">Child Link</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label" for="inputMenuTitle">Nama Menu</label>
                            <input type="text" class="form-control" name="title" placeholder="Nama Menu"/>
                        </div>
                        <div class="form-group style">
                            <label class="form-control-label" for="inputMenuTitle">Style CSS</label>
                            <input type="text" name="style" class="form-control" placeholder="Style CSS"/>
                            <!-- <input id="add_style_value" type="hidden" name="style"/> -->
                            <!-- <input id="add_style" type="text" class="form-control" placeholder="Choose Icon"/> -->
                        </div>

                        <div class="form-group parent" style="display: none">
                            <label class="form-control-label">Pilih Parent Menu</label>
                            <select class="form-control parent_check" name="parent">
                                <option value=""> Pilih Salah Satu </option>
                                <?php foreach ($parents as $parent):?>
                                <option value="<?php echo $parent['ID'] ?>"><?php echo $parent['TITLE'] ?></option>
                                <?php endforeach?>
                            </select>
                        </div>
                        <div class="form-group url" style="display: none">
                            <label class="form-control-label" for="inputMenuUrl">URL</label>
                            <input type="text" class="form-control" name="url" placeholder="URL"/>
                        </div>
                        <div id="showorder_group" class="form-group">
                            <label class="form-control-label">Showorder</label>
                            <select class="form-control showorder_parent" name="showorder_parent">
                                <option value="">Pilih Salah Satu</option>
                                <option value="first">Paling Pertama</option>
                                <?php foreach ($parent_singles as $parent_single):?>
                                <option value="<?php echo $parent_single['ID'] ?>">Setelah <?php echo $parent_single['TITLE'] ?></option>
                                <?php endforeach?>
                            </select>
                            <select class="form-control showorder_child" name="showorder_child" style="display: none;" id="showorder_child_add">
                                <option value="">Pilih Salah Satu</option>
                                <option value="first">Paling Pertama</option>
                            </select>
                            <input type="hidden" name="showorder_child_add_hidden" id="showorder_child_add_hidden">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">CANCEL</button>
                    <button type="button" class="btn btn-info" id="btnCreate">SAVE</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit" role="dialog" aria-labelledby="modal-edit">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" >Edit Data Menu</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="error-edit" class="alert alert-danger alert-dismissable hidden"></div>
                    <form role="form" id="form-edit" autocomplete="off">
                        <input type="hidden" class="form-control" id="id" name="id">
                        <div class="form-group">
                            <label class="form-control-label">Kategori Menu</label>
                            <select id="category" class="form-control category" name="category">
                                <option value="parent">Parent</option>
                                <option value="single">Single Link</option>
                                <option value="child">Child Link</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label" for="inputMenuTitle">Nama Menu</label>
                            <input id="title" type="text" class="form-control" name="title" placeholder="Nama Menu"/>
                        </div>
                        <div class="form-group style">
                            <label class="form-control-label" for="inputMenuTitle">Style CSS</label>
                            <input id="style" type="text" class="form-control" name="style" placeholder="Style CSS"/>
                            <!-- <input id="style" type="text" class="form-control" name="style" placeholder="Choose Icon"/> -->
                        </div>
                        <div class="form-group parent" style="display: none">
                            <label class="form-control-label">Pilih Parent Menu</label>
                            <select id="parent" class="form-control parent_check" name="parent">
                                <option value=""> Pilih Salah Satu </option>
                                <?php foreach ($parents as $parent):?>
                                <option value="<?php echo $parent['ID'] ?>"><?php echo $parent['TITLE'] ?></option>
                                <?php endforeach?>
                            </select>
                        </div>
                        <div class="form-group url" style="display: none">
                            <label class="form-control-label" for="inputMenuUrl">URL</label>
                            <input id="url" type="text" class="form-control" name="url" placeholder="URL"/>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">Showorder</label>
                            <select id="showorder_parent" class="form-control showorder_parent" name="showorder_parent">
                                <option value="">Pilih Salah Satu</option>
                                <option value="first">Paling Pertama</option>
                            </select>
                            <select id="showorder_child" class="form-control showorder_child" name="showorder_child" style="display: none;">
                                <option value="">Pilih Salah Satu</option>
                                <option value="first">Paling Pertama</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">CANCEL</button>
                    <button type="button" class="btn btn-info" id="btnUpdate">SAVE</button>
                </div>
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
    <style>
        .radio_custom:hover{
                padding: 10px;
            background: lightblue;
            color: blue;
        }
    </style>


    <div class="modal fade" id="modal-radio" role="dialog" aria-labelledby="modal-radio">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="white-box">
                <section>
                    <!-- <h3 class="box-title">Web App Icons</h3> -->
                    <div class="clearfix icon-list-demo">
                      <!--   <div class="col-xs-3 col-md-2">
                          <a href="javascript:void(0)" style="border:2px solid blue;padding:2px;width: 50%;">
                              <i class="fa fa-adjust"></i>
                          </a>
                      </div> -->
                        <div class="col-xs-3 col-md-2"><i class="fa fa-anchor radio_custom"></i></div>
                        <div class="col-xs-3 col-md-2"><i class="fa fa-archive"></i></div>
                        <div class="col-xs-3 col-md-2"><i class="fa fa-arrows"></i></div>
                        <div class="col-xs-3 col-md-2"><i class="fa fa-arrows-h"></i></div>
                        <div class="col-xs-3 col-md-2"><i class="fa fa-arrows-v"></i></div>
                        <div class="col-xs-3 col-md-2"><i class="fa fa-arrows-v"></i></div>
                        <div class="col-xs-3 col-md-2"><i class="fa fa-arrows-v"></i></div>
                        <div class="col-xs-3 col-md-2"><i class="fa fa-arrows-v"></i></div>
                        <div class="col-xs-3 col-md-2"><i class="fa fa-arrows-v"></i></div>
                    </div>
                </section>
            </div>
        </div>
      </div>
    </div>

</div><!-- ./container -->
</div><!-- ./container -->

<script>
$(document).ready(function() {

    $("#add_style").click(function(){
        console.log('yeaaa');
        $("#modal-radio").modal('show');
    });

    Pace.track(function(){
       $('#tabledata').DataTable({
        "serverSide"    : true,
        "processing"    : true,
        "ajax"          : {
                             "url"          : baseURL + 'menurights/load_all_menu',
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
                { "data": "parent", "width":"200px"},
                { "data": "parent_id", "width":"200px"},
                { "data": "category", "width":"200px"},
                { "data": "title", "width":"200px"},
                { "data": "url", "width":"200px"},
                { "data": "style", "width":"80px", "class":"text-center"},
                { "data": "showorder", "width":"80px", "class":"text-center"},
                { "data": "showorder_after", "width":"80px", "class":"text-center"},
                { 
                    "data": "title",
                    "width":"80px",
                    "class":"text-center",
                    "render": function (data) {
                     return '<a href="javascript:void(0)" class="action-edit" title="Edit ' + data + '" style="color:#ff6436"><i class="fa fa-edit" aria-hidden="true"></i></a> &nbsp;&nbsp;&nbsp; <a href="javascript:void(0)" class="action-delete" title="Hapus ' + data + '" style="color:#ff6436"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                    }
                },
                { "data": "showorder_icon", "width":"80px", "class":"text-center"}
            ],
            "columnDefs": [
                {
                    "searchable": false,
                    "orderable": false,
                    "targets": 0
                },
                {
                    "targets": [ 1, 2, 3, 4, 7, 8, 9],
                    "visible": false,
                    "searchable": false
                }
            ],
            "scrollY"          : false, 
            "scrollCollapse"   : true,
            "scrollX"          : true,
            "ordering"         : false,
            "pageLength": 100,
            drawCallback: function (settings) {
                var api = this.api();
                var rows = api.rows({ page: 'current' }).nodes();
                var last = null;
                api.column(2, { page: 'current' }).data().each(function (group, i) {
                    if (last !== group) {
                            if(group == 'XXX'){
                                $(rows).eq(i).before(
                                    '<tr class="group"><td align="center" colspan="6" style="color:#fff;background-color:#4caf50;font-weight:700;padding:10px 0;">Parent / Single Link</td></tr>'
                                );
                            }
                            else{
                                $(rows).eq(i).before(
                                    '<tr class="group"><td align="center" colspan="6" style="color:#fff;background-color:#f44336;font-weight:700;padding:10px 0;">' +  group  + '</td></tr>'
                                );
                            }
                        last = group;
                    }
                });
            }
        });
    });

    table = $('#tabledata').DataTable();

    $('#table-menu .dataTables_filter input[type="search"]').attr('placeholder','Search Title, URL ...').css({'width':'230px','display':'inline-block'}).addClass('form-control input-sm');

    $("#tabledata_filter .add-clear-x").on('click',function(){
        table.search('').column().search('').draw();
    });

    // action modal edit
    $('#tabledata tbody').on( 'click', 'a.action-edit', function () {
            
        data      = table.row( $(this).parents('tr') ).data();
        id        = data.id;
        parent_id = data.parent_id;
        category  = data.category;

        $("#id").val(id);
        $("#category").val(data.category);
        $("#title").val(data.title);
        $("#style").val(data.style);
        $("#parent").val(data.parent_id);
        $("#url").val(data.url);

        if(category == "parent"){
            $(".parent").css('display', 'none');
            $(".url").css('display', 'none');
            $(".style").css('display', 'block');
            $(".showorder_parent").css('display', 'block');
            $(".showorder_child").css('display', 'none');
        }
        else if(category == "single"){
            $(".parent").css('display', 'none');
            $(".url").css('display', 'block');
            $(".style").css('display', 'block');
            $(".showorder_parent").css('display', 'block');
            $(".showorder_child").css('display', 'none');
        }
        else{
            $(".parent").css('display', 'block');
            $(".url").css('display', 'block');
            $(".style").css('display', 'none');
            $(".showorder_parent").css('display', 'none');
            $(".showorder_child").css('display', 'block');
        }

        if(category == "child"){

            // $("select#showorder_child option[value!='first']").remove();
            $("select#showorder_child option[remove='removeAble']").remove();

            $.ajax({
                url     :  baseURL + 'menurights/get_childs/'+parent_id+"/"+id,
                type    : "GET",
                success : function(result){
                    var dataObject = $.parseJSON(result);
                    $.each(dataObject, function(i, item) {
                        if(data.showorder_after == item.ID){
                            selected = ' selected="selected"';
                        }
                        else{
                            selected = '';
                        }
                        $('#showorder_child').append('<option remove="removeAble" value="'+ item.ID + '"' + selected + '>Setelah '+ item.TITLE + '</option>');
                    });
                }
            }); 
        }
        else{

            // $("select#showorder_parent option[value!='first']").remove();
            $("select#showorder_parent option[remove='removeAble']").remove();

            $.ajax({
                url     :  baseURL + 'menurights/get_parent_singles/'+id,
                type    : "GET",
                success : function(result){
                    var dataObject = $.parseJSON(result);
                    $.each(dataObject, function(i, item) {
                        if(data.showorder_after == item.ID){
                            selected = ' selected="selected"';
                        }
                        else{
                            selected = '';
                        }
                        $('#showorder_parent').append('<option remove="removeAble" value="'+ item.ID + '"' + selected + '>Setelah '+ item.TITLE + '</option>');
                    });
                }
            }); 
        }

        $("#modal-edit").modal('show');

    });

    // action swaporder
    var show1   = "";
    var idItem1 = "";
    var show2   = "";
    var idItem2 = "";
    var parent1 = "";
    var parent2 = "";

    $('#tabledata tbody').on( 'click', 'a.showorder', function () {

        data = table.row( $(this).parents('tr') ).data();

        var id       = data.id;
        var order    = $(this).attr("data-order");
        var parent   = $(this).attr("data-parent");
        var disabled = $('a.showorder:not(a[data-parent="'+ parent +'"])');

        disabled.css("color","grey");
        disabled.addClass("disabled-showorder");

        disabled.click(function(e) {
            return false;
        });

        if(show1 == ""){
            $(this).css("color","red");
            show1   = order;
            idItem1 = id;
            parent1 = parent;
            console.log("ID " + idItem1 + " || ORDER " + show1);
        }else{
            show2   = order;
            idItem2 = id;
            parent2 = parent;

            console.log("ID " + idItem2 + " || ORDER " + show2);

            if(parent1 != parent2){
                alert('xx');
            }
        }

        
        if(show1 != "" && show2 != ""){
            $.ajax({
                url: baseURL + 'menurights/swaporder/'+idItem1+'/'+show1+'/'+idItem2+'/'+show2,
                type: "GET",
                dataType:"json",
                beforeSend  : function(){
                     $("body").addClass("loading");
                     },
                success:function(result) {
                    $("body").removeClass("loading");
                    if(result.status == true){
                        flashnotif('Sukses','Data Berhasil di Simpan!','success' );
                        show1   = "";
                        idItem1 = "";
                        show2   = "";
                        idItem2 = "";
                        parent1 = "";
                        parent2 = "";
                        disabled.removeClass("disabled-showorder");
                        table.ajax.reload(null, false);
                    }else{
                        flashnotif('Error', result.ket ,'error' );
                        show1   = "";
                        idItem1 = "";
                        show2   = "";
                        idItem2 = "";
                        parent1 = "";
                        parent2 = "";
                    }
                }
            });
        }

    });

    // action modal delete
    $('#tabledata tbody').on( 'click', 'a.action-delete', function () {
        data = table.row( $(this).parents('tr') ).data();

        $("#id-delete").val(data.id);
        $("#modal-delete").modal('show');
    });

    $('#modal-add').on('shown.bs.modal', function () {
        $(this)
            .find("input,textarea,select")
               .val('')
               .end()
            .find("input[type=checkbox], input[type=radio]")
               .prop("checked", "")
               .end();
        $('.category').trigger('focus');
        $('.category').val('parent');
        $('.parent').css('display', 'none');
        $('.url').css('display', 'none');
        $("select.showorder_child option[remove='removeAble']").remove();
        $('.showorder_parent').val('');
        $('.showorder_child').val('');
        $(".style").css('display', 'block');

    });

    $(".category").change(function(){

        if(this.value == "parent"){
            $(".parent").css('display', 'none');
            $(".url").css('display', 'none');
            $(".style").css('display', 'block');
            $(".showorder_parent").css('display', 'block');
            $(".showorder_child").css('display', 'none');
        }
        else if(this.value == "single"){
            $(".parent").css('display', 'none');
            $(".url").css('display', 'block');
            $(".style").css('display', 'block');
            $(".showorder_parent").css('display', 'block');
            $(".showorder_child").css('display', 'none');
        }
        else{
            $(".parent").css('display', 'block');
            $(".url").css('display', 'block');
            $(".style").css('display', 'none');
            $(".showorder_parent").css('display', 'none');
            $(".showorder_child").css('display', 'block');
        }

    });

    $(".parent_check").change(function(){

        $("select.showorder_child option[remove='removeAble']").remove();
        id = this.value;

        $.ajax({
            url     :  baseURL + 'menurights/get_childs/'+id,
            type    : "GET",
            success : function(result){
                console.log(result);
                if(result == '0'){
                    $("#showorder_child_add").attr('disabled', 'disabled');
                    $("#showorder_child_add_hidden").val("1");
                }
                else{
                    var dataObject = $.parseJSON(result);
                    $.each(dataObject, function(i, item) {
                        $('.showorder_child').append('<option remove="removeAble" value="'+ item.ID + '">Setelah '+ item.TITLE + '</option>');
                    });
                }
                
            }
        }); 
        return false;
    });

    // Form Add
    $("#btnCreate").click(function(){
        $.ajax({
            url     :  baseURL + 'menurights/create_menu',
            type    : "POST",
            data    : $('#form-add').serialize(),
            success : function(result){
                table.ajax.reload();
                console.log(result);
                if (result==1) {
                    flashnotif('Sukses','Data Berhasil di Simpan!','success' );
                    table.ajax.reload();
                    $("#modal-add").modal('hide');
                } else {
                    flashnotif('Error', result ,'error' );
                }
            }
        }); 
        return false;
    });

    // Form Edit
    $("#btnUpdate").click(function(){
        var id = $("#id").val();
        $.ajax({
            url     :  baseURL + 'menurights/edit_menu/'+id,
            type    : "POST",
            data    : $('#form-edit').serialize(),
            success : function(result){
                if (result==1) {
                    table.ajax.reload(null, false);
                    $("#modal-edit").modal('hide');
                } else {
                    $('#error-edit').attr('style','display: block !important');
                    $('#error-edit').html('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' + result);
                }
            }
        }); 
        return false;
    });

    // Form Delete
    $("#btnConfirm").click(function(){
        var id = $("#id-delete").val();
        $.ajax({
            url     :  baseURL + 'menurights/delete_menu/'+id,
            type    : "POST",
            data    : $('#form-delete').serialize(),
            success : function(result){
                if (result==1) {
                    table.ajax.reload(null, false);
                    $("#modal-delete").modal('hide');
                } else {
                    alert('Gagal');
                }
            }
        }); 
        return false;
    });

});

</script>