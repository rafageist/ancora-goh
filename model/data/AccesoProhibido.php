<?php 
   class AccesoProhibido{ 
       var $grupo; 
       var $lugar; 
       var $data;  
          /** 
            * @name: AccesoProhibido 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function AccesoProhibido($grupo,$lugar,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new AccesoProhibidoModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($grupo,$lugar); 
                    $this->grupo = $grupo; 
                    $this->lugar = $lugar; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($grupo,$lugar); 
                  $this->grupo = $r->grupo;
                  $this->lugar = $r->lugar;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->grupo = $grupo;
                  $this->lugar = $lugar;
              }
       } 
          /** 
            * @name: setGrupo 
            * @desc: Set value of property "Grupo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setGrupo($value){ 
              $this->grupo = $value; 
              $this->data->setBy("grupo = '".$this->grupo."' AND lugar = '".$this->lugar."'", "grupo = '$value'"); 
       } 
          /** 
            * @name: getGrupo 
            * @desc: Get value of property "Grupo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getGrupo(){ 
              $r = $this->data->get($this->grupo,$this->lugar); 
              $this->grupo = $r->grupo; 
              return $this->grupo;
       } 
          /** 
            * @name: setLugar 
            * @desc: Set value of property "Lugar" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setLugar($value){ 
              $this->lugar = $value; 
              $this->data->setBy("grupo = '".$this->grupo."' AND lugar = '".$this->lugar."'", "lugar = '$value'"); 
       } 
          /** 
            * @name: getLugar 
            * @desc: Get value of property "Lugar" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getLugar(){ 
              $r = $this->data->get($this->grupo,$this->lugar); 
              $this->lugar = $r->lugar; 
              return $this->lugar;
       } 
} 
?>