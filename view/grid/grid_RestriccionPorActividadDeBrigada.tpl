<?php 
 /** 
   * @name: gallery grid of RestriccionPorActividadDeBrigada 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-restriccion-por-actividad-de-brigada">
    <tr class = "table-gallery-grid-header table-borwser-grid-restriccion-por-actividad-de-brigada">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-restriccion-nominal"><label class = "title-of title-of-restriccion-nominal">Restricci&oacute;n nominal</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-actividad"><label class = "title-of title-of-actividad">Actividad</label></td>
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
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-actividad">{$item->actividad}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>