<?php 
   class LugarFijo{ 
       var $grupo; 
       var $lugar; 
       var $data;  
          /** 
            * @name: LugarFijo 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function __construct($grupo,$lugar,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new LugarFijoModel($dbconnection); else $this->data = $data;

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
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setGrupo($value){ 
              $this->grupo = $value; 
              $this->data->getAnythingParams("UPDATE lugar_fijo SET grupo = $1 WHERE grupo = $2 AND lugar = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->grupo, "text"), PartModel::sqlValue($this->lugar, "text"))); 
       } 
          /** 
            * @name: getGrupo 
            * @desc: Get value of property "Grupo" 
            * @updated: 20/06/10 10:40:55 
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
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setLugar($value){ 
              $this->lugar = $value; 
              $this->data->getAnythingParams("UPDATE lugar_fijo SET lugar = $1 WHERE grupo = $2 AND lugar = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->grupo, "text"), PartModel::sqlValue($this->lugar, "text"))); 
       } 
          /** 
            * @name: getLugar 
            * @desc: Get value of property "Lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getLugar(){ 
              $r = $this->data->get($this->grupo,$this->lugar); 
              $this->lugar = $r->lugar; 
              return $this->lugar;
       } 
} 
?>