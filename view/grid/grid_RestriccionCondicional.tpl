<?php 
 /** 
   * @name: gallery grid of RestriccionCondicional 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-restriccion-condicional">
    <tr class = "table-gallery-grid-header table-borwser-grid-restriccion-condicional">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-planificacion"><label class = "title-of title-of-planificacion">Planificaci&oacute;n</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-activa"><label class = "title-of title-of-activa">Activa</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-clasif-act"><label class = "title-of title-of-clasif-act">Clasif act</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-asig"><label class = "title-of title-of-asig">Asig</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-brigada"><label class = "title-of title-of-brigada">Brigada</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-profesor"><label class = "title-of title-of-profesor">Profesor</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-lugar"><label class = "title-of title-of-lugar">Lugar</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-otro-recurso"><label class = "title-of title-of-otro-recurso">Otro recurso</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-especialidad"><label class = "title-of title-of-especialidad">Especialidad</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-nivel"><label class = "title-of title-of-nivel">Nivel</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-fecha-ini"><label class = "title-of title-of-fecha-ini">Fecha ini</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-fecha-fin"><label class = "title-of title-of-fecha-fin">Fecha fin</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hora-ini"><label class = "title-of title-of-hora-ini">Hora ini</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hora-fin"><label class = "title-of title-of-hora-fin">Hora fin</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-discreto-exacto"><label class = "title-of title-of-discreto-exacto">Discreto exacto</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-dia-ini-planif"><label class = "title-of title-of-dia-ini-planif">D&iacute;a ini planif</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-dia-fin-planif"><label class = "title-of title-of-dia-fin-planif">D&iacute;a fin planif</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hora-ini-planif"><label class = "title-of title-of-hora-ini-planif">Hora ini planif</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hora-fin-planif"><label class = "title-of title-of-hora-fin-planif">Hora fin planif</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-discreto-exacto-planif"><label class = "title-of title-of-discreto-exacto-planif">Discreto exacto planif</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-periodo"><label class = "title-of title-of-periodo">Periodo</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-dia-ini"><label class = "title-of title-of-dia-ini">D&iacute;a ini</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-dia-fin"><label class = "title-of title-of-dia-fin">D&iacute;a fin</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-turno-ini"><label class = "title-of title-of-turno-ini">Turno ini</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-turno-fin"><label class = "title-of title-of-turno-fin">Turno fin</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-discreto"><label class = "title-of title-of-discreto">Discreto</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hoy"><label class = "title-of title-of-hoy">Hoy</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-ahora"><label class = "title-of title-of-ahora">Ahora</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-dia-relativo-anterior"><label class = "title-of title-of-dia-relativo-anterior">D&iacute;a relativo anterior</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-cant-dias-relativo-anterior"><label class = "title-of title-of-cant-dias-relativo-anterior">Cant dias relativo anterior</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-dia-relativo-posterior"><label class = "title-of title-of-dia-relativo-posterior">D&iacute;a relativo posterior</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-cant-dias-relativo-posterior"><label class = "title-of title-of-cant-dias-relativo-posterior">Cant dias relativo posterior</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-rango-tiempo-relativo-anterior-ini"><label class = "title-of title-of-rango-tiempo-relativo-anterior-ini">Rango tiempo relativo anterior ini</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-rango-tiempo-relativo-anterior-tamano"><label class = "title-of title-of-rango-tiempo-relativo-anterior-tamano">Rango tiempo relativo anterior tamano</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-turno-relativo-anterior"><label class = "title-of title-of-turno-relativo-anterior">Turno relativo anterior</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-cant-turnos-relativo-anterior"><label class = "title-of title-of-cant-turnos-relativo-anterior">Cant turnos relativo anterior</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-turno-relativo-posterior"><label class = "title-of title-of-turno-relativo-posterior">Turno relativo posterior</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-cant-turnos-relativo-posterior"><label class = "title-of title-of-cant-turnos-relativo-posterior">Cant turnos relativo posterior</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-rango-tiempo-relativo-posterior-ini"><label class = "title-of title-of-rango-tiempo-relativo-posterior-ini">Rango tiempo relativo posterior ini</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-rango-tiempo-relativo-posterior-tamano"><label class = "title-of title-of-rango-tiempo-relativo-posterior-tamano">Rango tiempo relativo posterior tamano</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-entre-dias-antes"><label class = "title-of title-of-entre-dias-antes">Entre dias antes</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-entre-dias-despues"><label class = "title-of title-of-entre-dias-despues">Entre dias despues</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-entre-turnos-antes"><label class = "title-of title-of-entre-turnos-antes">Entre turnos antes</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-entre-turnos-despues"><label class = "title-of title-of-entre-turnos-despues">Entre turnos despues</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-entre-tiempo-antes"><label class = "title-of title-of-entre-tiempo-antes">Entre tiempo antes</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-entre-tiempo-despues"><label class = "title-of title-of-entre-tiempo-despues">Entre tiempo despues</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-cant-exa"><label class = "title-of title-of-cant-exa">Cant exa</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-cant-min"><label class = "title-of title-of-cant-min">Cant min</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-cant-max"><label class = "title-of title-of-cant-max">Cant max</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-consec-brigada"><label class = "title-of title-of-consec-brigada">Consec brigada</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-consec-profesor"><label class = "title-of title-of-consec-profesor">Consec profesor</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-consec-lugar"><label class = "title-of title-of-consec-lugar">Consec lugar</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-consec-otro-recurso"><label class = "title-of title-of-consec-otro-recurso">Consec otro recurso</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-consec-periodo"><label class = "title-of title-of-consec-periodo">Consec periodo</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-consec-dia-ini"><label class = "title-of title-of-consec-dia-ini">Consec dia ini</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-consec-dia-fin"><label class = "title-of title-of-consec-dia-fin">Consec dia fin</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-consec-turno-ini"><label class = "title-of title-of-consec-turno-ini">Consec turno ini</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-consec-turno-fin"><label class = "title-of title-of-consec-turno-fin">Consec turno fin</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-consec-discreto"><label class = "title-of title-of-consec-discreto">Consec discreto</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-siempre"><label class = "title-of title-of-siempre">Siempre</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-valor"><label class = "title-of title-of-valor">Valor</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-planificacion">{$item->planificacion}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-activa">{$item->activa}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-clasif-act">{$item->clasif_act}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-asig">{$item->asig}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-brigada">{$item->brigada}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-profesor">{$item->profesor}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-lugar">{$item->lugar}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-otro-recurso">{$item->otro_recurso}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-especialidad">{$item->especialidad}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-nivel">{$item->nivel}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-fecha-ini">{$item->fecha_ini}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-fecha-fin">{$item->fecha_fin}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hora-ini">{$item->hora_ini}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hora-fin">{$item->hora_fin}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-discreto-exacto">{$item->discreto_exacto}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-dia-ini-planif">{$item->dia_ini_planif}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-dia-fin-planif">{$item->dia_fin_planif}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hora-ini-planif">{$item->hora_ini_planif}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hora-fin-planif">{$item->hora_fin_planif}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-discreto-exacto-planif">{$item->discreto_exacto_planif}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-periodo">{$item->periodo}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-dia-ini">{$item->dia_ini}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-dia-fin">{$item->dia_fin}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-turno-ini">{$item->turno_ini}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-turno-fin">{$item->turno_fin}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-discreto">{$item->discreto}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hoy">{$item->hoy}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-ahora">{$item->ahora}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-dia-relativo-anterior">{$item->dia_relativo_anterior}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-cant-dias-relativo-anterior">{$item->cant_dias_relativo_anterior}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-dia-relativo-posterior">{$item->dia_relativo_posterior}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-cant-dias-relativo-posterior">{$item->cant_dias_relativo_posterior}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-rango-tiempo-relativo-anterior-ini">{$item->rango_tiempo_relativo_anterior_ini}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-rango-tiempo-relativo-anterior-tamano">{$item->rango_tiempo_relativo_anterior_tamano}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-turno-relativo-anterior">{$item->turno_relativo_anterior}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-cant-turnos-relativo-anterior">{$item->cant_turnos_relativo_anterior}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-turno-relativo-posterior">{$item->turno_relativo_posterior}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-cant-turnos-relativo-posterior">{$item->cant_turnos_relativo_posterior}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-rango-tiempo-relativo-posterior-ini">{$item->rango_tiempo_relativo_posterior_ini}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-rango-tiempo-relativo-posterior-tamano">{$item->rango_tiempo_relativo_posterior_tamano}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-entre-dias-antes">{$item->entre_dias_antes}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-entre-dias-despues">{$item->entre_dias_despues}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-entre-turnos-antes">{$item->entre_turnos_antes}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-entre-turnos-despues">{$item->entre_turnos_despues}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-entre-tiempo-antes">{$item->entre_tiempo_antes}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-entre-tiempo-despues">{$item->entre_tiempo_despues}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-cant-exa">{$item->cant_exa}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-cant-min">{$item->cant_min}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-cant-max">{$item->cant_max}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-consec-brigada">{$item->consec_brigada}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-consec-profesor">{$item->consec_profesor}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-consec-lugar">{$item->consec_lugar}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-consec-otro-recurso">{$item->consec_otro_recurso}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-consec-periodo">{$item->consec_periodo}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-consec-dia-ini">{$item->consec_dia_ini}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-consec-dia-fin">{$item->consec_dia_fin}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-consec-turno-ini">{$item->consec_turno_ini}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-consec-turno-fin">{$item->consec_turno_fin}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-consec-discreto">{$item->consec_discreto}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-siempre">{$item->siempre}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-valor">{$item->valor}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>