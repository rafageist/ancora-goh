<label class="iconlink"
	onclick="ancoragoh.client.forms.lugar.showForNew();"
	title="Nuevo lugar"><img src='images/lugar_add.png'></label>
<table>
	<tr>
		<td></td>
		<td>Nombre</td>
		<td>Capacidad</td>
	</tr>
	<?php
	$i = 0;
	foreach ( $lugares as $lugar ) {
		$i ++;
		echo "<tr><td>$i</td>
			<td>
				<label class = 'iconlink' onclick = 'ancoragoh.client.forms.lugar.showForEdit(\"{$lugar->id}\");'>
					<img src='images/lugar.png'> &nbsp; {$lugar->nombre}
				</label>
			</td>
			<td>
				" . (($lugar->capacidad == 0) ? '(irrelevante)' : $lugar->capacidad) . "
			</td>
			<td>
				<label class = 'iconlink opaco' onclick = 'ancoragoh.client.forms.costotraslado.show(\"" . $lugar->id . "\");'>
					<img src='images/costotraslado_lite.png'>
				</label> 
			</td>
			</tr>";
	}
	?>
</table>