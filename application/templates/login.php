<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<title>Code2 <?= $page_title ?></title>
<meta name="description" content="">
<meta name="author" content="">

<meta name="viewport" content="width=device-width,initial-scale=1">
<script src="/theme/grey/js/libs/modernizr-2.0.6.min.js"></script> 
<script src='/theme/grey/js/libs/jquery-1.7.min.js'></script>
<link href="/theme/grey/css/style.css" rel="stylesheet">
<link href="/theme/grey/css/layout.css" rel="stylesheet">

<?= $head ?>

</head>
<body class="login <?= $bodyclass ?>" >
<div id="page">
	<div id="container">
		<div id="main" role="main">
			<?= $primary ?>
		</div>
		<div id="secondary">
			<div class="inner">
			<? include 'inc/userpanel.php' ?>
			<?= $secondary ?>
			</div>
		</div>
	</div>
</div>
</body>
</html>
	