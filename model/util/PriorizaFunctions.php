<?php 
    /** 
      * @name: priorizaFunctions 
      * @desc: Functions of schema prioriza 
      * @updated: 20/06/10 10:40:56 
      * @version: 1.0 
      */ 
     class PriorizaFunctions extends PartModel{ 
        var $db; 
          /** 
            * @name: priorizaFunctions 
            * @desc: Constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PriorizaFunctions($db){ 
               $this->db = $db;  
          }
          /** 
            * @name: nivelar 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Nivelar($arr,$criterio,$asobjects = false){ 
               $sql = "select  prioriza.nivelar('$arr',$criterio)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: nivelar_matrix 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function NivelarMatrix($tabla,$criterios,$asobjects = false){ 
               $sql = "select  prioriza.nivelar_matrix('$tabla','$criterios')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
     } 
