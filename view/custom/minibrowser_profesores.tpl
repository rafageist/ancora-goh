<label class = "iconlink" onclick = "ancoragoh.client.forms.profesor.showForNew();" title = "Nuevo profesor"><img src='images/profesor_add.png'></label>
<table>
	<tr><td></td><td>Nombre</td></tr>
	<?php 
		$i = 0;
		foreach($profesores as $profesor){
			$i++;
			echo "<tr><td>$i</td>
			<td>
				<label class = 'iconlink' onclick = 'ancoragoh.client.forms.profesor.showForEdit(\"{$profesor->id}\");'>
				<img src='images/profesor.png'> &nbsp; {$profesor->nombre}
				</label>
			</td>
			</tr>";
		}
	?>
</table>