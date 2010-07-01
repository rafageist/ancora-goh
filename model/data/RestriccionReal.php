<?php 
   class RestriccionReal{ 
       var $restriccion_nominal; 
       var $periodicidad; 
       var $fecha; 
       var $hora_inicial; 
       var $hora_final; 
       var $por_actividad; 
       var $actividad; 
       var $objeto; 
       var $data;  
          /** 
            * @name: RestriccionReal 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function RestriccionReal($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RestriccionRealModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto); 
                    $this->restriccion_nominal = $restriccion_nominal; 
                    $this->periodicidad = $periodicidad; 
                    $this->fecha = $fecha; 
                    $this->hora_inicial = $hora_inicial; 
                    $this->hora_final = $hora_final; 
                    $this->por_actividad = $por_actividad; 
                    $this->actividad = $actividad; 
                    $this->objeto = $objeto; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($restriccion_nominal,$periodicidad); 
                  $this->restriccion_nominal = $r->restriccion_nominal;
                  $this->periodicidad = $r->periodicidad;
                  $this->fecha = $r->fecha;
                  $this->hora_inicial = $r->hora_inicial;
                  $this->hora_final = $r->hora_final;
                  $this->por_actividad = $r->por_actividad;
                  $this->actividad = $r->actividad;
                  $this->objeto = $r->objeto;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->restriccion_nominal = $restriccion_nominal;
                  $this->periodicidad = $periodicidad;
                  $this->fecha = $fecha;
                  $this->hora_inicial = $hora_inicial;
                  $this->hora_final = $hora_final;
                  $this->por_actividad = $por_actividad;
                  $this->actividad = $actividad;
                  $this->objeto = $objeto;
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
              $this->data->setBy("restriccion_nominal = '".$this->restriccion_nominal."' AND periodicidad = '".$this->periodicidad."'", "restriccion_nominal = '$value'"); 
       } 
          /** 
            * @name: getRestriccionNominal 
            * @desc: Get value of property "RestriccionNominal" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getRestriccionNominal(){ 
              $r = $this->data->get($this->restriccion_nominal,$this->periodicidad); 
              $this->restriccion_nominal = $r->restriccion_nominal; 
              return $this->restriccion_nominal;
       } 
          /** 
            * @name: setPeriodicidad 
            * @desc: Set value of property "Periodicidad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPeriodicidad($value){ 
              $this->periodicidad = $value; 
              $this->data->setBy("restriccion_nominal = '".$this->restriccion_nominal."' AND periodicidad = '".$this->periodicidad."'", "periodicidad = '$value'"); 
       } 
          /** 
            * @name: getPeriodicidad 
            * @desc: Get value of property "Periodicidad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getPeriodicidad(){ 
              $r = $this->data->get($this->restriccion_nominal,$this->periodicidad); 
              $this->periodicidad = $r->periodicidad; 
              return $this->periodicidad;
       } 
          /** 
            * @name: setFecha 
            * @desc: Set value of property "Fecha" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setFecha($value){ 
              $this->fecha = $value; 
              $this->data->setBy("restriccion_nominal = '".$this->restriccion_nominal."' AND periodicidad = '".$this->periodicidad."'", "fecha = $value"); 
       } 
          /** 
            * @name: getFecha 
            * @desc: Get value of property "Fecha" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getFecha(){ 
              $r = $this->data->get($this->restriccion_nominal,$this->periodicidad); 
              $this->fecha = $r->fecha; 
              return $this->fecha;
       } 
          /** 
            * @name: setHoraInicial 
            * @desc: Set value of property "HoraInicial" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setHoraInicial($value){ 
              $this->hora_inicial = $value; 
              $this->data->setBy("restriccion_nominal = '".$this->restriccion_nominal."' AND periodicidad = '".$this->periodicidad."'", "hora_inicial = $value"); 
       } 
          /** 
            * @name: getHoraInicial 
            * @desc: Get value of property "HoraInicial" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getHoraInicial(){ 
              $r = $this->data->get($this->restriccion_nominal,$this->periodicidad); 
              $this->hora_inicial = $r->hora_inicial; 
              return $this->hora_inicial;
       } 
          /** 
            * @name: setHoraFinal 
            * @desc: Set value of property "HoraFinal" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setHoraFinal($value){ 
              $this->hora_final = $value; 
              $this->data->setBy("restriccion_nominal = '".$this->restriccion_nominal."' AND periodicidad = '".$this->periodicidad."'", "hora_final = $value"); 
       } 
          /** 
            * @name: getHoraFinal 
            * @desc: Get value of property "HoraFinal" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getHoraFinal(){ 
              $r = $this->data->get($this->restriccion_nominal,$this->periodicidad); 
              $this->hora_final = $r->hora_final; 
              return $this->hora_final;
       } 
          /** 
            * @name: setPorActividad 
            * @desc: Set value of property "PorActividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPorActividad($value){ 
              $this->por_actividad = $value; 
               if ($value == true || $value == 1 || $value == "t") $value = "true"; 
               else if ($value == true || $value == 0 || $value == "f") $value = "false"; 
              $this->data->setBy("restriccion_nominal = '".$this->restriccion_nominal."' AND periodicidad = '".$this->periodicidad."'", "por_actividad = $value"); 
       } 
          /** 
            * @name: getPorActividad 
            * @desc: Get value of property "PorActividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getPorActividad(){ 
              $r = $this->data->get($this->restriccion_nominal,$this->periodicidad); 
              $this->por_actividad = $r->por_actividad; 
              return $this->por_actividad;
       } 
          /** 
            * @name: setActividad 
            * @desc: Set value of property "Actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setActividad($value){ 
              $this->actividad = $value; 
              $this->data->setBy("restriccion_nominal = '".$this->restriccion_nominal."' AND periodicidad = '".$this->periodicidad."'", "actividad = '$value'"); 
       } 
          /** 
            * @name: getActividad 
            * @desc: Get value of property "Actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getActividad(){ 
              $r = $this->data->get($this->restriccion_nominal,$this->periodicidad); 
              $this->actividad = $r->actividad; 
              return $this->actividad;
       } 
          /** 
            * @name: setObjeto 
            * @desc: Set value of property "Objeto" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setObjeto($value){ 
              $this->objeto = $value; 
              $this->data->setBy("restriccion_nominal = '".$this->restriccion_nominal."' AND periodicidad = '".$this->periodicidad."'", "objeto = '$value'"); 
       } 
          /** 
            * @name: getObjeto 
            * @desc: Get value of property "Objeto" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getObjeto(){ 
              $r = $this->data->get($this->restriccion_nominal,$this->periodicidad); 
              $this->objeto = $r->objeto; 
              return $this->objeto;
       } 
} 
?>