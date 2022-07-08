<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
PermitirAcceso(1401);
$sw=0;//Para saber si ya se selecciono un cliente y mostrar la información
$Proyecto="";
$Departamento="";
$Municipio="";
$Vendedor=0;
$Cedula="";

//Proyectos
if(PermitirFuncion(1402)){//Traer solo el proyecto del empleado o todos segun el permiso de informes de proyectos
	$SQL_Proyectos=Seleccionar('uvw_Sap_tbl_Proyectos','*');
}else{
	$SQL_ValorDefault=Seleccionar('uvw_Sap_tbl_SN_VlrDef_Usu','*',"IdEmp='".$_SESSION['CodigoSAP']."'");
	$row_ValorDefault=sql_fetch_array($SQL_ValorDefault);
	
	$SQL_Proyectos=Seleccionar('uvw_Sap_tbl_Proyectos','*',"IdProyecto='".$row_ValorDefault['IdProyecto']."'");
}

//Departamento
$SQL_Departamento=Seleccionar('uvw_Sap_tbl_Clientes','DISTINCT Departamento');

//Vendedores
if(PermitirFuncion(1402)){//Traer solo el empleado de ventas o todos segun el permiso de informes de proyectos
	$SQL_Vendedor=Seleccionar('uvw_Sap_tbl_EmpleadosVentas','*','','DE_EmpVentas');
}else{
	$SQL_Vendedor=Seleccionar('uvw_Sap_tbl_EmpleadosVentas','*',"ID_EmpVentas='".$_SESSION['CodigoEmpVentas']."'",'DE_EmpVentas');
}

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

if(isset($_GET['Vendedor'])&&$_GET['Vendedor']!=""){
	$Vendedor=$_GET['Vendedor'];
	$sw=1;
}

if(isset($_GET['Cedula'])&&$_GET['Cedula']!=""){
	$Cedula=$_GET['Cedula'];
	$sw=1;
}

