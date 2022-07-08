<?php require_once("includes/conexion.php");
sqlsrv_close($conexion);
?>
<!DOCTYPE html>
<html>

<head>
<?php include_once("includes/cabecera.php"); ?>
<title>Fuera de servicio | <?php echo NOMBRE_PORTAL;?></title>
</head>

<body class="gray-bg">


    <div class="middle-box text-center animated fadeInDown">
		 <img src="img/mant.jpg" alt="" width="100%" height="100%" />
       <br><br>
         <a href="index1.php" class="btn btn-primary btn-outline"><i class="fa fa-home"></i> Volver al Inicio</a>
    </div>
</body>

</html>
