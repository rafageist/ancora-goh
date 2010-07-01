<img class="iconlink" src="images/nivel_add.png" title="Nuevo nivel" onclick = "ancoragoh.client.forms.nuevoNivel();">
<?php

    if (is_array($niveles->collection)) {
        echo "<table>";
        $i = 0;
        foreach($niveles->collection as $nivel){
            $i++;
            if ($i == 1) echo "<tr>";
            echo '<td align = "center"><img class="iconlink" src="images/nivel.png" onclick = "ancoragoh.client.forms.editNivel(\''.$nivel->id.'\');" ><br>'.$nivel->descripcion.'</td>';
            if ($i == 5) {echo "</tr>"; $i=0;}
        }
        if ($i<5) echo "</tr>";
        echo "</table>";
    }
?>