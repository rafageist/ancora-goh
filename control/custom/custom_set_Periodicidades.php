 <?php
 
    import("model.data.PeriodicidadModel");
  
    $id = $_POST['field_Periodo_id'];
  
    $m = new PeriodicidadModel($dbconnection);
 
    $rest = $_POST['field_Periodicidades'];
    
    $arr = explode(",",$rest);
    
    $dia = 0;
    foreach ($arr as $cas){
        $dia++;
        	$r = $m->getAllBy('periodo = $1 AND dia = $2', "*", false, array($id, $dia));
        	
        	$existe = false;
        	
        	if (isset($r[0])) if (isset($r[0]['periodo'])) if ($r[0]['periodo'] == $id) $existe = true;
        	
        	if ($existe == false) {
        		if ($cas == 1 || $cas == '1') 
        			$m->add($id, $dia);
        	} else {
        		if ($cas == 0 && $cas == '0'){
        			$m->removeBy('periodo = $1 AND dia = $2', false, array($id, $dia));
        		} 
        	}
   }
 
?>
        