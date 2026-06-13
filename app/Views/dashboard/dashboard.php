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
<style>
.gear-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: transparent;
    padding: 0;
    /* z-index: 1000; */
    color: white;
}

.ui-datepicker,
.datepicker,
.bootstrap-datepicker,
.datetimepicker-dropdown,
.flatpickr-calendar {
    z-index: 9999 !important;
}

.card-title+small {
    margin-top: 4px;
}

.count-badge {
    display: inline-block;
    background-color: #14365b;
    color: white;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    text-align: center;
    line-height: 22px;
    font-weight: bold;
    margin-right: 1px;
}

@media (max-width: 768px) {
    .ml-auto.pageheader-btn a {
        margin-bottom: 0px;
        /* Menambahkan jarak bawah di tombol */
    }

    /* Menambahkan jarak spesifik antara tombol "Attendance Visit" dan "Selling" */
    .btn-cyan {
        margin-top: 10px;
        /* Menambahkan jarak atas pada tombol selling */
    }
}
</style>

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
                <h1 class="page-title text-center">Hi! SELAMAT DATANG</h1>
            </div>
        </div>
        
    </div>
</div>
<!-- FOOTER -->
<?= $this->include('layout/footers') ?>
<!-- FOOTER END -->


<script src="<?= base_url() ?>/teamplate/assets/plugins/input-mask/jquery.maskedinput.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
