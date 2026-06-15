<!-- MAIN -->
<?= $this->include('layout/main') ?>
<!-- MAIN END -->

<!-- CSS -->
<!-- INTERNAL  DATA TABLE CSS-->
<link href="<?= base_url() ?>/teamplate/assets/plugins/datatable/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="<?= base_url() ?>/teamplate/assets/plugins/datatable/responsivebootstrap4.min.css" rel="stylesheet" />
<link href="<?= base_url() ?>/teamplate/assets/plugins/datatable/fileexport/buttons.bootstrap4.min.css" rel="stylesheet" />
<!-- INTERNAL  TABS STYLES -->
<link href="<?= base_url() ?>/teamplate/assets/plugins/tabs/tabs.css" rel="stylesheet" />
<!-- CSS END -->

<!-- MAIN -->
<?= $this->include('layout/body') ?>
<!-- MAIN END -->

<?php /** @var string $title */ ?>

<!--app-content open-->
<div class="app-content">
    <div class="side-app">

        <!-- PAGE-HEADER -->
        <div class="page-header">
            <div>
                <h1 class="page-title">
                </h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Table</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
                </ol>
            </div>
            <div class="ml-auto pageheader-btn">
                <a href="<?=base_url()?>/QualityControl/create" class="btn btn-primary btn-icon text-white mr-2">
                    <span>
                        <i class="fe fe-plus"></i>
                    </span> Create New
                </a>
            </div>
        </div>
        <!-- PAGE-HEADER END -->
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title text-white">DATA QUALITY CONTROL</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tableQc" class="table table-striped table-bordered text-nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Shipment Number</th>
                                        <th>Company Name</th>
                                        <th>RESULT</th>
                                        <th>FFA</th>
                                        <th>M & I</th>
                                        <th>Created Date</th>
                                        <th width="120">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<?= $this->include('layout/footers') ?>
<!-- FOOTER END -->

<!-- INTERNAL  DATA TABLE JS-->
<script src="<?= base_url() ?>/teamplate/assets/plugins/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/teamplate/assets/plugins/datatable/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/teamplate/assets/plugins/datatable/dataTables.responsive.min.js"></script>

<script>
$(document).ready(function () {

    $('#tableQc').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,

        ajax: {
            url: "<?= base_url('qualitycontrol/datatables') ?>",
            type: "POST"
        },
        columns: [
            { data: 'shipment_number' },
            { data: 'company_name' },
            { data: 'result_badge' },
            { data: 'ffa' },
            { data: 'mi' },
            { data: 'created_date' },
            { data: 'action', orderable: false }
        ]
    });
});
</script>