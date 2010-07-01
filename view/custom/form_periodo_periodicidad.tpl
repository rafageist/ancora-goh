<input type = "hidden" id = 'field_Periodo_Planificacion' value = "<?php echo $idplanif; ?>" >
<input type = "hidden" id = 'data_cant_dias_planificacion' value = "<?php echo $cant_dias_planificacion; ?>" >
<input type = "hidden" id = 'field_Periodo_Id' value = "<?php echo $id; ?>" >

<fieldset><legend>Periodicidad</legend>
<table cellspacing="0">
	
<?php

if ($for_new == false) {
	echo '<tr><td></td>';
	for($l = 1; $l <= $cant_dias_planificacion; $l ++)
		echo '<td align="center">' . $l . '<br>
                <input type = "hidden" id = "chk_periodicidad_' . $l . '" value = "' . (isset ( $periodicidades [$l] ) ? 'x' : ' ') . '">
                <input type = "button" id = "btn_periodicidad_' . $l . '" value = "' . (isset ( $periodicidades [$l] ) ? 'x' : ' ') . '" onclick ="ancoragoh.client.forms.periodo.periodicidadClick(' . $l . ');">
                </td>';
	
	$hi = TimeUtils::number_to_hour ( $hora_inicial );
	$hf = TimeUtils::number_to_hour ( $hora_inicial + $duracion_del_turno * $cant_turnos );
	
	echo '</tr>
				<tr height = "480">
				<td valign="top" width="60">
				<div style ="position: relative; top:0px; width: 60px; height: 20px;">00:00 hrs</div>
				<div style ="position: relative; top:' . ($hora_inicial / 3 - 20) . 'px; width: 60px; height: 20px;">' . $hi->asString ( 2 ) . ' hrs</div>
				<div style ="position: relative; top:' . ($hora_inicial / 3 + ($duracion_del_turno / 3 * $cant_turnos) - 40) . 'px; width: 60px;">' . $hf->asString ( 2 ) . ' hrs</div>
				<div style ="position: relative; top:' . (480 - 60) . 'px; width: 60px;">23:59 hrs</div>
				
				</td>';
	
	$marca = false;
	$dmarca = 0;
	for($l = 1; $l <= $cant_dias_planificacion; $l ++) {
		if (isset ( $periodicidades [$l] ) || $marca == true) {
			$back = 'blue';
			$marca = true;
			$dmarca ++;
			if ($dmarca == $cant_dias) {
				$marca = false;
				$dmarca = 0;
			}
		} else {
			$back = 'transparent';
			$marca = false;
		}
		
		echo '<td style = "border: 1px dashed red;" valign = "top" >
				<div style="position: relative; top: ' . ($hora_inicial / 3) . 'px; height:' . (($duracion_del_turno / 3) * $cant_turnos) . 'px; width: 100%; background: ' . $back . ';"></div></td>';
	}
	echo '</tr>';
}



?>
		</table>
</fieldset>
<div class = "buttons">
	<input class = "button" class = "iconlink" type="button" onclick="ancoragoh.client.forms.periodo.guardarPeriodicidades();" value="Aceptar"> 
	<input class = "button" class = "iconlink" type = "button" onclick = "ancoragoh.client.forms.periodo.event.cancelarPeriodicidades();" value = "Cancelar">
</div>