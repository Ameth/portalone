<?php 
require_once("includes/conexion.php");
PermitirAcceso(1501);
$sw=0;
//$Proyecto="";
//$Almacen="";
$CardCode="";
$type=1;
$Estado=1;//Abierto
$SQL=Seleccionar("uvw_tbl_FacturacionElectronica_Series","*");	
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
	.table > tbody > tr > td{
		padding: 1px !important;
		vertical-align: middle;
	}
	.checkbox, .radio {
		margin-top: 0px !important;
		margin-bottom: 0px !important;
	}
	.select2-container{ 
		width: 100% !important; 
	}
</style>
<script>
	$(document).ready(function() {
		$("#TipoDocumentoNew").change(function(){
			var TipoDocumento=document.getElementById("TipoDocumentoNew");
			if(TipoDocumento.value!=""){
				$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=25&id="+TipoDocumento.value,
					success: function(response){
						$('#SeriesNew').html(response).fadeIn();
					}
				});
			}
			
		});
		
		$("#SeriesNew").change(function(){
			var TipoDocumento=document.getElementById("TipoDocumentoNew");
			var Series=document.getElementById("SeriesNew");
			if(TipoDocumento.value!=""&&Series.value!=""){
				$.ajax({
					type: "GET",
					url: "includes/procedimientos.php?type=23&tipodoc="+TipoDocumento.value+"&series="+Series.value,
					success: function(response){
						window.location.href="detalle_parametros_fe.php?<?php echo $_SERVER['QUERY_STRING'];?>";
					}
				});
			}			
		});
	});
</script>
<script>
function Totalizar(num){
	window.parent.document.getElementById('TotalItems').value=num;
}
	
function BorrarLinea(LineNum){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=24&linenum="+LineNum,		
			success: function(response){
				window.location.href="detalle_parametros_fe.php?<?php echo $_SERVER['QUERY_STRING'];?>";
			}
		});
	}	
}
	
function ActualizarDatos(name,id,line){//Actualizar datos asincronicamente
	var input = $("input[id="+name+id+"]:checkbox");
	var value = "";
	
	//Verificar si es un check, sino envio el valor del input text
	if(input.length>=1){
		var Check = document.getElementById(name+id).checked;
		if(Check==true){
			value="1";
		}else{
			value="0";
		}
	}else{
		value=document.getElementById(name+id).value;
	}
	
	$.ajax({
		type: "GET",
		url: "registro.php?P=36&doctype=12&name="+name+"&value="+Base64.encode(value)+"&line="+line,
		success: function(response){
			if(response!="Error"){
				window.parent.document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
			}
		}
	});
	//alert(input.length);
}
</script>
</head>

