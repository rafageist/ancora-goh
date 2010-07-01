<?php 
 /** 
   * @name: gallery grid of LugarFijo 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-lugar-fijo">
    <tr class = "table-gallery-grid-header table-borwser-grid-lugar-fijo">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-grupo"><label class = "title-of title-of-grupo">Grupo</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-lugar"><label class = "title-of title-of-lugar">Lugar</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-grupo">{$item->grupo}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-lugar">{$item->lugar}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>