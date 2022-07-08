
<?php

/*require('includes/pclzip.lib.php');
$zip = new PclZip('smb://DIALNETCO;Administrador:Asdf1234$@192.168.5.200/ReportesPortalOne/test.zip');
$zip->create('a.txt,b.txt');*/

$zip = new ZipArchive();
$zipName=date('YmdHi').".zip";

//$SrvRuta = "smb://DIALNETCO;Administrador:D14ln3t#2020*@192.168.5.200/ReportesPortalOne/";
$SrvRuta="PortalOne/archivos/InformesSAP/";

$filezip = $SrvRuta.$zipName;

//$fp_aux = fopen($filezip, "w+");
//fwrite($fp_aux,"Prueba desde Linux");
//fclose($fp_aux);

$res=$zip->open($filezip, ZIPARCHIVE::CREATE);
if($res===TRUE){
	$zip->addFile($SrvRuta."EstadoCartera_CL0000000004_AORDONEZ.pdf","Carpeta/Archivo1.pdf");
	$zip->addFile($SrvRuta."EstadoCartera_CL0000000004_AORDONEZ.pdf","Carpeta/Archivo2.pdf");
	$zip->addFile($SrvRuta."EstadoCartera_CL0000000004_AORDONEZ.pdf","Carpeta/Archivo3.pdf");
	echo "numficheros: " . $zip->numFiles . "\n";
	echo "estado:" . $zip->status . "\n";
	$zip->close();
}else{
	echo 'fallo, codigo:' . $res;
}


/*if ($zip->open($filezip, ZIPARCHIVE::CREATE)===TRUE) {
	$zip->addFile($SrvRuta."EstadoCartera_CL0000000004_AORDONEZ.pdf","EstadoCartera_CL0000000004_AORDONEZ.pdf");
	$zip->addFile($SrvRuta."EstadoCartera_CL0000000004_NDUARTE.pdf","EstadoCartera_CL0000000004_NDUARTE.pdf");
	$zip->addFile($SrvRuta."FacturaServicioDialnetF378043_AORDONEZ.pdf","FacturaServicioDialnetF378043_AORDONEZ.pdf");
	echo "numficheros: " . $zip->numFiles . "\n";
	echo "estado:" . $zip->status . "\n";
	$zip->close();	
}else{
	exit("No se puede abrir el archivo $filezip\n");
}*/

/*$size = filesize($filezip);

header("Content-Transfer-Encoding: binary"); 
header('Content-type: application/pdf', true);
header("Content-Type: application/force-download"); 
header('Content-Disposition: attachment; filename="'.$zipName.'"');
header("Content-Length: $size"); 
readfile($filezip);*/
?>
