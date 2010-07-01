<?php 
   class Traza{ 
       var $fecha; 
       var $usuario; 
       var $accion; 
       var $ip; 
       var $data;  
          /** 
            * @name: Traza 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function Traza($fecha,$usuario,$accion,$ip,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new TrazaModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($fecha,$usuario,$accion,$ip); 
                    $this->fecha = $fecha; 
                    $this->usuario = $usuario; 
                    $this->accion = $accion; 
                    $this->ip = $ip; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get(); 
                  $this->fecha = $r->fecha;
                  $this->usuario = $r->usuario;
                  $this->accion = $r->accion;
                  $this->ip = $r->ip;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->fecha = $fecha;
                  $this->usuario = $usuario;
                  $this->accion = $accion;
                  $this->ip = $ip;
              }
       } 
          /** 
            * @name: setFecha 
            * @desc: Set value of property "Fecha" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setFecha($value){ 
              $this->fecha = $value; 
              $this->data->setBy("", "fecha = '$value'"); 
       } 
          /** 
            * @name: getFecha 
            * @desc: Get value of property "Fecha" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getFecha(){ 
              $r = $this->data->get(); 
              $this->fecha = $r->fecha; 
              return $this->fecha;
       } 
          /** 
            * @name: setUsuario 
            * @desc: Set value of property "Usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setUsuario($value){ 
              $this->usuario = $value; 
              $this->data->setBy("", "usuario = '$value'"); 
       } 
          /** 
            * @name: getUsuario 
            * @desc: Get value of property "Usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getUsuario(){ 
              $r = $this->data->get(); 
              $this->usuario = $r->usuario; 
              return $this->usuario;
       } 
          /** 
            * @name: setAccion 
            * @desc: Set value of property "Accion" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setAccion($value){ 
              $this->accion = $value; 
              $this->data->setBy("", "accion = '$value'"); 
       } 
          /** 
            * @name: getAccion 
            * @desc: Get value of property "Accion" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getAccion(){ 
              $r = $this->data->get(); 
              $this->accion = $r->accion; 
              return $this->accion;
       } 
          /** 
            * @name: setIp 
            * @desc: Set value of property "Ip" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setIp($value){ 
              $this->ip = $value; 
              $this->data->setBy("", "ip = '$value'"); 
       } 
          /** 
            * @name: getIp 
            * @desc: Get value of property "Ip" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getIp(){ 
              $r = $this->data->get(); 
              $this->ip = $r->ip; 
              return $this->ip;
       } 
} 
?>