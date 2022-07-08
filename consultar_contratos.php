<?php require_once("includes/conexion.php");
require_once("includes/conexion_hn.php");
PermitirAcceso(506);
$sw=0;//Para saber si ya se selecciono un cliente y mostrar la información

//Filtros
$Where="";//Filtro
//$WhereClaves="";
if(isset($_GET['BuscarDato'])&&$_GET['BuscarDato']!=""){
	$_GET['BuscarDato']=strtoupper($_GET['BuscarDato']);
	
	/*$Claves=preg_split('/[\s,]+/',$_GET['BuscarDato']);
	if(count($Claves) > 1){
		foreach ($Claves as $Valor){
			$WhereClaves.=" OR UPPER([CodigoCliente]) LIKE '%".$Valor."%' 
				OR UPPER([LicTradNum]) LIKE '%".$Valor."%' 
				OR UPPER([NombreCliente]) LIKE '%".$Valor."%' 
				OR UPPER([AliasCliente]) LIKE '%".$Valor."%' 
				OR UPPER([PersonaContacto]) LIKE '%".$Valor."%'
				OR UPPER([Telefono]) LIKE '%".$Valor."%'
				OR UPPER([Celular]) LIKE '%".$Valor."%' 
				OR UPPER([Email]) LIKE '%".$Valor."%'";
		}
	}
	
	$Where="UPPER([NombreCliente]) LIKE '%".$_GET['BuscarDato']."%'".$WhereClaves;*/
	
	$Where="UPPER([CodigoCliente]) LIKE '%".$_GET['BuscarDato']."%' 
			OR UPPER([NombreCliente]) LIKE '%".$_GET['BuscarDato']."%' 
			OR UPPER([ID_Contrato]) LIKE '%".$_GET['BuscarDato']."%' 
			OR UPPER([CDU_NoContratoImp]) LIKE '%".$_GET['BuscarDato']."%'
			OR UPPER([LicTradNum]) LIKE '%".$_GET['BuscarDato']."%'";
	
	$SQL=Seleccionar("uvw_Sap_tbl_Contratos","*",$Where);	
	$sw=1;
}


?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Consultar contratos | <?php echo NOMBRE_PORTAL;?></title>
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
                    <h2>Consultar contratos</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Socios de negocios</a>
                        </li>
                        <li class="active">
                            <strong>Consultar contratos</strong>
                        </li>
                    </ol>
                </div>
			<?php if(PermitirFuncion(505)){?>
                <div class="col-sm-4">
                    <div class="title-action">
                        <a href="contratos.php" class="alkin btn btn-primary"><i class="fa fa-plus-circle"></i> Crear contrato</a>
                    </div>
                </div>
			<?php }?>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
				  <form action="consultar_contratos.php" method="get" id="formBuscar" class="form-horizontal">
					   <div class="form-group">
							<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Buscar</label>
							<div class="col-lg-4">
								<input name="BuscarDato" type="text" class="form-control" id="BuscarDato" maxlength="100" placeholder="Consulte el número del contrato o el nombre del cliente" value="<?php if(isset($_GET['BuscarDato'])&&($_GET['BuscarDato']!="")){ echo $_GET['BuscarDato'];}?>">
							</div>
							<div class="col-lg-1">
								<button type="submit" class="btn btn-outline btn-info"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
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
                        <th>Código contrato</th>
						<th>Número impreso</th>
						<th>Nombre cliente</th>
						<th>NIT o Cédula</th>
						<th>Contacto</th>
						<th>Dirección</th>
						<th>Estado</th>
						<th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while($row=sql_fetch_array($SQL)){ ?>
						 <tr class="gradeX">
							<td><?php echo $row['ID_Contrato'];?></td>
							<td><?php echo $row['CDU_NoContratoImp'];?></td>
							<td><?php echo $row['NombreCliente'];?></td>
							<td><?php echo $row['LicTradNum'];?></td>
							<td><?php echo $row['NombreContacto'];?></td>
							<td><?php echo $row['CDU_DirSucursal'];?></td>
							<td><?php echo $row['DeEstadoContrato'];?></td>
							<td><a href="contratos.php?id=<?php echo base64_encode($row['ID_Contrato']);?>&tl=1" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
						</tr>
					<?php }?>
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