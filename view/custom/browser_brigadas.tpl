<style>
	li{
		list-style-type: none;
		list-style-image: none;
		cursor: pointer;
	}  
</style>
<input type="hidden" id="data_idplanif_selected" value = "<?php  if (is_array($planificaciones)) echo $planificaciones[0]['id']; ?>">
<input type="hidden" id="data_controlplanif_selected" value = "<?php  if (is_array($planificaciones)) echo "selectPlanif1"; ?>">
<input type='hidden' id="data_idesp_selected" value = "">
<input type='hidden' id="data_idnivel_selected" value = "">
<table width = "100%" style = "border: 1px dashed navy;">
<table width = "100%">
	<tr>
		<td colspan = 2>
			<div class = "toolbar">
			<table>
				<tr>
					
					<td><img class = "iconlink" src = "images/especialidad_add.png" title = "Nueva planificaci&oacute;n" onclick = "ancoragoh.client.forms.planificacion.showForNew();"></td>
					<td><img class = "iconlink" src = "images/nivel_add.png" title = "Gestionar planificaciones" onclick = "$('#browserPlanficaciones').fadeIn('fast'); $('#div-periodos').fadeIn('fast'); $('#diagrama_gantt').fadeOut('fast');"></td>
				</tr>
			 </table>
			 </div>
		 </td>
	</tr>
    <tr>
        <td width = "32">
            <img class="iconlink" src="images/brigada_add.png" title="Nueva brigada" onclick="ancoragoh.client.forms.brigada.showForNew();">
        </td>
        <td>
            <?php
                if (is_array($planificaciones)){
                    echo "<table><tr>";
                    $primera = true;
                    $i = 0;
                    foreach ($planificaciones as $plan){
                        $i++;
                        $selected = ($i == 1)?'style="background: #AAAAAA; border: 3px dashed #AAAAAA; color: white;"':'';
                        echo '<td id = "selectPlanif'.$i.'" align = "center" '.$selected.'><label style = "cursor: pointer; font-weight: bold;" onclick = "selectPlanificacion(\''.$plan['id'].'\',\'selectPlanif'.$i.'\');"><img src="images/planificacion.png"><br>'.$plan['descripcion'].'</label></td>';
                    }
                    echo "</tr></table>";
                }
            ?>
        </td>
        <td height = "50" align = "right">
                        <div id="brggrpselector" style="display:none; height:50px;">
                          <label class = "iconlink" style="cursor:pointer;" onclick="showBrigadas()" title = "Mostrar brigadas"><img src="images/brigada.png"></label>
                          <label class = "iconlink" style="cursor:pointer;" onclick="showGrupos()" title = "Mostrar grupos por clasificaci&oacute;n de actividad"><img src="images/grupo.png"></label>
                        </div>
                    </td>
    </tr>
</table>
<table style ="border: 1px dashed blue;" width = "100%" height = "70%" table=1>
    <tr>
        <td>Seleccione Especialidad/Nivel</td><td>Brigadas</td>
    </tr>
    <tr>
        <td width = "300" style="background: #DDDDDD;">
            <div id="div_tree_especs" style="height:400px; width:300px;overflow:auto; background: #EEEEEE; border: 1px dashed blue;">
            <?php

                $es = $especs->getAllBy('creador = $1', "*", false, array($_SESSION['user']));
                $ns = $niveles->getAllBy('creador = $1', "*", false, array($_SESSION['user']));
                
                $i = 0;
                echo "<ul>";
                foreach ($es as $esp){
                    $i++;
                    $id = "ul_esp_$i";
                    echo "<li><label onclick = \"showhide('$id');\"><img src='images/especialidad_lite.png'>".$esp['descripcion']."</label>";
                    echo "<ul id='$id'>";
                    foreach ($ns as $nivel)
                        echo "<li onclick=\"ancoragoh.client.browsers.brigadas.showBrigadasBy('".$esp['id']."','".$nivel['id']."');\"><img src='images/nivel_lite.png'>".$nivel['descripcion']."</li>";
                    echo "</ul></li>";
                    
                }
                echo "</ul>";
            ?>

            </div>
        </td>
        <td valign="top" border=1>
             <div id="div_brgs" style="height:400px; width:100%;overflow:auto; background: #DDDDDD; border: 1px dashed blue;">
             </div>
        </td>
    </tr>
</table>