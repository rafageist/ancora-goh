<?php 
   class RestriccionRealDeProfesor{ 
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
            * @name: RestriccionRealDeProfesor 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RestriccionRealDeProfesorModel($dbconnection); else $this->data = $data;

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
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setRestriccionNominal($value){ 
              $this->restriccion_nominal = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_real_de_profesor SET restriccion_nominal = $1 WHERE restriccion_nominal = $2 AND periodicidad = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->restriccion_nominal, "text"), PartModel::sqlValue($this->periodicidad, "text"))); 
       } 
          /** 
            * @name: getRestriccionNominal 
            * @desc: Get value of property "RestriccionNominal" 
            * @updated: 20/06/10 10:40:56 
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
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setPeriodicidad($value){ 
              $this->periodicidad = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_real_de_profesor SET periodicidad = $1 WHERE restriccion_nominal = $2 AND periodicidad = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->restriccion_nominal, "text"), PartModel::sqlValue($this->periodicidad, "text"))); 
       } 
          /** 
            * @name: getPeriodicidad 
            * @desc: Get value of property "Periodicidad" 
            * @updated: 20/06/10 10:40:56 
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
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setFecha($value){ 
              $this->fecha = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_real_de_profesor SET fecha = $1 WHERE restriccion_nominal = $2 AND periodicidad = $3;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->restriccion_nominal, "text"), PartModel::sqlValue($this->periodicidad, "text"))); 
       } 
          /** 
            * @name: getFecha 
            * @desc: Get value of property "Fecha" 
            * @updated: 20/06/10 10:40:56 
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
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setHoraInicial($value){ 
              $this->hora_inicial = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_real_de_profesor SET hora_inicial = $1 WHERE restriccion_nominal = $2 AND periodicidad = $3;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->restriccion_nominal, "text"), PartModel::sqlValue($this->periodicidad, "text"))); 
       } 
          /** 
            * @name: getHoraInicial 
            * @desc: Get value of property "HoraInicial" 
            * @updated: 20/06/10 10:40:56 
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
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setHoraFinal($value){ 
              $this->hora_final = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_real_de_profesor SET hora_final = $1 WHERE restriccion_nominal = $2 AND periodicidad = $3;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->restriccion_nominal, "text"), PartModel::sqlValue($this->periodicidad, "text"))); 
       } 
          /** 
            * @name: getHoraFinal 
            * @desc: Get value of property "HoraFinal" 
            * @updated: 20/06/10 10:40:56 
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
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setPorActividad($value){ 
              $this->por_actividad = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_real_de_profesor SET por_actividad = $1 WHERE restriccion_nominal = $2 AND periodicidad = $3;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->restriccion_nominal, "text"), PartModel::sqlValue($this->periodicidad, "text"))); 
       } 
          /** 
            * @name: getPorActividad 
            * @desc: Get value of property "PorActividad" 
            * @updated: 20/06/10 10:40:56 
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
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setActividad($value){ 
              $this->actividad = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_real_de_profesor SET actividad = $1 WHERE restriccion_nominal = $2 AND periodicidad = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->restriccion_nominal, "text"), PartModel::sqlValue($this->periodicidad, "text"))); 
       } 
          /** 
            * @name: getActividad 
            * @desc: Get value of property "Actividad" 
            * @updated: 20/06/10 10:40:56 
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
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setObjeto($value){ 
              $this->objeto = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_real_de_profesor SET objeto = $1 WHERE restriccion_nominal = $2 AND periodicidad = $3;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->restriccion_nominal, "text"), PartModel::sqlValue($this->periodicidad, "text"))); 
       } 
          /** 
            * @name: getObjeto 
            * @desc: Get value of property "Objeto" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getObjeto(){ 
              $r = $this->data->get($this->restriccion_nominal,$this->periodicidad); 
              $this->objeto = $r->objeto; 
              return $this->objeto;
       } 
} 
?>