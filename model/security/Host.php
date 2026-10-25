<?php 
   class Host{ 
       var $ip; 
       var $usuario; 
       var $data;  
          /** 
            * @name: Host 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($ip,$usuario,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new HostModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($ip,$usuario); 
                    $this->ip = $ip; 
                    $this->usuario = $usuario; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($usuario,$ip); 
                  $this->ip = $r->ip;
                  $this->usuario = $r->usuario;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->ip = $ip;
                  $this->usuario = $usuario;
              }
       } 
          /** 
            * @name: setIp 
            * @desc: Set value of property "Ip" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setIp($value){ 
              $this->ip = $value; 
              $this->data->getAnythingParams("UPDATE seguridad.host SET ip = $1 WHERE usuario = $2 AND ip = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->usuario, "text"), PartModel::sqlValue($this->ip, "text"))); 
       } 
          /** 
            * @name: getIp 
            * @desc: Get value of property "Ip" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getIp(){ 
              $r = $this->data->get($this->usuario,$this->ip); 
              $this->ip = $r->ip; 
              return $this->ip;
       } 
          /** 
            * @name: setUsuario 
            * @desc: Set value of property "Usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setUsuario($value){ 
              $this->usuario = $value; 
              $this->data->getAnythingParams("UPDATE seguridad.host SET usuario = $1 WHERE usuario = $2 AND ip = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->usuario, "text"), PartModel::sqlValue($this->ip, "text"))); 
       } 
          /** 
            * @name: getUsuario 
            * @desc: Get value of property "Usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getUsuario(){ 
              $r = $this->data->get($this->usuario,$this->ip); 
              $this->usuario = $r->usuario; 
              return $this->usuario;
       } 
} 
?>