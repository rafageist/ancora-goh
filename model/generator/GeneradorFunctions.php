<?php 
    /** 
      * @name: generadorFunctions 
      * @desc: Functions of schema generador 
      * @updated: 20/06/10 10:40:56 
      * @version: 1.0 
      */ 
     class GeneradorFunctions extends PartModel{ 
        var $db; 
          /** 
            * @name: generadorFunctions 
            * @desc: Constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function __construct($db){ 
               $this->db = $db;  
          }
          /** 
            * @name: fue_cancelado 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function FueCancelado($idesp,$idnivel,$idperiodo,$asobjects = false){ 
               $sql = "select  generador.fue_cancelado($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idesp, "text"), PartModel::sqlValue($idnivel, "text"), PartModel::sqlValue($idperiodo, "text")), $asobjects);
           } 
     } 
