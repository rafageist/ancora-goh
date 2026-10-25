<?php
/*
 ____________________________________
 _______/                                   /
 ______/ - ** cogePHP **                   /
 _____/ - C-oge O-tro GE-nerador para PHP /
 ____/ - version 1.0                     /
 ___/ - por Rafael Rodriguez Ramirez    /
 __/___________________________________/

 Generador de codigo PHP

 Este script genera

 - servicios

 desde un modelo en JSON, con una especificacion requerida

 */

include "cogephp.common.php";
include "../lib/basic/file/DirectoryUtil.php";


// preparando...

    preparaPaquetes();

    $fservs = DirectoryUtil::getFilesWithExtension("./services","json");
    
    
    foreach ($fservs as $fserv){
        $fichero = fopen("./services/$fserv","r");
	 
        if ($fichero!=false){
            prints("[OK]");
            prints("[INFO] Cargando modelo de servicio de datos'".$fserv."' ...");

            $json = new Services_JSON();
            $jsoncode = "";
             
            while (!(feof($fichero))) $jsoncode = $jsoncode.fgets($fichero);

            $service = $json->decode($jsoncode);
            
            $fservice = fopen($relative_path.dame_carpeta_de($service->name."Service","services")."/".$service->name."Service.php","w");

            fputs($fservice,"<?php \n");
            fputComentario($fservice,$service->description,"",array(),'',"      ");
                
            fputs($fservice, "      class ".$service->name."Service extends PartModel{ \n");
            fputs($fservice,'          var $db;'." \n");
                
            fputComentario($fservice,$service->name,"Constructor",array(),'');

            fputs($fservice,'          public function '.$service->name.'($db){'." \n");
            fputs($fservice,'                $this->db = $db; '." \n");
            fputs($fservice,'          }'."\n");

            fputs($fservice," \n");
                
                
            foreach ($service->methods as $method){
                fputComentario($fservice,$method->name,$method->descrip,array(),'');
                fputs($fservice,'          function '.$method->name.'(');
                $i=0;
                foreach ($method->params as $param) {
                    $i++;
                    if ($i>1) fputs($fservice,',');
                    fputs($fservice,'$'.$param);
                }
                if ($i>0) fputs($fservice,',');
                fputs($fservice, '$asobjects = false'."){ \n");
                // #35: each $param (optionally in single quotes) of the SQL
                // becomes a bound placeholder; values are never interpolated.
                list($sql, $binds) = serviceSqlParams($method);
                fputs($fservice,'               $sql = "'.addcslashes($sql, '"\\$').'"; '."\n");
                fputs($fservice,'               return $this->getAnythingParams($sql, array('.implode(', ', $binds).'), $asobjects);'."\n");
                fputs($fservice,"            } \n");
            }

                
            fputs($fservice, "    } \n");
            fputs($fservice, "?>");
            fclose ($fservice);
        }
    }
    
if(!isset($_SERVER['argv']) || php_sapi_name()!=='cli')
	echo "</body></html>";
?>
