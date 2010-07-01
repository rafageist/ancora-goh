<?php 
 /** 
   * @name: gallery grid of CostoDeTraslado 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-costo-de-traslado">
    <tr class = "table-gallery-grid-header table-borwser-grid-costo-de-traslado">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-origen"><label class = "title-of title-of-origen">Origen</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-destino"><label class = "title-of title-of-destino">Destino</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-costo"><label class = "title-of title-of-costo">Costo</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-origen">{$item->origen}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-destino">{$item->destino}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-costo">{$item->costo}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>