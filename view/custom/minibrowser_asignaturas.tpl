<?php
	    
	    $i = 0;
	    echo "<ul>";
	    foreach ($especs as $esp){
	        $i++;
	        $id = "ul_esp_$i";
	        echo "<li><label onclick = \"ancoragoh.client.showhide('$id');\"><img src='images/especialidad_lite.png'>".$esp['descripcion']."</label>";
	        
	        echo "<ul id='$id'>";
	        $j=0;
	        foreach ($niveles as $nivel){
	        	$j++;
	        	$id .= "_".$j;
	        	echo "<li>
	        	<table width = '100'><tr><td><label onclick = \"ancoragoh.client.showhide('$id');\"><img src='images/nivel_lite.png'>".$nivel['descripcion']."</label></td>
	        	<td align = 'right'><label class = 'opaco' onclick = '
	        	ancoragoh.client.browsers.desgloses.seleccionaEspecialidad(\"".$esp['id']."\");
	        	ancoragoh.client.browsers.desgloses.seleccionaNivel(\"".$nivel['id']."\");
	        	ancoragoh.client.forms.asignatura.showForNew(\"".$esp['id']."\",\"".$nivel['id']."\")' style = 'cursor: pointer;' title = 'Nueva asignatura para este nivel y esta especialidad'> <img src='images/asignatura_add_lite.png'></label></td></tr></table>";
	            
	            $as = $asigmodel->getAllBy('especialidad = $1 AND nivel = $2 ORDER BY descripcion', "*", false, array($esp['id'], $nivel['id']));
	            
	            echo "<ul id='$id'>";
	            
	            if (is_array($as)){
	                foreach ($as as $asig)
	            		echo "<li>
	            		<label onclick = 'ancoragoh.client.browsers.desgloses.seleccionaAsignatura(\"".$asig['id']."\", \"".$esp['id']."\", \"".$nivel['id']."\");' style = 'cursor: pointer;'> <img src='images/asignatura_lite.png'>".$asig['descripcion']."</label>
	            		<label class = 'iconlink opaco' onclick = 'ancoragoh.client.forms.asignatura.showForEdit(\"".$asig['id']."\");' style = 'cursor: pointer;'> <img src='images/edit.png'></label>
	            		</li>";
	            }
	            
	            echo "</li>";
	            echo "</ul>";
	        }
	        echo "</ul></li>";
	    }
	    echo "</ul>";
	?>