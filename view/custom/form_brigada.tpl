<!-- Preproccessing data -->

<?php
	if (!isset($id)) $id = '';
	if (!isset($descripcion)) $descripcion = '';
	if (!isset($matricula)) $matricula = 0;           
?>

<!-- Delete confirmation frame -->

<?php if ($for_new == false) {?>
	<div id = "frame_del_Brigada" class = "frame-delete">
		<table>
			<tr>
				<td><img src="images/brigada_delete.png"></td>
				<td>Est&aacute; seguro que desea eliminar?<br>
				<input type="button" onclick="ancoragoh.client.forms.brigada.event.eliminar();" value="Eliminar"> 
				<input type="button" onclick="$('#frame_del_Brigada').fadeOut('slow');" value="Cancelar"></td>
			</tr>
		</table>
	</div>
<?php }?>


<!-- Data form -->
<fieldset>
	<legend> Datos de la especialidad </legend>
	<input id = "field_Brigada_Id" value = "<?php echo $id; ?>" type = "hidden">
	Descripcion <br>
	<input id = "field_Brigada_Descripcion" type = "text" class ="edt-title" value="<?php echo $descripcion; ?>"><br>
	Matricula <br>
	<input id = "field_Brigada_Matricula" type = "text" name = "field_Brigada_matricula" class ="" value="<?php echo $matricula; ?>"><br>
</fieldset>     

<!-- Buttons -->
<br>
<div class = "buttons">
	<input class = "button" type = "button" onclick = "<?php echo ($for_new == true)?'ancoragoh.client.forms.brigada.event.agregar();':'ancoragoh.client.forms.brigada.event.modificar();'; ?>" value = "Aceptar">

    <?php if ($for_new == false) {?>
 		<input class = "button" type = "button" onclick="$('#frame_del_Brigada').fadeIn('slow');" value = "Eliminar">
    <?php }?>
        
    <input class = "button" type = "button" onclick = "ancoragoh.client.forms.brigada.event.cancelar()" value="Cancelar">
</div>