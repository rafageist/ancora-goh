<?php
class Services_XML{

    public function arraytoxml($row){
        $xml='';
        if (is_array($row)){
            foreach ($row as $key=>$value){
                $xml=$xml."<$key>".arraytoxml($row[$key])."</$key>";
            }
        }
        else 
            $xml=$xml.$row;
        return $xml;
    }
    public function encode($var,$encoding="utf8"){
        echo 'entro';
        $xml = '<?xml encoding="'.$encoding.'" version = 1.0?>
                <data>
                '.arraytoxml($var).'
                </data>';
        
        return $xml;
        
    }
}
?>