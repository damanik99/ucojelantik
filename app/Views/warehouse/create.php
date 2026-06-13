<!-- MAIN -->
<?= $this->include('layout/main') ?>
<!-- MAIN END -->

<!-- LAYOUT BODY -->
<?= $this->include('layout/body') ?>
<!-- LAYOUT BODY -->

<?php /** 
 * @var string $title 
 * @var string $warehouse_code 
 * */ ?>

<div class="app-content">
    <div class="side-app">

        <!-- PAGE HEADER -->
        <div class="page-header">
            <div>
                <h1 class="page-title"><?= $title ?></h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url() ?>/warehouse">Warehouse</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Create
                    </li>
                </ol>
            </div>
        </div>
        <!-- PAGE HEADER END -->

        <div class="row">

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header bg-primary br-tr-3 br-tl-3">
                        <h3 class="mb-0 card-title">
                            CREATE WAREHOUSE
                        </h3>
                    </div>

                    <div class="card-body">

                        <form id="warehouseForm">

                            <div class="row">

                                <!-- Warehouse Code -->
                                <div class="col-md-4">

                                    <div class="form-group">

                                        <label class="form-label">
                                            Warehouse Code
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="warehouse_code"
                                            value="<?= $warehouse_code ?>"
                                            readonly>

                                    </div>

                                </div>

                                <!-- Warehouse Name -->
                                <div class="col-md-8">

                                    <div class="form-group">

                                        <label class="form-label">
                                            Warehouse Name
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="warehouse_name"
                                            placeholder="Example : Warehouse Jakarta"
                                            required>

                                    </div>

                                </div>

                                <!-- Address -->
                                <div class="col-md-12">

                                    <div class="form-group">

                                        <label class="form-label">
                                            Address
                                        </label>

                                        <textarea
                                            class="form-control"
                                            rows="4"
                                            name="address"
                                            placeholder="Warehouse Address"></textarea>

                                    </div>

                                </div>

                                <!-- Latitude -->
                                <div class="col-md-6">

                                    <div class="form-group">

                                        <label class="form-label">
                                            Latitude
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="latitude"
                                            placeholder="-6.20876340">

                                    </div>

                                </div>

                                <!-- Longitude -->
                                <div class="col-md-6">

                                    <div class="form-group">

                                        <label class="form-label">
                                            Longitude
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="longitude"
                                            placeholder="106.84559900">

                                    </div>

                                </div>

                                <!-- Status -->
                                <div class="col-md-4">

                                    <div class="form-group">

                                        <label class="form-label">
                                            Status
                                        </label>

                                        <select
                                            name="is_active"
                                            class="form-control">

                                            <option value="1">
                                                Active
                                            </option>

                                            <option value="0">
                                                Inactive
                                            </option>

                                        </select>

                                    </div>

                                </div>

                            </div>

                            <div class="text-center mt-4">

                                <a href="<?= base_url() ?>/warehouse"
                                    class="btn btn-warning">

                                    <i class="fa fa-window-close"></i>
                                    Cancel

                                </a>

                                <button
                                    type="submit"
                                    class="btn btn-primary">

                                    <i class="fa fa-save"></i>
                                    Save

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

<!-- FOOTER -->
<?= $this->include('layout/footers') ?>
<!-- FOOTER END -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

$('#warehouseForm').submit(function(e){

    e.preventDefault();

    $.ajax({

        url : '<?= base_url() ?>/warehouse/store',

        type : 'POST',

        data : $(this).serialize(),

        dataType : 'json',

        beforeSend : function(){

            Swal.fire({
                title: 'Please Wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

        },

        success : function(response){

            Swal.close();

            if(response.status){

                Swal.fire({

                    icon : 'success',
                    title : 'Success',
                    text : response.message

                }).then(function(){

                    window.location.href =
                        '<?= base_url() ?>/warehouse';

                });

            }else{

                let msg = '';

                $.each(response.message,function(k,v){

                    msg += v + '<br>';

                });

                Swal.fire({

                    icon : 'error',
                    title : 'Validation Error',
                    html : msg

                });

            }

        },

        error : function(){

            Swal.fire({

                icon : 'error',
                title : 'Error',
                text : 'Internal Server Error'

            });

        }

    });

});

</script>