<?php 
if(isset($_GET['file'])&&$_GET['file']!=""){
	require_once("includes/conexion.php");
	
	$file=base64_decode($_GET['file']);
	
	//Selecciono los datos del archivo
	$SQL=Seleccionar('uvw_tbl_Cartera_Gestion','*',"ID_Gestion='".$file."'");
	$row=sqlsrv_fetch_array($SQL);
	
	$filename=ObtenerVariable("IPElastix")."/llamada/monitor/".$row['FechaRegistro']->format('Y')."/".$row['FechaRegistro']->format('m')."/".$row['FechaRegistro']->format('d')."/".$row['CallFile'];
	
	//echo $filename;
	
	header('Location: '.$filename);
}
?>