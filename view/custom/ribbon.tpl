	<script type="text/javascript">
	$(function() {
		/*$("#ribbon").tabs().find(".ui-tabs-nav").sortable({axis:'x'});*/
        $("#ribbon").tabs({
            selected: <?php 
                $tab_by_option['planificaciones'] = 0;
                $tab_by_option['periodos'] = 0;
                $tab_by_option['brigadas'] = 0;
                $tab_by_option['especialidades'] = 0;
                $tab_by_option['niveles'] = 0;
                
                $tab_by_option['clasif_act'] = 1;
                $tab_by_option['asignaturas'] = 1;
                
				$tab_by_option['profesores'] = 2;
                $tab_by_option['lugares'] = 2;
                $tab_by_option['otros_recursos'] = 2;
                
                $v = '0';
                foreach($tab_by_option as $key=>$value)
                    if (isset($_GET[$key])){
                        $v = $value;
                        break;
                    }
                echo $v;
            ?>
		});
        $("#ribbon").css("display","block");
	});
    
	</script>
    
<div id="ribbon" style = "display: none;">
	<ul>
		<li><a href="#tabs-1">Tiempo y Objetos</a></li>
        <li><a href="#tabs-2">Actividades</a></li>
		<li><a href="#tabs-3">Recursos</a></li>
		<li><a href="#tabs-4">Horarios</a></li>
	</ul>
	<div id="tabs-1">
		<table>
         <tr>
           <td class = "subribbon" align = "center" valign = "top">
                <table>
                <tr>
                <td align="center">
                    <img class="iconlink" src="images/planificacion.png" title="Planificaciones"><br>
                    <a href = "index.php?planificaciones&listar" class="iconlink">Planificaciones</a>
                </td>
                <td align="center">
                    <img class="iconlink" src="images/planificacion_add.png" title="Nueva planificaci&oacute;n"><br>
                    <a href = "index.php?planificaciones&agregar&event=add_Planificacion" class="iconlink">Nueva planificaci&oacute;n</a>
                </td>
                </tr>
                </table>
                <label class = "subribbon-title">Planificaciones</label>
           </td>
           <td class = "subribbon" align = "center" valign = "top">
                <table>
                <tr>
                <td align="center">
                    <img class="iconlink" src="images/periodo.png" title="Per&iacute;odos"><br>
                    <a href = "index.php?periodos&listar" class="iconlink">Per&iacute;odos</a>
                </td>
                <td align="center">
                    <img class="iconlink" src="images/periodo_add.png" title="Nuevo per&iacute;odo"><br>
                    <a href = "index.php?periodos&agregar&event=add_Periodo" class="iconlink">Nuevo per&iacute;odo</a>
                </td>
                </tr>
                </table>
                <label class = "subribbon-title">Per&iacute;odos</label>
           </td>
           <td class = "subribbon" align = "center" valign = "top">
           <table>
            <tr>
                <td align="center">
                    <img class="iconlink" src="images/especialidad.png" title="Especialidades"><br>
                    <a href = "index.php?especialidades&listar" class="iconlink">Especialidades</a>
                </td>
                <td align="center" align = "center">
                    <img class="iconlink" src="images/especialidad_add.png" title="Nueva especialidad"><br>
                    <a href = "index.php?especialidades&agregar&event=add_Especialidad" class="iconlink"> Nueva especialidad</a>
                </td>
            </tr>
            </table>
            <label class = "subribbon-title">Especialidades</label>
           </td>
           <td class = "subribbon" align = "center" valign = "top">
           <table>
            <tr>
                <td align="center">
                        <img class="iconlink" src="images/nivel.png" title="Niveles"><br>
                        <a href = "index.php?niveles&listar" class="iconlink">Niveles</a>
                    </td>
                    <td align="center">
                        <img class="iconlink" src="images/nivel_add.png" title="Nuevo nivel"><br>
                        <a href = "index.php?niveles&agregar&event=add_Nivel" class="iconlink">Nuevo nivel</a>
                    </td>
            </tr>
            </table>
            <label class = "subribbon-title">Niveles</label>
           </td>
           <td class = "subribbon" align = "center" valign = "top">
           <table>
            <tr>
                <td align="center">
                        <img class="iconlink" src="images/brigada.png" title="Brigadas"><br>
                        <a href = "index.php?brigadas&listar" class="iconlink">Brigadas</a>
                    </td>
                    <td align="center">
                        <img class="iconlink" src="images/brigada_add.png" title="Nueva brigada"><br>
                        <a href = "index.php?brigadas&agregar&event=add_Brigada" class="iconlink">Nueva brigada</a>
                    </td>
            </tr>
            </table>
            <label class = "subribbon-title">Brigadas</label>
           </td>
          </tr>
        </table>
	</div>
    <div id="tabs-2">
    <table>
         <tr>
           <td class = "subribbon" align = "center" valign = "top">
            <table>
                <tr>
                    <td align="center">
                        <img class="iconlink" src="images/clasif_act.png" title="Clasificaciones de actividad"><br>
                        <a href = "index.php?clasif_act&listar" class="iconlink">Clasificaciones de actividad</a>
                    </td>
                    <td align="center">                        
                        <img class="iconlink" src="images/clasif_act_add.png" title="Nueva clasificaci&oacute;n de actividad"><br>
                        <a href = "index.php?clasif_act&agregar&event=add_ClasificacionDeActividad" class="iconlink">Nueva clasificaci&oacute;n de actividad</a>
                    </td>
                </tr>
               </table>	
               <label class = "subribbon-title">Clasificaciones de actividad</label>
             </td>
            <td class = "subribbon" align = "center" valign = "top">
            <table>
                <tr>
                    <td align="center">
                        <img class="iconlink" src="images/asignatura.png" title="Asignaturas"><br>
                        <a href = "index.php?asignaturas&listar" class="iconlink">Asignaturas</a>
                    </td>
                    <td align="center">
                        <img class="iconlink" src="images/asignatura_add.png" title="Nueva asignatura"><br>
                        <a href = "index.php?asignaturas&agregar&event=add_Asignatura" class="iconlink">Nueva asignatura</a>
                    </td>
                </tr>
               </table>	
               <label class = "subribbon-title">Asignaturas</label>
             </td>
          </tr>
    </table>
	</div>
	<div id="tabs-3">
    <table>
         <tr>
           <td class = "subribbon" align = "center" valign = "top">
            <table>
                <tr>
                    <td align="center">
                        <img class="iconlink" src="images/profesor.png" title="Profesores"><br>
                        <a href = "index.php?profesores&listar" class="iconlink">Profesores</a>
                    </td>
                    <td align="center">
                        <img class="iconlink" src="images/profesor_add.png" title="Nuevo profesor"><br>
                        <a href = "index.php?profesores&agregar&event=add_Profesor" class="iconlink">Nuevo profesor</a>
                    </td>
                </tr>
               </table>	
               <label class = "subribbon-title">Profesores</label>
             </td>
            <td class = "subribbon" align = "center" valign = "top">
            <table>
                <tr>
                    <td align="center">
                        <img class="iconlink" src="images/lugar.png" title="Lugares"><br>
                        <a href = "index.php?lugares&listar" class="iconlink">Lugares</a>
                    </td>
                    <td align="center">
                        <img class="iconlink" src="images/lugar_add.png" title="Nuevo lugar"><br>
                        <a href = "index.php?lugares&agregar&event=add_Lugar" class="iconlink">Nuevo lugar</a>
                    </td>
                </tr>
               </table>	
               <label class = "subribbon-title">Lugares</label>
             </td>
             <td class = "subribbon" align = "center" valign = "top">
            <table>
                <tr>
                    <td align="center">
                        <img class="iconlink" src="images/otros_recursos.png" title="Otros recursos"><br>
                        <a href = "index.php?otros_recursos&listar" class="iconlink">Otros recursos</a>
                    </td>
                    <td align="center">
                        <img class="iconlink" src="images/otro_recurso_add.png" title="Otro recurso nuevo"><br>
                        <a href = "index.php?otros_recursos&agregar&event=add_OtroRecurso" class="iconlink">Agregar otro recurso</a>
                    </td>
                </tr>
               </table>	
               <label class = "subribbon-title">Otros recursos</label>
             </td>
          </tr>
    </table>
	</div>
	<div id="tabs-4">
		
	</div>
</div>
