<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
if(PermitirAcceso(1002)||PermitirAcceso(1003))

$sw_ext=0;//Sw que permite saber si la ventana esta abierta en modo pop-up. Si es así, no cargo el menú ni el menú superior.
$sw_tech=0;//Sw para saber si el articulo tiene algun tipo de tecnologia. (DIALNET)
$sw_error=0;//Sw para saber si ha ocurrido un error al crear o actualizar un articulo.

//Posicion y OLT para controlar los datos que se envian a SAP cuando sea tecnologia AMS.
$Posicion="";
$OLT="";

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

if(isset($_GET['tl'])&&($_GET['tl']!="")){//0 Si se está creando. 1 Se se está editando.
	$edit=$_GET['tl'];
}elseif(isset($_POST['tl'])&&($_POST['tl']!="")){
	$edit=$_POST['tl'];
}else{
	$edit=0;
}

if($edit==0){
	$Title="Crear artículo";
}else{
	$Title="Editar artículo";
}

if(isset($_POST['P'])&&($_POST['P']=="MM_Art")){//Insertar o actualizar articulo
	try{
		$Metodo=2;//Actualizar en el web services
		$Type=2;//Ejecutar actualizar en el SP
		$IdArticuloPortal="NULL";
		if(base64_decode($_POST['IdArticuloPortal'])==""){
			$Metodo=2;
			$Type=1;
		}else{
			$IdArticuloPortal="'".base64_decode($_POST['IdArticuloPortal'])."'";
		}
		
		$Grupo=explode("__",$_POST['GroupCode']);
		
		$Posicion="''";
		$OLT="''";
		
		if($_POST['CDU_TipoTecnologia']==2){
			//Para obtener la Posición y la OLT, verifico si la tecnología es AMS y envío los datos, sino los envío NULL.
			$Posicion="'".$_POST['Posicion']."'";
			$OLT="'".$_POST['OLT']."'";
		}
			
		$ParamArticulos=array(
			"$IdArticuloPortal",
			"'".$_POST['ItemCode']."'",
			"'".$_POST['ItemName']."'",
			"'".$_POST['FrgnName']."'",
			"'".$Grupo[0]."'",
			"'".$_POST['ItemType']."'",
			"'".$_POST['UnidadMedInv']."'",
			"'".$_POST['EstadoArticulo']."'",
			"'".$_POST['CodigoCliente']."'",
			"'".$_POST['NombreCliente']."'",
			"'".$_POST['NombreSucursal']."'",
			"'".$_POST['DireccionSucursal']."'",
			"'".$_POST['CDU_EstadoServicio']."'",
			$Posicion,
			$OLT,
			"'".$_POST['CDU_TipoTecnologia']."'",
			"'".$_POST['CDU_IdAutTecnologia']."'",
			"$Metodo",
			"'".$_SESSION['CodUser']."'",
			"'".$_SESSION['CodUser']."'",
			"$Type"
		);
		$SQL_Articulos=EjecutarSP('sp_tbl_Articulos',$ParamArticulos,48);
		if($SQL_Articulos){
			if(base64_decode($_POST['IdArticuloPortal'])==""){
				$row_NewIdArticulo=sqlsrv_fetch_array($SQL_Articulos);
				$IdArticulo=$row_NewIdArticulo[0];
			}else{
				$IdArticulo=base64_decode($_POST['IdArticuloPortal']);
			}	
			$IdItemCode=$_POST['ItemCode'];
			//sqlsrv_close($conexion);
			//header('Location:'.base64_decode($_POST['pag'])."?".base64_decode($_POST['return']).'&a='.base64_encode("OK_ArtUpd"));
			
			
			//Enviar datos al WebServices
			try{
				require_once("includes/conect_ws.php");
				$Parametros=array(
					'pIdArticulo' => $IdArticulo,
					'pLogin'=>$_SESSION['User']
				);
				$Client->AppPortal_InsertarArticulos($Parametros);
				$Respuesta=$Client->__getLastResponse();
				$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);
				$espaciosDeNombres = $Contenido->getNamespaces(true);
				$Nodos = $Contenido->children($espaciosDeNombres['s']);
				$Nodo=	$Nodos->children($espaciosDeNombres['']);
				$Nodo2=	$Nodo->children($espaciosDeNombres['']);
				
				$Archivo=json_decode($Nodo2,true);
				if($Archivo['ID_Respuesta']=="0"){
					//InsertarLog(1, 0, 'Error al generar el informe');
					//throw new Exception('Error al generar el informe. Error de WebServices');		
					$sw_error=1;
					$msg_error=$Archivo['DE_Respuesta'];
					//throw new Exception($Archivo['DE_Respuesta']);		
					/*if($_POST['EstadoActividad']=='Y'){
						$UpdEstado="Update tbl_Actividades Set Cod_Estado='N' Where ID_Actividad='".$IdActividad."'";
						$SQL_UpdEstado=sqlsrv_query($conexion,$UpdEstado);
					}*/
				}else{
					
					//Si cambio de tecnologia, elimino los datos de la tecnologia anterior
					if(($_POST['tech_act']!="")&&($_POST['tech_act']!=$_POST['CDU_TipoTecnologia'])){
						if($_POST['tech_act']==1){
							require_once("includes/conexion_mysql.php");
							$SQL_Delete=EjecutarSP('sp_DeleteAttUser',$_POST['idusertech_act'],48,3);
							$SQL_DeleteGroup=EjecutarSP('sp_DeleteUserGroup',$_POST['idusertech_act'],48,3);
							mysqli_close($conexion_mysql);						
						}						
					}
					//Actualizar dependiendo del tipo de tecnología, en caso de que tenga una asignada
					if($_POST['CDU_TipoTecnologia']!=0){
						if($_POST['CDU_TipoTecnologia']==1){//RADIUS
							require_once("includes/conexion_mysql.php");
							
							$SQL_Delete=EjecutarSP('sp_DeleteAttUser',$_POST['CDU_IdAutTecnologia'],48,3);
							$SQL_DeleteGroup=EjecutarSP('sp_DeleteUserGroup',$_POST['CDU_IdAutTecnologia'],48,3);
							
							if($_POST['CDU_IdAutTecnologia']!=""){
								
								//Atributos de usuario
								$Count=count($_POST['attatribute']);
								$i=0;							
								
								if($_POST['UsuarioTecnologia']!=""){
									if($SQL_Delete){
										
										//Insertar contraseña
										$ParamInsPass=array(
											"'radcheck'",
											"NULL",
											"'".$_POST['UsuarioTecnologia']."'",
											"'Cleartext-Password'",
											"':='",
											"'".$_POST['Password']."'",
											"1"
										);										
										$SQL_InsPass=EjecutarSP('sp_usuarios',$ParamInsPass,48,3);
										
										//Insertar información
										$ParamInsInfo=array(
											"'".$_POST['UsuarioTecnologia']."'",
											"'".$_POST['Notas']."'"
										);
										$SQL_InsInfo=EjecutarSP('sp_userinfo',$ParamInsInfo,48,3);
										
										while($i<$Count){
											if($_POST['attatribute'][$i]!=""){
												if($_POST['atttype'][$i]=="check"){
													$pTabla="radcheck";
												}else{
													$pTabla="radreply";
												}
												//Insertar los atributos del usuario
												$ParamInsAtt=array(
													"'".$pTabla."'",
													"NULL",
													"'".$_POST['UsuarioTecnologia']."'",
													"'".$_POST['attatribute'][$i]."'",
													"'".$_POST['attop'][$i]."'",
													"'".$_POST['attvalue'][$i]."'",
													"1"
												);

												$SQL_InsAtt=EjecutarSP('sp_usuarios',$ParamInsAtt,48,3);

												if(!$SQL_InsAtt){
													$sw_error=1;
													$msg_error="Ha ocurrido un problema al insertar el usuario a RADIUS";					
												}
											}
											$i=$i+1;
										}
									}else{
										$sw_error=1;
										$msg_error="Ha ocurrido un problema al insertar el usuario a RADIUS";	
									}
									
									//Asignacion de grupo
									if($SQL_DeleteGroup){
										//Insertar el grupo
										$ParamInsGrp=array(
											"'".$_POST['UsuarioTecnologia']."'",
											"'".$Grupo[1]."'",
											"10"
										);

										$SQL_InsGrp=EjecutarSP('sp_UserGroup',$ParamInsGrp,48,3);

										if(!$SQL_InsGrp){
											$sw_error=1;
											$msg_error="Ha ocurrido un problema al asignar el grupo al usuario en RADIUS";			
										}

										//Si está inactivo el servicio
										if($_POST['CDU_EstadoServicio']=="N"){
											$ParamInsGrp=array(
												"'".$_POST['UsuarioTecnologia']."'",
												"'daloRADIUS-Disabled-Users'",
												"0"
											);

											$SQL_InsGrp=EjecutarSP('sp_UserGroup',$ParamInsGrp,48,3);

											if(!$SQL_InsGrp){
												$sw_error=1;
												$msg_error="Ha ocurrido un problema al asignar el grupo de inactivacion al usuario en RADIUS";			
											}
										}									
									}
								}								
							}
							mysqli_close($conexion_mysql);
						}
						elseif($_POST['CDU_TipoTecnologia']==2){//AMS (NOKIA)
							
							if($_POST['CDU_IdAutTecnologia']!=""){								
								
								if($_POST['UsuarioTecnologia']!=""){
									
								
								}								
							}
						}					
					}
					sqlsrv_close($conexion);
					header('Location:'.base64_decode($_POST['pag'])."?".base64_decode($_POST['return']).'&a='.base64_encode("OK_ArtUpd"));
				}	
			}catch (Exception $e) {
				$sw_error=1;
				//echo 'Excepcion capturada 1: ',  $e->getMessage(), "\n";
			}
		}else{
			$sw_error=1;
			$msg_error="Error al actualizar el articulo";
		}						
	}catch (Exception $e) {
		$sw_error=1;
		//echo 'Excepcion capturada 2: ',  $e->getMessage(), "\n";
	}	
}

