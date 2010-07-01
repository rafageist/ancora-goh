<!-- Preproccessing data -->

<?php
	if (!isset($id)) $id = '';
	if (!isset($descripcion)) $descripcion = '';           
?>

<!-- Delete confirmation frame -->

<?php if ($for_new == false) {?>
	<div id = "frame_del_Nivel" class = "frame-delete">
		<table>
			<tr>
				<td><img src="images/nivel_delete.png"></td>
				<td>Est&aacute; seguro que desea eliminar?<br>
				<input type="button" onclick="ancoragoh.client.forms.nivel.event.eliminar();" value="Eliminar"> 
				<input type="button" onclick="$('#frame_del_Nivel').fadeOut('slow');" value="Cancelar"></td>
			</tr>
		</table>
	</div>
<?php }?>


<!-- Data form -->
<fieldset>
	<legend> Datos del nivel </legend>
	<input type = "hidden" id = "field_Nivel_Id" value="<?php echo $id; ?>">
	Descripci&oacute;n: <br>
	<input type = "text" id = "field_Nivel_Descripcion" class = "edt-title" value = "<?php echo $descripcion; ?>"> <br>
</fieldset>  
    

<!-- Buttons -->
<br>
<div class = "buttons">
	<input class = "button" type = "button" onclick="<?php echo $for_new ==true?'ancoragoh.client.forms.nivel.event.agregar();':'ancoragoh.client.forms.nivel.event.modificar();'; ?>" value="Aceptar">
	 <?php if ($for_new == false) {?>
	<input class = "button" type = "button" onclick="$('#frame_del_Nivel').fadeIn('slow');" value="Eliminar">
	 <?php }?>
	<input class = "button" type = "button" onclick="ancoragoh.client.forms.nivel.event.cancelar()" value="Cancelar">
</div>       
