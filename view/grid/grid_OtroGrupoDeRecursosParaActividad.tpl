<?php 
 /** 
   * @name: gallery grid of OtroGrupoDeRecursosParaActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-otro-grupo-de-recursos-para-actividad">
    <tr class = "table-gallery-grid-header table-borwser-grid-otro-grupo-de-recursos-para-actividad">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-plantilla"><label class = "title-of title-of-plantilla">Plantilla</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-prioridad"><label class = "title-of title-of-prioridad">Prioridad</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-necesarios"><label class = "title-of title-of-necesarios">Necesarios</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-imprescindibles"><label class = "title-of title-of-imprescindibles">Imprescindibles</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-creador"><label class = "title-of title-of-creador">Creador</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-plantilla">{$item->plantilla}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-prioridad">{$item->prioridad}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-necesarios">{$item->necesarios}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-imprescindibles">{$item->imprescindibles}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-creador">{$item->creador}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>