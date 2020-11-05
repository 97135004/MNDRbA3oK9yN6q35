<div class="container-fluid">

    <?php $this->load->view('template_top') ?>
    <div id="table_user" class="row">
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
                                    <th class="text-center">USER NAME</th>
                                    <th class="text-center">FULL NAME</th>
                                    <th class="text-center">EMAIL ADDRESS</th>
                                    <th class="text-center">GROUP ID</th>
                                    <th class="text-center">USER GROUPS</th>
                                    <th class="text-center">LAST UPDATE BY</th>
                                    <th class="text-center">LAST UPDATE DATE</th>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-center">ACTIONS</th>
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
                        <h3 class="modal-title">User #<span id="view-id"></span></h3>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form role="form" id="form-view" class="form-horizontal">
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label">Username : </label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="viewUserName" readonly="true" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label">Email Address: </label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="viewEmail" readonly="true" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label">Full Name : </label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="viewName" readonly="true" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label">User Groups : </label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="viewGroup" readonly="true" />
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
                    <form role="form" id="form-add" autocomplete="off" data-toggle="validator">
                        <div class="modal-header">
                            <h3 class="modal-title">Add New User</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="form-control-label" for="inputUserName">Username</label>
                                    <input type="text" class="form-control" id="inputUserName" name="user_name" placeholder="Username" autocomplete="off" data-toggle="validator" data-error="Please fill out this field" required />
                                    <div class="help-block with-errors"></div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-control-label" for="inputEmail">Email Address</label>
                                    <input type="email" class="form-control" id="inputEmail" name="email" placeholder="Email Address" autocomplete="off" data-toggle="validator" data-error="Please fill out this field or correct the email" required />
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="form-control-label" for="inputName">Full Name</label>
                                    <input type="text" class="form-control" id="inputName" name="display_name" placeholder="Full Name" autocomplete="off" data-toggle="validator" data-error="Please fill out this field" required />
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label class="form-control-label" for="pre-selected-options">Assign User Groups</label>
                                    <select multiple id="group-list_add" name="groups[]" data-toggle="validator" data-error="Please fill out this field" required>
                                        <?php foreach ($groups as $group) : ?>
                                            <option value="<?php echo $group['ID'] ?>"><?php echo $group['NAME'] ?></option>
                                        <?php endforeach ?>
                                    </select>
                                    <div class="button-box m-t-20">
                                        <a id="select-all_add" class="btn btn-danger btn-outline" href="#">select all</a>
                                        <a id="deselect-all_add" class="btn btn-info btn-outline" href="#">deselect all</a>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger waves-effect" id="btnBack"><i class="fa fa-reply"></i> CANCEL</button>
                            <button type="submit" class="btn btn-info waves-effect" id="btnSave"><i class="fa fa-save"></i> SAVE</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-edit" role="dialog" aria-labelledby="modal-edit">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form role="form" id="form-edit" autocomplete="off" data-toggle="validator">
                        <div class="modal-header">
                            <h3 class="modal-title">Edit Data User</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div id="error-edit" class="alert alert-danger alert-dismissable hidden"></div>
                            <input type="hidden" class="form-control" id="id" name="id">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="form-control-label" for="editUserName">Username</label>
                                    <input type="text" class="form-control" id="editUserName" name="user_name" placeholder="Username" disabled />
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-control-label" for="editEmail">Email Address</label>
                                    <input type="email" class="form-control" id="editEmail" name="email" placeholder="Email Address" disabled />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="form-control-label" for="inputName">Full Name</label>
                                    <input type="text" class="form-control" id="editName" name="display_name" placeholder="Full Name" autocomplete="off" data-toggle="validator" data-error="Please fill out this field" required />
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <div class="form-group">
                                        <label class="form-control-label" for="pre-selected-options">Assign User Groups</label>
                                        <select multiple id="group-list" name="groups[]" data-toggle="validator" data-error="Please fill out this field" required>
                                            <?php foreach ($groups as $group) : ?>
                                                <option value="<?php echo $group['ID'] ?>"><?php echo $group['NAME'] ?></option>
                                            <?php endforeach ?>
                                        </select>
                                        <div class="button-box m-t-20">
                                            <a id="select-all" class="btn btn-danger btn-outline" href="#">select all</a>
                                            <a id="deselect-all" class="btn btn-info btn-outline" href="#">deselect all</a>
                                            <a id="refresh" class="btn btn-warning btn-outline hidden" href="#">rollback</a>
                                        </div>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Status</label>
                                <div>
                                    <div class="radio radio-info">
                                        <input type="radio" id="active" name="active" value="1" />
                                        <label for="active">Active</label>
                                    </div>
                                    <div class="radio radio-info">
                                        <input type="radio" id="not_active" name="active" value="0" />
                                        <label for="not_active">Not Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger waves-effect" id="btnBack2"><i class="fa fa-reply"></i> CANCEL</button>
                            <button type="submit" class="btn btn-info waves-effect" id="btnSave"><i class="fa fa-save"></i> SUBMIT</button>
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
                            Apakah anda ingin menghapus user <b><span id="user-delete"></span></b>?
                            <br>
                            <br>
                            <div class="form-group">
                                <label class="form-control-label">Berikan alasan menghapus user ini</label>
                                <textarea class="form-control" name="keterangan" id="keterangan"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">CANCEL</button>
                            <button type="submit" class="btn btn-info" id="btnConfirm" name="confirm" value="Yes">YES</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-reset" role="dialog" aria-labelledby="modal-reset">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form role="form" id="form-reset">
                        <input type="hidden" class="form-control" id="id-reset" name="id">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Konfirmasi</h4>
                        </div>
                        <div class="modal-body">
                            Apakah anda ingin melanjutkan?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">CANCEL</button>
                            <button type="submit" class="btn btn-info" id="btnConfirmReset" name="confirm" value="Yes">YES</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div><!-- ./container -->
