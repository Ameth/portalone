<?php 

require("includes/definicion.php");
$_POST['BaseDatos']=BDPRO;

require("includes/conect_srv.php");
require("includes/funciones.php");
// require_once("includes/conexion_hn.php");
$sw_alert=0;//Indica que hay alertas
$sw_notify=0;//Indica que hay notificaciones
$Filtro="";//Filtro

$SQL_FactElect=Seleccionar('uvw_tbl_FacturacionElectronica_SeguimientoContadores','*');
$row_FactElect=sql_fetch_array($SQL_FactElect);

$Refresh=(isset($_GET['t']) && $_GET['t']!="") ? $_GET['t'] : 60000;

?>
<!DOCTYPE html>
<html>

<head>
<?php include("includes/cabecera.php"); ?>

<title>Facturación electrónica</title>

<style>
	#animar{
		animation-duration: 1.5s;
  		animation-name: tada;
  		animation-iteration-count: infinite;
	}
	#animar2{
		animation-duration: 1s;
  		animation-name: swing;
  		animation-iteration-count: infinite;
	}
	#animar3{
		animation-duration: 3s;
  		animation-name: pulse;
  		animation-iteration-count: infinite;
	}
	#page-wrapper{
		margin: 0 !important;
	}
	
</style>
<script>
$(document).ready(function() {
	setInterval(ObtenerDatos, <?php echo $Refresh;?>);
});

function ObtenerDatos(){
	$.ajax({
			url:"ajx_json.php",
			data:{
				type:1
			},
			dataType:'json',
			success: function(data){
				$("#FE_run1").html(data.FACTURAS_PENDIENTES);
				$("#FE_run2").html(data.FACTURAS_NOVEDADES);
				$("#FE_run3").html(data.NOTACREDITO_PENDIENTES);
				$("#FE_run4").html(data.NOTACREDITO_NOVEDADES);				
			}
		});
}
</script>
</head>

<body class="mini-navbar">

<div id="wrapper">

    <div id="page-wrapper" class="gray-bg">
        <div class="page-wrapper wrapper-content animated fadeInRight">
			<h2 class="bg-success p-xss b-r-xs"><i class="fa fa-line-chart"></i> Indicadores de facturación electrónica</h2>
				<div class="row">
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-success">Facturas pendientes</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-file-text fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="FE_run1">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-danger">Facturas con novedades</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-exclamation-triangle fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="FE_run2">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-success">Notas créditos pendientes</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-file-text fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="FE_run3">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-danger">Notas créditos con novedades</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-exclamation-triangle fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="FE_run4">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				</div>			
        </div>
    </div>
</div>
<?php include("includes/pie.php"); ?>
<script>
var amount=<?php echo $row_FactElect['FACTURAS_PENDIENTES'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#FE_run1").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_FactElect['FACTURAS_NOVEDADES'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#FE_run2").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_FactElect['NOTACREDITO_PENDIENTES'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#FE_run3").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_FactElect['NOTACREDITO_NOVEDADES'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#FE_run4").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
</script>
<script src="js/js_setcookie.js"></script>

</body>

</html>
<?php sqlsrv_close($conexion);?>