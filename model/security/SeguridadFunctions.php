<?php 
    /** 
      * @name: seguridadFunctions 
      * @desc: Functions of schema seguridad 
      * @updated: 20/06/10 10:40:56 
      * @version: 1.0 
      */ 
     class SeguridadFunctions extends PartModel{ 
        var $db; 
          /** 
            * @name: seguridadFunctions 
            * @desc: Constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function __construct($db){ 
               $this->db = $db;  
          }
          /** 
            * @name: check_host 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CheckHost($pusuario,$pip,$asobjects = false){ 
               $sql = "select  seguridad.check_host($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pusuario, "text"), PartModel::sqlValue($pip, "text")), $asobjects);
           } 
          /** 
            * @name: tiene_permiso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function TienePermiso($pusuario,$ppermiso,$asobjects = false){ 
               $sql = "select  seguridad.tiene_permiso($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pusuario, "text"), PartModel::sqlValue($ppermiso, "text")), $asobjects);
           } 
     } 
