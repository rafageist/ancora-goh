<table height="100%" width="100%">
<tr>
<td align="center" valign="top" width="50">
<?php

    if (is_array($clasifs)){
        foreach ($clasifs as $cla){
            $idclasif = $cla['id'];
            echo '<label style = "cursor: pointer" onclick = "ancoragoh.client.browsers.organizacion.showGruposFiltrados(\''.$idclasif.'\')"><img src="images/clasif_act.png"><br>'.$cla['descripcion']."<br></label><br>";
            
        }
    }
?>
</td>
<td valign = "top">
<div id = "div_grupos" style="background: #EEEEEE; height: 350px; width: 100%; border: 1px dashed black; overflow: auto;">
    <img src="images/flechita_izquierda.png"> Seleccione la clasificaci&oacute;n de actividad
</div>
</td>
</tr>
</table>