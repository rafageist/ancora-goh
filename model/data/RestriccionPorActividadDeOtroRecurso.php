<?php 
   class RestriccionPorActividadDeOtroRecurso{ 
       var $restriccion_nominal; 
       var $actividad; 
       var $data;  
          /** 
            * @name: RestriccionPorActividadDeOtroRecurso 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function __construct($restriccion_nominal,$actividad,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RestriccionPorActividadDeOtroRecursoModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($restriccion_nominal,$actividad); 
                    $this->restriccion_nominal = $restriccion_nominal; 
                    $this->actividad = $actividad; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($restriccion_nominal); 
                  $this->restriccion_nominal = $r->restriccion_nominal;
                  $this->actividad = $r->actividad;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->restriccion_nominal = $restriccion_nominal;
                  $this->actividad = $actividad;
              }
       } 
          /** 
            * @name: setRestriccionNominal 
            * @desc: Set value of property "RestriccionNominal" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setRestriccionNominal($value){ 
              $this->restriccion_nominal = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_por_actividad_de_otro_recurso SET restriccion_nominal = $1 WHERE restriccion_nominal = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->restriccion_nominal, "text"))); 
       } 
          /** 
            * @name: getRestriccionNominal 
            * @desc: Get value of property "RestriccionNominal" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getRestriccionNominal(){ 
              $r = $this->data->get($this->restriccion_nominal); 
              $this->restriccion_nominal = $r->restriccion_nominal; 
              return $this->restriccion_nominal;
       } 
          /** 
            * @name: setActividad 
            * @desc: Set value of property "Actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setActividad($value){ 
              $this->actividad = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_por_actividad_de_otro_recurso SET actividad = $1 WHERE restriccion_nominal = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->restriccion_nominal, "text"))); 
       } 
          /** 
            * @name: getActividad 
            * @desc: Get value of property "Actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getActividad(){ 
              $r = $this->data->get($this->restriccion_nominal); 
              $this->actividad = $r->actividad; 
              return $this->actividad;
       } 
} 
?>