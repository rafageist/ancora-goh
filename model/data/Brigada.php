<?php 
   class Brigada{ 
       var $id; 
       var $descripcion; 
       var $especialidad; 
       var $nivel; 
       var $matricula; 
       var $creador; 
       var $data;  
          /** 
            * @name: Brigada 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function Brigada($descripcion,$especialidad,$nivel,$matricula,$creador,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new BrigadaModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($descripcion,$especialidad,$nivel,$matricula,$creador); 
                    $this->id = $replicas["id"]; 
                    $this->descripcion = $descripcion; 
                    $this->especialidad = $especialidad; 
                    $this->nivel = $nivel; 
                    $this->matricula = $matricula; 
                    $this->creador = $creador; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->descripcion = $r->descripcion;
                  $this->especialidad = $r->especialidad;
                  $this->nivel = $r->nivel;
                  $this->matricula = $r->matricula;
                  $this->creador = $r->creador;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->descripcion = $descripcion;
                  $this->especialidad = $especialidad;
                  $this->nivel = $nivel;
                  $this->matricula = $matricula;
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
            * @name: setDescripcion 
            * @desc: Set value of property "Descripcion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDescripcion($value){ 
              $this->descripcion = $value; 
              $this->data->setBy("id = '".$this->id."'", "descripcion = '$value'"); 
       } 
          /** 
            * @name: getDescripcion 
            * @desc: Get value of property "Descripcion" 
            * @updated: 20/06/10 10:40:54 
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
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setEspecialidad($value){ 
              $this->especialidad = $value; 
              $this->data->setBy("id = '".$this->id."'", "especialidad = '$value'"); 
       } 
          /** 
            * @name: getEspecialidad 
            * @desc: Get value of property "Especialidad" 
            * @updated: 20/06/10 10:40:54 
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
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setNivel($value){ 
              $this->nivel = $value; 
              $this->data->setBy("id = '".$this->id."'", "nivel = '$value'"); 
       } 
          /** 
            * @name: getNivel 
            * @desc: Get value of property "Nivel" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getNivel(){ 
              $r = $this->data->get($this->id); 
              $this->nivel = $r->nivel; 
              return $this->nivel;
       } 
          /** 
            * @name: setMatricula 
            * @desc: Set value of property "Matricula" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setMatricula($value){ 
              $this->matricula = $value; 
              $this->data->setBy("id = '".$this->id."'", "matricula = $value"); 
       } 
          /** 
            * @name: getMatricula 
            * @desc: Get value of property "Matricula" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getMatricula(){ 
              $r = $this->data->get($this->id); 
              $this->matricula = $r->matricula; 
              return $this->matricula;
       } 
          /** 
            * @name: setCreador 
            * @desc: Set value of property "Creador" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setCreador($value){ 
              $this->creador = $value; 
              $this->data->setBy("id = '".$this->id."'", "creador = '$value'"); 
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
       public function addRestriccionCondicional($planificacion,$activa,$clasif_act,$asig,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor){ 
              $brigada = $this->id; 
              $consec_brigada = $this->id; 
              $obj = new RestriccionCondicionalClass($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionCondicional(){ 
          $collection = new RestriccionCondicionalCollection($this->data->db);
          $collection->Load(null,null,"*","brigada = '".$this->id."' AND consec_brigada = '".$this->id."'");
           return $collection;
       } 
       public function addBrigadaDeGrupo($grupo){ 
              $brigada = $this->id; 
              $obj = new BrigadaDeGrupoClass($brigada,$grupo,"",true,false); 
              return $obj;
       } 
       public function getBrigadaDeGrupo(){ 
          $collection = new BrigadaDeGrupoCollection($this->data->db);
          $collection->Load(null,null,"*","brigada = '".$this->id."'");
           return $collection;
       } 
       public function addHerenciaDeRestricciones($planificacion,$activa,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_profesor,$padre_lugar,$padre_otro_recurso){ 
              $hijo_brigada = $this->id; 
              $padre_brigada = $this->id; 
              $obj = new HerenciaDeRestriccionesClass($planificacion,$activa,$hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso,"",true,false); 
              return $obj;
       } 
       public function getHerenciaDeRestricciones(){ 
          $collection = new HerenciaDeRestriccionesCollection($this->data->db);
          $collection->Load(null,null,"*","hijo_brigada = '".$this->id."' AND padre_brigada = '".$this->id."'");
           return $collection;
       } 
       public function addHigieneParaBrigada($periodo,$gasto_maximo_diario){ 
              $brigada = $this->id; 
              $obj = new HigieneParaBrigadaClass($periodo,$brigada,$gasto_maximo_diario,"",true,false); 
              return $obj;
       } 
       public function getHigieneParaBrigada(){ 
          $collection = new HigieneParaBrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","brigada = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionDeBrigada($periodo,$dia,$turno,$valor,$por_actividad,$actividad){ 
              $brigada = $this->id; 
              $obj = new RestriccionDeBrigadaClass($brigada,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeBrigada(){ 
          $collection = new RestriccionDeBrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","brigada = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionRealDeBrigada($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad){ 
              $objeto = $this->id; 
              $obj = new RestriccionRealDeBrigadaClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeBrigada(){ 
          $collection = new RestriccionRealDeBrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","objeto = '".$this->id."'");
           return $collection;
       } 
} 
?>