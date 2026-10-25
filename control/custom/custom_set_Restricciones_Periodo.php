 <?php
 
   import("model.data.RestriccionDePeriodoModel");
  
   $id = $_POST['field_Periodo_id'];
  
   $m = new RestriccionDePeriodoModel($dbconnection);
    
   $rest = $_POST['field_Restricciones'];
    
   $arr = explode(";",$rest);
   
   foreach ($arr as $k => $v)  
   	$arr[$k] = explode(",", $v);

   $dia = 0;
   foreach ($arr as $fila){
       $dia++;
       $turno = 0;
       foreach ($fila as $cas){
            $turno++;
            if ($cas == 1 || $cas == '1'){
                $r = $m->getAllBy('periodo = $1 AND dia = $2 AND turno = $3', "*", false, array($id, $dia, $turno));
                if (isset($r[0])) 
                	if (isset($r[0]['periodo'])) 
                		if ($r[0]['periodo']==$id) continue;
                $m->add($id, $dia, $turno,1);
            }
            else
                $m->removeBy('periodo = $1 AND dia = $2 AND turno = $3', false, array($id, $dia, $turno));
        }
    } 
 
?>
        