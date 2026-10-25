<?php 
   class RestriccionDeBrigada{ 
       var $id; 
       var $brigada; 
       var $periodo; 
       var $dia; 
       var $turno; 
       var $valor; 
       var $por_actividad; 
       var $actividad; 
       var $data;  
          /** 
            * @name: RestriccionDeBrigada 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function __construct($brigada,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RestriccionDeBrigadaModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($brigada,$periodo,$dia,$turno,$valor,$por_actividad,$actividad); 
                    $this->id = $replicas["id"]; 
                    $this->brigada = $brigada; 
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
                  $this->brigada = $r->brigada;
                  $this->periodo = $r->periodo;
                  $this->dia = $r->dia;
                  $this->turno = $r->turno;
                  $this->valor = $r->valor;
                  $this->por_actividad = $r->por_actividad;
                  $this->actividad = $r->actividad;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->brigada = $brigada;
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
            * @name: setBrigada 
            * @desc: Set value of property "Brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setBrigada($value){ 
              $this->brigada = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_de_brigada SET brigada = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getBrigada 
            * @desc: Get value of property "Brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getBrigada(){ 
              $r = $this->data->get($this->id); 
              $this->brigada = $r->brigada; 
              return $this->brigada;
       } 
          /** 
            * @name: setPeriodo 
            * @desc: Set value of property "Periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPeriodo($value){ 
              $this->periodo = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_de_brigada SET periodo = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
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
              $this->data->getAnythingParams("UPDATE restriccion_de_brigada SET dia = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
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
              $this->data->getAnythingParams("UPDATE restriccion_de_brigada SET turno = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
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
              $this->data->getAnythingParams("UPDATE restriccion_de_brigada SET valor = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
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
              $this->data->getAnythingParams("UPDATE restriccion_de_brigada SET por_actividad = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->id, "text"))); 
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
              $this->data->getAnythingParams("UPDATE restriccion_de_brigada SET actividad = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
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
       public function addRestriccionRealDeBrigada($periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
              $restriccion_nominal = $this->id; 
              $obj = new RestriccionRealDeBrigadaClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeBrigada(){ 
          $collection = new RestriccionRealDeBrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","restriccion_nominal = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionPorActividadDeBrigada($actividad){ 
              $restriccion_nominal = $this->id; 
              $obj = new RestriccionPorActividadDeBrigadaClass($restriccion_nominal,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionPorActividadDeBrigada(){ 
          $collection = new RestriccionPorActividadDeBrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","restriccion_nominal = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
} 
?>