<?php 
$sw=0;//Para saber si se busco a un cliente.
$Result=0;//Para saber si hubo o no resultados de la busqueda.

//Filtros
$Where="";//Filtro
//$WhereClaves="";
if(isset($_POST['BuscarCliente'])&&$_POST['BuscarCliente']!=""){
	
	$Cliente=$_POST['BuscarCliente'];
	
	date_default_timezone_set('America/Bogota');
	$usuario='sa';
	$password='Asdf1234$';
	$servidor='(local)';
	$database='PortalOne';
	$connectionInfo = array( "UID"=>$usuario,"PWD"=>$password,"Database"=>$database,"CharacterSet" => "UTF-8");
	$conexion=sqlsrv_connect($servidor,$connectionInfo);
	if(!$conexion){
		echo "No es posible conectarse al servidor.</br>";
		$rs=error_get_last();
		echo $rs['message']."<br>";
		exit(print_r(sqlsrv_errors(), true));
	}
	
	$Consulta="Select * From uvw_Sap_tbl_FacturasPendientes Where NIT='".$Cliente."' Order by FechaContabilizacion";
	$SQL=sqlsrv_query($conexion,$Consulta,array(),array( "Scrollable" => 'Buffered' ));
	$Num=sqlsrv_num_rows($SQL);
	if($Num>0){
		$sw=1;
		$Result=1;
		function SumarFacturasPendientes($CodigoCliente){//Sumar el valor total de las facturas pendientes de un cliente
			global $conexion;
			$Con="Select SUM(SaldoDocumento) AS Total From uvw_Sap_tbl_FacturasPendientes Where NIT='".$CodigoCliente."'";
			$SQL=sqlsrv_query($conexion,$Con);
			$row=sqlsrv_fetch_array($SQL);
			return $row['Total'];
		}
		
	}else{
		$sw=1;
		$Result=0;
	}
	
}


?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Dialnet de Colombia | Pagar facturas</title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.text-orange{
		color:#F5821F;
	}
	.lazur-bg, .bg-orange {
		background-color: #F5821F;
		color:#ffffff;
	}
	.bg-orange{
		background-color: #F5821F;
		color:#ffffff;
	}
	.btn-orange{
		background-color: #F5821F;
		border-color:#F5821F;
		color:#FFFFFF;
	}
	.btn-orange:hover, .btn-orange:focus, .btn-orange:active, .btn-orange.active, .open .dropdown-toggle.btn-orange, .btn-orange:active:focus, .btn-orange:active:hover, .btn-orange.active:hover, .btn-orange.active:focus {
    	background-color: #E26E0A;
		border-color:#E26E0A;
		color:#FFFFFF;
	}
	.btn-gray{
		background-color: #888A8D;
		border-color:#888a8d;
		color:#FFFFFF;
	}
	.btn-gray:hover, .btn-gray:focus, .btn-gray:active, .btn-gray.active, .open .dropdown-toggle.btn-gray, .btn-gray:active:focus, .btn-gray:active:hover, .btn-gray.active:hover, .btn-gray.active:focus {
    	background-color: #626366;
		border-color:#626366;
		color:#FFFFFF;
	}
	.alert-orange {
		color: #DB6909;
		background-color:#FBCAA0;
		border-color:#F7A761;
	}
	
</style>
<!-- InstanceEndEditable -->
</head>

<body class="top-navigation">

