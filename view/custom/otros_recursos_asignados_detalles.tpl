<script>
    function agregarRecurso(){
    
    }
</script>
<label style = "cursor: pointer"; onclick="agregarRecurso();"><img src = "images/otro_recurso_add.png"></label>
Necesarios:
<select id = "edtNecesarios">
    <?php
        if (is_array($recs)){
            $c = count($recs);
            for($i = 1; $i <= $c; $i++){
                echo '<option value = "'.$i.'" '.(($rxact->necesarios==$i)?"selected":"").'>'.$i.'</option>';
            }
        }
    ?>
</select>
<br>
<input type="checkbox" id ="chkImprescindibles" <?php echo ($rxact->imprescindibles == true)?"checked":""; ?>> Imprescindibles todos
<table>
<?php
    if (is_array($recs))
        foreach ($recs as $rec){
            $id = $rec['otro_recurso'];
            echo '<tr><td><img src = "images/otro_recurso_lite.png">'.$orecs[$id]->nombre.'</td><td>'.$rec['prioridad'].'</td></tr>';
        }
?>

</table>