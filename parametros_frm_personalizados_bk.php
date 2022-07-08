<?php 
require_once("includes/conexion.php");
PermitirAcceso(216);

$SQL_BodegasPuerto=Seleccionar("tbl_BodegasPuerto","*");

$SQL_Productos=Seleccionar("tbl_ProductosPuerto","*");

$SQL_Transporte=Seleccionar("tbl_TransportesPuerto","*");

$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Clientes','CodigoCliente, NombreCliente','','NombreCliente');

//Creacion de OT
//$SQL_CreacionOT=Seleccionar("tbl_Parametros_Asistentes","*","TipoAsistente=2");

//OT de mantenimiento
//$SQL_MantOT=Seleccionar("tbl_Parametros_Asistentes","*","TipoAsistente=3");

$sw_error=0;
$dir_new=CrearObtenerDirAnx("formularios/monitoreos_temperaturas/planos");
//Insertar datos
if(isset($_POST['frmType'])&&($_POST['frmType']!="")){
	try{
		if($_POST['frmType']==1){//Guardar o actualizar conceptos
			
			//Bodegas
			$i=0;
			$Cuenta=count($_POST['CodigoBodega']);
			while($i<$Cuenta){
				if($_POST['CodigoBodega'][$i]!=""&&$_POST['Bodega'][$i]!=""&&$_POST['MetodoBodega'][$i]!="0"){
					
					//Anexos
					if($_FILES['AnexoBodega']['tmp_name'][$i]!=""){
						if(is_uploaded_file($_FILES['AnexoBodega']['tmp_name'][$i])){
							$Nombre_Archivo=$_FILES['AnexoBodega']['name'][$i];
							$NuevoNombre=FormatoNombreAnexo($Nombre_Archivo);
							if(!move_uploaded_file($_FILES['AnexoBodega']['tmp_name'][$i],$dir_new.$NuevoNombre[0])){
								$sw_error=1;
								$msg_error="No se pudo mover el anexo a la carpeta de anexos local";
							}
						}else{
							$sw_error=1;
							$msg_error="No se pudo cargar el anexo";
						}
					}else{
						$NuevoNombre[0]="";
					}
					
					$Param=array(
						"Bodegas",
						"'".$_POST['CodigoBodega'][$i]."'",
						"'".$_POST['ID_Bodega'][$i]."'",
						"'".$_POST['Bodega'][$i]."'",
						"'".$_POST['ComentariosBodega'][$i]."'",
						"'".$_POST['EstadoBodega'][$i]."'",
						"'".$_POST['MetodoBodega'][$i]."'",
						"'".$_SESSION['CodUser']."'",
						"'".$_POST['ClienteBodega'][$i]."'",
						"'".$_POST['SucursalBodega'][$i]."'",
						"'".$NuevoNombre[0]."'"
					);
					$SQL=EjecutarSP('sp_tbl_FrmPuerto',$Param);
					if(!$SQL){
						$sw_error=1;
						$msg_error="No se pudo insertar los datos";
					}
				}
				$i++;
			}
			
			//Productos
			$i=0;
			$Cuenta=count($_POST['CodigoProducto']);
			while($i<$Cuenta){
				if($_POST['CodigoProducto'][$i]!=""&&$_POST['Producto'][$i]!=""&&$_POST['MetodoProducto'][$i]!="0"){
					$Param=array(
						"Productos",
						"'".$_POST['CodigoProducto'][$i]."'",
						"'".$_POST['ID_Producto'][$i]."'",
						"'".$_POST['Producto'][$i]."'",
						"'".$_POST['ComentariosProducto'][$i]."'",
						"'".$_POST['EstadoProducto'][$i]."'",
						"'".$_POST['MetodoProducto'][$i]."'",
						"'".$_SESSION['CodUser']."'"
					);
					$SQL=EjecutarSP('sp_tbl_FrmPuerto',$Param);
					if(!$SQL){
						$sw_error=1;
						$msg_error="No se pudo insertar los datos";
					}
				}
				$i++;
			}
			
			//Transportes
			$i=0;
			$Cuenta=count($_POST['CodigoTransporte']);
			while($i<$Cuenta){
				if($_POST['CodigoTransporte'][$i]!=""&&$_POST['Transporte'][$i]!=""&&$_POST['MetodoTransporte'][$i]!="0"){
					$Param=array(
						"Transportes",
						"'".$_POST['CodigoTransporte'][$i]."'",
						"'".$_POST['ID_Transporte'][$i]."'",
						"'".$_POST['Transporte'][$i]."'",
						"'".$_POST['ComentariosTransporte'][$i]."'",
						"'".$_POST['EstadoTransporte'][$i]."'",
						"'".$_POST['MetodoTransporte'][$i]."'",
						"'".$_SESSION['CodUser']."'",
						"'".$_POST['Reg'][$i]."'",
					);
					$SQL=EjecutarSP('sp_tbl_FrmPuerto',$Param);
					if(!$SQL){
						$sw_error=1;
						$msg_error="No se pudo insertar los datos";
					}
				}
				$i++;
			}			
		
		}
		
		if($sw_error==0){
			header('Location:parametros_frm_personalizados.php?a='.base64_encode("OK_PRUpd"));
		}
	}catch (Exception $e) {
		$sw_error=1;
		$msg_error=$e->getMessage();
	}	
	
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Parámetros de formularios personalizados | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.ibox-title a{
		color: inherit !important;
	}
	.collapse-link:hover{
		cursor: pointer;
	}
	textarea.form-control{
		height: 28px;
	}
	.form-control{
		height: 28px;
	}
	.table > tbody > tr > td{
		padding: 1px !important;
		vertical-align: middle;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control {
		background-color: #fff;
		opacity: 1;
	}
</style>
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_PRUpd"))){
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
<script>

</script>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Parámetros de formularios personalizados</h2>
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
                            <strong>Parámetros de formularios personalizados</strong>
                        </li>
                    </ol>
                </div>
            </div>
            <?php  //echo $Cons;?>
         <div class="wrapper wrapper-content">
			 <div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" id="ContenidoModal">
						
					</div>
				</div>
			</div>
			 <div class="row">
			 	<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						 <div class="tabs-container">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Monitoreo de temperatura - Puerto</a></li>
							</ul>
							<div class="tab-content">
								<div id="tab-1" class="tab-pane active">
									<form action="parametros_frm_personalizados.php" method="post" id="frmParam" class="form-horizontal" enctype="multipart/form-data">	
									<br>
										<div class="form-group">
											<div class="col-lg-2">
												<button class="btn btn-primary" type="submit" id="Guardar"><i class="fa fa-check"></i> Guardar datos</button>  
											</div>
										</div>	
										<div class="ibox">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Bodegas</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>	
											</div>
											<div class="ibox-content">
												<div class="table-responsive">
													<table width="100%" class="table table-bordered">
														<thead>
															<tr>
																<th>Código bodega</th>
																<th>Nombre bodega</th>
																<th>Comentarios</th>
																<th>Nombre cliente</th>
																<th>Sucursal cliente</th>
																<th>Estado</th>
																<th>Anexo</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
															 <?php  
															$Cont=1;
															$row_BodegasPuerto=sqlsrv_fetch_array($SQL_BodegasPuerto);
															do{
															?>
															<tr id="divBodegasPuerto_<?php echo $Cont;?>">
																<td><input type="text" class="form-control" id="CodigoBodega<?php echo $Cont;?>" name="CodigoBodega[]" value="<?php echo $row_BodegasPuerto['id_bodega_puerto'];?>" onChange="CambiarMetodo('MetodoBodega<?php echo $Cont;?>');" autocomplete="off"  placeholder="Ingrese el código" /></td>
																
																<td><input type="text" class="form-control" id="Bodega<?php echo $Cont;?>" name="Bodega[]" value="<?php echo $row_BodegasPuerto['bodega_puerto'];?>" onChange="CambiarMetodo('MetodoBodega<?php echo $Cont;?>');" autocomplete="off"  placeholder="Ingrese el nombre" /></td>
																
																<td><textarea type="text" class="form-control" id="ComentariosBodega<?php echo $Cont;?>" name="ComentariosBodega[]" onChange="CambiarMetodo('MetodoBodega<?php echo $Cont;?>');" placeholder="Ingrese los comentarios"><?php echo $row_BodegasPuerto['comentarios'];?></textarea></td>
																
																<td>
																	<input name="ClienteBodega[]" type="hidden" id="ClienteBodega<?php echo $Cont;?>" value="<?php echo $row_BodegasPuerto['codigo_cliente'];?>">
																	<input name="NombreClienteBodega[]" type="text" class="form-control" id="NombreClienteBodega<?php echo $Cont;?>" value="<?php echo $row_BodegasPuerto['nombre_cliente'];?>" readonly>
																</td>
																<td>
																	<input name="SucursalBodega[]" type="text" class="form-control" id="SucursalBodega<?php echo $Cont;?>" value="<?php echo $row_BodegasPuerto['sucursal_cliente'];?>" readonly>
																</td>
																<td>
																	<select class="form-control" id="EstadoBodega<?php echo $Cont;?>" name="EstadoBodega[]" onChange="CambiarMetodo('MetodoBodega<?php echo $Cont;?>');">
																		 <option value="Y" <?php if($row_BodegasPuerto['estado']=='Y'){echo "selected='selected'";}?>>ACTIVO</option>
																		 <option value="N" <?php if($row_BodegasPuerto['estado']=='N'){echo "selected='selected'";}?>>INACTIVO</option>
																	 </select>
																</td>
																
																<td>
																	<?php if($row_BodegasPuerto['anexo']!=""){?>
																		<a id="LinkAnexoBodega<?php echo $Cont;?>" href="filedownload.php?file=<?php echo base64_encode($row_BodegasPuerto['anexo']);?>&dir=<?php echo base64_encode($dir_new);?>" target="_blank" title="Descargar archivo" class="btn-link btn-xs"><i class="fa fa-download"></i> <?php echo $row_BodegasPuerto['anexo'];?></a>
																	
																		<input name="AnexoBodega[]" type="file" id="AnexoBodega<?php echo $Cont;?>" onChange="CambiarMetodo('MetodoBodega<?php echo $Cont;?>');" style="display: none;" />
																	
																	<?php }else{ ?>
																 		<input name="AnexoBodega[]" type="file" id="AnexoBodega<?php echo $Cont;?>" onChange="CambiarMetodo('MetodoBodega<?php echo $Cont;?>');" />
																	<?php }?>
																</td>
																<td>
																	<button type="button" id="btnBodegasPuerto<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del" onClick="delRow3(this,'MetodoBodega<?php echo $Cont;?>');" title="Remover"><i class="fa fa-minus"></i></button>
																	
																	<button type="button" id="btnSelCliente<?php echo $Cont;?>" class="btn btn-primary btn-xs" onClick="SeleccionarCliente('<?php echo $Cont;?>');" title="Cambiar cliente"><i class="fa fa-refresh"></i></button>
																	
																	<?php if($row_BodegasPuerto['anexo']!=""){?>
																		<button type="button" id="btnCambiarAnx<?php echo $Cont;?>" class="btn btn-info btn-xs" onClick="CambiarAnexo('<?php echo $Cont;?>');" title="Cambiar Anexo"><i class="fa fa-paperclip"></i></button>
																	<?php }?>
																	
																	<input type="hidden" id="ID_Bodega<?php echo $Cont;?>" name="ID_Bodega[]" value="<?php echo $row_BodegasPuerto['id_bodega_puerto'];?>" />
																	<input type="hidden" id="MetodoBodega<?php echo $Cont;?>" name="MetodoBodega[]" value="0" />
																</td>
															</tr>
															 <?php 
																$Cont++;
															} while($row_BodegasPuerto=sqlsrv_fetch_array($SQL_BodegasPuerto));
														 ?>
															<tr id="divBodegasPuerto_<?php echo $Cont;?>">
																<td><input type="text" class="form-control" id="CodigoBodega<?php echo $Cont;?>" name="CodigoBodega[]" value="" autocomplete="off" placeholder="Ingrese el código" /></td>
																
																<td><input type="text" class="form-control" id="Bodega<?php echo $Cont;?>" name="Bodega[]" value="" autocomplete="off"  placeholder="Ingrese el nombre" /></td>
																
																<td><textarea type="text" class="form-control" id="ComentariosBodega<?php echo $Cont;?>" name="ComentariosBodega[]" placeholder="Ingrese los comentarios"></textarea></td>
																
																<td>
																	 <select name="ClienteBodega[]" class="form-control select2" style="width: 100%" id="ClienteBodega<?php echo $Cont;?>" onChange="BuscarSucursal('<?php echo $Cont;?>');">
																	  <option value="">Seleccione...</option>
																	  <?php while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){?>
																	  <option value="<?php echo $row_Cliente['CodigoCliente'];?>"><?php echo $row_Cliente['NombreCliente'];?></option>
																	  <?php }?>
																	</select>
																</td>
																<td>
																	<select name="SucursalBodega[]" class="form-control select2" style="width: 100%" id="SucursalBodega<?php echo $Cont;?>">
																	  <option value="">Seleccione...</option>
																	</select>
																</td>
																<td>
																	<select class="form-control" id="EstadoBodega<?php echo $Cont;?>" name="EstadoBodega[]">
																		 <option value="Y">ACTIVO</option>
																		 <option value="N">INACTIVO</option>
																	 </select>
																</td>
																
																<td>
																 	<input name="AnexoBodega[]" type="file" id="AnexoBodega<?php echo $Cont;?>" />
																</td>
																<td>
																	<input type="hidden" id="MetodoBodega<?php echo $Cont;?>" name="MetodoBodega[]" value="1" />
																	<button type="button" id="btnBodegasPuerto<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldBodegas(this);" title="Añadir otro"><i class="fa fa-plus"></i></button>
																</td>
															</tr>
														</tbody>
													</table>
												</div>	
											</div>
										</div>
										<div class="ibox">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Productos</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>	
											</div>
											<div class="ibox-content">
												<div class="table-responsive">
													<table width="100%" class="table table-bordered">
														<thead>
															<tr>
																<th>Código producto</th>
																<th>Nombre producto</th>
																<th>Comentarios</th>
																<th>Estado</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
														  <?php  
															$Cont=1;
															$row_Productos=sqlsrv_fetch_array($SQL_Productos);
															do{
															?>
															<tr id="divProductosPuerto_<?php echo $Cont;?>">
																 <td>
																	 <input type="text" class="form-control" id="CodigoProducto<?php echo $Cont;?>" name="CodigoProducto[]" value="<?php echo $row_Productos['id_producto_puerto'];?>" onChange="CambiarMetodo('MetodoProducto<?php echo $Cont;?>');" autocomplete="off" placeholder="Ingrese el código" />
																 </td>
																 <td>
																	 <input type="text" class="form-control" id="Producto<?php echo $Cont;?>" name="Producto[]" value="<?php echo $row_Productos['producto_puerto'];?>" onChange="CambiarMetodo('MetodoProducto<?php echo $Cont;?>');" autocomplete="off" placeholder="Ingrese el nombre" />
																 </td>
																 <td>
																	 <textarea type="text" class="form-control" id="ComentariosProducto<?php echo $Cont;?>" name="ComentariosProducto[]" onChange="CambiarMetodo('MetodoProducto<?php echo $Cont;?>');" placeholder="Ingrese los comentarios"><?php echo $row_Productos['comentarios'];?></textarea>
																 </td>
																 <td>
																	 <select class="form-control" id="EstadoProducto<?php echo $Cont;?>" name="EstadoProducto[]" onChange="CambiarMetodo('MetodoProducto<?php echo $Cont;?>');">
																		 <option value="Y" <?php if($row_Productos['estado']=='Y'){echo "selected='selected'";}?>>ACTIVO</option>
																		 <option value="N" <?php if($row_Productos['estado']=='N'){echo "selected='selected'";}?>>INACTIVO</option>
																	 </select>
																 </td>
																 <td>
																	<button type="button" id="btnProductosPuerto<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del" onClick="delRow3(this,'MetodoProducto<?php echo $Cont;?>');"><i class="fa fa-minus"></i> Remover</button>
																	 
																	<input type="hidden" id="ID_Producto<?php echo $Cont;?>" name="ID_Producto[]" value="<?php echo $row_Productos['id_producto_puerto'];?>" />
																	<input type="hidden" id="MetodoProducto<?php echo $Cont;?>" name="MetodoProducto[]" value="0" />
																 </td>
															</tr>
														 <?php 
																$Cont++;
															} while($row_Productos=sqlsrv_fetch_array($SQL_Productos));
														 ?>
															 <tr id="divProductosPuerto_<?php echo $Cont;?>">
																 <td>
																	 <input type="text" class="form-control" id="CodigoProducto<?php echo $Cont;?>" name="CodigoProducto[]" value="" autocomplete="off" placeholder="Ingrese el código" />
																 </td>
																 <td>
																	 <input type="text" class="form-control" id="Producto<?php echo $Cont;?>" name="Producto[]" value="" autocomplete="off" placeholder="Ingrese el nombre" />
																 </td>
																 <td>
																	 <textarea type="text" class="form-control" id="ComentariosProducto<?php echo $Cont;?>" name="ComentariosProducto[]" placeholder="Ingrese los comentarios"></textarea>
																 </td>
																 <td>
																	 <select class="form-control" id="EstadoProducto<?php echo $Cont;?>" name="EstadoProducto[]">
																		 <option value="Y">ACTIVO</option>
																		 <option value="N">INACTIVO</option>
																	 </select>
																 </td>
																 <td>
																	<input type="hidden" id="MetodoProducto<?php echo $Cont;?>" name="MetodoProducto[]" value="1" />
																	<button type="button" id="btnProductosPuerto<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldProductos(this);"><i class="fa fa-plus"></i> Añadir otro</button>
																 </td>
															</tr>					
														</tbody>
													</table>
												</div>	
											</div>
										</div>
										<div class="ibox">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Motonave</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>	
											</div>
											<div class="ibox-content">
												<div class="table-responsive">
													<table width="100%" class="table table-bordered">
														<thead>
															<tr>
																<th>Código transporte</th>
																<th>Nombre transporte</th>
																<th>REG (Registro capitanía)</th>
																<th>Comentarios</th>
																<th>Estado</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
														  <?php  
															$Cont=1;
															$row_Transporte=sqlsrv_fetch_array($SQL_Transporte);
															do{
															?>
															<tr id="divTransportePuerto_<?php echo $Cont;?>">
																 <td>
																	 <input type="text" class="form-control" id="CodigoTransporte<?php echo $Cont;?>" name="CodigoTransporte[]" value="<?php echo $row_Transporte['id_transporte_puerto'];?>" onChange="CambiarMetodo('MetodoTransporte<?php echo $Cont;?>');" autocomplete="off" placeholder="Ingrese el código" />
																 </td>
																 <td>
																	 <input type="text" class="form-control" id="Transporte<?php echo $Cont;?>" name="Transporte[]" value="<?php echo $row_Transporte['transporte_puerto'];?>" onChange="CambiarMetodo('MetodoTransporte<?php echo $Cont;?>');" autocomplete="off" placeholder="Ingrese el nombre" />
																 </td>
																 <td>
																	 <input type="text" class="form-control" id="Reg<?php echo $Cont;?>" name="Reg[]" value="<?php echo $row_Transporte['registro_capitania'];?>" onChange="CambiarMetodo('MetodoTransporte<?php echo $Cont;?>');" autocomplete="off" placeholder="Ingrese el registro" />
																 </td>
																 <td>
																	 <textarea type="text" class="form-control" id="ComentariosTransporte<?php echo $Cont;?>" name="ComentariosTransporte[]" onChange="CambiarMetodo('MetodoTransporte<?php echo $Cont;?>');" placeholder="Ingrese los comentarios"><?php echo $row_Transporte['comentarios'];?></textarea>
																 </td>
																 <td>
																	 <select class="form-control" id="EstadoTransporte<?php echo $Cont;?>" name="EstadoTransporte[]" onChange="CambiarMetodo('MetodoTransporte<?php echo $Cont;?>');">
																		 <option value="Y" <?php if($row_Transporte['estado']=='Y'){echo "selected='selected'";}?>>ACTIVO</option>
																		 <option value="N" <?php if($row_Transporte['estado']=='N'){echo "selected='selected'";}?>>INACTIVO</option>
																	 </select>
																 </td>
																 <td>
																	<button type="button" id="btnTransportesPuerto<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del" onClick="delRow3(this,'MetodoTransporte<?php echo $Cont;?>');"><i class="fa fa-minus"></i> Remover</button>
																	 
																	<input type="hidden" id="ID_Transporte<?php echo $Cont;?>" name="ID_Transporte[]" value="<?php echo $row_Transporte['id_transporte_puerto'];?>" />
																	<input type="hidden" id="MetodoTransporte<?php echo $Cont;?>" name="MetodoTransporte[]" value="0" />
																 </td>
															</tr>
														 <?php 
																$Cont++;
															} while($row_Transporte=sqlsrv_fetch_array($SQL_Transporte));
														 ?>
															 <tr id="divTransportePuerto_<?php echo $Cont;?>">
																 <td>
																	 <input type="text" class="form-control" id="CodigoTransporte<?php echo $Cont;?>" name="CodigoTransporte[]" value="" autocomplete="off" placeholder="Ingrese el código" />
																 </td>
																 <td>
																	 <input type="text" class="form-control" id="Transporte<?php echo $Cont;?>" name="Transporte[]" value="" autocomplete="off" placeholder="Ingrese el nombre" />
																 </td>
																 <td>
																	 <input type="text" class="form-control" id="Reg<?php echo $Cont;?>" name="Reg[]" value="" autocomplete="off" placeholder="Ingrese el registro" />
																 </td>
																 <td>
																	 <textarea type="text" class="form-control" id="ComentariosTransporte<?php echo $Cont;?>" name="ComentariosTransporte[]" placeholder="Ingrese los comentarios"></textarea>
																 </td>
																 <td>
																	 <select class="form-control" id="EstadoTransporte<?php echo $Cont;?>" name="EstadoTransporte[]">
																		 <option value="Y">ACTIVO</option>
																		 <option value="N">INACTIVO</option>
																	 </select>
																 </td>
																 <td>
																	<input type="hidden" id="MetodoTransporte<?php echo $Cont;?>" name="MetodoTransporte[]" value="1" />
																	<button type="button" id="btnTransportesPuerto<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldTransportes(this);"><i class="fa fa-plus"></i> Añadir otro</button>
																 </td>
															</tr>		
														</tbody>
													</table>
												</div>
											</div>
										</div>
										<input type="hidden" id="frmType" name="frmType" value="1" />
									</form>	 
								</div>
							</div>
						 </div>
						
												
					</div>
          		</div>
			 </div>
		
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
        $(document).ready(function(){
			$("#frmParam").validate({
				 submitHandler: function(form){
					Swal.fire({
						title: "¿Está seguro que desea guardar los datos?",
						icon: "info",
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
			$(".select2").select2();
			$('.i-checks').iCheck({
				 checkboxClass: 'icheckbox_square-green',
				 radioClass: 'iradio_square-green',
			  });	
        });
    </script>
<script>
function addFieldBodegas(btn){//Clonar div
	var clickID = parseInt($(btn).parent('td').parent('tr').attr('id').replace('divBodegasPuerto_',''));
	$("#ClienteBodega"+clickID).select2("destroy");
	$("#SucursalBodega"+clickID).select2("destroy");
	
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	var newID = (clickID+1);
	
	//var $example = $(".select2").select2();
	//$example.select2("destroy");

	$newClone = $('#divBodegasPuerto_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divBodegasPuerto_'+newID);

	//select
	$newClone.children("td").eq(3).children("select").eq(0).attr('id','ClienteBodega'+newID);
	$newClone.children("td").eq(3).children("select").eq(0).attr('onChange','BuscarSucursal('+newID+');');	
	$newClone.children("td").eq(4).children("select").eq(0).attr('id','SucursalBodega'+newID);
	$newClone.children("td").eq(5).children("select").eq(0).attr('id','EstadoBodega'+newID);

	//inputs
	$newClone.children("td").eq(0).children("input").eq(0).attr('id','CodigoBodega'+newID);
	$newClone.children("td").eq(1).children("input").eq(0).attr('id','Bodega'+newID);	
	$newClone.children("td").eq(2).children("textarea").eq(0).attr('id','ComentariosBodega'+newID);	
	$newClone.children("td").eq(6).children("input").eq(0).attr('id','AnexoBodega'+newID);	
	$newClone.children("td").eq(7).children("input").eq(0).attr('id','MetodoBodega'+newID);	

	//button
	$newClone.children("td").eq(7).children("button").eq(0).attr('id','btnBodegasPuerto'+newID);

	$newClone.insertAfter($('#divBodegasPuerto_'+clickID));

	document.getElementById('btnBodegasPuerto'+clickID).innerHTML="<i class='fa fa-minus'></i>";
	document.getElementById('btnBodegasPuerto'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnBodegasPuerto'+clickID).setAttribute('onClick','delRow2(this);');
	document.getElementById('btnBodegasPuerto'+clickID).setAttribute('title','Remover');
	
	//Limpiar campos
	document.getElementById('CodigoBodega'+newID).value='';
	document.getElementById('Bodega'+newID).value='';
	document.getElementById('ComentariosBodega'+newID).value='';
	document.getElementById('AnexoBodega'+newID).value='';
	
	$("#SucursalBodega"+newID).empty();
	$("#SucursalBodega"+newID).append($('<option>',{ value: '', text : 'Seleccione...' }));
	
	$("#ClienteBodega"+clickID).select2();
	$("#ClienteBodega"+newID).select2();
	
	$("#SucursalBodega"+clickID).select2();
	$("#SucursalBodega"+newID).select2();
}

function addFieldProductos(btn){//Clonar div
	var clickID = parseInt($(btn).parent('td').parent('tr').attr('id').replace('divProductosPuerto_',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	var newID = (clickID+1);
	
	//var $example = $(".select2").select2();
	//$example.select2("destroy");

	$newClone = $('#divProductosPuerto_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divProductosPuerto_'+newID);

	//select
	$newClone.children("td").eq(3).children("select").eq(0).attr('id','EstadoProducto'+newID);

	//inputs
	$newClone.children("td").eq(0).children("input").eq(0).attr('id','CodigoProducto'+newID);
	$newClone.children("td").eq(1).children("input").eq(0).attr('id','Producto'+newID);
	$newClone.children("td").eq(2).children("textarea").eq(0).attr('id','ComentariosProducto'+newID);	
	$newClone.children("td").eq(4).children("input").eq(0).attr('id','MetodoProducto'+newID);	

	//button
	$newClone.children("td").eq(4).children("button").eq(0).attr('id','btnProductosPuerto'+newID);

	$newClone.insertAfter($('#divProductosPuerto_'+clickID));

	document.getElementById('btnProductosPuerto'+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById('btnProductosPuerto'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnProductosPuerto'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('CodigoProducto'+newID).value='';
	document.getElementById('Producto'+newID).value='';
	document.getElementById('ComentariosProducto'+newID).value='';
}
	
function addFieldTransportes(btn){//Clonar div
	var clickID = parseInt($(btn).parent('td').parent('tr').attr('id').replace('divTransportePuerto_',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	var newID = (clickID+1);
	
	//var $example = $(".select2").select2();
	//$example.select2("destroy");

	$newClone = $('#divTransportePuerto_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divTransportePuerto_'+newID);

	//select
	$newClone.children("td").eq(4).children("select").eq(0).attr('id','EstadoTransporte'+newID);

	//inputs
	$newClone.children("td").eq(0).children("input").eq(0).attr('id','CodigoTransporte'+newID);
	$newClone.children("td").eq(1).children("input").eq(0).attr('id','Transporte'+newID);
	$newClone.children("td").eq(2).children("input").eq(0).attr('id','Reg'+newID);
	$newClone.children("td").eq(3).children("textarea").eq(0).attr('id','ComentariosTransporte'+newID);	
	$newClone.children("td").eq(5).children("input").eq(0).attr('id','MetodoTransporte'+newID);	

	//button
	$newClone.children("td").eq(5).children("button").eq(0).attr('id','btnTransportesPuerto'+newID);

	$newClone.insertAfter($('#divTransportePuerto_'+clickID));

	document.getElementById('btnTransportesPuerto'+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById('btnTransportesPuerto'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnTransportesPuerto'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('CodigoTransporte'+newID).value='';
	document.getElementById('Transporte'+newID).value='';
	document.getElementById('ComentariosTransporte'+newID).value='';
}
	
function delRow(){//Eliminar div
	$(this).parent('td').parent('tr').remove();
}
	
function delRow2(btn){//Eliminar div
	$(btn).parent('td').parent('tr').remove();
}
	
function delRow3(btn,id){//Ocultar div para parecer borrado
	CambiarMetodo(id,3)
	$(btn).parent('td').parent('tr').hide();
}
	
function CambiarMetodo(id,new_value=2){
	var inpMetodo=document.getElementById(id);
	inpMetodo.value=new_value;
}
	
function BuscarSucursal(id){
	$('.ibox-content').toggleClass('sk-loading',true);
	var ClienteBodega=document.getElementById('ClienteBodega'+id).value;
	$.ajax({
		type: "POST",
		async: false,
		url: "ajx_cbo_select.php?type=3&id="+ClienteBodega,
		success: function(response){
			$('#SucursalBodega'+id).html(response).fadeIn();
			$('#SucursalBodega'+id).trigger('change');
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
	$('.ibox-content').toggleClass('sk-loading',false);
}
	
function SeleccionarCliente(id){
	$('.ibox-content').toggleClass('sk-loading',true);
	var ClienteBodega=document.getElementById('ClienteBodega'+id).value;
	var SucursalBodega=document.getElementById('SucursalBodega'+id).value;
	$.ajax({
		type: "POST",
		async: false,
		url: "md_cliente_sucursal.php",
		data:{
			CodigoCliente:ClienteBodega,
			SucursalCliente:SucursalBodega,
			Id:id
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}
	
function CambiarAnexo(id){
	$('#LinkAnexoBodega'+id).hide();
	$('#AnexoBodega'+id).show();	
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>