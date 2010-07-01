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
          public function SeguridadFunctions($db){ 
               $this->db = $db;  
          }
          /** 
            * @name: check_host 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CheckHost($pusuario,$pip,$asobjects = false){ 
               $sql = "select  seguridad.check_host('$pusuario','$pip')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: tiene_permiso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function TienePermiso($pusuario,$ppermiso,$asobjects = false){ 
               $sql = "select  seguridad.tiene_permiso('$pusuario','$ppermiso')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
     } 
