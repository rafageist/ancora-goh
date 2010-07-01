<table>
<tr><td>Turno</td>
<?php 
for ($i =1; $i<=$periodo->cant_dias; $i++)
  echo '<td valign="center" align="center">'.$i.'</td>';
  
?>
</tr>
<?php 

  $colores = array();
  
  function RandomColor(){
  		$color = '#';
  		for($i=1; $i<=6; $i++)
  			$color .= substr("0987654321ABCDEF", rand(0,15),1);
  		return $color;
  }
  
  for ($i =1; $i<=$periodo->cant_turnos; $i++){
	echo '<tr><td align="center">'.$i.'</td>';
		for ($j =1; $j<=$periodo->cant_dias; $j++){
			
			if (isset($horario[$j][$i]->act)){
				$casilla = $horario[$j][$i];
				if (!isset($colores[$casilla->oasig->id]))
					$colores[$casilla->oasig->id] = RandomColor();
				
				$color = $colores[$casilla->oasig->id];
				echo '<td align = "center" valign ="center" class ="casilla iconlink" style = "background: '.$color.';">';
				echo $casilla->oclasif->descripcion."<br>".$casilla->oasig->descripcion."<br>".$casilla->oprofe->nombre."<br>".$casilla->olugar->nombre;
				echo '</td>';
			} else
			echo '<td class ="casilla"></td>';
			
		}
	echo '</tr>';
  }
?>
</table>
