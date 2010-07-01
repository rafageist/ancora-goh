<input type ="hidden" id="field_RecursoParaActividad_max_prioridad" value ="<?php echo $max_prioridad + 1; ?>">
<p align = "center"><b>Recursos disponibles</b></p>
<div style = "width:250px; height:150px; text-align: center; overflow: auto; background: #DDDDDD; border: 1px solid #AAAAAA;padding: 2px;margin: auto;">
	<table width="100%">
		<?php
			$i = 0;
			if (is_array($recursos))
				foreach ($recursos as $rec){
						$i++;
						echo '<tr><td width="10"><input type="hidden" id="edtRecursoDisponible_'.$i.'" value="'.$rec['id'].'"><input type="checkbox" id="chkRecursoParaActividad_'.$i.'"></td><td align="left"><img src="images/otro_recurso_lite.png">'.$rec['nombre'].'</td><tr>';
					}
		
		?>
		
	</table>
</div>
<br>
<div class = "buttons">
<input class = "button" type = "button" onclick="ancoragoh.client.forms.rxact.event.agregar(<?php echo count($recursos); ?>);" value = "Aceptar">
<input class = "button" type = "button" onclick="ancoragoh.client.forms.rxact.event.cancelar();" value = "Cancelar">
</div>