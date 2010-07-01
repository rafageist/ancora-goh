<input type="hidden" id='field_Periodo_Planificacion'
	value="<?php
	echo $idplanif;
	?>">
<input type="hidden" id='data_cant_dias_planificacion'
	value="<?php
	echo $cant_dias_planificacion;
	?>">
<input type="hidden" id='field_Periodo_Id' value="<?php
echo $id;
?>">
<input type="hidden" id="field_Periodo_Cant_dias"
	value="<?php
	echo $cant_dias;
	?>">
<br>

<input type="hidden" id="field_Periodo_Cant_turnos"
	value="<?php
	echo $cant_turnos;
	?>">
<br>

<?php
if ($for_new == false) {
	echo "
			<fieldset><legend>Restricciones en tiempo</legend>
			
			<table><tr><td>Hora</td><td>Turno</td>";
	for($j = 1; $j <= $cant_dias; $j ++)
		echo "<td>$j</td>";
	
	echo "</tr>";
	
	for($i = 1; $i <= $cant_turnos; $i ++) {
		$hi = TimeUtils::number_to_hour ( ($i - 1) * $duracion_del_turno );
		$hf = TimeUtils::number_to_hour ( ($i - 1) * $duracion_del_turno + $duracion_del_turno );
		echo "<tr><td>" . ($hi->h < 10 ? "0" : "") . "{$hi->h}:" . ($hi->m < 10 ? "0" : "") . "{$hi->m} - " . ($hf->h < 10 ? "0" : "") . "{$hf->h}:" . ($hf->m < 10 ? "0" : "") . "{$hf->m}</td><td  align=\"right\"> $i</td>";
		for($j = 1; $j <= $cant_dias; $j ++) {
			$v = ' ';
			if (isset ( $rest [$j] [$i] ))
				if ($rest [$j] [$i] == 1)
					$v = 'x';
			echo "<td><input type=\"hidden\" id = \"field_rest_{$j}_{$i}\" value = \"$v\"><input class= \"casilla-restriccion\" type=\"button\" id = \"button_rest_{$j}_{$i}\" onclick = \"ancoragoh.client.forms.periodo.restclick($j,$i);\" value = \"$v\"></td>";
		}
	}
}
echo "</table></fieldset>";
?>

<div class="buttons"><input class="button" class="iconlink"
	type="button"
	onclick="ancoragoh.client.forms.periodo.guardarRestricciones();"
	value="Aceptar"> <input class="button" class="iconlink" type="button"
	onclick="ancoragoh.client.forms.periodo.event.cancelarRestricciones();"
	value="Cancelar"></div>