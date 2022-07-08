<?php 
$sw=0;//Para saber si se busco a un cliente.
$Result=0;//Para saber si hubo o no resultados de la busqueda.

//Filtros
$Where="";//Filtro
//$WhereClaves="";
if(isset($_POST['BuscarCliente'])&&$_POST['BuscarCliente']!=""){
	
	$Cliente=$_POST['BuscarCliente'];
	
	$_POST['BaseDatos']="PortalOne";
	
	require("includes/conect_srv.php");
	
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
	.alert-orange {
		color: #DB6909;
		background-color:#FBCAA0;
		border-color:#F7A761;
	}
</style>
<script>
function SeleccionarFactura(Num, ValFact){
	var div=document.getElementById("dv_MediosPagos");
	var FactSel=document.getElementById("FactSel");
	var ValorTotal=document.getElementById("ValorTotal");
	var VTotal=document.getElementById("VTotal");
	
	var Fac=FactSel.value.indexOf(Num);	
	var Total=parseFloat(ValorTotal.value.replace(/,/g, ''));
	
	if(Fac<0){
		FactSel.value=FactSel.value + Num + "[*]";
		Total=parseFloat(Total)+parseFloat(ValFact);
		ValorTotal.value=Total;
		VTotal.innerHTML=number_format(Total,0);
	}else{
		var tmp=FactSel.value.replace(Num+"[*]","");
		FactSel.value=tmp;
		Total=parseFloat(Total)-parseFloat(ValFact);
		ValorTotal.value=Total;
		VTotal.innerHTML=number_format(Total,0);
	}
	
	if(FactSel.value==""){
		div.style.display='none';
	}else{
		div.style.display='';
	}
}	
</script>
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
					<strong class="text-orange">Paga tus servicios hogar y corporativo</strong><br>
					<small>Selecciona la opción con la que deseas consultar y pagar:</small>
				</h2>
			</div>
		</div>
		<div class="wrapper wrapper-content">
		<div class="row">
			<div class="col-lg-12">
				<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
					<form action="pago_facturas_pse.php" method="post" id="formBuscar" class="form-horizontal">
						<div class="form-group">
							<div class="col-lg-5">
								<div class="i-checks">
									<h2>
										<input name="TipoCliente" type="radio" id="TipoCliente1" value="PNatural" checked="checked"> Persona natural 
										<input name="TipoCliente" type="radio" id="TipoCliente2" value="Empresas"> Empresa 
									</h2>
								</div>
							</div>
						</div>
						<h3>
						<div class="form-group">
							<div class="col-lg-3">
								<input name="BuscarCliente" type="text" autocomplete="off" class="form-control" id="BuscarCliente" maxlength="20" placeholder="Consulte el número del documento" value="<?php if(isset($_POST['BuscarCliente'])&&($_POST['BuscarCliente']!="")){ echo $_POST['BuscarCliente'];}?>">
							</div>
							<div class="col-lg-1">
								<button type="submit" class="btn btn-outline btn-orange"><i class="fa fa-search"></i> Consultar</button>
							</div>
						</div>
						</h3>
					</form>
				</div>
			</div>
		</div>
      	<?php if($sw==1&&$Result==1){?>
		<br>
		<div class="row">
			<div class="col-lg-12">
				<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
					<form action="consultar_socios_negocios.php" method="get" id="formBuscar" class="form-horizontal">
						<div class="form-group">
							<div class="col-lg-12">
								<h3><div class="bg-orange p-sm b-r-xl"><i class="fa fa-info-circle"></i> RESUMEN DE TU TRANSACCIÓN</div></h3>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-12">
								<?php $Total=SumarFacturasPendientes($Cliente);?>
								<h2 class="text-center"><strong>Valor a pagar: </strong>$<span id="VTotal"><?php echo number_format($Total,0);?></span></h2>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-12">
								<h3><div class="text-orange"><strong>DETALLES DE LAS FACTURAS</strong></div></h3>
							</div>
						</div>
						<div class="table-responsive">
							<h3>
							<table class="table table-hover">
							<thead>
								<tr>
									<th>#</th>
									<th>Factura</th>
									<th>Fecha limite de pago</th>
									<th>Referencia de pago</th>
									<th>Saldo pendiente</th>
									<th>Seleccionar para pago</th>
								</tr>
							</thead>
							<tbody>
								<?php $i=1;
									 $Facts=array();
									while($row=sqlsrv_fetch_array($SQL)){ ?>
									 <tr>
										<td><?php echo $i;?></td>
										<td><?php echo $row['NoDocumento'];?></td>
										<td><?php if($row['FechaVencimiento']->format('Y-m-d')){echo $row['FechaVencimiento']->format('Y-m-d');}else{echo $row['FechaVencimiento'];}?></td>
										<td></td>
										<td><?php echo "$".number_format($row['SaldoDocumento'],2);?></td>
										<td><div class="checkbox checkbox-success"><input type="checkbox" id="singleCheckbox<?php echo $row['NoDocumento'];?>" onChange="SeleccionarFactura('<?php echo $row['NoDocumento'];?>','<?php echo $row['SaldoDocumento'];?>');" value="" checked="checked" aria-label="Single checkbox One"><label></label></div></td>
									</tr>
								<?php array_push($Facts, $row['NoDocumento']);
									$i++; }?>
							</tbody>
							</table>
							</h3>
						</div>
						<?php $Facturas = implode("[*]",$Facts);?>
						<input type="hidden" id="FactSel" name="FactSel" value="<?php echo $Facturas."[*]"; ?>" />
						<input type="hidden" id="ValorTotal" name="ValorTotal" value="<?php echo $Total; ?>" />
					</form>
				</div>
			</div>
		</div>
		<br>
		<div class="row" id="dv_MediosPagos">
			<div class="col-lg-12">
				<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
					<h3><strong class="text-orange">SELECCIONA UN MEDIO DE PAGO</strong></h3>
					<div class="panel-body">
						<div class="panel-group payments-method" id="accordion">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h3>
										<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
											<span class="text-orange">DÉBITO BANCARIO PSE</span>
											<img src="img/logo_pse_40.png"  class="no-paddings pull-right" /> 
											<br>
											<span class="text-primary">Pago desde tu cuenta de ahorros </span>
										</a>
									</h3>
								</div>
								<div id="collapseOne" class="panel-collapse collapse">
									<div class="panel-body">
										<form action="" method="post" id="frmPSE" class="ibox-content">
											<h3>
											<div class="form-group">
											<div class="col-lg-12">
											<div class="row">
												<div class="col-lg-3">
													<div class="form-group">
														<label class="col-form-label">Banco</label>
															<select name="Banco" class="form-control m-b select2" id="Banco" required>
																<option value="">Seleccione...</option>
																<option value="">BANCOCOLOMBIA</option>
																<option value="">BANCO DAVIVIENDA</option>
																<option value="">BANCO DE BOGOTA</option>
																<option value="">BANCO POPULAR</option>
															</select>
													</div>							
												</div>
												<div class="col-lg-2">
													<div class="form-group">							
														<label class="col-form-label">Tipo de persona</label>
														<select name="TipoPersona" class="form-control m-b select2" id="TipoPersona" required>
															<option value="">NATURAL</option>
															<option value="">EMPRESA</option>
														</select>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-3">
													<div class="form-group">
														<label class="col-form-label">Nombre completo</label>
															<input type="text" class="form-control" placeholder="Nombre completo">
													</div>							
												</div>
												<div class="col-lg-4">
													<div class="form-group">
														<div class="col-lg-12">
														<label class="col-form-label">Número de documento</label>
														</div>
														<div class="col-lg-3">
															<select name="TipoDoc" class="form-control m-b select2" id="TipoDoc" required>
																<option value="">CC</option>
																<option value="">CE</option>
															</select>
														</div>
														<div class="col-lg-9">
															<input type="text" class="form-control" placeholder="Número de documento">
														</div>														
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-3">
													<div class="form-group">
														<label class="col-form-label">Correo electrónico</label>
															<input type="text" class="form-control" placeholder="Correo electrónico">
													</div>							
												</div>
											</div>
											<div class="row">
												<div class="col-lg-11">
													<div class="form-group">
														<button class="btn btn-lg btn-orange btn-rounded pull-right" type="submit" id="Pagar">PAGAR</button>
													</div>							
												</div>
											</div>
											</div>
											</div>
											</h3>
					</form>
									</div>
								</div>
							</div>
							<div class="panel panel-default">
								<div class="panel-heading">
									<h3>
										<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
											<span class="text-orange">TARJETA DE CRÉDITO</span>	
											<i class="fa fa-credit-card text-warning pull-right"></i>
											<br>
											<span class="text-primary">Tu escoges el número de cuotas</span>
										</a>
									</h3>
								</div>
								<div id="collapseTwo" class="panel-collapse collapse">
									<div class="panel-body">
										<form action="" method="post" id="frmPSE" class="ibox-content">
											<h3>
											<div class="form-group">
											<div class="col-lg-12">
											<div class="row">
												<div class="col-lg-3">
													<div class="form-group">
														<label class="col-form-label">Número de la tarjeta</label>
															<input type="text" class="form-control" placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="19" data-mask="9999-9999-9999-9999">
													</div>							
												</div>
												<div class="col-lg-2">
													<div class="form-group">
														<label class="col-form-label">Fecha de expiración</label>
															<input type="text" class="form-control" placeholder="MM/AA" data-mask="99/99">
													</div>							
												</div>
												<div class="col-lg-2">
													<div class="form-group tooltip-demo">
														<label class="col-form-label">Código CCV <i data-toggle="tooltip" data-placement="top" title="El código de seguridad de tu tarjeta (CCV) es el número de 3 o 4 dígitos que se encuentra en el reverso de la mayoría de las tarjetas." class="fa fa-question-circle"></i></label>
															<input type="text" class="form-control" placeholder="CCV" maxlength="4">
													</div>							
												</div>
											</div>
											<div class="row">
												<div class="col-lg-3">
													<div class="form-group">
														<label class="col-form-label">Nombre del titular</label>
															<input type="text" class="form-control" placeholder="Nombre completo">
													</div>							
												</div>
												<div class="col-lg-2">
													<div class="form-group">							
														<label class="col-form-label">Número de cuotas</label>
														<select name="Cuotas" class="form-control m-b" id="Cuotas" required>
															<option value="">1</option>
															<option value="">2</option>
															<option value="">3</option>
															<option value="">4</option>
															<option value="">5</option>
															<option value="">6</option>
															<option value="">7</option>
															<option value="">8</option>
															<option value="">9</option>
														</select>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-11">
													<div class="form-group">
														<button class="btn btn-lg btn-orange btn-rounded pull-right" type="submit" id="Pagar">PAGAR</button>
													</div>							
												</div>
											</div>
											</div>
											</div>
											</h3>
										</form>
									</div>
								</div>
							</div>
						</div>
                    </div>
				</div>
			</div>
		</div>
		<?php }elseif($sw==1&&$Result==0){?>
		<br>
		<div class="row">
			<div class="col-lg-12">
				<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
					<h3>
						<div class="alert alert-orange"><i class="fa fa-info-circle"></i> Actualmente no tienes facturas pendientes por pagar</div>
					</h3>					
				</div>
			</div>
		</div>
		<?php }?>
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