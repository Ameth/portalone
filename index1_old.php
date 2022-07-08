﻿<?php require_once("includes/conexion.php"); 
//require_once("includes/conexion_hn.php");
$sw_alert=0;//Indica que hay alertas
$sw_notify=0;//Indica que hay notificaciones
$Filtro="";//Filtro
$TotalDoc=0;//Total de documentos nuevos
$Num_CntDoc=0;//Cantidad de documentos nuevos
$TotalInf=0;//Total de informes nuevos
$Num_InfAct=0;//Cantidad de informes nuevos
$Num_Alertas=0;//Total de alertas actuales
$Num_CntFrm=0;//Cantidad de formularios
$Num_CntProd=0;//Cantidad de productos

if(PermitirFuncion(905)){//Dashboard de gestor de documentos

	//Restar 7 dias a la fecha actual
	$fecha = date('d/m/Y');
	$nuevafecha_7 = strtotime ( '-7 day' , time() ) ;
	$nuevafecha_7 = date ( 'd/m/Y' , $nuevafecha_7 );
	//echo $nuevafecha_7."<br>";

	//Restar 15 dias a la fecha actual
	$nuevafecha_15 = strtotime ( '-15 day' , time() ) ;
	$nuevafecha_15 = date ( 'd/m/Y' , $nuevafecha_15 );
	//echo $nuevafecha_15."<br>";


	//Contar documentos nuevos
	$ParamCntDoc=array(
		"'".$nuevafecha_7."'",
		"'".date('d/m/Y')."'",
		"'".$_SESSION['CodUser']."'",
		"1"
	);

	$SQL_CntDoc=EjecutarSP('sp_ContarDocumentosNuevos',$ParamCntDoc);
	$Num_CntDoc=sqlsrv_num_rows($SQL_CntDoc);
	//echo $Cons_CntDoc;
	//Para contar los documentos y mostrarlos solo en las actualizaciones
	/*if($Num_CntDoc>0){
		while($row_CntDoc=sqlsrv_fetch_array($SQL_CntDoc)){
			$TotalDoc=$TotalDoc+$row_CntDoc['Cuenta'];
		}
	}*/

	//Formularios
	$ParamCntFrm=array(
		"'".$_SESSION['CodUser']."'"
	);

	$SQL_CntFrm=EjecutarSP('sp_ContarFormularios',$ParamCntFrm);
	$Num_CntFrm=sqlsrv_num_rows($SQL_CntFrm);
	
	//Productos
	$ParamCntProd=array(
		"'".$_SESSION['CodUser']."'"
	);

	$SQL_CntProd=EjecutarSP('sp_ContarProductos',$ParamCntProd);
	$Num_CntProd=sqlsrv_num_rows($SQL_CntProd);

	//Contar informes nuevos
	if(PermitirFuncion(205)){
		//Informes
		$ParamInfAct=array(
			"'".$nuevafecha_15."'",
			"'".date('d/m/Y')."'",
			"'".$_SESSION['CodUser']."'",
			"1"
		);

		$SQL_InfAct=EjecutarSP('sp_ContarInformesNuevos',$ParamInfAct);
		$Num_InfAct=sqlsrv_num_rows($SQL_InfAct);
	}else{
		//Informes
		$ParamInfAct=array(
			"'".$nuevafecha_15."'",
			"'".date('d/m/Y')."'",
			"'".$_SESSION['CodUser']."'",
			"2"
		);

		$SQL_InfAct=EjecutarSP('sp_ContarInformesNuevos',$ParamInfAct);
		$Num_InfAct=sqlsrv_num_rows($SQL_InfAct);
	}
}

if(PermitirFuncion(901)){//Llamadas de servicio
	if(PermitirFuncion(205)){
		$SQL_Llamadas=EjecutarSP('sp_DashboardLlamadas','',0,2);
	}else{
		$SQL_Llamadas=EjecutarSP('sp_DashboardLlamadas',strtolower($_SESSION['User']),0,2);
	}
	$row_Llamadas=sql_fetch_array($SQL_Llamadas,2);
}

if(PermitirFuncion(903)){//Indicadores de cartera
	$SQL_Cartera=EjecutarSP('sp_DashboardCartera','',0,2);
	$row_Cartera=sql_fetch_array($SQL_Cartera,2);
}

if(PermitirFuncion(904)){//Indicadores de gestiones de cartera
	$SQL_Gestion=EjecutarSP('sp_DashboardGestionCartera');
	$row_Gestion=sql_fetch_array($SQL_Gestion);
}

