
<h3> Gestionando per&iacute;odos de la planificaci&oacute;n: <?php echo $planifdesc; ?> </h3>
<?php 

	/*if (isset($collection->collection)) {
		foreach ($collection->collection as $planif){
			echo '<fieldset><legend>'.$planif->descripcion.'</legend>';*/
				echo "<table><tr>";
				$i = 0;
				if (isset($periodos->collection)){
					foreach ($periodos->collection as $period){
						$i++; 
						if ($i==1) echo "<tr>";
						echo '<td align="center" class = "gallery-subicon">
								<label class="iconlink" onclick = "ancoragoh.client.forms.periodo.showForEdit(\''.$period->id.'\');">
									<img src="images/periodo.png"><br>
									'.$period->descripcion.'
								</label><br>
								<label class="iconlink opaco" onclick = "ancoragoh.client.forms.periodo.showEditPeriodicidad(\''.$period->id.'\');">
									<img src="images/periodicidad_lite.png"> &nbsp;
								</label>
								<label class="iconlink opaco" onclick = "ancoragoh.client.forms.periodo.showEditRestricciones(\''.$period->id.'\');">
									<img src="images/restriccion_lite.png"><br>
								</label>
							  </td>';
						if ($i==3) {
							$i=0;
							echo "</tr>";
						}
					}
					if ($i<3 && $i!=0) echo "</tr>";		
				}
				echo '<td><img class="iconlink" src="images/periodo_add.png" title="Nuevo per&iacute;odo" onclick = "ancoragoh.client.forms.periodo.showForNew(\''.$planificacion.'\');"></td>';
			echo "</tr></table>";
			/*echo '</fieldset><br>';			
		}
	}*/

?>