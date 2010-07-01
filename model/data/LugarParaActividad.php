<?php 
   class LugarParaActividad{ 
       var $plantilla; 
       var $lugar; 
       var $prioridad; 
       var $data;  
          /** 
            * @name: LugarParaActividad 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function LugarParaActividad($plantilla,$lugar,$prioridad,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new LugarParaActividadModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($plantilla,$lugar,$prioridad); 
                    $this->plantilla = $plantilla; 
                    $this->lugar = $lugar; 
                    $this->prioridad = $prioridad; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($plantilla,$lugar); 
                  $this->plantilla = $r->plantilla;
                  $this->lugar = $r->lugar;
                  $this->prioridad = $r->prioridad;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->plantilla = $plantilla;
                  $this->lugar = $lugar;
                  $this->prioridad = $prioridad;
              }
       } 
          /** 
            * @name: setPlantilla 
            * @desc: Set value of property "Plantilla" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setPlantilla($value){ 
              $this->plantilla = $value; 
              $this->data->setBy("plantilla = '".$this->plantilla."' AND lugar = '".$this->lugar."'", "plantilla = '$value'"); 
       } 
          /** 
            * @name: getPlantilla 
            * @desc: Get value of property "Plantilla" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getPlantilla(){ 
              $r = $this->data->get($this->plantilla,$this->lugar); 
              $this->plantilla = $r->plantilla; 
              return $this->plantilla;
       } 
          /** 
            * @name: setLugar 
            * @desc: Set value of property "Lugar" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setLugar($value){ 
              $this->lugar = $value; 
              $this->data->setBy("plantilla = '".$this->plantilla."' AND lugar = '".$this->lugar."'", "lugar = '$value'"); 
       } 
          /** 
            * @name: getLugar 
            * @desc: Get value of property "Lugar" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getLugar(){ 
              $r = $this->data->get($this->plantilla,$this->lugar); 
              $this->lugar = $r->lugar; 
              return $this->lugar;
       } 
          /** 
            * @name: setPrioridad 
            * @desc: Set value of property "Prioridad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setPrioridad($value){ 
              $this->prioridad = $value; 
              $this->data->setBy("plantilla = '".$this->plantilla."' AND lugar = '".$this->lugar."'", "prioridad = $value"); 
       } 
          /** 
            * @name: getPrioridad 
            * @desc: Get value of property "Prioridad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getPrioridad(){ 
              $r = $this->data->get($this->plantilla,$this->lugar); 
              $this->prioridad = $r->prioridad; 
              return $this->prioridad;
       } 
} 
?>