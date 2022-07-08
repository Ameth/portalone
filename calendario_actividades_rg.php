<?php require_once("includes/conexion.php");
PermitirAcceso(303);

//Empleados
$Cons_EmpleadoActividad="Select * From uvw_Sap_tbl_Empleados Order by NombreEmpleado";
$SQL_EmpleadoActividad=sqlsrv_query($conexion,$Cons_EmpleadoActividad);

//Clientes
$Cons_Cliente="Select * From uvw_Sap_tbl_Clientes Order by NombreCliente";
$SQL_Cliente=sqlsrv_query($conexion,$Cons_Cliente);

$Filtro="";//Filtro
$sw=0;//Controlar si ya existe un filtro por el where y el and. 0 si no hay. 1 si ya hay.

//Empleados
if(isset($_POST['EmpleadoActividad'])&&$_POST['EmpleadoActividad']!=""){
	$FilEmpleado="";
	for($i=0;$i<count($_POST['EmpleadoActividad']);$i++){
		if($i==0){
			$FilEmpleado.="'".$_POST['EmpleadoActividad'][$i]."'";
		}else{
			$FilEmpleado.=",'".$_POST['EmpleadoActividad'][$i]."'";
		}		
	}
	if($sw==0){
		$Filtro.="Where ID_EmpleadoActividad IN (".$FilEmpleado.")";	
		$sw=1;
	}else{
		$Filtro.=" and ID_EmpleadoActividad IN (".$FilEmpleado.")";	
	}	
}

//Clientes
if(isset($_POST['ClienteActividad'])&&$_POST['ClienteActividad']!=""){
	if($sw==0){
		$Filtro.="Where ID_CodigoCliente='".$_POST['ClienteActividad']."'";
		$sw=1;
	}else{
		$Filtro.=" and ID_CodigoCliente='".$_POST['ClienteActividad']."'";
	}
	
}

$Cons="Select * From uvw_Sap_tbl_Actividades $Filtro";
//echo $Cons;
$SQL=sqlsrv_query($conexion,$Cons);
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Calendario de actividades</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

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
                    <h2>Calendario de actividades</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Servicios</a>
                        </li>
                        <li class="active">
                            <strong>Calendario de actividades</strong>
                        </li>
                    </ol>
                </div>
                <div class="col-sm-4">
                    <div class="title-action">
                        <a href="actividad_add.php" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Crear nueva actividad</a>
                    </div>
                </div>
               <?php  //echo $Cons;?>
            </div>
         <div class="wrapper wrapper-content">
			<div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
				  <form action="calendario_actividades.php" method="post" id="formFiltro" class="form-horizontal">
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Técnico</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="EmpleadoActividad[]" class="form-control m-b select2" multiple id="EmpleadoActividad">
								  <?php $j=0; 
									while($row_EmpleadoActividad=sqlsrv_fetch_array($SQL_EmpleadoActividad)){?>
										<option value="<?php echo $row_EmpleadoActividad['ID_Empleado'];?>" <?php if((isset($_POST['EmpleadoActividad'][$j])&&($_POST['EmpleadoActividad'][$j])!="")&&(strcmp($row_EmpleadoActividad['ID_Empleado'],$_POST['EmpleadoActividad'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_EmpleadoActividad['NombreEmpleado'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-4">
								<select name="ClienteActividad" class="form-control m-b select2" id="ClienteActividad">
										<option value="">(Todos)</option>
								  <?php while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){?>
										<option value="<?php echo $row_Cliente['CodigoCliente'];?>" <?php if((isset($_POST['ClienteActividad']))&&(strcmp($row_Cliente['CodigoCliente'],$_POST['ClienteActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Cliente['NombreCliente'];?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-3">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-filter"></i> Filtrar</button>
							</div>
						</div>
				 </form>
			</div>
			</div>
		  </div>
			<br>
			<div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						<div class="table-responsive">
							<div id="calendar"></div>
						</div>
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

    $(document).ready(function() {
		$(".select2").select2();
		
        /* initialize the calendar
         -----------------------------------------------------------------*/
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay,listWeek'
            },
			defaultView: 'agendaWeek',
            editable: false,
			timeFormat: 'hh:mm a',
			eventRender: function(event, element){
				element.qtip({
					content: {
						title: event.subtitle,
						text: event.description
					},
					position: {
						target: 'mouse',
						adjust: { x: 5, y: 5 }
					}
				});
			},
            events: [
			<?php 
				while($row=sqlsrv_fetch_array($SQL)){
					echo "{
						id: ".$row['ID_Actividad'].",
						title:'".$row['NombreCliente']." (".$row['NombreSucursal'].") - ".$row['TituloActividad']."',
						subtitle:'".$row['AsuntoLlamada']."',
						description:'".$row['ComentariosActividad']."',
						start: '".$row['FechaInicioActividad']->format('Y-m-d H:i')."',
						end: '".$row['FechaFinActividad']->format('Y-m-d H:i')."',
						allDay: false,
						textColor: '#ffffff',
						backgroundColor: '".$row['ColorPrioridadActividad']."',
						borderColor: '".$row['ColorPrioridadActividad']."',
						url: 'actividad_edit.php?id=".base64_encode($row['ID_Actividad'])."&return=".base64_encode($_SERVER['PHP_SELF'])."'
					},";
				}
			?>
            ]		
        });
    });
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>