<?php 
 /** 
   * @name: gallery grid of HigieneParaBrigada 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-higiene-para-brigada">
    <tr class = "table-gallery-grid-header table-borwser-grid-higiene-para-brigada">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-periodo"><label class = "title-of title-of-periodo">Periodo</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-brigada"><label class = "title-of title-of-brigada">Brigada</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-gasto-maximo-diario"><label class = "title-of title-of-gasto-maximo-diario">Gasto maximo diario</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-periodo">{$item->periodo}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-brigada">{$item->brigada}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-gasto-maximo-diario">{$item->gasto_maximo_diario}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>