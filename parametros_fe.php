<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
if(PermitirAcceso(1501))

$sw_ext=0;//Sw que permite saber si la ventana esta abierta en modo pop-up. Si es así, no cargo el menú ni el menú superior.
$sw_error=0;//Sw para saber si ha ocurrido un error al crear o actualizar un articulo.
$edit=1;

if(isset($_GET['id'])&&($_GET['id']!="")){
	$IdItemCode=base64_decode($_GET['id']);
}
	
if(isset($_GET['ext'])&&($_GET['ext']==1)){
	$sw_ext=1;//Se está abriendo como pop-up
}elseif(isset($_POST['ext'])&&($_POST['ext']==1)){
	$sw_ext=1;//Se está abriendo como pop-up
}else{
	$sw_ext=0;
}

if(isset($_POST['P'])&&($_POST['P']!="")){//Insertar o actualizar articulo
	try{
		$Type=2;//Ejecutar actualizar en el SP
		
		if($_POST['ID']==""){
			$Type=1;
			$_POST['ID']=0;
		}
		
		if(isset($_POST['chkEnvioComprobantes'])&&($_POST['chkEnvioComprobantes']==1)){
			$chkEnvioComprobantes=1;
		}else{
			$chkEnvioComprobantes=0;
		}
					
		$ParamFE=array(
			$_POST['ID'],
			"'".$_POST['ProvTecnologico']."'",
			"'".$_POST['CodPais']."'",
			$_POST['TipoAmbiente'],
			$_POST['TipoEsquema'],			
			"'".$_POST['CorreoDefault']."'",
			"'".$_POST['EnviarNotaCredito']."'",
			"'".$_POST['EnviarNotaDebito']."'",
			"'".$_POST['BaseDeDatos']."'",
			"'".$_POST['EstadoServicioAddon']."'",			
			"'".$_POST['RutaPrueba']."'",
			"'".$_POST['RutaProd']."'",
			"'".$_POST['RutaArchivos']."'",
			$chkEnvioComprobantes,
			"'".$_POST['URLServicio']."'",
			"'".$_POST['Usuario']."'",
			"'".$_POST['Password']."'",
			"'".$_POST['Contrato']."'",
			"'".$_POST['XWho']."'",
			"'".$_POST['VerificarPersona']."'",
			"'".$_POST['ExcluirDescuento']."'",
			"'".$_POST['EnviarRepGrafica']."'",
			"'".$_POST['EnviarAdjuntos']."'",
			$_SESSION['CodUser'],
			$Type
		);
		$SQL_FE=EjecutarSP('sp_tbl_FacturacionElectronica_Parametros',$ParamFE,$_POST['P']);
		if($SQL_FE){
			header('Location:parametros_fe.php?a='.base64_encode("OK_FEUpd"));
		}else{
			$sw_error=1;
			$msg_error="Error al actualizar la información";
		}						
	}catch (Exception $e) {
		$sw_error=1;
		//echo 'Excepcion capturada 2: ',  $e->getMessage(), "\n";
	}	
}

if($edit==1){//Editar parametros	

	//Datos
	$SQL=Seleccionar('uvw_tbl_FacturacionElectronica_Parametros','*');
	$row=sql_fetch_array($SQL);
	if(!isset($row['BaseDatos'])){
		$edit=0;
	}
		
}
if($sw_error==1){//Si ocurre un error
	
	//Datos
	$SQL=Seleccionar('uvw_tbl_FacturacionElectronica_Parametros','*');
	$row=sql_fetch_array($SQL);
	if(!isset($row['BaseDatos'])){
		$edit=0;
	}
		
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Parámetros Facturación Electrónica | <?php echo NOMBRE_PORTAL;?></title>
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_FEUpd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Datos actualizados exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Ha ocurrido un error!',
                text: '".LSiqmlObs($msg_error)."',
                icon: 'warning'
            });
		});		
		</script>";
}
?>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
.select2-container{ width: 100% !important; }
</style>
<script>
function Mostrar(){
	var x = document.getElementById("Password").getAttribute("type");
	if(x=="password"){
		document.getElementById('Password').setAttribute('type','text');
		document.getElementById('VerPass').setAttribute('class','glyphicon glyphicon-eye-close');
		document.getElementById('aVerPass').setAttribute('title','Ocultar contrase'+String.fromCharCode(241)+'a');
	}else{
		document.getElementById('Password').setAttribute('type','password');
		document.getElementById('VerPass').setAttribute('class','glyphicon glyphicon-eye-open');
		document.getElementById('aVerPass').setAttribute('title','Mostrar contrase'+String.fromCharCode(241)+'a');
	}	
}
</script>
<!-- InstanceEndEditable -->
</head>

