<?php 
 /** 
   * @name: gallery grid of RestriccionRealDeOtroRecurso 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-restriccion-real-de-otro-recurso">
    <tr class = "table-gallery-grid-header table-borwser-grid-restriccion-real-de-otro-recurso">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-restriccion-nominal"><label class = "title-of title-of-restriccion-nominal">Restricci&oacute;n nominal</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-periodicidad"><label class = "title-of title-of-periodicidad">Periodicidad</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-fecha"><label class = "title-of title-of-fecha">Fecha</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hora-inicial"><label class = "title-of title-of-hora-inicial">Hora inicial</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hora-final"><label class = "title-of title-of-hora-final">Hora final</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-por-actividad"><label class = "title-of title-of-por-actividad">Por actividad</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-actividad"><label class = "title-of title-of-actividad">Actividad</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-objeto"><label class = "title-of title-of-objeto">Objeto</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-restriccion-nominal">{$item->restriccion_nominal}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-periodicidad">{$item->periodicidad}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-fecha">{$item->fecha}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hora-inicial">{$item->hora_inicial}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hora-final">{$item->hora_final}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-por-actividad">{$item->por_actividad}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-actividad">{$item->actividad}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-objeto">{$item->objeto}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>