if($edit==1){//Editar articulo

	//Articulo
	$SQL=Seleccionar('uvw_Sap_tbl_ArticulosTodos','*',"ItemCode='".$IdItemCode."'");
	$row=sqlsrv_fetch_array($SQL);
	//$sw_tech=$row['CDU_IdTipoTecnologia'];
	//$Posicion=$row['Posicion'];
	//$OLT=$row['IdOLT'];
	
	//Datos de inventario
	$SQL_DtInvent=Seleccionar('uvw_Sap_tbl_Articulos','*',"ItemCode='".$IdItemCode."' and OnHand > 0",'WhsCode');
	
	//Lista de precios
	$SQL_ListaPrecio=Seleccionar('uvw_Sap_tbl_ListaPrecioArticulos','*',"ItemCode='".$IdItemCode."'");
	
	//Anexos
	$SQL_AnexoArticulos=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','*',"AbsEntry='".$row['IdAnexoArticulo']."'");
		
}
if($sw_error==1){//Si ocurre un error
	
	//Articulo
	$SQL=Seleccionar('uvw_tbl_Articulos','*',"ItemCode='".$IdItemCode."'");
	$row=sqlsrv_fetch_array($SQL);
	//$sw_tech=$row['CDU_IdTipoTecnologia'];
	//$Posicion=$row['Posicion'];
	//$OLT=$row['IdOLT'];
	
	//Datos de inventario
	$SQL_DtInvent=Seleccionar('uvw_Sap_tbl_Articulos','*',"ItemCode='".$IdItemCode."' and OnHand > 0",'WhsCode');
	
	//Lista de precios
	$SQL_ListaPrecio=Seleccionar('uvw_Sap_tbl_ListaPrecioArticulos','*',"ItemCode='".$IdItemCode."'");
		
}

