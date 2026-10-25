<?php 
   class Actividad{ 
       var $id; 
       var $plantilla; 
       var $grupo; 
       var $profesor; 
       var $lugar; 
       var $dia; 
       var $turno; 
       var $momento_de_colocacion; 
       var $creador; 
       var $data;  
          /** 
            * @name: Actividad 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function __construct($plantilla,$grupo,$profesor,$lugar,$dia,$turno,$momento_de_colocacion,$creador,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new ActividadModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($plantilla,$grupo,$profesor,$lugar,$dia,$turno,$momento_de_colocacion,$creador); 
                    $this->id = $replicas["id"]; 
                    $this->plantilla = $plantilla; 
                    $this->grupo = $grupo; 
                    $this->profesor = $profesor; 
                    $this->lugar = $lugar; 
                    $this->dia = $dia; 
                    $this->turno = $turno; 
                    $this->momento_de_colocacion = $momento_de_colocacion; 
                    $this->creador = $creador; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->plantilla = $r->plantilla;
                  $this->grupo = $r->grupo;
                  $this->profesor = $r->profesor;
                  $this->lugar = $r->lugar;
                  $this->dia = $r->dia;
                  $this->turno = $r->turno;
                  $this->momento_de_colocacion = $r->momento_de_colocacion;
                  $this->creador = $r->creador;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->plantilla = $plantilla;
                  $this->grupo = $grupo;
                  $this->profesor = $profesor;
                  $this->lugar = $lugar;
                  $this->dia = $dia;
                  $this->turno = $turno;
                  $this->momento_de_colocacion = $momento_de_colocacion;
                  $this->creador = $creador;
              }
       } 
          /** 
            * @name: getId 
            * @desc: Get value of property "Id" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getId(){ 
              $r = $this->data->get($this->id); 
              $this->id = $r->id; 
              return $this->id;
       } 
          /** 
            * @name: setPlantilla 
            * @desc: Set value of property "Plantilla" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPlantilla($value){ 
              $this->plantilla = $value; 
              $this->data->getAnythingParams("UPDATE actividad SET plantilla = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getPlantilla 
            * @desc: Get value of property "Plantilla" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPlantilla(){ 
              $r = $this->data->get($this->id); 
              $this->plantilla = $r->plantilla; 
              return $this->plantilla;
       } 
          /** 
            * @name: setGrupo 
            * @desc: Set value of property "Grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setGrupo($value){ 
              $this->grupo = $value; 
              $this->data->getAnythingParams("UPDATE actividad SET grupo = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getGrupo 
            * @desc: Get value of property "Grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getGrupo(){ 
              $r = $this->data->get($this->id); 
              $this->grupo = $r->grupo; 
              return $this->grupo;
       } 
          /** 
            * @name: setProfesor 
            * @desc: Set value of property "Profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setProfesor($value){ 
              $this->profesor = $value; 
              $this->data->getAnythingParams("UPDATE actividad SET profesor = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getProfesor 
            * @desc: Get value of property "Profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getProfesor(){ 
              $r = $this->data->get($this->id); 
              $this->profesor = $r->profesor; 
              return $this->profesor;
       } 
          /** 
            * @name: setLugar 
            * @desc: Set value of property "Lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setLugar($value){ 
              $this->lugar = $value; 
              $this->data->getAnythingParams("UPDATE actividad SET lugar = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getLugar 
            * @desc: Get value of property "Lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getLugar(){ 
              $r = $this->data->get($this->id); 
              $this->lugar = $r->lugar; 
              return $this->lugar;
       } 
          /** 
            * @name: setDia 
            * @desc: Set value of property "Dia" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDia($value){ 
              $this->dia = $value; 
              $this->data->getAnythingParams("UPDATE actividad SET dia = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDia 
            * @desc: Get value of property "Dia" 
            * @updated: 20/06/10 10:40:54 
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
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setTurno($value){ 
              $this->turno = $value; 
              $this->data->getAnythingParams("UPDATE actividad SET turno = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getTurno 
            * @desc: Get value of property "Turno" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getTurno(){ 
              $r = $this->data->get($this->id); 
              $this->turno = $r->turno; 
              return $this->turno;
       } 
          /** 
            * @name: setMomentoDeColocacion 
            * @desc: Set value of property "MomentoDeColocacion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setMomentoDeColocacion($value){ 
              $this->momento_de_colocacion = $value; 
              $this->data->getAnythingParams("UPDATE actividad SET momento_de_colocacion = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getMomentoDeColocacion 
            * @desc: Get value of property "MomentoDeColocacion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getMomentoDeColocacion(){ 
              $r = $this->data->get($this->id); 
              $this->momento_de_colocacion = $r->momento_de_colocacion; 
              return $this->momento_de_colocacion;
       } 
          /** 
            * @name: setCreador 
            * @desc: Set value of property "Creador" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setCreador($value){ 
              $this->creador = $value; 
              $this->data->getAnythingParams("UPDATE actividad SET creador = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getCreador 
            * @desc: Get value of property "Creador" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getCreador(){ 
              $r = $this->data->get($this->id); 
              $this->creador = $r->creador; 
              return $this->creador;
       } 
       public function addOtrosRecursosUsados($otro_recurso,$creador){ 
              $actividad = $this->id; 
              $obj = new OtrosRecursosUsadosClass($otro_recurso,$actividad,$creador,"",true,false); 
              return $obj;
       } 
       public function getOtrosRecursosUsados(){ 
          $collection = new OtrosRecursosUsadosCollection($this->data->db);
          $collection->Load(null,null,"*","actividad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionDeBrigada($brigada,$periodo,$dia,$turno,$valor,$por_actividad){ 
              $actividad = $this->id; 
              $obj = new RestriccionDeBrigadaClass($brigada,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeBrigada(){ 
          $collection = new RestriccionDeBrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","actividad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionDeProfesor($profesor,$periodo,$dia,$turno,$valor,$por_actividad){ 
              $actividad = $this->id; 
              $obj = new RestriccionDeProfesorClass($profesor,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeProfesor(){ 
          $collection = new RestriccionDeProfesorCollection($this->data->db);
          $collection->Load(null,null,"*","actividad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionPorActividadDeLugar($restriccion_nominal){ 
              $actividad = $this->id; 
              $obj = new RestriccionPorActividadDeLugarClass($restriccion_nominal,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionPorActividadDeLugar(){ 
          $collection = new RestriccionPorActividadDeLugarCollection($this->data->db);
          $collection->Load(null,null,"*","actividad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionPorActividadDeOtroRecurso($restriccion_nominal){ 
              $actividad = $this->id; 
              $obj = new RestriccionPorActividadDeOtroRecursoClass($restriccion_nominal,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionPorActividadDeOtroRecurso(){ 
          $collection = new RestriccionPorActividadDeOtroRecursoCollection($this->data->db);
          $collection->Load(null,null,"*","actividad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionPorActividadDeProfesor($restriccion_nominal){ 
              $actividad = $this->id; 
              $obj = new RestriccionPorActividadDeProfesorClass($restriccion_nominal,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionPorActividadDeProfesor(){ 
          $collection = new RestriccionPorActividadDeProfesorCollection($this->data->db);
          $collection->Load(null,null,"*","actividad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDeBrigada($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$objeto){ 
              $actividad = $this->id; 
              $obj = new RestriccionRealDeBrigadaClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeBrigada(){ 
          $collection = new RestriccionRealDeBrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","actividad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionPorActividadDeBrigada($restriccion_nominal){ 
              $actividad = $this->id; 
              $obj = new RestriccionPorActividadDeBrigadaClass($restriccion_nominal,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionPorActividadDeBrigada(){ 
          $collection = new RestriccionPorActividadDeBrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","actividad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionDeLugar($lugar,$periodo,$dia,$turno,$valor,$por_actividad){ 
              $actividad = $this->id; 
              $obj = new RestriccionDeLugarClass($lugar,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeLugar(){ 
          $collection = new RestriccionDeLugarCollection($this->data->db);
          $collection->Load(null,null,"*","actividad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionDeOtroRecurso($otro_recurso,$periodo,$dia,$turno,$valor,$por_actividad){ 
              $actividad = $this->id; 
              $obj = new RestriccionDeOtroRecursoClass($otro_recurso,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeOtroRecurso(){ 
          $collection = new RestriccionDeOtroRecursoCollection($this->data->db);
          $collection->Load(null,null,"*","actividad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
} 
?>