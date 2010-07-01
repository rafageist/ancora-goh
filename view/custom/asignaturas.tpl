<script>
        function showhide(id){
            if ($('#' + id).css('display')=='block') 
                $('#' + id).css('display','none'); 
            else
                $('#' + id).css('display','block'); 
        }
</script>
<style>
        li{
            list-style-type: none;
            list-style-image: none;
            cursor: pointer;
        }  
</style>
<img class="iconlink" src="images/asignatura_add.png" title="Nueva asignatura" onclick="application.forms.nuevaAsignatura();">
<table style ="border: 1px dashed blue;">
<tr><td>Seleccione Especialidad/Nivel</td><td>Asignaturas</td></tr>
<tr>
<td>
<div id="div_tree_especs" style="height:300px;width:300px;overflow:auto; background: #EEEEEE; border: 1px dashed blue;">
<?php

    $es = $especs->getAllBy("creador ='{$_SESSION['user']}'");
    $ns = $niveles->getAllBy("creador ='{$_SESSION['user']}'");
    
    $i = 0;
    echo "<ul>";
    foreach ($es as $esp){
        $i++;
        $id = "ul_esp_$i";
        echo "<li><label onclick = \"showhide('$id');\"><img src='images/especialidad_lite.png'>".$esp['descripcion']."</label>";
        echo "<ul id='$id'>";
        foreach ($ns as $nivel)
            echo "<li onclick=\"application.browsers.showAsignaturasBy('".$esp['id']."','".$nivel['id']."','div_asigs');\"><img src='images/nivel_lite.png'>".$nivel['descripcion']."</li>";
        echo "</ul></li>";
        
    }
    echo "</ul>";
?>

</div>
</td>
<td>
<div id="div_asigs" style="height:300px;width:500px;overflow:auto; background: #DDDDDD; border: 1px dashed blue;">
</div>
</td>
<tr>
</tr>
</table>