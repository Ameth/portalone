<?php require_once("includes/conexion.php");
PermitirAcceso(1403);

$sw=0;
$Proyecto="";
$Departamento="";
$Municipio="";
$SerieFactura=0;
$Cedula="";
$FechaFactura="";
$MesFactura=0;
$TipoFacturacion=0;

//Proyectos
$SQL_Proyectos=Seleccionar('uvw_Sap_tbl_Proyectos','*');

//Series de facturas
$ParamSerie=array(
	"'".$_SESSION['CodUser']."'",
	"'13'"
);
$SQL_Facturas=EjecutarSP('sp_ConsultarSeriesDocumentos',$ParamSerie);

//Departamento
$SQL_Departamento=Seleccionar('uvw_Sap_tbl_Clientes','DISTINCT Departamento');

//Municipio	
if(isset($_GET['Departamento'])&&$_GET['Departamento']!=""){	
	$SQL_Municipio=Seleccionar('uvw_Sap_tbl_Clientes','DISTINCT Municipio',"Departamento='".$_GET['Departamento']."'");
}

//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
	$sw=1;
}else{
	$FechaInicial=date('Y-m-d');
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
	$sw=1;
}else{
	$FechaFinal=date('Y-m-d');
}

//Filtros
if(isset($_GET['Proyecto'])&&$_GET['Proyecto']!=""){
	$Proyecto=$_GET['Proyecto'];
	$sw=1;
}

if(isset($_GET['Departamento'])&&$_GET['Departamento']!=""){
	$Departamento=$_GET['Departamento'];
	$sw=1;
}

if(isset($_GET['Municipio'])&&$_GET['Municipio']!=""){
	$Municipio=$_GET['Municipio'];
	$sw=1;
}

if(isset($_GET['SerieFactura'])&&$_GET['SerieFactura']!=""){
	$SerieFactura=$_GET['SerieFactura'];
	$sw=1;
}

if(isset($_GET['FechaFactura'])&&$_GET['FechaFactura']!=""){
	$FechaFactura=$_GET['FechaFactura'];
	$sw=1;
}

if(isset($_GET['TipoFacturacion'])&&$_GET['TipoFacturacion']!=""){
	$TipoFacturacion=$_GET['TipoFacturacion'];
	$sw=1;
}

if(isset($_GET['MesFactura'])&&$_GET['MesFactura']!=""){
	$MesFactura=$_GET['MesFactura'];
	$sw=1;
}

if($sw==1){
	$ParamCons=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Proyecto."'",
		"'".$SerieFactura."'",
		"'".$TipoFacturacion."'",
		"'".utf8_decode($Departamento)."'",
		"'".utf8_decode($Municipio)."'",
		"'".FormatoFecha($FechaFactura)."'",
		"'".$MesFactura."'",
		"'".strtolower($_SESSION['User'])."'"	
	);
	$SQL=EjecutarSP('sp_tbl_CreacionFacturaProyectos',$ParamCons,0,2);
	$row=sql_fetch_array($SQL,2);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Facturación de proyectos | <?php echo NOMBRE_PORTAL;?></title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ActAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido creada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
?>
<style>
.select2-container{ width: 100% !important; }
</style>
<script type="text/javascript">
	$(document).ready(function() {
		$("#Departamento").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Departamento=document.getElementById('Departamento').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=23&id="+Departamento,
				asyn: false,
				success: function(response){
					$('#Municipio').html(response).fadeIn();
					$('#Municipio').trigger('change');	
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});			
		});		
	});	
