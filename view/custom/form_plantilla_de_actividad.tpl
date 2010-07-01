<script>
		function refrescaDesgloses(){
			application.browsers.showDesgloseDetalle('<?php echo $asignatura; ?>','<?php echo $planificacion; ?>','desglosesDetalles');	
		}
		
		
</script>
<?php if ($for_new==false) {?>
<div id="frameEliminarPlantilla" class="frame-delete">
<table>
	<tr>
		<td><img src="images/plantilla_delete.png"></td>
		<td>Est&aacute; seguro que desea eliminar?<br>
		<input type="button" onclick="ancoragoh.client.forms.plantilla.event.eliminar();" value="Eliminar"> <input
			type="button" onclick="$('#frameEliminarPlantilla').fadeOut('slow');"
			value="Cancelar"></td>
	</tr>
</table>
</div>
<?php }?>
<h2> <?php echo $title; ?> </h2>
<?php if ($for_new ==false){ ?>
<input type="hidden" value="<?php echo $plantilla->id; ?>" id = "edtId"> 
<?php }?>
<input type="hidden" value="<?php echo $orden; ?>" id = "edtOrden"> 
<input type="hidden" value="<?php echo $desglose; ?>" id = "edtDesglose"> 

<fieldset>
	<?php
		$options = $clas;
		$name = "field_PlantillaDeActividad_clasificacion";
		$id = "field_PlantillaDeActividad_clasificacion";
		$default = $plantilla->clasificacion;
		
		include "../view/combo/combo_ClasificacionDeActividad.tpl";
		
		//$cs->showCboField($clasifs,"descripcion","id",$plantilla->clasificacion,"field_PlantillaDeActividad_clasificacion");
	?>
<br>

Exige profesor <input type="checkbox" id = "chkExigeProfe" <?php if ($for_new == false)  echo $plantilla->exige_profesor==true?'checked':''; ?>> &nbsp; 
Exige lugar <input type="checkbox" id = "chkExigeLugar" <?php if ($for_new == false)  echo $plantilla->exige_profesor==true?'checked':''; ?>> <br>

</fieldset>
<div class = "buttons">
<input class = "buttons" type = "button" value= "Aceptar" onclick = '<?php echo $for_new==true?'ancoragoh.client.forms.plantilla.event.agregar();':'ancoragoh.client.forms.plantilla.event.modificar();'; ?>'>
  <?php if ($for_new==false) {?>
 			<button onclick="$('#frameEliminarPlantilla').fadeIn('slow');">Eliminar</button>
   		 <?php }?>
<input class = "buttons" type="button" value= "Cancelar" onclick = 'ancoragoh.client.forms.plantilla.event.cancelar();'>
</div>