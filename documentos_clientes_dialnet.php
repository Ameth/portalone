<?php 
if(isset($_GET['id'])&&$_GET['id']!=""){
	require_once("includes/conexion.php");
	PermitirAcceso(101);
if(!is_numeric(base64_decode($_GET['id']))){
	$_GET['id']=base64_encode(1);
}
$sw=0;
//Categoria
$Where="ID_Categoria = '".base64_decode($_GET['id'])."'";
$SQL_Cat=Seleccionar("uvw_tbl_Categorias","ID_Categoria, NombreCategoria",$Where);
$row_Cat=sqlsrv_fetch_array($SQL_Cat);

//Seleccionar proyecto de la categoria
$SQL_Proy=Seleccionar("uvw_tbl_Categorias_Proyectos","IdProyecto","ID_Categoria='".base64_decode($_GET['id'])."'");
$row_Proy=sqlsrv_fetch_array($SQL_Proy);

//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
	$sw=1;
}else{
	//Restar 7 dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDoc").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha );
	$FechaInicial=$nuevafecha;
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
}else{
	$FechaFinal=date('Y-m-d');
}

$BuscarDato="NULL";
if(isset($_GET['BuscarDato'])&&$_GET['BuscarDato']!=""){
	$BuscarDato="'".$_GET['BuscarDato']."'";
	$sw=1;
}

if($sw==1){
	$ParamCons=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$row_Proy['IdProyecto']."'",
		"2",
		"'".utf8_decode($row_Cat['NombreCategoria'])."'",
		$BuscarDato
	);
	$SQL=EjecutarSP('sp_InformeSNProyecto_ConsultarBaseDatos',$ParamCons,0,2);	
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $row_Cat['NombreCategoria'];?> | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script>
	var json=[];
	var cant=0;
	
function PonerQuitarClase(idName,evento=1){
	if($("#"+idName).length){
		if(evento==1){
			$("#"+idName).parents('tr').addClass('bg-info');
		}else{
			$("#"+idName).parents('tr').removeClass('bg-info');
		}		
	}
}
	
function SeleccionarZIP(LicTradNum, DocCont, AbsEntry){
	//var add=new Array(DocNum,AbsEntry,LineNum);
	var btnZIP=document.getElementById('btnZIP');
	var Check = document.getElementById('chkSelOT'+DocCont).checked;
	var sw=-1;
	var strJSON;
	
	json.forEach(function(element,index){
		if(json[index].DocCont==DocCont){
			sw=index;
		}
		//console.log(element,index);
	});
	
	if(sw>=0){
		json.splice(sw, 1);
		cant--;
	}else{
		json.push({LicTradNum, DocCont, AbsEntry});
		cant++;
	}
	
	if(Check){
		PonerQuitarClase("chkSelOT"+DocCont);
    }else{
		PonerQuitarClase("chkSelOT"+DocCont,2);
	}
	
	strJSON=JSON.stringify(json);
	
	if(cant>0){
		btnZIP.setAttribute('href',"attachdownload.php?file="+Base64.encode(strJSON)+"&type=3&zip=<?php echo base64_encode('1');?>");
		$("#btnZIP").removeClass("disabled");
	}else{
		$("#btnZIP").addClass("disabled");
	}
	
	//console.log(json);
}
	
function SeleccionarTodos(){
	$(".chkSelOT").prop("checked", $("#chkAll").prop('checked'));
	$(".chkSelOT").trigger('change');	
}
	
