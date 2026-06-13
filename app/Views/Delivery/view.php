<!doctype html>
<html lang="en" dir="ltr">
	<head>

		<!-- META DATA -->
		<meta charset="UTF-8">
		<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="description" content="Yoha –  HTML5 Bootstrap Admin Template">
		<meta name="author" content="Spruko Technologies Private Limited">
		<meta name="keywords" content="admin dashboard html template, admin dashboard template bootstrap 4, analytics dashboard templates, best admin template bootstrap 4, best bootstrap admin template, bootstrap 4 template admin, bootstrap admin template premium, bootstrap admin ui, bootstrap basic admin template, cool admin template, dark admin dashboard, dark admin template, dark dashboard template, dashboard template bootstrap 4, ecommerce dashboard template, html5 admin template, light bootstrap dashboard, sales dashboard template, simple dashboard bootstrap 4, template bootstrap 4 admin">

		<!-- FAVICON -->
		<link rel="shortcut icon" type="image/x-icon" href="<?= base_url() ?>/teamplate/assets/images/brand/favicon.ico" />

		<!-- TITLE -->
		<title><?=$title?></title>


		<!-- BOOTSTRAP CSS -->
		<link href="<?= base_url() ?>/teamplate/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

		<!-- STYLE CSS -->
		<link href="<?= base_url() ?>/teamplate/assets/css/style.css" rel="stylesheet"/>
		<link href="<?= base_url() ?>/teamplate/assets/css/skin-modes.css" rel="stylesheet"/>
		<link href="<?= base_url() ?>/teamplate/assets/css/dark-style.css" rel="stylesheet"/>

		<!-- SIDE-MENU CSS -->
		<link href="<?= base_url() ?>/teamplate/assets/css/closed-sidemenu.css" rel="stylesheet">

		<!--PERFECT SCROLL CSS-->
		<link href="<?= base_url() ?>/teamplate/assets/plugins/p-scroll/perfect-scrollbar.css" rel="stylesheet"/>

		<!-- CUSTOM SCROLL BAR CSS-->
		<link href="<?= base_url() ?>/teamplate/assets/plugins/scroll-bar/jquery.mCustomScrollbar.css" rel="stylesheet"/>

		<!--- FONT-ICONS CSS -->
		<link href="<?= base_url() ?>/teamplate/assets/css/icons.css" rel="stylesheet"/>

		<!-- SIDEBAR CSS -->
		<link href="<?= base_url() ?>/teamplate/assets/plugins/sidebar/sidebar.css" rel="stylesheet">

		<!-- COLOR SKIN CSS -->
		<link id="theme" rel="stylesheet" type="text/css" media="all" href="<?= base_url() ?>/teamplate/assets/colors/color1.css" />
                
                <!-- TOASTR CSS -->
                <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
                <!-- JQUERY JS -->
		<script src="<?= base_url() ?>/teamplate/assets/js/jquery-3.4.1.min.js"></script>


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
								<h1 class="page-title"><?=$title?></h1>
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="<?= base_url() ?>/client">Index</a></li>
									<li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
								</ol>
							</div>
							<div class="ml-auto pageheader-btn">
								<a href="<?=base_url()?>/delivery/index" class="btn btn-warning mr-1">
									<span>
										<i class="fa fa-arrow-left"></i>
									</span> Back
								</a>
							</div>
						</div>
						<!-- PAGE-HEADER END -->
                                                
                        <!-- ROW-1 OPEN -->
						<div class="row">
							<div class="col-md-12">
								<div class="card">
									<div class="card-header bg-primary">
										<h3 class="mb-0 card-title"><?=$title?> View</h3>
									</div>
                        
									<div class="card-body">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">CODE</label>
                                                    <input type="text" class="form-control" value="<?= $datadelivery[0]["code"]; ?>" readonly="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">ITEM NAME </label>
													<input type="text" class="form-control" value="<?= $datadelivery[0]["item_name"]; ?>" readonly="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">USERNAME</label>
													<input type="text" class="form-control" value="<?= $datadelivery[0]["username"]; ?>" readonly="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">STATUS</label>
													<input type="text" class="form-control" value="<?= $datadelivery[0]["status"]; ?>" readonly="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group m-0">
													<label class="form-label">BOOKING DATE</label>
													<input type="text" class="form-control" value="<?= $datadelivery[0]["booking_date"]; ?>" readonly="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">OUTBOUND DATE</label>
                                                    <input type="text" class="form-control" value="<?= $datadelivery[0]["outbound_date"]; ?>" readonly="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">COURIER</label>
                                                    <input type="text" class="form-control" value="<?php if ($datadelivery[0]["courier"] == NULL) 
													{
														echo "NONE";
													}else{ echo $datadelivery[0]["courier"]; } ?>" readonly="">
                                                </div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">DOCUMENT</label>
                                                    <input type="text" class="form-control" value="<?php if ($datadelivery[0]["document"] == NULL) 
													{
														echo "NONE";
													}else{ echo $datadelivery[0]["document"]; } ?>" readonly="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">BOOKING QUANTITY</label>
                                                    <input type="text" class="form-control" value="<?php if ($datadelivery[0]["booking"] == NULL) 
													{
														echo "NONE";
													}else{ echo $datadelivery[0]["booking"]; } ?>" readonly="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">OUTBOUND QUANTITY</label>
                                                    <input type="text" class="form-control" value="<?php if ($datadelivery[0]["delivery_qty"] == NULL) 
													{
														echo "NONE";
													}else{ echo $datadelivery[0]["delivery_qty"]; } ?> " readonly="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">RECEIVED QUANTITY</label>
                                                    <input type="text" class="form-control" value="<?php if ($datadelivery[0]["received"] == NULL) 
													{
														echo "NONE";
													}else{ echo $datadelivery[0]["received"]; } ?>" readonly="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">DESCRIPTION</label>
                                                    <input type="text" class="form-control" value="<?php if ($datadelivery[0]["description"] == NULL) 
													{
														echo "NONE";
													}else{ echo $datadelivery[0]["description"]; } ?>" readonly="">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="form-label">CREATED DATE</label>
                                                    <input type="text" class="form-control" value="<?php if ($datadelivery[0]["created_date"] == NULL) 
													{
														echo "NONE";
													}else{ echo $datadelivery[0]["created_date"]; } ?>" readonly="">
												</div>
											</div>
										</div>
                                        
									</div>
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
		
		<!-- BOOTSTRAP JS -->
		<script src="<?= base_url() ?>/teamplate/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="<?= base_url() ?>/teamplate/assets/plugins/bootstrap/js/popper.min.js"></script>

		<!-- SPARKLINE JS-->
		<script src="<?= base_url() ?>/teamplate/assets/js/jquery.sparkline.min.js"></script>

		<!-- CHART-CIRCLE JS-->
		<script src="<?= base_url() ?>/teamplate/assets/js/circle-progress.min.js"></script>

		<!-- RATING STARJS -->
		<script src="<?= base_url() ?>/teamplate/assets/plugins/rating/jquery.rating-stars.js"></script>

		<!-- EVA-ICONS JS -->
		<script src="<?= base_url() ?>/teamplate/assets/iconfonts/eva.min.js"></script>

		<!-- INTERNAL CHARTJS CHART JS -->
		<script src="<?= base_url() ?>/teamplate/assets/plugins/chart/Chart.bundle.js"></script>
		<script src="<?= base_url() ?>/teamplate/assets/plugins/chart/utils.js"></script>

		<!-- INTERNAL PIETY CHART JS -->
		<script src="<?= base_url() ?>/teamplate/assets/plugins/peitychart/jquery.peity.min.js"></script>
		<script src="<?= base_url() ?>/teamplate/assets/plugins/peitychart/peitychart.init.js"></script>

		<!-- SIDE-MENU JS-->
		<script src="<?= base_url() ?>/teamplate/assets/plugins/sidemenu/sidemenu.js"></script>

		<!-- PERFECT SCROLL BAR js-->
		<script src="<?= base_url() ?>/teamplate/assets/plugins/p-scroll/perfect-scrollbar.min.js"></script>
		<script src="<?= base_url() ?>/teamplate/assets/plugins/sidemenu/sidemenu-scroll.js"></script>

		<!-- CUSTOM SCROLLBAR JS-->
		<script src="<?= base_url() ?>/teamplate/assets/plugins/scroll-bar/jquery.mCustomScrollbar.concat.min.js"></script>

		<!-- SIDEBAR JS -->
		<script src="<?= base_url() ?>/teamplate/assets/plugins/sidebar/sidebar.js"></script>

		<!-- INTERNAL APEXCHART JS -->
		<script src="<?= base_url() ?>/teamplate/assets/js/apexcharts.js"></script>

		<!--INTERNAL  INDEX JS -->
		<script src="<?= base_url() ?>/teamplate/assets/js/index1.js"></script>

		<!-- CUSTOM JS -->
		<script src="<?= base_url() ?>/teamplate/assets/js/custom.js"></script>
		<!-- TOASTR JS -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
		 
	</body>
</html>
