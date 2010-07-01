<?php 
/** 
  * @name: PartModel 
  * @desc: Father of other class model 
  * @updated: 20/06/10 10:40:54 
  * @version: 1.0 
  */ 
   class PartModel { 
 
          var $db; 
 
          public function PartModel($db){ 
                $this->db = $db;  
          } 
          /**
            * @name getAnything
            * @descrip Get anything SQL query
            */

             public function getAnything($sql,$asobjects=false){
               if ($asobjects==true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
          } 

          /**
            * @name getWHEREPartOfSearchFrase
            * @descrip Construct WHERE part of SQL query from phrase ...
            */

        function getWHEREPartOfSearchFrase($frase,$fields){
           $arr = explode(",",$fields); $words=explode(' ',trim($frase));
            $parteand =' TRUE ';
            foreach($arr as $field) foreach($words as $word){
                if (substr($word,0,1)=='"' || substr($word,strlen($word),1)=='"') {
                     $word=str_replace('"','',$word);
                     $parteand=$parteand.' AND '.$field." LIKE '%".$word."%' ";
                }
             }
             $parteor =' AND (TRUE';

            foreach($arr as $field) foreach($words as $word){
                if (!(substr($word,0,1)=='"' || substr($word,strlen($word),1)=='"')) {
                     $parteor=$parteor.' OR '.$field." LIKE '%".$word."%' ";
                }
            }

            $parteor = $parteor.')';
            return $parteand.$parteor;
        }

          /**
            * @name getAllWithPhraseIn
            * @descrip Get all records from table that cotains this phrase...
            */

          public function getAllWithPhraseIn($table,$fields,$phrase,$extrawhere="true", $showfields="*",$asobjects=false){
              return $this-getAnything("SELECT $showfields FROM $table WHERE (".$this->getWHEREPartOfSearchFrase($fields).") AND ($extrawhere);");
          }
}
 ?> 
