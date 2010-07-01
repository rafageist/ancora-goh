<div id="zpriori_detail">
<fieldset><legend>Per&iacute;odo:</legend> <select
	id="zpriori_cboPeriodo"
	onchange="ancoragoh.client.forms.zpriori.loadzpriori();">
<?php
if (is_array ( $planifs )) {
	foreach ( $planifs as $planif ) {
		echo '<optgroup label ="' . $planif->descripcion . '">';
		if (is_array ( $planif->periodos->collection ))
			foreach ( $planif->periodos->collection as $p ) {
				$s = '';
				if ($p->id == $idperiodo)
					$s = 'selected';
				echo '<option value = "' . $p->id . '" ' . $s . '>' . $p->descripcion . '</option>';
			}
		echo '</optgroup>';
	}
}
?>
</select></fieldset>
<fieldset><legend>Zonas</legend>
<table>
	<tr>
		<td>Turno</td><?php
		for($i = 1; $i <= $periodo->cant_dias; $i ++)
			echo '<td>' . $i . '</td>';
		?></tr>
	<?php
	for($j = 1; $j <= $periodo->cant_turnos; $j ++) {
		echo '<tr><td>' . $j . '</td>';
		for($i = 1; $i <= $periodo->cant_dias; $i ++) {
			echo '<td>
			<select id = "zp_' . $i . '_' . $j . '">
			';
			for($k = 1; $k <= $periodo->cant_turnos * $periodo->cant_dias; $k ++) {
				$s = '';
				if ($k == $zpriori [$i] [$j])
					$s = 'selected';
				echo '<option value = "' . $k . '" ' . $s . '>' . $k . '</option>';
			}
			//<input maxlength = 2 id = "zp_'.$i.'_'.$j.'" class= "casilla-restriccion" value = "' . $zpriori [$i] [$j] . '">
			

			echo '</select></td>';
		}
		echo '</tr>';
	}
	?>
</table>
</fieldset>
<input type="hidden" id="data_zpriori_idclasif"
	value="<?php
	echo $idclasif;
	?>"> <input type="hidden" id="data_zpriori_cd"
	value="<?php
	echo $periodo->cant_dias;
	?>"> <input type="hidden" id="data_zpriori_ct"
	value="<?php
	echo $periodo->cant_turnos;
	?>"> <!-- Buttons --> <br>
<div class="buttons"><input class="button" type="button"
	onclick="ancoragoh.client.forms.zpriori.event.aceptar();"
	value="Aceptar"> <input class="button" type="button"
	onclick="ancoragoh.client.forms.zpriori.event.cancelar();"
	value="Cancelar"></div>
</div>
