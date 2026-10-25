<?php 
   class RestriccionDeProfesor{ 
       var $id; 
       var $profesor; 
       var $periodo; 
       var $dia; 
       var $turno; 
       var $valor; 
       var $por_actividad; 
       var $actividad; 
       var $data;  
          /** 
            * @name: RestriccionDeProfesor 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function __construct($profesor,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RestriccionDeProfesorModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($profesor,$periodo,$dia,$turno,$valor,$por_actividad,$actividad); 
                    $this->id = $replicas["id"]; 
                    $this->profesor = $profesor; 
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
                  $this->profesor = $r->profesor;
                  $this->periodo = $r->periodo;
                  $this->dia = $r->dia;
                  $this->turno = $r->turno;
                  $this->valor = $r->valor;
                  $this->por_actividad = $r->por_actividad;
                  $this->actividad = $r->actividad;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->profesor = $profesor;
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
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getId(){ 
              $r = $this->data->get($this->id); 
              $this->id = $r->id; 
              return $this->id;
       } 
          /** 
            * @name: setProfesor 
            * @desc: Set value of property "Profesor" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setProfesor($value){ 
              $this->profesor = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_de_profesor SET profesor = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getProfesor 
            * @desc: Get value of property "Profesor" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getProfesor(){ 
              $r = $this->data->get($this->id); 
              $this->profesor = $r->profesor; 
              return $this->profesor;
       } 
          /** 
            * @name: setPeriodo 
            * @desc: Set value of property "Periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPeriodo($value){ 
              $this->periodo = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_de_profesor SET periodo = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
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
              $this->data->getAnythingParams("UPDATE restriccion_de_profesor SET dia = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
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
              $this->data->getAnythingParams("UPDATE restriccion_de_profesor SET turno = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
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
              $this->data->getAnythingParams("UPDATE restriccion_de_profesor SET valor = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
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
          /** 
            * @name: setPorActividad 
            * @desc: Set value of property "PorActividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPorActividad($value){ 
              $this->por_actividad = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_de_profesor SET por_actividad = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getPorActividad 
            * @desc: Get value of property "PorActividad" 
            * @updated: 20/06/10 10:40:55 
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
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setActividad($value){ 
              $this->actividad = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_de_profesor SET actividad = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getActividad 
            * @desc: Get value of property "Actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getActividad(){ 
              $r = $this->data->get($this->id); 
              $this->actividad = $r->actividad; 
              return $this->actividad;
       } 
       public function addRestriccionPorActividadDeProfesor($actividad){ 
              $restriccion_nominal = $this->id; 
              $obj = new RestriccionPorActividadDeProfesorClass($restriccion_nominal,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionPorActividadDeProfesor(){ 
          $collection = new RestriccionPorActividadDeProfesorCollection($this->data->db);
          $collection->Load(null,null,"*","restriccion_nominal = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDeProfesor($periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $restriccion_nominal = $this->id; 
              $obj = new RestriccionRealDeProfesorClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeProfesor(){ 
          $collection = new RestriccionRealDeProfesorCollection($this->data->db);
          $collection->Load(null,null,"*","restriccion_nominal = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
} 
?>