if(PermitirFuncion(906)){//Indicadores de faturacion electronica
	$SQL_FactElect=Seleccionar('uvw_tbl_FacturacionElectronica_SeguimientoContadores','*');
	$row_FactElect=sql_fetch_array($SQL_FactElect);
}

if(PermitirFuncion(902)){//Dashboard de actividades enviadas y recibidas
	//Actividades recibidas
	//Fechas
	//Restar dias a la fecha actual
	/*$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDashboard").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	
	$FechaInicial_MisAct=$nuevafecha;
	$FechaFinal_MisAct=date('Y-m-d');*/


	$SQL_MisAct=Seleccionar('uvw_Sap_tbl_Actividades','TOP 10 *',"ID_EmpleadoActividad='".$_SESSION['CodigoSAP']."' And IdEstadoActividad='N'");
	$Num_MisAct=sqlsrv_num_rows($SQL_MisAct);

	//Actividades enviadas
	//Fechas
	//Restar dias a la fecha actual
	/*$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDashboard").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	
	$FechaInicial_ActAsig=$nuevafecha;
	$FechaFinal_ActAsig=date('Y-m-d');*/
	
	$SQL_ActAsig=Seleccionar('uvw_Sap_tbl_Actividades','TOP 10 *',"UsuarioCreacion='".$_SESSION['User']."' And IdEstadoActividad='N'");
	$Num_ActAsig=sqlsrv_num_rows($SQL_ActAsig);
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Inicio | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
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
	.edit1 {/*Widget editado por aordonez*/
		border-radius: 0px !important; 
		padding: 15px 20px;
		margin-bottom: 10px;
		margin-top: 10px;
		height: 120px !important;
	}
	.modal-lg {
		width: 50% !important;
	}
</style>
<?php if(!isset($_SESSION['SetCookie'])||($_SESSION['SetCookie']=="")){?>
<script>
$(document).ready(function(){
	$('#myModal').modal("show");
});
</script>
<?php }?>
<!-- InstanceEndEditable -->
</head>

<body class="mini-navbar">

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-6">
                    <h2>Bienvenido a <?php echo NOMBRE_PORTAL;?></h2>
                </div>
        </div>
        <?php 
		$Nombre_archivo="contrato_confidencialidad.txt";
		$Archivo=fopen($Nombre_archivo,"r");
		$Contenido = fread($Archivo, filesize($Nombre_archivo));
		?>
        <div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" data-show="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Acuerdo de confidencialidad</h4>
						<small>Por favor lea atentamente este contrato que contiene los T&eacute;rminos y Condiciones de uso de este sitio. Si continua usando este portal, consideramos que usted est&aacute; de acuerdo con ellos.</small>
					</div>
					<div class="modal-body">
						<?php echo $Contenido;?>
					</div>

					<div class="modal-footer">
						<button type="button" onClick="AceptarAcuerdo();" class="btn btn-primary" data-dismiss="modal">Acepto los t&eacute;rminos</button>
					</div>
				</div>
			</div>
		</div>
        <div class="row page-wrapper wrapper-content animated fadeInRight">
          <div class="col-lg-10 col-md-10">
			   <div class="ibox-content">
				   <?php include("includes/spinner.php"); ?>
         <?php 
		if($Num_InfAct>0){?>
			<div class="row">          
			  <h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Informes nuevos</h3>
			  <?php
				while($row_InfAct=sqlsrv_fetch_array($SQL_InfAct)){?>
					<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12">
						<div class="widget lazur-bg text-center edit1">
							<div class="m-b-md">
								<i class="fa fa-file-text fa-3x"></i>
								<h3 class="m-xs"><?php echo $row_InfAct['Cuenta'];?></h3>
								<h5 class="font-bold no-margins truncate">
									<a class='text-white' href='<?php echo $row_InfAct['URL'];?>?id=<?php echo base64_encode($row_InfAct['ID_Categoria']);?>&_nw=<?php echo base64_encode("NeW");?>'><?php echo $row_InfAct['NombreCategoria'];?></a>	
								</h5>
							</div>
						</div>	
					</div>
				<?php $TotalInf=$TotalInf+$row_InfAct['Cuenta'];} ?>				
			</div>
			<br>
		<?php }	?>
		<?php 
		 if($Num_CntFrm>0){?>
			<div class="row">
			<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-thumbs-o-up"></i> Informes personalizados</h3>
		<?php 
			while($row_CntFrm=sqlsrv_fetch_array($SQL_CntFrm)){?>
				<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12">
					<div class="widget yellow-bg text-center edit1">
						<div class="m-b-md">
							<i class="fa fa-pencil-square-o fa-3x"></i>
							<h5 class="font-bold no-margins truncate">
								<a class='text-white' href='<?php echo $row_CntFrm['URL'];?>?id=<?php echo base64_encode($row_CntFrm['ID_Categoria']);?>'><?php echo $row_CntFrm['NombreCategoria'];?></a>	
							</h5>
						</div>
					</div>	
				</div>
		<?php }?>
			</div>
			<br>
		<?php }?>
		<?php 
		 if($Num_CntProd>0){?>
			<div class="row">
			<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-tags"></i> Fichas técnicas y Hojas de seguridad</h3>
		<?php 
			while($row_CntProd=sqlsrv_fetch_array($SQL_CntProd)){?>
				<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12">
					<div class="widget lazur-bg text-center edit1">
						<div class="m-b-md">
							<i class="fa fa-book fa-3x"></i>
							<h5 class="font-bold no-margins truncate">
								<a class='text-white' href='<?php echo $row_CntProd['URL'];?>?id=<?php echo base64_encode($row_CntProd['ID_Categoria']);?>'><?php echo $row_CntProd['NombreCategoria'];?></a>	
							</h5>
						</div>
					</div>	
				</div>
		<?php }?>
			</div>
			<br>
		<?php }?>
		<?php 
		 if($Num_CntDoc>0){?>
			  <div class="row">
			  <h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Documentos nuevos</h3>
			  <?php
				while($row_CntDoc=sqlsrv_fetch_array($SQL_CntDoc)){?>
					<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12">
						<div class="widget navy-bg text-center edit1">
							<div class="m-b-md">
								<i class="fa fa-clipboard fa-3x"></i>
								<h4 class="m-xs"><?php echo $row_CntDoc['Cuenta'];?></h4>
								<h5 class="font-bold no-margins truncate">
									<a class='text-white' href='<?php echo $row_CntDoc['URL'];?>?id=<?php echo base64_encode($row_CntDoc['ID_Categoria']);?>&_nw=<?php echo base64_encode("NeW");?>'><?php echo $row_CntDoc['NombreCategoria'];?></a>	
								</h5>
							</div>
						</div>	
					</div>
				<?php $TotalDoc=$TotalDoc+$row_CntDoc['Cuenta'];}?>
				</div>
		<?php }?>  
		<?php if(PermitirFuncion(901)){?>
			<h3 class="bg-info p-xss b-r-xs"><i class="fa fa-line-chart"></i> Indicadores de gestión - Llamadas de servicio</h3>
				<div class="row">
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-success">Llamadas sin resolver</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-phone fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="LS_run1">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-success">Llamadas abiertas</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-phone fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="LS_run2">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-success">Llamadas cerradas</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-phone fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="LS_run3">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-danger">Total llamadas</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-phone fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="LS_run4">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				</div>
			<?php }?>
		<?php if(PermitirFuncion(903)){?>
			<h3 class="bg-primary p-xss b-r-xs"><i class="fa fa-line-chart"></i> Indicadores de cartera - Total cartera</h3>
				<div class="row">
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-info">01 - 30 días</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-money fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="CA_run1">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-info">31 - 60 días</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-money fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="CA_run2">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-info">61 - 90 días</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-money fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="CA_run3">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>				
				</div>
				<div class="row">
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-info">91 - 120 días</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-money fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="CA_run4">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-info">121 - 180 días</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-money fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="CA_run5">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-info">Mayor a 180 días</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-money fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="CA_run6">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>				
				</div>
			<?php }?>
		<?php if(PermitirFuncion(904)){?>
			<h3 class="bg-info p-xss b-r-xs"><i class="fa fa-line-chart"></i> Indicadores de cartera - Gestión de cartera</h3>
			<div class="row">
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-success">Llamadas</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-phone fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="GS_run1">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-success">Visitas</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-car fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="GS_run2">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5 class="text-success">Cartas</h5>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-4">
									<i class="fa fa-file-text fa-3x"></i>
								</div>
								<div class="col-lg-8 text-right">
									<h1 class="no-margins" id="GS_run3">0</h1>
								</div>
							</div>
						</div>
					</div>
				</div>
				</div>
			<?php }?>
		<?php if(PermitirFuncion(906)){//Facturacion electronica?>
			<h3 class="bg-info p-xss b-r-xs"><i class="fa fa-line-chart"></i> Indicadores de facturación electrónica</h3>
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
			<?php }?>
		<?php if(PermitirFuncion(902)){//Actividades?>
			<div class="row">
			<h3 class="bg-success p-xss b-r-sm"><i class="fa fa-tasks"></i> Tareas recibidas - <?php echo $Num_MisAct;?></h3>
				<div class="ibox-content">
					<form action="index1.php" method="get" id="formBuscar_MisAct" class="form-horizontal">
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover dataTables-example" >
							<thead>
								<tr>
									<th>Núm.</th>
									<th>Titulo</th>
									<th>Asignado por</th>
									<th>Fecha creación</th>
									<th>Fecha actividad</th>
									<th>Fecha limite</th>
									<th>Dias venc.</th>
									<th>Respuesta</th>
									<th>Acciones</th>
								</tr>
							</thead>
							<tbody>
							<?php while($row_MisAct=sqlsrv_fetch_array($SQL_MisAct)){
									$DVenc_MisAct=DiasTranscurridos(date('Y-m-d'),$row_MisAct['FechaFinActividad']);
									if(($DVenc_MisAct[1]>=-2)&&($DVenc_MisAct[1]<0)){
										$Clase="class='WarningColor'";
									}elseif($DVenc_MisAct[1]>0){
										$Clase="class='DangerColor'";
									}else{
										$Clase="class='InfoColor'";
									}
								?>
								<tr class="gradeX">
									<td <?php echo $Clase;?>><?php echo $row_MisAct['ID_Actividad'];?></td>
									<td><?php echo $row_MisAct['TituloActividad'];?></td>
									<td><?php echo $row_MisAct['DeAsignadoPor'];?></td>
									<td><?php if($row_MisAct['FechaCreacion']!=""){echo $row_MisAct['FechaCreacion'];}else{ echo "--";}?></td>
									<td><?php echo $row_MisAct['FechaHoraInicioActividad']->format('Y-m-d H:i');?></td>
									<td><?php echo $row_MisAct['FechaHoraFinActividad']->format('Y-m-d H:i');?></td>
									<td><p class='<?php echo $DVenc_MisAct[0];?>'><?php echo $DVenc_MisAct[1];?></p></td>
									<td><?php echo ConsultarNotasActividad($row_MisAct['ID_Actividad']);?></td>
									<td><a href="actividad.php?id=<?php echo base64_encode($row_MisAct['ID_Actividad']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('index1.php');?>" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
								</tr>
							<?php }?>
							</tbody>
						</table>
					</div>
					</form>
							</div>	
			</div>
			<br>
			<div class="row">
			<h3 class="bg-primary p-xss b-r-sm"><i class="fa fa-tasks"></i> Tareas enviadas - <?php echo $Num_ActAsig;?></h3>
				<div class="ibox-content">
					<form action="index1.php" method="get" id="formBuscar_ActAsig" class="form-horizontal">
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover dataTables-example" >
							<thead>
								<tr>
									<th>Núm.</th>
									<th>Titulo</th>
									<th>Asignado a</th>
									<th>Fecha creación</th>
									<th>Fecha actividad</th>
									<th>Fecha limite</th>													
									<th>Dias venc.</th>
									<th>Respuesta</th>
									<th>Acciones</th>
								</tr>
							</thead>
							<tbody>
							<?php while($row_ActAsig=sqlsrv_fetch_array($SQL_ActAsig)){
									$DVenc_ActAsi=DiasTranscurridos(date('Y-m-d'),$row_ActAsig['FechaFinActividad']);
									if(($DVenc_ActAsi[1]>=-2)&&($DVenc_ActAsi[1]<0)){
										$Clase="class='WarningColor'";
									}elseif($DVenc_ActAsi[1]>0){
										$Clase="class='DangerColor'";
									}else{
										$Clase="class='InfoColor'";
									}
								?>
								<tr class="gradeX">
									<td <?php echo $Clase;?>><?php echo $row_ActAsig['ID_Actividad'];?></td>
									<td><?php echo $row_ActAsig['TituloActividad'];?></td>
									<td><?php if($row_ActAsig['NombreEmpleado']!=""){echo $row_ActAsig['NombreEmpleado'];}else{echo "(Sin asignar)";}?></td>
									<td><?php if($row_ActAsig['FechaCreacion']!=""){echo $row_ActAsig['FechaCreacion'];}else{ echo "--";}?></td>
									<td><?php echo $row_ActAsig['FechaHoraInicioActividad']->format('Y-m-d H:i');?></td>
									<td><?php echo $row_ActAsig['FechaHoraFinActividad']->format('Y-m-d H:i');?></td>							
									<td><p class='<?php echo $DVenc_ActAsi[0];?>'><?php echo $DVenc_ActAsi[1];?></p></td>
									<td><?php echo ConsultarNotasActividad($row_ActAsig['ID_Actividad']);?></td>
									<td><a href="actividad.php?id=<?php echo base64_encode($row_ActAsig['ID_Actividad']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('index1.php');?>" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
								</tr>
							<?php }?>
							</tbody>
						</table>
					</div>
					</form>
				</div>	
			</div>
			<br>
		<?php }?>
		</div> 
		</div>  
		<?php if(($Num_InfAct>0)||($Num_CntDoc>0)){?>
        <div class="col-lg-2 col-md-2">
			<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
		 <h3 class="bg-success p-xs b-r-sm"><i class="fa fa-refresh"></i> Actualizaciones</h3>
			<div class="row">
			  <div class="col-lg-12">
				<div class="widget style1 navy-bg">
					<div class="row">
						<div class="col-xs-4">
							<i class="fa fa-clipboard fa-4x"></i>
						</div>
						<div class="col-xs-8 text-right">
							<span>Documentos nuevos</span>
							<h2 class="font-bold" id="run1">0</h2>
						</div>
					</div>
				</div>
			 </div>
			 <div class="col-lg-12">
				<div class="widget style1 lazur-bg">
					<div class="row">
						<div class="col-xs-4">
							<i class="fa fa-file-text fa-4x"></i>
						</div>
						<div class="col-xs-8 text-right">
							<span>Informes nuevos</span>
							<h2 class="font-bold" id="run2">0</h2>
						</div>
					</div>
				</div>
			 </div>
			  </div>
        	</div>
		</div>
		<?php }?>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>	
	 $(document).ready(function(){
		 $('.navy-bg').each(function() {
                animationHover(this, 'pulse');
            });
		  $('.yellow-bg').each(function() {
                animationHover(this, 'pulse');
            });
		 $('.lazur-bg').each(function() {
                animationHover(this, 'pulse');
            });
		 $(".truncate").dotdotdot({
            watch: 'window'
		  });
	});
</script>
<script>
var amount=<?php echo $TotalDoc;?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#run1").html(Math.round(now))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $TotalInf;?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#run2").html(Math.round(now))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $Num_Alertas;?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#run3").html(Math.round(now))
		},
		duration:1300,
		easing:"linear"
	});
