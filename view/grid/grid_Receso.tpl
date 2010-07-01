<?php 
 /** 
   * @name: gallery grid of Receso 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-receso">
    <tr class = "table-gallery-grid-header table-borwser-grid-receso">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-periodo"><label class = "title-of title-of-periodo">Periodo</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-despues-del-turno"><label class = "title-of title-of-despues-del-turno">Despues del turno</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-duracion"><label class = "title-of title-of-duracion">Duraci&oacute;n</label></td>
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
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-despues-del-turno">{$item->despues_del_turno}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-duracion">{$item->duracion}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>