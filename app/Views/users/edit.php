<!-- MAIN -->
<?= $this->include('layout/main') ?>
<!-- MAIN END -->

<!-- JQUERY JS -->
<script src="<?= base_url() ?>/teamplate/assets/js/jquery-3.4.1.min.js"></script>
<!-- INTERNAL SELECT2 CSS -->
<link href="<?= base_url() ?>/teamplate/assets/plugins/select2/select2.min.css" rel="stylesheet" />
<!-- INTERNAL BOOTSTRAP-DATERANGEPICKER CSS -->
<link rel="stylesheet" href="<?= base_url() ?>/teamplate/assets/plugins/bootstrap-daterangepicker/daterangepicker.css">

<!-- INTERNAL  TIME PICKER CSS -->
<link href="<?= base_url() ?>/teamplate/assets/plugins/time-picker/jquery.timepicker.css" rel="stylesheet" />

<!-- INTERNAL  DATE PICKER CSS-->
<link href="<?= base_url() ?>/teamplate/assets/plugins/date-picker/spectrum.css" rel="stylesheet" />

<!-- MAIN -->
<?php echo $this->include('layout/body'); ?>
<!-- MAIN END -->

<!--app-content open-->
<div class="app-content">
    <div class="side-app">

        <!-- PAGE-HEADER -->
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <?//=$title?>
                </h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>/user">Index</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
                </ol>
            </div>
        </div>
        <!-- PAGE-HEADER END -->

        <!-- ROW-1 OPEN -->
        <div class="row">
            <form action='<?=base_url();?>/user/saveedit/<?php echo $users['users_id'];?>'
                enctype="multipart/form-data" method="post" accept-charset="utf-8">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary br-tr-3 br-tl-3">
                            <h3 class="mb-0 card-title">USERS UPDATE</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">USERNAME <required>*</required></label>
                                        <input class="form-control" type="text" name="username"
                                            value="<?php echo $users['username'];?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">PASSWORD <required>*</required></label>
                                        <input class="form-control" type="password" name="password"
                                            id="password">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <br><br>
                                        &nbsp;<input type='checkbox' class='form-check-input' id='show'
                                            onclick="toggleVisibility()">
                                        <label>Show password</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">FULL NAME <required>*</required></label>
                                        <input class="form-control" type="text" name="full_name"
                                            value="<?php echo $users['first_name'];?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">EMAIL <required>*</required></label>
                                        <input class="form-control" type="text" name="email"
                                            value="<?php echo $users['email'];?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">MOBILE PHONE<required> *</required>
                                        </label>
                                        <input class="form-control" type="text" name="phone"
                                            value="<?php echo $users['phone'];?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">TITLE
                                        </label>
                                        <input class="form-control" type="text" name="title"
                                            value="<?php echo $users['title'];?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">BIRTHDAY</label>
                                        <div class="wd-200 mg-b-30">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text">
                                                        <i class="fa fa-calendar tx-16 lh-0 op-6"></i>
                                                    </div>
                                                </div><input class="form-control fc-datepicker"
                                                    placeholder="MM/DD/YYYY" type="text" id="birthday"
                                                    name="birthday"
                                                    value="<?php echo $users['birthday'];?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">CITY</label>
                                        <select class="form-control select2-show-search" id="city_id"
                                            name="city">
                                            <option value="">--Select One--</option>
                                            <?php foreach($citys as $row): ?>
                                            <option value="<?php echo $row['city_id'];?>"
                                                <?php if($row['name'] == $users['city']) echo " selected" ?>
                                                id="city_id">
                                                <?php echo $row['name'];?></option>
                                            <?php endforeach;?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">REGION</label>
                                        <input class="form-control" type="text" name="region"
                                            value="<?php echo $users['region'];?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">TIMEZONE</label>
                                        <input class="form-control" type="text" name="timezone"
                                            value="<?php echo $users['timezone'];?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">LEADER NAME</label>
                                        <input class="form-control" type="text" name="leader_name"
                                            value="<?php echo $users['leader_name'];?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">COUNTER</label>
                                        <input class="form-control" type="text" name="counter"
                                            value="<?php echo $users['counter'];?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <br>
                                    <div class="form-group">
                                        <label class="custom-control custom-checkbox">
                                            <input type="hidden" name="active" value="0">
                                            <input type="checkbox"
                                                class="custom-control-input"
                                                name="active"
                                                value="1"
                                                <?= ($users['active'] == "1") ? 'checked' : '' ?>>
                                            <span class="custom-control-label">ACTIVE</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">PICTURE</label>
                                        <?php if ($users['picture'] == NULL) { ?>
                                        <input type="file" name="picture" class="dropify"
                                            data-default-file="<?=base_url()?>/upload/users/no-image.png"
                                            data-height="89" />
                                        <?php  }else { ?>
                                        <input type="file" name="picture" class="dropify"
                                            data-default-file="<?=base_url()?><?= $picture?>"
                                            data-height="150" />
                                        <?php  }?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="mb-0 card-title">GROUP</h3>
                        </div>

                        <div class="card-body">
                            <div id="step-3" class="">
                                <div class="alert alert-info" role="alert">
                                    Group is a menu option for user access<br>
                                    <ol class="list-order-style">
                                        <li>
                                            Click the dropdown group then select the group option.
                                        </li>
                                        <li>
                                            Displays the group and page data
                                        </li>
                                        <ol>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group" id='grouprefresh'>
                                        <label class="form-label">LIST GROUP<b class="text-danger">
                                                *</b></label>
                                        <select name="group_id" id="groups_id"
                                            class="form-control js-example-basic-single" required="">
                                            <option value="">--Select One--</option>
                                            <?php foreach ($listgroupexist as $r) { ?>
                                            <option value="<?php echo $r->group_id ?>" <?php if($r->group_name == $users['group_name']) echo " selected" ?>>
                                                <?php echo $r->group_name ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="view">

                            </div>
                            <div class="form-actions text-center mt-5">
                                <a class="btn btn-warning mr-1" href="<?=base_url()?>/User" role="button"><i
                                        class="fa fa-window-close"></i> Cancel</a>
                                <button type="submit" name="save" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div><!-- COL END -->
    </div>
    <!-- ROW-1 CLOSED -->
</div>
<!-- FOOTER -->
<?php echo $this->include('layout/footers'); ?>
<!-- FOOTER END -->

<!-- TOASTR JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- INTERNAL DATEPICKER JS-->
<script src="<?= base_url() ?>/teamplate/assets/plugins/date-picker/spectrum.js"></script>
<script src="<?= base_url() ?>/teamplate/assets/plugins/date-picker/jquery-ui.js"></script>
<script src="<?= base_url() ?>/teamplate/assets/plugins/input-mask/jquery.maskedinput.js"></script>

    <script type="text/javascript">
    <?php if (session()->getFlashdata('success')) {?>
    toastr.success("<?php echo session()->getFlashdata('success'); ?>");
    <?php }  ?>

    <?php if (session()->getFlashdata('error')) {?>
    toastr.error("<?php echo session()->getFlashdata('error'); ?>");
    <?php }  ?>

    $(document).ready(function() {

        $('.js-example-basic-single').select2();

        // username tidak menggunakan spasi
        $('#username').on('input', function() {
            // Hapus spasi dari input
            $(this).val($(this).val().replace(/\s+/g, ''));

        });

        $('#username').on('keydown', function(e) {
            // Cegah spasi ketika mengetik
            if (e.which === 32) {
                e.preventDefault();
            }

        });
        // end

    });

    $('.fc-datepicker').datepicker({
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: 'yy-mm-dd',
        maxDate: '0'
    });

    function toggleVisibility() {
        var getPasword = document.getElementById("password");
        if (getPasword.type === "password") {
            getPasword.type = "text";
        } else {
            getPasword.type = "password";
        }
    }

    $("#groups_id").change(function() {

        var group_id = $("#groups_id").val();
        var group_name = $("#groups_id :selected").text();
        // console.log(group_id);
        if (group_id == "") {
            $('#view').empty();
        } else if (group_name == "") {
            alert("Group Name Tidak boleh kosong");
        } else {
            var uri = '<?=base_url()?>/user/checkGroup';
            jQuery.ajax({
                type: 'POST',
                async: false,
                dataType: "json",
                url: uri,
                data: {
                    group_id: group_id,
                    group_name: group_name,
                },
                success: function(result) {

                    document.getElementById('view').innerHTML = result;
                    // $('#preview tbody').empty();

                }
            });
        }
    });
    </script>
</body>

</html>
