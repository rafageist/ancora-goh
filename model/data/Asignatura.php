<?php 
   class Asignatura{ 
       var $id; 
       var $descripcion; 
       var $especialidad; 
       var $nivel; 
       var $creador; 
       var $data;  
          /** 
            * @name: Asignatura 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($descripcion,$especialidad,$nivel,$creador,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new AsignaturaModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($descripcion,$especialidad,$nivel,$creador); 
                    $this->id = $replicas["id"]; 
                    $this->descripcion = $descripcion; 
                    $this->especialidad = $especialidad; 
                    $this->nivel = $nivel; 
                    $this->creador = $creador; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->descripcion = $r->descripcion;
                  $this->especialidad = $r->especialidad;
                  $this->nivel = $r->nivel;
                  $this->creador = $r->creador;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->descripcion = $descripcion;
                  $this->especialidad = $especialidad;
                  $this->nivel = $nivel;
                  $this->creador = $creador;
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
            * @name: setDescripcion 
            * @desc: Set value of property "Descripcion" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setDescripcion($value){ 
              $this->descripcion = $value; 
              $this->data->getAnythingParams("UPDATE asignatura SET descripcion = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDescripcion 
            * @desc: Get value of property "Descripcion" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getDescripcion(){ 
              $r = $this->data->get($this->id); 
              $this->descripcion = $r->descripcion; 
              return $this->descripcion;
       } 
          /** 
            * @name: setEspecialidad 
            * @desc: Set value of property "Especialidad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setEspecialidad($value){ 
              $this->especialidad = $value; 
              $this->data->getAnythingParams("UPDATE asignatura SET especialidad = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getEspecialidad 
            * @desc: Get value of property "Especialidad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getEspecialidad(){ 
              $r = $this->data->get($this->id); 
              $this->especialidad = $r->especialidad; 
              return $this->especialidad;
       } 
          /** 
            * @name: setNivel 
            * @desc: Set value of property "Nivel" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setNivel($value){ 
              $this->nivel = $value; 
              $this->data->getAnythingParams("UPDATE asignatura SET nivel = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getNivel 
            * @desc: Get value of property "Nivel" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getNivel(){ 
              $r = $this->data->get($this->id); 
              $this->nivel = $r->nivel; 
              return $this->nivel;
       } 
          /** 
            * @name: setCreador 
            * @desc: Set value of property "Creador" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setCreador($value){ 
              $this->creador = $value; 
              $this->data->getAnythingParams("UPDATE asignatura SET creador = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getCreador 
            * @desc: Get value of property "Creador" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getCreador(){ 
              $r = $this->data->get($this->id); 
              $this->creador = $r->creador; 
              return $this->creador;
       } 
       public function addRestriccionCondicional($planificacion,$activa,$clasif_act,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor){ 
              $asig = $this->id; 
              $obj = new RestriccionCondicionalClass($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionCondicional(){ 
          $collection = new RestriccionCondicionalCollection($this->data->db);
          $collection->Load(null,null,"*","asig = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addDesglose($periodo,$min_dias,$max_dias,$respetar_orden,$mismo_dia){ 
              $asignatura = $this->id; 
              $obj = new DesgloseClass($asignatura,$periodo,$min_dias,$max_dias,$respetar_orden,$mismo_dia,"",true,false); 
              return $obj;
       } 
       public function getDesglose(){ 
          $collection = new DesgloseCollection($this->data->db);
          $collection->Load(null,null,"*","asignatura = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionDeAsignatura($periodo,$dia,$turno,$valor){ 
              $asignatura = $this->id; 
              $obj = new RestriccionDeAsignaturaClass($asignatura,$periodo,$dia,$turno,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeAsignatura(){ 
          $collection = new RestriccionDeAsignaturaCollection($this->data->db);
          $collection->Load(null,null,"*","asignatura = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDeAsignatura($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad){ 
              $objeto = $this->id; 
              $obj = new RestriccionRealDeAsignaturaClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeAsignatura(){ 
          $collection = new RestriccionRealDeAsignaturaCollection($this->data->db);
          $collection->Load(null,null,"*","objeto = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
} 
?>