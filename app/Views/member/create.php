<!-- MAIN -->
<?= $this->include('layout/main') ?>
<!-- MAIN END -->

<!-- CSS -->

<!-- INTERNAL SELECT2 CSS -->
<link href="<?= base_url() ?>/teamplate/assets/plugins/select2/select2.min.css" rel="stylesheet" />

<!-- CSS END -->

<!-- LAYOUT BODY -->
<?= $this->include('layout/body') ?>
<!-- LAYOUT BODY -->

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
                    <!-- Alert akan muncul di sini -->
                    <div id="alert-container"></div>
                    <form id="memberPost" action='<?=base_url();?>/member/create' method="post">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label class="form-label">Member Code <b style="color:red">*</b></label>
                                        <input class="form-control" type="text" name="code" id="member_code"
                                            placeholder="Member code can be typed and click button generated"
                                            required="">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;&nbsp;<br></label>
                                    <button type="button" id="generate_code"
                                        class="btn btn-success">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <i class="fa fa-random"></i> Generate &nbsp;&nbsp;&nbsp;&nbsp;
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Full Name <b style="color:red">*</b></label>
                                        <input class="form-control" type="text" name="full_name"
                                            placeholder="Example: John Doe" required="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Gender <b style="color:red">*</b></label>
                                        <select class="form-control" name="gender" required="">
                                            <option value=''>-- Select One --</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Mobile Phone <b style="color:red">*</b></label>
                                        <input class="form-control" type="text" name="mobile_phone" id="mobile_phone"
                                            placeholder="Example: +6287384932" required="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" style="display: inline-block; width: auto;">
                                            Personal Email
                                        </label>
                                        <span id="email-error"
                                            style="color: red; display: none; font-size: 14px; margin-left: 10px;">
                                            Format email tidak valid
                                        </span>
                                        <input class="form-control" type="text" id="personal_email"
                                            name="personal_email" placeholder="mail@domain.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">City <b style="color:red">*</b></label>
                                        <select class="form-control select2-show-search" name="city" required="">
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
                                        <label class="form-label">Country</label>
                                        <select class="form-control select2-show-search" name="country">
                                            <option value=''>-- Select One --</option>
                                            <?php foreach($country as $country): ?>
                                            <option value="<?php echo $country['name'];?>">
                                                <?php echo $country['name'];?></option>
                                            <?php endforeach;?>
                                        </select>
                                        <?php  ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Domicile Address</label>
                                        <input class="form-control" type="text" id="domicile_address"
                                            name="domicile_address">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Postal Code</label>
                                        <input class="form-control" type="text" name="postal_code" id="postal_code"
                                            maxlength="5" placeholder="Just Numbers and Max Length 5">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Identity </label>
                                        <select class="form-control select2-show-search" name="identity_type_id">
                                            <option value=''>-- Select One --</option>
                                            <?php foreach($identity_type as $identity_types): ?>
                                            <option value="<?php echo $identity_types['identity_type_id'];?>">
                                                <?php echo $identity_types['name'];?></option>
                                            <?php endforeach;?>
                                        </select>
                                        <?php  ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Identity Number</label>
                                        <input class="form-control" type="text" name="identity_no">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Company Name</label>
                                        <input class="form-control" type="text" name="company_name" id="company_name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Company Phone</label>
                                        <input class="form-control" type="text" id="company_phone" name="company_phone"
                                            placeholder="Example: +6287384932">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" style="display: inline-block; width: auto;">Company
                                            Email</label>
                                        <span id="company-email-error"
                                            style="color: red; display: none; font-size: 14px; margin-left: 10px;">
                                            Format email tidak valid
                                        </span>
                                        <input class="form-control" type="text" id="company_email" name="company_email"
                                            placeholder="mail@domain.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Company Address</label>
                                        <input class="form-control" type="text" name="company_address"
                                            id="company_address">
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
                                        <label class="form-label">Account Name</label>
                                        <input class="form-control" type="text" name="account_name">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Account Number</label>
                                        <input class="form-control" type="text" name="account_number"
                                            id="account_number">
                                    </div>
                                </div>

                            </div>
                            <div class="form-actions text-center mt-5">
                                <a class="btn btn-warning mr-1" href="<?=base_url()?>/member" role="button"><i
                                        class="fa fa-window-close"></i> Cancel</a>
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
<?= $this->include('layout/footers') ?>
<!-- FOOTER END -->

<!--INTERNAL  FORMELEMENTS JS -->
<script src="<?= base_url() ?>/teamplate/assets/js/select2.js"></script>

<!-- INTERNAL SELECT2 JS -->
<script src="<?= base_url() ?>/teamplate/assets/plugins/select2/select2.full.min.js"></script>

