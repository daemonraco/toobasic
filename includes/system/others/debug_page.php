<!DOCTYPE html>
<html>
	<head>
		<title>TooBasic-<?php echo TOOBASIC_VERSION ?>-<?php echo TOOBASIC_VERSION_NAME ?>: <?php echo$DebugPage->title ? $DebugPage->title : 'Debug Page' ?></title>
		<link rel="shortcut icon" type="image/png" href="<?php echo (ROOTURI != '/' ? ROOTURI : '') ?>/includes/system/images/TooBasic-icon-24px.png"/>

		<!-- jQuery -->
		<script type="text/javascript" src="<?php echo (ROOTURI != '/' ? ROOTURI : '') ?>/libraries/jquery/jquery-2.1.3.min.js"></script>

		<!-- Bootstrap -->
		<link rel="stylesheet" type="text/css" href="<?php echo (ROOTURI != '/' ? ROOTURI : '') ?>/libraries/bootstrap/css/bootstrap.min.css"/>
		<link rel="stylesheet" type="text/css" href="<?php echo (ROOTURI != '/' ? ROOTURI : '') ?>/libraries/bootstrap/css/bootstrap-theme.min.css"/>
		<script type="text/javascript" src="<?php echo (ROOTURI != '/' ? ROOTURI : '') ?>/libraries/bootstrap/js/bootstrap.min.js"></script>
		<style type="text/css">
			pre {
				width: 100%;
			}
		</style>
	</head>
	<body class="container">
		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
					<span class="navbar-brand">TooBasic-<?php echo TOOBASIC_VERSION ?>-<?php echo TOOBASIC_VERSION_NAME ?></span>
				</div>
			</div>
		</nav>

		<div class="col-xs-12 col-md-8">
			<div class="row text-center<?php echo $DebugPage->title ? '' : ' hide'; ?>">
				<?php if($DebugPage->title) { ?>
					<h1><?php echo $DebugPage->title; ?></h1>
				<?php } ?>
			</div>
			<br/>
			<?php echo $DebugPage->thing; ?>
		</div>
		<div class="hidden-xs hidden-sm col-md-1"></div>
		<div class="hidden-xs hidden-sm col-md-3 well well-sm">
			<div class="row text-center">
				<img src="<?php echo (ROOTURI != '/' ? ROOTURI : '') ?>/includes/system/images/TooBasic-logo-128px.png"/>
				<br/>
				<h4><?php echo TOOBASIC_VERSION ?>-<?php echo TOOBASIC_VERSION_NAME ?></h4>
			</div>
		</div>
	</body>
</html>
