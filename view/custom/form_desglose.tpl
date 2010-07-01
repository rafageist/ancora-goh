<fieldset>
<?php 

	echo "Planificaci&oacute;n: <b> ".$planificacion->descripcion."</b><br>";
	echo "Asignatura: <b>".$asignatura->descripcion."</b><br>";
	echo "Per&iacute;odo: <b>".$periodo->descripcion."</b><br>";
	
?>
</fieldset>

	<script>
		
		
		function cancelar(){
			$("#dataform").fadeOut("fast");
		}
	</script>

	<input type="hidden" id="edtIdDesglose" value = "<?php echo $desglose->id?>">
	<input type="hidden" id="edtAsigDesglose" value = "<?php echo $desglose->asignatura?>">
	<input type="hidden" id="edtPeriodoDesglose" value = "<?php echo $desglose->periodo?>">
<fieldset>
	M&iacute;nima cantidad de d&iacute;as entre actividades: <input type="text" id="edtMinDias" value="<?php echo $desglose->min_dias; ?>"><br>
	M&aacute;xima cantidad de d&iacute;as entre actividades: <input type="text" id="edtMaxDias" value="<?php echo $desglose->max_dias; ?>"><br>
	Respetar orden de las actividades: <input type="checkbox" id="chkRespetarOrden" <?php echo $desglose->respetar_orden==true?'checked':""; ?>><br>
	Permitir m&aacute;s de una actividad el mismo d&iacute;a: <input type="checkbox" id="chkMismoDia" <?php echo $desglose->mismo_dia==true?"checked":""; ?>><br>
</fieldset>
<div class="buttons">
	<input class = "button" type = "button" onclick = "ancoragoh.client.forms.desglose.event.modificar();" value = "Guardar">
	<input class = "button" type = "button" onclick = "ancoragoh.client.forms.desglose.event.cancelar();" value = "Cancelar"> 
</div>