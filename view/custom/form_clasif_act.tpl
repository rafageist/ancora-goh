<!-- Preproccessing data -->
	<?php 
  			if (!isset($id)) $id = '';
            if (!isset($descripcion)) $descripcion = '';           
            if (!isset($duracion)) $duracion = 1;
            if (!isset($gasto)) $gasto = 1;
            if (!isset($prioridad)) $prioridad = 1;
	?>

<!-- Delete confirmation frame -->
	<?php if ($for_new == false) {?>
		<div id = "frame_del_ClasifAct" class = "frame-delete">
		<table>
			<tr>
				<td><img src="images/profesor_delete.png"></td>
				<td>Est&aacute; seguro que desea eliminar?<br>
				<input class = "button" type = "button" onclick = "ancoragoh.client.forms.clasifact.event.eliminar();" value="Eliminar"> 
				<input class = "button" type = "button" onclick = "$('#frame_del_Profesor').fadeOut('slow');" value="Cancelar">
				</td>
			</tr>
		</table>
		</div>
	<?php }?>  

<!-- Data form --> 
	<fieldset>
		<legend>Datos principales</legend>
		<input type = "hidden" id = "field_ClasifAct_Id" value = "<?php echo $id; ?>">
		<input  type = "hidden" id = "field_ClasifAct_Prioridad" value = "<?php echo $prioridad; ?>">

     	Descripci&oacute;n: <br>
     	<input id = "field_ClasifAct_Descripcion" type = "text" value = "<?php echo $descripcion; ?>"><br>
     	Duraci&oacute;n: <br>
     	<input id = "field_ClasifAct_Duracion" type = "text" value = "<?php echo $duracion; ?>"><br>
     	Gasto: <br>
     	<input id = "field_ClasifAct_Gasto" type = "text" value = "<?php echo $gasto; ?>"><br>
	</fieldset>

<!-- Buttons -->
	<br>
	<div class = "buttons">
		<input class = "button" type = "button" onclick="<?php echo $for_new == true?'ancoragoh.client.forms.clasifact.event.agregar();':'ancoragoh.client.forms.clasifact.event.modificar();'; ?>" value="Aceptar">
		 <?php if ($for_new == false) {?>
		<input class = "button" type = "button" onclick="$('#frame_del_ClasifAct').fadeIn('slow');" value="Eliminar">
		 <?php }?>
		<input class = "button" type = "button" onclick="ancoragoh.client.forms.clasifact.event.cancelar()" value="Cancelar">
	 </div>    
      
      
