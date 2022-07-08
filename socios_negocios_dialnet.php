<?php require_once("includes/conexion.php");
require_once("includes/conexion_hn.php");
if(PermitirAcceso(502)||PermitirAcceso(503))

$msg_error="";//Mensaje del error
$sw_ext=0;//Sw que permite saber si la ventana esta abierta en modo pop-up. Si es así, no cargo el menú ni el menú superior.
$CodCliente=0;
$Metod="";
$EsProyecto=0;

if(isset($_GET['id'])&&($_GET['id']!="")){
	$CodCliente=base64_decode($_GET['id']);
}
	
if(isset($_GET['ext'])&&($_GET['ext']==1)){
	$sw_ext=1;//Se está abriendo como pop-up
}

if(isset($_GET['metod'])&&($_GET['metod']!="")){
	$Metod=base64_decode($_GET['metod']);
}

if(isset($_POST['swError'])&&($_POST['swError']!="")){//Para saber si ha ocurrido un error.
	$sw_error=$_POST['swError'];
}else{
	$sw_error=0;
}

if(isset($_GET['tl'])&&($_GET['tl']!="")){//0 Si se está creando. 1 Se se está editando.
	$edit=$_GET['tl'];
}elseif(isset($_POST['tl'])&&($_POST['tl']!="")){
	$edit=$_POST['tl'];
}else{
	$edit=0;
}

if($edit==0){
	$Title="Crear socios de negocios";
}elseif($edit==1&&$Metod==4){
	$Title="Crear nuevo contrato";
}else{
	$Title="Editar socios de negocios";
}

$Num_Dir=0;
$Num_Cont=0;

if(isset($_POST['P'])&&($_POST['P']!="")){
	try{
		//LimpiarDirTemp();
		//Carpeta de archivos anexos
		$i=0;//Archivos
		$RutaAttachSAP=ObtenerDirAttach();
		$dir=CrearObtenerDirTemp();
		$dir_firma=CrearObtenerDirTempFirma();
		$dir_new=CrearObtenerDirAnx("socios_negocios");
		
		//Si esta creando, cargo los anexos primero
		if($_POST['tl']==0||$_POST['metod']==4){//Creando SN			
			
			if($_POST['EsProyecto']==1){
				if((isset($_POST['SigCliente']))&&($_POST['SigCliente']!="")){
					$NombreFileFirma=base64_decode($_POST['SigCliente']);
					$Nombre_Archivo=$_POST['LicTradNum']."_FR_".$NombreFileFirma;
					if(!copy($dir_firma.$NombreFileFirma,$dir.$Nombre_Archivo)){
						$sw_error=1;
						$msg_error="No se pudo mover la firma";
					}
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
		}
		
		
		#Comprobar si el cliente ya esta guardado en la tabla de SN. Si no está guardado se ejecuta el INSERT con el Metodo de actualizar
		//$SQL_Dir=Seleccionar('tbl_SociosNegocios','CardCode',"CardCode='".$_POST['CardCode']."'");
		//$row_Dir=sqlsrv_fetch_array($SQL_Dir);
		
		$Metodo=2;//Actualizar en el web services
		$Type=2;//Ejecutar actualizar en el SP
		$IdSNPortal="NULL";
		
		if(base64_decode($_POST['IdSNPortal'])==""){//Insertando en la tabla
			$Metodo=2;
			$Type=1;
		}else{
			$IdSNPortal="'".base64_decode($_POST['IdSNPortal'])."'";
		}
		
		if($_POST['tl']==0){//Creando SN
			$Metodo=1;
		}		
		
		if($_POST['metod']==4){//Si esta actualizando pero creando el contrato
			$Metodo=4;
		}
		
		$EsProyecto=$_POST['EsProyecto'];
		
		if(isset($_POST['CapacidadServ'])&&($_POST['CapacidadServ']!="")){
			$CapacidadServ="'".$_POST['CapacidadServ']."'";
		}else{
			$CapacidadServ="NULL";
		}
		
		if(isset($_POST['VigenciaCont'])&&($_POST['VigenciaCont']!="")){
			$VigenciaCont="'".$_POST['VigenciaCont']."'";
		}else{
			$VigenciaCont="NULL";
		}
		
		$ParamSN=array(
			"$IdSNPortal",
			"'".$_POST['CardCode']."'",
			"'".$_POST['CardName']."'",
			"'".$_POST['PNNombres']."'",
			"'".$_POST['PNApellido1']."'",
			"'".$_POST['PNApellido2']."'",
			"'".$_POST['AliasName']."'",
			"'".$_POST['CardType']."'",
			"'".$_POST['TipoEntidad']."'",
			"'".$_POST['TipoDocumento']."'",
			"'".$_POST['LicTradNum']."'",
			"'".$_POST['GroupCode']."'",
			"'".$_POST['RegimenTributario']."'",
			"'".$_POST['ID_MunicipioMM']."'",
			"'".$_POST['GroupNum']."'",
			"'".$_POST['Industria']."'",
			"'".$_POST['Territorio']."'",
			"'".$_POST['Proyecto']."'",
			"'".$_POST['Latitud']."'",
			"'".$_POST['Longitud']."'",
			"'".$_POST['Genero']."'",
			"'".$_POST['Sexo']."'",
			"'".$_POST['OrienSexual']."'",
			"'".$_POST['Etnia']."'",
			"'".$_POST['Discapacidad']."'",
			"'".$_POST['NivelEduca']."'",
			$CapacidadServ,
			$VigenciaCont,
			$Metodo,
			"'".$_SESSION['CodUser']."'",
			$Type
		);
		$SQL_SN=EjecutarSP('sp_tbl_SociosNegocios',$ParamSN,$_POST['P']);
		if($SQL_SN){		
			if(base64_decode($_POST['IdSNPortal'])==""){
				$row_NewIdSN=sqlsrv_fetch_array($SQL_SN);
				$IdSN=$row_NewIdSN[0];
				$CodCliente=$_POST['CardCode'];
			}else{
				$IdSN=base64_decode($_POST['IdSNPortal']);
				$CodCliente=$_POST['CardCode'];
			}
			
			//Insertar Contactos
			$Count=count($_POST['NombreContacto']);
			$i=0;
			$Delete="Delete From tbl_SociosNegocios_Contactos Where ID_SocioNegocio='".$IdSN."'";
			if(sqlsrv_query($conexion,$Delete)){
				while($i<$Count){
					if($_POST['NombreContacto'][$i]!=""){
						//Insertar el registro en la BD
						$ParamInsConct=array(
							"'".$IdSN."'",
							"'".$_POST['CardCode']."'",
							"'".$_POST['CodigoContacto'][$i]."'",
							"'".$_POST['NombreContacto'][$i]."'",
							"'".$_POST['SegundoNombre'][$i]."'",
							"'".$_POST['Apellidos'][$i]."'",
							"'".$_POST['Telefono'][$i]."'",
							"'".$_POST['TelefonoCelular'][$i]."'",
							"'".$_POST['Posicion'][$i]."'",
							"'".$_POST['Email'][$i]."'",
							"'".$_POST['ActEconomica'][$i]."'",
							"'".$_POST['CedulaContacto'][$i]."'",
							"'".$_POST['RepLegal'][$i]."'",
							"'".$_POST['GrupoCorreo'][$i]."'",
							"'".$_POST['MetodoCtc'][$i]."'",
							"1"
						);
						
						$SQL_InsConct=EjecutarSP('sp_tbl_SociosNegocios_Contactos',$ParamInsConct,$_POST['P']);

						if(!$SQL_InsConct){
							$sw_error=1;
							$msg_error="Ha ocurrido un error al insertar los contactos";
						}
					}
					$i=$i+1;
				}
				//sqlsrv_close($conexion);
				//header('Location:socios_negocios_add.php?a='.base64_encode("OK_SNAdd"));
			}else{
				InsertarLog(1, 45, $Delete);
				$sw_error=1;
				$msg_error="Ha ocurrido un error al eliminar los contactos";
			}
			
			//Insertar direcciones
			$Count=count($_POST['Address']);
			$i=0;
			$Delete="Delete From tbl_SociosNegocios_Direcciones Where ID_SocioNegocio='".$IdSN."'";
			if(sqlsrv_query($conexion,$Delete)){
				while($i<$Count){
					if($_POST['Address'][$i]!=""){
						//Insertar el registro en la BD
						$ParamInsDir=array(
							"'".$IdSN."'",
							"'".$_POST['Address'][$i]."'",
							"'".$_POST['CardCode']."'",
							"'".$_POST['Street'][$i]."'",
							"'".$_POST['Block'][$i]."'",
							"'".$_POST['City'][$i]."'",
							"'".$_POST['County'][$i]."'",
							"'".$_POST['AdresType'][$i]."'",
							"'".$_POST['Estrato'][$i]."'",
							"'".$_POST['DirContrato'][$i]."'",
							"'".$_POST['CodigoPostal'][$i]."'",
							"'".$_POST['LineNum'][$i]."'",
							"'".$_POST['Metodo'][$i]."'",
							"1"
						);
						
						$SQL_InsDir=EjecutarSP('sp_tbl_SociosNegocios_Direcciones',$ParamInsDir,$_POST['P']);

						if(!$SQL_InsDir){
							$sw_error=1;
							$msg_error="Ha ocurrido un error al insertar las direcciones";
						}
					}
					$i=$i+1;
				}					
			}else{
				InsertarLog(1, 45, $Delete);
				$sw_error=1;
				$msg_error="Ha ocurrido un error al eliminar las direcciones";
			}
			
			if(($_POST['tl']==0&&$_POST['EsProyecto']==1)||$_POST['metod']==4){//Creando SN		
				try{
					//Mover los anexos a la carpeta de archivos de SAP
					$Delete="Delete From tbl_DocumentosSAP_Anexos Where TipoDocumento=2 and Metodo=1 and ID_Documento='".$IdSN."'";
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
								"'2'",
								"'".$IdSN."'",
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
			}

			/*if($_POST['tl']==0){//Mensaje para devuelta
				$Msg=base64_encode("OK_SNAdd");
			}else{
				$Msg=base64_encode("OK_SNEdit");
			}
			sqlsrv_close($conexion);						
			if($_POST['ext']==0){//Validar a donde debe ir la respuesta
				header('Location:socios_negocios.php?id='.base64_encode($_POST['CardCode']).'&ext='.$_POST['ext'].'&pag='.$_POST['pag'].'&return='.$_POST['return'].'&a='.$Msg.'&tl='.$_POST['tl']);
			}else{
				header('Location:socios_negocios.php?id='.base64_encode($_POST['CardCode']).'&ext='.$_POST['ext'].'&a='.$Msg.'&tl='.$_POST['tl']);
			}*/

			//Enviar datos al WebServices				
			try{
				require_once("includes/conect_ws.php");
				$Parametros=array(
					'pIdCliente' => $IdSN,
					'pLogin'=>$_SESSION['User']
				);
				$Client->InsertarClientePortal($Parametros);
				//$Client->AppPortal_InsertarClientePortal($Parametros);
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
				}else{
					if($_POST['tl']==0){//Mensaje para devuelta
						$Msg=base64_encode("OK_SNAdd");
					}else{
						$Msg=base64_encode("OK_SNEdit");
					}
					
					if(($Metodo==1||$Metodo==4)&&$EsProyecto==1){
						//Enviar correo
						$ParamEnviaMail=array(
							"'".$IdSN."'",
							"'2'",
							"'5'"
						);
						$SQL_EnviaMail=EjecutarSP('usp_CorreoEnvio',$ParamEnviaMail,$_POST['P']);
						if(!$SQL_EnviaMail){
							//$sw_error=1;
							//$msg_error="Error al enviar el correo al usuario.";
						}
					}
					
					sqlsrv_close($conexion);						
					if($_POST['ext']==0){//Validar a donde debe ir la respuesta
						header('Location:socios_negocios.php?id='.base64_encode($_POST['CardCode']).'&ext='.$_POST['ext'].'&pag='.$_POST['pag'].'&return='.$_POST['return'].'&a='.$Msg.'&tl='.$_POST['tl']);
					}else{
						header('Location:socios_negocios.php?id='.base64_encode($_POST['CardCode']).'&ext='.$_POST['ext'].'&a='.$Msg.'&tl='.$_POST['tl']);
					}
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}
			
		}else{
			$sw_error=1;
			$msg_error="Ha ocurrido un error al crear el Socio de Negocio";
		}
	}catch (Exception $e){
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if($edit==0){
	//Verificar si el usuario esta asignado a un proyecto en particular o es general
	$SQL_ValorDefault=Seleccionar('uvw_Sap_tbl_SN_VlrDef_Usu','*',"IdEmp='".$_SESSION['CodigoSAP']."'");
	$row_ValorDefault=sql_fetch_array($SQL_ValorDefault);
	
	if($row_ValorDefault['IdEmp']!=""){
		$EsProyecto=1;
	}
	
	if($row_ValorDefault['IdMunicipio']!=""){
		$SQL_Municipio=Seleccionar('uvw_Sap_tbl_SN_Municipio','*',"ID_Municipio='".$row_ValorDefault['IdMunicipio']."'");
		$row_Municipio=sql_fetch_array($SQL_Municipio);
	}	
}

if($edit==1){
	
	if($Metod==4){//Actualizar creando contrato
		$SQL_ValorDefault=Seleccionar('uvw_Sap_tbl_SN_VlrDef_Usu','*',"IdEmp='".$_SESSION['CodigoSAP']."'");
		$row_ValorDefault=sql_fetch_array($SQL_ValorDefault);
		
		if($row_ValorDefault['IdEmp']!=""){
			$EsProyecto=1;
		}

		if($row_ValorDefault['IdMunicipio']!=""){
			$SQL_Municipio=Seleccionar('uvw_Sap_tbl_SN_Municipio','*',"ID_Municipio='".$row_ValorDefault['IdMunicipio']."'");
			$row_Municipio=sql_fetch_array($SQL_Municipio);
		}		
	}

	//Cliente
	$SQL=Seleccionar("uvw_Sap_tbl_Clientes","*","[CodigoCliente]='".$CodCliente."'",'','',2);
	$row=sql_fetch_array($SQL,2);
	
	//Direcciones
	$SQL_Dir=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","*","[CodigoCliente]='".$row['CodigoCliente']."'",'','',2);
	$Num_Dir=sql_num_rows($SQL_Dir,2);
	
	//Contactos
	$SQL_Cont=Seleccionar("uvw_Sap_tbl_ClienteContactos","*","[CodigoCliente]='".$row['CodigoCliente']."'",'','',2);
	$Num_Cont=sql_num_rows($SQL_Cont,2);
	
	//Municipio MM
	$SQL_MunMM=Seleccionar('uvw_Sap_tbl_SN_Municipio','*',"ID_Municipio='".$row['U_HBT_MunMed']."'");
	$row_MunMM=sql_fetch_array($SQL_MunMM);
	
	//Facturas pendientes
	$SQL_FactPend=Seleccionar('uvw_Sap_tbl_FacturasPendientes','TOP 10 *',"ID_CodigoCliente='".$row['CodigoCliente']."'","FechaContabilizacion","DESC");
		
	//ID de servicios
	$SQL_IDServicio=Seleccionar('uvw_Sap_tbl_ArticulosLlamadas','*',"[CodigoCliente]='".$row['CodigoCliente']."'",'[ItemCode]','',2);
		
	//Historico de gestiones
	$SQL_HistGestion=Seleccionar('uvw_tbl_Cartera_Gestion','TOP 10 *',"CardCode='".$row['CodigoCliente']."'",'FechaRegistro');
}

if($sw_error==1){

	//Cliente
	$SQL=Seleccionar("uvw_tbl_SociosNegocios","*","[ID_SocioNegocio]='".$IdSN."'");
	$row=sql_fetch_array($SQL);
	
	//Direcciones
	$SQL_Dir=Seleccionar("uvw_tbl_SociosNegocios_Direcciones","*","[ID_SocioNegocio]='".$IdSN."'");
	$Num_Dir=sql_num_rows($SQL_Dir);
	
	//Contactos
	$SQL_Cont=Seleccionar("uvw_tbl_SociosNegocios_Contactos","*","[ID_SocioNegocio]='".$IdSN."'");
	$Num_Cont=sql_num_rows($SQL_Cont);
	
	//Municipio MM
	$SQL_MunMM=Seleccionar('uvw_Sap_tbl_SN_Municipio','*',"ID_Municipio='".$row['U_HBT_MunMed']."'");
	$row_MunMM=sql_fetch_array($SQL_MunMM);
	
	//Facturas pendientes
	$SQL_FactPend=Seleccionar('uvw_Sap_tbl_FacturasPendientes','TOP 10 *',"ID_CodigoCliente='".$row['CodigoCliente']."'","FechaContabilizacion","DESC");
		
	//ID de servicios
	$SQL_IDServicio=Seleccionar('uvw_Sap_tbl_ArticulosLlamadas','*',"[CodigoCliente]='".$row['CodigoCliente']."'",'[ItemCode]','',2);
		
	//Historico de gestiones
	$SQL_HistGestion=Seleccionar('uvw_tbl_Cartera_Gestion','TOP 10 *',"CardCode='".$row['CodigoCliente']."'",'FechaRegistro');
}

//Condiciones de pago
$SQL_CondicionPago=Seleccionar('uvw_Sap_tbl_CondicionPago','*','','NombreCondicion');

//Tipos de SN
$SQL_TipoSN=Seleccionar('uvw_tbl_TiposSN','*');

//Regimen tributario
$SQL_RegimenT=Seleccionar('uvw_Sap_tbl_SN_RegimenTributario','*','','RegimenTributario');

//Tipo documento
$SQL_TipoDoc=Seleccionar('tbl_TipoDocumentoSN','*','','TipoDocumento');

//Tipo entidad
$SQL_TipoEntidad=Seleccionar('tbl_TipoEntidadSN','*','','NombreEntidad');

//Grupos de Clientes
$SQL_GruposClientes=Seleccionar('uvw_Sap_tbl_GruposClientes','*','','GroupName');

//Industrias
$SQL_Industria=Seleccionar('uvw_Sap_tbl_Clientes_Industrias','*','','DeIndustria');

//Territorio
$SQL_Territorio=Seleccionar('uvw_Sap_tbl_Territorios','*','','DeTerritorio');

//Proyectos
$SQL_Proyecto=Seleccionar('uvw_Sap_tbl_Proyectos','*','','DeProyecto');

//Grupos de articulos
$SQL_GruposArticulos=Seleccionar('uvw_Sap_tbl_GruposArticulos','*',"CDU_Activo='SI' and CDU_IdTipoServicio='INTERNET' and CDU_PrecioPlan > 0",'ItmsGrpNam');

//Vigencia de contratos
$SQL_VigenciaServ=Seleccionar('uvw_Sap_tbl_ContratosVigencia','*','','IdVigenciaServ');

//Genero
$SQL_Genero=Seleccionar('uvw_Sap_tbl_SN_Genero','*');

//Sexo
$SQL_Sexo=Seleccionar('uvw_Sap_tbl_SN_Sexo','*');

//Orientacion sexual
$SQL_OrienSexual=Seleccionar('uvw_Sap_tbl_SN_OrienSexual','*');

//Etnias
$SQL_Etnias=Seleccionar('uvw_Sap_tbl_SN_Etnias','*');

//Discapacidad
$SQL_Discapacidad=Seleccionar('uvw_Sap_tbl_SN_Discapacidad','*');

//Nivel educacion
$SQL_NivelEduca=Seleccionar('uvw_Sap_tbl_SN_NivelEduca','*');

//Departamentos
$SQL_Dptos=Seleccionar('uvw_Sap_tbl_SN_Municipio','Distinct DeDepartamento','','DeDepartamento');

//Grupo de correos
$SQL_GrupoCorreo=Seleccionar('uvw_Sap_tbl_GrupoCorreo','*');

//Estrato
if(($edit==0||$Metod==4)&&($EsProyecto==1&&$row_ValorDefault['IdEstrato']!="")){
	$SQL_Estrato=Seleccionar('tbl_EstratosSN','*',"Estrato IN ('1','2')",'Estrato');
}else{
	$SQL_Estrato=Seleccionar('tbl_EstratosSN','*','','Estrato');
}


?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $Title;?> | <?php echo NOMBRE_PORTAL;?></title>
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_SNAdd"))){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'El Socio de Negocio ha sido creado exitosamente.',
                type: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_SNEdit"))){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'El Socio de Negocio ha sido actualizado exitosamente.',
                type: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ArtUpd"))){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'El ID de servicio ha sido actualizado exitosamente.',
                type: 'success'
            });
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Lo sentimos!',
                text: '".LSiqmlObs($msg_error)."',
                type: 'error'
            });
		});		
		</script>";
}
?>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.panel-body{
		padding: 0px !important;
	}
	.tabs-container .panel-body{
		padding: 0px !important;
	}
	.nav-tabs > li > a{
		padding: 14px 20px 14px 25px !important;
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#CardCode").change(function(){
			var carcode=document.getElementById('CardCode').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=7&id="+carcode,
				success: function(response){
					$('#CondicionPago').html(response).fadeIn();
				}
			});
		});
		$("#TipoEntidad").change(function(){
			var TipoEntidad=document.getElementById('TipoEntidad').value;
			var Nombres=document.getElementById('PNNombres');
			var Apellido1=document.getElementById('PNApellido1');
			var Apellido2=document.getElementById('PNApellido2');
			var CardName=document.getElementById('CardName');
			
			if(TipoEntidad==1){//Natural
				//Quitar
				Nombres.removeAttribute("readonly");
				Apellido1.removeAttribute("readonly");
				Apellido2.removeAttribute("readonly");
				
				//Poner
				Nombres.setAttribute("required","required");
				Apellido1.setAttribute("required","required");				
				CardName.setAttribute("readonly","readonly");
				<?php if($edit==0){?>CardName.value="";<?php }?>
			}else{//Juridica
				//Quitar
				CardName.removeAttribute("readonly");
				Nombres.removeAttribute("required");
				Apellido1.removeAttribute("required");
				
				//Poner
				CardName.value="";
				Nombres.value="";
				Apellido1.value="";
				Apellido2.value="";
				Nombres.setAttribute("readonly","readonly");
				Apellido1.setAttribute("readonly","readonly");
				Apellido2.setAttribute("readonly","readonly");
			}
		});
		//NomDir('1');
		<?php if($edit==0||$Metod==4){?>
		$('#TipoEntidad').trigger('change');
		<?php }?>
		CapturarGPS();
	});
