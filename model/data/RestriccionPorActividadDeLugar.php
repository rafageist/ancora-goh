<?php 
   class RestriccionPorActividadDeLugar{ 
       var $restriccion_nominal; 
       var $actividad; 
       var $data;  
          /** 
            * @name: RestriccionPorActividadDeLugar 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function RestriccionPorActividadDeLugar($restriccion_nominal,$actividad,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RestriccionPorActividadDeLugarModel($dbconnection); else $this->data = $data;

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
              $this->data->setBy("restriccion_nominal = '".$this->restriccion_nominal."'", "restriccion_nominal = '$value'"); 
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
              $this->data->setBy("restriccion_nominal = '".$this->restriccion_nominal."'", "actividad = '$value'"); 
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