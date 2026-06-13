<!doctype html>
<html lang="en" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Yoha –  HTML5 Bootstrap Admin Template">
    <meta name="author" content="Spruko Technologies Private Limited">
    <meta name="keywords"
        content="admin dashboard html template, admin dashboard template bootstrap 4, analytics dashboard templates, best admin template bootstrap 4, best bootstrap admin template, bootstrap 4 template admin, bootstrap admin template premium, bootstrap admin ui, bootstrap basic admin template, cool admin template, dark admin dashboard, dark admin template, dark dashboard template, dashboard template bootstrap 4, ecommerce dashboard template, html5 admin template, light bootstrap dashboard, sales dashboard template, simple dashboard bootstrap 4, template bootstrap 4 admin">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url() ?>/teamplate/assets/images/brand/favicon.ico" />

    <!-- TITLE -->
    <title>MEMBER CREATE</title>

    <!-- BOOTSTRAP CSS -->
    <link href="<?= base_url() ?>/teamplate/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="<?= base_url() ?>/teamplate/assets/css/style.css" rel="stylesheet" />
    <link href="<?= base_url() ?>/teamplate/assets/css/skin-modes.css" rel="stylesheet" />
    <link href="<?= base_url() ?>/teamplate/assets/css/dark-style.css" rel="stylesheet" />

    <!-- SIDE-MENU CSS -->
    <link href="<?= base_url() ?>/teamplate/assets/css/sidemenu.css" rel="stylesheet">
    <link href="<?= base_url() ?>/teamplate/assets/css/closed-sidemenu.css" rel="stylesheet">

    <!--PERFECT SCROLL CSS-->
    <link href="<?= base_url() ?>/teamplate/assets/plugins/p-scroll/perfect-scrollbar.css" rel="stylesheet" />

    <!-- CUSTOM SCROLL BAR CSS-->
    <link href="<?= base_url() ?>/teamplate/assets/plugins/scroll-bar/jquery.mCustomScrollbar.css" rel="stylesheet" />

    <!--- FONT-ICONS CSS -->
    <link href="<?= base_url() ?>/teamplate/assets/css/icons.css" rel="stylesheet" />

    <!-- SIDEBAR CSS -->
    <link href="<?= base_url() ?>/teamplate/assets/plugins/sidebar/sidebar.css" rel="stylesheet">

    <!-- INTERNAL  FILE UPLODE CSS -->
    <link href="<?= base_url() ?>/teamplate/assets/plugins/fileuploads/css/fileupload.css" rel="stylesheet"
        type="text/css" />

    <!-- INTERNAL SELECT2 CSS -->
    <link href="<?= base_url() ?>/teamplate/assets/plugins/select2/select2.min.css" rel="stylesheet" />

    <!-- INTERNAL BOOTSTRAP-DATERANGEPICKER CSS -->
    <link rel="stylesheet"
        href="<?= base_url() ?>/teamplate/assets/plugins/bootstrap-daterangepicker/daterangepicker.css">

    <!-- INTERNAL  TIME PICKER CSS -->
    <link href="<?= base_url() ?>/teamplate/assets/plugins/time-picker/jquery.timepicker.css" rel="stylesheet" />

    <!-- INTERNAL  DATE PICKER CSS-->
    <link href="<?= base_url() ?>/teamplate/assets/plugins/date-picker/spectrum.css" rel="stylesheet" />

    <!-- INTERNAL  MULTI SELECT CSS -->
    <link rel="stylesheet" href="<?= base_url() ?>/teamplate/assets/plugins/multipleselect/multiple-select.css">

    <!-- INTERNAL TELEPHONE CSS-->
    <link rel="stylesheet" href="<?= base_url() ?>/teamplate/assets/plugins/telephoneinput/telephoneinput.css">

    <!-- COLOR SKIN CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all"
        href="<?= base_url() ?>/teamplate/assets/colors/color1.css" />

    <style>
    fonts {
        color: black;
    }
    </style>
</head>

