<?php 
 /** 
   * @name: gallery grid of PlantillaDeActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-plantilla-de-actividad">
    <tr class = "table-gallery-grid-header table-borwser-grid-plantilla-de-actividad">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-desglose"><label class = "title-of title-of-desglose">Desglose</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-orden"><label class = "title-of title-of-orden">Orden</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-clasificacion"><label class = "title-of title-of-clasificacion">Clasificaci&oacute;n</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-exige-profesor"><label class = "title-of title-of-exige-profesor">Exige profesor</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-exige-lugar"><label class = "title-of title-of-exige-lugar">Exige lugar</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-desglose">{$item->desglose}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-orden">{$item->orden}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-clasificacion">{$item->clasificacion}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-exige-profesor">{$item->exige_profesor}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-exige-lugar">{$item->exige_lugar}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>