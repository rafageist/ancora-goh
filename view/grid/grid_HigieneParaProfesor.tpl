<?php 
 /** 
   * @name: gallery grid of HigieneParaProfesor 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-higiene-para-profesor">
    <tr class = "table-gallery-grid-header table-borwser-grid-higiene-para-profesor">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-periodo"><label class = "title-of title-of-periodo">Periodo</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-profesor"><label class = "title-of title-of-profesor">Profesor</label></td>
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
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-profesor">{$item->profesor}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-gasto-maximo-diario">{$item->gasto_maximo_diario}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>