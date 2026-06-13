<!-- MAIN -->
<?= $this->include('layout/main') ?>
<!-- MAIN END -->

<!-- CSS -->
<!-- INTERNAL  DATA TABLE CSS-->
<link href="<?= base_url() ?>/teamplate/assets/plugins/datatable/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="<?= base_url() ?>/teamplate/assets/plugins/datatable/responsivebootstrap4.min.css" rel="stylesheet" />
<link href="<?= base_url() ?>/teamplate/assets/plugins/datatable/fileexport/buttons.bootstrap4.min.css"
    rel="stylesheet" />
<!-- CSS END -->

<!-- MAIN -->
<?= $this->include('layout/body') ?>
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
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>/supplier">INDEX</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
                </ol>
            </div>
        </div>
        <!-- PAGE-HEADER END -->
        <!-- ROW-1 OPEN -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title"><i class="fa fa-info-circle"></i> USERS INFORMATION</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-nowrap w-100">
                                <tbody>
                                    <tr>
                                        <th width="300px" style="color: white;">USERNAME</th>
                                        <td>&nbsp;<?php echo $views['username']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="300px" style="color: white;">FULL NAME</th>
                                        <td>&nbsp;<?php echo $views['first_name']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="300px" style="color: white;">PHONE</th>
                                        <td>&nbsp;<?php echo $views['phone']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="300px" style="color: white;">EMAIL</th>
                                        <td>&nbsp;<?php echo $views['email']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="300px" style="color: white;">CITY</th>
                                        <td>&nbsp;<?= !empty($views['city']) ? esc($views['city']) : '-';?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="300px" style="color: white;">REGION</th>
                                        <td>&nbsp;<?= !empty($views['region']) ? esc($views['region']) : '-';?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="300px" style="color: white;">ADDITIONAL HOUR</th>
                                        <td>&nbsp;<?= !empty($views['timezone']) ? esc($views['timezone']) : '-';?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="300px" style="color: white;">BIRTHDAY</th>
                                        <td>&nbsp;<?= !empty($views['birthday']) ? esc($views['birthday']) : '-';?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="300px" style="color: white;">LEADER NAME</th>
                                        <td>&nbsp;<?= !empty($views['leader_name']) ? esc($views['leader_name']) : '-';?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="300px" style="color: white;">COUNTER</th>
                                        <td>&nbsp;<?= !empty($views['counter']) ? esc($views['counter']) : '-'; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="300px" style="color: white;">ACTIVE</th>
                                        <td>
                                            <?= ($views['active'] == 1) ? 'Active' : 'Tidak Aktif'; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <h4 class="leading-normal">AUTHORIZATION LIST</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-nowrap w-100">
                                <thead>
                                    <tr>
                                        <th width="300px" style="color: white;">PROGRAM</th>
                                        <th width="300px" style="color: white;">GROUP</th>
                                        <th width="300px" style="color: white;">DATA LEVEL</th>
                                        <th width="300px" style="color: white;">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <?php
                                            if (empty($usrgrp)): ?>
                                        <td colspan="4" style="text-align: center;">Empty</td>
                                        <?php else: ?>
                                        <?php
                                            for($a=0 ; $a<count($usrgrp) ; $a++)
                                            {
                                                $usrgp_id = $usrgrp[$a]['users_group_program_id'];
                                                $id = $usrgrp[$a]['users_id'];
                                            
                                        ?>
                                        <td>&nbsp;<?php echo $usrgrp[$a]['name_program'];?></td>
                                        <td>&nbsp;<?php echo $usrgrp[$a]['group'];?></td>
                                        <td>&nbsp;<?php echo $usrgrp[$a]['data_level'];?></td>
                                        <td>
                                            <a href="<?=base_url()?>/user/delete/<?= $usrgp_id?>"
                                                class="badge badge-pill badge-danger" title="Edit"><i
                                                    class="fa fa-times"></i></a>
                                        </td>
                                        <?php } ?>
                                        <?php endif; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <a href="<?= base_url() ?>/User" type="button" class="btn btn-warning"><i
                                class="fa fa-arrow-left mr-2"></i>Back</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- ROW-1 CLOSED -->
    </div>
</div>

<!-- FOOTER -->
<?= $this->include('layout/footers') ?>
<!-- FOOTER END -->

<!-- INTERNAL  DATA TABLE JS-->
<script src="<?= base_url() ?>/teamplate/assets/plugins/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/teamplate/assets/plugins/datatable/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/teamplate/assets/plugins/datatable/dataTables.responsive.min.js"></script>