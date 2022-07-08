<?php require_once("includes/conexion.php");
$type_llmd=0; //0 Creando una llamada de servicio. 1 Editando llamada de servicio.
$sw_error=0;//Para saber si ha ocurrido un error.
$msg_error="";//Mensaje del error
if(isset($_POST['P'])&&($_POST['P']==32)){
	//Insertar llamada de servicio
	try{
		//*** Carpeta temporal ***
		$i=0;//Archivos
		$temp=ObtenerVariable("CarpetaTmp");
		$carp_archivos=ObtenerVariable("RutaArchivos");
		$carp_anexos="llamadas";
		$NuevoNombre="";
		$RutaAttachSAP=ObtenerDirAttach();
		$dir=$temp."/".$_SESSION['CodUser']."/";		   
		$route= opendir($dir);
		//$directorio = opendir("."); //ruta actual
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
		
		//Insertar el registro en la BD
		if($_POST['swTipo']==1){
			$ClienteLlamada=base64_decode($_POST['ClienteLlamadaInterno']);
			$SucursalLlamada=base64_decode($_POST['SucursalClienteInterno']);
			//Direccion
			$SQL_DirLlamada=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','Direccion, Ciudad',"CodigoCliente=''".$ClienteLlamada."'' and NombreSucursal=''".$SucursalLlamada."''");
			$row_DirLlamada=sqlsrv_fetch_array($SQL_DirLlamada);
			$DireccionLlamada=$row_DirLlamada['Direccion'];
			$CiudadLlamada=$row_DirCliente['Ciudad'];
			
			//Contacto
			$SQL_ContLlamada=Seleccionar('uvw_Sap_tbl_ClienteContactos','CodigoContacto',"CodigoCliente=''".$ClienteLlamada."''");
			$row_ContLlamada=sqlsrv_fetch_array($SQL_ContLlamada);
			$ContactoCliente=$row_ContLlamada[0];
		}else{
			$ClienteLlamada=$_POST['ClienteLlamada'];
			$SucursalLlamada=$_POST['SucursalCliente'];
			$DireccionLlamada=$_POST['DireccionLlamada'];
			$CiudadLlamada=$_POST['CiudadLlamada'];
			$ContactoCliente=$_POST['ContactoCliente'];
		}
		
		$ParamInsLlamada=array(
			"NULL",
			"NULL",
			"NULL",
			"'".$_POST['TipoTarea']."'",
			"'".$_POST['AsuntoLlamada']."'",
			"'".$_POST['Series']."'",
			"'".$_POST['EstadoLlamada']."'",
			"'".$_POST['TipoLlamada']."'",
			"'".$_POST['TipoProblema']."'",
			"'".$_POST['SubTipoProblema']."'",
			"'".$ClienteLlamada."'",
			"'".$ContactoCliente."'",
			"'".$_POST['TelefonoLlamada']."'",
			"'".$_POST['CorreoLlamada']."'",
			"'".$_POST['ArticuloLlamada']."'",
			"'".$SucursalLlamada."'",
			"'".$DireccionLlamada."'",
			"'".$CiudadLlamada."'",
			"'".$_POST['BarrioDireccionLlamada']."'",
			"'".$_POST['EmpleadoLlamada']."'",
			"'".LSiqmlObs($_POST['ComentarioLlamada'])."'",
			"'".LSiqmlObs($_POST['ResolucionLlamada'])."'",
			"'".$_POST['FechaCierre']." ".$_POST['HoraCierre']."'",
			"'".$_POST['TipoResolucion']."'",
			"'".$_POST['EstadoServicio']."'",
			"'".$_POST['CanceladoPor']."'",
			"'".$_POST['CategoriaOrigen']."'",
			"'".$_POST['Indisponibilidad']."'",
			"'".$_POST['Responsabilidad']."'",
			"'".$_POST['ColaLlamada']."'",
			"1",
			"'".$_SESSION['CodUser']."'",
			"'".$_SESSION['CodUser']."'",
			"1"
		);
		$SQL_InsLlamada=EjecutarSP('sp_tbl_LlamadaServicios',$ParamInsLlamada,32);		
		if($SQL_InsLlamada){
			$row_NewIdLlamada=sqlsrv_fetch_array($SQL_InsLlamada);
			
			try{
				//Mover los anexos a la carpeta de archivos de SAP
				$j=0;
				while($j<$CantFiles){
					//Sacar la extension del archivo
					$Ext = end(explode('.',$DocFiles[$j]));
					//Sacar el nombre sin la extension
					$OnlyName = substr($DocFiles[$j],0,strlen($DocFiles[$j])-(strlen($Ext)+1));
					//Reemplazar espacios
					$OnlyName=str_replace(" ","_",$OnlyName);
					$Prefijo = substr(uniqid(rand()),0,3);
					$OnlyName=LSiqmlObs($OnlyName)."_".date('Ymd').$Prefijo;
					$NuevoNombre=$OnlyName.".".$Ext;

					$dir_new=$_SESSION['BD']."/".$carp_archivos."/".$carp_anexos."/";
					if(!file_exists($dir_new)){
						mkdir($dir_new,0777, true);
					}
					if(file_exists($dir_new)){
						copy($dir.$DocFiles[$j],$dir_new.$NuevoNombre);
						//move_uploaded_file($_FILES['FileArchivo']['tmp_name'],$dir_new.$NuevoNombre);
						copy($dir_new.$NuevoNombre,$RutaAttachSAP[0].$NuevoNombre);

						//Registrar archivo en la BD
						$ParamInsAnex=array(
							"'191'",
							"'".$row_NewIdLlamada[0]."'",
							"'".$OnlyName."'",
							"'".$Ext."'",
							"1",
							"'".$_SESSION['CodUser']."'",
							"1"					
						);
						$SQL_InsAnex=EjecutarSP('sp_tbl_DocumentosSAP_Anexos',$ParamInsAnex,32);
						if(!$SQL_InsAnex){
							$sw_error=1;
							$msg_error="Error al crear la llamada de servicio";
						}
					}
					$j++;
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}			
			
			//Enviar datos al WebServices
			try{
				require_once("includes/conect_ws.php");
				$Parametros=array(
					'pIdLlamada' => $row_NewIdLlamada[0]
				);
				$Client->InsertarLlamadaServicioPortal($Parametros);
				$Respuesta=$Client->__getLastResponse();
				$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);
				$espaciosDeNombres = $Contenido->getNamespaces(true);
				$Nodos = $Contenido->children($espaciosDeNombres['s']);
				$Nodo=	$Nodos->children($espaciosDeNombres['']);
				$Nodo2=	$Nodo->children($espaciosDeNombres['']);
				
				$Archivo=json_decode($Nodo2[0],true);
				if($Archivo['ID_Respuesta']=="0"){
					//InsertarLog(1, 0, 'Error al generar el informe');
					//throw new Exception('Error al generar el informe. Error de WebServices');		
					$sw_error=1;
					$msg_error=$Archivo['DE_Respuesta'];
				}else{
					sqlsrv_close($conexion);
					header('Location:gestionar_llamadas_servicios.php?a='.base64_encode("OK_LlamAdd"));	
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}			
		}else{
			$sw_error=1;
			$msg_error="Error al crear la llamada de servicio";
		}
	}catch (Exception $e) {
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
}

PermitirAcceso(302);

if($sw_error==1){
	//Si ocurre un error, vuelvo a consultar los datos insertados desde la base de datos.
	$SQL=Seleccionar('uvw_Sap_tbl_LlamadasServicios_DatosMaestros','*',"ID_LlamadaServicio='".base64_decode($row_NewIdLlamada[0])."'");
	$row=sqlsrv_fetch_array($SQL);
	
	//Clientes
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreCliente');	
}

$dt_LS=0;//sw para saber si vienen datos del SN. 0 no vienen. 1 si vienen.
if(isset($_GET['dt_LS'])&&($_GET['dt_LS'])==1){//Verificar que viene de un Socio de negocio (Datos SN)
	$dt_LS=1;
	
	//Clientes
	$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Clientes','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreCliente');
	$row_Cliente=sqlsrv_fetch_array($SQL_Cliente);
	
	//Contacto cliente
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreContacto');
		
	//Sucursal cliente
	$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreSucursal');
}

//Tipo de llamada
$SQL_TipoLlamadas=Seleccionar('uvw_Sap_tbl_TipoLlamadas','*','','DeTipoLlamada');

//Serie de llamada
$SQL_Series=Seleccionar('uvw_Sap_tbl_SeriesLlamadas','*','','DeSeries');

//Tipo problema llamadas
$SQL_TipoProblema=Seleccionar('uvw_Sap_tbl_TipoProblemasLlamadas','*','','DeTipoProblemaLlamada');

//SubTipo problema llamadas
$SQL_SubTipoProblema=Seleccionar('uvw_Sap_tbl_SubTipoProblemasLlamadas','*','','DeSubTipoProblemaLlamada');

//Categoria origen llamada
$SQL_CatOrigen=Seleccionar('uvw_Sap_tbl_CategoriaOrigenLlamadas','*','','DeCateOrigenLlamada');

//Indisponibilidad llamada
$SQL_IndispoLlamada=Seleccionar('uvw_Sap_tbl_IndisponibilidadLlamadas','*','','DeIndispoLlamada');

//Responsabilidad llamada
$SQL_ResponLlamada=Seleccionar('uvw_Sap_tbl_ResponsabilidadLlamadas','*','','DeResponLlamada');

//Tipo resolucion llamada
$SQL_ResolucionLlamada=Seleccionar('uvw_Sap_tbl_TipoResolucionLlamadas','*','','DeTipoResolucionLlamada');

//Estado servicio llamada
$SQL_EstServLlamada=Seleccionar('uvw_Sap_tbl_EstadoServicioLlamadas','*','','DeEstadoServicioLlamada');

//Cancelar llamada
$SQL_CancelarLlamada=Seleccionar('uvw_Sap_tbl_CancelarLlamadas','*','','DeCancelarLlamada','DESC');

//Cola llamada
$SQL_ColaLlamada=Seleccionar('uvw_Sap_tbl_ColaLlamadas','*','','DeColaLlamada');

//Empleados
$SQL_EmpleadoLlamada=Seleccionar('uvw_Sap_tbl_Empleados','*',"UsuarioSAP <> ''",'NombreEmpleado');

//Estado llamada
$SQL_EstadoLlamada=Seleccionar('uvw_tbl_EstadoLlamada','*');

//Contactos clientes
$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreContacto');

//Sucursales
$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreSucursal');

//Anexos
$SQL_AnexoLlamada=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','*',"AbsEntry='".$row['IdAnexoLlamada']."'");

//Articulos del cliente (ID servicio)
$SQL_Articulos=Seleccionar('uvw_Sap_tbl_ArticulosLlamadas','*',"CodigoCliente='".$row['ID_CodigoCliente']."'",'ItemCode');

//Activides relacionadas
$SQL_Actividad=Seleccionar('uvw_Sap_tbl_Actividades','*',"ID_LlamadaServicio='".base64_decode($row['ID_LlamadaServicio'])."'",'ID_Actividad');

//Ordenes de venta
$SQL_OrdenVenta=Seleccionar('uvw_tbl_OrdenVenta','*',"ID_LlamadaServicio='".base64_decode($row['ID_LlamadaServicio'])."'",'FechaRegistro');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Crear llamada de servicio</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#ClienteLlamada").change(function(){
			$('.ibox-content').toggleClass('sk-loading',"",true);
			var Cliente=document.getElementById('ClienteLlamada').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+Cliente,
				success: function(response){
					$('#ContactoCliente').html(response).fadeIn();
					$('#ContactoCliente').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',"",false);
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&id="+Cliente,
				success: function(response){
					$('#SucursalCliente').html(response).fadeIn();
					$('#SucursalCliente').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',"",false);
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=11&id="+Cliente,
				success: function(response){
					$('#ArticuloLlamada').html(response).fadeIn();
					$('#ArticuloLlamada').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',"",false);
				}
			});
		});
		$("#SucursalCliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',"",true);
			var Cliente=document.getElementById('ClienteLlamada').value;
			var Sucursal=document.getElementById('SucursalCliente').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:1,CardCode:Cliente,Sucursal:Sucursal},
				dataType:'json',
				success: function(data){
					document.getElementById('DireccionLlamada').value=data.Direccion;
					document.getElementById('BarrioDireccionLlamada').value=data.Barrio;
					document.getElementById('CiudadLlamada').value=data.Ciudad;
					$('.ibox-content').toggleClass('sk-loading',"",false);
				}
			});
		});
		$("#ContactoCliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',"",true);
			var Contacto=document.getElementById('ContactoCliente').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:5,Contacto:Contacto},
				dataType:'json',
				success: function(data){
					document.getElementById('TelefonoLlamada').value=data.Telefono;
					document.getElementById('CorreoLlamada').value=data.Correo;
					$('.ibox-content').toggleClass('sk-loading',"",false);
				}
			});
		});
		$("#ArticuloLlamada").change(function(){
			$('.ibox-content').toggleClass('sk-loading',"",true);
			var ID=document.getElementById('ArticuloLlamada').value;
			if(ID!=""){
				$.ajax({
					url:"ajx_buscar_datos_json.php",
					data:{type:6,id:ID},
					dataType:'json',
					success: function(data){
						document.getElementById('GrupoArticulo').value=data.ItmsGrpNam;
						document.getElementById('SucursalCliente').value=data.NombreSucursal;
						document.getElementById('PosicionArticulo').value=data.Posicion;
						document.getElementById('OLTArticulo').value=data.DeOLT;
						$('#SucursalCliente').trigger('change');
						$('.ibox-content').toggleClass('sk-loading',"",false);
					}
				});
			}else{
				document.getElementById('GrupoArticulo').value="";
				$('.ibox-content').toggleClass('sk-loading',"",false);
			}			
		});
		$("#TipoTarea").change(function(){
			var TipoTarea=document.getElementById('TipoTarea').value;
			if(TipoTarea=="Interna"){
				document.getElementById('ClienteLlamada').value='<?php echo NIT_EMPRESA;?>';
				document.getElementById('NombreClienteLlamada').value='<?php echo NOMBRE_EMPRESA;?>';
				document.getElementById('NombreClienteLlamada').readOnly=true;
				$('#ClienteLlamada').trigger('change');
				//HabilitarCampos(0);
			}else{
				document.getElementById('ClienteLlamada').value='';
				document.getElementById('NombreClienteLlamada').value='';
				document.getElementById('NombreClienteLlamada').readOnly=false;
				$('#ClienteLlamada').trigger('change');
				//HabilitarCampos(1);
				
			}		
		});
	});