//Estado articulo
$SQL_EstadoArticulo=Seleccionar('uvw_tbl_EstadoArticulo','*');

//Estado servicio articulo
$SQL_EstadoServicio=Seleccionar('uvw_Sap_tbl_EstadoServicioArticulos','*');

//Tipos de articulos
$SQL_TipoArticulo=Seleccionar('uvw_tbl_TipoArticulo','*');

//Grupos de articulos
$SQL_GruposArticulos=Seleccionar('uvw_Sap_tbl_GruposArticulos','*','','ItmsGrpNam');

//Tipos de tecnologia
$SQL_TipoTecnologia=Seleccionar('uvw_Sap_tbl_TipoTecnologiaArticulos','*');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $Title;?> | <?php echo NOMBRE_PORTAL;?></title>
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ArtUpd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El ID de servicio ha sido actualizado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Advertencia!',
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
	.select2-container{ 
		width: 100% !important; 
	}
	.ibox-title a{
		color: inherit !important;
	}
	.collapse-link:hover{
		cursor: pointer;
	}
</style>
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
                    <h2><?php echo $Title;?></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gestión de artículos</a>
                        </li>
                        <li class="active">
                            <strong><?php echo $Title;?></strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <form action="articulos.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="FrmArticulo">
			 <div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
			 	<div class="row">
					<div class="col-lg-12 form-horizontal">	
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-plus-square"></i> Acciones</h3></label>
						</div>
						<div class="form-group">
							<div class="col-lg-12">
								<?php 								
								if(isset($_REQUEST['return'])){
									$_REQUEST['return']=base64_decode($_REQUEST['return']);
									$_REQUEST['return']=QuitarParametrosURL($_REQUEST['return'],array("a"));
									$return=base64_decode($_REQUEST['pag'])."?".$_REQUEST['return'];						
								}else{
									$return="consultar_articulos.php?";
								}
								$return=QuitarParametrosURL($return,array("a"));
								
								?>
								<?php 
								if($edit==1){
									if(PermitirFuncion(1003)){?>
										<button class="btn btn-warning" type="submit" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar</button>
								<?php }
								}else{
									if(PermitirFuncion(1001)){?>
										<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Crear articulo</button>
								<?php }
								} ?>
								<?php if($sw_ext==0){?>
									<a href="<?php echo $return;?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
								<?php }?>
							</div>
						</div>
						<input type="hidden" id="P" name="P" value="MM_Art" />
						<input type="hidden" id="IdArticuloPortal" name="IdArticuloPortal" value="<?php if(isset($row['IdArticuloPortal'])){echo base64_encode($row['IdArticuloPortal']); }?>" />
						<input type="hidden" id="tech_act" name="tech_act" value="<?php if($edit==1){echo $row['CDU_IdTipoTecnologia'];}?>" />
						<input type="hidden" id="idusertech_act" name="idusertech_act" value="<?php if($edit==1){echo $row['CDU_IdAutTecnologia'];}?>" />
						<input type="hidden" id="ext" name="ext" value="<?php echo $sw_ext;?>" />
						<input type="hidden" id="tl" name="tl" value="<?php echo $edit;?>" />
						<input type="hidden" id="error" name="error" value="<?php echo $sw_error;?>" />
						<input type="hidden" id="pag" name="pag" value="<?php if(isset($_REQUEST['pag'])){echo $_REQUEST['pag'];}else{echo base64_encode("articulos.php");}//viene de afuera ?>" />
						<input type="hidden" id="return" name="return" value="<?php if(isset($_REQUEST['return'])){echo base64_encode($_REQUEST['return']);}else{echo base64_encode($_SERVER['QUERY_STRING']);}//viene de afuera ?>" />
					</div>
				</div>
			 </div>
			 <br>
			 <div class="row">
				<div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
					 <div class="tabs-container">
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tabSN-1"><i class="fa fa-info-circle"></i> Información general</a></li>
							<?php if($edit==1){?><li><a data-toggle="tab" href="#tabSN-2"><i class="fa fa-database"></i> Datos de inventario</a></li><?php }?>
							<?php if($edit==1){?><li><a data-toggle="tab" href="#tabSN-3" onClick="ConsultarTab('3');"><i class="fa fa-list-alt"></i> Lista de materiales</a></li><?php }?>
							<li><a data-toggle="tab" href="#tabSN-4"><i class="fa fa-user-circle"></i> Datos adicionales</a></li>
							<li><a data-toggle="tab" href="#tabSN-5"><i class="fa fa-paperclip"></i> Anexos</a></li>
						</ul>
					   <div class="tab-content">
						   <div id="tabSN-1" class="tab-pane active">
							   <div class="ibox">
									<div class="ibox-title bg-success">
										<h5 class="collapse-link"><i class="fa fa-info-circle"></i> Información principal</h5>
										 <a class="collapse-link pull-right">
											<i class="fa fa-chevron-up"></i>
										</a>	
									</div>
									<div class="ibox-content">
										<div class="form-group">
											<label class="col-lg-1 control-label">Código</label>
											<div class="col-lg-3">
												<input name="ItemCode" autofocus="autofocus" type="text" required class="form-control" id="ItemCode" value="<?php if($edit==1){echo $row['ItemCode'];}?>" <?php if($edit==1){ echo "readonly='readonly'";} ?>>
											</div>
											<label class="col-lg-1 control-label">Artículo de inventario</label>
											<div class="col-lg-1">
												<label class="checkbox-inline i-checks"><input name="ArtInventario" id="ArtInventario" type="checkbox" value="Y" <?php if($edit==1){if($row['InvntItem']=='Y'){echo "checked=\"checked\"";}}?>></label>
											</div>
											<label class="col-lg-1 control-label">Artículo de venta</label>
											<div class="col-lg-1">
												<label class="checkbox-inline i-checks"><input name="ArtVenta" id="ArtVenta" type="checkbox" value="Y" <?php if($edit==1){if($row['SellItem']=='Y'){echo "checked=\"checked\"";}}?>></label>
											</div>
											<label class="col-lg-1 control-label">Artículo de compra</label>
											<div class="col-lg-1">
												<label class="checkbox-inline i-checks"><input name="ArtCompra" id="ArtCompra" type="checkbox" value="Y" <?php if($edit==1){if($row['PrchseItem']=='Y'){echo "checked=\"checked\"";}}?>></label>
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Descripción</label>
											<div class="col-lg-3">
												<input type="text" class="form-control" name="ItemName" id="ItemName" required value="<?php if($edit==1){ echo $row['ItemName'];}?>">
											</div>
											<label class="col-lg-1 control-label">Referencia</label>
											<div class="col-lg-3">
												<input type="text" class="form-control" name="FrgnName" id="FrgnName" value="<?php if($edit==1){echo $row['FrgnName'];}?>">
											</div>
											<label class="col-lg-1 control-label">Tipo artículo</label>
											<div class="col-lg-3">
												<select name="ItemType" class="form-control" id="ItemType" required>
												<?php
													while($row_TipoArticulo=sqlsrv_fetch_array($SQL_TipoArticulo)){?>
														<option value="<?php echo $row_TipoArticulo['ItemType'];?>" <?php if((isset($row['ItemType']))&&(strcmp($row_TipoArticulo['ItemType'],$row['ItemType'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoArticulo['DE_ItemType'];?></option>
												<?php }?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Estado</label>
											<div class="col-lg-3">
												<select name="EstadoArticulo" class="form-control" id="EstadoArticulo" required>
												<?php
													while($row_EstadoArticulo=sqlsrv_fetch_array($SQL_EstadoArticulo)){?>
														<option value="<?php echo $row_EstadoArticulo['Cod_Estado'];?>" <?php if((isset($row['Estado']))&&(strcmp($row_EstadoArticulo['Cod_Estado'],$row['Estado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoArticulo['NombreEstado'];?></option>
												<?php }?>
												</select>
											</div>
											<label class="col-lg-1 control-label">Unidad de medida</label>
											<div class="col-lg-3">
												<input type="text" class="form-control" name="UnidadMedInv" id="UnidadMedInv" value="<?php if($edit==1){echo $row['InvntryUom'];}?>">
											</div>
											<label class="col-lg-1 control-label">Grupo</label>
											<div class="col-lg-3">
												<select name="GroupCode" class="form-control select2" id="GroupCode" required>
													<option value="">Seleccione...</option>
												<?php
													while($row_GruposArticulos=sqlsrv_fetch_array($SQL_GruposArticulos)){?>
														<option value="<?php echo $row_GruposArticulos['ItmsGrpCod']."__".$row_GruposArticulos['ItmsGrpNam'];?>" <?php if((isset($row['ItmsGrpCod']))&&(strcmp($row_GruposArticulos['ItmsGrpCod'],$row['ItmsGrpCod'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_GruposArticulos['ItmsGrpNam'];?></option>
												<?php }?>
												</select>
											</div>
										</div>
									</div>
							   </div>
							   <div class="ibox">
									<div class="ibox-title bg-success">
										<h5 class="collapse-link"><i class="fa fa-money"></i> Lista de precios</h5>
										 <a class="collapse-link pull-right">
											<i class="fa fa-chevron-up"></i>
										</a>	
									</div>
									<div class="ibox-content">
										<div class="form-group">
											<div class="table-responsive">
												<table class="table table-bordered table-hover" >
												<thead>
												<tr>
													<th>Lista de precio</th>
													<th>Precio por unidad</th>
													<th>Tarifa de impuesto</th>
													<th>Valor del impuesto</th>
													<th>Precio con impuesto</th>
												</tr>
												</thead>
												<tbody>
												<?php 
													while($row_ListaPrecio=sqlsrv_fetch_array($SQL_ListaPrecio)){ ?>
														<tr>
															<td><?php echo $row_ListaPrecio['ListName'];?></td>
															<td><?php echo number_format($row_ListaPrecio['Price'],2);?></td>
															<td><?php echo number_format($row_ListaPrecio['TarifaIVA'],2);?></td>
															<td><?php echo number_format($row_ListaPrecio['VatSum'],2);?></td>
															<td><?php echo number_format($row_ListaPrecio['PriceTax'],2);?></td>
														</tr>
												<?php }?>
												</tbody>
												</table>
											</div>
										</div>
									</div>
							   </div>
						   </div>
						   <?php if($edit==1){?>
						   <div id="tabSN-2" class="tab-pane">
								<div class="panel-body">
									<div class="form-group">
										<div class="col-lg-12">
											<div class="table-responsive">
											<table class="table table-striped table-bordered">
												<thead>
												<tr>
													<th>Código almacén</th>
													<th>Nombre almacén</th>
													<th>Stock</th>
													<th>Comprometido</th>
													<th>Pedido</th>
													<th>Disponible</th>
													<th>Costo del artículo</th>
												</tr>
												</thead>
												<tbody>
												<?php while($row_DtInvent=sqlsrv_fetch_array($SQL_DtInvent)){?>
													 <tr>
														<td><?php echo $row_DtInvent['WhsCode'];?></td>
														<td><?php echo $row_DtInvent['WhsName'];?></td>
														<td><?php echo number_format($row_DtInvent['OnHand'],2);?></td>
														<td><?php echo number_format($row_DtInvent['Comprometido'],2);?></td>
														<td><?php echo number_format($row_DtInvent['Pedido'],2);?></td>
														<td><?php echo number_format($row_DtInvent['Disponible'],2);?></td>
														<td><?php echo "$".number_format($row_DtInvent['CostoArticulo'],2);?></td>
													</tr>
												<?php }?>
												</tbody>
											</table>
											</div>
										</div>
									</div>	
								</div>										   
						   </div>
						   <?php }?>
						   <?php if($edit==1){?>
						   <div id="tabSN-3" class="tab-pane">
								<div id="dv_ListaMateriales" class="panel-body">

								</div>										   
						   </div>
						   <?php }?>
						   <div id="tabSN-4" class="tab-pane">
								<br>
								<div class="form-group">
									<label class="col-lg-1 control-label">Código cliente</label>
									<div class="col-lg-2">
										<input type="text" class="form-control" name="CodigoCliente" id="CodigoCliente" value="<?php if($edit==1){echo $row['CodigoCliente'];}?>" readonly>
									</div>
									<label class="col-lg-1 control-label">Nombre cliente</label>
									<div class="col-lg-4">
										<input type="text" class="form-control" name="NombreCliente" id="NombreCliente" value="<?php if($edit==1){echo $row['NombreCliente'];}?>" readonly>
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Sucursal</label>
									<div class="col-lg-2">
										<input type="text" class="form-control" name="NombreSucursal" id="NombreSucursal" value="<?php if($edit==1){echo $row['NombreSucursal'];}?>" readonly>
									</div>	
									<label class="col-lg-1 control-label">Dirección</label>
									<div class="col-lg-5">
										<input type="text" class="form-control" name="DireccionSucursal" id="DireccionSucursal" value="<?php if($edit==1){echo $row['DireccionSucursal'];}?>" readonly>
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Estado de servicio</label>
									<div class="col-lg-2">
										<select name="CDU_EstadoServicio" class="form-control m-b" id="CDU_EstadoServicio" required>
										<?php
											while($row_EstadoServicio=sqlsrv_fetch_array($SQL_EstadoServicio)){?>
												<option value="<?php echo $row_EstadoServicio['IdEstadoServicioArticulo'];?>" <?php if((isset($row['CDU_EstadoServicio']))&&(strcmp($row_EstadoServicio['IdEstadoServicioArticulo'],$row['CDU_EstadoServicio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoServicio['DeEstadoServicioArticulo'];?></option>
										<?php }?>
										</select>
									</div>
									<label class="col-lg-1 control-label">Tipo tecnología</label>
									<div class="col-lg-2">
										<select name="CDU_TipoTecnologia" class="form-control m-b" id="CDU_TipoTecnologia" required onChange="ConsultarTecnologia();">
										<?php
											while($row_TipoTecnologia=sqlsrv_fetch_array($SQL_TipoTecnologia)){?>
												<option value="<?php echo $row_TipoTecnologia['IdTipoTecnologia'];?>" <?php if((isset($row['CDU_IdTipoTecnologia']))&&(strcmp($row_TipoTecnologia['IdTipoTecnologia'],$row['CDU_IdTipoTecnologia'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoTecnologia['DeTipoTecnologia'];?></option>
										<?php }?>
										</select>
									</div>
									<label class="col-lg-1 control-label">Autenticación tecnología</label>
									<div class="col-lg-3">
										<input type="text" class="form-control" name="CDU_IdAutTecnologia" id="CDU_IdAutTecnologia" value="<?php if($edit==1){echo $row['CDU_IdAutTecnologia'];}?>" autocomplete="off">
									</div>
									<div class="col-lg-1">
										<button type="button" onClick="ConsultarTecnologia();" class="btn btn-success btn-xs" title="Consultar"><i class="fa fa-search"></i></button>
									</div>
								</div>
							   <div id="dv_Tech" <?php if($sw_tech==0){?>style="display: none;"<?php }?>>
								<?php 
								   if($edit==1){
									   if($row['CDU_IdTipoTecnologia']==1){//RADIUS
										   $_GET['idTec']=$row['CDU_IdAutTecnologia'];
										   include_once("tech_radius.php");
									   }elseif($row['CDU_IdTipoTecnologia']==2){//AMS (NOKIA)
										   $_GET['idTec']=$row['CDU_IdAutTecnologia'];
										   $_GET['AMSPos']=$row['Posicion'];
										   $_GET['AMSOlt']=$row['IdOLT'];
										   include_once("tech_ams.php");
									   }
								   }							   
								   ?>
							   </div>									   
						   </div>
						   </form>
						   <div id="tabSN-5" class="tab-pane">
								<div class="panel-body">
									<?php if($edit==1){
											if($row['IdAnexoArticulo']!=0){?>
											<div class="form-group">
												<div class="col-xs-12">
													<?php while($row_AnexoArticulos=sqlsrv_fetch_array($SQL_AnexoArticulos)){
														$Icon=IconAttach($row_AnexoArticulos['FileExt']);?>
														<div class="file-box">
															<div class="file">
																<a href="attachdownload.php?file=<?php echo base64_encode($row_AnexoArticulos['AbsEntry']);?>&line=<?php echo base64_encode($row_AnexoArticulos['Line']);?>" target="_blank">
																	<div class="icon">
																		<i class="<?php echo $Icon;?>"></i>
																	</div>
																	<div class="file-name">
																		<?php echo $row_AnexoArticulos['NombreArchivo'];?>
																		<br/>
																		<small><?php echo $row_AnexoArticulos['Fecha'];?></small>
																	</div>
																</a>
															</div>
														</div>
													<?php }?>
												</div>
											</div>
								<?php }else{ echo "<p>Sin anexos.</p>"; }
									}?>
									<?php if(($edit==0)||(($edit==1)&&($row['Estado']!='N')&&(PermitirFuncion(1003)))){?> 
									<div class="row">
										<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
											<?php if($sw_error==0){LimpiarDirTemp();}?>
											<div class="fallback">
												<input name="File" id="File" type="file" form="dropzoneForm" />
											</div>
										 </form>
									</div>
									<?php }?>
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
	 $("#FrmArticulo").validate({
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
	 
	 $('.i-checks').iCheck({
		 checkboxClass: 'icheckbox_square-green',
		 radioClass: 'iradio_square-green',
	  });
	 
	$(".select2").select2();
	 
	$(".btn_del").each(function (el){
		$(this).bind("click",delRow);
	});
	 
 });
</script>
<script>
function addField(btn){//Clonar divDir
	var clickID = parseInt($(btn).parent('div').attr('id').replace('div_',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	var newID = (clickID+1);

	$newClone = $('#div_'+clickID).clone(true);

	//div
	$newClone.attr("id",'div_'+newID);

	//select
	$newClone.children("div").eq(0).children("select").eq(0).attr('id','attvendor'+newID);
	$newClone.children("div").eq(1).children("select").eq(0).attr('id','attatribute'+newID);
	$newClone.children("div").eq(2).children("select").eq(0).attr('id','attop'+newID);
	$newClone.children("div").eq(4).children("select").eq(0).attr('id','atttype'+newID);
	
	$newClone.children("div").eq(0).children("select").eq(0).attr('onChange','BuscarAtributo('+newID+');');	
	$newClone.children("div").eq(1).children("select").eq(0).attr('onChange','BuscarDatosAtributo('+newID+');');	

	//inputs
	$newClone.children("div").eq(3).children("input").eq(0).attr('id','attvalue'+newID);

	//button
	$newClone.children("button").eq(0).attr('id',''+newID);

	$newClone.insertAfter($('#div_'+clickID));

	//$("#"+clickID).val('Remover');
	document.getElementById(''+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById(''+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById(''+clickID).setAttribute('onClick','delRow2(this);');
	
	document.getElementById('attvalue'+newID).value='';

	//$("#"+clickID).addEventListener("click",delRow);

	//$("#"+clickID).bind("click",delRow);
}
</script>
<script>
function delRow(){//Eliminar div
	$(this).parent('div').remove();
}
function delRow2(btn){//Eliminar div
	$(btn).parent('div').remove();
}
</script>
<script>
//Variables de tab
 var tab_3=0;

function BuscarAtributo(id){
	$.ajax({
		type: "POST",
		url: "ajx_cbo_select.php?type=18&id="+document.getElementById('attvendor'+id).value,
		success: function(response){
			$('#attatribute'+id).html(response).fadeIn();
			$('#attatribute'+id).trigger('change');
		}
	});
}
function BuscarDatosAtributo(id){
	var att=document.getElementById('attatribute'+id).value;
	if(att!=""){
		$.ajax({
			url:"ajx_buscar_datos_json.php",
			data:{type:14,NomAtt:att},
			dataType:'json',
			success: function(data){
				document.getElementById('attop'+id).value=data.RecommendedOP;
				document.getElementById('attvalue'+id).value=data.Value;
				document.getElementById('atttype'+id).value=data.RecommendedTable;
			}
		});		
	}
	
}
function ConsultarTecnologia(){
	$('.ibox-content').toggleClass('sk-loading',true);
	var TipoTec=document.getElementById('CDU_TipoTecnologia').value;
	var IdTec=document.getElementById('CDU_IdAutTecnologia').value;
	if(TipoTec==1){
		document.getElementById('dv_Tech').style.display='block';
		$.ajax({
			type: "POST",
			url: "tech_radius.php?idTec="+IdTec,
			success: function(response){
				$('#dv_Tech').html(response).fadeIn();
				$(".select2").select2();
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});
	}else if(TipoTec==2){
		document.getElementById('dv_Tech').style.display='block';
		$.ajax({
			type: "POST",
			url: "tech_ams.php?idTec="+IdTec+"&AMSPos=<?php echo $Posicion;?>"+"&AMSOlt=<?php echo $OLT;?>",
			success: function(response){
				$('#dv_Tech').html(response).fadeIn();
				$(".select2").select2();
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});
	}else{
		document.getElementById('CDU_IdAutTecnologia').value='';
		document.getElementById('dv_Tech').style.display='none';
		$('.ibox-content').toggleClass('sk-loading',false);
	}	
}
function GenerarClave(){
	Swal.fire({
		title:"Se va a generar una nueva contraseña",
		text: "¿Realmente desea continuar?",
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "SI",
		cancelButtonText: "NO"
	}).then((result) => {
		if (result.isConfirmed) {
			var Pass=document.getElementById('Password');
			var NewPass=generar_clave('12');
			Pass.value=NewPass;
		}
	});	
}
function ValidarUsuario(){
	var UserSAP=document.getElementById('CDU_IdAutTecnologia').value;
	var UserTec=document.getElementById('UsuarioTecnologia').value;
	if((UserSAP!="")&&(UserTec!="")){
		if(UserSAP!=UserTec){
			Swal.fire({
				title: '¡Error!',
				text: 'El usuario de la tecnología no es el mismo que el de SAP. Por favor verifique.',
				icon: "error"
			}).then((result) => {
				if (result.isConfirmed) {
					document.getElementById('UsuarioTecnologia').value='';
					document.getElementById('UsuarioTecnologia').focus();
				}
			});				
		}
	}
	
}

function ConsultarTab(type){
	if(type==3){//Lista de materiales
		if(tab_3==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "ar_lista_materiales.php?id=<?php if($edit==1){echo base64_encode($IdItemCode);}?>",
				success: function(response){
					$('#dv_ListaMateriales').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_3=1;
				}
			});
		}
	}
}
</script>
<script>
 Dropzone.options.dropzoneForm = {
		paramName: "File", // The name that will be used to transfer the file
		maxFilesize: "<?php echo ObtenerVariable("MaxSizeFile");?>", // MB
	 	maxFiles: "<?php echo ObtenerVariable("CantidadArchivos");?>",
		uploadMultiple: true,
		addRemoveLinks: true,
		dictRemoveFile: "Quitar",
	 	acceptedFiles: "<?php echo ObtenerVariable("TiposArchivos");?>",
		dictDefaultMessage: "<strong>Haga clic aqui para cargar anexos</strong><br>Tambien puede arrastrarlos hasta aqui<br><h4><small>(máximo <?php echo ObtenerVariable("CantidadArchivos");?> archivos a la vez)<small></h4>",
		dictFallbackMessage: "Tu navegador no soporta cargue de archivos mediante arrastrar y soltar",
	 	removedfile: function(file) {
		  $.get( "includes/procedimientos.php", {
			type: "3",
		  	nombre: file.name
		  }).done(function( data ) {
		 	var _ref;
		  	return (_ref = file.previewElement) !== null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
		 	});
		 }
	};
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>