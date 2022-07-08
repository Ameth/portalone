<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
PermitirAcceso(215);

$SQL_TipoDoc=Seleccionar("uvw_tbl_ObjetosSAP","*",'','CategoriaObjeto, DeTipoDocumento');
$SQL_Usuarios=Seleccionar("uvw_tbl_Usuarios","*","Estado='1'",'NombreUsuario');

$sw=0;

if(isset($_GET['TipoDocumento'])&&$_GET['TipoDocumento']!=""){
	$sw=1;
}

if($sw==1){
	$SQL=Seleccionar("uvw_tbl_CamposValoresDefecto_Detalle","*","TipoObjeto='".$_GET['TipoDocumento']."' and ID_Usuario='".$_GET['Usuario']."'",'NombreUsuario');
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Gestionar valores por defecto | <?php echo NOMBRE_PORTAL;?></title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.modal-dialog{
		width: 70% !important;
	}
	.modal-footer{
		border: 0px !important;
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreCliente").change(function(){
			var NomCliente=document.getElementById("NombreCliente");
			var Cliente=document.getElementById("Cliente");
			if(NomCliente.value==""){
				Cliente.value="";
				$("#Cliente").trigger("change");
			}	
		});
		$("#Cliente").change(function(){
			var Cliente=document.getElementById("Cliente");
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value,
				success: function(response){
					$('#Sucursal').html(response).fadeIn().change();
				}
			});
		});
		
	});
</script>
<script>
	function CargarAct(ID, DocNum){
		$('.ibox-content').toggleClass('sk-loading',true);
		$.ajax({
			type: "POST",
			async: false,
			url: "sn_actividades.php?id="+Base64.encode(ID)+"&objtype=191",
			success: function(response){
				$('.ibox-content').toggleClass('sk-loading',false);
				$('#ContenidoModal').html(response);
				$('#TituloModal').html('Actividades relacionadas - OT: '+ DocNum);
				$('#myModal').modal("show");
			}
		});
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
                    <h2>Gestionar valores por defecto</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                      	<li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li class="active">
                            <strong>Gestionar valores por defecto</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
			 <div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="TituloModal"></h4>
						</div>
						<div class="modal-body" id="ContenidoModal">							
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-success m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
						</div>
					</div>
				</div>
			</div>
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="gestionar_campos_defecto.php" method="get" id="formBuscar" class="form-horizontal">
					  <div class="form-group">
						<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
					  </div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Tipo documento</label>
							<div class="col-lg-3">
								<select name="TipoDocumento" class="form-control" id="TipoDocumento" required>
										<option value="">Seleccione...</option>
								  <?php $CatActual="";
									while($row_TipoDoc=sqlsrv_fetch_array($SQL_TipoDoc)){
										if($CatActual!=$row_TipoDoc['CategoriaObjeto']){
											echo "<optgroup label='".$row_TipoDoc['CategoriaObjeto']."'></optgroup>";
											$CatActual=$row_TipoDoc['CategoriaObjeto'];
										}
									?>
										<option value="<?php echo $row_TipoDoc['IdTipoDocumento'];?>" <?php if((isset($_GET['TipoDocumento']))&&(strcmp($row_TipoDoc['IdTipoDocumento'],$_GET['TipoDocumento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoDoc['DeTipoDocumento'];?></option>
								  <?php }?>
								</select>
							</div>		
							<label class="col-lg-1 control-label">Usuario</label>
							<div class="col-lg-3">
								<select name="Usuario" class="form-control select2" id="Usuario" required>
									<option value="">Seleccione...</option>
								  <?php $j=0;
									while($row_Usuarios=sqlsrv_fetch_array($SQL_Usuarios)){?>										
										<option value="<?php echo $row_Usuarios['ID_Usuario'];?>" <?php if((isset($_GET['Usuario']))&&(strcmp($row_Usuarios['ID_Usuario'],$_GET['Usuario'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Usuarios['NombreUsuario'];?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-2">
								<button type="submit" class="btn btn-outline btn-success"><i class="fa fa-search"></i> Buscar</button>
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
					<div class="table-responsive">
						<?php while($row=sqlsrv_fetch_array($SQL)){?>
							<div class="row m-b-md">
								<div class="form-group">
									<label class="col-sm-3 control-label"><?php echo $row['LabelCampo'];?></label>
									<div class="col-sm-5">
										<input name="ValorCampo<?php echo $row['ID_Campo'];?>" type="text" required="required" class="form-control" id="ValorCampo<?php echo $row['ID_Campo'];?>" value="<?php echo $row['ValorCampo'];?>">
									</div>
									<div class="col-sm-2">
										<button type="button" id="btn_ActValor" onClick="ActualizarVariable(<?php echo $row_Var['ID_Campo'];?>);" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
									</div>
									<div class="col-sm-2">
										<div id="ID_Campo<?php echo $row_Var['ID_Campo'];?>"></div>
									</div>
								</div>
							</div>
						<?php }?>	
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
			$(".select2").select2();
			
			$('.i-checks').iCheck({
				 checkboxClass: 'icheckbox_square-green',
				 radioClass: 'iradio_square-green',
			  });
	
			
            $('.dataTables-example').DataTable({
                dom: '<"html5buttons"B>lTfgitp',
				order: [[ 1, "desc" ]],
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