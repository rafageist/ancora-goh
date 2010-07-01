<?php 
 /** 
   * @name: gallery grid of ProfesorParaActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-profesor-para-actividad">
    <tr class = "table-gallery-grid-header table-borwser-grid-profesor-para-actividad">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-plantilla"><label class = "title-of title-of-plantilla">Plantilla</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-profesor"><label class = "title-of title-of-profesor">Profesor</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-prioridad"><label class = "title-of title-of-prioridad">Prioridad</label></td>
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
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-profesor">{$item->profesor}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-prioridad">{$item->prioridad}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>