<img class="iconlink" src="images/especialidad_add.png" title="Nueva especialidad" onclick = "ancoragoh.client.forms.especialidad.showForNew();">
<?php

    echo "<table>";
    $i = 0;
    foreach($esps->collection as $esp){
        $i++;
        if ($i == 1) echo "<tr>";
        echo '<td align = "center"><img class="iconlink" src="images/especialidad.png" onclick = "ancoragoh.client.forms.especialidad.showForEdit(\''.$esp->id.'\');" ><br>'.$esp->descripcion.'</td>';
        if ($i == 5) {echo "</tr>"; $i=0;}
    }
    if ($i<5) echo "</tr>";
    echo "</table>";
?>