</script>
<script>
function CapturarGPS(){
	var Latitud=document.getElementById("Latitud");
	var Longitud=document.getElementById("Longitud");
	var CoordGPS=document.getElementById("CoordGPS");
	if ("geolocation" in navigator){//check geolocation available 
		//try to get user current location using getCurrentPosition() method
		navigator.geolocation.getCurrentPosition(function(position){
			Latitud.value=position.coords.latitude;
			Longitud.value=position.coords.longitude;
			CoordGPS.innerHTML=Latitud.value + "," +Longitud.value;
		});
	}else{
		console.log("Navegador no soporta geolocalizacion");
		CoordGPS.innerHTML='No está activado el GPS';
	}
}	

function ValidarSN(ID){
	if(isNaN(ID)){
		document.getElementById('Crear').disabled=true;
		swal({
			title: '¡Advertencia!',
			text: 'La cedula del cliente no es un valor numerico. Por favor valide.',
			type: 'warning'
		});
	}else{
		var spinner=document.getElementById('spinner1');
		spinner.style.visibility='visible';
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=16&id="+ID,
			success: function(response){
				document.getElementById('Validar').innerHTML=response;
				spinner.style.visibility='hidden';
				if(response!=""){
					document.getElementById('Crear').disabled=true;
				}else{
					document.getElementById('Crear').disabled=false;
				}
			}
		});
	}	
}
	
function ValidarEmail(ID){
	var spinner=document.getElementById('spinEmail'+ID);
	spinner.style.visibility='visible';
	//var Nombre=document.getElementById('NombreContacto'+ID);
	var Email=document.getElementById('Email'+ID);
	var ValEmail=document.getElementById('ValEmail'+ID);
	if(Email.value!=""){
		$.ajax({
			url:"ajx_buscar_datos_json.php",
			data:{
				type:22,
				email:Base64.encode(Email.value)
			},
			dataType:'json',
			success: function(data){
				if(data.Result==1){
					ValEmail.innerHTML= '<p class="text-info"><i class="fa fa-thumbs-up"></i> Email válido</p>';
					document.getElementById('Crear').disabled=false;
				}else{
					ValEmail.innerHTML= '<p class="text-danger"><i class="fa fa-times-circle-o"></i> Email NO válido</p>';
					document.getElementById('Crear').disabled=true;
				}
				spinner.style.visibility='hidden';
			}
		});
	}else{
		ValEmail.innerHTML='';
		spinner.style.visibility='hidden';
		document.getElementById('Crear').disabled=false;
	}
}

function SeleccionarFactura(Num, Obj, Frm){
	var div=document.getElementById("dwnAllFact");
	var FactSel=document.getElementById("FactSel");
	var FactFrm=document.getElementById("FactFrm");	
	var Fac=FactSel.value.indexOf(Num);
	var Link=document.getElementById("LinkAllFact");
	
	if(Fac<0){
		FactSel.value=FactSel.value + Num + "[*]";
		FactFrm.value=FactFrm.value + Frm + "[*]";
	}else{
		var tmp=FactSel.value.replace(Num+"[*]","");
		var tmpfrm=FactFrm.value.replace(Frm+"[*]","");
		FactSel.value=tmp;
		FactFrm.value=tmpfrm;
	}
	
	if(FactSel.value==""){
		div.style.display='none';
	}else{
		div.style.display='';
		Link.setAttribute('href',"sapdownload.php?id=<?php echo base64_encode('15');?>&type=<?php echo base64_encode('2');?>&zip=<?php echo base64_encode('1');?>&ObType="+Obj+"&IdFrm="+FactFrm.value+"&DocKey="+FactSel.value);
	}
}

