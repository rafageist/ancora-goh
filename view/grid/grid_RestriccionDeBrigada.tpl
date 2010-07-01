<?php 
 /** 
   * @name: gallery grid of RestriccionDeBrigada 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-restriccion-de-brigada">
    <tr class = "table-gallery-grid-header table-borwser-grid-restriccion-de-brigada">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-brigada"><label class = "title-of title-of-brigada">Brigada</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-periodo"><label class = "title-of title-of-periodo">Periodo</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-dia"><label class = "title-of title-of-dia">D&iacute;a</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-turno"><label class = "title-of title-of-turno">Turno</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-valor"><label class = "title-of title-of-valor">Valor</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-por-actividad"><label class = "title-of title-of-por-actividad">Por actividad</label></td>
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
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-brigada">{$item->brigada}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-periodo">{$item->periodo}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-dia">{$item->dia}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-turno">{$item->turno}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-valor">{$item->valor}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-por-actividad">{$item->por_actividad}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-actividad">{$item->actividad}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>