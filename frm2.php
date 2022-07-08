<?php 
if(isset($_GET['type'])&&($_GET['type']!="")){
	$type=0;
	if($_GET['type']==1){//Sin llamada
		$type=$_GET['type'];
	}elseif($_GET['type']==2){
		//Recargar esta pagina con la pagina del archivo valida que se encuentra en Elastix
		//Esto para no mostrar la ruta del servidor en el front
		$type=$_GET['type'];
		require_once("includes/conexion.php");
		$LinkElastx=ObtenerVariable("IPElastix")."/llamada/portalone_valida.php?type=1&ext=".base64_encode($_SESSION['Ext'])."&dest=".$_GET['dest']."&etiq=".$_GET['etiq'];
	}
?>
<?php if($type==1){?>
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
		<?php 
	if($type==1){
		?>
			<h2 class="text-muted">Listo para realizar llamada...</h2>
		<?php	
		}
	?>
	</div>
</body>
</html>
<?php }elseif($type==2){
		$arrContextOptions=array(
			"ssl"=>array(
				"verify_peer"=>false,
				"verify_peer_name"=>false,
			),
		);  

		//include_once($LinkElastx);
		$pagina_inicio = file_get_contents($LinkElastx,false, stream_context_create($arrContextOptions));
		echo $pagina_inicio;
	}
}?>