<?php 
   class OtrosRecursosUsados{ 
       var $otro_recurso; 
       var $actividad; 
       var $creador; 
       var $data;  
          /** 
            * @name: OtrosRecursosUsados 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function __construct($otro_recurso,$actividad,$creador,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new OtrosRecursosUsadosModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($otro_recurso,$actividad,$creador); 
                    $this->otro_recurso = $otro_recurso; 
                    $this->actividad = $actividad; 
                    $this->creador = $creador; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($otro_recurso,$actividad); 
                  $this->otro_recurso = $r->otro_recurso;
                  $this->actividad = $r->actividad;
                  $this->creador = $r->creador;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->otro_recurso = $otro_recurso;
                  $this->actividad = $actividad;
                  $this->creador = $creador;
              }
       } 
          /** 
            * @name: setOtroRecurso 
            * @desc: Set value of property "OtroRecurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setOtroRecurso($value){ 
              $this->otro_recurso = $value; 
              $this->data->setBy("otro_recurso = '".$this->otro_recurso."' AND actividad = '".$this->actividad."'", "otro_recurso = '$value'"); 
       } 
          /** 
            * @name: getOtroRecurso 
            * @desc: Get value of property "OtroRecurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getOtroRecurso(){ 
              $r = $this->data->get($this->otro_recurso,$this->actividad); 
              $this->otro_recurso = $r->otro_recurso; 
              return $this->otro_recurso;
       } 
          /** 
            * @name: setActividad 
            * @desc: Set value of property "Actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setActividad($value){ 
              $this->actividad = $value; 
              $this->data->setBy("otro_recurso = '".$this->otro_recurso."' AND actividad = '".$this->actividad."'", "actividad = '$value'"); 
       } 
          /** 
            * @name: getActividad 
            * @desc: Get value of property "Actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getActividad(){ 
              $r = $this->data->get($this->otro_recurso,$this->actividad); 
              $this->actividad = $r->actividad; 
              return $this->actividad;
       } 
          /** 
            * @name: setCreador 
            * @desc: Set value of property "Creador" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setCreador($value){ 
              $this->creador = $value; 
              $this->data->setBy("otro_recurso = '".$this->otro_recurso."' AND actividad = '".$this->actividad."'", "creador = '$value'"); 
       } 
          /** 
            * @name: getCreador 
            * @desc: Get value of property "Creador" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getCreador(){ 
              $r = $this->data->get($this->otro_recurso,$this->actividad); 
              $this->creador = $r->creador; 
              return $this->creador;
       } 
} 
?>