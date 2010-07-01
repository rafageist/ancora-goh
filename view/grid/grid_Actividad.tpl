<?php 
 /** 
   * @name: gallery grid of Actividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-actividad">
    <tr class = "table-gallery-grid-header table-borwser-grid-actividad">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-plantilla"><label class = "title-of title-of-plantilla">Plantilla</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-grupo"><label class = "title-of title-of-grupo">Grupo</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-profesor"><label class = "title-of title-of-profesor">Profesor</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-lugar"><label class = "title-of title-of-lugar">Lugar</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-dia"><label class = "title-of title-of-dia">D&iacute;a</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-turno"><label class = "title-of title-of-turno">Turno</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-momento-de-colocacion"><label class = "title-of title-of-momento-de-colocacion">Momento de colocaci&oacute;n</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-creador"><label class = "title-of title-of-creador">Creador</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-plantilla">{$item->plantilla}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-grupo">{$item->grupo}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-profesor">{$item->profesor}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-lugar">{$item->lugar}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-dia">{$item->dia}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-turno">{$item->turno}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-momento-de-colocacion">{$item->momento_de_colocacion}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-creador">{$item->creador}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>