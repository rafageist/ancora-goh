<?php 

	$components = array();
	$fallos = array();
	
	function cargar($path){
		global $components;
		
		echo "[INFO] Cargando $path ... \n";
		
		$f = fopen ($path, "r");
		
		while (!feof($f)){
			$s = fgets($f);
			$s = trim($s);
			if (substr($s,0,1)=="#" || $s == "") continue;
			
			$p = strpos($s,":");
			$tipo = trim(substr($s,0,$p));
			$s = substr($s,$p+1);
			
			$arr = explode ("@", $s);
			
			$pack = "";
			$dependences = "";
			$desc = "";
			
			if (isset($arr[0])) $pack = trim($arr[0]);
			if (isset($arr[1])) $desc = trim($arr[1]);
			if (isset($arr[2])) $dependences = trim($arr[2]);
			
				$ds = array();
				if ($dependences!="") {
						$ds = explode(",",$dependences);
						foreach ($ds as $k=>$v)
							$ds[$k] = trim($v);
						
				}
			
			$components[$pack] = array("desc"=>$desc,"dependences"=>$ds,"anexado" => false);
			
		}
		fclose($f);
	}
	
	function packsiguales($flexible, $solid){
		
		$flexible = strtolower(trim($flexible));
		$solid = strtolower(trim($solid));
		//iguales
		if ($flexible==$solid) return true;
		// termina en
		if (substr($flexible,0,1)=="*" && substr($flexible,1) == substr($solid,strlen($solid) - strlen($flexible))) return true;
		// comienza en
		if (substr($flexible,strlen($flexible)-1,1)=="*" && substr($flexible,0,strlen($flexible)-1) == substr($solid,0,strlen($flexible))-1) return true;

		// solo nombre del componente
		if ($flexible == substr($solid,strlen($solid) - strlen($flexible))) return true;

		return false;

	}
	
	function anexa($fout, $pack){
		global $components;
		global $fallos;
		
		foreach ($components as $k => $component){
			
			if (packsiguales($pack,$k)==true){
				
				if ($component["anexado"]==false){ // evitar bucle infinito
					
					// anexar las dependencias primero
					
					foreach ($component['dependences'] as $dep){
						// llamada recursiva
						if ($dep!=""){
						//echo "dependencia $dep \n";
						anexa($fout, $dep);}
					}
					
					echo "[INFO] Anexando ... $k \n";
					
					$path = "./".str_replace(".","/",$k).".sql";
					
					$f = fopen($path,"r");
					fputs($fout,"/* ------------------------------ $k ------------------------- */ \n");
					if ($f){
						while (!feof($f)){
							$s = fgets($f);
							fputs($fout,$s);
						}
						
						fputs($fout,"/* --------------------------------------------------------- */ \n");
						fclose($f);
					}					
					else{
						$fallos[] = $k;
						echo "[FAIL] No se encontro $path \n";
					}
					
					
					$components[$k]["anexado"]=true;
					break;
				}
			}
				
		}
	}

	cargar("./deploy.priority");
	
	$fout = fopen("./result.sql","w");
	
	foreach ($components as $k => $component) anexa($fout, $k);
	
	fclose($fout);
	
	if (count($fallos)>0) print_r($fallos);
?>