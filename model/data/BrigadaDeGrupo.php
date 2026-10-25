<?php 
   class BrigadaDeGrupo{ 
       var $brigada; 
       var $grupo; 
       var $data;  
          /** 
            * @name: BrigadaDeGrupo 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function __construct($brigada,$grupo,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new BrigadaDeGrupoModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($brigada,$grupo); 
                    $this->brigada = $brigada; 
                    $this->grupo = $grupo; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($brigada,$grupo); 
                  $this->brigada = $r->brigada;
                  $this->grupo = $r->grupo;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->brigada = $brigada;
                  $this->grupo = $grupo;
              }
       } 
          /** 
            * @name: setBrigada 
            * @desc: Set value of property "Brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setBrigada($value){ 
              $this->brigada = $value; 
              $this->data->getAnythingParams("UPDATE brigada_de_grupo SET brigada = $1 WHERE brigada = $2 AND grupo = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->brigada, "text"), PartModel::sqlValue($this->grupo, "text"))); 
       } 
          /** 
            * @name: getBrigada 
            * @desc: Get value of property "Brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getBrigada(){ 
              $r = $this->data->get($this->brigada,$this->grupo); 
              $this->brigada = $r->brigada; 
              return $this->brigada;
       } 
          /** 
            * @name: setGrupo 
            * @desc: Set value of property "Grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setGrupo($value){ 
              $this->grupo = $value; 
              $this->data->getAnythingParams("UPDATE brigada_de_grupo SET grupo = $1 WHERE brigada = $2 AND grupo = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->brigada, "text"), PartModel::sqlValue($this->grupo, "text"))); 
       } 
          /** 
            * @name: getGrupo 
            * @desc: Get value of property "Grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getGrupo(){ 
              $r = $this->data->get($this->brigada,$this->grupo); 
              $this->grupo = $r->grupo; 
              return $this->grupo;
       } 
} 
?>