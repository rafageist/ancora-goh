<?php

class DirectoryUtil{

    public static function getFilesWithExtension($path,$ext){
        $arr = scandir($path);
        
        $result = array();
        
        foreach ($arr as $dir){
            if ($dir != ".." && $dir != "."){
                if (substr($dir, 0 - (strlen($ext) + 1)) == ".$ext")
                    $result[] = $dir;
            }
        }
        
        return $result;
    }
    
}


?>