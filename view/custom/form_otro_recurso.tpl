<!-- Preproccessing data -->
	<?php 
		if (!isset($id)) $id = "";
		if (!isset($nombre)) $nombre = "";
	?>

<!-- Delete confirmation frame -->
	<?php if ($for_new == false) {?>
		<div id = "frame_del_OtroRecurso" class = "frame-delete">
		<table>
			<tr>
				<td><img src="images/otro_recurso_delete.png"></td>
				<td>Est&aacute; seguro que desea eliminar?<br>
				<input class = "button" type = "button" onclick = "ancoragoh.client.forms.otrorecurso.event.eliminar();" value="Eliminar"> 
				<input class = "button" type = "button" onclick = "$('#frame_del_OtroRecurso').fadeOut('slow');" value="Cancelar">
				</td>
			</tr>
		</table>
		</div>
	<?php }?>  

<!-- Data form --> 
	<fieldset>
		<legend>Datos principales</legend>
		<input type = "hidden" id = "field_OtroRecurso_Id" value = "<?php echo $id; ?>">
		Nombre:<br>
		<input type = "text" id = "field_OtroRecurso_Nombre"  value = "<?php echo $nombre; ?>">
	</fieldset>

<!-- Buttons -->
	<br>
	<div class = "buttons">
		<input class = "button" type = "button" onclick = "<?php echo $for_new == true?'ancoragoh.client.forms.otrorecurso.event.agregar();':'ancoragoh.client.forms.otrorecurso.event.modificar();'; ?>" value="Aceptar">
		 <?php if ($for_new == false) {?>
		<input class = "button" type = "button" onclick = "$('#frame_del_OtroRecurso').fadeIn('slow');" value="Eliminar">
		 <?php }?>
		<input class = "button" type = "button" onclick = "ancoragoh.client.forms.otrorecurso.event.cancelar()" value="Cancelar">
	 </div>   