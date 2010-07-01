
<input type="hidden" id="data_costotraslado_idlugar"
	value="<?php
	echo $idlugar;
	?>">
<input type="hidden" id="data_costotraslado_cantlugs"
	value="<?php
	echo count ( $lugares );
	?>">
<fieldset>
<table>
	<tr>
		<td>Destino</td>
		<td>Costo</td>
	</tr>
<?php
$i = 0;
foreach ( $costos as $costo ) {
	$i ++;
	echo '<tr><td>
	<input type="hidden" id = "ct_destino_' . $i . '" value="' . $costo->destino . '"><img src="images/lugar_lite.png">' . $lugares [$costo->destino]->nombre . '</td>';
	echo '<td><select id ="ct_' . $i . '">';
	for($c = 0; $c <= 999; $c ++) {
		$s = '';
		if ($c == $costo->costo)
			$s = 'selected';
		echo '<option value = "' . $c . '" ' . $s . '>' . ($c == 0 ? '(irrelevante)' : $c) . '</option>';
	
	}
	echo '</select></td></tr>';
}
?>
</table>
</fieldset>
<div class="buttons"><input class="button" class="iconlink"
	type="button"
	onclick="ancoragoh.client.forms.costotraslado.event.aceptar();"
	value="Aceptar"> <input class="button" class="iconlink" type="button"
	onclick="ancoragoh.client.forms.costotraslado.event.cancelar();"
	value="Cancelar"></div>

