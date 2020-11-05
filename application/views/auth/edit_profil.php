<div class="container-fluid">

    <?php $this->load->view('template_top') ?>

    <div class="row">
        <div class="col-md-7">
            <div class="white-box p-l-20 p-r-20">
                <div class="row">
                    <div class="col-md-12">
                        <?php if($message_success){ ?>
                        <div class="alert alert-success alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><?php echo $message_success ?></div>
                        <?php } ?>
                        <?php if($message){ ?>
                        <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><?php echo $message ?></div>
                        <?php } ?>
                            <?php echo form_open_multipart("auth/edit_profil", array("class" => "form-horizontal"));?>
                            <div class="form-group">
                                <label class="col-md-12" for="example-email">Username</label>
                                <div class="col-md-12">
                                    <input type="email" name="username" class="form-control" value="<?php echo $user->USER_NAME ?>" disabled="disabled"> </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-12" for="example-email">Full Name</label>
                                <div class="col-md-12">
                                    <input type="text" name="display_name" class="form-control" value="<?php echo $user->DISPLAY_NAME ?>" placeholder="Full Name"> </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-12" for="example-email">Email Address</label>
                                <div class="col-md-12">
                                    <input type="email" name="email" class="form-control" value="<?php echo $user->EMAIL ?>" disabled="disabled"> </div>
                            </div>
                            <div class="form-group ">
                                <label class="col-md-12">Password</label>
                                <div class="col-sm-12">
                                    <input type="password" name="password" class="form-control" value="password" autocomplete="new-password">
                                    <span class="help-block"><small>Leave blank if you don't want to change it.</small></span> 
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success waves-effect waves-light m-r-10">Submit</button>
                        <?php echo form_close();?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>