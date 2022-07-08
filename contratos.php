<?php require_once("includes/conexion.php");
PermitirAcceso(506);

$sw_ext=0;//Sw que permite saber si la ventana esta abierta en modo pop-up. Si es así, no cargo el menú ni el menú superior.
$IdContrato="";
$msg_error="";//Mensaje del error
$BloqEdit=0;//Saber si puede editar el contrato. 1 Bloqueado para editar. 0 Se puede editar.
$Metod="";

if(isset($_GET['id'])&&($_GET['id']!="")){
	$IdContrato=base64_decode($_GET['id']);
}

if(isset($_GET['ext'])&&($_GET['ext']==1)){
	$sw_ext=1;//Se está abriendo como pop-up
}elseif(isset($_POST['ext'])&&($_POST['ext']==1)){
	$sw_ext=1;//Se está abriendo como pop-up
}else{
	$sw_ext=0;
}

if(isset($_GET['metod'])&&($_GET['metod']!="")){
	$Metod=base64_decode($_GET['metod']);
}

if(isset($_GET['tl'])&&($_GET['tl']!="")){//0 Creando una actividad. 1 Editando actividad.
	$edit=$_GET['tl'];
}elseif(isset($_POST['tl'])&&($_POST['tl']!="")){
	$edit=$_POST['tl'];
}else{
	$edit=0;
}

if(isset($_POST['swError'])&&($_POST['swError']!="")){//Para saber si ha ocurrido un error.
	$sw_error=$_POST['swError'];
}else{
	$sw_error=0;
}

if($edit==0){
	$Title="Crear contrato";
}else{
	$Title="Editar contrato";
}