</div><!-- ./container -->

<script>
    $(document).ready(function() {
        $('#group-list, #group-list_add').multiSelect();
        $('#select-all').click(function() {
            $('#group-list').multiSelect('select_all');
            return false;
        });
        $('#deselect-all').click(function() {
            $('#group-list').multiSelect('deselect_all');
            return false;
        });
        $('#select-all_add').click(function() {
            $('#group-list_add').multiSelect('select_all');
            return false;
        });
        $('#deselect-all_add').click(function() {
            $('#group-list_add').multiSelect('deselect_all');
            return false;
        });
    });

    $(document).ready(function() {

        Pace.track(function() {
            $('#tabledata').DataTable({
                "serverSide": true,
                "processing": true,
                "ajax": {
                    "url": baseURL + 'auth/get_all_user',
                    "type": "POST"
                },

                "language": {
                    "emptyTable": "<span class='label label-danger'>Data Tidak Ditemukan!</span>",
                    "infoEmpty": "Data Kosong",
                    "processing": ' <img src="' + baseURL + 'assets/vendor/simtax/css/images/loading2.gif">',
                    "search": "_INPUT_"
                },
                "columns": [{
                        "data": "no",
                        "width": "10px",
                        "class": "text-center"
                    },
                    {
                        "data": "id",
                        "width": "10px",
                        "class": "text-center"
                    },
                    {
                        "data": "user_name",
                        "width": "200px",
                        "class": "text-center",
                        "render": function(data) {
                            return '<a href="javascript:void(0)" class="action-view" title="Click to view ' + data + '" style="color:#ff6436">' + data + '</a>';
                        }
                    },
                    {
                        "data": "display_name",
                        "width": "200px"
                    },
                    {
                        "data": "email",
                        "width": "200px",
                        "class": "text-center"
                    },
                    {
                        "data": "group_id",
                        "width": "200px"
                    },
                    {
                        "data": "user_group",
                        "width": "200px"
                    },
                    {
                        "data": "last_update_by",
                        "width": "100px",
                        "class": "text-center"
                    },
                    {
                        "data": "last_update_date",
                        "width": "100px",
                        "class": "text-center"
                    },
                    {
                        "data": "status",
                        "width": "100px",
                        "class": "text-center"
                    },
                    {
                        "data": "display_name",
                        "width": "80px",
                        "class": "text-center",
                        "render": function(data) {
                            return '<a href="javascript:void(0)" class="action-edit" title="Click to edit ' + data + '" style="color:#ff6436"><i class="fa fa-edit" aria-hidden="true"></i></a> &nbsp;&nbsp;&nbsp; <a href="javascript:void(0)" class="action-delete" title="Click to delete ' + data + '" style="color:#ff6436"><i class="fa fa-trash" aria-hidden="true"></i></a> &nbsp;&nbsp;&nbsp; <a href="javascript:void(0)" class="action-reset" title="Click to reset password ' + data + '" style="color:#ff6436"><i class="fa fa-refresh" aria-hidden="true"></i></a>';
                        }
                    }
                ],
                "columnDefs": [{
                        "searchable": false,
                        "orderable": false,
                        "targets": 0
                    },
                    {
                        "targets": [1, 5, 6],
                        "visible": false,
                        "searchable": false
                    }
                ],
                "scrollY": false,
                "scrollCollapse": true,
                "scrollX": true,
                "ordering": false,
            });
        });

        table = $('#tabledata').DataTable();


        $('#table_user .dataTables_filter input[type="search"]').attr('placeholder', 'Search Username, FUll Name, Email, ...').css({
            'width': '230px',
            'display': 'inline-block'
        }).addClass('form-control input-sm');

        // action modal view
        $('#tabledata tbody').on('click', 'a.action-view', function() {

            data = table.row($(this).parents('tr')).data();

            $("#view-id").html(data.id);
            $("#viewUserName").val(data.user_name);
            $("#viewEmail").val(data.email);
            $("#viewName").val(data.display_name);
            $("#viewGroup").val(data.user_group);
            $("#modal-view").modal('show');
        });

        // action modal edit
        $('#tabledata tbody').on('click', 'a.action-edit', function() {

            data = table.row($(this).parents('tr')).data();
            status = data.status;
            group_id = data.group_id;
            groups = group_id.split(", ");

            $("#id").val(data.id);
            $("#editUserName").val(data.user_name);
            $("#editEmail").val(data.email);
            $("#editName").val(data.display_name);
            $("#status").val(data.status);

            $('#group-list').val(groups);
            $('#group-list').multiSelect('refresh');

            $('#refresh').attr('style', 'display: inline-block !important');
            $('#refresh').on('click', function() {
                $('#group-list').val(groups);
                $('#group-list').multiSelect('refresh');
                return false;
            });

            if (status == 'Active') {
                $("#active").prop("checked", true);
            } else {
                $("#not_active").prop("checked", true);
            }

            $("#modal-edit").modal('show');
        });

        // action modal delete
        $('#tabledata tbody').on('click', 'a.action-delete', function() {
            data = table.row($(this).parents('tr')).data();
            $("#id-delete").val(data.id);
            $("#user-delete").html(data.user_name);
            $("#keterangan").val('');
            $("#modal-delete").modal('show');

        });
        // action modal reset
        $('#tabledata tbody').on('click', 'a.action-reset', function() {
            data = table.row($(this).parents('tr')).data();
            $("#id-reset").val(data.id);
            $("#modal-reset").modal('show');
        });

        $('#modal-add').on('shown.bs.modal', function() {
            $("#deselect-all_add").trigger('click');
            $(this)
                .find("input,textarea,select")
                .val('')
                .end()
                .find("input[type=checkbox], input[type=radio]")
                .prop("checked", "")
                .end();
            $('#inputUserName').trigger('focus');
        });

        $('#form-add').validator().on('submit', function(e) {
            if (e.isDefaultPrevented()) {
                console.log('tidak valid');
            } else {
                $.ajax({
                    url: baseURL + 'auth/create_user',
                    type: "POST",
                    data: $('#form-add').serialize(),
                    success: function(result) {
                        console.log(result);
                        if (result == 1) {
                            table.ajax.reload(null, false);
                            $("#modal-add").modal('hide');
                            flashnotif('Success', 'Record added succesfully!', 'success');
                        } else {
                            $("body").removeClass("loading");
                            flashnotif('Failed', result, 'error');
                        }
                    }
                });
            }
            e.preventDefault();
        });

        $('#form-edit').validator().on('submit', function(e) {
            if (e.isDefaultPrevented()) {
                console.log('tidak valid');
            } else {
                $.ajax({
                    url: baseURL + 'auth/edit_user',
                    type: "POST",
                    data: $('#form-edit').serialize(),

                    success: function(result) {
                        console.log(result);
                        if (result == 1) {
                            table.ajax.reload(null, false);
                            $("#modal-edit").modal('hide');
                            flashnotif('Success', 'Record changed!', 'success');
                        } else {
                            $("body").removeClass("loading");
                            flashnotif('Failed', result, 'error');
                        }
                    }
                });
            }
            e.preventDefault();
        });

        // Form Delete
        $("#btnConfirm").click(function() {

            id = $("#id-delete").val();
            keterangan = $("#keterangan").val();

            $.ajax({
                url: baseURL + 'auth/delete_user/' + id + '/' + keterangan,
                type: "POST",
                data: $('#form-delete').serialize(),
                success: function(result) {
                    if (result == 1) {
                        flashnotif('Success', 'Record deleted!', 'success');
                        table.ajax.reload(null, false);
                        $("#modal-delete").modal('hide');
                    } else {
                        flashnotif('Failed', 'Failed to delete user', 'error');
                    }
                }
            });

            return false;

        });
        // Form Delete
        $("#btnConfirmReset").click(function() {

            var id = $("#id-reset").val();

            $.ajax({
                url: baseURL + 'auth/reset_user_pass/' + id,
                type: "POST",
                data: $('#form-reset').serialize(),
                success: function(result) {
                    console.log(result);
                    if (result == 1) {
                        flashnotif('Success', 'Password has been reset!', 'success');
                        table.ajax.reload(null, false);
                        $("#modal-reset").modal('hide');
                    } else {
                        flashnotif('Failed', 'Failed to reset password', 'error');
                    }
                }
            });

            return false;

        });

        $("#btnBack, #btnBack2").on("click", function() {
            $("#modal-add, #modal-edit").modal('hide');
            $("#deselect-all_add").trigger('click');
        });

    });
</script>