<?php 
   class Planificacion{ 
       var $id; 
       var $descripcion; 
       var $fecha_inicial; 
       var $cant_dias; 
       var $activa; 
       var $creador; 
       var $data;  
          /** 
            * @name: Planificacion 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function Planificacion($descripcion,$fecha_inicial,$cant_dias,$activa,$creador,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new PlanificacionModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($descripcion,$fecha_inicial,$cant_dias,$activa,$creador); 
                    $this->id = $replicas["id"]; 
                    $this->descripcion = $descripcion; 
                    $this->fecha_inicial = $fecha_inicial; 
                    $this->cant_dias = $cant_dias; 
                    $this->activa = $activa; 
                    $this->creador = $creador; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->descripcion = $r->descripcion;
                  $this->fecha_inicial = $r->fecha_inicial;
                  $this->cant_dias = $r->cant_dias;
                  $this->activa = $r->activa;
                  $this->creador = $r->creador;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->descripcion = $descripcion;
                  $this->fecha_inicial = $fecha_inicial;
                  $this->cant_dias = $cant_dias;
                  $this->activa = $activa;
                  $this->creador = $creador;
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
            * @name: setDescripcion 
            * @desc: Set value of property "Descripcion" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setDescripcion($value){ 
              $this->descripcion = $value; 
              $this->data->setBy("id = '".$this->id."'", "descripcion = '$value'"); 
       } 
          /** 
            * @name: getDescripcion 
            * @desc: Get value of property "Descripcion" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getDescripcion(){ 
              $r = $this->data->get($this->id); 
              $this->descripcion = $r->descripcion; 
              return $this->descripcion;
       } 
          /** 
            * @name: setFechaInicial 
            * @desc: Set value of property "FechaInicial" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setFechaInicial($value){ 
              $this->fecha_inicial = $value; 
              $this->data->setBy("id = '".$this->id."'", "fecha_inicial = $value"); 
       } 
          /** 
            * @name: getFechaInicial 
            * @desc: Get value of property "FechaInicial" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getFechaInicial(){ 
              $r = $this->data->get($this->id); 
              $this->fecha_inicial = $r->fecha_inicial; 
              return $this->fecha_inicial;
       } 
          /** 
            * @name: setCantDias 
            * @desc: Set value of property "CantDias" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setCantDias($value){ 
              $this->cant_dias = $value; 
              $this->data->setBy("id = '".$this->id."'", "cant_dias = $value"); 
       } 
          /** 
            * @name: getCantDias 
            * @desc: Get value of property "CantDias" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getCantDias(){ 
              $r = $this->data->get($this->id); 
              $this->cant_dias = $r->cant_dias; 
              return $this->cant_dias;
       } 
          /** 
            * @name: setActiva 
            * @desc: Set value of property "Activa" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setActiva($value){ 
              $this->activa = $value; 
               if ($value == true || $value == 1 || $value == "t") $value = "true"; 
               else if ($value == true || $value == 0 || $value == "f") $value = "false"; 
              $this->data->setBy("id = '".$this->id."'", "activa = $value"); 
       } 
          /** 
            * @name: getActiva 
            * @desc: Get value of property "Activa" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getActiva(){ 
              $r = $this->data->get($this->id); 
              $this->activa = $r->activa; 
              return $this->activa;
       } 
          /** 
            * @name: setCreador 
            * @desc: Set value of property "Creador" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setCreador($value){ 
              $this->creador = $value; 
              $this->data->setBy("id = '".$this->id."'", "creador = '$value'"); 
       } 
          /** 
            * @name: getCreador 
            * @desc: Get value of property "Creador" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getCreador(){ 
              $r = $this->data->get($this->id); 
              $this->creador = $r->creador; 
              return $this->creador;
       } 
       public function addGrupo($clasificacion,$descripcion,$especialidad,$nivel){ 
              $planificacion = $this->id; 
              $obj = new GrupoClass($clasificacion,$descripcion,$especialidad,$nivel,$planificacion,"",true,false); 
              return $obj;
       } 
       public function getGrupo(){ 
          $collection = new GrupoCollection($this->data->db);
          $collection->Load(null,null,"*","planificacion = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionCondicional($activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor){ 
              $planificacion = $this->id; 
              $obj = new RestriccionCondicionalClass($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionCondicional(){ 
          $collection = new RestriccionCondicionalCollection($this->data->db);
          $collection->Load(null,null,"*","planificacion = '".$this->id."'");
           return $collection;
       } 
       public function addHerenciaDeRestricciones($activa,$hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso){ 
              $planificacion = $this->id; 
              $obj = new HerenciaDeRestriccionesClass($planificacion,$activa,$hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso,"",true,false); 
              return $obj;
       } 
       public function getHerenciaDeRestricciones(){ 
          $collection = new HerenciaDeRestriccionesCollection($this->data->db);
          $collection->Load(null,null,"*","planificacion = '".$this->id."'");
           return $collection;
       } 
       public function addPeriodo($descripcion,$cant_dias,$cant_turnos,$hora_inicial,$duracion_del_turno){ 
              $planificacion = $this->id; 
              $obj = new PeriodoClass($descripcion,$cant_dias,$cant_turnos,$hora_inicial,$duracion_del_turno,$planificacion,"",true,false); 
              return $obj;
       } 
       public function getPeriodo(){ 
          $collection = new PeriodoCollection($this->data->db);
          $collection->Load(null,null,"*","planificacion = '".$this->id."'");
           return $collection;
       } 
} 
?>