</script>
<script>
function HabilitarCampos(type=1){
	if(type==0){//Deshabilitar
		document.getElementById('DatosCliente').style.display='none';
		document.getElementById('swTipo').value="1";
	}else{//Habilitar
		document.getElementById('DatosCliente').style.display='block';
		document.getElementById('swTipo').value="0";
	}
}
function ConsultarDatosCliente(){
	var Cliente=document.getElementById('ClienteLlamada');
	if(Cliente.value!=""){
		self.name='opener';
		remote=open('socios_negocios_edit.php?id='+Base64.encode(Cliente.value)+'&ext=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
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
                    <h2>Crear llamada de servicio</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Servicios</a>
                        </li>
                        <li>
                            <a href="gestionar_llamadas_servicios.php">Gestionar llamadas de servicios</a>
                        </li>
                        <li class="active">
                            <strong>Crear llamada de servicio</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <div class="ibox-content">
				 <?php include("includes/spinner.php"); ?>
          <div class="row"> 
           <div class="col-lg-12">
              <form action="registro.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="CrearLlamada">
				<div id="DatosCliente">
				<div class="form-group">
					<label class="col-lg-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-group"></i> Información de cliente</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label"><i onClick="ConsultarDatosCliente();" title="Consultar cliente" style="cursor: pointer" class="btn-outline btn-link fa fa-search"></i> Cliente</label>
					<div class="col-lg-3">
						<input name="ClienteLlamada" type="hidden" id="ClienteLlamada" value="<?php if($dt_LS==1){echo $row_Cliente['CodigoCliente'];}?>">
						<input name="NombreClienteLlamada" type="text" required="required" class="form-control" id="NombreClienteLlamada" placeholder="Digite para buscar..." value="<?php if($sw_error==1){echo $_POST['ClienteLlamada'];}elseif($dt_LS==1){echo $row_Cliente['NombreCliente'];}?>" <?php if($dt_LS==1){ echo "readonly";}?>>
                    	<?php /*?><select name="ClienteLlamada" class="form-control m-b select2" id="ClienteLlamada" required>
								<option value="">Seleccione...</option>
                          <?php while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){?>
								<option value="<?php echo $row_Cliente['CodigoCliente'];?>"><?php echo $row_Cliente['NombreCliente'];?></option>
						  <?php }?>
						</select><?php */?>
               	  	</div>
					<label class="col-lg-1 control-label">Contacto</label>
					<div class="col-lg-3">
						<?php if($type_llmd==0){?>
						<select name="ContactoCliente" class="form-control m-b" id="ContactoCliente">
								<option value="">Seleccione...</option>
						</select>
						<?php }else{?>
							<select name="ContactoCliente" class="form-control m-b" id="ContactoCliente" <?php if(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1')){ echo "disabled='disabled'";}?>>
							  <?php while($row_ContactoCliente=sqlsrv_fetch_array($SQL_ContactoCliente)){?>
									<option value="<?php echo $row_ContactoCliente['CodigoContacto'];?>" <?php if((isset($row['IdContactoLLamada']))&&(strcmp($row_ContactoCliente['CodigoContacto'],$row['IdContactoLLamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto'];?></option>
							  <?php }?>
							</select>
						<?php }?>
               	  	</div>
					<label class="col-lg-1 control-label">Sucursal</label>
				  	<div class="col-lg-3">
                    	<select name="SucursalCliente" class="form-control m-b select2" id="SucursalCliente">
                         		<option value="">Seleccione...</option>
						</select>
               	  	</div>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Dirección</label>
					<div class="col-lg-3">
                    	<input name="DireccionLlamada" type="text" required="required" class="form-control" id="DireccionLlamada" maxlength="100">
               	  	</div>
					<label class="col-lg-1 control-label">Barrio</label>
					<div class="col-lg-3">
                    	<input name="BarrioDireccionLlamada" type="text" class="form-control" id="BarrioDireccionLlamada" maxlength="50">
               	  	</div>
					<label class="col-lg-1 control-label">Teléfono</label>
					<div class="col-lg-3">
                    	<input name="TelefonoLlamada" type="text" class="form-control" required="required" id="TelefonoLlamada" maxlength="50">
               	  	</div>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Ciudad</label>
					<div class="col-lg-3">
						<input name="CiudadLlamada" type="text" required="required" class="form-control" id="CiudadLlamada" placeholder="Digite para buscar..." value="">
               	  	</div>
					<label class="col-lg-1 control-label">Correo</label>
					<div class="col-lg-3">
                    	<input name="CorreoLlamada" type="text" class="form-control" id="CorreoLlamada" maxlength="100">
               	  	</div>
				</div>
				<div class="form-group">					
					<label class="col-lg-1 control-label">ID servicio</label>
				  	<div class="col-lg-5">
                    	<select name="ArticuloLlamada" class="form-control m-b select2" id="ArticuloLlamada" required>
								<option value="">Seleccione...</option>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Grupo ID</label>
					<div class="col-lg-3">
                    	<input name="GrupoArticulo" type="text" class="form-control" id="GrupoArticulo" maxlength="50" readonly>
               	  	</div>
				</div>
				<div class="form-group">					
					<label class="col-lg-1 control-label">Posición</label>
				  	<div class="col-lg-3">
                    	<input name="PosicionArticulo" type="text" class="form-control" id="PosicionArticulo" maxlength="50" readonly>
               	  	</div>
					<label class="col-lg-1 control-label">OLT</label>
					<div class="col-lg-3">
                    	<input name="OLTArticulo" type="text" class="form-control" id="OLTArticulo" maxlength="50" readonly>
               	  	</div>
				</div>
				</div>
				<div class="form-group">
					<label class="col-lg-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-info-circle"></i> Información de llamada</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Asunto de llamada</label>
					<div class="col-lg-8">
                    	<input autocomplete="off" name="AsuntoLlamada" type="text" required="required" class="form-control" id="AsuntoLlamada" maxlength="150">
               	  	</div>
					<label class="col-lg-1 control-label">Tipo tarea</label>
				  	<div class="col-lg-2">
                    	<select name="TipoTarea" class="form-control m-b" id="TipoTarea">
                         	<option value="Externa" selected="selected">Externa</option>
							<option value="Interna">Interna</option>
						</select>
               	  	</div>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Serie</label>
					<div class="col-lg-3">
                    	<select name="Series" class="form-control m-b" id="Series">
                          <?php while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
								<option value="<?php echo $row_Series['IdSeries'];?>"><?php echo $row_Series['DeSeries'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Tipo problema</label>
					<div class="col-lg-3">
                    	<select name="TipoProblema" class="form-control m-b" required id="TipoProblema">
								<option value="">Seleccione...</option>
                          <?php while($row_TipoProblema=sqlsrv_fetch_array($SQL_TipoProblema)){?>
								<option value="<?php echo $row_TipoProblema['IdTipoProblemaLlamada'];?>"><?php echo $row_TipoProblema['DeTipoProblemaLlamada'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">SubTipo problema</label>
				  	<div class="col-lg-3">
                    	<select name="SubTipoProblema" class="form-control m-b" required id="SubTipoProblema">
								<option value="">Seleccione...</option>
                          <?php while($row_SubTipoProblema=sqlsrv_fetch_array($SQL_SubTipoProblema)){?>
								<option value="<?php echo $row_SubTipoProblema['IdSubTipoProblemaLlamada'];?>"><?php echo $row_SubTipoProblema['DeSubTipoProblemaLlamada'];?></option>
						  <?php }?>
						</select>
               	  	</div>
				</div>
				<div class="form-group">
				  <label class="col-lg-1 control-label">Tipo llamada</label>
				  	<div class="col-lg-3">
                    	<select name="TipoLlamada" class="form-control m-b" required="required" id="TipoLlamada">
								<option value="">Seleccione...</option>
                          <?php while($row_TipoLlamadas=sqlsrv_fetch_array($SQL_TipoLlamadas)){?>
								<option value="<?php echo $row_TipoLlamadas['IdTipoLlamada'];?>"><?php echo $row_TipoLlamadas['DeTipoLlamada'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Cola</label>
				  	<div class="col-lg-4">
                    	<select name="ColaLlamada" class="form-control m-b" id="ColaLlamada">
								<option value="">Seleccione...</option>
                          <?php while($row_ColaLlamada=sqlsrv_fetch_array($SQL_ColaLlamada)){?>
								<option value="<?php echo $row_ColaLlamada['IdColaLlamada'];?>"><?php echo $row_ColaLlamada['DeColaLlamada'];?></option>
						  <?php }?>
						</select>
               	  	</div>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Asignado a</label>
					<div class="col-lg-4">
                    	<select name="EmpleadoLlamada" class="form-control m-b select2" id="EmpleadoLlamada">
								<option value="">(Sin asignar)</option>
                          <?php while($row_EmpleadoLlamada=sqlsrv_fetch_array($SQL_EmpleadoLlamada)){?>
								<option value="<?php echo $row_EmpleadoLlamada['ID_Empleado'];?>"><?php echo $row_EmpleadoLlamada['NombreEmpleado'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Estado</label>
					<div class="col-lg-2">
                    	<select name="EstadoLlamada" class="form-control m-b" id="EstadoLlamada">
                          <?php while($row_EstadoLlamada=sqlsrv_fetch_array($SQL_EstadoLlamada)){?>
								<option value="<?php echo $row_EstadoLlamada['Cod_Estado'];?>"><?php echo $row_EstadoLlamada['NombreEstado'];?></option>
						  <?php }?>
						</select>
               	  	</div>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Comentario</label>
				  	<div class="col-lg-8">
                    	<textarea name="ComentarioLlamada" rows="7" required="required" class="form-control" id="ComentarioLlamada" type="text"></textarea>
               	  	</div>
				</div>
				<div class="form-group">
					<label class="col-lg-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-edit"></i> Información adicional</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Categoria origen</label>
					<div class="col-lg-3">
                    	<select name="CategoriaOrigen" class="form-control m-b" id="CategoriaOrigen" required>
							<option value="">Seleccione...</option>
                          <?php while($row_CatOrigen=sqlsrv_fetch_array($SQL_CatOrigen)){?>
								<option value="<?php echo $row_CatOrigen['IdCateOrigenLlamada'];?>"><?php echo $row_CatOrigen['DeCateOrigenLlamada'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-2 control-label">Indisponibilidad</label>
					<div class="col-lg-2">
                    	<select name="Indisponibilidad" class="form-control m-b" required id="Indisponibilidad">
								<option value="">Seleccione...</option>
                          <?php while($row_IndispoLlamada=sqlsrv_fetch_array($SQL_IndispoLlamada)){?>
								<option value="<?php echo $row_IndispoLlamada['IdIndispoLlamada'];?>"><?php echo $row_IndispoLlamada['DeIndispoLlamada'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-2 control-label">Responsabilidad</label>
				  	<div class="col-lg-2">
                    	<select name="Responsabilidad" class="form-control m-b" required id="Responsabilidad">
								<option value="">Seleccione...</option>
                          <?php while($row_ResponLlamada=sqlsrv_fetch_array($SQL_ResponLlamada)){?>
								<option value="<?php echo $row_ResponLlamada['IdResponLlamada'];?>"><?php echo $row_ResponLlamada['DeResponLlamada'];?></option>
						  <?php }?>
						</select>
               	  	</div>
				</div>
				<div class="form-group">
					<label class="col-lg-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-check-circle"></i> Cierre de llamada</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Resolución de llamada</label>
					<div class="col-lg-8">
						<textarea name="ResolucionLlamada" rows="5" type="text" class="form-control" id="ResolucionLlamada"></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Fecha de cierre</label>
					<div class="col-lg-2 input-group date">
						 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCierre" type="text" required="required" class="form-control" id="FechaCierre" value="<?php echo date('Y-m-d');?>" readonly="readonly">
					</div>
					<div class="col-lg-2 input-group clockpicker" data-autoclose="true">
						<input name="HoraCierre" id="HoraCierre" type="text" class="form-control" value="<?php echo date('H:i');?>" required="required" readonly="readonly">
						<span class="input-group-addon">
							<span class="fa fa-clock-o"></span>
						</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Tipo resolución</label>
					<div class="col-lg-3">
						<select name="TipoResolucion" class="form-control m-b" id="TipoResolucion">
							<option value="">Seleccione...</option>
						  <?php while($row_ResolucionLlamada=sqlsrv_fetch_array($SQL_ResolucionLlamada)){?>
								<option value="<?php echo $row_ResolucionLlamada['IdTipoResolucionLlamada'];?>"><?php echo $row_ResolucionLlamada['DeTipoResolucionLlamada'];?></option>
						  <?php }?>
						</select>
					</div>
					<label class="col-lg-1 control-label">Estado servicio</label>
					<div class="col-lg-3">
						<select name="EstadoServicio" class="form-control m-b" id="EstadoServicio">
								<option value="">Seleccione...</option>
						  <?php while($row_EstServLlamada=sqlsrv_fetch_array($SQL_EstServLlamada)){?>
								<option value="<?php echo $row_EstServLlamada['IdEstadoServicioLlamada'];?>"><?php echo $row_EstServLlamada['DeEstadoServicioLlamada'];?></option>
						  <?php }?>
						</select>
					</div>
					<label class="col-lg-1 control-label">Cancelado por</label>
					<div class="col-lg-3">
						<select name="CanceladoPor" class="form-control m-b" id="CanceladoPor">
						  <?php while($row_CancelarLlamada=sqlsrv_fetch_array($SQL_CancelarLlamada)){?>
								<option value="<?php echo $row_CancelarLlamada['IdCancelarLlamada'];?>"><?php echo $row_CancelarLlamada['DeCancelarLlamada'];?></option>
						  <?php }?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-paperclip"></i> Anexos</h3></label>
				</div>
					<input type="hidden" id="P" name="P" value="32" />
					<input type="hidden" id="swTipo" name="swTipo" value="0" />
					<input type="hidden" id="ClienteLlamadaInterno" name="ClienteLlamadaInterno" value="<?php echo base64_encode(NIT_EMPRESA);?>" />
					<input type="hidden" id="SucursalClienteInterno" name="SucursalClienteInterno" value="<?php echo base64_encode(SUCURSAL_EMPRESA);?>" />
			  </form>
			  <div class="row">
			  	<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
					<?php LimpiarDirTemp();	?>		 
					<div class="fallback">
						<input name="File" id="File" type="file" form="dropzoneForm" />
					</div>
                    	<?php /*?><div class="fileinput fileinput-new input-group" data-provides="fileinput">
							<div class="form-control" data-trigger="fileinput">
								<i class="glyphicon glyphicon-file fileinput-exists"></i>
							<span class="fileinput-filename"></span>
							</div>
							<span class="input-group-addon btn btn-default btn-file">
								<span class="fileinput-new">Seleccionar</span>
								<span class="fileinput-exists">Cambiar</span>
								<input name="FileArchivo" type="file" id="FileArchivo" />
							</span>
							<a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a>
						</div> <?php */?>
				 </form>
			  </div>
			   <br><br>
				<div class="form-group">
					<div class="col-lg-9">
						<button class="btn btn-primary" form="CrearLlamada" type="submit" id="Crear" onClick="ValidarFrm();"><i class="fa fa-check"></i>&nbsp;Crear llamada</button>  
						<a href="gestionar_llamadas_servicios.php" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>
				</div>
			    <br><br>
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
		 $("#CrearLlamada").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		 $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			});
		 $(".select2").select2();		 
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
					var value = $("#NombreClienteLlamada").getSelectedItemData().CodigoCliente;
					$("#ClienteLlamada").val(value).trigger("change");
				}
			}
		};
		 var options2 = {
			url: function(phrase) {
				return "ajx_buscar_datos_json.php?type=8&id="+phrase;
			},

			getValue: "Ciudad",
			requestDelay: 400,
			template: {
				type: "description",
				fields: {
					description: "Codigo"
				}
			},
			list: {
				match: {
					enabled: true
				}
			}
		};

		$("#NombreClienteLlamada").easyAutocomplete(options);
		$("#CiudadLlamada").easyAutocomplete(options2);
		<?php if($dt_LS==1){?>
		$('#ClienteLlamada').trigger('change'); 
	 	<?php }?>
		 
	});

function ValidarFrm(){
	var EstLlamada=document.getElementById('EstadoLlamada');
	var txtResol=document.getElementById('ResolucionLlamada');
	var EstServ=document.getElementById('EstadoServicio');
	if(EstLlamada.value=='-1'){
		txtResol.setAttribute("required","required");
		EstServ.setAttribute("required","required");
	}else{
		txtResol.removeAttribute("required");
		EstServ.removeAttribute("required");
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
<?php /*?><script>	
	function ComprobarExt(){
		var form=document.getElementById("CrearLlamada");
		var archivo=document.getElementById("FileArchivo").value;
		var ext=".pdf";
		var permitido=false;
		if(archivo!=""){
			var ext_archivo=(archivo.substring(archivo.lastIndexOf("."))).toLowerCase();
			if(ext_archivo==ext){
				permitido=true;
			}
			if(!permitido){
				$(document).ready(function(){
					swal({
						title: '¡Error!',
						text: 'El archivo que intenta cargar no es extensión .pdf',
						type: 'error'
					});
				});
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
</script><?php */?>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>