<div id="wrapper">

    <div id="page-wrapper" class="gray-bg">
       <div class="row border-bottom white-bg">
			<nav class="navbar navbar-expand-lg navbar-static-top" role="navigation">
            <!--<div class="navbar-header">-->
                <!--<button aria-controls="navbar" aria-expanded="false" data-target="#navbar" data-toggle="collapse" class="navbar-toggle collapsed" type="button">-->
                    <!--<i class="fa fa-reorder"></i>-->
                <!--</button>-->

                <img src="img/img_logo_cliente.png" alt="Cliente" height="100" width="180" />

            <!--</div>-->
        	</nav>
		</div>
        <!-- InstanceBeginEditable name="Contenido" -->
		<div class="row wrapper border-bottom white-bg page-heading">
			<div class="col-lg-12">
				<h2>
					<div class="text-orange text-center"><strong>RESULTADO DE LA TRANSACCIÓN</strong></div>
				</h2>
			</div>
		</div>
		<div class="wrapper wrapper-content">
		<div class="row">
			<div class="col-lg-12">
				<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
					<form action="consultar_socios_negocios.php" method="get" id="formBuscar" class="form-horizontal">
						<div class="form-group">
							<h3>
								<div class="alert alert-success text-center"><strong>TRANSACCION APROBADA</strong></div>
							</h3>
						</div>
						<div class="table-responsive">
							<h3>
							<table class="table table-bordered">
							<thead>
								<tr class="text-orange">
									<th colspan="2" class="text-center">DIALNET DE COLOMBIA S.A. E.S.P<br>NIT: 819003851-6<br>CALLE 13 # 3 - 13 CENTRO COMERCIAL SAN FRANCISCO PLAZA</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="text-center"><strong>Fecha de la transacción:</strong> 2020-01-16</td>
									<td class="text-center"><strong>Número de la transacción:</strong> 0964758463758</td>
								</tr>
								<tr class="text-orange">
									<th colspan="2" class="text-center">DETALLE DE LA TRANSACCIÓN</th>
								</tr>
								<tr>
									<td class="text-right"><strong>Tipo de persona</strong></td>
									<td>NATURAL</td>
								</tr>
								<tr>
									<td class="text-right"><strong>Identificación del cliente</strong></td>
									<td>1.140.847.389</td>
								</tr>
								<tr>
									<td class="text-right"><strong>Nombre del cliente</strong></td>
									<td>PEDRO ALBERTO FUENTEZ MARTINEZ</td>
								</tr>
								<tr>
									<td class="text-right"><strong>Descripción de la transacción</strong></td>
									<td>PAGOS ACH PSE</td>
								</tr>
								<tr>
									<td class="text-right"><strong>Entidad bancaria</strong></td>
									<td>BANCOLOMBIA</td>
								</tr>
								<tr>
									<td class="text-right"><strong>Valor pagado</strong></td>
									<td>$107.000</td>
								</tr>
								<tr>
									<td class="text-right"><strong>Estado aprobación</strong></td>
									<td>Aprobado</td>
								</tr>
								<tr>
									<td class="text-right"><strong>IP donde se realizó la transacción</strong></td>
									<td>190.242.110.118</td>
								</tr>
								<tr>
									<td class="text-right"><strong>CUS</strong></td>
									<td>489999517</td>
								</tr>
								<tr>
									<td colspan="2" class="text-center">*Esta transacción está sujeta a verificación</td>
								</tr>
								<tr>
									<td colspan="2" class="text-center">Si requiere más información acerca de la transacción, por favor comunicarse al número de teléfono: <strong>01 8000 510947</strong></td>
								</tr>
							</tbody>
							</table>
							<div class="col-lg-11 text-center">
								<div class="form-group">
									<button class="btn btn-lg btn-orange btn-rounded" type="button" id="Finalizar" onClick="javascript:location.href='pago_facturas_pse.php'"><i class="fa fa-check"></i> Finalizar</button>
									<button class="btn btn-lg btn-gray btn-rounded" type="button" id="Imprimir" onclick="window.print();"><i class="fa fa-print"></i> Imprimir</button>
								</div>							
							</div>
							</h3>
						</div>
					</form>
				</div>
			</div>
		</div>
		</div>
        <!-- InstanceEndEditable -->
       <div class="footer">
			<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
				<strong>Todos los derechos reservados</strong> &copy; 2020 DIALNET DE COLOMBIA S.A.S E.S.P. <a href="politica_privacidad.php">Pol&iacute;tica de privacidad</a> 
			</div>
			<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 text-right pull-right"></div>
		</div>

    </div>
</div>
<?php //include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
	$(document).ready(function(){
		 $("#formBuscar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
			}
		});
		$(".btn-link").on('click', function(){
			$('.ibox-content').toggleClass('sk-loading');
		});
		 $('.i-checks').iCheck({
				checkboxClass: 'icheckbox_square-green',
				radioClass: 'iradio_square-green',
			});

	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php if($sw==1){sqlsrv_close($conexion);}?>