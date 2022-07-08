<?php 
if(isset($_GET['type'])&&($_GET['type']!="")){
	$type=0;
	$Tel="";
	if($_GET['type']==1){//Sin llamada
		$type=$_GET['type'];
	}elseif($_GET['type']==2){//Marcacion iniciada
		$type=$_GET['type'];
		if($_GET['etiq']!=""){
			$Tel=base64_decode($_GET['etiq']);
		}
	}
?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="x-ua-compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
<link rel="shortcut icon" href="css/favicon.png" />
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<style>
	.ibox-content{
		border-top: none; !important
	}
</style>
</head>
<body>
	<div class="ibox-content">
		<?php if($type==1){?>
			<h2 class="text-muted">Sin llamada detectada...</h2>
		<?php }elseif($type==2){?>
			<h2><span class="text-success font-bold">Procesado: </span><span class="text-danger"><?php echo $Tel;?></span></h2>
		<?php }?>
	</div>
</body>
</html>
<?php }?>