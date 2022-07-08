<?php 
require_once("includes/conexion.php");
header('Content-Type: application/json; charset=utf-8');
$dir=CrearObtenerDirTemp();
$i=0;
$result=array();
if($_POST['id']==1){//Cedula frontal
	$NuevoNombre=NormalizarNombreArchivo(str_replace(" ","_",$_FILES['FileCC1']['name'][$i]));
	$NombreArchivo=$_POST['doc']."_CC1_".$NuevoNombre;
	//Buscr archivo para eliminarlo
	EliminarArchivo($dir."*_CC1_*");
	if(move_uploaded_file($_FILES['FileCC1']['tmp_name'][$i], $dir.$NombreArchivo)){
		RedimensionarImagen($NombreArchivo,$dir.$NombreArchivo,800,600);
		/*if(!RedimensionarImagen($NombreArchivo,$dir.$NombreArchivo,800,600)){
			$result['error']="Error al redimensionar el archivo ".$NombreArchivo;
		}*/
	}
	
}elseif($_POST['id']==2){//Cedula posterior
	$NuevoNombre=NormalizarNombreArchivo(str_replace(" ","_",$_FILES['FileCC2']['name'][$i]));
	$NombreArchivo=$_POST['doc']."_CC2_".$NuevoNombre;
	EliminarArchivo($dir."*_CC2_*");
	if(move_uploaded_file($_FILES['FileCC2']['tmp_name'][$i], $dir.$NombreArchivo)){
		RedimensionarImagen($NombreArchivo,$dir.$NombreArchivo,800,600);
		/*if(!RedimensionarImagen($NombreArchivo,$dir.$NombreArchivo,800,600)){
			$result['error']="Error al redimensionar el archivo ".$NombreArchivo;
		}*/
	}	
}
elseif($_POST['id']==3){//Servicios publicos
	$NuevoNombre=NormalizarNombreArchivo(str_replace(" ","_",$_FILES['FileSP']['name'][$i]));
	$NombreArchivo=$_POST['doc']."_SP_".$NuevoNombre;
	EliminarArchivo($dir."*_SP_*");
	if(move_uploaded_file($_FILES['FileSP']['tmp_name'][$i], $dir.$NombreArchivo)){
		RedimensionarImagen($NombreArchivo,$dir.$NombreArchivo,1024,768);
		/*if(!RedimensionarImagen($NombreArchivo,$dir.$NombreArchivo,800,600)){
			$result['error']="Error al redimensionar el archivo ".$NombreArchivo;
		}*/
	}
	
}
elseif($_POST['id']==4){//Predio
	$NuevoNombre=NormalizarNombreArchivo(str_replace(" ","_",$_FILES['FilePR']['name'][$i]));
	$NombreArchivo=$_POST['doc']."_PR_".$NuevoNombre;
	EliminarArchivo($dir."*_PR_*");
	if(move_uploaded_file($_FILES['FilePR']['tmp_name'][$i], $dir.$NombreArchivo)){
		RedimensionarImagen($NombreArchivo,$dir.$NombreArchivo,800,600);
		AgregarDatosImagen($dir.$NombreArchivo,$_POST['Lat'],$_POST['Long']);
		/*if(!RedimensionarImagen($NombreArchivo,$dir.$NombreArchivo,800,600)){
			$result['error']="Error al redimensionar el archivo ".$NombreArchivo;
		}*/
	}
	
}

/*$config[] = [
	'key' => $_POST['id'],
	'caption' => $NombreArchivo,
	'url' => 'includes/procedimientos.php?type=3&nombre='.$NombreArchivo, // server api to delete the file based on key
];*/

//$result = ['initialPreviewConfig' => $config, 'initialPreviewAsData' => true];
/*$cant=count($_FILES['File']['name']);




while($i<$cant){	
	
	
	//$Nuevo_NombreArchivo=str_replace(" ","_",$dir.$NombreArchivo)
	//rename($dir.$NombreArchivo,$Nuevo_NombreArchivo);
	$i++;
}*/
echo json_encode($result);

sqlsrv_close($conexion);
?>