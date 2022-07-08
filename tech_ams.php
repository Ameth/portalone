<?php
require_once("includes/conexion.php");

$IdUser=$_GET['idTec'];
$AMSPos=$_GET['AMSPos'];
$AMSOlt=$_GET['AMSOlt'];

//OLT
$SQL_OLT=Seleccionar('uvw_Sap_tbl_OLT','*');

//VLAN
$SQL_VLAN=Seleccionar('tbl_AMS_VLAN','*');

//Version de software
$SQL_SoftVer=Seleccionar('tbl_AMS_SoftwareVersion','*');

?>
<div class="form-group">
	<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-database"></i> Información en tecnología - Access Management System (AMS) NOKIA</h3></label>
</div>
<div class="form-group">
	<label class="col-lg-1 control-label">Serial Number</label>
	<div class="col-lg-2">
		<input type="text" class="form-control" name="UsuarioTecnologia" id="UsuarioTecnologia" value="" autocomplete="off" onBlur="ValidarUsuario();" readonly>
	</div>
	<div class="form-group">
		<label class="col-lg-1 control-label">Posición</label>
		<div class="col-lg-2">
			<input type="text" class="form-control" name="Posicion" id="Posicion" value="<?php echo $AMSPos;?>">
		</div>	
		<label class="col-lg-1 control-label">OLT</label>
		<div class="col-lg-3">
			<select name="OLT" class="form-control m-b select2" id="OLT">
				<option value="">(Ninguno)</option>
			<?php
				while($row_OLT=sqlsrv_fetch_array($SQL_OLT)){?>
					<option value="<?php echo $row_OLT['IdOLT'];?>" <?php if((isset($AMSOlt))&&(strcmp($row_OLT['IdOLT'],$AMSOlt)==0)){ echo "selected=\"selected\"";}?>><?php echo $row_OLT['NombreOLT']." (".$row_OLT['CodigoOLT'].")";?></option>
			<?php }?>
			</select>
		</div>
	</div>	
</div>
<div class="form-group">
	<label class="col-lg-1 control-label">Plantilla</label>
	<div class="col-lg-3">
		<input type="text" class="form-control" name="GrupoTecnologia" id="GrupoTecnologia" value="">
	</div>
</div>
<div class="form-group">
	<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-bullseye"></i> Lista de atributos</h3></label>
</div>
<div class="form-group">	
	<label class="col-lg-2 control-label">Number of data ports</label>
	<div class="col-lg-3">
		<input type="text" class="form-control" name="oper_NumberOfDataPorts" id="oper_NumberOfDataPorts" value="">
	</div>
</div>
<div class="form-group">
	<label class="col-lg-2 control-label">Max number of MAC addresses</label>
	<div class="col-lg-3">
		<input type="text" class="form-control" name="maxNumberOfMacAddresses" id="maxNumberOfMacAddresses" value="">
	</div>
</div>
<div class="form-group">
	<label class="col-lg-2 control-label">Downstream frame tagging</label>
	<div class="col-lg-3">
		<select name="tagMode" class="form-control m-b select2" id="tagMode">
			<option value="untagged">untagged</option>
			<option value="singletagged">singletagged</option>
			<option value="prioritytagged">prioritytagged</option>
		</select>
	</div>
</div>
<div class="form-group">	
	<label class="col-lg-2 control-label">Default VLAN</label>
	<div class="col-lg-3">
		<select name="default" class="form-control m-b select2" id="default">
			<option value="true">True</option>
			<option value="false">False</option>
		</select>
	</div>
</div>
<div class="form-group">	
	<label class="col-lg-2 control-label">Port C-VLAN ID</label>
	<div class="col-lg-3">
		<select name="vlanCustomerId" class="form-control m-b select2" id="vlanCustomerId">
			<?php
				while($row_VLAN=sqlsrv_fetch_array($SQL_VLAN)){?>
					<option value="<?php echo $row_VLAN['Valor'];?>"><?php echo $row_VLAN['Etiqueta'];?></option>
			<?php }?>
		</select>
	</div>
</div>
<div class="form-group">
	<label class="col-lg-2 control-label">Planned software</label>
	<div class="col-lg-3">
		<select name="ontSoftwarePlannedVersion" class="form-control m-b select2" id="ontSoftwarePlannedVersion">
			<?php
				while($row_SoftVer=sqlsrv_fetch_array($SQL_SoftVer)){?>
					<option value="<?php echo $row_SoftVer['Valor'];?>"><?php echo $row_SoftVer['Etiqueta'];?></option>
			<?php }?>
		</select>
	</div>
</div>
<div class="form-group">		
	<label class="col-lg-2 control-label">Software download version</label>
	<div class="col-lg-3">
		<select name="identification_Version_DownloadedSoftware" class="form-control m-b select2" id="identification_Version_DownloadedSoftware">
			<?php
			//Version de software
			$SQL_SoftVer=Seleccionar('tbl_AMS_SoftwareVersion','*');
				while($row_SoftVer=sqlsrv_fetch_array($SQL_SoftVer)){?>
					<option value="<?php echo $row_SoftVer['Valor'];?>"><?php echo $row_SoftVer['Etiqueta'];?></option>
			<?php }?>
		</select>
	</div>
</div>