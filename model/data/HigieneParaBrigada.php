<?php 
   class HigieneParaBrigada{ 
       var $periodo; 
       var $brigada; 
       var $gasto_maximo_diario; 
       var $data;  
          /** 
            * @name: HigieneParaBrigada 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function __construct($periodo,$brigada,$gasto_maximo_diario,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new HigieneParaBrigadaModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($periodo,$brigada,$gasto_maximo_diario); 
                    $this->periodo = $periodo; 
                    $this->brigada = $brigada; 
                    $this->gasto_maximo_diario = $gasto_maximo_diario; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($periodo,$brigada,$gasto_maximo_diario); 
                  $this->periodo = $r->periodo;
                  $this->brigada = $r->brigada;
                  $this->gasto_maximo_diario = $r->gasto_maximo_diario;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->periodo = $periodo;
                  $this->brigada = $brigada;
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
              $this->data->getAnythingParams("UPDATE higiene_para_brigada SET periodo = $1 WHERE periodo = $2 AND brigada = $3 AND gasto_maximo_diario = $4;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->brigada, "text"), PartModel::sqlValue($this->gasto_maximo_diario, "number"))); 
       } 
          /** 
            * @name: getPeriodo 
            * @desc: Get value of property "Periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getPeriodo(){ 
              $r = $this->data->get($this->periodo,$this->brigada,$this->gasto_maximo_diario); 
              $this->periodo = $r->periodo; 
              return $this->periodo;
       } 
          /** 
            * @name: setBrigada 
            * @desc: Set value of property "Brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setBrigada($value){ 
              $this->brigada = $value; 
              $this->data->getAnythingParams("UPDATE higiene_para_brigada SET brigada = $1 WHERE periodo = $2 AND brigada = $3 AND gasto_maximo_diario = $4;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->brigada, "text"), PartModel::sqlValue($this->gasto_maximo_diario, "number"))); 
       } 
          /** 
            * @name: getBrigada 
            * @desc: Get value of property "Brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getBrigada(){ 
              $r = $this->data->get($this->periodo,$this->brigada,$this->gasto_maximo_diario); 
              $this->brigada = $r->brigada; 
              return $this->brigada;
       } 
          /** 
            * @name: setGastoMaximoDiario 
            * @desc: Set value of property "GastoMaximoDiario" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setGastoMaximoDiario($value){ 
              $this->gasto_maximo_diario = $value; 
              $this->data->getAnythingParams("UPDATE higiene_para_brigada SET gasto_maximo_diario = $1 WHERE periodo = $2 AND brigada = $3 AND gasto_maximo_diario = $4;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->brigada, "text"), PartModel::sqlValue($this->gasto_maximo_diario, "number"))); 
       } 
          /** 
            * @name: getGastoMaximoDiario 
            * @desc: Get value of property "GastoMaximoDiario" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getGastoMaximoDiario(){ 
              $r = $this->data->get($this->periodo,$this->brigada,$this->gasto_maximo_diario); 
              $this->gasto_maximo_diario = $r->gasto_maximo_diario; 
              return $this->gasto_maximo_diario;
       } 
} 
?>