<body>
<form id="from" name="form">
	<div class="">
	<table width="100%" class="table table-bordered">
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th>Tipo documento</th>
				<th>Serie</th>
				<th>Prefijo</th>
				<th>Nombre del documento</th>
				<th>Autorización</th>
				<th>Clave técnica</th>	
				<th>Fecha inicio</th>
				<th>Fecha fin</th>		
				<th>Primer número</th>
				<th>Último número</th>
				<th>Número de digitos</th>
				<th>QR Y</th>
				<th>QR X</th>
				<th>CUFE Y</th>
				<th>CUFE X</th>
				<th>Serie contingencia</th>
				<th>Motivo contingencia</th>				
				<th>Valor UUID</th>
				<th>Validar prefijo</th>
				<th>Versión UBL</th>
				<th>Fecha. Actualización</th>
				<th>Usuario actualización</th>
			</tr>
		</thead>
		<tbody>
		<?php 
			$i=1;
			while($row=sql_fetch_array($SQL)){
				
				//Objetos
				//$SQL_Objetos=Seleccionar("uvw_tbl_ObjetosSAP","*","IdTipoDocumento IN ('13','14')","DeTipoDocumento",'',2);
		?>
		<tr>
			<td><button type="button" title="Borrar linea" class="btn btn-danger btn-xs" onClick="BorrarLinea(<?php echo $row['ID'];?>);"><i class="fa fa-trash"></i></button></td>
			<td><input size="25" type="text" id="TipoDocumento<?php echo $i;?>" name="TipoDocumento[]" class="form-control" value="<?php echo $row['DeTipoDocumento'];?>" readonly></td>
			<td><input size="15" type="text" id="Series<?php echo $i;?>" name="Series[]" class="form-control" value="<?php echo $row['DeSeries'];?>" readonly></td>
			<td><input size="15" type="text" id="Prefijo<?php echo $i;?>" name="Prefijo[]" class="form-control" value="<?php echo $row['Prefijo'];?>" maxlength="50" onChange="ActualizarDatos('Prefijo',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>
			<td><input size="30" type="text" id="NombreDocumento<?php echo $i;?>" name="NombreDocumento[]" class="form-control" value="<?php echo $row['NombreDocumento'];?>" maxlength="100" onChange="ActualizarDatos('NombreDocumento',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>
			<td><input size="40" type="text" id="Autorizacion<?php echo $i;?>" name="Autorizacion[]" class="form-control" value="<?php echo $row['Autorizacion'];?>" maxlength="100" onChange="ActualizarDatos('Autorizacion',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>
			<td><input size="50" type="text" id="ClaveTecnica<?php echo $i;?>" name="ClaveTecnica[]" class="form-control" value="<?php echo $row['ClaveTecnica'];?>" maxlength="100" onChange="ActualizarDatos('ClaveTecnica',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>			
			<td><input size="15" type="text" id="FechaInicio<?php echo $i;?>" name="FechaInicio[]" class="form-control" value="<?php if(is_object($row['FechaInicio'])&&($row['FechaInicio']!="")){echo $row['FechaInicio']->format('Y-m-d');}else{echo $row['FechaInicio'];}?>" maxlength="100" onChange="ActualizarDatos('FechaInicio',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99"></td>			
			<td><input size="15" type="text" id="FechaFin<?php echo $i;?>" name="FechaFin[]" class="form-control" value="<?php if(is_object($row['FechaFin'])&&($row['FechaFin']!="")){echo $row['FechaFin']->format('Y-m-d');}else{echo $row['FechaFin'];}?>" maxlength="100" onChange="ActualizarDatos('FechaFin',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99"></td>			
			<td><input size="10" type="text" id="PrimerNumero<?php echo $i;?>" name="PrimerNumero[]" class="form-control" value="<?php echo $row['PrimerNumero'];?>" maxlength="100" onChange="ActualizarDatos('PrimerNumero',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>
			<td><input size="10" type="text" id="UltimoNumero<?php echo $i;?>" name="UltimoNumero[]" class="form-control" value="<?php echo $row['UltimoNumero'];?>" maxlength="100" onChange="ActualizarDatos('UltimoNumero',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>
			<td><input size="10" type="text" id="NumeroDigitos<?php echo $i;?>" name="NumeroDigitos[]" class="form-control" value="<?php echo $row['NumeroDigitos'];?>" maxlength="100" onChange="ActualizarDatos('NumeroDigitos',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>			
			<td><input size="10" type="text" id="QRY<?php echo $i;?>" name="QRY[]" class="form-control" value="<?php echo number_format($row['QRY'],2);?>" maxlength="100" onChange="ActualizarDatos('QRY',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>
			<td><input size="10" type="text" id="QRX<?php echo $i;?>" name="QRX[]" class="form-control" value="<?php echo number_format($row['QRX'],2);?>" maxlength="100" onChange="ActualizarDatos('QRX',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>
			<td><input size="10" type="text" id="CUFEY<?php echo $i;?>" name="CUFEY[]" class="form-control" value="<?php echo number_format($row['CUFEY'],2);?>" maxlength="100" onChange="ActualizarDatos('CUFEY',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>
			<td><input size="10" type="text" id="CUFEX<?php echo $i;?>" name="CUFEX[]" class="form-control" value="<?php echo number_format($row['CUFEX'],2);?>" maxlength="100" onChange="ActualizarDatos('CUFEX',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>			
			<td>
				<div class="checkbox checkbox-primary text-center">
					<input name="SerieContingencia[]" type="checkbox" class="actions" id="SerieContingencia<?php echo $i;?>" onChange="ActualizarDatos('SerieContingencia',<?php echo $i;?>,<?php echo $row['ID'];?>);" value="1" <?php if($row['SerieContingencia']==1){echo "checked='checked'";}?>>
					<label for="SerieContingencia<?php echo $i;?>"></label>
				</div>				
			</td>			
			<td><input size="15" type="text" id="MotivoContingencia<?php echo $i;?>" name="MotivoContingencia[]" class="form-control" value="<?php echo $row['MotivoContingencia'];?>" maxlength="100" onChange="ActualizarDatos('MotivoContingencia',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>			
			<td><input size="50" type="text" id="ValorUUID<?php echo $i;?>" name="ValorUUID[]" class="form-control" value="<?php echo $row['ValorUUID'];?>" maxlength="100" onChange="ActualizarDatos('ValorUUID',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>
			<td>
				<div class="checkbox checkbox-primary text-center">
					<input name="ValidarPrefijo[]" type="checkbox" class="actions" id="ValidarPrefijo<?php echo $i;?>" onChange="ActualizarDatos('ValidarPrefijo',<?php echo $i;?>,<?php echo $row['ID'];?>);" value="1" <?php if($row['ValidarPrefijo']==1){echo "checked='checked'";}?>>
					<label for="ValidarPrefijo<?php echo $i;?>"></label>
				</div>
			</td>
			<td><input size="10" type="text" id="VersionUBL<?php echo $i;?>" name="VersionUBL[]" class="form-control" value="<?php echo $row['VersionUBL'];?>" maxlength="100" onChange="ActualizarDatos('VersionUBL',<?php echo $i;?>,<?php echo $row['ID'];?>);"></td>
			<td><input size="15" type="text" id="FechaActualizacion<?php echo $i;?>" name="FechaActualizacion[]" class="form-control" value="<?php if(is_object($row['FechaActualizacion'])){echo $row['FechaActualizacion']->format('Y-m-d');}else{echo $row['FechaActualizacion'];}?>" readonly></td>
			<td><input size="20" type="text" id="UsuarioActualizacion<?php echo $i;?>" name="UsuarioActualizacion[]" class="form-control" value="<?php echo $row['NombreUsuarioActualizacion'];?>" readonly></td>
		</tr>	
		<?php 
			$i++;}
			echo "<script>
			Totalizar(".($i-1).");
			</script>";
			
			//Objetos
			$SQL_Objetos=Seleccionar("uvw_tbl_ObjetosSAP","*","[IdTipoDocumento] IN ('13','14')","[DeTipoDocumento]");
		?>
		<tr>
			<td>&nbsp;</td>
			<td>
				<select id="TipoDocumentoNew" name="TipoDocumentoNew" class="form-control select2">
				  <option value="">Seleccione...</option>
				  <?php while($row_Objetos=sql_fetch_array($SQL_Objetos)){?>
						<option value="<?php echo $row_Objetos['IdTipoDocumento'];?>"><?php echo $row_Objetos['DeTipoDocumento'];?></option>
				  <?php }?>
				</select>
			</td>
			<td>
				<select id="SeriesNew" name="SeriesNew" class="form-control select2">
				  <option value="">Seleccione...</option>
				</select>
			</td>
			<td><input size="15" type="text" id="PrefijoNew" name="PrefijoNew" class="form-control" readonly></td>
			<td><input size="30" type="text" id="NombreDocumentoNew" name="NombreDocumentoNew" class="form-control" readonly></td>
			<td><input size="40" type="text" id="AutorizacionNew" name="AutorizacionNew" class="form-control" readonly></td>
			<td><input size="50" type="text" id="ClaveTecnicaNew" name="ClaveTecnicaNew" class="form-control" readonly></td>	
			<td><input size="15" type="text" id="FechaInicioNew" name="FechaInicioNew" class="form-control" readonly></td>
			<td><input size="15" type="text" id="FechaFinNew" name="FechaFinNew" class="form-control" readonly></td>
			<td><input size="10" type="text" id="PrimerNumeroNew" name="PrimerNumeroNew" class="form-control" readonly></td>
			<td><input size="10" type="text" id="UltimoNumeroNew" name="UltimoNumeroNew" class="form-control" readonly></td>
			<td><input size="10" type="text" id="NumeroDigitosNew" name="NumeroDigitosNew" class="form-control" readonly></td>
			<td><input size="10" type="text" id="QRYNew" name="QRYNew" class="form-control" readonly></td>
			<td><input size="10" type="text" id="QRXNew" name="QRXNew" class="form-control" readonly></td>
			<td><input size="10" type="text" id="CUFEYNew" name="CUFEYNew" class="form-control" readonly></td>
			<td><input size="10" type="text" id="CUFEXNew" name="CUFEXNew" class="form-control" readonly></td>
			<td><input size="10" type="text" id="SerieContingenciaNew" name="SerieContingenciaNew" class="form-control" readonly></td>
			<td><input size="15" type="text" id="MotivoContingenciaNew" name="MotivoContingenciaNew" class="form-control" readonly></td>			
			<td><input size="50" type="text" id="ValorUUIDNew" name="ValorUUIDNew" class="form-control" readonly></td>
			<td><input size="10" type="text" id="ValidarPrefijoNew" name="ValidarPrefijoNew" class="form-control" readonly></td>
			<td><input size="10" type="text" id="VersionUBLNew" name="VersionUBLNew" class="form-control" readonly></td>
			<td><input size="15" type="text" id="FechaActualizacionNew" name="FechaActualizacionNew" class="form-control" readonly></td>		
			<td><input size="20" type="text" id="UsuarioActualizacionNew" name="UsuarioActualizacionNew" class="form-control" readonly></td>		
		</tr>
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
		 $('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
			 radioClass: 'iradio_square-green',
		  });
	});
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
?>