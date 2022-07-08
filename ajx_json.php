<?php 
if((isset($_GET['type'])&&($_GET['type']!=""))||(isset($_POST['type'])&&($_POST['type']!=""))){
	require("includes/definicion.php");
	$_POST['BaseDatos']=BDPRO;
	require("includes/conect_srv.php");
	require("includes/funciones.php");
	header('Content-Type: application/json');
	if(isset($_GET['type'])&&($_GET['type']!="")){
		$type=$_GET['type'];
	}else{
		$type=$_POST['type'];
	}
	   
	if($type==1){//Consultar indicadores Facturacion electronica
		$SQL=Seleccionar("uvw_tbl_FacturacionElectronica_SeguimientoContadores","*");
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'FACTURAS_PENDIENTES' => $row['FACTURAS_PENDIENTES'],
			'FACTURAS_NOVEDADES' => $row['FACTURAS_NOVEDADES'],
			'NOTACREDITO_PENDIENTES' => $row['NOTACREDITO_PENDIENTES'],
			'NOTACREDITO_NOVEDADES' => $row['NOTACREDITO_NOVEDADES']
		);	
		echo json_encode($records);
	}
	sqlsrv_close($conexion);
}
?>