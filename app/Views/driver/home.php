<!-- MAIN -->
<?= $this->include('layout/main') ?>
<!-- MAIN END -->

<!-- CSS -->
<!-- INTERNAL SELECT2 CSS -->
<link href="<?= base_url() ?>/teamplate/assets/plugins/select2/select2.min.css" rel="stylesheet" />
<!-- CUSTOM SCROLL BAR CSS-->
<link href="<?= base_url() ?>/teamplate/assets/plugins/scroll-bar/jquery.mCustomScrollbar.css" rel="stylesheet" />
<!--PERFECT SCROLL CSS-->
<link href="<?= base_url() ?>/teamplate/assets/plugins/p-scroll/perfect-scrollbar.css" rel="stylesheet" />

<!-- CSS END -->

<!-- LAYOUT BODY -->
<?= $this->include('layout/body') ?>
<!-- LAYOUT BODY -->

<!--app-content open-->
<div class="app-content">
    <div class="side-app">
        <?php 
        $programId = session()->get('program');
        // var_dump($programId);exit;
        if(!$programId) 
        {?>
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div><br></div>
                <div class="col-md-12">
                    <div class="alert alert-danger text-center">
                        <strong>PROGRAM BELUM DI PILIH!!</strong>
                        <hr class="message-inner-separator">
                        <p>Klik tanda <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"></path><path d="M21 11.01L3 11v2h18zM3 16h12v2H3zM21 6H3v2.01L21 8z"></path></svg>
                        untuk memilih program
                        </p>
                    </div>
                
                </div>
            </div>
        </div>
        <?php }?>
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div><br></div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Card-Header</div>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary btn-block">Check In</button>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
<!-- FOOTER -->
<?= $this->include('layout/footers') ?>
<!-- FOOTER END -->


<script src="<?= base_url() ?>/teamplate/assets/plugins/input-mask/jquery.maskedinput.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url() ?>/teamplate/assets/plugins/date-picker/spectrum.js"></script>