<body <?php if($sw_ext==1){echo "class='mini-navbar'"; }?>>

<div id="wrapper">

    <?php if($sw_ext!=1){include("includes/menu.php"); }?>

    <div id="page-wrapper" class="gray-bg">
        <?php if($sw_ext!=1){include("includes/menu_superior.php"); }?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Parámetros Facturación Electrónica</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administración</a>
                        </li>
						<li>
                            <a href="#">Parámetros del sistema</a>
                        </li>
                        <li class="active">
                            <strong>Parámetros Facturación Electrónica</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <form action="parametros_fe.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="FrmFE">
			 <div class="row">
				<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<div class="col-lg-4">
								<?php 
									$return="index1.php";
								?>
								<button class="btn btn-warning" type="submit" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar</button>
								<a href="<?php echo $return;?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
							</div>
							<div class="col-lg-4"></div>
							<div class="col-lg-2">
								<div class="form-group border">
									<div class="p-xs">
										<label class="text-muted">Última actualización</label>
										<div class="font-bold"><?php if($edit==1){echo $row['NombreUsuarioActualizacion'];}?></div>
									</div>
								</div>
							</div>
							<div class="col-lg-2">
								<div class="form-group border">
									<div class="p-xs">
										<label class="text-muted">Fecha</label>
										<div class="font-bold">
											<?php 
											if($edit==1){
												if(is_object($row['FechaActualizacion'])){
													echo $row['FechaActualizacion']->format('Y-m-d');
												}else{
													echo $row['FechaActualizacion'];
												}
											}?></div>
									</div>
								</div>
							</div>
						</div>
						<input type="hidden" id="P" name="P" value="56" />
						<input type="hidden" id="ID" name="ID" value="<?php if($edit==1){echo $row['ID'];}?>" />
						<input type="hidden" id="return" name="return" value="<?php echo base64_encode($return);?>" />
					</div>
				</div>
			 </div>
			 <br>
			 <div class="row">
			 	<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						 <div class="tabs-container">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#tabSN-1"><i class="fa fa-database"></i> Parámetros</a></li>
								<li><a data-toggle="tab" href="#tabSN-2"><i class="fa fa-user"></i> Proveedor tecnológico</a></li>
							</ul>
						   <div class="tab-content">
							   <div id="tabSN-1" class="tab-pane active">
								   <br>
								    <div class="form-group">
										<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-cog"></i> Configuración general</h3></label>
									</div>
								    <div class="form-group">
										<label class="col-lg-1 control-label">Base de datos</label>
										<div class="col-lg-3">
											<select name="BaseDeDatos" class="form-control" id="BaseDeDatos" required>
												<option value="SQL" <?php if(($edit==1)&&($row['BaseDatos']=="SQL")){ echo "selected=\"selected\"";}?>>SQL Server</option>
												<option value="HANA" <?php if(($edit==1)&&($row['BaseDatos']=="HANA")){ echo "selected=\"selected\"";}?>>SAP HANA</option>
											</select>
										</div>
										<label class="col-lg-1 control-label">Estado servicio Addon</label>
										<div class="col-lg-3">
											<select name="EstadoServicioAddon" class="form-control" id="EstadoServicioAddon" required>
												<option value="1" <?php if(($edit==1)&&($row['EstadoServicioAddon']=="1")){ echo "selected=\"selected\"";}?>>Activo</option>
												<option value="0" <?php if(($edit==1)&&($row['EstadoServicioAddon']=="0")){ echo "selected=\"selected\"";}?>>Inactivo</option>
											</select>
										</div>
										<div class="col-lg-3">
											<?php if(($edit==1)&&($row['EstadoServicioAddon']==1)){?>
											<div class="sk-spinner sk-spinner-circle pull-left">
												<div class="sk-circle1 sk-circle"></div>
												<div class="sk-circle2 sk-circle"></div>
												<div class="sk-circle3 sk-circle"></div>
												<div class="sk-circle4 sk-circle"></div>
												<div class="sk-circle5 sk-circle"></div>
												<div class="sk-circle6 sk-circle"></div>
												<div class="sk-circle7 sk-circle"></div>
												<div class="sk-circle8 sk-circle"></div>
												<div class="sk-circle9 sk-circle"></div>
												<div class="sk-circle10 sk-circle"></div>
												<div class="sk-circle11 sk-circle"></div>
												<div class="sk-circle12 sk-circle"></div>
											</div>
											<h3 class="text-info font-bold m-l-lg">Servicio activo</h3>
											<?php }else{?>
											<div class="sk-spinner sk-spinner-double-bounce pull-left">
												<div class="sk-double-bounce1-danger"></div>
												<div class="sk-double-bounce2-danger"></div>
											</div>
											<h3 class="text-danger font-bold m-l-xl">Servicio detenido</h3>
											<?php }?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Código pais</label>
										<div class="col-lg-3">
											<input name="CodPais" autofocus="autofocus" type="text" required class="form-control" id="CodPais" value="<?php if($edit==1){echo $row['CodPais'];}?>">
										</div>
										<label class="col-lg-1 control-label">Tipo ambiente</label>
										<div class="col-lg-3">
											<select name="TipoAmbiente" class="form-control" id="TipoAmbiente" required>
												<option value="1" <?php if(($edit==1)&&($row['TipoAmbiente']=="1")){ echo "selected=\"selected\"";}?>>Producción</option>
												<option value="2" <?php if(($edit==1)&&($row['TipoAmbiente']=="2")){ echo "selected=\"selected\"";}?>>Pruebas</option>
											</select>
										</div>
										<label class="col-lg-1 control-label">Tipo esquema</label>
										<div class="col-lg-3">
											<select name="TipoEsquema" class="form-control" id="TipoEsquema" required>
												<option value="1" <?php if(($edit==1)&&($row['TipoEsquema']=="1")){ echo "selected=\"selected\"";}?>>Online</option>
												<option value="2" <?php if(($edit==1)&&($row['TipoEsquema']=="2")){ echo "selected=\"selected\"";}?>>Offline</option>
											</select>
										</div>
									</div>
								    <div class="form-group">
										<label class="col-lg-1 control-label">Correo por defecto</label>
										<div class="col-lg-3">
											<input name="CorreoDefault" type="email" required class="form-control" id="CorreoDefault" value="<?php if($edit==1){echo $row['CorreoDefault'];}?>">
										</div>
										<label class="col-lg-1 control-label">Enviar Nota crédito a cliente</label>
										<div class="col-lg-3">
											<select name="EnviarNotaCredito" class="form-control" id="EnviarNotaCredito" required>
												<option value="SI" <?php if(($edit==1)&&($row['EnviarNotaCredito']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
												<option value="NO" <?php if(($edit==1)&&($row['EnviarNotaCredito']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
											</select>
										</div>
										<label class="col-lg-1 control-label">Enviar Nota débito a cliente</label>
										<div class="col-lg-3">
											<select name="EnviarNotaDebito" class="form-control" id="EnviarNotaDebito" required>
												<option value="SI" <?php if(($edit==1)&&($row['EnviarNotaDebito']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
												<option value="NO" <?php if(($edit==1)&&($row['EnviarNotaDebito']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
											</select>
										</div>
									</div>
								   <div class="form-group">
										<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-files-o"></i> Directorio de documentos</h3></label>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Ruta de prueba</label>
										<div class="col-lg-5">
											<input type="text" class="form-control" name="RutaPrueba" id="RutaPrueba" required value="<?php if($edit==1){ echo $row['RutaPrueba'];}?>">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Ruta de producción</label>
										<div class="col-lg-5">
											<input type="text" class="form-control" name="RutaProd" id="RutaProd" value="<?php if($edit==1){echo $row['RutaProd'];}?>">
										</div>
									</div>
								   <div class="form-group">
										<label class="col-lg-1 control-label">Ruta de archivos FE y XML</label>
										<div class="col-lg-5">
											<input type="text" class="form-control" name="RutaArchivos" id="RutaArchivos" value="<?php if($edit==1){echo $row['RutaArchivos'];}?>">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Comprobantes</label>
										<div class="col-lg-6">
											<label class="checkbox-inline i-checks"><input name="chkEnvioComprobantes" id="chkEnvioComprobantes" type="checkbox" value="1" <?php if($edit==1){if($row['EnvioComprobantes']==1){echo "checked=\"checked\"";}}?>> Generar comprobantes electrónicos automáticamente</label>
										</div>
									</div>
							   </div>
							   <div id="tabSN-2" class="tab-pane">
								<div class="panel-body">
									 <div class="form-group">
										<label class="col-lg-1 control-label">Proveedor técnologico</label>
										<div class="col-lg-3">
											<select name="ProvTecnologico" class="form-control" id="ProvTecnologico" required>
												<option value="Facture">Facture SAS</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-key"></i> Datos de la conexión</h3></label>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">URL servicio</label>
										<div class="col-lg-5">
											<input type="text" class="form-control" name="URLServicio" id="URLServicio" value="<?php if($edit==1){echo $row['URLServicio'];}?>">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Usuario</label>
										<div class="col-lg-2">
											<input type="text" class="form-control" name="Usuario" id="Usuario" value="<?php if($edit==1){echo $row['Usuario'];}?>">
										</div>
										<label class="col-lg-1 control-label">Contraseña</label>
										<div class="col-lg-2">
											<input type="password" class="form-control" name="Password" id="Password" value="<?php if($edit==1){echo $row['Password'];}?>">
											<a href="#" id="aVerPass" onClick="javascript:Mostrar();" title="Mostrar contrase&ntilde;a" class="btn btn-default btn-xs"><span id="VerPass" class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Contrato</label>
										<div class="col-lg-2">
											<input type="text" class="form-control" name="Contrato" id="Contrato" value="<?php if($edit==1){echo $row['Contrato'];}?>">
										</div>
										<label class="col-lg-1 control-label">XWho</label>
										<div class="col-lg-2">
											<input type="text" class="form-control" name="XWho" id="XWho" value="<?php if($edit==1){echo $row['XWho'];}?>">
										</div>
									</div>
									<div class="form-group">
										<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-gears"></i> Parámetros adicionales</h3></label>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Verificar persona de contacto</label>
										<div class="col-lg-2">
											<select name="VerificarPersona" class="form-control" id="VerificarPersona" required>
												<option value="SI" <?php if(($edit==1)&&($row['VerificarPersona']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
												<option value="NO" <?php if(($edit==1)&&($row['VerificarPersona']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
											</select>
										</div>
										<label class="col-lg-1 control-label">Excluir descuento linea</label>
										<div class="col-lg-2">
											<select name="ExcluirDescuento" class="form-control" id="ExcluirDescuento" required>
												<option value="SI" <?php if(($edit==1)&&($row['ExcluirDescuento']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
												<option value="NO" <?php if(($edit==1)&&($row['ExcluirDescuento']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Enviar representación gráfica</label>
										<div class="col-lg-2">
											<select name="EnviarRepGrafica" class="form-control" id="EnviarRepGrafica" required>
												<option value="SI" <?php if(($edit==1)&&($row['EnviarRepGrafica']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
												<option value="NO" <?php if(($edit==1)&&($row['EnviarRepGrafica']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
											</select>
										</div>
										<label class="col-lg-1 control-label">Enviar adjuntos</label>
										<div class="col-lg-2">
											<select name="EnviarAdjuntos" class="form-control" id="EnviarAdjuntos" required>
												<option value="SI" <?php if(($edit==1)&&($row['EnviarAdjuntos']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
												<option value="NO" <?php if(($edit==1)&&($row['EnviarAdjuntos']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
											</select>
										</div>
									</div>
								</div>										   
							   </div>							  
						   </div>
						 </div>
					</div>
          		</div>
			 </div>
			 <br>
			 <div class="row">
			 	<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						 <div class="tabs-container">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#tabSerie-1"><i class="fa fa-list"></i> Series</a></li>
								<li><span class="TimeAct"><div id="TimeAct">&nbsp;</div></span></li>
								<span class="TotalItems"><strong>Total Items:</strong>&nbsp;<input type="text" name="TotalItems" id="TotalItems" class="txtLimpio" value="0" size="1" readonly></span>
							</ul>
						   <div class="tab-content">
							   <div id="tabSerie-1" class="tab-pane active">
								  <iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="500" src="detalle_parametros_fe.php"></iframe>	
							   </div>					  
						   </div>
						 </div>
					</div>
          		</div>
			 </div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
 $(document).ready(function(){
	 $("#FrmFE ").validate({
		 submitHandler: function(form){
			Swal.fire({
				title: "¿Está seguro que desea guardar los datos?",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Si, confirmo",
				cancelButtonText: "No"
			}).then((result) => {
				if (result.isConfirmed) {
					$('.ibox-content').toggleClass('sk-loading',true);
					form.submit();
				}
			});
		}
	});
   $(".alkin").on('click', function(){
	   $('.ibox-content').toggleClass('sk-loading');
	});
	 
	$(".select2").select2();
	 
 	$('.i-checks').iCheck({
		checkboxClass: 'icheckbox_square-green',
		radioClass: 'iradio_square-green',
	});
	 
 });
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>