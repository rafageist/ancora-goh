<?php 
 /** 
   * @name: gallery grid of Periodo 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-periodo">
    <tr class = "table-gallery-grid-header table-borwser-grid-periodo">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-descripcion"><label class = "title-of title-of-descripcion">Descripci&oacute;n</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-cant-dias"><label class = "title-of title-of-cant-dias">Cant dias</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-cant-turnos"><label class = "title-of title-of-cant-turnos">Cant turnos</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hora-inicial"><label class = "title-of title-of-hora-inicial">Hora inicial</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-duracion-del-turno"><label class = "title-of title-of-duracion-del-turno">Duraci&oacute;n del turno</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-planificacion"><label class = "title-of title-of-planificacion">Planificaci&oacute;n</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-descripcion">{$item->descripcion}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-cant-dias">{$item->cant_dias}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-cant-turnos">{$item->cant_turnos}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hora-inicial">{$item->hora_inicial}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-duracion-del-turno">{$item->duracion_del_turno}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-planificacion">{$item->planificacion}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>