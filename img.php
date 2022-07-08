<?php 
function RedimensionarImagen($nombreimg, $rutaimg, $xmax, $ymax){  
	$ext = end(explode('.',$nombreimg)); 

	if($ext == "jpg" || $ext == "jpeg")  
		$imagen = imagecreatefromjpeg($rutaimg);  
	elseif($ext == "png")  
		$imagen = imagecreatefrompng($rutaimg);  
	elseif($ext == "gif")  
		$imagen = imagecreatefromgif($rutaimg);  

	$x = imagesx($imagen);  
	$y = imagesy($imagen);  

	if($x <= $xmax && $y <= $ymax){
		//echo "<center>Esta imagen ya esta optimizada para los maximos que deseas.<center>";
		return $imagen;
	}

	if($x >= $y) {  
		$nuevax = $xmax;  
		$nuevay = $nuevax * $y / $x;  
	}  
	else {  
		$nuevay = $ymax;  
		$nuevax = $x / $y * $nuevay;  
	}  

	$img2 = imagecreatetruecolor($nuevax, $nuevay);  
	imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y); 
	imagejpeg($img2, $rutaimg);
	//unlink($archivos_carpeta);
	//echo "<center>La imagen se ha optimizado correctamente.</center>";
	//return $img2;
}

$i=0;//Archivos
$dir="PortalOneCopla/archivos/prueba/";	
$NuevoNombre="";
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

$cnt=0;

$j=0;
while($j<$CantFiles){
	if(!RedimensionarImagen($DocFiles[$j],$dir.$DocFiles[$j],300,400)){
		echo "Error al redimensionar el archivo: ".$DocFiles[$j];
		echo "<br>";
	}else{
		echo "Archivo procesado: ".$DocFiles[$j];
		echo "<br>";
		$cnt++;
	}
	$j++;
}
echo "Total archivos procesados: ".$cnt;
?>