<?php 
   class CancelarGenerador{ 
       var $especialidad; 
       var $nivel; 
       var $periodo; 
       var $data;  
          /** 
            * @name: CancelarGenerador 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function __construct($especialidad,$nivel,$periodo,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new CancelarGeneradorModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($especialidad,$nivel,$periodo); 
                    $this->especialidad = $especialidad; 
                    $this->nivel = $nivel; 
                    $this->periodo = $periodo; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($especialidad,$nivel,$periodo); 
                  $this->especialidad = $r->especialidad;
                  $this->nivel = $r->nivel;
                  $this->periodo = $r->periodo;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->especialidad = $especialidad;
                  $this->nivel = $nivel;
                  $this->periodo = $periodo;
              }
       } 
          /** 
            * @name: setEspecialidad 
            * @desc: Set value of property "Especialidad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setEspecialidad($value){ 
              $this->especialidad = $value; 
              $this->data->setBy("especialidad = '".$this->especialidad."' AND nivel = '".$this->nivel."' AND periodo = '".$this->periodo."'", "especialidad = '$value'"); 
       } 
          /** 
            * @name: getEspecialidad 
            * @desc: Get value of property "Especialidad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getEspecialidad(){ 
              $r = $this->data->get($this->especialidad,$this->nivel,$this->periodo); 
              $this->especialidad = $r->especialidad; 
              return $this->especialidad;
       } 
          /** 
            * @name: setNivel 
            * @desc: Set value of property "Nivel" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setNivel($value){ 
              $this->nivel = $value; 
              $this->data->setBy("especialidad = '".$this->especialidad."' AND nivel = '".$this->nivel."' AND periodo = '".$this->periodo."'", "nivel = '$value'"); 
       } 
          /** 
            * @name: getNivel 
            * @desc: Get value of property "Nivel" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getNivel(){ 
              $r = $this->data->get($this->especialidad,$this->nivel,$this->periodo); 
              $this->nivel = $r->nivel; 
              return $this->nivel;
       } 
          /** 
            * @name: setPeriodo 
            * @desc: Set value of property "Periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPeriodo($value){ 
              $this->periodo = $value; 
              $this->data->setBy("especialidad = '".$this->especialidad."' AND nivel = '".$this->nivel."' AND periodo = '".$this->periodo."'", "periodo = '$value'"); 
       } 
          /** 
            * @name: getPeriodo 
            * @desc: Get value of property "Periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPeriodo(){ 
              $r = $this->data->get($this->especialidad,$this->nivel,$this->periodo); 
              $this->periodo = $r->periodo; 
              return $this->periodo;
       } 
} 
?>