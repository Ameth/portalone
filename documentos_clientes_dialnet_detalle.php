<?php 
if(isset($_GET['id'])&&$_GET['id']!=""){
	require_once("includes/conexion.php");
	PermitirAcceso(101);
$sw=0;
	
//Categorias de productos
$SQL_CatProductos=Seleccionar("uvw_tbl_CategoriasProductos","ID_CategoriaProductos, NombreCategoriaProductos","");

$Categoria="NULL";
if(isset($_GET['Categoria'])&&$_GET['Categoria']!=""){
	$Categoria="'".$_GET['Categoria']."'";
	$sw=1;
}

//Datos del cliente
$SQL=Seleccionar("uvw_Sap_tbl_Contratos","ID_Contrato, NombreCliente, LicTradNum, IdAnexos","ID_Contrato='".base64_decode($_GET['cont'])."' and CodigoCliente='".base64_decode($_GET['id'])."'");
$row=sqlsrv_fetch_array($SQL);
	
//Datos de los anexos
$ParamAnx=array(
	$row['ID_Contrato'],
	$Categoria
);
$SQL_Anexos=EjecutarSP('sp_InformeSNProyecto_ConsultarAnexos',$ParamAnx,0,2);


?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $row['NombreCliente'];?> | <?php echo NOMBRE_PORTAL;?></title>
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
                    <h2><?php echo $row['NombreCliente']." - ".$row['LicTradNum'];?></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li class="active">
                            <strong><?php echo $row['NombreCliente'];?></strong>
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
				  <form action="documentos_clientes_dialnet_detalle.php" method="get" id="formBuscar" class="form-horizontal">
					<div class="form-group">
						<div class="col-lg-1">
							<?php
							if(isset($_GET['return'])){
								$return=base64_decode($_GET['pag'])."?".base64_decode($_GET['return']);
							}else{
								$return="index1.php?";
							}?>
							<a href="<?php echo $return;?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
						</div>
						<label class="col-lg-1 control-label">Categoría</label>
						<div class="col-lg-2">
							<select name="Categoria" class="form-control m-b" id="Categoria">
								<option value="">(Todos)</option>
							  <?php while($row_CatProductos=sqlsrv_fetch_array($SQL_CatProductos)){?>
									<option value="<?php echo $row_CatProductos['ID_CategoriaProductos'];?>" <?php if(isset($_GET['Categoria'])&&(strcmp($row_CatProductos['ID_CategoriaProductos'],$_GET['Categoria'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CatProductos['NombreCategoriaProductos'];?></option>
							  <?php }?>
							</select>
						</div>
						<div class="col-lg-1">
							<button type="submit" class="btn btn-outline btn-success"><i class="fa fa-search"></i> Buscar</button>
				    	</div>
					</div>
					  <input type="hidden" name="id" id="id" value="<?php echo $_GET['id'];?>" />
					  <input type="hidden" name="cont" id="cont" value="<?php echo $_GET['cont'];?>" />
					  <input type="hidden" name="return" id="return" value="<?php echo $_GET['return'];?>" />
					  <input type="hidden" name="pag" id="pag" value="<?php echo $_GET['pag'];?>" />
				 </form>
				</div>
				</div>
		</div>
         <br>
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
			<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
                    <tr>
                        <th>Nombre archivo</th>
						<th>Formato</th>
						<th>Categoria</th>
						<th>Acciones</th>
                    </tr>
                    </thead>
                     <tbody>
                   <?php while($row_Anexos=sql_fetch_array($SQL_Anexos,2)){ ?>
						 <tr class="gradeX">							
							<td><?php echo $row_Anexos['FileName'];?></td>
							<td><?php echo $row_Anexos['FileExt'];?></td>
							<td><?php echo utf8_encode($row_Anexos['DeCategoria']);?></td>
							<td><a href="attachdownload.php?file=<?php echo base64_encode($row_Anexos['AbsEntry']);?>&line=<?php echo base64_encode($row_Anexos['Line']);?>" target="_blank" class="btn btn-link btn-xs"><i class="fa fa-download"></i> Descargar</a></td>
						</tr>
					<?php }?>
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
						
            $('.dataTables-example').DataTable({
                pageLength: 25,
                responsive: true,
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