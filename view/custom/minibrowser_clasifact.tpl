<table>
<tr><td> Nombre </td> <td> Duraci&oacute;n (turnos)</td> <td>Gasto</td> </tr>

<?php
	  $i =0;
	  if (is_array($clasifs)) 
		foreach ($clasifs as $clasif){
				$i++;
				echo "<tr><td> 
				<label style='cursor:pointer' onclick='ancoragoh.client.forms.clasifact.showForEdit(\"".$clasif->id."\");'> 
				<img src='images/clasif_act.png'> &nbsp;".$clasif->descripcion."</td><td>".$clasif->duracion."</td>
				<td>".$clasif->gasto."</td>
				<td>
					".($i>1?"<label class = 'iconlink' onclick = \"ancoragoh.client.forms.clasifact.subePrioridad('{$clasif->id}');\"><img src='images/arrow_up.png'></label>":"")."
					".($i<count($clasifs)?"<label class = 'iconlink' onclick = \"ancoragoh.client.forms.clasifact.bajaPrioridad('{$clasif->id}');\"><img src='images/arrow_down.png'></label>":"")."
				</td><td>
					<label class='iconlink opaco' onclick = 'ancoragoh.client.forms.zpriori.show(\"".$clasif->id."\");' title = 'Editar zonas de prioridad de esta clasificaci&oacute;n' ><img src = 'images/zpriori_lite.png'></label>
				</td></tr>";
			}
?>

</table>
