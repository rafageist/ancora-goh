<?php 
   class Profesor{ 
       var $id; 
       var $nombre; 
       var $creador; 
       var $data;  
          /** 
            * @name: Profesor 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function __construct($nombre,$creador,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new ProfesorModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($nombre,$creador); 
                    $this->id = $replicas["id"]; 
                    $this->nombre = $nombre; 
                    $this->creador = $creador; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->nombre = $r->nombre;
                  $this->creador = $r->creador;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->nombre = $nombre;
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
            * @name: setNombre 
            * @desc: Set value of property "Nombre" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setNombre($value){ 
              $this->nombre = $value; 
              $this->data->setBy("id = '".$this->id."'", "nombre = '$value'"); 
       } 
          /** 
            * @name: getNombre 
            * @desc: Get value of property "Nombre" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getNombre(){ 
              $r = $this->data->get($this->id); 
              $this->nombre = $r->nombre; 
              return $this->nombre;
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
       public function addRestriccionCondicional($planificacion,$activa,$clasif_act,$asig,$brigada,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor){ 
              $consec_profesor = $this->id; 
              $profesor = $this->id; 
              $obj = new RestriccionCondicionalClass($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionCondicional(){ 
          $collection = new RestriccionCondicionalCollection($this->data->db);
          $collection->Load(null,null,"*","consec_profesor = '".$this->id."' AND profesor = '".$this->id."'");
           return $collection;
       } 
       public function addActividad($plantilla,$grupo,$lugar,$dia,$turno,$momento_de_colocacion,$creador){ 
              $profesor = $this->id; 
              $obj = new ActividadClass($plantilla,$grupo,$profesor,$lugar,$dia,$turno,$momento_de_colocacion,$creador,"",true,false); 
              return $obj;
       } 
       public function getActividad(){ 
          $collection = new ActividadCollection($this->data->db);
          $collection->Load(null,null,"*","profesor = '".$this->id."'");
           return $collection;
       } 
       public function addHerenciaDeRestricciones($planificacion,$activa,$hijo_brigada,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_lugar,$padre_otro_recurso){ 
              $hijo_profesor = $this->id; 
              $padre_profesor = $this->id; 
              $obj = new HerenciaDeRestriccionesClass($planificacion,$activa,$hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso,"",true,false); 
              return $obj;
       } 
       public function getHerenciaDeRestricciones(){ 
          $collection = new HerenciaDeRestriccionesCollection($this->data->db);
          $collection->Load(null,null,"*","hijo_profesor = '".$this->id."' AND padre_profesor = '".$this->id."'");
           return $collection;
       } 
       public function addHigieneParaProfesor($periodo,$gasto_maximo_diario){ 
              $profesor = $this->id; 
              $obj = new HigieneParaProfesorClass($periodo,$profesor,$gasto_maximo_diario,"",true,false); 
              return $obj;
       } 
       public function getHigieneParaProfesor(){ 
          $collection = new HigieneParaProfesorCollection($this->data->db);
          $collection->Load(null,null,"*","profesor = '".$this->id."'");
           return $collection;
       } 
       public function addProfesorParaActividad($plantilla,$prioridad){ 
              $profesor = $this->id; 
              $obj = new ProfesorParaActividadClass($plantilla,$profesor,$prioridad,"",true,false); 
              return $obj;
       } 
       public function getProfesorParaActividad(){ 
          $collection = new ProfesorParaActividadCollection($this->data->db);
          $collection->Load(null,null,"*","profesor = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionDeProfesor($periodo,$dia,$turno,$valor,$por_actividad,$actividad){ 
              $profesor = $this->id; 
              $obj = new RestriccionDeProfesorClass($profesor,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeProfesor(){ 
          $collection = new RestriccionDeProfesorCollection($this->data->db);
          $collection->Load(null,null,"*","profesor = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionRealDeProfesor($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad){ 
              $objeto = $this->id; 
              $obj = new RestriccionRealDeProfesorClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeProfesor(){ 
          $collection = new RestriccionRealDeProfesorCollection($this->data->db);
          $collection->Load(null,null,"*","objeto = '".$this->id."'");
           return $collection;
       } 
} 
?>