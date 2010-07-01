<!-- Preproccessing data -->
	<?php 
		if (!isset($id)) $id = "";
		if (!isset($nombre)) $nombre = "";
	?>

<!-- Delete confirmation frame -->
	<?php if ($for_new == false) {?>
		<div id = "frame_del_Profesor" class = "frame-delete">
		<table>
			<tr>
				<td><img src="images/profesor_delete.png"></td>
				<td>Est&aacute; seguro que desea eliminar?<br>
				<input class = "button" type = "button" onclick = "ancoragoh.client.forms.profesor.event.eliminar();" value="Eliminar"> 
				<input class = "button" type = "button" onclick = "$('#frame_del_Profesor').fadeOut('slow');" value="Cancelar">
				</td>
			</tr>
		</table>
		</div>
	<?php }?>  

<!-- Data form --> 
	<fieldset>
		<legend>Datos principales</legend>
		<input type = "hidden" id = "field_Profesor_Id" value = "<?php echo $id; ?>">
		Nombre:<br>
		<input type = "text" id = "field_Profesor_Nombre"  value = "<?php echo $nombre; ?>">
	</fieldset>

<!-- Buttons -->
	<br>
	<div class = "buttons">
		<input class = "button" type = "button" onclick="<?php echo $for_new == true?'ancoragoh.client.forms.profesor.event.agregar();':'ancoragoh.client.forms.profesor.event.modificar();'; ?>" value="Aceptar">
		 <?php if ($for_new == false) {?>
		<input class = "button" type = "button" onclick="$('#frame_del_Profesor').fadeIn('slow');" value="Eliminar">
		 <?php }?>
		<input class = "button" type = "button" onclick="ancoragoh.client.forms.profesor.event.cancelar()" value="Cancelar">
	 </div>   