if($sw==1){
	$ParamCons=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Proyecto."'",
		"'".utf8_decode($Departamento)."'",
		"'".utf8_decode($Municipio)."'",
		$Vendedor,
		"'".$Cedula."'"
	);
	$SQL=EjecutarSP('sp_InformeSNProyecto',$ParamCons,0,2);
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Seguimiento de contratos | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#Departamento").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Departamento=document.getElementById('Departamento').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=23&id="+Departamento,
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
function Recrear(IdSN,Metodo,Line,btn){
	if(IdSN!=""){
		$('.ibox-content').toggleClass('sk-loading',true);
		$.ajax({
			url:"ajx_ejecutar_json.php",
			data:{type:1,id:IdSN,metodo:Metodo},
			dataType:'json',
			async: false,
			success: function(data){
				if(data.Estado==1){
					$(btn).remove();
					if(Metodo=='1'){
						$("#CT"+Line).html(data.Objeto[0].NoObjeto);
					}else if(Metodo=='2'){
						$("#IS"+Line).html(data.Objeto[0].NoObjeto);
					}else if(Metodo=='3'){
						$("#LS"+Line).html(data.Objeto[0].NoObjeto);
					}else if(Metodo=='4'){
						$("#AN"+Line).html("OK");
						$("#AN"+Line).removeClass();
						$("#AN"+Line).addClass("bg-primary");
					}else if(Metodo=='5'){
						data.Objeto.forEach(function(element,index){
							if(data.Objeto[index].TipoObjeto=='190'){
								$("#CT"+Line).html(data.Objeto[index].NoObjeto);
							}else if(data.Objeto[index].TipoObjeto=='4'){
								$("#IS"+Line).html(data.Objeto[index].NoObjeto);
							}else if(data.Objeto[index].TipoObjeto=='191'){
								$("#LS"+Line).html(data.Objeto[index].NoObjeto);
							}else if(data.Objeto[index].TipoObjeto=='1190'){
								$("#AN"+Line).html("OK");
								$("#AN"+Line).removeClass();
								$("#AN"+Line).addClass("bg-primary");
							}
						});						
					}
				}
				swal({
					title: data.Title,
					text: data.Mensaje,
					type: data.Icon,
				});
			}
		});
		$('.ibox-content').toggleClass('sk-loading',false);
	}	
}
function EnviarMail(IdSN,btn){
	if(IdSN!=""){
		$('.ibox-content').toggleClass('sk-loading',true);
		$.ajax({
			url:"ajx_buscar_datos_json.php",
			data:{type:23,id:IdSN,objtype:2,plantilla:5},
			dataType:'json',
			async: false,
			success: function(data){
				if(data.Estado){
					$(btn).remove();
				}
				swal({
					title: data.Title,
					text: data.Mensaje,
					type: data.Icon,
				});
			}
		});
		$('.ibox-content').toggleClass('sk-loading',false);
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
                    <h2>Seguimiento de contratos</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gestión de proyectos</a>
                        </li>
                        <li class="active">
                            <strong>Seguimiento de contratos</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
				  <form action="seguimiento_contratos.php" method="get" id="formBuscar" class="form-horizontal">
					  <div class="form-group">
							<label class="col-lg-1 control-label">Fechas</label>
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
										<?php if(PermitirFuncion(1402)){?><option value="">Seleccione...</option><?php }?>
								  <?php while($row_Proyectos=sqlsrv_fetch_array($SQL_Proyectos)){?>
										<option value="<?php echo $row_Proyectos['IdProyecto'];?>" <?php if((isset($_GET['Proyecto']))&&(strcmp($row_Proyectos['IdProyecto'],$_GET['Proyecto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Proyectos['DeProyecto'];?></option>
								  <?php }?>
								</select>
							</div>
						  	<label class="col-lg-1 control-label">Vendedor</label>
							<div class="col-lg-3">
								<select name="Vendedor" class="form-control select2" id="Vendedor">
										<?php if(PermitirFuncion(1402)){?><option value="0">(TODOS)</option><?php }?>
								  <?php while($row_Vendedor=sqlsrv_fetch_array($SQL_Vendedor)){?>
										<option value="<?php echo $row_Vendedor['ID_EmpVentas'];?>" <?php if((isset($_GET['Vendedor']))&&(strcmp($row_Vendedor['ID_EmpVentas'],$_GET['Vendedor'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Vendedor['DE_EmpVentas'];?></option>
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
							<label class="col-lg-1 control-label">Cédula</label>
							<div class="col-lg-2">
								<input name="Cedula" type="text" class="form-control" id="Cedula" maxlength="20" value="<?php if(isset($_GET['Cedula'])&&($_GET['Cedula']!="")){ echo $_GET['Cedula'];}?>">
							</div>
							<div class="col-lg-1 pull-right">
								<button type="submit" class="btn btn-outline btn-info"><i class="fa fa-search"></i> Buscar</button>
							</div>		
						</div>
					  <?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-10 col-md-10">
								<a href="exportar_excel.php?exp=3&Cons=<?php echo base64_encode(implode(",",$ParamCons));?>">
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
			 <?php //echo $Cons;?>
		<?php if($sw==1){?>
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
			<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
                    <tr>
                        <th>Código cliente</th>
						<th>Nombre cliente</th>
						<th>Cédula</th>
						<th>Fecha creación</th>
						<th>Municipio</th>
						<th>Departamento</th>
                        <th>Proyecto</th>
						<th>Contrato</th>
						<th>ID Servicio</th>
						<th>Llamada de servicio</th>
						<th>Instalado</th>
						<th>Envio de correo</th>
						<th>Anexos</th>						
						<th>Vendedor</th>
						<th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i=0; 
						 while($row=sql_fetch_array($SQL,2)){ ?>
						 <tr class="gradeX">
							<td><a href="socios_negocios.php?id=<?php echo base64_encode($row['CodigoCliente']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('seguimiento_contratos.php');?>&tl=1" target="_blank" title="Id Portal: <?php echo $row['IdSNPortal'];?>"><?php echo $row['CodigoCliente'];?></a></td>
							<td><?php echo utf8_encode($row['NombreCliente']);?></td>
							<td><?php echo $row['LicTradNum'];?></td>
							<td><?php echo $row['FechaCreacion']." ".$row['HoraCreacion'];?></td>
							<td><?php echo utf8_encode($row['Municipio']);?></td>
							<td><?php echo utf8_encode($row['Departamento']);?></td>
							<td><?php echo $row['DeProyecto'];?></td>
							<td id="CT<?php echo $i;?>"><a href="contratos.php?id=<?php echo base64_encode($row['ID_Contrato']);?>&tl=1&metod=<?php echo base64_encode('4');?>" class="alkin" target="_blank"><?php echo $row['ID_Contrato'];?></a></td>
							<td id="IS<?php echo $i;?>"><a href="articulos.php?id=<?php echo base64_encode($row['ID_Servicio']);?>&tl=1" class="alkin" target="_blank"><?php echo $row['ID_Servicio'];?></a></td>
							<td id="LS<?php echo $i;?>"><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('seguimiento_contratos.php');?>" target="_blank" class="alkin"><?php echo $row['LlamadaServicio'];?></a></td>
							<td <?php if($row['Instalado']=="NO"){echo "class='text-danger'";}else{echo "class='text-success'";}?>><?php echo $row['Instalado'];?></td>
							<td <?php if($row['EnvioCorreo']=="NO"){echo "class='text-danger'";}else{echo "class='text-success'";}?>><?php echo $row['EnvioCorreo'];?></td>
							<td id="AN<?php echo $i;?>" <?php if($row['EstadoAnexos']=='OK'){echo "class='bg-primary'";}else{echo "class='bg-danger'";}?>><?php echo $row['EstadoAnexos'];?></td>					
							<td><?php echo utf8_encode($row['DeVendedor']);?></td>
							<td>
								<?php 
									if($row['ID_Contrato']==""&&$row['ID_Servicio']==""){?>
									<button type="button" onClick="Recrear('<?php echo $row['IdSNPortal'];?>','5','<?php echo $i;?>',this);" class="btn btn-success btn-xs btn-circle" title="Crear todos los documentos"><i class="fa fa-play" aria-hidden="true"></i></button>
								<?php }else{
									if($row['ID_Contrato']==""){?>
									<button type="button" onClick="Recrear('<?php echo $row['IdSNPortal'];?>','1','<?php echo $i;?>',this);" class="btn btn-primary btn-xs btn-circle" title="Crear contrato"><i class="fa fa-handshake-o" aria-hidden="true"></i></button>
								<?php }
									if($row['ID_Servicio']==""){?>
									<button type="button" onClick="Recrear('<?php echo $row['IdSNPortal'];?>','2','<?php echo $i;?>',this);" class="btn btn-danger btn-xs btn-circle" title="Crear ID de servicio"><i class="fa fa-laptop"></i></button>
								<?php }
									if($row['ID_LlamadaServicio']==""){?>
									<button type="button" onClick="Recrear('<?php echo $row['IdSNPortal'];?>','3','<?php echo $i;?>',this);" class="btn btn-success btn-xs btn-circle" title="Crear llamada de servicio (ticket)"><i class="fa fa-ticket"></i></button>
								<?php }
									if($row['EstadoAnexos']!='OK'){?>
									<button type="button" onClick="Recrear('<?php echo $row['ID_Contrato'];?>','4','<?php echo $i;?>',this);" class="btn btn-warning btn-xs btn-circle" title="Crear anexos PDF"><i class="fa fa-file-pdf-o"></i></button>
								<?php }
									if($row['EnvioCorreo']=='NO'){?>
									<button type="button" onClick="EnviarMail('<?php echo $row['IdSNPortal'];?>',this);" class="btn btn-info btn-xs btn-circle" title="Enviar correo del contrato"><i class="fa fa-envelope-o"></i></button>
								<?php }
									}?>
							</td>
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
			$(".btn-link").on('click', function(){
				$('.ibox-content').toggleClass('sk-loading');
			});
			$(".select2").select2();
			
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
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
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