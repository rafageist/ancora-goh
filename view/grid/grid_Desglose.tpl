<?php 
 /** 
   * @name: gallery grid of Desglose 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-desglose">
    <tr class = "table-gallery-grid-header table-borwser-grid-desglose">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-asignatura"><label class = "title-of title-of-asignatura">Asignatura</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-periodo"><label class = "title-of title-of-periodo">Periodo</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-min-dias"><label class = "title-of title-of-min-dias">Min dias</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-max-dias"><label class = "title-of title-of-max-dias">Max dias</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-respetar-orden"><label class = "title-of title-of-respetar-orden">Respetar orden</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-mismo-dia"><label class = "title-of title-of-mismo-dia">Mismo dia</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-asignatura">{$item->asignatura}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-periodo">{$item->periodo}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-min-dias">{$item->min_dias}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-max-dias">{$item->max_dias}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-respetar-orden">{$item->respetar_orden}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-mismo-dia">{$item->mismo_dia}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>