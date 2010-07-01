<label class = "iconlink" onclick = "ancoragoh.client.forms.otrorecurso.showForNew();" title = "Nuevo recurso gen&eacute;rico"><img src='images/otro_recurso_add.png'></label>
<table>
	<tr><td></td><td>Nombre</td></tr>
	<?php 
		$i = 0;
		foreach($recursos as $recurso){
			$i++;
			echo "<tr><td>$i</td>
			<td>
				<label class = 'iconlink' onclick = 'ancoragoh.client.forms.otrorecurso.showForEdit(\"{$recurso->id}\");'>
				<img src='images/otro_recurso.png'> &nbsp; {$recurso->nombre}
				</label>
			</td>
			</tr>";
		}
	?>
</table>