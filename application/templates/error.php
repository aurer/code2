<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title><?= $heading ?></title>
<meta name="description" content="">
<meta name="author" content="">

<meta name="viewport" content="width=device-width,initial-scale=1">
<script src="/theme/grey/js/libs/modernizr-2.0.6.min.js"></script> 
<script src='/theme/grey/
js/libs/jquery-1.7.min.js'></script>
<link href="/theme/grey/css/style.css" rel="stylesheet">
<link href="/theme/grey/css/layout.css" rel="stylesheet">
</head>
<body>
<div id="page">
	<div id="container">
		<? include 'inc/nav1.php' ?>
		<div id="main" role="main">
			<h1><?php echo $heading; ?></h1>
			<?php echo $message; ?>
		</div>
	</div>
</div>
<div id="footer"><div class="inner">
	<? include 'inc/footer.php' ?>
</div></div>
</body>
</html>
	