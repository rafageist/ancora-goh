<img class="iconlink" src="images/lugar_add.png" title="Nuevo lugar" onclick="application.forms.nuevoLugar();">
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
		echo "<td align='center'><label style='cursor: pointer' onclick='application.forms.editLugar(\"".$item->id."\");'> <img src='images/lugar.png'><br>".$item->nombre.'</td>';
	}
	
	if ($i<10) echo "</tr>";
	echo "</table>";
	}
?>