<body class="app sidebar-mini dark-mode">

    <!-- GLOBAL-LOADER -->
    <!-- <div id="global-loader">
			<img src="<?= base_url() ?>/teamplate/assets/images/loader.svg" class="loader-img" alt="Loader">
		</div> -->
    <!-- /GLOBAL-LOADER -->

    <!-- PAGE -->
    <div class="page">
        <div class="page-main">

            <!-- SIDEBAR -->
            <?= $this->include('layout/sidebar') ?>
            <!-- END SIDEBAR -->
            <!-- TOP BAR -->
            <?= $this->include('layout/topbar') ?>
            <!-- END TOP BAR -->

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
                                <li class="breadcrumb-item"><a href="<?= base_url() ?>/member">Index</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
                            </ol>
                        </div>
                    </div>
                    <!-- PAGE-HEADER END -->
                    <!-- <div class="alert alert-info" role="alert">

                        <fonts>Member belum mempunyai akun redeem klik
                            di</fonts> <a href="<?= base_url() ?>/user/add" style="color: blue;">sini</a><br>
                    </div> -->
                    <!-- ROW-1 OPEN -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-primary br-tr-3 br-tl-3">
                                    <h3 class="mb-0 card-title">MEMBER CREATE</h3>
                                </div>
                                <form action='<?=base_url();?>/member/save' method="post">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Code *</label>
                                                    <input class="form-control" type="text" name="code">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Channel</label>
                                                    <input class="form-control" type="text" name="channel">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">First Name *</label>
                                                    <input class="form-control" type="text" name="first_name"
                                                        required="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Last Name</label>
                                                    <input class="form-control" type="text" name="last_name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Gender *</label>
                                                    <select class="form-control" name="gender">
                                                        <option value=''>-- Select One --</option>
                                                        <option value="Pria">Pria</option>
                                                        <option value="wanita">Perempuan</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Merchant</label>
                                                    <select class="form-control select2-show-search" name="merchant">
                                                        <option value=''>-- Select One --</option>
                                                        <?php foreach($identity_type as $identity_types): ?>
                                                        <option
                                                            value="<?php echo $identity_types['identity_type_id'];?>">
                                                            <?php echo $identity_types['name'];?></option>
                                                        <?php endforeach;?>
                                                    </select>
                                                    <?php  ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Identity Number</label>
                                                    <input class="form-control" type="text" name="identity_number">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Place Of Bird</label>
                                                    <input class="form-control" type="text" name="placeofbird">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Date Of Bird</label>
                                                    <div class="wd-200 mg-b-30">
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <div class="input-group-text">
                                                                    <i class="fa fa-calendar tx-16 lh-0 op-6"></i>
                                                                </div>
                                                            </div><input class="form-control fc-datepicker"
                                                                placeholder="MM/DD/YYYY" type="text">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Mobile Phone</label>
                                                    <input class="form-control" type="text" name="mobile_phone">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Personal Email</label>
                                                    <input class="form-control" type="text" name="persnoal_email">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="form-label">Domicile Address</label>
                                                    <textarea class="form-control" name="domicile_address" rows="3"
                                                        placeholder=""></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Postal Code</label>
                                                    <input class="form-control" type="text" name="postal_code">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">City</label>
                                                    <select class="form-control select2-show-search" name="merchant">
                                                        <option value=''>-- Select One --</option>
                                                        <?php foreach($city as $citys): ?>
                                                        <option value="<?php echo $citys['city_id'];?>">
                                                            <?php echo $citys['name'];?></option>
                                                        <?php endforeach;?>
                                                    </select>
                                                    <?php  ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">State</label>
                                                    <input class="form-control" type="text" name="state">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Country</label>
                                                    <input class="form-control" type="text" name="country">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Company Name</label>
                                                    <input class="form-control" type="text" name="company_name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Company Phone</label>
                                                    <input class="form-control" type="text" name="company_phone">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Company Email</label>
                                                    <input class="form-control" type="text" name="company_email">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Company Fax</label>
                                                    <input class="form-control" type="text" name="company_fax">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Account Bank</label>
                                                    <input class="form-control" type="text" name="account_bank">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Account Name *</label>
                                                    <input class="form-control" type="text" name="account_name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Account Number *</label>
                                                    <input class="form-control" type="text" name="account_number">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="form-label">Domicile Address</label>
                                                    <textarea class="form-control" type="text" rows="3"
                                                        name="placeofbird"> </textarea>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="form-actions text-center mt-5">
                                            <a class="btn btn-warning mr-1" href="<?=base_url()?>/member"
                                                role="button"><i class="fa fa-window-close"></i> Cancel</a>
                                            <button type="submit" name="submit" value="save" class="btn btn-primary">
                                                <i class="fa fa-save"></i> Save
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div><!-- COL END -->
                    </div>
                    <!-- ROW-1 CLOSED -->
                </div>
            </div>
            <!-- CONTAINER END -->
        </div>
        <!-- FOOTER -->
        <?= $this->include('layout/footer') ?>
        <!-- FOOTER END -->

    </div>

    <!-- BACK-TO-TOP -->
    <a href="#top" id="back-to-top"><i class="fa fa-angle-up"></i></a>

    <!-- JQUERY JS -->
    <script src="<?= base_url() ?>/teamplate/assets/js/jquery-3.4.1.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url() ?>/teamplate/assets/plugins/bootstrap/js/popper.min.js"></script>

    <!-- RATING STAR JS-->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/rating/jquery.rating-stars.js"></script>

    <!-- EVA-ICONS JS -->
    <script src="<?= base_url() ?>/teamplate/assets/iconfonts/eva.min.js"></script>

    <!-- INPUT MASK JS-->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/input-mask/jquery.mask.min.js"></script>

    <!-- CUSTOM SCROLLBAR JS-->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/scroll-bar/jquery.mCustomScrollbar.concat.min.js"></script>

    <!-- SIDE-MENU JS-->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/sidemenu/sidemenu.js"></script>

    <!-- SIDEBAR JS -->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/sidebar/sidebar.js"></script>

    <!-- INTERNAL  FILE UPLOADES JS -->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/fileuploads/js/fileupload.js"></script>
    <script src="<?= base_url() ?>/teamplate/assets/plugins/fileuploads/js/file-upload.js"></script>

    <!-- INTERNAL SELECT2 JS -->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/select2/select2.full.min.js"></script>

    <!-- INTERNAL BOOTSTRAP-DATERANGEPICKER JS -->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/bootstrap-daterangepicker/moment.min.js"></script>
    <script src="<?= base_url() ?>/teamplate/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>

    <!-- INTERNAL  TIMEPICKER JS -->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/time-picker/jquery.timepicker.js"></script>
    <script src="<?= base_url() ?>/teamplate/assets/plugins/time-picker/toggles.min.js"></script>

    <!-- INTERNAL DATEPICKER JS-->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/date-picker/spectrum.js"></script>
    <script src="<?= base_url() ?>/teamplate/assets/plugins/date-picker/jquery-ui.js"></script>
    <script src="<?= base_url() ?>/teamplate/assets/plugins/input-mask/jquery.maskedinput.js"></script>

    <!-- INTERNAL MULTI SELECT JS -->
    <script src="<?= base_url() ?>/teamplate/assets/plugins/multipleselect/multiple-select.js"></script>
    <script src="<?= base_url() ?>/teamplate/assets/plugins/multipleselect/multi-select.js"></script>

    <!--INTERNAL  FORMELEMENTS JS -->
    <script src="<?= base_url() ?>/teamplate/assets/js/form-elements.js"></script>
    <script src="<?= base_url() ?>/teamplate/assets/js/select2.js"></script>

    <!-- CUSTOM JS -->
    <script src="<?= base_url() ?>/teamplate/assets/js/custom.js"></script>

    <script type="text/javascript">
    <?php if (session()->getFlashdata('success')) {?>
    toastr.success("<?php echo session()->getFlashdata('success'); ?>");
    <?php }  ?>
    </script>

    <script>
    $(document).ready(function() {
        $("#programid").change(function() {
            var programid = $('#programid').val();
            console.log(programid);
            $.ajax({
                type: 'GET',
                url: "<?= base_url('/dashboard/menuprivilage');?>/" + $(this).val(),
                data: {
                    'programid': programid
                },
                beforeSend: function() {
                    $("#global-loader").show();
                },
                success: function(data) {
                    $('#resultdiv').html(data);
                    $("#global-loader").hide();
                    var slideMenu = $('.side-menu');
                    // Activate sidebar slide toggle
                    $("[data-toggle='slide']").on('click', function(event) {
                        event.preventDefault();
                        if (!$(this).parent().hasClass('is-expanded')) {
                            slideMenu.find("[data-toggle='slide']").parent()
                                .removeClass('is-expanded');
                        }
                        $(this).parent().toggleClass('is-expanded');
                    });
                    // Set initial active toggle
                    $("[data-toggle='slide.'].is-expanded").parent().toggleClass(
                        'is-expanded');
                }
            });
        });
    });

    $(document).ready(function() {

        //document.getElementById("hidepromo").style.display = "none";

        $('#itemtype').change(function() {

            var type_id = $(this).val();
            console.log(type_id);

            //AJAX request 
            $.ajax({
                url: '<?php echo base_url('Item/itemsubtype');?>',
                method: 'post',
                data: {
                    type_id: type_id
                },
                dataType: 'json',
                success: function(response) {
                    response.forEach(function(responses) {
                        console.log(responses);

                    });
                    // output = "Your order for the following items has been placed: ";
                    document.getElementById('text').innerHTML = response.text;
                }
            });
        });
    });
    </script>

</body>

</html>