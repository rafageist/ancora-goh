<img class="iconlink" src="images/clasif_act_add.png" title="Nueva clasificaci&oacute;n de actividad" onclick="application.forms.nuevaClasifAct();">
<table>
<tr><td> Nombre </td> <td> Duraci&oacute;n (turnos)</td> <td>Gasto</td> </tr>

<?php

	  if (is_array($collection->collection)) 
		foreach ($collection->collection as $clasif){
				echo "<tr><td> <label style='cursor:pointer' onclick='application.forms.editClasifAct(\"".$clasif->id."\");'> <img src='images/clasif_act.png'> &nbsp;".$clasif->descripcion."</td><td>".$clasif->duracion."</td><td>".$clasif->gasto."</td></tr>";
			}
?>

</table>
