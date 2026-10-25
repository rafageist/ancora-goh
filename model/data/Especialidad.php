<?php 
   class Especialidad{ 
       var $id; 
       var $descripcion; 
       var $creador; 
       var $data;  
          /** 
            * @name: Especialidad 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($descripcion,$creador,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new EspecialidadModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($descripcion,$creador); 
                    $this->id = $replicas["id"]; 
                    $this->descripcion = $descripcion; 
                    $this->creador = $creador; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->descripcion = $r->descripcion;
                  $this->creador = $r->creador;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->descripcion = $descripcion;
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
              $this->data->getAnythingParams("UPDATE especialidad SET descripcion = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
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
            * @name: setCreador 
            * @desc: Set value of property "Creador" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setCreador($value){ 
              $this->creador = $value; 
              $this->data->getAnythingParams("UPDATE especialidad SET creador = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
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
       public function addGrupo($clasificacion,$descripcion,$nivel,$planificacion){ 
              $especialidad = $this->id; 
              $obj = new GrupoClass($clasificacion,$descripcion,$especialidad,$nivel,$planificacion,"",true,false); 
              return $obj;
       } 
       public function getGrupo(){ 
          $collection = new GrupoCollection($this->data->db);
          $collection->Load(null,null,"*","especialidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addBrigada($descripcion,$nivel,$matricula,$creador){ 
              $especialidad = $this->id; 
              $obj = new BrigadaClass($descripcion,$especialidad,$nivel,$matricula,$creador,"",true,false); 
              return $obj;
       } 
       public function getBrigada(){ 
          $collection = new BrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","especialidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionCondicional($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor){ 
              $especialidad = $this->id; 
              $obj = new RestriccionCondicionalClass($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionCondicional(){ 
          $collection = new RestriccionCondicionalCollection($this->data->db);
          $collection->Load(null,null,"*","especialidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDeEspecialidad($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad){ 
              $objeto = $this->id; 
              $obj = new RestriccionRealDeEspecialidadClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeEspecialidad(){ 
          $collection = new RestriccionRealDeEspecialidadCollection($this->data->db);
          $collection->Load(null,null,"*","objeto = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionDeEspecialidad($periodo,$dia,$turno,$valor){ 
              $especialidad = $this->id; 
              $obj = new RestriccionDeEspecialidadClass($especialidad,$periodo,$dia,$turno,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeEspecialidad(){ 
          $collection = new RestriccionDeEspecialidadCollection($this->data->db);
          $collection->Load(null,null,"*","especialidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addAsignatura($descripcion,$nivel,$creador){ 
              $especialidad = $this->id; 
              $obj = new AsignaturaClass($descripcion,$especialidad,$nivel,$creador,"",true,false); 
              return $obj;
       } 
       public function getAsignatura(){ 
          $collection = new AsignaturaCollection($this->data->db);
          $collection->Load(null,null,"*","especialidad = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
} 
?>