</script>
<script>
function EjecutarProceso(Tipo){
	var Evento = document.getElementById("IdEvento").value;
	var DGDetalle = document.getElementById("DGDetalle");
	
	Swal.fire({
		title: "Facturación",
		text: "¿Estás seguro que deseas generar las facturas?",
		icon: "info",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			$('.ibox-content').toggleClass('sk-loading',true);
			
			$.ajax({
				url:"ajx_ejecutar_json.php",
				data:{
					type:6,
					Evento:Evento,
					Tipo:Tipo
				},
				dataType:'json',
				success: function(data){
					if(data.Estado==1){
						$("#UltEjecucion").html(MostrarFechaHora());				
						DGDetalle.src="detalle_facturacion_proyectos.php";				
					}
					Swal.fire({
						title: data.Title,
						text: data.Mensaje,
						icon: data.Icon,
					});
					$('.ibox-content').toggleClass('sk-loading',false);
				},
				error: function(msg){
					Swal.fire({
						title: "¡Advertencia!",
						text: "Ocurrio un error",
						icon: "warning",
					});
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		}
	});		
}
</script>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Facturación de proyectos</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gestión de proyectos</a>
                        </li>
						 <li>
                            <a href="#">Asistentes</a>
                        </li>
                        <li class="active">
                            <strong>Facturación de proyectos</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="facturacion_proyectos.php" method="get" id="formBuscar" class="form-horizontal">
						<div class="form-group">
							<label class="col-lg-1 control-label">Fecha instalación</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
								</div>
							</div>
						 	<label class="col-lg-1 control-label">Proyecto</label>
							<div class="col-lg-3">
								<select name="Proyecto" class="form-control select2" id="Proyecto" required>
										<option value="">Seleccione...</option>
								  <?php while($row_Proyectos=sqlsrv_fetch_array($SQL_Proyectos)){?>
										<option value="<?php echo $row_Proyectos['IdProyecto'];?>" <?php if((isset($_GET['Proyecto']))&&(strcmp($row_Proyectos['IdProyecto'],$_GET['Proyecto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Proyectos['DeProyecto'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Serie factura</label>
							<div class="col-lg-3">
								<select name="SerieFactura" class="form-control" id="SerieFactura" required>
									<option value="">Seleccione...</option>
								  <?php while($row_Facturas=sqlsrv_fetch_array($SQL_Facturas)){?>
										<option value="<?php echo $row_Facturas['IdSeries'];?>" <?php if((isset($_GET['SerieFactura']))&&(strcmp($row_Facturas['IdSeries'],$_GET['SerieFactura'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Facturas['DeSeries'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Departamento</label>
							<div class="col-lg-3">
								<select name="Departamento" class="form-control select2" id="Departamento">
										<option value="">(TODOS)</option>
								  <?php while($row_Departamento=sqlsrv_fetch_array($SQL_Departamento)){?>
										<option value="<?php echo $row_Departamento['Departamento'];?>" <?php if((isset($_GET['Departamento']))&&(strcmp($row_Departamento['Departamento'],$_GET['Departamento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Departamento['Departamento'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Municipio</label>
							<div class="col-lg-3">
								<select name="Municipio" class="form-control select2" id="Municipio">
										<option value="">(TODOS)</option>
								  <?php if($Departamento!=""){while($row_Municipio=sqlsrv_fetch_array($SQL_Municipio)){?>
										<option value="<?php echo $row_Municipio['Municipio'];?>" <?php if((isset($_GET['Municipio']))&&(strcmp($row_Municipio['Municipio'],$_GET['Municipio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Municipio['Municipio'];?></option>
								  <?php }}?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Tipo facturación</label>
							<div class="col-lg-3">
								<select name="TipoFacturacion" class="form-control" id="TipoFacturacion">
									<option value="0" <?php if((isset($_GET['TipoFacturacion']))&&(strcmp(0,$_GET['TipoFacturacion'])==0)){ echo "selected=\"selected\"";}?>>Factura de prorrateo</option>
									<option value="1" <?php if((isset($_GET['TipoFacturacion']))&&(strcmp(1,$_GET['TipoFacturacion'])==0)){ echo "selected=\"selected\"";}?>>Factura recurrente</option>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Fecha factura</label>
							<div class="col-lg-3 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaFactura" type="text" class="form-control" id="FechaFactura" value="<?php if(isset($_GET['FechaFactura'])&&($_GET['FechaFactura']!="")){ echo $_GET['FechaFactura'];}?>" readonly="readonly" placeholder="YYYY-MM-DD">
							</div>
							<label class="col-lg-1 control-label">Mes factura</label>
							<div class="col-lg-3">
								<select name="MesFactura" class="form-control" id="MesFactura" required>
									<option value="">Seleccione...</option>
									<option value="1" <?php if((isset($_GET['MesFactura']))&&(strcmp(1,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Enero</option>
									<option value="2" <?php if((isset($_GET['MesFactura']))&&(strcmp(2,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Febrero</option>
									<option value="3" <?php if((isset($_GET['MesFactura']))&&(strcmp(3,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Marzo</option>
									<option value="4" <?php if((isset($_GET['MesFactura']))&&(strcmp(4,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Abril</option>
									<option value="5" <?php if((isset($_GET['MesFactura']))&&(strcmp(5,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Mayo</option>
									<option value="6" <?php if((isset($_GET['MesFactura']))&&(strcmp(6,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Junio</option>
									<option value="7" <?php if((isset($_GET['MesFactura']))&&(strcmp(7,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Julio</option>
									<option value="8" <?php if((isset($_GET['MesFactura']))&&(strcmp(8,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Agosto</option>
									<option value="9" <?php if((isset($_GET['MesFactura']))&&(strcmp(9,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Septiembre</option>
									<option value="10" <?php if((isset($_GET['MesFactura']))&&(strcmp(10,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Octubre</option>
									<option value="11" <?php if((isset($_GET['MesFactura']))&&(strcmp(11,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Noviembre</option>
									<option value="12" <?php if((isset($_GET['MesFactura']))&&(strcmp(12,$_GET['MesFactura'])==0)){ echo "selected=\"selected\"";}?>>Diciembre</option>
								</select>
							</div>
							<div class="col-lg-4">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
					  	<?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-10 col-md-10">
								<a href="exportar_excel.php?exp=8&Cons=1">
									<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
								</a>
							</div>
						</div>
					  <?php }?>
				 </form>
			</div>
			</div>
		  </div>
         <br>
		<?php if($sw==1){?>	 
		 <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="row">
						<div class="col-lg-3">
							<?php if(PermitirFuncion(1404)){?><button class="btn btn-primary btn-lg" type="button" disabled id="CrearFacturas" onClick="EjecutarProceso('1');"><i class="fa fa-play-circle"></i> Crear facturas</button><?php }?>
							<input type="hidden" id="IdEvento" value="<?php if(isset($row['IdEvento'])){echo $row['IdEvento'];}?>" />
						</div>	
						<div class="col-lg-5">&nbsp;</div>
						<div class="col-lg-2">
							<div class="form-group border">
								<div class="p-xs">
									<label class="text-muted">Última validación</label>
									<div class="font-bold"><?php echo date('Y-m-d H:i');?></div>
								</div>
							</div>
						</div>
						<div class="col-lg-2">
							<div class="form-group border">
								<div class="p-xs">
									<label class="text-muted">Última ejecución</label>
									<div id="UltEjecucion" class="font-bold">&nbsp;</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tabs-container">  
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Contenido</a></li>
							<li><span class="TimeAct"><div id="TimeAct">&nbsp;</div></span></li>
						</ul>
						<div class="tab-content">
							<div id="tab-1" class="tab-pane active">
								<iframe id="DGDetalle" name="DGDetalle" style="border: 0;" width="100%" height="700" src="detalle_facturacion_proyectos.php"></iframe>
							</div>
						</div>					
					</div>
				</div>
			</div>			
          </div>	
		 <?php }?>
        </div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
        $(document).ready(function(){
			$("#formBuscar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
			 $(".alkin").on('click', function(){
					$('.ibox-content').toggleClass('sk-loading');
				});
			 $('#FechaInicial').datepicker({
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
			$('#FechaFactura').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				todayHighlight: true,
				format: 'yyyy-mm-dd'
            });
			
			 $(".select2").select2();	
			
			var options = {
				url: function(phrase) {
					return "ajx_buscar_datos_json.php?type=7&id="+phrase;
				},

				getValue: "NombreBuscarCliente",
				requestDelay: 400,
				list: {
					match: {
						enabled: true
					},
					onClickEvent: function() {
						var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
						$("#Cliente").val(value).trigger("change");
					}
				}
			};

			$("#NombreCliente").easyAutocomplete(options);
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
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
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>