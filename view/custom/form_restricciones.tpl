<?php
include_once "../lib/timeutils.lib";
?>
<h1>Restricciones</h1>
<table align = "center" width = "100%">
<tr>
<td valign = "top">
<div style = "overflow:auto">
<?php
    foreach($enlaces_periodos as $key=>$enlace){
        if (isset($enlace["periodos"])) {
            echo "<fieldset title ='{$enlace['descrip']}'>";
            echo $enlace['descrip'];
            echo "<table><tr>";
            foreach ($enlace["periodos"] as $k => $per)
                echo "<td align=\"center\"><img src=\"images/periodo.png\"><br><a href =\"index.php?$tipo_restricciones&restricciones&periodo=$k&id=$id\">$per</a></td>";
            echo "<tr></table>";
            echo "</fieldset>";
        }
    }
?>
<div>
</td>
<td>
<?php if ($periodo !='') { ?>
<form id = "form-restricciones" method = "post" action = <?php echo "\"index.php?$tipo_restricciones&restricciones&periodo=$periodo&id=$id\""; ?>S>
    <?php echo $titulo; ?>
    <table align="center" border = "1" width = "100%" height = "100%">
    <input type ="hidden" name = "field_tipo" value="<?php echo $tipo_restricciones; ?>">
    <input type ="hidden" name = "field_id" value="<?php echo $id; ?>">
    <input type ="hidden" name = "field_periodo" value="<?php echo $periodo; ?>">
    <?php

        echo "<table><tr><td>Hora</td><td>Turno</td>";
       for ($j = 1; $j <= $cant_dias; $j++) 
         echo "<td>$j</td>";
     
         echo "</tr>";
         
         for($i = 1; $i <= $cant_turnos; $i++){
            $hi = TimeUtils::number_to_hour(($i-1) * $duracion_del_turno);
            $hf = TimeUtils::number_to_hour(($i-1) * $duracion_del_turno + $duracion_del_turno-1);
            echo "<tr><td>".($hi->h < 10?"0":"")."{$hi->h}:".($hi->m < 10?"0":"")."{$hi->m} - ".($hf->h<10?"0":"")."{$hf->h}:".($hf->m<10?"0":"")."{$hf->m}</td><td  align=\"right\"> $i</td>";
            for ($j = 1; $j <= $cant_dias; $j++) {
                $v = '';
                if (isset($rest[$j][$i])) 
                    if ($rest[$j][$i]==1) $v = 'checked'; 
                     if ($v != 'disabled')
                echo "<td><input type=\"checkbox\" name = \"field_rest_{$j}_{$i}\" $v> </td>";
            else
                echo "<td align = 'center' title ='Restriccion por actividad'>X</td>";
            }
        }
    ?>
    </table>
    <input type = "submit" name ="app_event_<?php echo $evento; ?>" value = "Guardar">
</form>
<?php }?>
</td>
</table>
