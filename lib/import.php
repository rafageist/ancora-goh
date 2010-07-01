<?php

set_include_path("./");

function import($packages, $debug = false){
	
	global $dbconnection;
	
	if (file_exists($packages)){
		include_once $packages;
		return true;
	}	
	
	$prefix = "";
	$pos = strpos($packages,":", 0); 
	
	if ($pos > 0){
		$prefix = substr($packages,0,$pos);
		$packages = substr($packages,$pos+1);
	}
	
	if ($prefix!='') $prefix = str_replace(".","/",$prefix);

	if ($debug) echo "prefix: $prefix";
	
	$arr = explode(",",$packages);
	
	if ($debug) print_r($arr);
	
	foreach ($arr as $package_name){
		$package_name = trim($package_name);
		
		if (file_exists("path.php")) {
				include_once "path.php";

				global $relative_path;	
			}
		else
			$relative_path = "../";
		
		if (!isset($relative_path))	$relative_path = "../";
		
		$relative_path .= "/";
	
		$relative_path = str_replace("//","/",$relative_path);
		
		$package_name = str_replace(".","/",$package_name);
		
		$path = $relative_path.$prefix."/".$package_name;
		
		$path = str_replace("//","/",$path);

		if ($debug) echo "path: $path";
			
		if (file_exists("$path.php")) include_once "$path.php";
			elseif (file_exists("$path.lib")) include_once "$path.lib";
				elseif (file_exists("$path.conf")) include_once "$path.conf";
					elseif (file_exists("$path.tpl")) include_once "$path.tpl";						
                        elseif (file_exists("$path.frm")) {
                                include_once "$path.frm";
                            }
								elseif (file_exists("$path")) include_once $path;
										else{
											if ($debug) echo "No se pudo importar $path";
										}
}

}

    function importjs($path){
        $path = "js/".$path;
        if (file_exists("$path.js")) 
            echo '   <script type="text/javascript" src="'.$path.'.js"></script>'."\n";
    }

    function importcss($path){
        if (file_exists("$path.css")) 
            echo '  <link type="text/css" href="'.$path.'.css" rel="stylesheet"></script>'."\n";
    }
?>