</script>
<?php if(PermitirFuncion(901)){?>
<script>
var amount=<?php echo $row_Llamadas['LlamadasPendientes'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#LS_run1").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_Llamadas['LlamadasAbiertas'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#LS_run2").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_Llamadas['LlamadasCerradas'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#LS_run3").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_Llamadas['TotalLlamadas'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#LS_run4").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
</script>
<?php }?>
<?php if(PermitirFuncion(903)){?>
<script>
var amount=<?php echo $row_Cartera['Mes1a30'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#CA_run1").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_Cartera['Mes31a60'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#CA_run2").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_Cartera['Mes61a90'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#CA_run3").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_Cartera['Mes91a120'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#CA_run4").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_Cartera['Mes121a180'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#CA_run5").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_Cartera['Mes181'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#CA_run6").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
</script>
<?php }?>	
<?php if(PermitirFuncion(904)){?>
<script>
var amount=<?php echo $row_Gestion['CantLlamadas'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#GS_run1").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_Gestion['CantVisitas'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#GS_run2").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
var amount=<?php echo $row_Gestion['CantCartas'];?>;
	$({c:0}).animate({c:amount},{
		step: function(now){
			$("#GS_run3").html(number_format(Math.round(now),0))
		},
		duration:2000,
		easing:"linear"
	});
</script>
<?php }?>
<?php if(PermitirFuncion(906)){?>
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
<?php }?>
<?php if(isset($_GET['dt'])&&$_GET['dt']==base64_encode("result")){?>
<script>
	$(document).ready(function(){
		toastr.options = {
			closeButton: true,
			progressBar: true,
			showMethod: 'slideDown',
			timeOut: 6000
		};
		toastr.success('¡Su contraseña ha sido modificada!', 'Felicidades');
	});
</script>
<?php }?>
<script src="js/js_setcookie.js"></script>
<script>
        $(document).ready(function(){
            $('.dataTables-example').DataTable({
                pageLength: 10,
                responsive: true,
                dom: '<"html5buttons"B>lTfgitp',
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
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>