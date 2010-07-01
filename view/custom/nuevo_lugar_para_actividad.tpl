<p align = "center"><b>Lugares disponibles</b></p>
<div style = "width:250px; height:150px; text-align: center; overflow: auto; background: #DDDDDD; border: 1px solid #AAAAAA;padding: 2px;margin: auto;">
	<table width="100%">
		<?php
			$i = 0;
			if (is_array($lugares_disponibles))
				foreach ($lugares_disponibles as $pd){
						$i++;
						echo '<tr><td width="10"><input type="hidden" id="edtLugarDisponible_'.$i.'" value="'.$pd->id.'"><input type="checkbox" id="chkLugarParaActividad_'.$i.'"></td><td align="left"><img src="images/lugar_lite.png">'.$pd->nombre.'</td><tr>';
					}
		
		?>
		
	</table>
</div>
<br>
<div class = "buttons">
<input class = "button" type = "button" onclick="ancoragoh.client.forms.lxact.event.agregar(<?php echo count($lugares_disponibles); ?>);" value = "Aceptar">
<input class = "button" type = "button" onclick="ancoragoh.client.forms.lxact.event.cancelar();" value = "Cancelar">
</div>