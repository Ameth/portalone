<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
PermitirAcceso(408);

$sw=0;
$CodCliente="";
$Filtro="";
$sw_suc=0;

$Estado="";
$Proyecto="";
$Municipio="";
$Cedula="";

//Estado actividad
$SQL_EstadoLlamada=Seleccionar('uvw_tbl_EstadoLlamada','*');

//Proyectos
$SQL_Proyectos=Seleccionar('uvw_Sap_tbl_Proyectos','*');

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

//Estado de llamada
if(isset($_GET['EstadoLlamada'])&&$_GET['EstadoLlamada']!=""){
	$Estado=$_GET['EstadoLlamada'];
	$sw=1;
}

//Proyecto
if(isset($_GET['Proyecto'])&&$_GET['Proyecto']!=""){
	$Proyecto=$_GET['Proyecto'];
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


if($sw==1){
	$ParamCons=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Proyecto."'",
		"'".$Cedula."'",
		"'".utf8_decode($Municipio)."'",
		"'".$Estado."'"
	);
	$SQL=EjecutarSP('sp_ImpresionOT',$ParamCons,0,2);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Impresión de OT y Facturas | <?php echo NOMBRE_PORTAL;?></title>
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
	}else{
		$("#btnZIP").addClass("disabled");
	}
	
//	if(Fac<0){
//		FactSel.value=FactSel.value + Num + "[*]";
//		FactFrm.value=FactFrm.value + Frm + "[*]";
//	}else{
//		var tmp=FactSel.value.replace(Num+"[*]","");
//		var tmpfrm=FactFrm.value.replace(Frm+"[*]","");
//		FactSel.value=tmp;
//		FactFrm.value=tmpfrm;
//	}

//	if(FactSel.value==""){
//		$("#btnZIP").addClass("disabled");
//	}else{
//		$("#btnZIP").removeClass("disabled");
//		btnZIP.setAttribute('href',"sapdownload.php?id=<?php //echo base64_encode('15');?>&type=<?php //echo base64_encode('2');?>&zip=<?php //echo base64_encode('1');?>&ObType="+Obj+"&IdFrm="+FactFrm.value+"&DocKey="+FactSel.value);
//	}
}
	
function DescargarZIP(){
	DescargarSAPDownload("sapdownload.php", "id="+Base64.encode('16')+"&type="+Base64.encode('2')+"&zip="+Base64.encode('1')+"&file="+JSON.stringify(json), true)
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
                    <h2>Impresión de OT y Facturas</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Servicios</a>
						</li>
						<li>
                            <a href="#">Asistentes</a>
                        </li>
                        <li class="active">
                            <strong>Impresión de OT y Facturas</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="impresion_orden_servicio.php" method="get" id="formBuscar" class="form-horizontal">
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
							<label class="col-lg-1 control-label">Estado</label>
							<div class="col-lg-3">
								<select name="EstadoLlamada" class="form-control" id="EstadoLlamada">
										<option value="">(Todos)</option>
								  <?php while($row_EstadoLlamada=sqlsrv_fetch_array($SQL_EstadoLlamada)){?>
										<option value="<?php echo $row_EstadoLlamada['Cod_Estado'];?>" <?php if((isset($_GET['EstadoLlamada']))&&(strcmp($row_EstadoLlamada['Cod_Estado'],$_GET['EstadoLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoLlamada['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Proyecto</label>
							<div class="col-lg-3">
								<select name="Proyecto" class="form-control m-b select2" id="Proyecto">
										<option value="">(Todos)</option>
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
					<table class="table table-bordered dataTables-example">
					<thead>
					<tr>
						<th>Ticket</th>
						<th>Tipo llamada</th>
						<th>Cliente</th>
						<th>Cédula</th>
						<th>Municipio</th>
						<th>Proyecto</th>  
						<th>Fecha cierre</th>
						<th>Estado</th>
						<th>Fecha factura</th>
						<th>Factura</th>
						<th>Seleccionar <div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div></th>
					</tr>
					</thead>
					<tbody>
					<?php $i=0;
						 while($row=sql_fetch_array($SQL,2)){?>
							<tr id="tr_<?php echo $i;?>" class="gradeX">
								<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('impresion_orden_servicio.php');?>&tl=1" target="_blank"><?php echo $row['DocNum'];?></a></td>
								<td><?php echo $row['DeTipoProblemaLlamada'];?></td>
								<td><?php echo utf8_encode($row['NombreClienteLlamada']);?></td>
								<td><a href="socios_negocios.php?id=<?php echo base64_encode($row['ID_CodigoCliente']);?>&tl=1" target="_blank"><?php echo $row['LicTradNum'];?></a></td>
								<td><?php echo utf8_encode($row['CiudadLlamada']);?></td>
								<td><?php echo $row['DeProyecto'];?></td>
								<td><?php echo $row['FechaCierreLLamada'];?></td>
								<td <?php if($row['IdEstadoLlamada']=='-3'){echo "class='text-success'";}elseif($row['IdEstadoLlamada']=='-2'){echo "class='text-warning'";}else{echo "class='text-danger'";}?>><?php echo $row['DeEstadoLlamada'];?></td>
								<td><?php echo $row['FechaContabilizacionFact'];?></td>
								<td><a href="sapdownload.php?id=<?php echo base64_encode('16');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row['NoInternoFact']);?>&ObType=<?php echo base64_encode('13');?>&IdFrm=<?php echo base64_encode($row['SeriesFact']);?>&Cedula=<?php echo base64_encode($row['LicTradNum']);?>&Fecha=<?php echo base64_encode($row['FechaContabilizacionFact']);?>" target="_blank" class="btn btn-link btn-xs"><i class="fa fa-download"></i> <?php echo $row['NoDocumentoFact'];?></a></td>

								<td><div class="checkbox checkbox-success"><input type="checkbox" class="chkSelOT" id="singleCheckbox<?php echo $row['NoDocumentoFact'];?>" value="" onChange="SeleccionarFactura('<?php echo ($row['NoInternoFact']);?>','<?php echo ('13');?>','<?php echo ($row['SeriesFact']);?>','<?php echo ($row['LicTradNum']);?>','<?php echo ($row['FechaContabilizacionFact']);?>');" aria-label="Single checkbox One"><label></label></div></td>
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
                pageLength: 10,
				lengthMenu: [ [10, 25, 50, 100, 150, 200, -1], [10, 25, 50, 100, 150, 200, "Todos"] ],
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
<?php sqlsrv_close($conexion);?>