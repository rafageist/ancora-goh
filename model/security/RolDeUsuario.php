<?php 
   class RolDeUsuario{ 
       var $usuario; 
       var $rol; 
       var $data;  
          /** 
            * @name: RolDeUsuario 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($usuario,$rol,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RolDeUsuarioModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($usuario,$rol); 
                    $this->usuario = $usuario; 
                    $this->rol = $rol; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($usuario,$rol); 
                  $this->usuario = $r->usuario;
                  $this->rol = $r->rol;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->usuario = $usuario;
                  $this->rol = $rol;
              }
       } 
          /** 
            * @name: setUsuario 
            * @desc: Set value of property "Usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setUsuario($value){ 
              $this->usuario = $value; 
              $this->data->setBy("usuario = '".$this->usuario."' AND rol = '".$this->rol."'", "usuario = '$value'"); 
       } 
          /** 
            * @name: getUsuario 
            * @desc: Get value of property "Usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getUsuario(){ 
              $r = $this->data->get($this->usuario,$this->rol); 
              $this->usuario = $r->usuario; 
              return $this->usuario;
       } 
          /** 
            * @name: setRol 
            * @desc: Set value of property "Rol" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setRol($value){ 
              $this->rol = $value; 
              $this->data->setBy("usuario = '".$this->usuario."' AND rol = '".$this->rol."'", "rol = '$value'"); 
       } 
          /** 
            * @name: getRol 
            * @desc: Get value of property "Rol" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getRol(){ 
              $r = $this->data->get($this->usuario,$this->rol); 
              $this->rol = $r->rol; 
              return $this->rol;
       } 
} 
?>