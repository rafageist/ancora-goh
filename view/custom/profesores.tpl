<img class="iconlink" src="images/profesor_add.png" title="Nuevo profesor" onclick="application.forms.nuevoProfesor();">
<?php

if (is_array($collection->collection)) {
	echo "<table>";
	$i = 0;
	foreach($collection->collection as $item){
		if ($i==10) {
			$i=0;
			echo "</tr>";
			}
		
		$i++;
		if ($i==1) echo "<tr>";
		echo "<td align='center'><label style='cursor: pointer' onclick='application.forms.editProfesor(\"".$item->id."\");'> <img src='images/profesor.png'><br>".$item->nombre.'</td>';
	}
	
	if ($i<10) echo "</tr>";
	echo "</table>";
	}
?>