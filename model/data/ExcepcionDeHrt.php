<?php 
   class ExcepcionDeHrt{ 
       var $herencia; 
       var $periodo; 
       var $dia; 
       var $turno; 
       var $data;  
          /** 
            * @name: ExcepcionDeHrt 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function ExcepcionDeHrt($herencia,$periodo,$dia,$turno,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new ExcepcionDeHrtModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($herencia,$periodo,$dia,$turno); 
                    $this->herencia = $herencia; 
                    $this->periodo = $periodo; 
                    $this->dia = $dia; 
                    $this->turno = $turno; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($herencia,$periodo,$dia,$turno); 
                  $this->herencia = $r->herencia;
                  $this->periodo = $r->periodo;
                  $this->dia = $r->dia;
                  $this->turno = $r->turno;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->herencia = $herencia;
                  $this->periodo = $periodo;
                  $this->dia = $dia;
                  $this->turno = $turno;
              }
       } 
          /** 
            * @name: setHerencia 
            * @desc: Set value of property "Herencia" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setHerencia($value){ 
              $this->herencia = $value; 
              $this->data->setBy("herencia = '".$this->herencia."' AND periodo = '".$this->periodo."' AND dia = ".$this->dia." AND turno = ".$this->turno."", "herencia = '$value'"); 
       } 
          /** 
            * @name: getHerencia 
            * @desc: Get value of property "Herencia" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getHerencia(){ 
              $r = $this->data->get($this->herencia,$this->periodo,$this->dia,$this->turno); 
              $this->herencia = $r->herencia; 
              return $this->herencia;
       } 
          /** 
            * @name: setPeriodo 
            * @desc: Set value of property "Periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPeriodo($value){ 
              $this->periodo = $value; 
              $this->data->setBy("herencia = '".$this->herencia."' AND periodo = '".$this->periodo."' AND dia = ".$this->dia." AND turno = ".$this->turno."", "periodo = '$value'"); 
       } 
          /** 
            * @name: getPeriodo 
            * @desc: Get value of property "Periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPeriodo(){ 
              $r = $this->data->get($this->herencia,$this->periodo,$this->dia,$this->turno); 
              $this->periodo = $r->periodo; 
              return $this->periodo;
       } 
          /** 
            * @name: setDia 
            * @desc: Set value of property "Dia" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDia($value){ 
              $this->dia = $value; 
              $this->data->setBy("herencia = '".$this->herencia."' AND periodo = '".$this->periodo."' AND dia = ".$this->dia." AND turno = ".$this->turno."", "dia = $value"); 
       } 
          /** 
            * @name: getDia 
            * @desc: Get value of property "Dia" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDia(){ 
              $r = $this->data->get($this->herencia,$this->periodo,$this->dia,$this->turno); 
              $this->dia = $r->dia; 
              return $this->dia;
       } 
          /** 
            * @name: setTurno 
            * @desc: Set value of property "Turno" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setTurno($value){ 
              $this->turno = $value; 
              $this->data->setBy("herencia = '".$this->herencia."' AND periodo = '".$this->periodo."' AND dia = ".$this->dia." AND turno = ".$this->turno."", "turno = $value"); 
       } 
          /** 
            * @name: getTurno 
            * @desc: Get value of property "Turno" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getTurno(){ 
              $r = $this->data->get($this->herencia,$this->periodo,$this->dia,$this->turno); 
              $this->turno = $r->turno; 
              return $this->turno;
       } 
} 
?>