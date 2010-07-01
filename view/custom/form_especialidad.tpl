<!-- Preproccessing data -->

<?php
	if (!isset($id)) $id = '';
	if (!isset($descripcion)) $descripcion = '';           
?>

<!-- Delete confirmation frame -->

<?php if ($for_new == false) {?>
	<div id = "frame_del_Especialidad" class = "frame-delete">
		<table>
			<tr>
				<td><img src="images/especialidad_delete.png"></td>
				<td>Est&aacute; seguro que desea eliminar?<br>
				<input type="button" onclick="ancoragoh.client.forms.especialidad.event.eliminar();" value="Eliminar"> 
				<input type="button" onclick="$('#frame_del_Especialidad').fadeOut('slow');" value="Cancelar"></td>
			</tr>
		</table>
	</div>
<?php }?>

<!-- Data form -->
<fieldset>
	<legend> Datos de la especialidad </legend>
	<input type = "hidden" id = "edit_Especialidad_Id" value="<?php echo $id; ?>">
	Descripci&oacute;n: <br>
	<input type = "text" id = "<?php echo ($for_new==true)?"new":"edit"; ?>_Especialidad_Descripcion" class = "edt-title" value = "<?php echo $descripcion; ?>"> <br>
</fieldset>

<!-- Buttons -->
<br>
<div class = "buttons">
	<input class = "button" type = "button" onclick="<?php echo $for_new ==true?'ancoragoh.client.forms.especialidad.event.agregar();':'ancoragoh.client.forms.especialidad.event.modificar();'; ?>" value="Aceptar">
	 <?php if ($for_new == false) {?>
	<input class = "button" type = "button" onclick="$('#frame_del_Especialidad').fadeIn('slow');" value="Eliminar">
	 <?php }?>
	<input class = "button" type = "button" onclick="ancoragoh.client.forms.especialidad.event.cancelar()" value="Cancelar">
</div>