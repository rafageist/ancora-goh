<!-- Preproccessing data -->

<?php
	if (!isset($id)) $id = '';
	if (!isset($descripcion)) $descripcion = ''; 
	if (!isset($especialidad)) $especialidad = '';
	if (!isset($nivel)) $nivel = '';
	      
?>

<!-- Delete confirmation frame -->

<?php if ($for_new == false) {?>
	<div id = "frame_del_Asignatura" class = "frame-delete">
		<table>
			<tr>
				<td><img src="images/asignatura_delete.png"></td>
				<td>Est&aacute; seguro que desea eliminar?<br>
				<input class = "button" type = "button" onclick="ancoragoh.client.forms.asignatura.event.eliminar();" value="Eliminar"> 
				<input class = "button" type = "button" onclick="$('#frame_del_Asignatura').fadeOut('slow');" value="Cancelar"></td>
			</tr>
		</table>
	</div>
<?php }?>

<!-- Data form -->
<fieldset>
	<legend> Datos principales </legend>
	<input type = "hidden" id = "field_Asignatura_Especialidad" value = "<?php echo $especialidad; ?>">
	<input type = "hidden" id = "field_Asignatura_Nivel" value = "<?php echo $nivel; ?>">
	<input id = "field_Asignatura_Id" value = "<?php echo $id; ?>" type = "hidden">
	Descripcion <br>
	<input id = "field_Asignatura_Descripcion" type = "text" class ="edt-title" value="<?php echo $descripcion; ?>"><br>
</fieldset>     

<!-- Buttons -->
<br>
<div class = "buttons">
	<input class = "button" type = "button" onclick = "<?php echo ($for_new == true)?'ancoragoh.client.forms.asignatura.event.agregar();':'ancoragoh.client.forms.asignatura.event.modificar();'; ?>" value = "Aceptar">

    <?php if ($for_new == false) {?>
 		<input class = "button" type = "button" onclick="$('#frame_del_Asignatura').fadeIn('slow');" value = "Eliminar">
    <?php }?>
        
    <input class = "button" type = "button" onclick = "ancoragoh.client.forms.asignatura.event.cancelar()" value="Cancelar">
</div>