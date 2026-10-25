<?php
/*
        ____________________________________
_______/                                   / 
______/ - ** cogePHP **                   / 
_____/ - C-oge O-tro GE-nerador para PHP /
____/ - version 1.0                     / 
___/ - por Rafael Rodriguez Ramirez    / 
__/___________________________________/
   
    Generador de código PHP
    
    Este script genera el 

        - clases de vista para clase
        - clases de control para clase
    
    desde un modelo en JSON, con una especificacion requerida
    
*/

include "cogephp.common.php";

// preparando...

preparaPaquetes();

// cargando el modelo
$modelo = cargaModelo($modelfilename);
/*
// Eliminando archivos generados anteriormente

    //eliminando de eventos
    if (file_exists("control/generated.files")){
        $f = fopen("control/generated.files", "r");
        while (!feof($f)){
            $s = fgets($f);
            if (trim($s)!="") if (file_exists("eventos/$s")) unlink("eventos/$s");
        }
        fclose($f);
    }

    $f = fopen("eventos/generated.files", "w");

    //eliminando de vistas

    if (file_exists("vista/generated.files")){
        $f1 = fopen("vista/generated.files", "r");
        while (!feof($f)){
            $s = fgets($f);
            if (trim($s)!="") if (file_exists("vista/$s")) unlink("vista/$s");
        }
        fclose($f1);
    }

    $f1 = fopen("view/generated.files", "w");  
    */
//generando...
    foreach ($modelo->data->tables as $tabla){  
        $classname = getClassNameOf($tabla->schema, $tabla->name);
		$collectionname = $classname."Collection";
        $carpeta = dame_carpeta_de($classname);
		
        prints("[INFO] Creando SCRIPT php...".$tabla->name);
        
        //-------- TPLs ------------
        
        // gallery gallery
        //fputs($f1, "gallery_gallery_".$classname.".tpl\n");
        $file_tpl = fopen("../view/gallery/gallery_".$classname.".tpl","w");
        
        fputs($file_tpl,"<?php \n");
        fputComentario($file_tpl,"gallery gallery of $classname",'Part View of MVC pattern',array(),'',' ');
        fputs($file_tpl,"\n");
        fputs($file_tpl,'if (!isset($link)) $link = "";'."\n");
        fputs($file_tpl,'if (!isset($fieldlink)) $fieldlink = "";'."\n");
        fputs($file_tpl,'if (!isset($columns)) $columns = 3;'."\n");
        fputs($file_tpl,"\n");
        fputs($file_tpl,'echo $title;'."\n");
        fputs($file_tpl,'echo \'<table class = "view-gallery" id = "view-gallery-'.$tabla->name.'"><tr>\';'."\n");
        fputs($file_tpl,'$i = $offset; $c=0;'."\n");
        fputs($file_tpl,'if (is_array($collection->collection )) foreach ($collection->collection as $item){'."\n");
        fputs($file_tpl,'   $i++; $c++;'."\n");
        fputs($file_tpl,'   if ($i == $offset+1 || $c==1) echo "<tr>";'."\n");
        fputs($file_tpl,'   if ($c>$columns) {echo "</tr><tr>"; $c = 1;}'."\n");
        fputs($file_tpl,'   $link_ini =""; $link_fin = ""; '."\n");
        fputs($file_tpl,'   if ($link!="")'."\n");
        // #14: emit property access instead of eval() of request text
        fputs($file_tpl, <<<'TPL'
      // #14: no eval of request text; $fieldlink must name a property of $item
      if (is_string($fieldlink) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $fieldlink) && is_object($item) && property_exists($item, $fieldlink))
         $link_ini = "<a href = '".htmlspecialchars($link."&".$fieldlink."=".$item->$fieldlink, ENT_QUOTES)."'>";

TPL
        );
        fputs($file_tpl,'   echo "<td align=\'center\'><div class=\"gallery\">$link_ini<img src=\'$icon\'><br>";'."\n");
        fputs($file_tpl, <<<'TPL'
   if (isset($fielddescrip) && is_string($fielddescrip) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $fielddescrip) && is_object($item) && property_exists($item, $fielddescrip))
      echo $item->$fielddescrip;

