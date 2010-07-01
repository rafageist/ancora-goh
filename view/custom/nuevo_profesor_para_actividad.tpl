<p align = "center"><b>Profesores disponibles</b></p>
<div style = "width:250px;height:150px; text-align: center; overflow: auto; background: #DDDDDD; border: 1px solid #AAAAAA;padding: 2px;margin: auto;">
	<table width="100%">
		<?php
			$i = 0;
			if (is_array($profesores_disponibles))
				foreach ($profesores_disponibles as $pd){
						$i++;
						echo '<tr><td width="10"><input type="hidden" id="edtProfesorDisponible_'.$i.'" value="'.$pd->id.'"><input type="checkbox" id="chkProfeParaActividad_'.$i.'"></td><td align="left"><img src="images/profesor_lite.png">'.$pd->nombre.'</td><tr>';
					}
		
		?>
		
	</table>
</div>
<br>
<div class = "buttons">
	<input class = "button" type = "button" onclick = "ancoragoh.client.forms.pxact.event.agregar(<?php echo count($profesores_disponibles); ?>,'<?php echo $idplantilla; ?>');" value = "Aceptar">
	<input class = "button" type = "button" onclick = "ancoragoh.client.forms.pxact.event.cancelar();" value = "Cancelar">
</div>