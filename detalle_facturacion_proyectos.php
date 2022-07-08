<?php 
require_once("includes/conexion.php");
PermitirAcceso(1403);
$sw=0;
//$Proyecto="";
//$Almacen="";
$CardCode="";
$type=1;
$Estado=1;//Abierto

$SQL=Seleccionar("tbl_CreacionFacturasProyectos","*","[Usuario]='".strtolower($_SESSION['User'])."'",'[FechaActividadLlamada], [DocNumLlamadaServicio]','',2);
if($SQL){
	$sw=1;
}

if(isset($_GET['id'])&&($_GET['id']!="")){
	if($_GET['type']==1){
		$type=1;
	}else{
		$type=$_GET['type'];
	}
	if($type==1){//Creando Orden de Venta
		
	}
}
?>
<!doctype html>
<html>
<head>
<?php include_once("includes/cabecera.php"); ?>
<style>
	.ibox-content{
		padding: 0px !important;	
	}
	body{
		background-color: #ffffff;
		overflow-x: auto;
	}
	.form-control{
		width: auto;
		height: 28px;
	}	
</style>
<script>
var json=[];
var cant=0;

function BorrarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=32&linenum="+json,		
			success: function(response){
				window.location.href="detalle_facturacion_proyectos.php?<?php echo $_SERVER['QUERY_STRING'];?>";
			}
		});
	}	
}

function ActualizarDatos(name,id,line){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "registro.php?P=36&doctype=10&type=1&name="+name+"&value="+Base64.encode(document.getElementById(name+id).value)+"&line="+line,
		success: function(response){
			if(response!="Error"){
				window.parent.document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
			}
		}
	});
}

function Seleccionar(ID){
	var btnBorrarLineas=document.getElementById('btnBorrarLineas');
	var Check = document.getElementById('chkSel'+ID).checked;
	var sw=-1;
	json.forEach(function(element,index){
//		console.log(element,index);
//		console.log(json[index])deta
		if(json[index]==ID){
			sw=index;
		}
		
	});
	
	if(sw>=0){
		json.splice(sw, 1);
		cant--;
	}else if(Check){
		json.push(ID);
		cant++;
	}
	if(cant>0){
		$("#btnBorrarLineas").removeClass("disabled");
		$("#btnBorrarLineas").removeAttr("disabled");
	}else{
		$("#btnBorrarLineas").addClass("disabled");
		$("#btnBorrarLineas").attr("disabled");
	}
	
	//console.log(json);
}

function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		json=[];
		cant=0;
		$("#btnBorrarLineas").addClass("disabled");
		$("#btnBorrarLineas").attr("disabled");
	}
	$(".chkSel").prop("checked", Check);
	if(Check){
		json=[];
		cant=0;
		$(".chkSel").trigger('change');
	}		
}

</script>
</head>

<body>
<form id="from" name="form">
	<div class="">
	<table width="100%" class="table table-bordered dataTables-example">
		<thead>
			<tr>
				<th>#</th>
				<th class="text-center form-inline w-80"><div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div> <button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs disabled" disabled onClick="BorrarLinea();"><i class="fa fa-trash"></i></button></th>
				<th>Código cliente</th>
				<th>Nombre cliente</th>
				<th>Cédula</th>
				<th>Fecha creación</th>
				<th>Fecha instalación</th>
				<th>Fecha factura</th>
				<th>Municipio</th>
				<th>Departamento</th>					
				<th>Proyecto</th>		
				<th>Contrato</th>
				<th>Id Servicio</th>
				<th>Llamada servicio</th>
				<th>Instalado</th>
				<th>Factura</th>
				<th>Validación</th>
				<th>Ejecución</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		if($sw==1){
			$i=1;
			while($row=sql_fetch_array($SQL,2)){
		?>
		<tr>
			<td class="text-center"><?php echo $i;?></td>
			<td class="text-center">
				<div class="checkbox checkbox-success no-margins">
					<input type="checkbox" class="chkSel" id="chkSel<?php echo $row['ID'];?>" value="" onChange="Seleccionar('<?php echo $row['ID'];?>');" aria-label="Single checkbox One"><label></label>
				</div>
			</td>
			<td><a href="socios_negocios.php?id=<?php echo base64_encode($row['IdCliente']);?>&tl=1" target="_blank"><?php echo $row['IdCliente'];?></a></td>
			<td><?php echo utf8_encode($row['DeCliente']);?></td>
			<td><?php echo $row['Cedula'];?></td>
			<td><?php echo $row['FechaCreacionLlamada'];?></td>
			<td><span class="<?php if($row['FechaActividadLlamada']==$row['FechaFactura']){echo "badge badge-success";}else{echo "badge badge-warning";}?>"><?php echo $row['FechaActividadLlamada'];?></span></td>
			<td><span class="<?php if($row['FechaActividadLlamada']==$row['FechaFactura']){echo "badge badge-success";}else{echo "badge badge-warning";}?>"><?php echo $row['FechaFactura'];?></span></td>
			<td><?php echo utf8_encode($row['DeMunicipio']);?></td>
			<td><?php echo utf8_encode($row['DeDepartamento']);?></td>
			<td><?php echo $row['DeProyecto'];?></td>
			<td><a href="contratos.php?id=<?php echo base64_encode($row['IdContrato']);?>&tl=1" target="_blank"><?php echo $row['IdContrato'];?></a></td>
			<td><a href="articulos.php?id=<?php echo base64_encode($row['IdArticulo']);?>&tl=1" target="_blank"><?php echo $row['IdArticulo'];?></a></td>
			<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row['IdLlamadaServicio']);?>&tl=1" target="_blank"><?php echo $row['DocNumLlamadaServicio'];?></a></td>
			<td><?php echo $row['Instalado'];?></td>		
			<td><?php echo $row['DocNumFactura'];?></td>
			<td><span class="<?php if($row['Validacion']=="OK"){echo "badge badge-primary";}else{echo "badge badge-danger";}?>"><?php echo $row['Validacion'];?></span></td>
			<td class="<?php if($row['Integracion']==0){ echo "bg-warning";}elseif($row['Integracion']==-1){echo "bg-danger";}else{echo "bg-primary";}?>"><?php echo $row['Ejecucion'];?></td>
		</tr>
		<?php 
			$i++;}
		}
		?>
		</tbody>
	</table>
	</div>
</form>
<script>
	 $(document).ready(function(){
		 $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			}); 
		  $(".select2").select2();		 
		
		 
		$('.dataTables-example').DataTable({
			searching: true,
			paging: false,
			fixedHeader: true,
//			fixedColumns: {
//				leftColumns: 2
//			},
//			scrollX: true,
			language: {
				"search":"Filtrar:"
			}
		});
		 
		<?php if((($i-1)>0)&&(PermitirFuncion(1404))){?>
		 	window.parent.document.getElementById('CrearFacturas').disabled=false;
	 	<?php }?>
	});
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
?>