<?php 
   class Permiso{ 
       var $permiso; 
       var $rol; 
       var $data;  
          /** 
            * @name: Permiso 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($permiso,$rol,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new PermisoModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($permiso,$rol); 
                    $this->permiso = $permiso; 
                    $this->rol = $rol; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($permiso,$rol); 
                  $this->permiso = $r->permiso;
                  $this->rol = $r->rol;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->permiso = $permiso;
                  $this->rol = $rol;
              }
       } 
          /** 
            * @name: setPermiso 
            * @desc: Set value of property "Permiso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setPermiso($value){ 
              $this->permiso = $value; 
              $this->data->getAnythingParams("UPDATE seguridad.permiso SET permiso = $1 WHERE permiso = $2 AND rol = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->permiso, "text"), PartModel::sqlValue($this->rol, "text"))); 
       } 
          /** 
            * @name: getPermiso 
            * @desc: Get value of property "Permiso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getPermiso(){ 
              $r = $this->data->get($this->permiso,$this->rol); 
              $this->permiso = $r->permiso; 
              return $this->permiso;
       } 
          /** 
            * @name: setRol 
            * @desc: Set value of property "Rol" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setRol($value){ 
              $this->rol = $value; 
              $this->data->getAnythingParams("UPDATE seguridad.permiso SET rol = $1 WHERE permiso = $2 AND rol = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->permiso, "text"), PartModel::sqlValue($this->rol, "text"))); 
       } 
          /** 
            * @name: getRol 
            * @desc: Get value of property "Rol" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getRol(){ 
              $r = $this->data->get($this->permiso,$this->rol); 
              $this->rol = $r->rol; 
              return $this->rol;
       } 
} 
?>