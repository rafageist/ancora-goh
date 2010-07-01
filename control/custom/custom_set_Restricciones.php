<?php

    if (isset($_POST['field_tipo'])){
        
        $tipo = $_POST['field_tipo'];
        $id = $_POST['field_id'];
        $periodo = $_POST['field_periodo'];
        
        switch($tipo){
            
            case 'lugares':
            
                include_once "../modelo/RestriccionDeLugarModel.php";
                include_once "../modelo/RestriccionDeLugar.php";
                include_once "../modelo/RestriccionDeLugarCollection.php";
                
                $m = new RestriccionDeLugarModel($dbconnection);
                
                //eliminar las actuales
                $m->removeBy("lugar = '$id' AND periodo = '$periodo'");
                
                // insertar las nuevas y viejas
                $prefix = "field_rest_";
                
                foreach ($_POST as $key => $value){
                    if (substr($key,0,strlen($prefix))==$prefix){
                        $arr = explode("_",$key);
                        $dia = $arr[2];
                        $turno = $arr[3];
                        $r = new RestriccionDeLugar($id, $periodo, $dia, $turno, 1, false);
                    }
                }
                
                echo getHTMLInstantMessage("Restricciones guardadas satisfactoriamente");
                break;
            
            case 'otros_recursos':
            
                include_once "../modelo/RestriccionDeOtroRecursoModel.php";
                include_once "../modelo/RestriccionDeOtroRecurso.php";
                include_once "../modelo/RestriccionDeOtroRecursoCollection.php";
                
                $m = new RestriccionDeOtroRecursoModel($dbconnection);
                
                //eliminar las actuales
                $m->removeBy("otro_recurso = '$id' AND periodo = '$periodo'");
                
                // insertar las nuevas y viejas
                $prefix = "field_rest_";
                
                foreach ($_POST as $key => $value){
                    if (substr($key,0,strlen($prefix))==$prefix){
                        $arr = explode("_",$key);
                        $dia = $arr[2];
                        $turno = $arr[3];
                        $r = new RestriccionDeOtroRecurso($id, $periodo, $dia, $turno, 1, false);
                    }
                }
                
                echo getHTMLInstantMessage("<img src = \"images/aceptar.png\"> Restricciones guardadas satisfactoriamente");
                break;
        }
        
    }

?>