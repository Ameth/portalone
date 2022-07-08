<?php 
require_once("includes/conexion.php"); 
$route=CrearObtenerDirTemp();

$cant=count($_FILES['File']['name']);

$i=0;
while($i<$cant){
	$NombreArchivo=NormalizarNombreArchivo(str_replace(" ","_",$_FILES['File']['name'][$i]));
	move_uploaded_file($_FILES['File']['tmp_name'][$i], $route.$NombreArchivo);
	//$Nuevo_NombreArchivo=str_replace(" ","_",$route.$NombreArchivo)
	//rename($route.$NombreArchivo,$Nuevo_NombreArchivo);
	$i++;
}

sqlsrv_close($conexion);
?>