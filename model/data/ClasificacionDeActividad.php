<?php 
   class ClasificacionDeActividad{ 
       var $id; 
       var $descripcion; 
       var $duracion; 
       var $creador; 
       var $prioridad; 
       var $gasto; 
       var $data;  
          /** 
            * @name: ClasificacionDeActividad 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function __construct($descripcion,$duracion,$creador,$prioridad,$gasto,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new ClasificacionDeActividadModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($descripcion,$duracion,$creador,$prioridad,$gasto); 
                    $this->id = $replicas["id"]; 
                    $this->descripcion = $descripcion; 
                    $this->duracion = $duracion; 
                    $this->creador = $creador; 
                    $this->prioridad = $prioridad; 
                    $this->gasto = $gasto; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->descripcion = $r->descripcion;
                  $this->duracion = $r->duracion;
                  $this->creador = $r->creador;
                  $this->prioridad = $r->prioridad;
                  $this->gasto = $r->gasto;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->descripcion = $descripcion;
                  $this->duracion = $duracion;
                  $this->creador = $creador;
                  $this->prioridad = $prioridad;
                  $this->gasto = $gasto;
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
              $this->data->getAnythingParams("UPDATE clasificacion_de_actividad SET descripcion = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
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
            * @name: setDuracion 
            * @desc: Set value of property "Duracion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDuracion($value){ 
              $this->duracion = $value; 
              $this->data->getAnythingParams("UPDATE clasificacion_de_actividad SET duracion = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDuracion 
            * @desc: Get value of property "Duracion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDuracion(){ 
              $r = $this->data->get($this->id); 
              $this->duracion = $r->duracion; 
              return $this->duracion;
       } 
          /** 
            * @name: setCreador 
            * @desc: Set value of property "Creador" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setCreador($value){ 
              $this->creador = $value; 
              $this->data->getAnythingParams("UPDATE clasificacion_de_actividad SET creador = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
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
          /** 
            * @name: setPrioridad 
            * @desc: Set value of property "Prioridad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPrioridad($value){ 
              $this->prioridad = $value; 
              $this->data->getAnythingParams("UPDATE clasificacion_de_actividad SET prioridad = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getPrioridad 
            * @desc: Get value of property "Prioridad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPrioridad(){ 
              $r = $this->data->get($this->id); 
              $this->prioridad = $r->prioridad; 
              return $this->prioridad;
       } 
          /** 
            * @name: setGasto 
            * @desc: Set value of property "Gasto" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setGasto($value){ 
              $this->gasto = $value; 
              $this->data->getAnythingParams("UPDATE clasificacion_de_actividad SET gasto = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getGasto 
            * @desc: Get value of property "Gasto" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getGasto(){ 
              $r = $this->data->get($this->id); 
              $this->gasto = $r->gasto; 
              return $this->gasto;
       } 
       public function addGrupo($descripcion,$especialidad,$nivel,$planificacion){ 
              $clasificacion = $this->id; 
              $obj = new GrupoClass($clasificacion,$descripcion,$especialidad,$nivel,$planificacion,"",true,false); 
              return $obj;
       } 
       public function getGrupo(){ 
          $collection = new GrupoCollection($this->data->db);
          $collection->Load(null,null,"*","clasificacion = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionCondicional($planificacion,$activa,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor){ 
              $clasif_act = $this->id; 
              $obj = new RestriccionCondicionalClass($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionCondicional(){ 
          $collection = new RestriccionCondicionalCollection($this->data->db);
          $collection->Load(null,null,"*","clasif_act = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionDeClasifAct($periodo,$dia,$turno,$valor){ 
              $clasificacion = $this->id; 
              $obj = new RestriccionDeClasifActClass($clasificacion,$periodo,$dia,$turno,$valor,"",true,false); 
              return $obj;
       } 
       public function getRestriccionDeClasifAct(){ 
          $collection = new RestriccionDeClasifActCollection($this->data->db);
          $collection->Load(null,null,"*","clasificacion = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addRestriccionRealDeClasifAct($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad){ 
              $objeto = $this->id; 
              $obj = new RestriccionRealDeClasifActClass($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,"",true,false); 
              return $obj;
       } 
       public function getRestriccionRealDeClasifAct(){ 
          $collection = new RestriccionRealDeClasifActCollection($this->data->db);
          $collection->Load(null,null,"*","objeto = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addZpriori($periodo,$dia,$turno,$valor){ 
              $clasificacion = $this->id; 
              $obj = new ZprioriClass($clasificacion,$periodo,$dia,$turno,$valor,"",true,false); 
              return $obj;
       } 
       public function getZpriori(){ 
          $collection = new ZprioriCollection($this->data->db);
          $collection->Load(null,null,"*","clasificacion = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
       public function addPlantillaDeActividad($desglose,$orden,$exige_profesor,$exige_lugar){ 
              $clasificacion = $this->id; 
              $obj = new PlantillaDeActividadClass($desglose,$orden,$clasificacion,$exige_profesor,$exige_lugar,"",true,false); 
              return $obj;
       } 
       public function getPlantillaDeActividad(){ 
          $collection = new PlantillaDeActividadCollection($this->data->db);
          $collection->Load(null,null,"*","clasificacion = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
} 
?>