</script>
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
                    <h2><?php echo $row_Cat['NombreCategoria'];?></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li class="active">
                            <strong><?php echo $row_Cat['NombreCategoria'];?></strong>
                        </li>
                    </ol>
                </div>
            </div>
            <?php  //echo $Cons;?>
         <div class="wrapper wrapper-content">
         <div class="row">
			  <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="documentos_clientes_dialnet.php" method="get" id="formBuscar" class="form-horizontal">
					<div class="form-group">
						<label class="col-lg-1 control-label">Fechas</label>
						<div class="col-lg-3">
							<div class="input-daterange input-group" id="datepicker">
								<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
								<span class="input-group-addon">hasta</span>
								<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
							</div>
						</div>
						<label class="col-lg-1 control-label">Buscar dato</label>
						<div class="col-lg-3">
							<input name="BuscarDato" type="text" class="form-control" id="BuscarDato" maxlength="100" value="<?php if(isset($_GET['BuscarDato'])&&($_GET['BuscarDato']!="")){ echo $_GET['BuscarDato'];}?>">
						</div>
						<div class="col-lg-1">
							<button type="submit" name="submit" class="btn btn-outline btn-primary"><i class="fa fa-search"></i> Buscar</button>
						</div>
					   <input type="hidden" name="id" id="id" value="<?php echo $_GET['id'];?>">
					</div>
					 <?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-10 col-md-10">
								<a href="exportar_excel.php?exp=6&Cons=<?php echo base64_encode(implode(",",$ParamCons));?>">
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
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				<div class="row m-b-md">
					<div class="col-lg-12">
						<a href="#" class="pull-right btn btn-primary disabled" id="btnZIP" name="btnZIP" target="_blank"><i class="fa fa-file-zip-o"></i> Exportar en ZIP</a>
					</div>
				</div>
			<div class="table-responsive">
                    <table class="table table-bordered dataTables-example" >
                    <thead>
                    <tr>
                        <th>Nombre</th>
						<th>Apellido</th>
						<th>Cédula</th>
						<th>Cuenta (ID)</th>
						<th>Estado</th>
						<th>Fecha instalación</th>
						<th>Fecha retiro</th>
						<th>Departamento</th>
						<th>Municipio</th>						
						<th>Barrio</th>
                        <th>Latitud</th>
						<th>Longitud</th>
						<th>Anexos</th>
						<th>Seleccionar <div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div></th>
                    </tr>
                    </thead>
                     <tbody>
                   <?php if($sw==1){
							while($row=sql_fetch_array($SQL,2)){ ?>
								<tr class="gradeX">							
									<td><?php echo utf8_encode($row['Nombre']);?></td>
									<td><?php echo utf8_encode($row['Apellido']);?></td>
									<td><?php echo $row['Cedula'];?></td>
									<td><?php echo $row['Cuenta'];?></td>
									<td><?php echo $row['Estado'];?></td>
									<td><?php echo $row['FechaInstalacion'];?></td>
									<td><?php echo $row['FechaFinContrato'];?></td>
									<td><?php echo utf8_encode($row['Departamento']);?></td>
									<td><?php echo utf8_encode($row['Municipio']);?></td>									
									<td><?php echo utf8_encode($row['Barrio']);?></td>
									<td><?php echo $row['Latitud'];?></td>
									<td><?php echo $row['Longitud'];?></td>
									<td><?php if($row['IdContrato']!=""){?><a href="documentos_clientes_dialnet_detalle.php?id=<?php echo base64_encode($row['CodigoCliente']);?>&cont=<?php echo base64_encode($row['IdContrato']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('documentos_clientes_dialnet.php');?>" class="alkin"><i class="fa fa-paperclip"></i> Ver anexos</a><?php }?></td>
									<td>
									<div class="checkbox checkbox-success">
									<input type="checkbox" class="chkSelOT" id="chkSelOT<?php echo $row['IdContrato'];?>" value="" onChange="SeleccionarZIP('<?php echo $row['Cedula'];?>','<?php echo $row['IdContrato'];?>','<?php echo $row['IdAnexosCont'];?>');" aria-label="Single checkbox One"><label></label>
									</div>
									</td>
								</tr>
					<?php }}?>
                    </tbody>
                    </table>
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
			
			$('.chosen-select').chosen({width: "100%"});
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
                ordering:  false,
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
<?php sqlsrv_close($conexion);
}?>