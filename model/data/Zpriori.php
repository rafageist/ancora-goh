<?php 
   class Zpriori{ 
       var $clasificacion; 
       var $periodo; 
       var $dia; 
       var $turno; 
       var $valor; 
       var $data;  
          /** 
            * @name: Zpriori 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($clasificacion,$periodo,$dia,$turno,$valor,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new ZprioriModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($clasificacion,$periodo,$dia,$turno,$valor); 
                    $this->clasificacion = $clasificacion; 
                    $this->periodo = $periodo; 
                    $this->dia = $dia; 
                    $this->turno = $turno; 
                    $this->valor = $valor; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($clasificacion,$periodo,$dia,$turno); 
                  $this->clasificacion = $r->clasificacion;
                  $this->periodo = $r->periodo;
                  $this->dia = $r->dia;
                  $this->turno = $r->turno;
                  $this->valor = $r->valor;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->clasificacion = $clasificacion;
                  $this->periodo = $periodo;
                  $this->dia = $dia;
                  $this->turno = $turno;
                  $this->valor = $valor;
              }
       } 
          /** 
            * @name: setClasificacion 
            * @desc: Set value of property "Clasificacion" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setClasificacion($value){ 
              $this->clasificacion = $value; 
              $this->data->getAnythingParams("UPDATE zpriori SET clasificacion = $1 WHERE clasificacion = $2 AND periodo = $3 AND dia = $4 AND turno = $5;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->clasificacion, "text"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->dia, "number"), PartModel::sqlValue($this->turno, "number"))); 
       } 
          /** 
            * @name: getClasificacion 
            * @desc: Get value of property "Clasificacion" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getClasificacion(){ 
              $r = $this->data->get($this->clasificacion,$this->periodo,$this->dia,$this->turno); 
              $this->clasificacion = $r->clasificacion; 
              return $this->clasificacion;
       } 
          /** 
            * @name: setPeriodo 
            * @desc: Set value of property "Periodo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setPeriodo($value){ 
              $this->periodo = $value; 
              $this->data->getAnythingParams("UPDATE zpriori SET periodo = $1 WHERE clasificacion = $2 AND periodo = $3 AND dia = $4 AND turno = $5;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->clasificacion, "text"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->dia, "number"), PartModel::sqlValue($this->turno, "number"))); 
       } 
          /** 
            * @name: getPeriodo 
            * @desc: Get value of property "Periodo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getPeriodo(){ 
              $r = $this->data->get($this->clasificacion,$this->periodo,$this->dia,$this->turno); 
              $this->periodo = $r->periodo; 
              return $this->periodo;
       } 
          /** 
            * @name: setDia 
            * @desc: Set value of property "Dia" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setDia($value){ 
              $this->dia = $value; 
              $this->data->getAnythingParams("UPDATE zpriori SET dia = $1 WHERE clasificacion = $2 AND periodo = $3 AND dia = $4 AND turno = $5;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->clasificacion, "text"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->dia, "number"), PartModel::sqlValue($this->turno, "number"))); 
       } 
          /** 
            * @name: getDia 
            * @desc: Get value of property "Dia" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getDia(){ 
              $r = $this->data->get($this->clasificacion,$this->periodo,$this->dia,$this->turno); 
              $this->dia = $r->dia; 
              return $this->dia;
       } 
          /** 
            * @name: setTurno 
            * @desc: Set value of property "Turno" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setTurno($value){ 
              $this->turno = $value; 
              $this->data->getAnythingParams("UPDATE zpriori SET turno = $1 WHERE clasificacion = $2 AND periodo = $3 AND dia = $4 AND turno = $5;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->clasificacion, "text"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->dia, "number"), PartModel::sqlValue($this->turno, "number"))); 
       } 
          /** 
            * @name: getTurno 
            * @desc: Get value of property "Turno" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getTurno(){ 
              $r = $this->data->get($this->clasificacion,$this->periodo,$this->dia,$this->turno); 
              $this->turno = $r->turno; 
              return $this->turno;
       } 
          /** 
            * @name: setValor 
            * @desc: Set value of property "Valor" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setValor($value){ 
              $this->valor = $value; 
              $this->data->getAnythingParams("UPDATE zpriori SET valor = $1 WHERE clasificacion = $2 AND periodo = $3 AND dia = $4 AND turno = $5;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->clasificacion, "text"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->dia, "number"), PartModel::sqlValue($this->turno, "number"))); 
       } 
          /** 
            * @name: getValor 
            * @desc: Get value of property "Valor" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getValor(){ 
              $r = $this->data->get($this->clasificacion,$this->periodo,$this->dia,$this->turno); 
              $this->valor = $r->valor; 
              return $this->valor;
       } 
} 
?>