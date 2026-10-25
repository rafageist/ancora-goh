<?php 
   class Rol{ 
       var $nombre; 
       var $descripcion; 
       var $data;  
          /** 
            * @name: Rol 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($nombre,$descripcion,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RolModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($nombre,$descripcion); 
                    $this->nombre = $nombre; 
                    $this->descripcion = $descripcion; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($nombre); 
                  $this->nombre = $r->nombre;
                  $this->descripcion = $r->descripcion;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->nombre = $nombre;
                  $this->descripcion = $descripcion;
              }
       } 
          /** 
            * @name: setNombre 
            * @desc: Set value of property "Nombre" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setNombre($value){ 
              $this->nombre = $value; 
              $this->data->setBy("nombre = '".$this->nombre."'", "nombre = '$value'"); 
       } 
          /** 
            * @name: getNombre 
            * @desc: Get value of property "Nombre" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getNombre(){ 
              $r = $this->data->get($this->nombre); 
              $this->nombre = $r->nombre; 
              return $this->nombre;
       } 
          /** 
            * @name: setDescripcion 
            * @desc: Set value of property "Descripcion" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setDescripcion($value){ 
              $this->descripcion = $value; 
              $this->data->setBy("nombre = '".$this->nombre."'", "descripcion = '$value'"); 
       } 
          /** 
            * @name: getDescripcion 
            * @desc: Get value of property "Descripcion" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getDescripcion(){ 
              $r = $this->data->get($this->nombre); 
              $this->descripcion = $r->descripcion; 
              return $this->descripcion;
       } 
       public function addSeguridadPermiso($permiso){ 
              $rol = $this->nombre; 
              $obj = new SeguridadPermisoClass($permiso,$rol,true,false); 
              return $obj;
       } 
       public function getSeguridadPermiso(){ 
          $collection = new SeguridadPermisoCollection($this->data->db);
          $collection->Load(null,null,"*","rol = '".$this->nombre."'");
           return $collection;
       } 
       public function addSeguridadRolDeUsuario($usuario){ 
              $rol = $this->nombre; 
              $obj = new SeguridadRolDeUsuarioClass($usuario,$rol,true,false); 
              return $obj;
       } 
       public function getSeguridadRolDeUsuario(){ 
          $collection = new SeguridadRolDeUsuarioCollection($this->data->db);
          $collection->Load(null,null,"*","rol = '".$this->nombre."'");
           return $collection;
       } 
} 
?>