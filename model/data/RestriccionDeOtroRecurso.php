<?php 
   class RestriccionDeOtroRecurso{ 
       var $id; 
       var $otro_recurso; 
       var $periodo; 
       var $dia; 
       var $turno; 
       var $valor; 
       var $por_actividad; 
       var $actividad; 
       var $data;  
          /** 
            * @name: RestriccionDeOtroRecurso 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($otro_recurso,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RestriccionDeOtroRecursoModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($otro_recurso,$periodo,$dia,$turno,$valor,$por_actividad,$actividad); 
                    $this->id = $replicas["id"]; 
                    $this->otro_recurso = $otro_recurso; 
                    $this->periodo = $periodo; 
                    $this->dia = $dia; 
                    $this->turno = $turno; 
                    $this->valor = $valor; 
                    $this->por_actividad = $por_actividad; 
                    $this->actividad = $actividad; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->otro_recurso = $r->otro_recurso;
                  $this->periodo = $r->periodo;
                  $this->dia = $r->dia;
                  $this->turno = $r->turno;
                  $this->valor = $r->valor;
                  $this->por_actividad = $r->por_actividad;
                  $this->actividad = $r->actividad;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->otro_recurso = $otro_recurso;
                  $this->periodo = $periodo;
                  $this->dia = $dia;
                  $this->turno = $turno;
                  $this->valor = $valor;
                  $this->por_actividad = $por_actividad;
                  $this->actividad = $actividad;
              }
       } 
          /** 
            * @name: getId 
            * @desc: Get value of property "Id" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getId(){ 
              $r = $this->data->get($this->id); 
              $this->id = $r->id; 
              return $this->id;
       } 
          /** 
            * @name: setOtroRecurso 
            * @desc: Set value of property "OtroRecurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setOtroRecurso($value){ 
              $this->otro_recurso = $value; 
              $this->data->setBy("id = '".$this->id."'", "otro_recurso = '$value'"); 
       } 
          /** 
            * @name: getOtroRecurso 
            * @desc: Get value of property "OtroRecurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getOtroRecurso(){ 
              $r = $this->data->get($this->id); 
              $this->otro_recurso = $r->otro_recurso; 
              return $this->otro_recurso;
       } 
          /** 
            * @name: setPeriodo 
            * @desc: Set value of property "Periodo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setPeriodo($value){ 
              $this->periodo = $value; 
              $this->data->setBy("id = '".$this->id."'", "periodo = '$value'"); 
       } 
          /** 
            * @name: getPeriodo 
            * @desc: Get value of property "Periodo" 
            * @updated: 20/06/10 10:40:56 
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
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setDia($value){ 
              $this->dia = $value; 
              $this->data->setBy("id = '".$this->id."'", "dia = $value"); 
       } 
          /** 
            * @name: getDia 
            * @desc: Get value of property "Dia" 
            * @updated: 20/06/10 10:40:56 
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
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setTurno($value){ 
              $this->turno = $value; 
              $this->data->setBy("id = '".$this->id."'", "turno = $value"); 
       } 
          /** 
            * @name: getTurno 
            * @desc: Get value of property "Turno" 
            * @updated: 20/06/10 10:40:56 
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
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setValor($value){ 
              $this->valor = $value; 
              $this->data->setBy("id = '".$this->id."'", "valor = $value"); 
       } 
          /** 
            * @name: getValor 
            * @desc: Get value of property "Valor" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getValor(){ 
              $r = $this->data->get($this->id); 
              $this->valor = $r->valor; 
              return $this->valor;
       } 
          /** 
            * @name: setPorActividad 
            * @desc: Set value of property "PorActividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setPorActividad($value){ 
              $this->por_actividad = $value; 
               if ($value == true || $value == 1 || $value == "t") $value = "true"; 
               else if ($value == true || $value == 0 || $value == "f") $value = "false"; 
              $this->data->setBy("id = '".$this->id."'", "por_actividad = $value"); 
       } 
          /** 
            * @name: getPorActividad 
            * @desc: Get value of property "PorActividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getPorActividad(){ 
              $r = $this->data->get($this->id); 
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
              $this->data->setBy("id = '".$this->id."'", "actividad = '$value'"); 
       } 
          /** 
            * @name: getActividad 
            * @desc: Get value of property "Actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getActividad(){ 
              $r = $this->data->get($this->id); 
              $this->actividad = $r->actividad; 
              return $this->actividad;
       } 
       public function addRestriccionPorActividadDeOtroRecurso($actividad){ 
              $restriccion_nominal = $this->id; 
              $obj = new RestriccionPorActividadDeOtroRecursoClass($restriccion_nominal,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionPorActividadDeOtroRecurso(){ 
          $collection = new RestriccionPorActividadDeOtroRecursoCollection($this->data->db);
          $collection->Load(null,null,"*","restriccion_nominal = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionRealDeOtroRecurso($periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $restriccion_nominal = $this->id; 
              $obj = new RestriccionRealDeOtroRecursoClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeOtroRecurso(){ 
          $collection = new RestriccionRealDeOtroRecursoCollection($this->data->db);
          $collection->Load(null,null,"*","restriccion_nominal = '".$this->id."'");
           return $collection;
       } 
} 
?>