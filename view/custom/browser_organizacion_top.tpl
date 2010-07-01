<table>
<tr>
<?php
$i =1;
	foreach ($especs as $esp)
		echo '
		<td align = "center">
		<div class = "icon-edit">
			<table>
			<tr>
			<td>
			<label class = "opaco iconlink"  
				onclick = "
				ancoragoh.client.browsers.organizacion.seleccionaEspecialidad(\''.$esp->id.'\'); 
				$(\'.especialidad_indicator\').fadeOut(\'slow\');
				$(\'#especialidad_indicator_'.$i.'\').fadeIn(\'slow\'); 
				">
				
				<img class="iconlink" src="images/especialidad.png"><br>'
				.$esp->descripcion.'
				<label>
			</td>
			<td>
				<img class = "opaco iconlink link-to-edit" src = "images/edit.png" onclick = "ancoragoh.client.forms.especialidad.showForEdit(\''.$esp->id.'\');">
			</td>
			</tr>
			<tr><td align = "center"> <div class = "hidden especialidad_indicator" id = "especialidad_indicator_'.($i++).'"><img src = "images/arrow_down.png"> </div></td> </tr>
			</table>
		</div>
		</td>';			  
?>
<td><img class="iconlink" src="images/especialidad_add.png" title="Nueva especialidad" onclick = "ancoragoh.client.forms.especialidad.showForNew();"></td>
</tr>
</table>