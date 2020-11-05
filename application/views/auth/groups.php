<div class="container-fluid">

    <?php $this->load->view('template_top') ?>

    <div id="table_group" class="row">
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
                                    <th class="text-center">NO.</th>
                                    <th class="text-center">ID.</th>
                                    <th class="text-center">NAME</th>
                                    <th class="text-center">DESCRIPTION</th>
                                    <th class="text-center">MENU ID</th>
                                    <th class="text-center">MENU RIGHTS</th>
                                    <th class="text-center">ACTIONS</th>
                                    <th class="text-center">ASSIGN MENUS</th>
                                </tr>`
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="container">
    <div class="modal fade" id="modal-view" role="dialog" aria-labelledby="modal-view">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Group #<span id="view-id"></span></h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="form-view" class="form-horizontal">
                        <div class="form-group row">
                            <label class="col-md-4 form-control-label">Name : </label>
                            <div class="col-md-8">
                               <input type="text" class="form-control" id="viewName" disabled="true" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 form-control-label">Description : </label>
                            <div class="col-md-8">
                               <textarea class="form-control" id="viewDesc" rows="4" disabled="true"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 form-control-label">Menu Rights : </label>
                            <div class="col-md-8">
                               <input type="text" class="form-control" id="viewMenus" disabled="true" />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">CLOSE</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-add" role="dialog" aria-labelledby="modal-add">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" >Add New Group</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="error-add" class="alert alert-danger alert-dismissable hidden"></div>
                    <form role="form" id="form-add" autocomplete="off">
                        <div class="form-group">
                            <label class="form-control-label" for="inputGroupName">Name</label>
                                <input type="text" class="form-control" id="inputGroupName" name="group_name" placeholder="Name" autocomplete="off" />
                        </div>
                        <div class="form-group">
                            <label class="form-control-label" for="inputGroupDesc">Description</label>
                            <textarea class="form-control" name="description" id="inputGroupDesc" rows="5" autocomplete="off" ></textarea>
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
                    <h3 class="modal-title" >Edit Data Group</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="error-edit" class="alert alert-danger alert-dismissable hidden"></div>
                    <form role="form" id="form-edit" autocomplete="off">
                        <input type="hidden" class="form-control" id="id" name="id">
                        <div class="form-group">
                            <label class="form-control-label" for="editGroupName">Name</label>
                                <input type="text" class="form-control" id="editGroupName" name="group_name" placeholder="Name"/>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label" for="editGroupDesc">Description</label>
                            <textarea class="form-control" name="description" id="editGroupDesc" rows="5"></textarea>
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

    <div class="modal fade" id="modal-assign" role="dialog" aria-labelledby="modal-assign">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" >Assign to Menus</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="error-assign" class="alert alert-danger alert-dismissable hidden"></div>
                    <form role="form" id="form-assign" autocomplete="off">
                        <input type="hidden" class="form-control" id="id-assign" name="id">
                        <div class="form-group">
                            <label class="form-control-label" for="pre-selected-options">Assign Menus</label>
                            <select multiple id="menu-list" name="menus[]">
                                <?php foreach ($menus as $menu):?>
                                <?php
                                    if($menu['PARENT_ID'] == 0){

                                        if ($menu['IS_PARENT'] == 1) {

                                            echo '<optgroup label="'.$menu['TITLE'].'">';

                                            foreach ($menus as $value) {
                                                if($value['PARENT_ID'] == $menu['ID']){
                                                    echo '<option value="'.$value['ID'].'">'.$value['TITLE'].'</option>';

                                                }
                                            }
                                
                                            echo '</optgroup>';
                                        }
                                        else{

                                            echo '<optgroup label="'.$menu['TITLE'].'">';
                                            echo '<option value="'.$menu['ID'].'">'.$menu['TITLE'].'</option>';
                                            echo '</optgroup>';
                                            
                                        }

                                    }
                                ?>
                              <?php endforeach?>
                            </select>
                            <div class="button-box m-t-20">
                                <a id="select-all" class="btn btn-danger btn-outline" href="#">select all</a>
                                <a id="deselect-all" class="btn btn-info btn-outline" href="#">deselect all</a>
                                <a id="refresh" class="btn btn-warning btn-outline hidden" href="#">rollback</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">CANCEL</button>
                    <button type="button" class="btn btn-info" id="btnAssign">SUBMIT</button>
                </div>
            </div>
        </div>
    </div>

</div><!-- ./container -->
</div><!-- ./container-fluid-->

<script>

$(document).ready(function() {

    Pace.track(function(){
       $('#tabledata').DataTable({
        "serverSide"    : true,
        "processing"    : true,
        "ajax"          : {
                             "url"          : baseURL + 'auth/get_all_groups',
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
                {
                    "data": "group_name",
                    "width":"200px",
                    "class":"text-center",
                    "render": function (data) {
                     return '<a href="javascript:void(0)" class="action-view" title="Click to view ' + data + '" style="color:#ff6436">' + data + '</a>';
                    }
                },
                { "data": "description", "width":"200px"},
                { "data": "menu_id", "width":"200px"},
                { "data": "menu_name", "width":"200px"},
                { 
                    "data": "group_name",
                    "width":"80px",
                    "class":"text-center",
                    "render": function (data) {
                     return '<a href="javascript:void(0)" class="action-edit" title="Click to edit ' + data + '" style="color:#ff6436"><i class="fa fa-edit" aria-hidden="true"></i></a> &nbsp;&nbsp;&nbsp; <a href="javascript:void(0)" class="action-delete" title="Click to delete ' + data + '" style="color:#ff6436"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                    }
                },
                { 
                    "data": "group_name",
                    "width":"80px",
                    "class":"text-center",
                    "render": function (data) {
                     return '<a href="javascript:void(0)" class="action-assign" title="Assign ' + data + ' to Menus" style="color:#ff6436"><i class="fa fa-tasks" aria-hidden="true"></i></a>';
                    }
                }
            ],
            "columnDefs": [
                {
                    "searchable": false,
                    "orderable": false,
                    "targets": 0
                },
                {
                    "targets": [1, 4, 5],
                    "visible": false,
                    "searchable": false
                }
            ],
            "scrollY"          : false, 
            "scrollCollapse"   : true,
            "scrollX"          : true,
            "ordering"         : false,
        });
    });

    table = $('#tabledata').DataTable();
    $('#table_group .dataTables_filter input[type="search"]').attr('placeholder','Search Name ...').css({'width':'230px','display':'inline-block'}).addClass('form-control input-sm');

    // action modal view
    $('#tabledata tbody').on( 'click', 'a.action-view', function () {
        
        data = table.row( $(this).parents('tr') ).data();
            
        $("#view-id").html(data.id);
        $("#viewName").val(data.group_name);
        $("#viewDesc").val(data.description);
        $("#viewMenus").val(data.menu_name);

        $("#modal-view").modal('show');

    });

    // action modal edit
    $('#tabledata tbody').on( 'click', 'a.action-edit', function () {
            
        data = table.row( $(this).parents('tr') ).data();

        $("#id").val(data.id);
        $("#editGroupName").val(data.group_name);
        $("#editGroupDesc").val(data.description);

        $("#modal-edit").modal('show');

    });

    // action modal delete
    $('#tabledata tbody').on( 'click', 'a.action-delete', function () {
        data = table.row( $(this).parents('tr') ).data();

        $("#id-delete").val(data.id);
        $("#modal-delete").modal('show');
    });

    // action modal assign
    $('#tabledata tbody').on( 'click', 'a.action-assign', function () {
        data     = table.row( $(this).parents('tr') ).data();
        menu_id = data.menu_id;
        menus   = menu_id.split(", ");

        $("#id-assign").val(data.id);

        $('#menu-list').val(menus);
        $('#menu-list').multiSelect('refresh');

        $('#refresh').attr('style','display: inline-block !important');
        $('#refresh').on('click', function () {
            $('#menu-list').val(menus);
            $('#menu-list').multiSelect('refresh');
            return false;
        });

        $("#modal-assign").modal('show');
    });

    $('#modal-add').on('shown.bs.modal', function () {
        $(this)
            .find("input,textarea,select")
               .val('')
               .end()
            .find("input[type=checkbox], input[type=radio]")
               .prop("checked", "")
               .end();
        $('#inputGroupName').trigger('focus')
    });

    // Form Add
    $("#btnCreate").click(function(){
        $.ajax({
            url     : baseURL + 'auth/create_group/',
            type    : "POST",
            data    : $('#form-add').serialize(),
            success : function(result){
                if (result==1) {
                    table.ajax.reload(null, false);
                    $("#modal-add").modal('hide');
                } else {
                    $('#error-add').attr('style','display: block !important');
                    $('#error-add').html('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' + result);
                }
            }
        }); 
        return false;
    });

    // Form Edit
    $("#btnUpdate").click(function(){
        var id = $("#id").val();
        $.ajax({
            url     : baseURL + 'auth/edit_group/' + id,
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
            url     : baseURL + 'auth/delete_group/' + id,
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

    // Form Assign
    $("#btnAssign").click(function(){
        var id     = $("#id-assign").val();
        $.ajax({
            url     : baseURL + 'auth/assign_menu/' + id,
            type    : "POST",
            data    : $('#form-assign').serialize(),
            success : function(result){
                if (result==1) {
                    table.ajax.reload(null, false);
                    $("#modal-assign").modal('hide');
                } else {
                    $('#error-assign').attr('style','display: block !important');
                    $('#error-assign').html('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' + result);
                }
            }
        }); 
        return false;
    });

    $(document).ready(function() {

        $('#menu-list').multiSelect();
        $('#select-all').click(function () {
            $('#menu-list').multiSelect('select_all');
            return false;
        });
        $('#deselect-all').click(function () {
            $('#menu-list').multiSelect('deselect_all');
            return false;
        });

    });

});

</script>