if(isset($_POST['P'])&&($_POST['P']!="")){
	try{
		
		//Carpeta de archivos anexos
		$i=0;//Archivos
		$RutaAttachSAP=ObtenerDirAttach();
		$dir=CrearObtenerDirTemp();
		$dir_firma=CrearObtenerDirTempFirma();
		$dir_new=CrearObtenerDirAnx("contratos");
		
		if((isset($_POST['SigCliente']))&&($_POST['SigCliente']!="")){
			$NombreFileFirma=base64_decode($_POST['SigCliente']);
			$Nombre_Archivo=$_POST['LicTradNum']."_FR_".$NombreFileFirma;
			if(!copy($dir_firma.$NombreFileFirma,$dir.$Nombre_Archivo)){
				$sw_error=1;
				$msg_error="No se pudo mover la firma";
			}
		}
			
		$route= opendir($dir);
		$DocFiles=array();
		while ($archivo = readdir($route)){ //obtenemos un archivo y luego otro sucesivamente
			if(($archivo == ".")||($archivo == "..")) continue;

			if (!is_dir($archivo)){//verificamos si es o no un directorio
				$DocFiles[$i]=$archivo;
				$i++;
				}
		}
		closedir($route);
		$CantFiles=count($DocFiles);
		
		
		
		$Metodo=2;//Actualizar en el web services
		$Type=2;//Ejecutar actualizar en el SP
		$IdContratoPortal="NULL";
		
		if($_POST['tl']==0){//Creando Contrato
			$Metodo=1;
			$Type=1;
		}else{//Actualizando el contrato
			if($_POST['IdContratoPortal']==""){//Insertando en la tabla
				$Type=1;
				$Metodo=2;
			}else{
				$IdContratoPortal="'".base64_decode($_POST['IdContratoPortal'])."'";
			}
		}
		
		$ParamCont=array(
			"$IdContratoPortal",
			"'".$_POST['ContractID']."'",
			"'".$_POST['Cliente']."'",
			"'".$_POST['NombreCliente']."'",
			"'".$_POST['Descripcion']."'",
			"'".$_POST['LicTradNum']."'",
			"'".$_POST['ContactoCliente']."'",
			"'".$_POST['TipoContServicio']."'",
			"'".FormatoFecha($_POST['FechaInicio'])."'",
			"'".FormatoFecha($_POST['FechaFinal'])."'",
			"'".FormatoFecha($_POST['FechaRescision'])."'",
			"'".$_POST['TipoServicio']."'",
			"'".$_POST['TipoContrato']."'",
			"'".$_POST['ModeloContrato']."'",
			"'".$_POST['EstadoContrato']."'",
			"'".$_POST['Proyecto']."'",
			"'".$_POST['ContratoImp']."'",
			"'".$_POST['SucursalCliente']."'",
			"'".$_POST['DireccionSucursal']."'",
			"'".$_POST['VigenciaCont']."'",
			"'".$_POST['RenovacionAuto']."'",
			"'".$_POST['Verificado']."'",
			$Metodo,
			"'".$_SESSION['CodUser']."'",
			$Type
		);
		$SQL_Cont=EjecutarSP('sp_tbl_Contratos',$ParamCont,$_POST['P']);
		if($SQL_Cont){			
			if(base64_decode($_POST['IdContratoPortal'])==""){
				$row_NewIdContrato=sqlsrv_fetch_array($SQL_Cont);
				$IdContrato=$row_NewIdContrato[0];
			}else{
				$IdContrato=base64_decode($_POST['IdContratoPortal']);
			}
			
			try{
				//Mover los anexos a la carpeta de archivos de SAP
				$Delete="Delete From tbl_DocumentosSAP_Anexos Where TipoDocumento=190 and Metodo=1 and ID_Documento='".$IdContrato."'";
				sqlsrv_query($conexion,$Delete);
				$j=0;
				while($j<$CantFiles){
					$Archivo=FormatoNombreAnexo($DocFiles[$j]);
					$NuevoNombre=$Archivo[0];
					$OnlyName=$Archivo[1];
					$Ext=$Archivo[2];

					if(file_exists($dir_new)){
						copy($dir.$DocFiles[$j],$dir_new.$NuevoNombre);
						copy($dir_new.$NuevoNombre,$RutaAttachSAP[0].$NuevoNombre);

						//Registrar archivo en la BD
						$ParamInsAnex=array(
							"'190'",
							"'".$IdContrato."'",
							"'".$OnlyName."'",
							"'".$Ext."'",
							"1",
							"'".$_SESSION['CodUser']."'",
							"1"
						);
						$SQL_InsAnex=EjecutarSP('sp_tbl_DocumentosSAP_Anexos',$ParamInsAnex,$_POST['P']);
						if(!$SQL_InsAnex){
							$sw_error=1;
							$msg_error="Error al insertar los anexos.";
						}
					}
					$j++;
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}

				//Enviar datos al WebServices
				try{
					$Parametros=array(
						'pIdContrato' => $IdContrato,
						'pLogin'=>$_SESSION['User']
					);
					$Resultado=EnviarWebServiceSAP('AppPortal_InsertarContratoServicio',$Parametros,true);
					if($Resultado->Success==0){
						$sw_error=1;
						$msg_error=$Resultado->Mensaje;
					}else{
						if($_POST['tl']==0){//Mensaje para devuelta
							$Msg=base64_encode("OK_ContAdd");
						}else{
							$Msg=base64_encode("OK_ContEdit");
						}

						sqlsrv_close($conexion);
						header('Location:'.base64_decode($_POST['pag'])."?".base64_decode($_POST['return']).'&a='.$Msg);
					}
				}catch (Exception $e) {
					echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
				}
		}else{
			$sw_error=1;
			$msg_error="Ha ocurrido un error al crear el contrato";
		}
	}catch (Exception $e){
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if($edit==1&&$sw_error==0){//Editando la actividad
	$SQL=Seleccionar('uvw_Sap_tbl_Contratos','*',"ID_Contrato='".$IdContrato."'");
	$row=sqlsrv_fetch_array($SQL);
	
	//Validar si tiene permitido editar el contrato
	if(!PermitirFuncion(507)){
		$BloqEdit=1;
	}
	
	//Clientes	
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","CodigoCliente='".$row['CodigoCliente']."'",'NombreCliente');	
	
	//Contactos clientes
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".$row['CodigoCliente']."'",'NombreContacto');

	//Sucursales
	$sw_dirS=0;
	$sw_dirB=0;
	$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".$row['CodigoCliente']."'",'TipoDireccion, NombreSucursal');

	//Anexos
	$SQL_AnexoActividad=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','*',"AbsEntry='".$row['IdAnexos']."'");

}

if($sw_error==1){
	//Si ocurre un error, vuelvo a consultar los datos insertados desde la base de datos.
	$SQL=Seleccionar('uvw_tbl_Actividades','*',"ID_Actividad='".$IdContrato."'");
	$row=sqlsrv_fetch_array($SQL);
	
	//Validar si es el creador de la actividad
	if(!PermitirFuncion(308)){
		if($row['ID_Usuario']!=$_SESSION['CodUser']){
			$BloqEdit=1;
		}	
	}
	
	//Clientes
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreCliente');
	
	//Contactos clientes
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreContacto');

	//Sucursales
	$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreSucursal');

	//Asunto actividad
	$SQL_AsuntoActividad=Seleccionar('uvw_Sap_tbl_AsuntosActividad','*',"Id_TipoActividad='".$row['ID_TipoActividad']."'",'DE_AsuntoActividad');
	
	//Documentos asociados
	$ParametrosDoc=array(
		"'".$row['DocMarkDocType']."'",
		"'".$row['ID_CodigoCliente']."'"
	);
	$SQL_DocMarketing=EjecutarSP('sp_ConsultarDocMarketing',$ParametrosDoc);
	
	//Orden de servicio
	$SQL_OrdenServicioCliente=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_CodigoCliente='".$row['ID_CodigoCliente']."' and NombreSucursal='".$row['NombreSucursal']."'",'AsuntoLlamada');
}

//Clase contrato
$SQL_ClaseContrato=Seleccionar('uvw_tbl_Contratos_Clase','*');

//Tipo de servicio
$SQL_TipoServicio=Seleccionar('uvw_tbl_Contratos_TipoServicio','*');

//Tipo contrato
$SQL_TipoContrato=Seleccionar('uvw_tbl_Contratos_TipoContrato','*');

//Estado contrato
$SQL_Estado=Seleccionar('uvw_tbl_EstadoContrato','*','','DeEstadoContrato');

//Modelos de contrato
$SQL_ModeloCont=Seleccionar('uvw_Sap_tbl_ContratosModelos','*','','ID_ModeloContrato');

//Vigencia de contratos
$SQL_VigenciaServ=Seleccionar('uvw_Sap_tbl_ContratosVigencia','*','','IdVigenciaServ');

//Proyectos
$SQL_Proyecto=Seleccionar('uvw_Sap_tbl_Proyectos','*','','DE_Proyecto');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $Title;?> | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ContAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El contrato ha sido agregado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ContEdit"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El contrato ha sido actualizado exitosamente.',
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
                icon: 'error'
            });
		});		
		</script>";
}
?>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#Cliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Cliente=document.getElementById('Cliente').value;
			var Sucursal=document.getElementById('SucursalCliente').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:18,CardCode:Cliente},
				dataType:'json',
				success: function(data){
					document.getElementById('LicTradNum').value=data.LicTradNum;
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+Cliente,
				success: function(response){
					$('#ContactoCliente').html(response).fadeIn();
					$('#ContactoCliente').trigger('change');
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&id="+Cliente,
				success: function(response){
					$('#SucursalCliente').html(response).fadeIn();
					$('#SucursalCliente').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
		$("#SucursalCliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Cliente=document.getElementById('Cliente').value;
			var Sucursal=document.getElementById('SucursalCliente').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:1,CardCode:Cliente,Sucursal:Sucursal},
				dataType:'json',
				success: function(data){
					document.getElementById('DireccionSucursal').value=data.Direccion;
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
		
		CapturarGPS();
		
	});
</script>
<script>
function CapturarGPS(){
	var Latitud=document.getElementById("Latitud");
	var Longitud=document.getElementById("Longitud");
	//var CoordGPS=document.getElementById("CoordGPS");
	if ("geolocation" in navigator){//check geolocation available 
		//try to get user current location using getCurrentPosition() method
		navigator.geolocation.getCurrentPosition(function(position){
			Latitud.value=position.coords.latitude;
			Longitud.value=position.coords.longitude;
			//CoordGPS.innerHTML=Latitud.value + "," +Longitud.value;
		});
	}else{
		console.log("Navegador no soporta geolocalizacion");
		//CoordGPS.innerHTML='No está activado el GPS';
	}
}	

function ConsultarDatosCliente(){
	var Cliente=document.getElementById('Cliente');
	if(Cliente.value!=""){
		self.name='opener';
		remote=open('socios_negocios.php?id='+Base64.encode(Cliente.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
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
                    <h2><?php echo $Title;?></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Socios de negocios</a>
                        </li>
                        <li>
                            <a href="consultar_contratos.php">Consultar contratos</a>
                        </li>
                        <li class="active">
                            <strong><?php echo $Title;?></strong>
                        </li>
                    </ol>
                </div>
            </div>
           
      <div class="wrapper wrapper-content">
		 <form action="contratos.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="EditarSN">  
		 	<div class="row">
				<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<div class="col-lg-12">
						<?php 			
						if(isset($_GET['return'])){
							$_GET['return']=base64_decode($_GET['return']);
							$_GET['return']=QuitarParametrosURL($_GET['return'],array("a"));
							$return=base64_decode($_GET['pag'])."?".$_GET['return'];						
						}else{
							$return="consultar_contratos.php?";
						}
						$return=QuitarParametrosURL($return,array("a"));
						?>

						<input type="hidden" id="P" name="P" value="53" />
						<input type="hidden" id="swError" name="swError" value="<?php echo $sw_error;?>" />
						<input type="hidden" id="tl" name="tl" value="<?php echo $edit;?>" />		
						<input type="hidden" id="ext" name="ext" value="<?php echo $sw_ext;?>" />
						<input type="hidden" id="ID_Contrato" name="ID_Contrato" value="<?php if($edit==1){ echo base64_encode($row['ID_Contrato']);}?>" />
						<input type="hidden" id="IdContratoPortal" name="IdContratoPortal" value="<?php if($edit==1){ echo base64_encode($row['IdContratoPortal']);}?>" />
						<input type="hidden" id="pag" name="pag" value="<?php if(isset($_REQUEST['pag'])){echo $_REQUEST['pag'];}else{echo base64_encode("contratos.php");}//viene de afuera ?>" />
						<input type="hidden" id="Latitud" name="Latitud" value="" />
						<input type="hidden" id="Longitud" name="Longitud" value="" />
						<input type="hidden" id="return" name="return" value="<?php if(isset($_GET['return'])){echo base64_encode($_GET['return']);}else{echo base64_encode($_SERVER['QUERY_STRING']);}?>" />

						<?php if(($edit==1)&&(PermitirFuncion(507))){?> 
							<button class="btn btn-warning" form="EditarSN" type="submit" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar contrato</button> 
						<?php }?>
						<?php if($edit==0){?> 
							<button class="btn btn-primary" form="EditarSN" type="submit" id="Crear"><i class="fa fa-check"></i> Crear contrato</button>  
							<?php }?>
						<?php if($sw_ext==0){?>
							<a href="<?php echo $return;?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
						<?php }?>
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
						<li class="active"><a data-toggle="tab" href="#tabCT-1"><i class="fa fa-info-circle"></i> Información general</a></li>
						<?php if($edit==1){?><li><a data-toggle="tab" href="#tabCT-2" onClick="ConsultarTab('2');"><i class="fa fa-phone"></i> Llamadas de servicios</a></li><?php }?>
						<li><a data-toggle="tab" href="#tabCT-3" onClick="ConsultarTab('3');"><i class="fa fa-paperclip"></i> Anexos</a></li>
					</ul>
					<div class="tab-content">
						<div id="tabCT-1" class="tab-pane active">
						<br>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-user"></i> Información del cliente</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label"><i onClick="ConsultarDatosCliente();" title="Consultar cliente" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Cliente</label>
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if(($edit==1)||($sw_error==1)){echo $row['CodigoCliente'];}?>">
								<input name="NombreCliente" type="text" required="required" class="form-control" id="NombreCliente" placeholder="Digite para buscar..." value="<?php if(($edit==1)||($sw_error==1)){echo $row['NombreCliente'];}?>" <?php if($BloqEdit==1){echo "readonly='readonly'";}?>>
							</div>
							<label class="col-lg-1 control-label">NIT/Cédula</label>
							<div class="col-lg-2">
								<input name="LicTradNum" type="text" class="form-control" id="LicTradNum" value="<?php if($edit==1){echo $row['LicTradNum'];}?>" <?php if($BloqEdit==1){echo "readonly";}?>>
							</div>	
							<label class="col-lg-1 control-label">Contacto</label>
							<div class="col-lg-3">
								<select name="ContactoCliente" class="form-control m-b" id="ContactoCliente" <?php if($BloqEdit==1){ echo "disabled='disabled'";}?>>
									<option value="">Seleccione...</option>
									<?php if(($edit==1)||($sw_error==1)){while($row_ContactoCliente=sqlsrv_fetch_array($SQL_ContactoCliente)){?>
										<option value="<?php echo $row_ContactoCliente['CodigoContacto'];?>" <?php if((isset($row['IdContacto']))&&(strcmp($row_ContactoCliente['CodigoContacto'],$row['IdContacto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto'];?></option>
								  <?php }}?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Sucursal</label>
							<div class="col-lg-3">
								<select name="SucursalCliente" class="form-control m-b select2" id="SucursalCliente" <?php if($BloqEdit==1){ echo "disabled='disabled'";}?>>
									<option value="">Seleccione...</option>
									<?php if(($edit==1)||($sw_error==1)){while($row_SucursalCliente=sqlsrv_fetch_array($SQL_SucursalCliente)){
										if(($row_SucursalCliente['TipoDireccion']=="B")&&($sw_dirB==0)){
											echo "<optgroup label='Dirección de facturas'></optgroup>";
											$sw_dirB=1;
										}elseif(($row_SucursalCliente['TipoDireccion']=="S")&&($sw_dirS==0)){
											echo "<optgroup label='Dirección de destino'></optgroup>";
											$sw_dirS=1;
										}?>
										<option value="<?php echo $row_SucursalCliente['NombreSucursal'];?>" <?php if((isset($row['CDU_NombreSucursal']))&&(strcmp($row_SucursalCliente['NombreSucursal'],$row['CDU_NombreSucursal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SucursalCliente['NombreSucursal'];?></option>
								  <?php }}?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Dirección sucursal</label>
							<div class="col-lg-4">
								<input name="DireccionSucursal" type="text" class="form-control" id="DireccionSucursal" value="<?php if($edit==1){echo $row['CDU_DirSucursal'];}?>" <?php if($BloqEdit==1){echo "readonly";}?>>
							</div>	
						</div>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-handshake-o"></i> Información del contrato</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">No Contrato</label>
							<div class="col-lg-2">
								<input name="ContractID" type="text" readonly class="form-control" id="ContractID" value="<?php if($edit==1){echo $row['ID_Contrato'];}?>">
							</div>
							<label class="col-lg-1 control-label">No contrato impreso</label>
							<div class="col-lg-2">
								<input name="ContratoImp" type="text" class="form-control" id="ContratoImp" value="<?php if($edit==1){echo $row['CDU_NoContratoImp'];}?>" <?php if($BloqEdit==1){echo "readonly";}?>>
							</div>	
							<label class="col-lg-1 control-label">Tipo contrato de servicio</label>
							<div class="col-lg-2">
								<select name="TipoContServicio" class="form-control m-b" id="TipoContServicio" required <?php if($BloqEdit==1){echo "disabled='disabled'";}?>>
									<?php
									while($row_ClaseContrato=sqlsrv_fetch_array($SQL_ClaseContrato)){?>
										<option value="<?php echo $row_ClaseContrato['IdClaseContrato'];?>" <?php if((isset($row['IdClaseContrato']))&&(strcmp($row_ClaseContrato['IdClaseContrato'],$row['IdClaseContrato'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ClaseContrato['DeClaseContrato'];?></option>
									<?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Proyecto</label>
							<div class="col-lg-4">
								<select name="Proyecto" class="form-control m-b select2" id="Proyecto">
									<option value="">(Ninguno)</option>
								<?php
									while($row_Proyecto=sqlsrv_fetch_array($SQL_Proyecto)){?>
										<option value="<?php echo $row_Proyecto['ID_Proyecto'];?>" <?php if((isset($row['IdProyecto']))&&(strcmp($row_Proyecto['ID_Proyecto'],$row['IdProyecto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Proyecto['DE_Proyecto'];?></option>
								<?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Descripción del contrato</label>
							<div class="col-lg-5">
								<input name="Descripcion" type="text" class="form-control" id="Descripcion" value="<?php if($edit==1){echo $row['DE_Contrato'];}?>" <?php if($BloqEdit==1){echo "readonly";}?>>
							</div>		
						</div>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-briefcase"></i> Información contractual</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fecha inicio</label>
							<div class="col-lg-2 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaInicio" type="text" required="required" class="form-control" id="FechaInicio" value="<?php if($edit==1){if(is_object($row['FechaInicioContrato'])){ echo $row['FechaInicioContrato']->format('Y-m-d');}else{echo $row['FechaInicioContrato'];}}?>" placeholder="YYYY-MM-DD" <?php if($BloqEdit==1){echo "readonly";}?>>
							</div>
							<label class="col-lg-1 control-label">Fecha final</label>
							<div class="col-lg-2 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaFinal" type="text" required="required" class="form-control" id="FechaFinal" value="<?php if($edit==1){if(is_object($row['FechaFinContrato'])){ echo $row['FechaFinContrato']->format('Y-m-d');}else{echo $row['FechaFinContrato'];}}?>" placeholder="YYYY-MM-DD" <?php if($BloqEdit==1){echo "readonly";}?>>
							</div>
							<label class="col-lg-1 control-label">Fecha rescisión</label>
							<div class="col-lg-2 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaRescision" type="text" class="form-control" id="FechaRescision" value="<?php if($edit==1&&$row['FechaRescisionContrato']!=""){if(is_object($row['FechaRescisionContrato'])){echo $row['FechaRescisionContrato']->format('Y-m-d');}else{echo $row['FechaRescisionContrato'];}}?>" placeholder="YYYY-MM-DD" <?php if($BloqEdit==1){echo "readonly";}?>>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Vigencia</label>
							<div class="col-lg-2">
								<select name="VigenciaCont" class="form-control m-b" id="VigenciaCont" required <?php if($BloqEdit==1){echo "disabled='disabled'";}?>>
								<?php
									while($row_VigenciaServ=sqlsrv_fetch_array($SQL_VigenciaServ)){?>
										<option value="<?php echo $row_VigenciaServ['IdVigenciaServ'];?>" <?php if((isset($row['CDU_VigServicio']))&&(strcmp($row_VigenciaServ['IdVigenciaServ'],$row['CDU_VigServicio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_VigenciaServ['DeVigenciaServ'];?></option>
								<?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Tipo de servicio</label>
							<div class="col-lg-2">
								<select name="TipoServicio" class="form-control m-b" id="TipoServicio" required <?php if($BloqEdit==1){echo "disabled='disabled'";}?>>
									<option value="">Seleccione...</option>
								<?php
									while($row_TipoServicio=sqlsrv_fetch_array($SQL_TipoServicio)){?>
										<option value="<?php echo $row_TipoServicio['IdTipoServicio'];?>" <?php if((isset($row['IdTipoServicio']))&&(strcmp($row_TipoServicio['IdTipoServicio'],$row['IdTipoServicio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoServicio['DeTipoServicio'];?></option>
								<?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Tipo contrato</label>
							<div class="col-lg-2">
								<select name="TipoContrato" class="form-control m-b" id="TipoContrato" required <?php if($BloqEdit==1){echo "disabled='disabled'";}?>>
									<option value="">Seleccione...</option>
								<?php
									while($row_TipoContrato=sqlsrv_fetch_array($SQL_TipoContrato)){?>
										<option value="<?php echo $row_TipoContrato['IdTipoContrato'];?>" <?php if((isset($row['IdTipoContrato']))&&(strcmp($row_TipoContrato['IdTipoContrato'],$row['IdTipoContrato'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoContrato['DeTipoContrato'];?></option>
								<?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Renovación automática</label>
							<div class="col-lg-2">
								<select name="RenovacionAuto" class="form-control m-b" id="RenovacionAuto" required <?php if($BloqEdit==1){echo "disabled='disabled'";}?>>
									<option value="SI" <?php if((isset($row['RenovacionAuto']))&&(strcmp("SI",$row['RenovacionAuto'])==0)){ echo "selected=\"selected\"";}?>>SI</option>
									<option value="NO" <?php if((isset($row['RenovacionAuto']))&&(strcmp("NO",$row['RenovacionAuto'])==0)){ echo "selected=\"selected\"";}?>>NO</option>
								</select>
							</div>
							<label class="col-lg-1 control-label">Verificado</label>
							<div class="col-lg-2">
								<select name="Verificado" class="form-control m-b" id="Verificado" required <?php if($BloqEdit==1){echo "disabled='disabled'";}?>>
									<option value="SI" <?php if((isset($row['Verificado']))&&(strcmp("SI",$row['Verificado'])==0)){ echo "selected=\"selected\"";}?>>SI</option>
									<option value="NO" <?php if((isset($row['Verificado']))&&(strcmp("NO",$row['Verificado'])==0)){ echo "selected=\"selected\"";}?>>NO</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Modelo de contrato</label>
							<div class="col-lg-4">
								<select name="ModeloContrato" class="form-control m-b" id="ModeloContrato" required <?php if($BloqEdit==1){echo "disabled='disabled'";}?>>
									<option value="">Seleccione...</option>
								<?php
									while($row_ModeloCont=sqlsrv_fetch_array($SQL_ModeloCont)){?>
										<option value="<?php echo $row_ModeloCont['ID_ModeloContrato'];?>" <?php if((isset($row['IdModeloContrato']))&&(strcmp($row_ModeloCont['ID_ModeloContrato'],$row['IdModeloContrato'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ModeloCont['ID_ModeloContrato'];?></option>
								<?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Estado</label>
							<div class="col-lg-2">
								<select name="EstadoContrato" class="form-control m-b" id="EstadoContrato" required <?php if($BloqEdit==1){echo "disabled='disabled'";}?>>
									<option value="">Seleccione...</option>
								<?php
									while($row_Estado=sqlsrv_fetch_array($SQL_Estado)){?>
										<option value="<?php echo $row_Estado['IdEstadoContrato'];?>" <?php if((isset($row['IdEstadoContrato']))&&(strcmp($row_Estado['IdEstadoContrato'],$row['IdEstadoContrato'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Estado['DeEstadoContrato'];?></option>
								<?php }?>
								</select>
							</div>
						</div>
						</div>
						<?php if($edit==1){?>
						<div id="tabCT-2" class="tab-pane">
							<div id="dv_llamadasrv" class="panel-body">
								
							</div>																	
						</div>
						<?php }?>
						<div id="tabCT-3" class="tab-pane">
							<div id="dv_anexos" class="panel-body">

							</div>																		
						</div>
					</div>
				</div>
				
		   		</div>
			</div>
          </div>
		  </form>
	</div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
	 $(document).ready(function(){
		 $("#EditarSN").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
			}
		});
		 $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			});
		 <?php if($BloqEdit==0){?>
		 $('#FechaInicio').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
			 	todayHighlight: true,
				format: 'yyyy-mm-dd'
            });
		 $('#FechaFinal').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
			 	todayHighlight: true,
				format: 'yyyy-mm-dd'
            });
		 $('#FechaRescision').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
			 	todayHighlight: true,
				format: 'yyyy-mm-dd'
            });
	 	<?php }?>

		 $(".select2").select2();
		 $('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
             radioClass: 'iradio_square-green',
          });
		 var options = {
			url: function(phrase) {
				return "ajx_buscar_datos_json.php?type=7&id="+phrase;
			},

			getValue: "NombreBuscarCliente",
			requestDelay: 400,
			list: {
				match: {
					enabled: true
				},
				onClickEvent: function() {
					var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
					$("#Cliente").val(value).trigger("change");
				}
			}
		};
		<?php if($BloqEdit==0){?>
		$("#NombreCliente").easyAutocomplete(options);
	 	<?php }?>
	});
</script>
<script>
//Variables de tab
var tab_2=0;
var tab_3=0;
	
function ConsultarTab(type){
	if(type==2){//Llamada de servicio
		if(tab_2==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "sn_llamadas_servicios.php?id=<?php if($edit==1){echo base64_encode($row['ID_Contrato']);}?>&objtype=190",
				success: function(response){
					$('#dv_llamadasrv').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_2=1;
				}
			});
		}
	}else if(type==3){//Anexos
		if(tab_3==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			//CapturarGPS();
			//var Latitud=document.getElementById("Latitud");
			//var Longitud=document.getElementById("Longitud");
			$.ajax({
				type: "POST",
				url: "sn_anexos.php?id=<?php if($edit==1){echo base64_encode($row['CodigoCliente']);}?>&edit=<?php echo $edit; ?>&anx=<?php if($edit==1){echo base64_encode($row['IdAnexos']);}?>&metod=<?php echo $Metod; ?>&esproyecto=&pediranexos=SI",
				success: function(response){
					$('#dv_anexos').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_3=1;
				}
			});
		}
	}
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>