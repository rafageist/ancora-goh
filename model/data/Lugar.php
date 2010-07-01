<?php 
   class Lugar{ 
       var $id; 
       var $nombre; 
       var $capacidad; 
       var $creador; 
       var $data;  
          /** 
            * @name: Lugar 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function Lugar($nombre,$capacidad,$creador,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new LugarModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($nombre,$capacidad,$creador); 
                    $this->id = $replicas["id"]; 
                    $this->nombre = $nombre; 
                    $this->capacidad = $capacidad; 
                    $this->creador = $creador; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->nombre = $r->nombre;
                  $this->capacidad = $r->capacidad;
                  $this->creador = $r->creador;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->nombre = $nombre;
                  $this->capacidad = $capacidad;
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
            * @name: setCapacidad 
            * @desc: Set value of property "Capacidad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setCapacidad($value){ 
              $this->capacidad = $value; 
              $this->data->setBy("id = '".$this->id."'", "capacidad = $value"); 
       } 
          /** 
            * @name: getCapacidad 
            * @desc: Get value of property "Capacidad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getCapacidad(){ 
              $r = $this->data->get($this->id); 
              $this->capacidad = $r->capacidad; 
              return $this->capacidad;
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
       public function addRestriccionCondicional($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor){ 
              $consec_lugar = $this->id; 
              $lugar = $this->id; 
              $obj = new RestriccionCondicionalClass($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionCondicional(){ 
          $collection = new RestriccionCondicionalCollection($this->data->db);
          $collection->Load(null,null,"*","consec_lugar = '".$this->id."' AND lugar = '".$this->id."'");
           return $collection;
       } 
       public function addActividad($plantilla,$grupo,$profesor,$dia,$turno,$momento_de_colocacion,$creador){ 
              $lugar = $this->id; 
              $obj = new ActividadClass($plantilla,$grupo,$profesor,$lugar,$dia,$turno,$momento_de_colocacion,$creador,"",true,false); 
              return $obj;
       } 
       public function getActividad(){ 
          $collection = new ActividadCollection($this->data->db);
          $collection->Load(null,null,"*","lugar = '".$this->id."'");
           return $collection;
       } 
       public function addHerenciaDeRestricciones($planificacion,$activa,$hijo_brigada,$hijo_profesor,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_otro_recurso){ 
              $hijo_lugar = $this->id; 
              $padre_lugar = $this->id; 
              $obj = new HerenciaDeRestriccionesClass($planificacion,$activa,$hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso,"",true,false); 
              return $obj;
       } 
       public function getHerenciaDeRestricciones(){ 
          $collection = new HerenciaDeRestriccionesCollection($this->data->db);
          $collection->Load(null,null,"*","hijo_lugar = '".$this->id."' AND padre_lugar = '".$this->id."'");
           return $collection;
       } 
       public function addCostoDeTraslado($costo){ 
              $destino = $this->id; 
              $origen = $this->id; 
              $obj = new CostoDeTrasladoClass($origen,$destino,$costo,"",true,false); 
              return $obj;
       } 
       public function getCostoDeTraslado(){ 
          $collection = new CostoDeTrasladoCollection($this->data->db);
          $collection->Load(null,null,"*","destino = '".$this->id."' AND origen = '".$this->id."'");
           return $collection;
       } 
       public function addLugarFijo($grupo){ 
              $lugar = $this->id; 
              $obj = new LugarFijoClass($grupo,$lugar,"",true,false); 
              return $obj;
       } 
       public function getLugarFijo(){ 
          $collection = new LugarFijoCollection($this->data->db);
          $collection->Load(null,null,"*","lugar = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionRealDeLugar($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad){ 
              $objeto = $this->id; 
              $obj = new RestriccionRealDeLugarClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeLugar(){ 
          $collection = new RestriccionRealDeLugarCollection($this->data->db);
          $collection->Load(null,null,"*","objeto = '".$this->id."'");
           return $collection;
       } 
       public function addLugarParaActividad($plantilla,$prioridad){ 
              $lugar = $this->id; 
              $obj = new LugarParaActividadClass($plantilla,$lugar,$prioridad,"",true,false); 
              return $obj;
       } 
       public function getLugarParaActividad(){ 
          $collection = new LugarParaActividadCollection($this->data->db);
          $collection->Load(null,null,"*","lugar = '".$this->id."'");
           return $collection;
       } 
       public function addRestriccionDeLugar($periodo,$dia,$turno,$valor,$por_actividad,$actividad){ 
              $lugar = $this->id; 
              $obj = new RestriccionDeLugarClass($lugar,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeLugar(){ 
          $collection = new RestriccionDeLugarCollection($this->data->db);
          $collection->Load(null,null,"*","lugar = '".$this->id."'");
           return $collection;
       } 
       public function addAccesoProhibido($grupo){ 
              $lugar = $this->id; 
              $obj = new AccesoProhibidoClass($grupo,$lugar,"",true,false); 
              return $obj;
       } 
       public function getAccesoProhibido(){ 
          $collection = new AccesoProhibidoCollection($this->data->db);
          $collection->Load(null,null,"*","lugar = '".$this->id."'");
           return $collection;
       } 
} 
?>