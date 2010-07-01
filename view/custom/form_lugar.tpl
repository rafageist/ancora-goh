<!-- Preproccessing data -->
	<?php 
		if (!isset($id)) $id = "";
		if (!isset($nombre)) $nombre = "";
		if (!isset($capacidad)) $capacidad = 0;
	?>

<!-- Delete confirmation frame -->
	<?php if ($for_new == false) {?>
		<div id = "frame_del_Lugar" class = "frame-delete">
		<table>
			<tr>
				<td><img src="images/lugar_delete.png"></td>
				<td>Est&aacute; seguro que desea eliminar?<br>
				<input class = "button" type = "button" onclick = "ancoragoh.client.forms.lugar.event.eliminar();" value="Eliminar"> 
				<input class = "button" type = "button" onclick = "$('#frame_del_Lugar').fadeOut('slow');" value="Cancelar">
				</td>
			</tr>
		</table>
		</div>
	<?php }?>  

<!-- Data form --> 
	<fieldset>
		<legend>Datos principales</legend>
		<input type = "hidden" id = "field_Lugar_Id" value = "<?php echo $id; ?>">
		Nombre:<br>
		<input type = "text" id = "field_Lugar_Nombre"  value = "<?php echo $nombre; ?>"><br>
		Capacidad:<br>
		<select id = "field_Lugar_Capacidad">
			<option value = "0"> (irrelevante) </option>
			<option value = "1"> 1 estudiante </option>
			<?php 
				for($i=1; $i<=200; $i++)
					echo '<option value ="'.$i.'">'.$i.' estudiantes</option>';
			?>
		</select>
	</fieldset>

<!-- Buttons -->
	<br>
	<div class = "buttons">
		<input class = "button" type = "button" onclick="<?php echo $for_new == true?'ancoragoh.client.forms.lugar.event.agregar();':'ancoragoh.client.forms.lugar.event.modificar();'; ?>" value="Aceptar">
		 <?php if ($for_new == false) {?>
		<input class = "button" type = "button" onclick="$('#frame_del_Lugar').fadeIn('slow');" value="Eliminar">
		 <?php }?>
		<input class = "button" type = "button" onclick="ancoragoh.client.forms.lugar.event.cancelar()" value="Cancelar">
	 </div>  