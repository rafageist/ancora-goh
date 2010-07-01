<?php 
 /** 
   * @name: gallery grid of CancelarGenerador 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-cancelar">
    <tr class = "table-gallery-grid-header table-borwser-grid-cancelar">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-especialidad"><label class = "title-of title-of-especialidad">Especialidad</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-nivel"><label class = "title-of title-of-nivel">Nivel</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-periodo"><label class = "title-of title-of-periodo">Periodo</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-especialidad">{$item->especialidad}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-nivel">{$item->nivel}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-periodo">{$item->periodo}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>