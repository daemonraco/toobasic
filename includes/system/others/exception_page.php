<!DOCTYPE html>
<html>
	<head>
		<title>TooBasic-<?php echo TOOBASIC_VERSION ?>: <?php echo $exceptionType ?></title>
		<link rel="shortcut icon" type="image/png" href="<?php echo ROOTURI ?>/includes/system/images/TooBasic-icon-24px.png"/>

		<!-- jQuery -->
		<script type="text/javascript" src="<?php echo ROOTURI ?>/libraries/jquery/jquery-2.1.3.min.js"></script>

		<!-- Bootstrap -->
		<link rel="stylesheet" type="text/css" href="<?php echo ROOTURI ?>/libraries/bootstrap/css/bootstrap.min.css"/>
		<link rel="stylesheet" type="text/css" href="<?php echo ROOTURI ?>/libraries/bootstrap/css/bootstrap-theme.min.css"/>
		<script type="text/javascript" src="<?php echo ROOTURI ?>/libraries/bootstrap/js/bootstrap.min.js"></script>
		<style type="text/css">
			body {
				background-image: url(<?php echo ROOTURI ?>/includes/system/images/TooBasic-failed-background.png);
			}
			.jumbotron {
				background-color: #fee;
			}
		</style>
	</head>
	<body class="container">
		<div class="jumbotron">
			<div class="row text-center">
				<img src="<?php echo ROOTURI ?>/includes/system/images/TooBasic-failed-logo.png"/>
			</div>
			<div class="row text-center">
				<h1><?php echo $exceptionType ?></h1>

				<h2>Message:</h2>
				<p><?php echo $exception->getMessage() ?></p>
				<p><?php echo $exception->getFile().':'.$exception->getLine() ?></p>
			</div>
<?php if($exceptionTrace) { ?>
			<div class="row">
				<pre>
<?php
	$position = 0;
	foreach($exceptionTrace as $entry) { 
		echo "#{$position}  ".(isset($entry['class'])?"{$entry['class']}::":'')."{$entry['function']}() called at [{$entry['file']}:{$entry['line']}]\n";
		$position++;
	}
?>
				</pre>
			</div>
<?php } ?>
		</div>
	</body>
</html>
