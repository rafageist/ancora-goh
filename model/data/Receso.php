<?php 
   class Receso{ 
       var $periodo; 
       var $despues_del_turno; 
       var $duracion; 
       var $data;  
          /** 
            * @name: Receso 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function __construct($periodo,$despues_del_turno,$duracion,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RecesoModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($periodo,$despues_del_turno,$duracion); 
                    $this->periodo = $periodo; 
                    $this->despues_del_turno = $despues_del_turno; 
                    $this->duracion = $duracion; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($periodo,$despues_del_turno); 
                  $this->periodo = $r->periodo;
                  $this->despues_del_turno = $r->despues_del_turno;
                  $this->duracion = $r->duracion;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->periodo = $periodo;
                  $this->despues_del_turno = $despues_del_turno;
                  $this->duracion = $duracion;
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
              $this->data->getAnythingParams("UPDATE receso SET periodo = $1 WHERE periodo = $2 AND despues_del_turno = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->despues_del_turno, "number"))); 
       } 
          /** 
            * @name: getPeriodo 
            * @desc: Get value of property "Periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getPeriodo(){ 
              $r = $this->data->get($this->periodo,$this->despues_del_turno); 
              $this->periodo = $r->periodo; 
              return $this->periodo;
       } 
          /** 
            * @name: setDespuesDelTurno 
            * @desc: Set value of property "DespuesDelTurno" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setDespuesDelTurno($value){ 
              $this->despues_del_turno = $value; 
              $this->data->getAnythingParams("UPDATE receso SET despues_del_turno = $1 WHERE periodo = $2 AND despues_del_turno = $3;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->despues_del_turno, "number"))); 
       } 
          /** 
            * @name: getDespuesDelTurno 
            * @desc: Get value of property "DespuesDelTurno" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getDespuesDelTurno(){ 
              $r = $this->data->get($this->periodo,$this->despues_del_turno); 
              $this->despues_del_turno = $r->despues_del_turno; 
              return $this->despues_del_turno;
       } 
          /** 
            * @name: setDuracion 
            * @desc: Set value of property "Duracion" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setDuracion($value){ 
              $this->duracion = $value; 
              $this->data->getAnythingParams("UPDATE receso SET duracion = $1 WHERE periodo = $2 AND despues_del_turno = $3;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->periodo, "text"), PartModel::sqlValue($this->despues_del_turno, "number"))); 
       } 
          /** 
            * @name: getDuracion 
            * @desc: Get value of property "Duracion" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getDuracion(){ 
              $r = $this->data->get($this->periodo,$this->despues_del_turno); 
              $this->duracion = $r->duracion; 
              return $this->duracion;
       } 
} 
?>