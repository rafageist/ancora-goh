<?php 
   class RestriccionDeAsignatura{ 
       var $id; 
       var $asignatura; 
       var $periodo; 
       var $dia; 
       var $turno; 
       var $valor; 
       var $data;  
          /** 
            * @name: RestriccionDeAsignatura 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function RestriccionDeAsignatura($asignatura,$periodo,$dia,$turno,$valor,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RestriccionDeAsignaturaModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($asignatura,$periodo,$dia,$turno,$valor); 
                    $this->id = $replicas["id"]; 
                    $this->asignatura = $asignatura; 
                    $this->periodo = $periodo; 
                    $this->dia = $dia; 
                    $this->turno = $turno; 
                    $this->valor = $valor; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->asignatura = $r->asignatura;
                  $this->periodo = $r->periodo;
                  $this->dia = $r->dia;
                  $this->turno = $r->turno;
                  $this->valor = $r->valor;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->asignatura = $asignatura;
                  $this->periodo = $periodo;
                  $this->dia = $dia;
                  $this->turno = $turno;
                  $this->valor = $valor;
              }
       } 
          /** 
            * @name: getId 
            * @desc: Get value of property "Id" 
            * @updated: 20/06/10 10:40:55 
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
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setAsignatura($value){ 
              $this->asignatura = $value; 
              $this->data->setBy("id = '".$this->id."'", "asignatura = '$value'"); 
       } 
          /** 
            * @name: getAsignatura 
            * @desc: Get value of property "Asignatura" 
            * @updated: 20/06/10 10:40:55 
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
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPeriodo($value){ 
              $this->periodo = $value; 
              $this->data->setBy("id = '".$this->id."'", "periodo = '$value'"); 
       } 
          /** 
            * @name: getPeriodo 
            * @desc: Get value of property "Periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getPeriodo(){ 
              $r = $this->data->get($this->id); 
              $this->periodo = $r->periodo; 
              return $this->periodo;
       } 
          /** 
            * @name: setDia 
            * @desc: Set value of property "Dia" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setDia($value){ 
              $this->dia = $value; 
              $this->data->setBy("id = '".$this->id."'", "dia = $value"); 
       } 
          /** 
            * @name: getDia 
            * @desc: Get value of property "Dia" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getDia(){ 
              $r = $this->data->get($this->id); 
              $this->dia = $r->dia; 
              return $this->dia;
       } 
          /** 
            * @name: setTurno 
            * @desc: Set value of property "Turno" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setTurno($value){ 
              $this->turno = $value; 
              $this->data->setBy("id = '".$this->id."'", "turno = $value"); 
       } 
          /** 
            * @name: getTurno 
            * @desc: Get value of property "Turno" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getTurno(){ 
              $r = $this->data->get($this->id); 
              $this->turno = $r->turno; 
              return $this->turno;
       } 
          /** 
            * @name: setValor 
            * @desc: Set value of property "Valor" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setValor($value){ 
              $this->valor = $value; 
              $this->data->setBy("id = '".$this->id."'", "valor = $value"); 
       } 
          /** 
            * @name: getValor 
            * @desc: Get value of property "Valor" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getValor(){ 
              $r = $this->data->get($this->id); 
              $this->valor = $r->valor; 
              return $this->valor;
       } 
       public function addRestriccionRealDeAsignatura($periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $restriccion_nominal = $this->id; 
              $obj = new RestriccionRealDeAsignaturaClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeAsignatura(){ 
          $collection = new RestriccionRealDeAsignaturaCollection($this->data->db);
          $collection->Load(null,null,"*","restriccion_nominal = '".$this->id."'");
           return $collection;
       } 
} 
?>