<?php 
 /** 
   * @name: gallery grid of OtroRecursoParaActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-otro-recurso-para-actividad">
    <tr class = "table-gallery-grid-header table-borwser-grid-otro-recurso-para-actividad">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-grupo-de-recursos"><label class = "title-of title-of-grupo-de-recursos">Grupo de recursos</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-prioridad"><label class = "title-of title-of-prioridad">Prioridad</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-otro-recurso"><label class = "title-of title-of-otro-recurso">Otro recurso</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-grupo-de-recursos">{$item->grupo_de_recursos}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-prioridad">{$item->prioridad}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-otro-recurso">{$item->otro_recurso}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>