<!-- MAIN -->
<?= $this->include('layout/main') ?>
<!-- MAIN END -->

<!-- CSS -->
<style>
th {
    color: white;
    width: 300px;
}
</style>
<!-- CSS END -->

<!-- LAYOUT BODY -->
<?= $this->include('layout/body') ?>
<!-- LAYOUT BODY -->

<?php /** @var array<string, mixed> $views */ ?>

<!--app-content open-->
<div class="app-content">
    <div class="side-app">

        <!-- PAGE-HEADER -->
        <div class="page-header">
            <div>
                <h1 class="page-title"></h1>

                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url() ?>/warehouse">
                            Warehouse
                        </a>
                    </li>

                    <li class="breadcrumb-item active">
                        Detail
                    </li>
                </ol>
            </div>
        </div>
        <!-- PAGE-HEADER END -->

        <!-- ROW -->
        <div class="row">

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header bg-primary">
                        <h3 class="card-title">
                            <i class="fa fa-info-circle"></i>
                            WAREHOUSE DETAIL
                        </h3>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-striped table-bordered">

                                <tbody>

                                    <tr>
                                        <th>WAREHOUSE CODE</th>
                                        <td><?= $views['warehouse_code']; ?></td>
                                    </tr>

                                    <tr>
                                        <th>WAREHOUSE NAME</th>
                                        <td><?= $views['warehouse_name']; ?></td>
                                    </tr>

                                    <tr>
                                        <th>ADDRESS</th>
                                        <td>
                                            <?= !empty($views['address']) ? $views['address'] : '-'; ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>LATITUDE</th>
                                        <td>
                                            <?= !empty($views['latitude']) ? $views['latitude'] : '-'; ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>LONGITUDE</th>
                                        <td>
                                            <?= !empty($views['longitude']) ? $views['longitude'] : '-'; ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>STATUS</th>
                                        <td>

                                            <?php if($views['is_active'] == 1){ ?>

                                            <span class="badge badge-success">
                                                Active
                                            </span>

                                            <?php } else { ?>

                                            <span class="badge badge-danger">
                                                Inactive
                                            </span>

                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>CREATED DATE</th>
                                        <td>
                                            <?= date(
                                                'd M Y H:i',
                                                strtotime($views['created_date'])
                                            ); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>MODIFIED DATE</th>
                                        <td>
                                            <?= date(
                                                'd M Y H:i',
                                                strtotime($views['modified_date'])
                                            ); ?>
                                        </td>
                                    </tr>

                                </tbody>

                            </table>

                        </div>

                        <?php if(
                            !empty($views['latitude']) &&
                            !empty($views['longitude'])
                        ){ ?>

                        <div class="mt-4">

                            <a href="https://www.google.com/maps?q=<?= $views['latitude'] ?>,<?= $views['longitude'] ?>"
                                target="_blank"
                                class="btn btn-info">

                                <i class="fa fa-map-marker"></i>
                                Open Google Maps

                            </a>

                        </div>

                        <?php } ?>

                        <hr>

                        <a href="<?= base_url() ?>/Warehouse"
                            class="btn btn-warning">

                            <i class="fa fa-arrow-left mr-2"></i>
                            Back

                        </a>

                    </div>

                </div>

            </div>

        </div>
        <!-- ROW END -->

    </div>
</div>

<!-- FOOTER -->
<?= $this->include('layout/footers') ?>
<!-- FOOTER END -->

<!-- SWEET ALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>