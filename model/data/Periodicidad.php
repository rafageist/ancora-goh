<?php 
   class Periodicidad{ 
       var $id; 
       var $periodo; 
       var $dia; 
       var $data;  
          /** 
            * @name: Periodicidad 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function __construct($periodo,$dia,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new PeriodicidadModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($periodo,$dia); 
                    $this->id = $replicas["id"]; 
                    $this->periodo = $periodo; 
                    $this->dia = $dia; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->periodo = $r->periodo;
                  $this->dia = $r->dia;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->periodo = $periodo;
                  $this->dia = $dia;
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
            * @name: setPeriodo 
            * @desc: Set value of property "Periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPeriodo($value){ 
              $this->periodo = $value; 
              $this->data->getAnythingParams("UPDATE periodicidad SET periodo = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
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
              $this->data->getAnythingParams("UPDATE periodicidad SET dia = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
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
       public function addRestriccionRealDeAsignatura($restriccion_nominal,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $periodicidad = $this->id; 
              $obj = new RestriccionRealDeAsignaturaClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeAsignatura(){ 
          $collection = new RestriccionRealDeAsignaturaCollection($this->data->db);
          $collection->Load(null,null,"*","periodicidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDeClasifAct($restriccion_nominal,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $periodicidad = $this->id; 
              $obj = new RestriccionRealDeClasifActClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeClasifAct(){ 
          $collection = new RestriccionRealDeClasifActCollection($this->data->db);
          $collection->Load(null,null,"*","periodicidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDeLugar($restriccion_nominal,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $periodicidad = $this->id; 
              $obj = new RestriccionRealDeLugarClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeLugar(){ 
          $collection = new RestriccionRealDeLugarCollection($this->data->db);
          $collection->Load(null,null,"*","periodicidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDeBrigada($restriccion_nominal,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $periodicidad = $this->id; 
              $obj = new RestriccionRealDeBrigadaClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeBrigada(){ 
          $collection = new RestriccionRealDeBrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","periodicidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDeEspecialidad($restriccion_nominal,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $periodicidad = $this->id; 
              $obj = new RestriccionRealDeEspecialidadClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeEspecialidad(){ 
          $collection = new RestriccionRealDeEspecialidadCollection($this->data->db);
          $collection->Load(null,null,"*","periodicidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDeOtroRecurso($restriccion_nominal,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $periodicidad = $this->id; 
              $obj = new RestriccionRealDeOtroRecursoClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeOtroRecurso(){ 
          $collection = new RestriccionRealDeOtroRecursoCollection($this->data->db);
          $collection->Load(null,null,"*","periodicidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDeProfesor($restriccion_nominal,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $periodicidad = $this->id; 
              $obj = new RestriccionRealDeProfesorClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeProfesor(){ 
          $collection = new RestriccionRealDeProfesorCollection($this->data->db);
          $collection->Load(null,null,"*","periodicidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDePeriodo($restriccion_nominal,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $periodicidad = $this->id; 
              $obj = new RestriccionRealDePeriodoClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDePeriodo(){ 
          $collection = new RestriccionRealDePeriodoCollection($this->data->db);
          $collection->Load(null,null,"*","periodicidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
} 
?>