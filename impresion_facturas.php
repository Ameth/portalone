<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
PermitirAcceso(1405);

$sw=0;

$Proyecto="";
$Departamento="";
$Municipio="";
$Cedula="";
$TipoFacturacion="2";

//Proyectos
$ParamProy=array(
	"'".$_SESSION['CodUser']."'"
);
$SQL_Proyectos=EjecutarSP('sp_ConsultarProyectosUsuario',$ParamProy);

//Departamento
$SQL_Departamento=Seleccionar('uvw_Sap_tbl_SN_Municipio','DISTINCT DeDepartamento as Departamento');

//Municipio	
if(isset($_GET['Departamento'])&&$_GET['Departamento']!=""){	
	$SQL_Municipio=Seleccionar('uvw_Sap_tbl_SN_Municipio','DISTINCT DE_Municipio as Municipio',"DeDepartamento='".$_GET['Departamento']."'");
}

//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
	$sw=1;
}else{
	//Restar 7 dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDocSAP").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	$FechaInicial=$nuevafecha;
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
	$sw=1;
}else{
	$FechaFinal=date('Y-m-d');
}

//Filtros

//Proyecto
if(isset($_GET['Proyecto'])&&$_GET['Proyecto']!=""){
	$Proyecto=$_GET['Proyecto'];
	$sw=1;
}

//Departamento
if(isset($_GET['Departamento'])&&$_GET['Departamento']!=""){
	$Departamento=$_GET['Departamento'];
	$sw=1;
}

//Municipio
if(isset($_GET['Municipio'])&&$_GET['Municipio']!=""){
	$Municipio=$_GET['Municipio'];
	$sw=1;
}

//Cedula
if(isset($_GET['Cedula'])&&$_GET['Cedula']!=""){
	$Cedula=$_GET['Cedula'];
	$sw=1;
}

//TipoFacturacion
if(isset($_GET['TipoFacturacion'])&&$_GET['TipoFacturacion']!=""){
	$TipoFacturacion=$_GET['TipoFacturacion'];
	$sw=1;
}


if($sw==1){
	$ParamCons=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Proyecto."'",
		"'".$Cedula."'",
		"'".utf8_decode($Departamento)."'",
		"'".utf8_decode($Municipio)."'",
		"'".$TipoFacturacion."'"
	);
	$SQL=EjecutarSP('sp_ImpresionFactura',$ParamCons,0,2);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Impresión de facturas | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {
		$("#Departamento").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Departamento=document.getElementById('Departamento').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=23&id="+Departamento,
				success: function(response){
					$('#Municipio').html(response).fadeIn();
					$('#Municipio').trigger('change');	
				}
			});
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		
//		$("#btnZIP").click(function(){
//			$.ajax({
//				dataType: "json",
//				method: "POST",
//				url: "sapdownload.php",
//				data: {
//					id: Base64.encode('16'),
//					type: Base64.encode('2')			
//				},
//				success: function(response){
//					console.log(response);
//				}
//			});
//		});
		
	});
</script>
<script>
	var json=[];
	var cant=0;
function SeleccionarOT(DocNum, AbsEntry, LineNum){
	//var add=new Array(DocNum,AbsEntry,LineNum);
	var btnZIP=document.getElementById('btnZIP');
	var Check = document.getElementById('chkSelOT'+DocNum).checked;
	var sw=-1;
	var strJSON;
	var JSONFile=document.getElementById('file');
	
	json.forEach(function(element,index){
		if(json[index].DocNum==DocNum){
			sw=index;
		}
		//console.log(element,index);
	});
	
	if(sw>=0){
		json.splice(sw, 1);
		cant--;
	}else{
		json.push({DocNum,AbsEntry,LineNum});
		cant++;
	}
	
	if(Check){
		PonerQuitarClase("chkSelOT"+DocNum);
    }else{
		PonerQuitarClase("chkSelOT"+DocNum,2);
	}
	
	strJSON=JSON.stringify(json);
	
	if(cant>0){
		JSONFile.value=Base64.encode(strJSON);
		//btnZIP.setAttribute('href',"attachdownload.php?file="+Base64.encode(strJSON)+"&line=&zip=<?php //echo base64_encode('1');?>");
		$("#btnZIP").removeClass("disabled");
	}else{
		$("#btnZIP").addClass("disabled");
	}
	
	//console.log(json);
}

function PonerQuitarClase(idName,evento=1){
	if($("#"+idName).length){
		if(evento==1){
			$("#"+idName).parents('tr').addClass('bg-info');
		}else{
			$("#"+idName).parents('tr').removeClass('bg-info');
		}		
	}
}
	