TPL
        );
        fputs($file_tpl,'   echo "$link_fin</div></td>";'."\n");     
        fputs($file_tpl,'}'."\n");
        fputs($file_tpl,'echo "</table>";'."\n\n");
        fputs($file_tpl,"\n");
        fputs($file_tpl,"?> \n");
        fclose($file_tpl);
        
           
            
        
        // gallery grid
        //fputs($f1, "gallery_grid_".$classname.".tpl\n");
        $file_tpl = fopen("../view/grid/grid_".$classname.".tpl","w");
        
        fputs($file_tpl,"<?php \n");
        fputComentario($file_tpl,"gallery grid of $classname",'Part View of MVC pattern',array(),'',' ');
        fputs($file_tpl,"\n");
        fputs($file_tpl,"?>\n");
        fputs($file_tpl,'<table class = "table-gallery-grid table-gallery-grid-'.str_replace("_","-",$tabla->name).'">'."\n");
        fputs($file_tpl,'    <tr class = "table-gallery-grid-header table-borwser-grid-'.str_replace("_","-",$tabla->name).'">'."\n");
        fputs($file_tpl,'        <td></td>'."\n");
        
        foreach ($tabla->fields as $campo){
            if ($campo->type!='replica')
                fputs($file_tpl,'        <td class ="table-gallery-grid-header-td table-gallery-header-td-'.str_replace("_","-",$campo->name).'">'.getTitleOf($campo->name).'</td>'."\n");
        }
        fputs($file_tpl,'        <td><button>Eliminar</button></td>'."\n");
        fputs($file_tpl,'        <td></td>'."\n");
        fputs($file_tpl,'    </tr>'."\n\n");
        fputs($file_tpl,"<?php \n");
        fputs($file_tpl,' $i = $offset;'."\n");
        fputs($file_tpl,' if (is_array($collection->collection )) '."\n");
        fputs($file_tpl,'    foreach ($collection->collection as $item){'."\n");
        fputs($file_tpl,'       $i++;'."\n");
        fputs($file_tpl,'       echo "<tr class = \"table-gallery-grid-row\">";'."\n");
        
        foreach ($tabla->fields as $campo){
            if ($campo->type!='replica')
                fputs($file_tpl,'       echo \'   <td class ="table-gallery-grid-td table-gallery-grid-td-'.str_replace("_","-",$campo->name).'">{$item->'.$campo->name.'}</td>\';'."\n");
        }
        
        fputs($file_tpl,'       echo "   <td><input type=\'checkbox\' name=\'chk_$i\'></td><td><input type=\'submit\' name=\'edtEdit_$i\'></td>";'."\n");
        fputs($file_tpl,'       echo "</tr>";'."\n");
        fputs($file_tpl,'    }'."\n");
        fputs($file_tpl,"?>\n");
        fputs($file_tpl,'</table>');
        
        fclose($file_tpl);
        
         //evento
            //fputs($f, "get_ui_gallery_".$classname.".php\n");
            $file_e = fopen("../control/view/gallery/get_ui_gallery_".$classname.".php","w");

            fputs($file_e,"<?php \n");
            fputComentario($file_e,"gallery views of $classname",'Part Control of MVC pattern',array(),'',' ');
            fputs($file_e,"\n");
            
            fputs($file_e,' import ("'.str_replace("/",".",$carpeta).".".$classname.'Model.php");'."\n");
            fputs($file_e,' import ("'.str_replace("/",".",$carpeta).".".$classname.'.php");'."\n");
            fputs($file_e,' import ("'.str_replace("/",".",$carpeta).".".$classname.'Collection.php");'."\n");
           
            fputs($file_e,"\n");
            fputs($file_e,' $collection = new '.$classname.'($dbconnection);'."\n");
            fputs($file_e,' $collection->Load();'."\n");
            fputs($file_e,"\n");
            
            fputs($file_e,' if (isset($_POST["forma"])){ '."\n");
            fputs($file_e,'   switch($_POST["forma"]){'."\n");
            fputs($file_e,'     case "gallery":'."\n");     
            fputs($file_e,'       if (isset($_POST["link"])) $link = $_POST["link"];'."\n");
            fputs($file_e,'       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];'."\n");
            fputs($file_e,'       if (isset($_POST["columns"])) $columns = $_POST["columns"];'."\n");
            fputs($file_e,'       include "../view/gallery/gallery_'.$classname.'.tpl";'."\n");
            fputs($file_e,'       break;'."\n");
            fputs($file_e,'     case "grid":'."\n");
            fputs($file_e,'       include "../view/gallery_grid_'.$classname.'.tpl";'."\n");
            fputs($file_e,'     break;'."\n");
            fputs($file_e,"   }\n");
            fputs($file_e," }\n");
            fputs($file_e,"?> \n");      
            fclose($file_e);
        
        // combo
        //fputs($f1, "combo_".$classname.".tpl\n");
        $file_tpl = fopen("../view/combo/combo_".$classname.".tpl","w");
        
        fputs($file_tpl,"<?php \n");
        fputComentario($file_tpl,"Combobox of $classname",'Part View of MVC pattern',array(),'',' ');
        fputs($file_tpl,"\n");
        
        fputs($file_tpl,' if (!isset($id)) $id = "combo-'.$classname.'";'."\n");
        fputs($file_tpl,' if (!isset($name)) $name = "combo-'.$classname.'";'."\n");
        fputs($file_tpl,' if (!isset($default)) $default = null;'."\n");
        
        fputs($file_tpl,"\n");
        fputs($file_tpl,' echo getHTMLCombobox($name, $id, $options, "combobox-'.$tabla->name.'", $default, "'.getCrudalTitleOf($tabla->name).'", true);'."\n");
        fputs($file_tpl,"?>");
               
         //evento
            //fputs($f, "get_ui_combo_".$classname.".php\n");
            $file_e = fopen("../control/view/combo/get_ui_combo_".$classname.".php","w");

            fputs($file_e,"<?php \n");
            fputComentario($file_e,"Combobox views of $classname",'Part Control of MVC pattern',array(),'',' ');
            fputs($file_e,"\n");
            
            fputs($file_e,' import ("'.str_replace("/",".",$carpeta).".".$classname.'Model");'."\n");
            fputs($file_e,' import ("'.str_replace("/",".",$carpeta).".".$classname.'");'."\n");
            fputs($file_e,' import ("'.str_replace("/",".",$carpeta).".".$classname.'Collection");'."\n");
           
            fputs($file_e,"\n");
            fputs($file_e,' $collection = new '.$classname.'Collection($dbconnection);'."\n");
            fputs($file_e,' $collection->Load();'."\n");
            fputs($file_e,"\n");
            fputs($file_e,' if (isset($_POST["id"])) $indentity = $_POST["id"];'."\n");
            fputs($file_e,' if (isset($_POST["name"])) $name = $_POST["name"];'."\n");
            fputs($file_e,' if (isset($_POST["default"])) $default = $_POST["default"];'."\n");
            fputs($file_e,"\n");
            fputs($file_e,' $options = array();'."\n");
            
            if (count($tabla->primary_key)<=0) 
                fputs($file_e,' $i = 0;'."\n");
                
            fputs($file_e,' foreach($collection->collection as $item){'."\n");
            if (count($tabla->primary_key)<=0) 
                fputs($file_e,' $i++;'."\n");
            
            $sid = '$i';
            if (count($tabla->primary_key) > 0){
                $sid = '';
                
                $primero = true;
                foreach ($tabla->primary_key as $k){
                    if ($primero == false) $sid .= ".','.";
                	$sid .= '$item->'.$k;
                    $primero = false;
                }
                if (count($tabla->primary_key) > 1) $sid = "'{'.".$sid.".'}'";
            }
            
            $sdesc = '(isset($item->descripcion)?$item->descripcion:"")';
            
            if (isset($table_desc[$tabla->schema.".".$tabla->name])){
                $sdesc = $table_desc[$tabla->schema.".".$tabla->name];
                foreach ($tabla->fields as $campo)
                    $sdesc = str_replace("{".$campo->name."}", '\'.$item->'.$campo->name.'.\'', $sdesc);
                $sdesc = "'$sdesc'";
            }
            
            fputs($file_e,'     $options['.$sid.'] = '.$sdesc.';'."\n");
            fputs($file_e,' }'."\n");
            fputs($file_e,"\n");
            fputs($file_e,' if (isset($_POST["forma"])){ '."\n");
            fputs($file_e,'   switch($_POST["forma"]){'."\n");
            fputs($file_e,'     case "combo":'."\n");
            fputs($file_e,'       include "../view/combo/combo_'.$classname.'.tpl";'."\n");
            fputs($file_e,'       break;'."\n");
            fputs($file_e,"   }\n");
            fputs($file_e," }\n");
            fputs($file_e,"?> \n");      
            fclose($file_e);
        
        //------------------------------------------------
        //eventos
        
        //fputs($f, "add_".$classname.".php\n");
        
        $file_event = fopen("../control/data/add/add_".$classname.".php","w");
        
        fputs($file_event,"<?php \n");
        
        fputs($file_event, "\n");
        
        fputComentario($file_event,"Receptor of $classname data from POST for INSERT",'Part Control of MVC Pattern',array(),'','');
        
        fputs($file_event, 'import("'.dame_paquete_de($classname).'.'.$classname.'Model"); '."\n");
        fputs($file_event, 'import("'.dame_paquete_de($classname).'.'.$classname.'"); '."\n");        
        
        fputs($file_event, "\n");
        fputs($file_event, "// Execute fields's modification data \n");
            
        foreach ($tabla->fields as $campo){
            if ($campo->type != 'replica'){
                $fdm = "fdm_".$classname."_".$campo->name.".php";
                fputs($file_event,'   if (file_exists("../control/data/fdms/'.$fdm.'")) include "../control/data/fdms/'.$fdm.'"; '."\n");
            }
        }
        
        fputs($file_event, "\n");
        fputs($file_event, "// Getting data from POST \n"); 

        foreach ($tabla->fields as $campo)
            if ($campo->type!='replica'){
                
                fputs($file_event,'   $'.$campo->name.' = $_POST["field_'.$classname."_".$campo->name.'"];'."\n");
                
            }
        fputs($file_event, "\n // Construyendo el objeto \n");
        fputs($file_event, '   $obj = new '.$classname.'(');
        $pllaves = ""; $i = 0; foreach ($tabla->primary_key as $llave){$i++; if ($i>1) $pllaves = $pllaves.","; $pllaves = $pllaves.'$'.$llave;}
      
            
        $camposphp = ""; $i = 0; foreach ($tabla->fields as $campo) if ($campo->type!='replica'){ $i++; if ($i>1) $camposphp = $camposphp.","; $camposphp = $camposphp."$".$campo->name;}
        $campsarr = explode (",",$camposphp);
    
        $ya = false;
        $replicas = "";
        $i = 0;
        foreach ($tabla->fields as $campo){
            if ($campo->type == 'replica'){
                $i++;
                if ($ya == true) $replicas.=',';
                $replicas.='""';
                $ya = true;
            }
        }
    
        if ($i==1) $replicas.=",";
        if ($i>=1) $replicas = ",".$replicas;
    
        $p = true;
        foreach ($campsarr as $item){
            if ($p==false) fputs($file_event, ',');
            $p=false;
            fputs($file_event, '$'.substr($item,1));
        }
        fputs($file_event, $replicas.($replicas==""?",":"").'true, null, false);'."\n"); 
       
        fputs($file_event,"?>");   
         
         //--------------------------------------------------------
            //fputs($f, "set_".$classname.".php\n");
            
            $file_event = fopen("../control/data/set/set_".$classname.".php","w");
            
            fputs($file_event,"<?php \n");            
            fputs($file_event, "\n");
            
            fputComentario($file_event, "Receptor of $classname data from POST for UPDATE",'Part Control of MVC Pattern',array(),'','');
            fputs($file_event, "\n");
        	fputs($file_event, 'import("'.dame_paquete_de($classname).'.'.$classname.'Model"); '."\n");
        	fputs($file_event, 'import("'.dame_paquete_de($classname).'.'.$classname.'"); '."\n");        
            fputs($file_event, 'import("'.dame_paquete_de($classname).'.'.$classname.'Collection"); '."\n");
            
            fputs($file_event, "\n");
            fputs($file_event, "// Execute fields's modification data \n");
            
            foreach ($tabla->fields as $campo){
                if ($campo->type != 'replica'){
                    $fdm = "fdm_".$classname."_".$campo->name.".php";
                    fputs($file_event,'if (file_exists("../control/data/fdms/'.$fdm.'")) include "../control/data/fdms/'.$fdm.'"; '."\n");
                }
            }
            
            fputs($file_event, "\n");
            fputs($file_event, "// Getting data from POST \n"); 
            
            // #35: a field that was not posted is PHP null and is left unchanged.
            // It used to be the string "null", which was written into text
            // columns as 'null' (and set booleans to true).
            foreach ($tabla->fields as $campo)
                    fputs($file_event,'   if (isset($_POST["field_'.$classname."_".$campo->name.'"])) $'.$campo->name.' = $_POST["field_'.$classname."_".$campo->name.'"]; else $'.$campo->name.' = null;'."\n");

            fputs($file_event, "\n // Actualizando el objeto \n");
            fputs($file_event, '   $col = new '.$classname.'Collection($dbconnection);'."\n");
            $pllaves = ""; $i = 0; foreach ($tabla->primary_key as $llave){$i++; if ($i>1) $pllaves = $pllaves.","; $pllaves = $pllaves.'$'.$llave;}
            fputs($file_event, '   $obj = $col->getItem('.$pllaves.');'."\n");
                
            foreach ($tabla->fields as $campo){
                if ($campo->type == 'md5'){
                    // #14: password fields change only when a new value was posted;
                    // the "null" placeholder must not become a password (it is hashed)
                    $post = '$_POST["field_'.$classname."_".$campo->name.'"]';
                    fputs($file_event, "// #14: only change the password when a new one was posted; the \"null\"\n");
                    fputs($file_event, "// placeholder above must not become a password (it is hashed now)\n");
                    fputs($file_event, 'if (isset('.$post.') && is_string('.$post.') && '.$post.' !== \'\') $obj->set'.capitalLetter($campo->name).'($'.$campo->name.');'."\n");
                }
                else if ($campo->type != 'replica'){
                    fputs($file_event, 'if ($'.$campo->name.' !== null) $obj->set'.capitalLetter($campo->name).'($'.$campo->name.');'."\n");
                }
            }
          
            fputs($file_event,"?>"); 
        
        //--------------------------------------------------
            //fputs($f, "del_".$classname.".php\n");
            $file_event = fopen("../control/data/del/del_".$classname.".php","w");
            
            fputs($file_event,"<?php \n");
            
            fputs($file_event, "\n");
            
            fputComentario($file_event, "Receptor of $classname identification from POST for DELETE",'Part Control of MVC Pattern',array(),'','');
            
        	fputs($file_event, 'import("'.dame_paquete_de($classname).'.'.$classname.'Model"); '."\n");
        	fputs($file_event, 'import("'.dame_paquete_de($classname).'.'.$classname.'"); '."\n");        
            fputs($file_event, 'import("'.dame_paquete_de($classname).'.'.$classname.'Collection"); '."\n");
            fputs($file_event, "\n");
            
            fputs($file_event, "// Tomando los valores del POST \n");
            
            foreach ($tabla->fields as $campo){
                if ($campo->type != 'replica'){
                    $fdm = "fdm_".$classname."_".$campo->name.".php";
                    fputs($file_event,'   if (file_exists("../control/data/fdms/'.$fdm.'")) include "../control/data/fdms/'.$fdm.'"; '."\n");
                }
            }
             
            foreach ($tabla->fields as $campo)
                    fputs($file_event,'   if (isset($_POST["field_'.$classname."_".$campo->name.'"])) $'.$campo->name.' = $_POST["field_'.$classname."_".$campo->name.'"]; else $'.$campo->name.' = null;'."\n");
                
            
            fputs($file_event, "\n // Eliminando el objeto \n");
            fputs($file_event, '   $col = new '.$classname.'Collection($dbconnection);'."\n");
            $pllaves = ""; $i = 0; foreach ($tabla->primary_key as $llave){$i++; if ($i>1) $pllaves = $pllaves.","; $pllaves = $pllaves.'$'.$llave;}
            fputs($file_event, '   $col->removeItem('.$pllaves.');'."\n");
            
            fputs($file_event,"?>"); 
        
        //--------------------------------------------------
       
    }

?>
