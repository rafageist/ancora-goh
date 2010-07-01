<?php
    
    if (is_array($arr)) {
        echo "<table>";
		$i = 0;
        foreach ($arr as $asig){            
			
			if ($i==10){
				$i = 0;
				echo "</tr>";
				}
			$i++;
		    if ($i==1) echo "<tr>";
			
            echo "<td valign='center' align='center'><label style=\"cursor:pointer;\" onclick=\"application.forms.editBrigada('".$asig['id']."')\"><img src='images/asignatura.png'><br>".$asig['descripcion']."</label></td>";
        }
		
		if ($i<10) echo "</tr>";
        echo "</table>";
    }

?>