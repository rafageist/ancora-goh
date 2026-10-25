<?php 
   class HigieneParaProfesor{ 
       var $periodo; 
       var $profesor; 
       var $gasto_maximo_diario; 
       var $data;  
          /** 
            * @name: HigieneParaProfesor 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function __construct($periodo,$profesor,$gasto_maximo_diario,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new HigieneParaProfesorModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($periodo,$profesor,$gasto_maximo_diario); 
                    $this->periodo = $periodo; 
                    $this->profesor = $profesor; 
                    $this->gasto_maximo_diario = $gasto_maximo_diario; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($periodo,$profesor,$gasto_maximo_diario); 
                  $this->periodo = $r->periodo;
                  $this->profesor = $r->profesor;
                  $this->gasto_maximo_diario = $r->gasto_maximo_diario;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->periodo = $periodo;
                  $this->profesor = $profesor;
                  $this->gasto_maximo_diario = $gasto_maximo_diario;
              }
       } 
          /** 
            * @name: setPeriodo 
            * @desc: Set value of property "Periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPeriodo($value){ 
              $this->periodo = $value; 
              $this->data->getAnythingParams("UPDATE higiene_para_profesor SET periodo = $1 WHERE periodo = $2 AND profesor = $3 AND gasto_maximo_diario = $4;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->profesor, "text"), PartModel::sqlValue($this->gasto_maximo_diario, "number"))); 
       } 
          /** 
            * @name: getPeriodo 
            * @desc: Get value of property "Periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getPeriodo(){ 
              $r = $this->data->get($this->periodo,$this->profesor,$this->gasto_maximo_diario); 
              $this->periodo = $r->periodo; 
              return $this->periodo;
       } 
          /** 
            * @name: setProfesor 
            * @desc: Set value of property "Profesor" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setProfesor($value){ 
              $this->profesor = $value; 
              $this->data->getAnythingParams("UPDATE higiene_para_profesor SET profesor = $1 WHERE periodo = $2 AND profesor = $3 AND gasto_maximo_diario = $4;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->profesor, "text"), PartModel::sqlValue($this->gasto_maximo_diario, "number"))); 
       } 
          /** 
            * @name: getProfesor 
            * @desc: Get value of property "Profesor" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getProfesor(){ 
              $r = $this->data->get($this->periodo,$this->profesor,$this->gasto_maximo_diario); 
              $this->profesor = $r->profesor; 
              return $this->profesor;
       } 
          /** 
            * @name: setGastoMaximoDiario 
            * @desc: Set value of property "GastoMaximoDiario" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setGastoMaximoDiario($value){ 
              $this->gasto_maximo_diario = $value; 
              $this->data->getAnythingParams("UPDATE higiene_para_profesor SET gasto_maximo_diario = $1 WHERE periodo = $2 AND profesor = $3 AND gasto_maximo_diario = $4;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->profesor, "text"), PartModel::sqlValue($this->gasto_maximo_diario, "number"))); 
       } 
          /** 
            * @name: getGastoMaximoDiario 
            * @desc: Get value of property "GastoMaximoDiario" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getGastoMaximoDiario(){ 
              $r = $this->data->get($this->periodo,$this->profesor,$this->gasto_maximo_diario); 
              $this->gasto_maximo_diario = $r->gasto_maximo_diario; 
              return $this->gasto_maximo_diario;
       } 
} 
?>