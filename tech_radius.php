<?php
require_once("includes/conexion.php");
require_once("includes/conexion_mysql.php");

$IdUser=$_GET['idTec'];
$Num_AttUser=0;

//Seleccionar usuario
$SQL_UserTec=Seleccionar('radcheck','*',"username='".$IdUser."' and attribute='Cleartext-Password'",'','',3);
$row_UserTec=mysqli_fetch_array($SQL_UserTec);

if($row_UserTec['username']!=""){
	//Seleccionar grupo de ese usuario
	$SQL_UserGroup=Seleccionar('radusergroup','*',"username='".$IdUser."' and priority > 0",'','',3);
	$row_UserGroup=mysqli_fetch_array($SQL_UserGroup);
	
	//Seleccionar datos del userinfo
	$SQL_UserInfo=Seleccionar('userinfo','*',"username='".$IdUser."'",'','',3);
	$row_UserInfo=mysqli_fetch_array($SQL_UserInfo);

	//Atributos de este usuario
	$SQL_AttUser=EjecutarSP('sp_ConsultaAttUser',$IdUser,48,3);
	$Num_AttUser=mysqli_num_rows($SQL_AttUser);
}

//Listar las options
$SQL_OP=Seleccionar('list_op','*','','','',3);

//Listar todos los grupos
$SQL_ListaGruposTec=Seleccionar('radgroupreply','DISTINCT groupname','','','',3);

//Listar los fabricantes
$SQL_Vendor=Seleccionar('dictionary','DISTINCT Vendor','','Vendor','',3);
?>
<div class="form-group">
	<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-database"></i> Información en tecnología - RADIUS</h3></label>
</div>
<div class="form-group">
	<label class="col-lg-1 control-label"><?php if($row_UserTec['username']!=""){echo "Usuario";}else{echo "Nuevo usuario";}?></label>
	<div class="col-lg-2">
		<input type="text" class="form-control" name="UsuarioTecnologia" id="UsuarioTecnologia" value="<?php if($row_UserTec['username']!=""){echo $row_UserTec['username'];}else{echo $IdUser;}?>" autocomplete="off" onBlur="ValidarUsuario();" readonly>
	</div>
	<label class="col-lg-1 control-label">Contraseña</label>
	<div class="col-lg-3">
		<input type="text" class="form-control" name="Password" id="Password" value="<?php echo $row_UserTec['value'];?>" autocomplete="off" readonly>
	</div>
	<div class="col-lg-1">
		<button type="button" onClick="GenerarClave();" class="btn btn-primary btn-xs" title="Generar contraseña"><i class="fa fa-refresh"></i></button>
	</div>
	<label class="col-lg-1 control-label">Grupo</label>
	<div class="col-lg-3">
		<input type="text" class="form-control" name="GrupoTecnologia" id="GrupoTecnologia" value="<?php if(isset($row_UserGroup['groupname'])){echo $row_UserGroup['groupname'];}?>" readonly placeholder="Se asignará automaticamente...">
		<?php /*?><select name="GrupoTecnologia" class="form-control m-b select2" id="GrupoTecnologia">
			<option value="">Seleccione...</option>
		<?php
			while($row_ListaGruposTec=mysqli_fetch_array($SQL_ListaGruposTec)){?>
				<option value="<?php echo $row_ListaGruposTec['groupname'];?>" <?php if((isset($row_UserGroup['groupname']))&&(strcmp($row_ListaGruposTec['groupname'],$row_UserGroup['groupname'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ListaGruposTec['groupname'];?></option>
		<?php }?>
		</select><?php */?>
	</div>
</div>
<div class="form-group">
	<label class="col-lg-1 control-label">Notas</label>
	<div class="col-lg-4">
		<textarea name="Notas" rows="5" maxlength="200" class="form-control" id="Notas" type="text"><?php if(isset($row_UserInfo['notes'])){echo $row_UserInfo['notes'];}?></textarea>
	</div>