function SeleccionarFactura(Num, Obj, Serie, Cedula, Fecha){
//	var FactSel=document.getElementById("FactSel");
//	var FactFrm=document.getElementById("FactFrm");	
//	var Fac=FactSel.value.indexOf(Num);
	var btnZIP=document.getElementById('btnZIP');
	var sw=-1;
	var strJSON;
	
	json.forEach(function(element,index){
		if(json[index].Num==Num){
			sw=index;
		}
		//console.log(element,index);
	});
	
	if(sw>=0){
		json.splice(sw, 1);
		cant--;
	}else{
		json.push({Num,Obj,Serie,Cedula,Fecha});
		cant++;
	}
	
	strJSON=JSON.stringify(json);
	
	if(cant>0){
		//JSONFile.value=Base64.encode(strJSON);
		//btnZIP.setAttribute('href',"attachdownload.php?file="+Base64.encode(strJSON)+"&line=&zip=<?php //echo base64_encode('1');?>");
		$("#btnZIP").removeClass("disabled");
		$("#btnZIP").removeAttr("disabled");
	}else{
		$("#btnZIP").addClass("disabled");
		$("#btnZIP").attr("disabled");
	}
	
}
	
function DescargarZIP(){
	DescargarSAPDownload("sapdownload.php", "id="+Base64.encode('16')+"&type="+Base64.encode('2')+"&zip="+Base64.encode('1')+"&file="+JSON.stringify(json), true)
}

