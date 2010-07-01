
<table>

	<?php 
	
		if (is_array($desgs))
		foreach ($desgs as $dsg){
			echo "<tr><td><label onclick = \"ancoragoh.client.forms.desglose.showForEdit('".$dsg['id']."');\" style=\"cursor: pointer;\"><img src='images/desglose_lite.png'>";
			
			$per = $periodosModel->get($dsg['periodo']);
			echo $per->descripcion;
						
			echo "<label></td><td><div class='toolbar'><table><tr>";
			
			// Plantillas
			
			$plans = $plantillasModel->getAllBy("desglose = '{$dsg['id']}' ORDER BY orden");
			
			if (is_array($plans)){
				$i=1;
				foreach ($plans as $plan){
					echo "<td align='center'>";
						
						$cla = $clasifsModel->get($plan['clasificacion']);
						echo "<label style='cursor: pointer' onclick = 'ancoragoh.client.forms.plantilla.showForEdit(\"".$plan['id']."\");'>".$cla->descripcion."</label>";
						echo '<br>';
						if ($i>1) echo '<label class = "opaco" title="Mover a la izquierda" style="cursor: pointer" onclick="ancoragoh.client.browsers.desgloses.subePlantilla(\''.$plan['id'].'\');"><img src="images/flechita_izquierda.png"></label>';
						if ($i<count($plans)) echo '<label class = "opaco" title="Mover a la derecha" style="cursor: pointer" onclick="ancoragoh.client.browsers.desgloses.bajaPlantilla(\''.$plan['id'].'\');"><img src="images/flechita_derecha.png"></label>';
						echo '<br>';
						echo '<label class = "opaco" style="cursor: pointer" onclick = "ancoragoh.client.browsers.desgloses.showProfesoresParaActividad(\''.$plan['id'].'\',\'div_recursos\');"><img title = "Profesores para esta plantilla de actividad" src= "images/profesor_lite.png"></label> ';
						echo '<label class = "opaco" style="cursor: pointer" onclick = "ancoragoh.client.browsers.desgloses.showLugaresParaActividad(\''.$plan['id'].'\',\'div_recursos\');"><img title = "Lugares para esta plantilla de actividad" src= "images/lugar_lite.png"></label> ';
						echo '<label class = "opaco" style="cursor: pointer" onclick = "ancoragoh.client.browsers.desgloses.showOtrosRecursosParaActividad(\''.$plan['id'].'\',\'div_recursos\');"><img title = "Otros recursos para esta plantilla de actividad" src= "images/otro_recurso_lite.png"></label> ';
					echo "</td>";
					$i++;
				}
			}
			echo '</tr></table></div></td>';
			echo '<td> <input class ="button opaco" type="button" value="+" onclick="ancoragoh.client.forms.plantilla.showForNew(\''.$dsg['id'].'\');" title="Agregar plantilla de actividad"> </td>';
			echo "</tr>";
		}
	
	?>

</table>
