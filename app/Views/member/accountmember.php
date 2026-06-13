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
                    <form id="accountPost" action='<?=base_url();?>/member/createaccount/<?= $member['member_id'] ?>'
                        method="post">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label class="form-label">Username <b style="color:red">*</b></label>
                                        <input class="form-control" type="text" name="username" id="username"
                                            placeholder="Username" required="">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Password <b style="color:red">*</b></label>
                                        <input class="form-control" type="password" name="password" id="password"
                                            placeholder="******" required="">
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions text-center mt-5">
                                <a class="btn btn-warning mr-1" href="<?=base_url()?>/member" role="button"><i
                                        class="fa fa-window-close"></i> Skip</a>
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

<!-- SWEET ALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Cegah tombol back
history.pushState(null, null, window.location.href);
window.onpopstate = function() {
    history.pushState(null, null, window.location.href);
};

// Insert database
$('#accountPost').on('submit', function(e) {
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
                    timer: 9500
                });
                // form[0].reset(); // Reset form jika sukses
                window.location.href = '<?= base_url() ?>/Member';
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
</script>