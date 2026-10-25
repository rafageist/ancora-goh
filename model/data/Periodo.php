<?php 
   class Periodo{ 
       var $id; 
       var $descripcion; 
       var $cant_dias; 
       var $cant_turnos; 
       var $hora_inicial; 
       var $duracion_del_turno; 
       var $planificacion; 
       var $data;  
          /** 
            * @name: Periodo 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function __construct($descripcion,$cant_dias,$cant_turnos,$hora_inicial,$duracion_del_turno,$planificacion,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new PeriodoModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($descripcion,$cant_dias,$cant_turnos,$hora_inicial,$duracion_del_turno,$planificacion); 
                    $this->id = $replicas["id"]; 
                    $this->descripcion = $descripcion; 
                    $this->cant_dias = $cant_dias; 
                    $this->cant_turnos = $cant_turnos; 
                    $this->hora_inicial = $hora_inicial; 
                    $this->duracion_del_turno = $duracion_del_turno; 
                    $this->planificacion = $planificacion; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->descripcion = $r->descripcion;
                  $this->cant_dias = $r->cant_dias;
                  $this->cant_turnos = $r->cant_turnos;
                  $this->hora_inicial = $r->hora_inicial;
                  $this->duracion_del_turno = $r->duracion_del_turno;
                  $this->planificacion = $r->planificacion;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->descripcion = $descripcion;
                  $this->cant_dias = $cant_dias;
                  $this->cant_turnos = $cant_turnos;
                  $this->hora_inicial = $hora_inicial;
                  $this->duracion_del_turno = $duracion_del_turno;
                  $this->planificacion = $planificacion;
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
            * @name: setCantTurnos 
            * @desc: Set value of property "CantTurnos" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setCantTurnos($value){ 
              $this->cant_turnos = $value; 
              $this->data->setBy("id = '".$this->id."'", "cant_turnos = $value"); 
       } 
          /** 
            * @name: getCantTurnos 
            * @desc: Get value of property "CantTurnos" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getCantTurnos(){ 
              $r = $this->data->get($this->id); 
              $this->cant_turnos = $r->cant_turnos; 
              return $this->cant_turnos;
       } 
          /** 
            * @name: setHoraInicial 
            * @desc: Set value of property "HoraInicial" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setHoraInicial($value){ 
              $this->hora_inicial = $value; 
              $this->data->setBy("id = '".$this->id."'", "hora_inicial = $value"); 
       } 
          /** 
            * @name: getHoraInicial 
            * @desc: Get value of property "HoraInicial" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getHoraInicial(){ 
              $r = $this->data->get($this->id); 
              $this->hora_inicial = $r->hora_inicial; 
              return $this->hora_inicial;
       } 
          /** 
            * @name: setDuracionDelTurno 
            * @desc: Set value of property "DuracionDelTurno" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setDuracionDelTurno($value){ 
              $this->duracion_del_turno = $value; 
              $this->data->setBy("id = '".$this->id."'", "duracion_del_turno = $value"); 
       } 
          /** 
            * @name: getDuracionDelTurno 
            * @desc: Get value of property "DuracionDelTurno" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getDuracionDelTurno(){ 
              $r = $this->data->get($this->id); 
              $this->duracion_del_turno = $r->duracion_del_turno; 
              return $this->duracion_del_turno;
       } 
          /** 
            * @name: setPlanificacion 
            * @desc: Set value of property "Planificacion" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPlanificacion($value){ 
              $this->planificacion = $value; 
              $this->data->setBy("id = '".$this->id."'", "planificacion = '$value'"); 
       } 
          /** 
            * @name: getPlanificacion 
            * @desc: Get value of property "Planificacion" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getPlanificacion(){ 
              $r = $this->data->get($this->id); 
              $this->planificacion = $r->planificacion; 
              return $this->planificacion;
       } 
       public function addExcepcionDeHrt($herencia,$dia,$turno){ 
              $periodo = $this->id; 
              $obj = new ExcepcionDeHrtClass($herencia,$periodo,$dia,$turno,"",true,false); 
              return $obj;
       } 
       public function getExcepcionDeHrt(){ 
          $collection = new ExcepcionDeHrtCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionCondicional($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor){ 
              $consec_periodo = $this->id; 
              $periodo = $this->id; 
              $obj = new RestriccionCondicionalClass($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionCondicional(){ 
          $collection = new RestriccionCondicionalCollection($this->data->db);
          $collection->Load(null,null,"*","consec_periodo = '".$this->id."' AND periodo = '".$this->id."'");
           return $collection;
       } 
       public function addDesglose($asignatura,$min_dias,$max_dias,$respetar_orden,$mismo_dia){ 
              $periodo = $this->id; 
              $obj = new DesgloseClass($asignatura,$periodo,$min_dias,$max_dias,$respetar_orden,$mismo_dia,"",true,false); 
              return $obj;
       } 
       public function getDesglose(){ 
          $collection = new DesgloseCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addHigieneParaProfesor($profesor,$gasto_maximo_diario){ 
              $periodo = $this->id; 
              $obj = new HigieneParaProfesorClass($periodo,$profesor,$gasto_maximo_diario,"",true,false); 
              return $obj;
       } 
       public function getHigieneParaProfesor(){ 
          $collection = new HigieneParaProfesorCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addHigieneParaBrigada($brigada,$gasto_maximo_diario){ 
              $periodo = $this->id; 
              $obj = new HigieneParaBrigadaClass($periodo,$brigada,$gasto_maximo_diario,"",true,false); 
              return $obj;
       } 
       public function getHigieneParaBrigada(){ 
          $collection = new HigieneParaBrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addReceso($despues_del_turno,$duracion){ 
              $periodo = $this->id; 
              $obj = new RecesoClass($periodo,$despues_del_turno,$duracion,"",true,false); 
              return $obj;
       } 
       public function getReceso(){ 
          $collection = new RecesoCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionDeAsignatura($asignatura,$dia,$turno,$valor){ 
              $periodo = $this->id; 
              $obj = new RestriccionDeAsignaturaClass($asignatura,$periodo,$dia,$turno,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeAsignatura(){ 
          $collection = new RestriccionDeAsignaturaCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionDeBrigada($brigada,$dia,$turno,$valor,$por_actividad,$actividad){ 
              $periodo = $this->id; 
              $obj = new RestriccionDeBrigadaClass($brigada,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeBrigada(){ 
          $collection = new RestriccionDeBrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addPeriodicidad($dia){ 
              $periodo = $this->id; 
              $obj = new PeriodicidadClass($periodo,$dia,"",true,false); 
              return $obj;
       } 
       public function getPeriodicidad(){ 
          $collection = new PeriodicidadCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionDeClasifAct($clasificacion,$dia,$turno,$valor){ 
              $periodo = $this->id; 
              $obj = new RestriccionDeClasifActClass($clasificacion,$periodo,$dia,$turno,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeClasifAct(){ 
          $collection = new RestriccionDeClasifActCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionDeProfesor($profesor,$dia,$turno,$valor,$por_actividad,$actividad){ 
              $periodo = $this->id; 
              $obj = new RestriccionDeProfesorClass($profesor,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeProfesor(){ 
          $collection = new RestriccionDeProfesorCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionDePeriodo($dia,$turno,$valor){ 
              $periodo = $this->id; 
              $obj = new RestriccionDePeriodoClass($periodo,$dia,$turno,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDePeriodo(){ 
          $collection = new RestriccionDePeriodoCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionDeEspecialidad($especialidad,$dia,$turno,$valor){ 
              $periodo = $this->id; 
              $obj = new RestriccionDeEspecialidadClass($especialidad,$periodo,$dia,$turno,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeEspecialidad(){ 
          $collection = new RestriccionDeEspecialidadCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionDeLugar($lugar,$dia,$turno,$valor,$por_actividad,$actividad){ 
              $periodo = $this->id; 
              $obj = new RestriccionDeLugarClass($lugar,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeLugar(){ 
          $collection = new RestriccionDeLugarCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionDeOtroRecurso($otro_recurso,$dia,$turno,$valor,$por_actividad,$actividad){ 
              $periodo = $this->id; 
              $obj = new RestriccionDeOtroRecursoClass($otro_recurso,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeOtroRecurso(){ 
          $collection = new RestriccionDeOtroRecursoCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addZpriori($clasificacion,$dia,$turno,$valor){ 
              $periodo = $this->id; 
              $obj = new ZprioriClass($clasificacion,$periodo,$dia,$turno,$valor,"",true,false); 
              return $obj;
       } 
       public function getZpriori(){ 
          $collection = new ZprioriCollection($this->data->db);
          $collection->Load(null,null,"*","periodo = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionRealDePeriodo($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad){ 
              $objeto = $this->id; 
              $obj = new RestriccionRealDePeriodoClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDePeriodo(){ 
          $collection = new RestriccionRealDePeriodoCollection($this->data->db);
          $collection->Load(null,null,"*","objeto = '".$this->id."'");
           return $collection;
       } 
} 
?>