<script src="<?= base_url() ?>/teamplate/assets/plugins/date-picker/spectrum.js"></script>
<script src="<?= base_url() ?>/teamplate/assets/plugins/date-picker/jquery-ui.js"></script>
<script src="<?= base_url() ?>/teamplate/assets/plugins/input-mask/jquery.maskedinput.js"></script>

<!-- SWEET ALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

    // Mobile Phone
    let prefix = "+62"; // Prefix wajib

    $('#mobile_phone').on('input', function() {
        // Pastikan awalan selalu +62
        if (!this.value.startsWith(prefix)) {
            this.value = prefix;
        }

        // Hanya izinkan angka setelah +62 dan tidak boleh dimulai dengan 0
        this.value = this.value.replace(/[^0-9\+]/g, ''); // Hanya angka
        if (this.value.length === 3 && this.value.charAt(3) === '0') {
            this.value = prefix; // Kembalikan ke +62 jika angka pertama setelah +62 adalah 0
        }
    });
    // Mencegah pengguna menghapus prefix +62
    $('#mobile_phone').on('keydown', function(e) {
        if (this.selectionStart < prefix.length && (e.key === "Backspace" || e.key === "Delete")) {
            e.preventDefault();
        }
    });
    // End Mobile Phone

    // Company Phone
    let company_prefix = "+62"; // Prefix wajib

    $('#company_phone').on('input', function() {
        // Pastikan awalan selalu +62
        if (!this.value.startsWith(company_prefix)) {
            this.value = company_prefix;
        }

        // Hanya izinkan angka setelah +62 dan tidak boleh dimulai dengan 0
        this.value = this.value.replace(/[^0-9\+]/g, ''); // Hanya angka
        if (this.value.length === 3 && this.value.charAt(3) === '0') {
            this.value = company_prefix; // Kembalikan ke +62 jika angka pertama setelah +62 adalah 0
        }
    });
    // Mencegah pengguna menghapus prefix +62
    $('#company_phone').on('keydown', function(e) {
        if (this.selectionStart < company_prefix.length && (e.key === "Backspace" || e.key ===
                "Delete")) {
            e.preventDefault();
        }
    });
    // End Company Phone

    $('#personal_email').on('input', function() {
        let email = $(this).val();
        let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!emailPattern.test(email)) {
            $('#email-error').show(); // Tampilkan pesan error jika format tidak valid
        } else {
            $('#email-error').hide(); // Sembunyikan pesan error jika valid
        }
    });

    $('#company_email').on('input', function() {
        let email = $(this).val();
        let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!emailPattern.test(email)) {
            $('#company-email-error').show(); // Tampilkan pesan error jika format tidak valid
        } else {
            $('#company-email-error').hide(); // Sembunyikan pesan error jika valid
        }
    });

    $('#domicile_address').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    $('#company_address').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    $('#company_name').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    $('#postal_code').on('input', function() {
        // Hanya izinkan angka (0-9)
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $('#account_number').on('input', function() {
        // Hanya izinkan angka (0-9)
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $('#generate_code').on('click', function() {
        $.ajax({
            url: '<?= base_url('member/generateMemberCode') ?>', // Ganti dengan URL sesuai routing CI4
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#member_code').val(response.code);
            },
            error: function() {
                alert('Gagal menghasilkan kode, coba lagi.');
            }
        });
    });

    // Insert database
    $('#memberPost').on('submit', function(e) {
        e.preventDefault(); // Cegah submit awal

        let form = $(this);
        let submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true); // Disable tombol submit sementara

        // Hapus alert jika ada sebelumnya
        $('#alert-container').html('');

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'error') {

                    if (response.message === 'Program Not selected yet') {
                        // Jika program belum dipilih, tampilkan alert di atas form
                        $('#alert-container').html(`
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <strong>Warning!</strong> ${response.message}
                            </div>
                        `);
                    } else {
                        // Jika error lain, tampilkan di SweetAlert
                        Swal.fire({
                            icon: 'error',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 3500
                        });
                    }

                } else {
                    Swal.fire({
                        icon: 'success',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 3500
                    });
                    // form[0].reset(); // Reset form jika sukses
                    window.location.href =
                        '<?= base_url() ?>/member/createaccount?member_id=' + response
                        .member_id;
                    console.log(window.location.href);
                }
                submitBtn.prop('disabled', false); // Aktifkan tombol submit kembali
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    text: 'Terjadi Kesalahan, Coba ulangi lagi.',
                    showConfirmButton: false,
                    timer: 3500
                });
                submitBtn.prop('disabled', false);
            }
        });
    });
});
</script>