</div>
<div class="form-group">
	<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-bullseye"></i> Atributos actuales</h3></label>
</div>
<?php $Cont=1;
	if($Num_AttUser>0){
		$row_AttUser=mysqli_fetch_array($SQL_AttUser);
		do{ ?>
			<div id="div_<?php echo $Cont;?>" class="form-group">
				<input type="hidden" id="attatribute<?php echo $Cont;?>" name="attatribute[]" value="<?php echo $row_AttUser['attribute'];?>" />
				<label class="col-lg-2 control-label"><?php echo $row_AttUser['attribute'];?></label>
				<div class="col-lg-1">
					<select name="attop[]" class="form-control m-b" id="attop<?php echo $Cont;?>">
					<?php
						while($row_OP=mysqli_fetch_array($SQL_OP)){?>
							<option value="<?php echo $row_OP['op'];?>" <?php if((isset($row_AttUser['op']))&&(strcmp($row_OP['op'],$row_AttUser['op'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_OP['op'];?></option>
					<?php }?>
					</select>
				</div>
				<div class="col-lg-2">
					<input type="text" class="form-control" name="attvalue[]" id="attvalue<?php echo $Cont;?>" value="<?php echo $row_AttUser['value'];?>" autocomplete="off">
				</div>
				<div class="col-lg-2">
					<select name="atttype[]" class="form-control m-b" id="atttype<?php echo $Cont;?>">
						<option value="reply" <?php if($row_AttUser['type']=='reply'){echo "selected=\"selected\"";}?>>Respuesta</option>
						<option value="check" <?php if($row_AttUser['type']=='check'){echo "selected=\"selected\"";}?>>Verificación</option>	
					</select>
				</div>
				<button type="button" id="<?php echo $Cont;?>" class="btn btn-warning btn-xs" onClick="delRow2(this);"><i class="fa fa-minus"></i> Remover</button>
			</div>
<?php 
		$Cont++;
		$SQL_OP=Seleccionar('list_op','*','','','',3);
		} while($row_AttUser=mysqli_fetch_array($SQL_AttUser));
	} ?>

<div class="form-group">
	<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-exchange"></i> Agregar nuevos atributos</h3></label>
</div>

   <div id="div_<?php echo $Cont;?>" class="form-group">
	   <div class="col-lg-2">
			<select name="attvendor[]" class="form-control m-b" id="attvendor<?php echo $Cont;?>" onChange="BuscarAtributo('<?php echo $Cont;?>');">
				<option value="">Seleccione...</option>

			<?php
				while($row_Vendor=mysqli_fetch_array($SQL_Vendor)){?>
					<option value="<?php echo $row_Vendor['Vendor'];?>"><?php echo  $row_Vendor['Vendor'];?></option>
			<?php }?>
			</select>
		</div>
		<div class="col-lg-3">
			<select name="attatribute[]" class="form-control m-b" id="attatribute<?php echo $Cont;?>" onChange="BuscarDatosAtributo('<?php echo $Cont;?>');">
				<option value="">Seleccione...</option>
			</select>
		</div>
		<div class="col-lg-1">
			<select name="attop[]" class="form-control m-b" id="attop<?php echo $Cont;?>">
			<?php
				while($row_OP=mysqli_fetch_array($SQL_OP)){?>
					<option value="<?php echo $row_OP['op'];?>"><?php echo $row_OP['op'];?></option>
			<?php }?>
			</select>
		</div>
		<div class="col-lg-2">
			<input type="text" class="form-control" name="attvalue[]" id="attvalue<?php echo $Cont;?>" value="" autocomplete="off">
		</div>
		<div class="col-lg-2">
			<select name="atttype[]" class="form-control m-b" id="atttype<?php echo $Cont;?>">
				<option value="reply">Respuesta</option>
				<option value="check">Verificación</option>	
			</select>
		</div>
		<button type="button" id="<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addField(this);"><i class="fa fa-plus"></i> Añadir</button>			    
   </div>

<?php 	
mysqli_close($conexion_mysql);
?>