function CrearNombre(){
	var Nombre=document.getElementById("PNNombres");
	var PrimerApellido=document.getElementById("PNApellido1");
	var SegundoApellido=document.getElementById("PNApellido2");
	var CardName=document.getElementById("CardName");
	var AliasName=document.getElementById("AliasName");
	
	
	if(Nombre.value!=""&&PrimerApellido.value!=""){
		CardName.value=PrimerApellido.value + ' ' + SegundoApellido.value + ' ' + Nombre.value;
		AliasName.value=CardName.value;
	}else{
		CardName.value="";
		AliasName.value=CardName.value;
	}
	
	<?php if($edit==0||$Metod==4){?>
	CopiarNombreCont();
	<?php }?>
}
<?php if($edit==0){?>
function CopiarNombreCont(){
	var Nombre=document.getElementById("PNNombres");
	var PrimerApellido=document.getElementById("PNApellido1");
	var SegundoApellido=document.getElementById("PNApellido2");
	var Cedula=document.getElementById("LicTradNum");
	
	var NombreContacto=document.getElementById("NombreContacto1");
	var SegundoNombre=document.getElementById("SegundoNombre1");
	var Apellidos=document.getElementById("Apellidos1");
	var CedulaContacto=document.getElementById("CedulaContacto1");
	var Posicion=document.getElementById("Posicion1");
	var RepLegal=document.getElementById("RepLegal1");
	var Address=document.getElementById("Address1");

	var res = Nombre.value.split(" ");
	NombreContacto.value=res[0];
	if(res[1]===undefined){
		res[1]="";
	}
	SegundoNombre.value=res[1];
	Apellidos.value=PrimerApellido.value + ' ' + SegundoApellido.value;
	CedulaContacto.value=Cedula.value;
	Posicion.value="TITULAR";
	Address.value="PRINCIPAL";
	Address.readOnly=true;
	RepLegal.value="SI";	
	
}
<?php }?>
</script>
<?php /*?><script>
function NomDir(id){
	var tipodir=document.getElementById("AdresType"+id);
	var nombredir=document.getElementById("Address"+id);
	
	if(tipodir.value=="B"){
		nombredir.value="<?php echo ObtenerVariable("DirFacturacion");?>";
	}else if(tipodir.value=="S"){
		nombredir.value="<?php echo ObtenerVariable("DirDestino");?>";
	}
}
</script><?php */?>
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
                        <li class="active">
                            <strong><?php echo $Title;?></strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <form action="socios_negocios.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="EditarSN" name="EditarSN">
			 <div class="row">
				<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<div class="col-lg-6">
								<?php 
								if($edit==1){
									if(PermitirFuncion(503)||($Metod==4&&PermitirFuncion(505))||(PermitirFuncion(504)&&($row['CardType']=="L"))){?>
										<button class="btn btn-warning" type="submit" id="Crear"><i class="fa fa-refresh"></i> Actualizar Socio de negocio</button>
								<?php }
								}else{
									if(PermitirFuncion(501)){?>
										<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Crear Socio de negocio</button>
								<?php }
								}?>
								<?php 
									$EliminaMsg=array("&a=".base64_encode("OK_SNAdd"),"&a=".base64_encode("OK_SNEdit"));
									if(isset($_GET['return'])){
										$_GET['return']=str_replace($EliminaMsg,"",base64_decode($_GET['return']));
									}
									if(isset($_GET['return'])){
										$return=base64_decode($_GET['pag'])."?".$_GET['return'];
									}elseif(isset($_POST['return'])){
										$return=base64_decode($_POST['return']);
									}else{
										$return="socios_negocios.php?";
									}
								if($sw_ext==0){?>
									<a href="<?php echo $return;?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
								<?php }?>
							</div>	
							<div class="col-lg-6">
								<?php if(($edit==1)&&(PermitirFuncion(302))){?>
									<a href="llamada_servicio.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['CodigoCliente']);?>" target="_blank" class="pull-right btn btn-primary"><i class="fa fa-plus-circle"></i> Crear llamada de servicio</a>
								<?php }?>		
								<p class="pull-right p-xs"><button type="button" class="btn btn-outline btn-link" onClick="CapturarGPS();" title="Obtener coordenadas nuevamente"><i class="fa fa-map-marker"></i> Coordenadas GPS: </button><span id="CoordGPS"></span></p>
							</div>
						</div>
						<input type="hidden" id="P" name="P" value="<?php if($edit==1){echo "45";}else{echo "38";}?>" />
						<input type="hidden" id="IdSNPortal" name="IdSNPortal" value="<?php if(isset($row['IdSNPortal'])){echo base64_encode($row['IdSNPortal']); }?>" />
						<input type="hidden" id="tl" name="tl" value="<?php echo $edit;?>" />
						<input type="hidden" id="ext" name="ext" value="<?php echo $sw_ext;?>" />
						<input type="hidden" id="Latitud" name="Latitud" value="" />
						<input type="hidden" id="Longitud" name="Longitud" value="" />
						<input type="hidden" id="metod" name="metod" value="<?php echo $Metod;?>" />
						<input type="hidden" id="EsProyecto" name="EsProyecto" value="<?php echo $EsProyecto;?>" />
						<?php if($sw_ext==0){?>
						<input type="hidden" id="pag" name="pag" value="<?php if(isset($_GET['pag'])){echo $_GET['pag'];}?>" />
						<input type="hidden" id="return" name="return" value="<?php if(isset($_GET['return'])){echo base64_encode($_GET['return']);}?>" />
						<?php }?>						
					</div>
				</div>
			 </div>
			 <br>
			 <div class="row">
			 	<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<?php if($edit==1){?> 
						 <div class="form-group">
							<h3 class="col-xs-12 bg-info p-xs b-r-sm">Socio de negocio: <?php echo utf8_encode($row['NombreCliente'])." [".$row['LicTradNum']."]";?></h3>
						 </div>
						 <?php }?>
						 <div class="tabs-container">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#tabSN-1"><i class="fa fa-info-circle"></i> Información general</a></li>
								<li><a data-toggle="tab" href="#tabSN-2"><i class="fa fa-user-circle"></i> Contactos</a></li>
								<li><a data-toggle="tab" href="#tabSN-3"><i class="fa fa-home"></i> Direcciones</a></li>
								<?php if($edit==1){?><li><a data-toggle="tab" href="#tabSN-4"><i class="fa fa-folder-open"></i> Documentos relacionados</a></li><?php } ?>
								<?php if($edit==1){?><li><a data-toggle="tab" href="#tabSN-5" onClick="ConsultarTab('501');"><i class="fa fa-handshake-o" aria-hidden="true"></i> Contratos</a></li><?php }?>
								<li><a data-toggle="tab" href="#tabSN-6" onClick="ConsultarTab('601');"><i class="fa fa-paperclip"></i> Anexos</a></li>
							</ul>
						   <div class="tab-content">
							   <div id="tabSN-1" class="tab-pane active">
								   <br>
									<div class="form-group">
										<label class="col-lg-1 control-label">Código</label>
										<div class="col-lg-2">
											<input name="CardCode" autofocus="autofocus" type="text" readonly class="form-control" id="CardCode" value="<?php if($edit==1){echo $row['CodigoCliente'];}?>">
										</div>
										<div class="col-lg-2">
											<select name="CardType" class="form-control" id="CardType" required>
											<?php
												while($row_TipoSN=sqlsrv_fetch_array($SQL_TipoSN)){?>
													<option value="<?php echo $row_TipoSN['CardType'];?>" <?php if((isset($row['CardType']))&&(strcmp($row_TipoSN['CardType'],$row['CardType'])==0)){ echo "selected=\"selected\"";}elseif(PermitirFuncion(504)&&($row_TipoSN['CardType']=="L")){echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdTipoSN']))&&(strcmp($row_TipoSN['CardType'],$row_ValorDefault['IdTipoSN'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoSN['DE_CardType'];?></option>
											<?php }?>
											</select>
										</div>
										<div class="col-lg-1">
											<div id="spinner1" style="visibility: hidden;" class="sk-spinner sk-spinner-wave">
												<div class="sk-rect1"></div>
												<div class="sk-rect2"></div>
												<div class="sk-rect3"></div>
												<div class="sk-rect4"></div>
												<div class="sk-rect5"></div>
											</div>
										</div>
										<div id="Validar" class="col-lg-6"></div>									
									</div>
								   <div class="form-group">
										<label class="col-lg-1 control-label">Tipo entidad</label>
										<div class="col-lg-3">
											<select name="TipoEntidad" class="form-control" id="TipoEntidad" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_TipoEntidad=sqlsrv_fetch_array($SQL_TipoEntidad)){?>
													<option value="<?php echo $row_TipoEntidad['ID_TipoEntidad'];?>" <?php if((isset($row['U_HBT_TipEnt']))&&(strcmp($row_TipoEntidad['ID_TipoEntidad'],$row['U_HBT_TipEnt'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdTipoEntidad']))&&(strcmp($row_TipoEntidad['ID_TipoEntidad'],$row_ValorDefault['IdTipoEntidad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoEntidad['NombreEntidad'];?></option>
											<?php }?>
											</select>
										</div>
									    <label class="col-lg-1 control-label">Tipo documento</label>
										<div class="col-lg-3">
											<select name="TipoDocumento" class="form-control" id="TipoDocumento" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_TipoDoc=sqlsrv_fetch_array($SQL_TipoDoc)){?>
													<option value="<?php echo $row_TipoDoc['ID_TipoDocumento'];?>" <?php if((isset($row['U_HBT_TipDoc']))&&(strcmp($row_TipoDoc['ID_TipoDocumento'],$row['U_HBT_TipDoc'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdTipoDocumento']))&&(strcmp($row_TipoDoc['ID_TipoDocumento'],$row_ValorDefault['IdTipoDocumento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoDoc['TipoDocumento'];?></option>
											<?php }?>
											</select>
										</div>
									   	<label class="col-lg-1 control-label">Número documento</label>
										<div class="col-lg-2">
											<input name="LicTradNum" type="text" required class="form-control" id="LicTradNum" value="<?php if($edit==1){echo $row['LicTradNum'];}?>" maxlength="15" onKeyPress="return justNumbers(event,this.value);" <?php if($edit==0){?>onChange="CopiarNombreCont();ValidarSN(this.value);"<?php }?>>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Nombres</label>
										<div class="col-lg-3">
											<input name="PNNombres" type="text" class="form-control" id="PNNombres" readonly="readonly" value="<?php if($edit==1){echo utf8_encode($row['U_HBT_Nombres']);}?>" onChange="CrearNombre();">
										</div>	
										<label class="col-lg-1 control-label">Primer apellido</label>
										<div class="col-lg-3">
											<input name="PNApellido1" type="text" class="form-control" id="PNApellido1" readonly="readonly" value="<?php if($edit==1){echo utf8_encode($row['U_HBT_Apellido1']);}?>" onChange="CrearNombre();">
										</div>
										<label class="col-lg-1 control-label">Segundo apellido</label>
										<div class="col-lg-3">
											<input name="PNApellido2" type="text" class="form-control" id="PNApellido2" readonly="readonly" value="<?php if($edit==1){echo utf8_encode($row['U_HBT_Apellido2']);}?>" onChange="CrearNombre();">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Nombre</label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="CardName" id="CardName" required value="<?php if($edit==1){ echo utf8_encode($row['NombreCliente']);}?>">
										</div>
										<?php if($edit==1){?>
										<label class="col-lg-1 control-label">Estado servicio</label>
										<div class="col-lg-3">
											<input type="text" readonly class="form-control" name="EstadoServicio" id="EstadoServicio" value="<?php if($edit==1){echo $row['DeEstadoServicioCliente'];}?>">
										</div>
										<?php }?>
									</div>
								   <div class="form-group">
										<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-briefcase"></i> Información comercial</h3></label>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Nombre comercial</label>
										<div class="col-lg-6">
											<input name="AliasName" type="text" required class="form-control" id="AliasName" value="<?php if($edit==1){echo utf8_encode($row['AliasCliente']);}?>" readonly="readonly">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Grupo</label>
										<div class="col-lg-4">
											<select name="GroupCode" class="form-control select2" id="GroupCode" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_GruposClientes=sqlsrv_fetch_array($SQL_GruposClientes)){?>
													<option value="<?php echo $row_GruposClientes['GroupCode'];?>" <?php if((isset($row['GrupoCliente']))&&(strcmp($row_GruposClientes['GroupCode'],$row['GrupoCliente'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdGrupoSN']))&&(strcmp($row_GruposClientes['GroupCode'],$row_ValorDefault['IdGrupoSN'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_GruposClientes['GroupName'];?></option>
											<?php }?>
											</select>
										</div>
										<label class="col-lg-1 control-label">Condición de pago</label>
										<div class="col-lg-3">
											<select name="GroupNum" class="form-control" id="GroupNum" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_CondicionPago=sqlsrv_fetch_array($SQL_CondicionPago)){?>
													<option value="<?php echo $row_CondicionPago['IdCondicionPago'];?>" <?php if((isset($row['GroupNum']))&&(strcmp($row_CondicionPago['IdCondicionPago'],$row['GroupNum'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdCondiPago']))&&(strcmp($row_CondicionPago['IdCondicionPago'],$row_ValorDefault['IdCondiPago'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CondicionPago['NombreCondicion'];?></option>
											<?php }?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Industria</label>
										<div class="col-lg-2">
											<select name="Industria" class="form-control" id="Industria" required>
											<?php
												while($row_Industria=sqlsrv_fetch_array($SQL_Industria)){?>
													<option value="<?php echo $row_Industria['IdIndustria'];?>" <?php if((isset($row['IdIndustria']))&&(strcmp($row_Industria['IdIndustria'],$row['IdIndustria'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdIndustria']))&&(strcmp($row_Industria['IdIndustria'],$row_ValorDefault['IdIndustria'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Industria['DeIndustria'];?></option>
											<?php }?>
											</select>
										</div>
										<label class="col-lg-1 control-label">Territorio</label>
										<div class="col-lg-3">
											<select name="Territorio" class="form-control select2" id="Territorio" required>
											<?php
												while($row_Territorio=sqlsrv_fetch_array($SQL_Territorio)){?>
													<option value="<?php echo $row_Territorio['IdTerritorio'];?>" <?php if((isset($row['IdTerritorio']))&&(strcmp($row_Territorio['IdTerritorio'],$row['IdTerritorio'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdTerritorio']))&&(strcmp($row_Territorio['IdTerritorio'],$row_ValorDefault['IdTerritorio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Territorio['DeTerritorio'];?></option>
											<?php }?>
											</select>
										</div>
									</div>
								   <div class="form-group">
										<label class="col-lg-1 control-label">Proyecto</label>
										<div class="col-lg-4">
											<select name="Proyecto" class="form-control select2" id="Proyecto">
												<option value="">(Ninguno)</option>
											<?php
												while($row_Proyecto=sqlsrv_fetch_array($SQL_Proyecto)){?>
													<option value="<?php echo $row_Proyecto['IdProyecto'];?>" <?php if((isset($row['IdProyecto']))&&(strcmp($row_Proyecto['IdProyecto'],$row['IdProyecto'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdProyecto']))&&(strcmp($row_Proyecto['IdProyecto'],$row_ValorDefault['IdProyecto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Proyecto['DeProyecto'];?></option>
											<?php }?>
											</select>
										</div>
									</div>
								   <?php if($edit==0||$Metod==4){?>
								   <div class="form-group">
										<label class="col-lg-1 control-label">Capacidad de servicio</label>
										<div class="col-lg-4">
											<select name="CapacidadServ" class="form-control select2" id="CapacidadServ" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_GruposArticulos=sqlsrv_fetch_array($SQL_GruposArticulos)){?>
													<option value="<?php echo $row_GruposArticulos['ItmsGrpCod'];?>" <?php if((isset($row_ValorDefault['IdCapServicio']))&&(strcmp($row_GruposArticulos['ItmsGrpCod'],$row_ValorDefault['IdCapServicio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_GruposArticulos['ItmsGrpNam']." ($".number_format($row_GruposArticulos['CDU_PrecioPlan'],0).")";?></option>
											<?php }?>
											</select>
										</div>
									   	<label class="col-lg-1 control-label">Vigencia</label>
										<div class="col-lg-2">
											<select name="VigenciaCont" class="form-control" id="VigenciaCont" required>
											<?php
												while($row_VigenciaServ=sqlsrv_fetch_array($SQL_VigenciaServ)){?>
													<option value="<?php echo $row_VigenciaServ['IdVigenciaServ'];?>" <?php if((isset($row_ValorDefault['IdVigServicio']))&&(strcmp($row_VigenciaServ['IdVigenciaServ'],$row_ValorDefault['IdVigServicio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_VigenciaServ['DeVigenciaServ'];?></option>
											<?php }?>
											</select>
										</div>
									</div>
								   <?php }?>
								   <div class="form-group">
										<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-info-circle"></i> Información adicional</h3></label>
									</div>
								   <div class="form-group">
										<label class="col-lg-1 control-label">Género</label>
										<div class="col-lg-2">
											<select name="Genero" class="form-control" id="Genero" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_Genero=sqlsrv_fetch_array($SQL_Genero)){?>
													<option value="<?php echo $row_Genero['ID_Genero'];?>" <?php if((isset($row['CDU_IdGenero']))&&(strcmp($row_Genero['ID_Genero'],$row['CDU_IdGenero'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdGenero']))&&(strcmp($row_Genero['ID_Genero'],$row_ValorDefault['IdGenero'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Genero['DE_Genero'];?></option>
											<?php }?>
											</select>
										</div>
										<label class="col-lg-1 control-label">Sexo</label>
										<div class="col-lg-2">
											<select name="Sexo" class="form-control" id="Sexo" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_Sexo=sqlsrv_fetch_array($SQL_Sexo)){?>
													<option value="<?php echo $row_Sexo['ID_Sexo'];?>" <?php if((isset($row['CDU_IdSexo']))&&(strcmp($row_Sexo['ID_Sexo'],$row['CDU_IdSexo'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdSexo']))&&(strcmp($row_Sexo['ID_Sexo'],$row_ValorDefault['IdSexo'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Sexo['DE_Sexo'];?></option>
											<?php }?>
											</select>
										</div>
									    <label class="col-lg-1 control-label">Orientación sexual</label>
										<div class="col-lg-2">
											<select name="OrienSexual" class="form-control" id="OrienSexual" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_OrienSexual=sqlsrv_fetch_array($SQL_OrienSexual)){?>
													<option value="<?php echo $row_OrienSexual['ID_OrienSexual'];?>" <?php if((isset($row['CDU_IdOrienSexual']))&&(strcmp($row_OrienSexual['ID_OrienSexual'],$row['CDU_IdOrienSexual'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdOrienSexual']))&&(strcmp($row_OrienSexual['ID_OrienSexual'],$row_ValorDefault['IdOrienSexual'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_OrienSexual['DE_OrienSexual'];?></option>
											<?php }?>
											</select>
										</div>
									</div>
								   <div class="form-group">
										<label class="col-lg-1 control-label">Etnia</label>
										<div class="col-lg-2">
											<select name="Etnia" class="form-control" id="Etnia" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_Etnias=sqlsrv_fetch_array($SQL_Etnias)){?>
													<option value="<?php echo $row_Etnias['ID_Etnias'];?>" <?php if((isset($row['CDU_IdEtnias']))&&(strcmp($row_Etnias['ID_Etnias'],$row['CDU_IdEtnias'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdEtnia']))&&(strcmp($row_Etnias['ID_Etnias'],$row_ValorDefault['IdEtnia'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Etnias['DE_Etnias'];?></option>
											<?php }?>
											</select>
										</div>
									    <label class="col-lg-1 control-label">Discapacidad</label>
										<div class="col-lg-2">
											<select name="Discapacidad" class="form-control" id="Discapacidad" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_Discapacidad=sqlsrv_fetch_array($SQL_Discapacidad)){?>
													<option value="<?php echo $row_Discapacidad['ID_Discapacidad'];?>" <?php if((isset($row['CDU_IdDiscapacidad']))&&(strcmp($row_Discapacidad['ID_Discapacidad'],$row['CDU_IdDiscapacidad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Discapacidad['DE_Discapacidad'];?></option>
											<?php }?>
											</select>
										</div>
										<label class="col-lg-1 control-label">Nivel de educación</label>
										<div class="col-lg-3">
											<select name="NivelEduca" class="form-control" id="NivelEduca" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_NivelEduca=sqlsrv_fetch_array($SQL_NivelEduca)){?>
													<option value="<?php echo $row_NivelEduca['ID_NivelEduca'];?>" <?php if((isset($row['CDU_IdNivelEduca']))&&(strcmp($row_NivelEduca['ID_NivelEduca'],$row['CDU_IdNivelEduca'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdNivelEduca']))&&(strcmp($row_NivelEduca['ID_NivelEduca'],$row_ValorDefault['IdNivelEduca'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_NivelEduca['DE_NivelEduca'];?></option>
											<?php }?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-bank"></i> Información tributaria</h3></label>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Régimen tributario</label>
										<div class="col-lg-3">
											<select name="RegimenTributario" class="form-control" id="RegimenTributario" required>
												<option value="">Seleccione...</option>
											<?php
												while($row_RegimenT=sqlsrv_fetch_array($SQL_RegimenT)){?>
													<option value="<?php echo $row_RegimenT['ID_RegimenTributario'];?>" <?php if((isset($row['U_HBT_RegTrib']))&&(strcmp($row_RegimenT['ID_RegimenTributario'],$row['U_HBT_RegTrib'])==0)){ echo "selected=\"selected\"";}elseif((isset($row_ValorDefault['IdRegTributario']))&&(strcmp($row_RegimenT['ID_RegimenTributario'],$row_ValorDefault['IdRegTributario'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_RegimenT['RegimenTributario'];?></option>
											<?php }?>
											</select>
										</div>
										<label class="col-lg-1 control-label">Municipio MM</label>
										<div class="col-lg-3">
											<input name="ID_MunicipioMM" type="hidden" id="ID_MunicipioMM" value="<?php if($edit==1){echo $row_MunMM['ID_Municipio'];}elseif(isset($row_Municipio['ID_Municipio'])){echo $row_Municipio['ID_Municipio'];}?>">
											<input name="MunicipioMM" type="text" class="form-control" id="MunicipioMM" placeholder="Digite para buscar..." value="<?php if($edit==1){echo $row_MunMM['DE_Municipio'];}elseif(isset($row_Municipio['DE_Municipio'])){echo $row_Municipio['DE_Municipio'];}?>">
										</div>
									</div>
								   <?php if($edit==1){?>
									<div class="form-group">
										<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-credit-card"></i> Datos de finanzas</h3></label>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Saldo de cuenta</label>
										<div class="col-lg-2">
											<input name="Balance" type="text" class="form-control" id="Balance" value="<?php echo number_format($row['Balance'],2);?>" readonly="readonly">
										</div>
										<label class="col-lg-2 control-label">Limite de crédito</label>
										<div class="col-lg-2">
											<input name="LimiteCredito" type="text" class="form-control" id="LimiteCredito" value="<?php echo number_format($row['Balance'],2);?>" readonly="readonly">
										</div>
										<label class="col-lg-2 control-label">Crédito consumido</label>
										<div class="col-lg-2">
											<input name="CreditoConsumido" type="text" class="form-control" id="CreditoConsumido" value="<?php echo number_format($row['Balance'],2);?>" readonly="readonly">
										</div>
									</div>
								   <?php }?>
							   </div>
							   <div id="tabSN-2" class="tab-pane">
									<br>
										<?php $Cont=1;
										if($edit==1&&$Num_Cont>0){
											$row_Cont=sql_fetch_array($SQL_Cont,2);
											do{ ?>
										<div id="divCtc_<?php echo $Cont;?>" class="bg-muted p-sm m-t-md"> 
										<div class="form-group">
											<label class="col-lg-1 control-label">Nombre</label>
											<div class="col-lg-3">
												<input type="text" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="NombreContacto[]" id="NombreContacto<?php echo $Cont;?>" value="<?php if($row_Cont['NombreContacto']!=""){ echo utf8_encode($row_Cont['NombreContacto']);}else{echo utf8_encode($row_Cont['ID_Contacto']);}?>" required>
											</div>
											<label class="col-lg-1 control-label">Segundo nombre</label>
											<div class="col-lg-3">
												<input type="text" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="SegundoNombre[]" id="SegundoNombre<?php echo $Cont;?>" value="<?php echo utf8_encode($row_Cont['SegundoNombre']);?>">
											</div>
											<label class="col-lg-1 control-label">Apellidos</label>
											<div class="col-lg-3">
												<input type="text" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="Apellidos[]" id="Apellidos<?php echo $Cont;?>" value="<?php echo utf8_encode($row_Cont['Apellidos']);?>" required>
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Cédula</label>
											<div class="col-lg-3">
												<input type="text" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" maxlength="15" onKeyPress="return justNumbers(event,this.value);" name="CedulaContacto[]" id="CedulaContacto<?php echo $Cont;?>" value="<?php echo $row_Cont['CedulaContacto'];?>">
											</div>
											<label class="col-lg-1 control-label">Teléfono</label>
											<div class="col-lg-3">
												<input type="text" maxlength="20" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="Telefono[]" id="Telefono<?php echo $Cont;?>" value="<?php echo $row_Cont['Telefono1'];?>" required>
											</div>
											<label class="col-lg-1 control-label">Celular</label>
											<div class="col-lg-3">
												<input type="text" maxlength="50" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="TelefonoCelular[]" id="TelefonoCelular<?php echo $Cont;?>" value="<?php echo $row_Cont['TelefonoCelular'];?>">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Actividad económica</label>
											<div class="col-lg-3">
												<select name="ActEconomica[]" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" id="ActEconomica<?php echo $Cont;?>" required>
													<option value="">Seleccione...</option>
													<option value="EMPLEADO" <?php if($row_Cont['ActEconomica']=='EMPLEADO'){echo "selected=\"selected\"";} ?>>EMPLEADO</option>
													<option value="INDEPENDIENTE" <?php if($row_Cont['ActEconomica']=='INDEPENDIENTE'){echo "selected=\"selected\"";} ?>>INDEPENDIENTE</option>
													<option value="OTRO" <?php if($row_Cont['ActEconomica']=='OTRO'){echo "selected=\"selected\"";} ?>>OTRO</option>
												</select>
											</div>		
											<label class="col-lg-1 control-label">Rep. Legal</label>
											<div class="col-lg-3">
												<select name="RepLegal[]" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" id="RepLegal<?php echo $Cont;?>" required>
													<option value="NO" <?php if($row_Cont['RepLegal']=='NO'){echo "selected=\"selected\"";}?>>NO</option>
													<option value="SI" <?php if($row_Cont['RepLegal']=='SI'){echo "selected=\"selected\"";}?>>SI</option>
												</select>
											</div>
											<label class="col-lg-1 control-label">Email</label>
											<div class="col-lg-3">
												<input type="email" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');ValidarEmail('<?php echo $Cont;?>');" class="form-control" name="Email[]" id="Email<?php echo $Cont;?>" value="<?php echo $row_Cont['CorreoElectronico'];?>">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Cargo/Vínculo</label>
											<div class="col-lg-3">
												<input type="text" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="Posicion[]" id="Posicion<?php echo $Cont;?>" value="<?php echo $row_Cont['Posicion'];?>" required>
											</div>
											<label class="col-lg-1 control-label">Grupo correo</label>
											<div class="col-lg-3">
												<select name="GrupoCorreo[]" id="GrupoCorreo<?php echo $Cont;?>" class="form-control">
													<option value="">(Ninguno)</option>
												<?php 
											   		$SQL_GrupoCorreo=Seleccionar('uvw_Sap_tbl_GrupoCorreo','*');
													while($row_GrupoCorreo=sqlsrv_fetch_array($SQL_GrupoCorreo)){?>
														<option value="<?php echo $row_GrupoCorreo['ID_GrupoCorreo'];?>" <?php if((strcmp($row_GrupoCorreo['ID_GrupoCorreo'],$row_Cont['IdGrupoCorreo'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_GrupoCorreo['DE_GrupoCorreo'];?></option>
												<?php }?>
												</select>
											</div>
											<div class="col-lg-1">
												<div id="spinEmail<?php echo $Cont;?>" style="visibility: hidden;" class="sk-spinner sk-spinner-wave">
													<div class="sk-rect1"></div>
													<div class="sk-rect2"></div>
													<div class="sk-rect3"></div>
													<div class="sk-rect4"></div>
													<div class="sk-rect5"></div>
												</div>
											</div>
											<div class="col-lg-3" id="ValEmail<?php echo $Cont;?>"></div>
										</div>
										<input id="CodigoContacto<?php echo $Cont;?>" name="CodigoContacto[]" type="hidden" value="<?php echo $row_Cont['CodigoContacto'];?>" />
										<input id="MetodoCtc<?php echo $Cont;?>" name="MetodoCtc[]" type="hidden" value="0" />
										<?php if($Metod!=4){?><button type="button" id="btnCtc<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del"><i class="fa fa-minus"></i> Remover</button><?php }?>
										<br><br>
										</div>
										<?php 
												$Cont++;
											} while($row_Cont=sql_fetch_array($SQL_Cont,2));
										} ?>
										<div id="divCtc_<?php echo $Cont;?>" class="bg-muted p-sm m-t-md"> 
										<div class="form-group">
											<label class="col-lg-1 control-label">Nombre</label>
											<div class="col-lg-3">
												<input type="text" class="form-control" name="NombreContacto[]" id="NombreContacto<?php echo $Cont;?>" value="" required>
											</div>
											<label class="col-lg-1 control-label">Segundo nombre</label>
											<div class="col-lg-3">
												<input type="text" class="form-control" name="SegundoNombre[]" id="SegundoNombre<?php echo $Cont;?>" value="">
											</div>
											<label class="col-lg-1 control-label">Apellidos</label>
											<div class="col-lg-3">
												<input type="text" class="form-control" name="Apellidos[]" id="Apellidos<?php echo $Cont;?>" value="" required>
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Cédula</label>
											<div class="col-lg-3">
												<input type="text" class="form-control" maxlength="15" onKeyPress="return justNumbers(event,this.value);" name="CedulaContacto[]" id="CedulaContacto<?php echo $Cont;?>" required>
											</div>	
											<label class="col-lg-1 control-label">Teléfono</label>
											<div class="col-lg-3">
												<input type="text" maxlength="20" class="form-control" name="Telefono[]" id="Telefono<?php echo $Cont;?>" value="" required>
											</div>
											<label class="col-lg-1 control-label">Celular</label>
											<div class="col-lg-3">
												<input type="text" maxlength="50" class="form-control" name="TelefonoCelular[]" id="TelefonoCelular<?php echo $Cont;?>" value="">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Actividad económica</label>
											<div class="col-lg-3">
												<select name="ActEconomica[]" class="form-control" id="ActEconomica<?php echo $Cont;?>" required>
													<option value="">Seleccione...</option>
													<option value="EMPLEADO">EMPLEADO</option>
													<option value="INDEPENDIENTE">INDEPENDIENTE</option>
													<option value="OTRO">OTRO</option>
												</select>
											</div>
											<label class="col-lg-1 control-label">Rep. Legal</label>
											<div class="col-lg-3">
												<select name="RepLegal[]" class="form-control" id="RepLegal<?php echo $Cont;?>">
													<option value="NO">NO</option>
													<option value="SI">SI</option>
												</select>
											</div>
											<label class="col-lg-1 control-label">Email</label>
											<div class="col-lg-3">
												<input type="email" class="form-control" name="Email[]" id="Email<?php echo $Cont;?>" value="" onChange="ValidarEmail('<?php echo $Cont;?>');">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Cargo/Vínculo</label>
											<div class="col-lg-3">
												<input type="text" class="form-control" name="Posicion[]" id="Posicion<?php echo $Cont;?>" value="" required>
											</div>
											<label class="col-lg-1 control-label">Grupo correo</label>
											<div class="col-lg-3">
												<select name="GrupoCorreo[]" id="GrupoCorreo<?php echo $Cont;?>" class="form-control">
													<option value="">(Ninguno)</option>
												<?php
													$SQL_GrupoCorreo=Seleccionar('uvw_Sap_tbl_GrupoCorreo','*');
													while($row_GrupoCorreo=sqlsrv_fetch_array($SQL_GrupoCorreo)){?>
														<option value="<?php echo $row_GrupoCorreo['ID_GrupoCorreo'];?>"><?php echo $row_GrupoCorreo['DE_GrupoCorreo'];?></option>
												<?php }?>
												</select>
											</div>
											<div class="col-lg-1">
												<div id="spinEmail<?php echo $Cont;?>" style="visibility: hidden;" class="sk-spinner sk-spinner-wave">
													<div class="sk-rect1"></div>
													<div class="sk-rect2"></div>
													<div class="sk-rect3"></div>
													<div class="sk-rect4"></div>
													<div class="sk-rect5"></div>
												</div>
											</div>
											<div class="col-lg-3" id="ValEmail<?php echo $Cont;?>"></div>											
										</div>
										<input id="CodigoContacto<?php echo $Cont;?>" name="CodigoContacto[]" type="hidden" value="0" />
										<input id="MetodoCtc<?php echo $Cont;?>" name="MetodoCtc[]" type="hidden" value="1" />
										<button type="button" id="btnCtc<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldCtc(this);"><i class="fa fa-plus"></i> Añadir otro</button>
										<br><br>
										</div>
							   </div>
							   <div id="tabSN-3" class="tab-pane">
									<br>
										<?php $Cont=1;
										if($edit==1&&$Num_Dir>0){
											$row_Dir=sql_fetch_array($SQL_Dir,2);
											do{ ?>
										<div id="div_<?php echo $Cont;?>" class="bg-muted p-sm m-t-md">
											<div class="form-group">
												<label class="col-lg-1 control-label">Tipo dirección</label>
												<div class="col-lg-4">
												  <select name="AdresType[]" onChange="CambiarMetodo('<?php echo $Cont;?>');" id="AdresType<?php echo $Cont;?>" class="form-control" required>
														<option value="B" <?php if($row_Dir['TipoDireccion']=='B'){echo "selected=\"selected\"";}?>>DIRECCIÓN DE FACTURACIÓN</option>
														<option value="S" <?php if($row_Dir['TipoDireccion']=='S'){echo "selected=\"selected\"";}?>>DIRECCIÓN DE ENVÍO</option>
													</select>
												</div>
												<label class="col-lg-1 control-label">Nombre dirección</label>
												<div class="col-lg-4">
													<input name="Address[]" type="text" required class="form-control" id="Address<?php echo $Cont;?>" onChange="CambiarMetodo('<?php echo $Cont;?>');" value="<?php echo $row_Dir['NombreSucursal'];?>" maxlength="50" readonly="readonly">
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Dirección</label>
												<div class="col-lg-4">
													<input name="Street[]" onChange="CambiarMetodo('<?php echo $Cont;?>');" type="text" required class="form-control" id="Street<?php echo $Cont;?>" maxlength="100" value="<?php echo utf8_encode($row_Dir['Direccion']);?>">
												</div>
												<label class="col-lg-1 control-label">Departamento</label>
												<div class="col-lg-4">
													<select name="County[]" id="County<?php echo $Cont;?>" class="form-control" required onChange="BuscarCiudad('<?php echo $Cont;?>');BuscarCodigoPostal('<?php echo $Cont;?>');CambiarMetodo('<?php echo $Cont;?>');">
														<option value="">Seleccione...</option>
													<?php
														while($row_Dptos=sqlsrv_fetch_array($SQL_Dptos)){?>
															<option value="<?php echo $row_Dptos['DeDepartamento'];?>" <?php if((strcmp($row_Dptos['DeDepartamento'],$row_Dir['Departamento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Dptos['DeDepartamento'];?></option>
													<?php }?>
													</select>
												</div>
											</div>				
											<div class="form-group">										
												<label class="col-lg-1 control-label">Ciudad</label>
												<div class="col-lg-4">
													<select name="City[]" onChange="BuscarBarrio('<?php echo $Cont;?>');CambiarMetodo('<?php echo $Cont;?>');" id="City<?php echo $Cont;?>" class="form-control" required>
														<option value="">Seleccione...</option>
													<?php
														$SQL_City=Seleccionar('uvw_Sap_tbl_SN_Municipio','Distinct ID_Municipio, DE_Municipio',"DeDepartamento='".$row_Dir['Departamento']."'",'DE_Municipio');
														while($row_City=sqlsrv_fetch_array($SQL_City)){?>
															<option value="<?php echo $row_City['ID_Municipio'];?>" <?php if((strcmp($row_City['ID_Municipio'],$row_Dir['IdMunicipio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_City['DE_Municipio'];?></option>
													<?php }?>
													</select>
												</div>
												<label class="col-lg-1 control-label">Barrio</label>
												<div class="col-lg-4">
													<select name="Block[]" onChange="CambiarMetodo('<?php echo $Cont;?>');" id="Block<?php echo $Cont;?>" class="form-control" required>
														<option value="">Seleccione...</option>
													<?php
														$SQL_Barrio=Seleccionar('uvw_Sap_tbl_Barrios','*',"IdMunicipio='".$row_Dir['IdMunicipio']."'",'DeBarrio');
														while($row_Barrio=sqlsrv_fetch_array($SQL_Barrio)){?>
															<option value="<?php echo $row_Barrio['IdBarrio'];?>" <?php if((strcmp($row_Barrio['IdBarrio'],$row_Dir['IdBarrio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Barrio['DeBarrio'];?></option>
													<?php }?>
													</select>
												</div>
											</div>
											<div class="form-group">													
												<label class="col-lg-1 control-label">Estrato</label>
												<div class="col-lg-2">
													<select name="Estrato[]" id="Estrato<?php echo $Cont;?>" class="form-control" required onChange="CambiarMetodo('<?php echo $Cont;?>');">
													<?php
														while($row_Estrato=sqlsrv_fetch_array($SQL_Estrato)){?>
															<option value="<?php echo $row_Estrato['ID_Estrato'];?>" <?php if((strcmp($row_Estrato['ID_Estrato'],$row_Dir['Estrato'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Estrato['Estrato'];?></option>
													<?php }?>
													</select>
												</div>
												<div class="col-lg-2"></div>
												<label class="col-lg-1 control-label">Código postal</label>
												<div class="col-lg-3">
													<select name="CodigoPostal[]" onChange="CambiarMetodo('<?php echo $Cont;?>');" id="CodigoPostal<?php echo $Cont;?>" class="form-control" required>
														<option value="">Seleccione...</option>
													<?php
														$SQL_CodigoPostal=Seleccionar('uvw_Sap_tbl_CodigosPostales','*',"DeDepartamento='".$row_Dir['Departamento']."'",'ID_CodigoPostal');
														while($row_CodigoPostal=sqlsrv_fetch_array($SQL_CodigoPostal)){?>
															<option value="<?php echo $row_CodigoPostal['ID_CodigoPostal'];?>" <?php if((strcmp($row_CodigoPostal['ID_CodigoPostal'],$row_Dir['CodigoPostal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CodigoPostal['DeCodigoPostal'];?></option>
													<?php }?>
													</select>
												</div>
												<label class="col-lg-1 control-label">Dirección del contrato</label>
												<div class="col-lg-2">
													<select name="DirContrato[]" id="DirContrato<?php echo $Cont;?>" class="form-control" required onChange="CambiarMetodo('<?php echo $Cont;?>');">
														<option value="0">NO</option>
														<option value="1">SI</option>
													</select>
												</div>
											</div>
										<input id="LineNum<?php echo $Cont;?>" name="LineNum[]" type="hidden" value="<?php echo $row_Dir['NumeroLinea'];?>" />
										<input id="Metodo<?php echo $Cont;?>" name="Metodo[]" type="hidden" value="0" />
										<?php if($Metod!=4){?><button type="button" id="<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del"><i class="fa fa-minus"></i> Remover</button><?php }?>
										<br><br>
										</div>
										<?php 
												$Cont++;
												$SQL_Dptos=Seleccionar('uvw_Sap_tbl_SN_Municipio','Distinct DeDepartamento','','DeDepartamento');
											    
											    $SQL_Estrato=Seleccionar('tbl_EstratosSN','*','','Estrato');
											} while($row_Dir=sql_fetch_array($SQL_Dir,2));
										} ?>
										<div id="div_<?php echo $Cont;?>" class="bg-muted p-sm m-t-md">
											<div class="form-group">
												<label class="col-lg-1 control-label">Tipo dirección</label>
												<div class="col-lg-4">
												  <select name="AdresType[]" id="AdresType<?php echo $Cont;?>" class="form-control" required>
														<option value="B" <?php if((isset($row_ValorDefault['IdTipoDireccion']))&&($row_ValorDefault['IdTipoDireccion']=="B")){ echo "selected=\"selected\"";} ?>>DIRECCIÓN DE FACTURACIÓN</option>
														<option value="S" <?php if((isset($row_ValorDefault['IdTipoDireccion']))&&($row_ValorDefault['IdTipoDireccion']=="S")){ echo "selected=\"selected\"";} ?>>DIRECCIÓN DE ENVÍO</option>
													</select>
												</div>
												<label class="col-lg-1 control-label">Nombre dirección</label>
												<div class="col-lg-4">
													<input name="Address[]" type="text" required class="form-control" id="Address<?php echo $Cont;?>" maxlength="50">
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Dirección</label>
												<div class="col-lg-4">
													<input name="Street[]" type="text" required class="form-control" id="Street<?php echo $Cont;?>" maxlength="100">
												</div>
												<label class="col-lg-1 control-label">Departamento</label>
												<div class="col-lg-4">
													<select name="County[]" id="County<?php echo $Cont;?>" class="form-control" required onChange="BuscarCiudad('<?php echo $Cont;?>');BuscarCodigoPostal('<?php echo $Cont;?>');">
														<option value="">Seleccione...</option>
													<?php
														while($row_Dptos=sqlsrv_fetch_array($SQL_Dptos)){?>
															<option value="<?php echo $row_Dptos['DeDepartamento'];?>" <?php if((isset($row_ValorDefault['IdDepartamento']))&&(strcmp($row_Dptos['DeDepartamento'],$row_ValorDefault['IdDepartamento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Dptos['DeDepartamento'];?></option>
													<?php }?>
													</select>
												</div>
											</div>				
											<div class="form-group">
												<label class="col-lg-1 control-label">Ciudad</label>
												<div class="col-lg-4">
													<select name="City[]" id="City<?php echo $Cont;?>" onChange="BuscarBarrio('<?php echo $Cont;?>');" class="form-control" required>
														<option value="">Seleccione...</option>
													<?php
														if(isset($row_ValorDefault['IdMunicipio'])){
															$SQL_City=Seleccionar('uvw_Sap_tbl_SN_Municipio','Distinct ID_Municipio, DE_Municipio',"DeDepartamento='".$row_ValorDefault['IdDepartamento']."'",'DE_Municipio');
														while($row_City=sqlsrv_fetch_array($SQL_City)){?>
															<option value="<?php echo $row_City['ID_Municipio'];?>" <?php if((strcmp($row_City['ID_Municipio'],$row_ValorDefault['IdMunicipio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_City['DE_Municipio'];?></option>
													<?php }
														}?>
													</select>
												</div>
												<label class="col-lg-1 control-label">Barrio</label>
												<div class="col-lg-4">
													<select name="Block[]" id="Block<?php echo $Cont;?>" class="form-control" required>
														<option value="">Seleccione...</option>
													<?php
														if(isset($row_ValorDefault['IdBarrio'])){
															$SQL_Barrio=Seleccionar('uvw_Sap_tbl_Barrios','*',"IdMunicipio='".$row_ValorDefault['IdMunicipio']."'",'DeBarrio');
														while($row_Barrio=sqlsrv_fetch_array($SQL_Barrio)){?>
															<option value="<?php echo $row_Barrio['IdBarrio'];?>" <?php if((strcmp($row_Barrio['IdBarrio'],$row_ValorDefault['IdBarrio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Barrio['DeBarrio'];?></option>
													<?php }
														}?>
													</select>
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Estrato</label>
												<div class="col-lg-2">
													<select name="Estrato[]" id="Estrato<?php echo $Cont;?>" class="form-control" required>
													<?php
														while($row_Estrato=sqlsrv_fetch_array($SQL_Estrato)){?>
															<option value="<?php echo $row_Estrato['ID_Estrato'];?>" <?php if((isset($row_ValorDefault['IdEstrato']))&&(strcmp($row_Estrato['ID_Estrato'],$row_ValorDefault['IdEstrato'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Estrato['Estrato'];?></option>
													<?php }?>
													</select>
												</div>
												<div class="col-lg-2"></div>
												<label class="col-lg-1 control-label">Código postal</label>
												<div class="col-lg-3">
													<select name="CodigoPostal[]" id="CodigoPostal<?php echo $Cont;?>" class="form-control" required>
														<?php if(!isset($row_ValorDefault['IdCodigoPostal'])){?><option value="">Seleccione...</option><?php }?>
													<?php
														if(isset($row_ValorDefault['IdCodigoPostal'])){
															$SQL_CodigoPostal=Seleccionar('uvw_Sap_tbl_CodigosPostales','*',"ID_CodigoPostal='".$row_ValorDefault['IdCodigoPostal']."'",'ID_CodigoPostal');
														while($row_CodigoPostal=sqlsrv_fetch_array($SQL_CodigoPostal)){?>
															<option value="<?php echo $row_CodigoPostal['ID_CodigoPostal'];?>" <?php if((strcmp($row_CodigoPostal['ID_CodigoPostal'],$row_ValorDefault['IdCodigoPostal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CodigoPostal['DeCodigoPostal'];?></option>
													<?php }
														}?>
													</select>
												</div>
												<label class="col-lg-1 control-label">Dirección del contrato</label>
												<div class="col-lg-2">
													<select name="DirContrato[]" id="DirContrato<?php echo $Cont;?>" class="form-control" required>
														<option value="0">NO</option>
														<option value="1">SI</option>
													</select>
												</div>
											</div>
										<input id="LineNum<?php echo $Cont;?>" name="LineNum[]" type="hidden" value="0" />
										<input id="Metodo<?php echo $Cont;?>" name="Metodo[]" type="hidden" value="1" />
										<button type="button" id="<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addField(this);"><i class="fa fa-plus"></i> Añadir otro</button>
										<br><br>
										</div>	
							   </div>
							   <?php if($edit==1){?>
							   <div id="tabSN-4" class="tab-pane">
									<br>
<div class="tabs-container">
	<ul class="nav nav-tabs">
		<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-laptop"></i> ID servicios</a></li>
		<li><a data-toggle="tab" href="#tab-2" onClick="ConsultarTab('2');"><i class="fa fa-phone"></i> Llamadas de servicios</a></li>
		<li><a data-toggle="tab" href="#tab-3" onClick="ConsultarTab('3');"><i class="fa fa-calendar"></i> Actividades</a></li>
		<li><a data-toggle="tab" href="#tab-4"><i class="fa fa-file-text"></i> Facturas pendientes</a></li>
		<li><a data-toggle="tab" href="#tab-5" onClick="ConsultarTab('5');"><i class="fa fa-money"></i> Pagos realizados</a></li>
		<li><a data-toggle="tab" href="#tab-6"><i class="fa fa-suitcase"></i> Historico de cartera</a></li>
	</ul>
	<div class="tab-content">
		<div id="tab-1" class="tab-pane active">
			<div class="panel-body">
				<br>
				<div class="table-responsive">
				<table class="table table-striped table-bordered table-hover dataTables-example" >
					<thead>
					<tr>
						<th>Código servicio</th>
						<th>Nombre servicio</th>
						<th>Grupo de servicio</th>
						<th>Sucursal</th>
						<th>Contrato</th>
						<th>Precio</th>
						<th>Dirección</th>
						<th>Posición</th>
						<th>OLT</th>
						<th>Estado ID</th>
						<th>Estado servicio</th>
						<th>Acciones</th>
					</tr>
					</thead>
					<tbody>
					<?php while($row_IDServicio=sql_fetch_array($SQL_IDServicio,2)){ 
						?>
						 <tr class="gradeX tooltip-demo">
							<td><?php echo $row_IDServicio['ItemCode'];?></td>
							<td><?php echo $row_IDServicio['ItemName'];?></td>						
							<td><?php echo $row_IDServicio['ItmsGrpNam'];?></td>
							<td><?php echo $row_IDServicio['NombreSucursal'];?></td>
							<td><?php if($row_IDServicio['IdContrato']!=""){?><a href="contratos.php?id=<?php echo base64_encode($row_IDServicio['IdContrato']);?>&tl=1" class="btn btn-link btn-xs" target="_blank"><?php echo $row_IDServicio['IdContrato'];?></a><?php }?></td>							 
							<td><?php echo "$".number_format($row_IDServicio['Precio'],0);?></td>
							<td><?php echo utf8_encode($row_IDServicio['DireccionSucursal']);?></td>
							<td><?php echo $row_IDServicio['Posicion'];?></td>
							<td><?php echo $row_IDServicio['DeOLT'];?></td>
							<td><span <?php if($row_IDServicio['Estado']=='Y'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row_IDServicio['NombreEstado'];?></span></td>
							<td <?php if($row_IDServicio['CDU_EstadoServicio']=='Y'){echo "class='text-success'";}else{echo "class='text-danger'";}?>><?php echo $row_IDServicio['CDU_NomEstadoServicio'];?></td>
							<td><a href="articulos.php?id=<?php echo base64_encode($row_IDServicio['ItemCode']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('socios_negocios.php');?>" class="alkin btn btn-link btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
						</tr>
					<?php }?>
					</tbody>
				</table>
				</div>															
			</div>	
		</div>
		<div id="tab-2" class="tab-pane">
			<div id="dv_llamadasrv" class="panel-body">

			</div>	
		</div>
		<div id="tab-3" class="tab-pane">
			<div id="dv_actividades" class="panel-body">

			</div>	
		</div>
		<div id="tab-4" class="tab-pane">
			<div class="panel-body">
				<div class="form-group">
					<div class="col-lg-12">
						<div class="table-responsive">
						<table class="table table-striped table-bordered">
							<thead>
							<tr>
								<th>Número</th>
								<th>Fecha contabilización</th>
								<th>Fecha vencimiento</th>
								<th>Valor factura</th>
								<th>Abono</th>
								<th>Dias vencidos</th>
								<th>Saldo total</th>						
								<th>Acciones</th>
								<th>Seleccionar</th>
							</tr>
							</thead>
							<tbody>
							<?php while($row_FactPend=sqlsrv_fetch_array($SQL_FactPend)){?>
								 <tr>
									<td><?php echo $row_FactPend['NoDocumento'];?></td>
									<td><?php if($row_FactPend['FechaContabilizacion']->format('Y-m-d')){echo $row_FactPend['FechaContabilizacion']->format('Y-m-d');}else{echo $row_FactPend['FechaContabilizacion'];}?></td>
									<td><?php if($row_FactPend['FechaVencimiento']->format('Y-m-d')){echo $row_FactPend['FechaVencimiento']->format('Y-m-d');}else{echo $row_FactPend['FechaVencimiento'];}?></td>
									<td><?php echo "$".number_format($row_FactPend['TotalDocumento'],2);?></td>
									<td><?php echo "$".number_format($row_FactPend['ValorPagoDocumento'],2);?></td>
									<td><?php echo number_format($row_FactPend['DiasVencidos'],0);?></td>
									<td><?php echo "$".number_format($row_FactPend['SaldoDocumento'],2);?></td>
									<td><a href="sapdownload.php?id=<?php echo base64_encode('15');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row_FactPend['NoInterno']);?>&ObType=<?php echo base64_encode('13');?>&IdFrm=<?php echo base64_encode($row_FactPend['Series']);?>" target="_blank" class="btn btn-link btn-xs"><i class="fa fa-download"></i> Descargar</a></td> 
									<td><div class="checkbox checkbox-success"><input type="checkbox" id="singleCheckbox<?php echo $row_FactPend['NoDocumento'];?>" value="" onChange="SeleccionarFactura('<?php echo base64_encode($row_FactPend['NoInterno']);?>','<?php echo base64_encode('13');?>','<?php echo base64_encode($row_FactPend['Series']);?>');" aria-label="Single checkbox One"><label></label></div></td>
								</tr>
							<?php }?>
								<tr id="dwnAllFact" style="display:none">
									<td colspan="9" class="text-right">
										<input type="hidden" id="FactSel" name="FactSel" value="" />
										<input type="hidden" id="FactFrm" name="FactFrm" value="" />
										<a id="LinkAllFact" href="#" target="_blank" class="btn btn-link btn-xs"><i class="fa fa-download"></i> Descargar facturas seleccionadas</a>
									</td>
								</tr>
							</tbody>
						</table>
						</div>
					</div>
				</div>	
			</div>	
		</div>
		<div id="tab-5" class="tab-pane">
			<div id="dv_pagosreal" class="panel-body">

			</div>	
		</div>
		<div id="tab-6" class="tab-pane">
			<div class="panel-body">
				<div class="form-group">
					<div class="col-lg-12">
						<div class="table-responsive">
						<table class="table table-bordered" >
							<thead>
							<tr>
								<th>Tipo gestión</th>
								<th>Destino</th>
								<th>Evento</th>
								<th>Resultado</th>
								<th>Comentario</th>
								<th>Causa no pago</th>
								<th>Acuerdo de pago</th>
								<th>Fecha registro</th>
								<th>Usuario</th>
							</tr>
							</thead>
							<tbody>
							<?php while($row_HistGestion=sqlsrv_fetch_array($SQL_HistGestion)){?>
								 <tr class="gradeX">
									<td><?php echo $row_HistGestion['TipoGestion'];?></td>
									<td><?php echo $row_HistGestion['Destino'];?></td>
									<td><?php echo $row_HistGestion['NombreEvento'];?></td>
									<td><?php echo $row_HistGestion['ResultadoGestion'];?></td>
									<td><?php echo $row_HistGestion['Comentarios'];?></td>
									<td><?php echo $row_HistGestion['CausaNoPago'];?></td>
									<td><?php if($row_HistGestion['AcuerdoPago']==1){echo "SI";}else{echo "NO";}?></td>
									<td><?php echo $row_HistGestion['FechaRegistro']->format('Y-m-d H:i');?></td>
									<td><?php echo $row_HistGestion['Usuario'];?></td>
								</tr>
							<?php }?>
							</tbody>
						</table>
				  </div>
					</div>
				</div>	
			</div>	
		</div>			
	</div>
</div>
							   </div>
							   <?php } ?>
							   <?php if($edit==1){?>
							   <div id="tabSN-5" class="tab-pane">
									<br>
									<div id="dv_contratos" class="panel-body">										
										
									</div>																		
							   </div>
							   <?php }?>
							   </form>
							   <div id="tabSN-6" class="tab-pane">
									<br>
								   	<div id="dv_anexos" class="panel-body">
										
									</div>																					
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
	 $("#EditarSN").validate({
		 submitHandler: function(form){
		 <?php if($edit==1&&$Metod==""){?>
			 $('.ibox-content').toggleClass('sk-loading');
			 form.submit();
		 <?php }elseif($edit==0||$Metod==4){?>
			 $('.ibox-content').toggleClass('sk-loading',true);
			 if(Validar()){
				 form.submit();
			 }else{
				 $('.ibox-content').toggleClass('sk-loading',false);
			 }
		 <?php }?>
		}
	});
	 $(".alkin").on('click', function(){
		 $('.ibox-content').toggleClass('sk-loading');
	 });
	 
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
	 
	$(".select2").select2();
	
	<?php if($edit==0&&$EsProyecto==1){ ?>
		$('#CardType option:not(:selected)').attr('disabled',true);
		$('#TipoEntidad option:not(:selected)').attr('disabled',true);
		<?php if($row_ValorDefault['IdGrupoSN']!=""){?>
		$('#GroupCode option:not(:selected)').attr('disabled',true);
		<?php }?>
		<?php if($row_ValorDefault['IdCondiPago']!=""){?>
		$('#GroupNum option:not(:selected)').attr('disabled',true);
		<?php }?>
		<?php if($row_ValorDefault['IdIndustria']!=""){?>
		$('#Industria option:not(:selected)').attr('disabled',true);
		<?php }?>
		<?php if($row_ValorDefault['IdTerritorio']!=""){?>
		$('#Territorio option:not(:selected)').attr('disabled',true);
		<?php }?>
		<?php if($row_ValorDefault['IdProyecto']!=""){?>
		$('#Proyecto option:not(:selected)').attr('disabled',true);
		<?php }?>
		<?php if($row_ValorDefault['IdCapServicio']!=""){?>
		$('#CapacidadServ option:not(:selected)').attr('disabled',true);
		<?php }?>
		<?php if($row_ValorDefault['IdVigServicio']!=""){?>
		$('#VigenciaCont option:not(:selected)').attr('disabled',true);
		<?php }?>
		<?php if($row_ValorDefault['IdRegTributario']!=""){?>
		$('#RegimenTributario option:not(:selected)').attr('disabled',true); 
		<?php }?>
	<?php }?>
	 
	<?php if($Metod==4){ ?>
	$('#Proyecto option:not(:selected)').attr('disabled',true);
	$('#CardType option:not(:selected)').attr('disabled',true);
 	<?php }?>
	
	<?php if(PermitirFuncion(504)){ ?>
		$('#CardType option:not(:selected)').attr('disabled',true);
 	<?php }?>
	 
	$('.dataTables-example').DataTable({
                pageLength: 10,
                dom: '<"html5buttons"B>lTfgitp',
				order: [[ 0, "desc" ]],
				language: {
					"decimal":        "",
					"emptyTable":     "No se encontraron resultados.",
					"info":           "Mostrando _START_ - _END_ de _TOTAL_ registros",
					"infoEmpty":      "Mostrando 0 - 0 de 0 registros",
					"infoFiltered":   "(filtrando de _MAX_ registros)",
					"infoPostFix":    "",
					"thousands":      ",",
					"lengthMenu":     "Mostrar _MENU_ registros",
					"loadingRecords": "Cargando...",
					"processing":     "Procesando...",
					"search":         "Filtrar:",
					"zeroRecords":    "Ningún registro encontrado",
					"paginate": {
						"first":      "Primero",
						"last":       "Último",
						"next":       "Siguiente",
						"previous":   "Anterior"
					},
					"aria": {
						"sortAscending":  ": Activar para ordenar la columna ascendente",
						"sortDescending": ": Activar para ordenar la columna descendente"
					}
				},
                buttons: []

            });
 });
</script>
<?php if($edit==0||$Metod==4){?>	
<script>
function Validar(){
	var result=true;
	
	//Validar se la cedula es numero
	var Cedula = document.getElementById("LicTradNum");
	if(isNaN(Cedula.value)){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'La cedula del cliente no es un valor numerico. Por favor valide.',
			type: 'warning'
		});
	}
	
	//Campos adicionales
	var Genero = document.getElementById("Genero");
	var Sexo = document.getElementById("Sexo");
	var OrienSexual = document.getElementById("OrienSexual");
	var Etnia = document.getElementById("Etnia");
	var Discapacidad = document.getElementById("Discapacidad");
	var NivelEduca = document.getElementById("NivelEduca");
	
	if(Genero.value=="" || Sexo.value=="" || OrienSexual.value=="" || Etnia.value=="" || Discapacidad.value=="" || NivelEduca.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe ingresar todos los campos adicionales en la informacion del cliente.',
			type: 'warning'
		});
	}
	
	//Datos de las Direcciones
	var NomDireccion = document.getElementsByName("Address[]");
	var countNomDireccionLleno=0;
	var cantDirFact=0;
	var cantDirEnv=0;
	for(var i=0;i<NomDireccion.length;i++){
		if(NomDireccion[i].value!=''){
			countNomDireccionLleno++;
			var DirID = parseInt(NomDireccion[i].id.replace('Address',''));
			var AdresType=document.getElementById("AdresType"+DirID);
			var Direccion = document.getElementById("Street"+DirID);
			var Departamento = document.getElementById("County"+DirID);
			var Ciudad = document.getElementById("City"+DirID);
			var Barrio = document.getElementById("Block"+DirID);
			var CodigoPostal = document.getElementById("CodigoPostal"+DirID);
			
			if(AdresType.value=="B"){
				cantDirFact++;
			}else if(AdresType.value=="S"){
				cantDirEnv++;
			}
			if(Direccion.value==""){
				result=false;
				swal({
					title: '¡Advertencia!',
					text: 'Debe ingresar todas las direcciones',
					type: 'warning'
				});
			}
			if(Departamento.value==""){
				result=false;
				swal({
					title: '¡Advertencia!',
					text: 'Debe ingresar todos los departamentos en las direcciones',
					type: 'warning'
				});
			}
			if(Ciudad.value==""){
				result=false;
				swal({
					title: '¡Advertencia!',
					text: 'Debe ingresar todas las ciudades en las direcciones',
					type: 'warning'
				});
			}
			if(Barrio.value==""){
				result=false;
				swal({
					title: '¡Advertencia!',
					text: 'Debe ingresar todos los barrios en las direcciones',
					type: 'warning'
				});
			}
			if(CodigoPostal.value==""){
				result=false;
				swal({
					title: '¡Advertencia!',
					text: 'Debe ingresar los codigos postales en las direcciones',
					type: 'warning'
				});
			}
		}
	}
	if(countNomDireccionLleno==0){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe tener por lo menos una dirección',
			type: 'warning'
		});
	}
	
	<?php if($Metod==4){?>	
	
	if(cantDirEnv==0){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe tener por lo menos una dirección de envío',
			type: 'warning'
		});
	}
	if(cantDirFact==0){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe tener por lo menos una dirección de facturación',
			type: 'warning'
		});
	}
	
	<?php }?>
	
	//Contar direcciones contrato
	var DirContrato = document.getElementsByName("DirContrato[]");
	var countDirContrato=0;
	for(var i=0;i<DirContrato.length;i++){
		if(DirContrato[i].value==1){
			countDirContrato++;
		}
	}
	
	if(countDirContrato>1){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Solo debe tener una dirección como dirección del contrato',
			type: 'warning'
		});
	}else if(countDirContrato==0){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe seleccionar una dirección como dirección del contrato',
			type: 'warning'
		});
	}
	
	//Representante legal
	var RepLegal=document.getElementsByName("RepLegal[]");
	var countRepLegal=0;
	for(var i=0;i<RepLegal.length;i++){
		if(RepLegal[i].value=='SI'){
			countRepLegal++;
		}
	}
	
	if(countRepLegal==0){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe haber por lo menos un Representante legal entre los contactos.',
			type: 'warning'
		});
	}else if(countRepLegal>1){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Solo debe haber un Representante legal entre los contactos.',
			type: 'warning'
		});	
	}
	
	//Nombre del cliente
	var CardName = document.getElementById("CardName");
	var PNNombres = document.getElementById("PNNombres");
	var PNApellido1 = document.getElementById("PNApellido1");
	var PNApellido2 = document.getElementById("PNApellido2");
	if(CardName.value=="" || PNNombres.value=="" || PNApellido1.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe ingresar el nombre del cliente.',
			type: 'warning'
		});
	}
	
	//Datos del cliente
	var CapacidadServ = document.getElementById("CapacidadServ");
	var GroupCode = document.getElementById("GroupCode");
	var GroupNum = document.getElementById("GroupNum");	
	var Industria = document.getElementById("Industria");
	var Territorio = document.getElementById("Territorio");
	var Proyecto = document.getElementById("Proyecto");
	var CapacidadServ = document.getElementById("CapacidadServ");
	var VigenciaCont = document.getElementById("VigenciaCont");
	var RegimenTributario = document.getElementById("RegimenTributario");
	var ID_MunicipioMM = document.getElementById("ID_MunicipioMM");
	
	if(CapacidadServ.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe seleccionar la capacidad del servicio.',
			type: 'warning'
		});
	}
	
	if(GroupCode.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe seleccionar el grupo del cliente.',
			type: 'warning'
		});
	}
	
	if(GroupNum.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe seleccionar la condicion de pago del cliente.',
			type: 'warning'
		});
	}
	
	if(Industria.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe seleccionar la industria del cliente.',
			type: 'warning'
		});
	}
	
	if(Territorio.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe seleccionar el territorio del cliente.',
			type: 'warning'
		});
	}
	
	if(Proyecto.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe seleccionar el proyecto del cliente.',
			type: 'warning'
		});
	}
	
	if(CapacidadServ.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe seleccionar la capacidad del servicio del cliente.',
			type: 'warning'
		});
	}
	
	if(VigenciaCont.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe seleccionar la vigencia del contrato del cliente.',
			type: 'warning'
		});
	}
	
	if(RegimenTributario.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe seleccionar el regimen tributario del cliente.',
			type: 'warning'
		});
	}
	
	if(ID_MunicipioMM.value==""){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe seleccionar el municipio MM del cliente.',
			type: 'warning'
		});
	}
	
	<?php if($EsProyecto==1&&$row_ValorDefault['PedirAnexo']=="SI"){?>
	//Firma
	if(document.getElementById("SigCliente")){
		var AnxFirma = document.getElementById("SigCliente");
		if(AnxFirma.value==""){
			result=false;
			swal({
				title: '¡Advertencia!',
				text: 'No se ha firmado el contrato. Por favor verifique.',
				type: 'warning'
			});
		}
	}else{
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'No se ha firmado el contrato. Por favor verifique.',
			type: 'warning'
		});
	}
	<?php }?>
	//Datos del contacto
	var NombreContacto = document.getElementsByName("NombreContacto[]");
	var countNombreContactoLleno=0;
	for(var i=0;i<NombreContacto.length;i++){
		if(NombreContacto[i].value!=''){
			countNombreContactoLleno++;
			var CntcID = parseInt(NombreContacto[i].id.replace('NombreContacto',''));
			var CedulaContacto = document.getElementById("CedulaContacto"+CntcID);
			var TelefonoContacto = document.getElementById("Telefono"+CntcID);
			var TelefonoCelular = document.getElementById("TelefonoCelular"+CntcID);
			var Email = document.getElementById("Email"+CntcID);
			var Posicion = document.getElementById("Posicion"+CntcID);
			var ActEconomica = document.getElementById("ActEconomica"+CntcID);
			var RepLegal = document.getElementById("RepLegal"+CntcID);
			var GrupoCorreo = document.getElementById("GrupoCorreo"+CntcID);
			
			if(RepLegal.value=="SI"){
				if(CedulaContacto.value==""){
					result=false;
					swal({
						title: '¡Advertencia!',
						text: 'Debe ingresar la cedula en el Representate legal (pestaña Contactos)',
						type: 'warning'
					});
				}
				if(Email.value==""){
					result=false;
					swal({
						title: '¡Advertencia!',
						text: 'Debe ingresar todos los correos en los contactos',
						type: 'warning'
					});
				}
				if(TelefonoCelular.value==""){
					result=false;
					swal({
						title: '¡Advertencia!',
						text: 'Debe ingresar todos los celulares en los contactos',
						type: 'warning'
					});
				}
				if(GrupoCorreo.value==""){
					result=false;
					swal({
						title: '¡Advertencia!',
						text: 'Debe seleccionar un Grupo de correo al Rep. Legal',
						type: 'warning'
					});
				}	
			}			
			
			if(TelefonoContacto.value==""){
				result=false;
				swal({
					title: '¡Advertencia!',
					text: 'Debe ingresar todos los telefonos en los contactos',
					type: 'warning'
				});
			}				
			if(Posicion.value==""){
				result=false;
				swal({
					title: '¡Advertencia!',
					text: 'Debe seleccionar un cargo en todos los contactos',
					type: 'warning'
				});
			}
			if(ActEconomica.value==""){
				result=false;
				swal({
					title: '¡Advertencia!',
					text: 'Debe seleccionar una actividad economica en todos los contactos',
					type: 'warning'
				});
			}
		}
	}
	if(countNombreContactoLleno==0){
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe tener por lo menos un contacto',
			type: 'warning'
		});
	}
	
	for(var i=0;i<NombreContacto.length;i++){
		if(NombreContacto[i].value!=''){
			var CntcID = parseInt(NombreContacto[i].id.replace('NombreContacto',''));
			var Nombre = document.getElementById("NombreContacto"+CntcID);
			var SegundoNombre = document.getElementById("SegundoNombre"+CntcID);
			var Apellidos = document.getElementById("Apellidos"+CntcID);
			var NomCompleto= Nombre.value + SegundoNombre.value + Apellidos.value;
			var Cant=0;
			for(var j=0;j<NombreContacto.length;j++){
				if(NombreContacto[j].value!=''){
					var CntcIDAct = parseInt(NombreContacto[j].id.replace('NombreContacto',''));
					var NombreAct = document.getElementById("NombreContacto"+CntcIDAct);
					var SegundoNombreAct = document.getElementById("SegundoNombre"+CntcIDAct);
					var ApellidosAct = document.getElementById("Apellidos"+CntcIDAct);
					var NomCompletoAct= NombreAct.value + SegundoNombreAct.value + ApellidosAct.value;
					if(NomCompleto==NomCompletoAct){
						Cant++;
					}
				}				
			}
			if(Cant>1){
				result=false;
				swal({
					title: '¡Advertencia!',
					text: 'Hay contactos repetidos',
					type: 'warning'
				});
			}
		}		
	}
	
	<?php if($EsProyecto==1&&$row_ValorDefault['PedirAnexo']=="SI"){?>
	//Campos de anexos
	if(document.getElementById("FileCC1Val")){
		var FileCC1Val = document.getElementById("FileCC1Val");
		var FileCC2Val = document.getElementById("FileCC2Val");
		var FileSPVal = document.getElementById("FileSPVal");
		var FilePRVal = document.getElementById("FilePRVal");
		
		if(FileCC1Val.value=="" || FileCC2Val.value=="" || FileSPVal.value=="" || FilePRVal.value==""){
			result=false;
			swal({
				title: '¡Advertencia!',
				text: 'Faltan anexos por cargar.',
				type: 'warning'
			});
		}
	}else{
		result=false;
		swal({
			title: '¡Advertencia!',
			text: 'Debe cargar los anexos.',
			type: 'warning'
		});
	}
	<?php }?>
		
	return result;
}
</script>
<?php }?>
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
	$newClone.children("div").eq(0).children("div").eq(0).children("select").eq(0).attr('id','AdresType'+newID);
	$newClone.children("div").eq(1).children("div").eq(1).children("select").eq(0).attr('id','County'+newID);
	$newClone.children("div").eq(1).children("div").eq(1).children("select").eq(0).attr('onChange','BuscarCiudad('+newID+');BuscarCodigoPostal('+newID+');');
	$newClone.children("div").eq(2).children("div").eq(0).children("select").eq(0).attr('id','City'+newID);
	$newClone.children("div").eq(2).children("div").eq(0).children("select").eq(0).attr('onChange','BuscarBarrio('+newID+');');
	$newClone.children("div").eq(2).children("div").eq(1).children("select").eq(0).attr('id','Block'+newID);
	$newClone.children("div").eq(3).children("div").eq(0).children("select").eq(0).attr('id','Estrato'+newID);
	$newClone.children("div").eq(3).children("div").eq(2).children("select").eq(0).attr('id','CodigoPostal'+newID);
	$newClone.children("div").eq(3).children("div").eq(3).children("select").eq(0).attr('id','DirContrato'+newID);
	
	//$newClone.children("div").eq(1).children("div").eq(1).children("select").eq(0).select2('destroy');
	//$newClone.children("div").eq(1).children("div").eq(1).children("select").eq(0).select2();

	//inputs
	$newClone.children("div").eq(0).children("div").eq(1).children("input").eq(0).attr('id','Address'+newID);
	$newClone.children("div").eq(1).children("div").eq(0).children("input").eq(0).attr('id','Street'+newID);
	
	$newClone.children("input").eq(0).attr('id','LineNum'+newID);
	$newClone.children("input").eq(1).attr('id','Metodo'+newID);

	//button
	$newClone.children("button").eq(0).attr('id',''+newID);

	$newClone.insertAfter($('#div_'+clickID));

	//$("#"+clickID).val('Remover');
	document.getElementById(''+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById(''+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById(''+clickID).setAttribute('onClick','delRow2(this);');

	//$("#"+clickID).addEventListener("click",delRow);

	//$("#"+clickID).bind("click",delRow);
}
	
function addFieldCtc(btn){//Clonar divCtc
	var clickID = parseInt($(btn).parent('div').attr('id').replace('divCtc_',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	var newID = (clickID+1);

	$newClone = $('#divCtc_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divCtc_'+newID);

	//select
	$newClone.children("div").eq(2).children("div").eq(0).children("select").eq(0).attr('id','ActEconomica'+newID);
	$newClone.children("div").eq(2).children("div").eq(1).children("select").eq(0).attr('id','RepLegal'+newID);
	$newClone.children("div").eq(3).children("div").eq(1).children("select").eq(0).attr('id','GrupoCorreo'+newID);

	//inputs
	$newClone.children("div").eq(0).children("div").eq(0).children("input").eq(0).attr('id','NombreContacto'+newID);
	$newClone.children("div").eq(0).children("div").eq(1).children("input").eq(0).attr('id','SegundoNombre'+newID);
	$newClone.children("div").eq(0).children("div").eq(2).children("input").eq(0).attr('id','Apellidos'+newID);
	$newClone.children("div").eq(1).children("div").eq(0).children("input").eq(0).attr('id','CedulaContacto'+newID);
	$newClone.children("div").eq(1).children("div").eq(1).children("input").eq(0).attr('id','Telefono'+newID);
	$newClone.children("div").eq(1).children("div").eq(2).children("input").eq(0).attr('id','TelefonoCelular'+newID);
	$newClone.children("div").eq(2).children("div").eq(2).children("input").eq(0).attr('id','Email'+newID);
	$newClone.children("div").eq(3).children("div").eq(0).children("input").eq(0).attr('id','Posicion'+newID);
	
	//div
	$newClone.children("div").eq(3).children("div").eq(2).children("div").eq(0).attr('id','spinEmail'+newID);
	$newClone.children("div").eq(3).children("div").eq(3).attr('id','ValEmail'+newID);
	
	$newClone.children("input").eq(0).attr('id','CodigoContacto'+newID);
	$newClone.children("input").eq(1).attr('id','MetodoCtc'+newID);

	//button
	$newClone.children("button").eq(0).attr('id','btnCtc'+newID);

	$newClone.insertAfter($('#divCtc_'+clickID));

	//$("#"+clickID).val('Remover');
	document.getElementById('btnCtc'+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById('btnCtc'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnCtc'+clickID).setAttribute('onClick','delRow2(this);');
	
	document.getElementById('NombreContacto'+newID).value='';
	document.getElementById('SegundoNombre'+newID).value='';
	document.getElementById('Apellidos'+newID).value='';
	document.getElementById('CedulaContacto'+newID).value='';
	document.getElementById('Telefono'+newID).value='';
	document.getElementById('TelefonoCelular'+newID).value='';
	document.getElementById('Email'+newID).value='';
	document.getElementById('Posicion'+newID).value='REFERENCIA';
	document.getElementById('ActEconomica'+newID).value='OTRO';	
	document.getElementById('RepLegal'+newID).value='NO';
	document.getElementById('GrupoCorreo'+newID).value='';
	document.getElementById('ValEmail'+newID).innerHTML='';	

	//$("#"+clickID).addEventListener("click",delRow);

	//$("#"+clickID).bind("click",delRow);
}
</script>
<script>
	 $(document).ready(function(){
		 $(".btn_del").each(function (el){
			 $(this).bind("click",delRow);
		 });
		 
		 //Municipio MM
		  var options = {
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
				},
				onSelectItemEvent: function() {
					var value = $("#MunicipioMM").getSelectedItemData().Codigo;
					$("#ID_MunicipioMM").val(value).trigger("change");
				}
			}
		};

		$("#MunicipioMM").easyAutocomplete(options);

	});
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
 var tab_2=0;
 var tab_3=0;
 var tab_4=0;
 var tab_5=0;
 var tab_6=0;
 var tab_501=0;
 var tab_601=0;
	
function BuscarCiudad(id){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		url: "ajx_cbo_select.php?type=8&id="+document.getElementById('County'+id).value,
		success: function(response){
			$('#City'+id).html(response).fadeIn();
			$('#City'+id).trigger('change');
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
}
	
function BuscarCodigoPostal(id){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		url: "ajx_cbo_select.php?type=24&id="+document.getElementById('County'+id).value,
		success: function(response){
			$('#CodigoPostal'+id).html(response).fadeIn();
			//$('#CodigoPostal'+id).trigger('change');
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
}

function BuscarBarrio(id){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		url: "ajx_cbo_select.php?type=13&id="+document.getElementById('City'+id).value,
		success: function(response){
			$('#Block'+id).html(response).fadeIn();
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
}

function CambiarMetodo(id){
	var inpMetodo=document.getElementById("Metodo"+id);
	inpMetodo.value=2;
}

function CambiarMetodoCtc(id){
	var inpMetodo=document.getElementById("MetodoCtc"+id);
	inpMetodo.value=2;
}

function ConsultarTab(type){
	if(type==2){//Llamada de servicio
		if(tab_2==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "sn_llamadas_servicios.php?id=<?php if($edit==1){echo base64_encode($row['CodigoCliente']);}?>",
				success: function(response){
					$('#dv_llamadasrv').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_2=1;
				}
			});
		}
	}else if(type==3){//Actividades
		if(tab_3==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "sn_actividades.php?id=<?php if($edit==1){echo base64_encode($row['CodigoCliente']);}?>",
				success: function(response){
					$('#dv_actividades').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_3=1;
				}
			});
		}
	}else if(type==5){//Pagos realizados
		if(tab_5==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "sn_pagos_realizados.php?id=<?php if($edit==1){echo base64_encode($row['CodigoCliente']);}?>",
				success: function(response){
					$('#dv_pagosreal').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_5=1;
				}
			});
		}
	}else if(type==501){//Contratos
		if(tab_501==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "sn_contratos.php?id=<?php if($edit==1){echo base64_encode($row['CodigoCliente']);}?>",
				success: function(response){
					$('#dv_contratos').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_501=1;
				}
			});
		}
	}else if(type==601){//Anexos
		if(tab_601==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			var CC=document.getElementById("LicTradNum");
			var Lat=document.getElementById("Latitud");
			var Long=document.getElementById("Longitud");
			
			if(CC.value==""){
				$('.ibox-content').toggleClass('sk-loading',false);
				swal({
					title: '¡Advertencia!',
					text: 'Debe ingresar primero la cédula del cliente antes de ingresar los anexos.',
					type: 'warning'
				});
			}else if(Lat.value==""||Long.value==""){
				$('.ibox-content').toggleClass('sk-loading',false);
				swal({
					title: '¡Advertencia!',
					text: 'No se ha capturado la posición GPS. Verifique que su localización este activada e intente nuevamente.',
					type: 'warning'
				});
				CapturarGPS(); 
			}else{
				$.ajax({
					type: "POST",
					url: "sn_anexos.php?id=<?php if($edit==1){echo base64_encode($row['CodigoCliente']);}?>&edit=<?php echo $edit; ?>&anx=<?php if($edit==1){echo base64_encode($row['IdAnexos']);}?>&metod=<?php echo $Metod; ?>&esproyecto=<?php echo $EsProyecto;?>&pediranexos=<?php if($EsProyecto==1){ echo $row_ValorDefault['PedirAnexo'];}?>",
					success: function(response){
						$('#dv_anexos').html(response).fadeIn();
						$('.ibox-content').toggleClass('sk-loading',false);
						tab_601=1;
					}
				});
			}			
		}
	}
}
</script>

<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>