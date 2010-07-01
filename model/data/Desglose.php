<?php 
   class Desglose{ 
       var $id; 
       var $asignatura; 
       var $periodo; 
       var $min_dias; 
       var $max_dias; 
       var $respetar_orden; 
       var $mismo_dia; 
       var $data;  
          /** 
            * @name: Desglose 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function Desglose($asignatura,$periodo,$min_dias,$max_dias,$respetar_orden,$mismo_dia,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new DesgloseModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($asignatura,$periodo,$min_dias,$max_dias,$respetar_orden,$mismo_dia); 
                    $this->id = $replicas["id"]; 
                    $this->asignatura = $asignatura; 
                    $this->periodo = $periodo; 
                    $this->min_dias = $min_dias; 
                    $this->max_dias = $max_dias; 
                    $this->respetar_orden = $respetar_orden; 
                    $this->mismo_dia = $mismo_dia; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->asignatura = $r->asignatura;
                  $this->periodo = $r->periodo;
                  $this->min_dias = $r->min_dias;
                  $this->max_dias = $r->max_dias;
                  $this->respetar_orden = $r->respetar_orden;
                  $this->mismo_dia = $r->mismo_dia;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->asignatura = $asignatura;
                  $this->periodo = $periodo;
                  $this->min_dias = $min_dias;
                  $this->max_dias = $max_dias;
                  $this->respetar_orden = $respetar_orden;
                  $this->mismo_dia = $mismo_dia;
              }
       } 
          /** 
            * @name: getId 
            * @desc: Get value of property "Id" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getId(){ 
              $r = $this->data->get($this->id); 
              $this->id = $r->id; 
              return $this->id;
       } 
          /** 
            * @name: setAsignatura 
            * @desc: Set value of property "Asignatura" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setAsignatura($value){ 
              $this->asignatura = $value; 
              $this->data->setBy("id = '".$this->id."'", "asignatura = '$value'"); 
       } 
          /** 
            * @name: getAsignatura 
            * @desc: Get value of property "Asignatura" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getAsignatura(){ 
              $r = $this->data->get($this->id); 
              $this->asignatura = $r->asignatura; 
              return $this->asignatura;
       } 
          /** 
            * @name: setPeriodo 
            * @desc: Set value of property "Periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPeriodo($value){ 
              $this->periodo = $value; 
              $this->data->setBy("id = '".$this->id."'", "periodo = '$value'"); 
       } 
          /** 
            * @name: getPeriodo 
            * @desc: Get value of property "Periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPeriodo(){ 
              $r = $this->data->get($this->id); 
              $this->periodo = $r->periodo; 
              return $this->periodo;
       } 
          /** 
            * @name: setMinDias 
            * @desc: Set value of property "MinDias" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setMinDias($value){ 
              $this->min_dias = $value; 
              $this->data->setBy("id = '".$this->id."'", "min_dias = $value"); 
       } 
          /** 
            * @name: getMinDias 
            * @desc: Get value of property "MinDias" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getMinDias(){ 
              $r = $this->data->get($this->id); 
              $this->min_dias = $r->min_dias; 
              return $this->min_dias;
       } 
          /** 
            * @name: setMaxDias 
            * @desc: Set value of property "MaxDias" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setMaxDias($value){ 
              $this->max_dias = $value; 
              $this->data->setBy("id = '".$this->id."'", "max_dias = $value"); 
       } 
          /** 
            * @name: getMaxDias 
            * @desc: Get value of property "MaxDias" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getMaxDias(){ 
              $r = $this->data->get($this->id); 
              $this->max_dias = $r->max_dias; 
              return $this->max_dias;
       } 
          /** 
            * @name: setRespetarOrden 
            * @desc: Set value of property "RespetarOrden" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setRespetarOrden($value){ 
              $this->respetar_orden = $value; 
               if ($value == true || $value == 1 || $value == "t") $value = "true"; 
               else if ($value == true || $value == 0 || $value == "f") $value = "false"; 
              $this->data->setBy("id = '".$this->id."'", "respetar_orden = $value"); 
       } 
          /** 
            * @name: getRespetarOrden 
            * @desc: Get value of property "RespetarOrden" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getRespetarOrden(){ 
              $r = $this->data->get($this->id); 
              $this->respetar_orden = $r->respetar_orden; 
              return $this->respetar_orden;
       } 
          /** 
            * @name: setMismoDia 
            * @desc: Set value of property "MismoDia" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setMismoDia($value){ 
              $this->mismo_dia = $value; 
               if ($value == true || $value == 1 || $value == "t") $value = "true"; 
               else if ($value == true || $value == 0 || $value == "f") $value = "false"; 
              $this->data->setBy("id = '".$this->id."'", "mismo_dia = $value"); 
       } 
          /** 
            * @name: getMismoDia 
            * @desc: Get value of property "MismoDia" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getMismoDia(){ 
              $r = $this->data->get($this->id); 
              $this->mismo_dia = $r->mismo_dia; 
              return $this->mismo_dia;
       } 
       public function addPlantillaDeActividad($orden,$clasificacion,$exige_profesor,$exige_lugar){ 
              $desglose = $this->id; 
              $obj = new PlantillaDeActividadClass($desglose,$orden,$clasificacion,$exige_profesor,$exige_lugar,"",true,false); 
              return $obj;
       } 
       public function getPlantillaDeActividad(){ 
          $collection = new PlantillaDeActividadCollection($this->data->db);
          $collection->Load(null,null,"*","desglose = '".$this->id."'");
           return $collection;
       } 
} 
?>