function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		json=[];
		cant=0;
		$("#btnZIP").addClass("disabled");
		$("#btnZIP").attr("disabled");
	}
	$(".chkSelOT").prop("checked", Check);
	if(Check){
		json=[];
		cant=0;
		$(".chkSelOT").trigger('change');
	}		
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
                    <h2>Impresión de facturas</h2>
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
                            <strong>Impresión de facturas</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="impresion_facturas.php" method="get" id="formBuscar" class="form-horizontal">
						<div class="form-group">
						<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Departamento</label>
							<div class="col-lg-3">
								<select name="Departamento" class="form-control m-b select2" id="Departamento">
										<option value="">(TODOS)</option>
								  <?php while($row_Departamento=sqlsrv_fetch_array($SQL_Departamento)){?>
										<option value="<?php echo $row_Departamento['Departamento'];?>" <?php if((isset($_GET['Departamento']))&&(strcmp($row_Departamento['Departamento'],$_GET['Departamento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Departamento['Departamento'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Municipio</label>
							<div class="col-lg-3">
								<select name="Municipio" class="form-control m-b select2" id="Municipio">
										<option value="">(TODOS)</option>
								  <?php if($_GET['Departamento']!=""){while($row_Municipio=sqlsrv_fetch_array($SQL_Municipio)){?>
										<option value="<?php echo $row_Municipio['Municipio'];?>" <?php if((isset($_GET['Municipio']))&&(strcmp($row_Municipio['Municipio'],$_GET['Municipio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Municipio['Municipio'];?></option>
								  <?php }}?>
								</select>
							</div>					
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Tipo facturación</label>
							<div class="col-lg-3">
								<select name="TipoFacturacion" class="form-control" id="TipoFacturacion">
									<option value="2">(Todos)</option>
									<option value="0" <?php if((isset($_GET['TipoFacturacion']))&&(strcmp(0,$_GET['TipoFacturacion'])==0)){ echo "selected=\"selected\"";}?>>Factura de prorrateo</option>
									<option value="1" <?php if((isset($_GET['TipoFacturacion']))&&(strcmp(1,$_GET['TipoFacturacion'])==0)){ echo "selected=\"selected\"";}?>>Factura recurrente</option>
								</select>
							</div>
							<label class="col-lg-1 control-label">Proyecto</label>
							<div class="col-lg-3">
								<select name="Proyecto" class="form-control m-b select2" id="Proyecto" required>
										<option value="">Seleccione..</option>
								  <?php while($row_Proyectos=sqlsrv_fetch_array($SQL_Proyectos)){?>
										<option value="<?php echo $row_Proyectos['IdProyecto'];?>" <?php if((isset($_GET['Proyecto']))&&(strcmp($row_Proyectos['IdProyecto'],$_GET['Proyecto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Proyectos['DeProyecto'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Cédula</label>
							<div class="col-lg-3">
								<input name="Cedula" type="text" class="form-control" id="Cedula" maxlength="20" value="<?php if(isset($_GET['Cedula'])&&($_GET['Cedula']!="")){ echo $_GET['Cedula'];}?>">
							</div>							
						</div>
						<div class="form-group">							
							<div class="col-lg-12 pull-right">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>							
						</div>
					    <?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-10 col-md-10">
								<a href="exportar_excel.php?exp=11&Cons=<?php echo base64_encode(implode(",", $ParamCons));?>&sp=<?php echo base64_encode('sp_ImpresionFactura');?>">
									<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
								</a>
							</div>
						</div>
					  <?php }?>
				 </form>
			</div>
			</div>
		  </div>
		<?php if($sw==1){?>
        <br>
        <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				<div class="row m-b-md">
					<div class="col-lg-10">
						<input type="hidden" id="FactSel" name="FactSel" value="" />
						<input type="hidden" id="FactFrm" name="FactFrm" value="" />
					</div>
					<div class="col-lg-2">
						<button type="button" class="btn btn-primary disabled pull-right" id="btnZIP" name="btnZIP" onClick="DescargarZIP();"><i class="fa fa-file-zip-o"></i> Exportar en ZIP</button>
					</div>
				</div>
				<div class="table-responsive">
					<table class="table table-bordered table-striped table-hover dataTables-example">
					<thead>
					<tr>
						<th>#</th>
						<th>Código cliente</th>
						<th>Nombre cliente</th>
						<th>Cédula</th>
						<th>Municipio</th>
						<th>Departamento</th>	
						<th>Proyecto</th>  
						<th>Fecha instalación</th>
						<th>Fecha factura</th>
						<th>Serie factura</th>
						<th>Factura</th>
						<th>Llamada servicio</th>						
						<th>Comentarios</th>
						<th>Seleccionar <div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div></th>
					</tr>
					</thead>
					<tbody>
					<?php $i=1;
						 while($row=sql_fetch_array($SQL,2)){?>
							<tr id="tr_<?php echo $i;?>" class="gradeX">
								<td><?php echo $i;?></td>
								<td><?php if(PermitirFuncion(1304)){?><a href="socios_negocios.php?id=<?php echo base64_encode($row['ID_CodigoCliente']);?>&tl=1" target="_blank"><?php echo $row['ID_CodigoCliente'];?></a><?php }else{echo $row['ID_CodigoCliente'];}?></td>
								<td><?php echo utf8_encode($row['NombreCliente']);?></td>
								<td><?php echo $row['LicTradNum'];?></td>
								<td><?php echo utf8_encode($row['Municipio']);?></td>
								<td><?php echo utf8_encode($row['Departamento']);?></td>
								<td><?php echo $row['DeProyecto'];?></td>
								<td><?php echo $row['FechaInicioActividad'];?></td>
								<td><?php echo $row['FechaContabilizacion'];?></td>
								<td><?php echo $row['SeriesName'];?></td>
								<td><a href="sapdownload.php?id=<?php echo base64_encode('16');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row['NoInterno']);?>&ObType=<?php echo base64_encode('13');?>&IdFrm=<?php echo base64_encode($row['Series']);?>&Cedula=<?php echo base64_encode($row['LicTradNum']);?>&Fecha=<?php echo base64_encode($row['FechaContabilizacion']);?>" target="_blank" class="btn btn-link btn-xs"><i class="fa fa-download"></i> <?php echo $row['NoDocumento'];?></a></td>
								<td><?php if(PermitirFuncion(1304)){?><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&tl=1" target="_blank"><?php echo $row['DocNumLlamada'];?></a><?php }else{echo $row['DocNumLlamada'];}?></td>						
								<td><?php echo utf8_encode($row['Comentarios']);?></td>
								<td><div class="checkbox checkbox-success"><input type="checkbox" class="chkSelOT" id="singleCheckbox<?php echo $row['NoDocumento'];?>" value="" onChange="SeleccionarFactura('<?php echo ($row['NoInterno']);?>','<?php echo ('13');?>','<?php echo ($row['Series']);?>','<?php echo ($row['LicTradNum']);?>','<?php echo ($row['FechaContabilizacion']);?>');" aria-label="Single checkbox One"><label></label></div></td>
							</tr>
					<?php $i++;}?>
					</tbody>
					</table>
				</div>
			</div>
			 </div> 
          </div>
		<?php }?>
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
				format: 'yyyy-mm-dd'
            });
			 $('#FechaFinal').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd'
            }); 
			$(".select2").select2();
			$('.chosen-select').chosen({width: "100%"});
			$('.i-checks').iCheck({
				 checkboxClass: 'icheckbox_square-green',
				 radioClass: 'iradio_square-green',
			  });
			
            $('.dataTables-example').DataTable({
                pageLength: 50,
				lengthMenu: [ [10, 25, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, -1], [10, 25, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, "Todos"] ],
                dom: '<"html5buttons"B>lTfgitp',
				ordering:  false,
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