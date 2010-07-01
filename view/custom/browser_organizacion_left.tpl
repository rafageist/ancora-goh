<table>
<?php
	$i = 1;
	foreach ($niveles as $nivel)
		echo '<tr><td align = "left">
		<div class = "icon-edit">
			<table>
			<tr>
			<td aling = "left">
			<label class = "opaco iconlink" onclick = "
			ancoragoh.client.browsers.organizacion.seleccionaNivel(\''.$nivel->id.'\');
			$(\'.nivel_indicator\').fadeOut(\'slow\');
			$(\'#nivel_indicator_'.$i.'\').fadeIn(\'slow\'); 
				">
				<img src="images/nivel_lite.png">
				'.$nivel->descripcion.'
			<label>
			</td>
			<td>
				<img class = "opaco iconlink link-to-edit" src = "images/edit.png" onclick = "ancoragoh.client.forms.nivel.showForEdit(\''.$nivel->id.'\');">
			</td>
			<td align = "center"> <div class = "hidden nivel_indicator" id = "nivel_indicator_'.($i++).'"><img src = "images/flechita_derecha.png"> </div></td>
			</tr>
			</table>
		</div>
		</td></tr>';
?>
<tr>
<td><img class="iconlink" src="images/nivel_add.png" title="Nuevo nivel" onclick = "ancoragoh.client.forms.nivel.showForNew();"></td>
</tr>
</table>