<?php 
   class RestriccionCondicionalCollection{ 
       var $data;  
       var $collection;  
       var $last_offest;  
       var $last_limit;  
       /** 
         * @name: RestriccionCondicionalCollection 
         * @desc: Class constructor 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function __construct($db = false){ 
              if ($db !=false) $this->data = new RestriccionCondicionalModel($db); 
       } 
       /** 
         * @name: RestriccionCondicionalCollection 
         * @desc: Repair item 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function RepairItem(&$item){ 
            if (!isset($item->id)) $item->id = null; 
            if (!isset($item->planificacion)) $item->planificacion = null; 
            if (!isset($item->activa)) $item->activa = null; 
            if (!isset($item->clasif_act)) $item->clasif_act = null; 
            if (!isset($item->asig)) $item->asig = null; 
            if (!isset($item->brigada)) $item->brigada = null; 
            if (!isset($item->profesor)) $item->profesor = null; 
            if (!isset($item->lugar)) $item->lugar = null; 
            if (!isset($item->otro_recurso)) $item->otro_recurso = null; 
            if (!isset($item->especialidad)) $item->especialidad = null; 
            if (!isset($item->nivel)) $item->nivel = null; 
            if (!isset($item->fecha_ini)) $item->fecha_ini = null; 
            if (!isset($item->fecha_fin)) $item->fecha_fin = null; 
            if (!isset($item->hora_ini)) $item->hora_ini = null; 
            if (!isset($item->hora_fin)) $item->hora_fin = null; 
            if (!isset($item->discreto_exacto)) $item->discreto_exacto = null; 
            if (!isset($item->dia_ini_planif)) $item->dia_ini_planif = null; 
            if (!isset($item->dia_fin_planif)) $item->dia_fin_planif = null; 
            if (!isset($item->hora_ini_planif)) $item->hora_ini_planif = null; 
            if (!isset($item->hora_fin_planif)) $item->hora_fin_planif = null; 
            if (!isset($item->discreto_exacto_planif)) $item->discreto_exacto_planif = null; 
            if (!isset($item->periodo)) $item->periodo = null; 
            if (!isset($item->dia_ini)) $item->dia_ini = null; 
            if (!isset($item->dia_fin)) $item->dia_fin = null; 
            if (!isset($item->turno_ini)) $item->turno_ini = null; 
            if (!isset($item->turno_fin)) $item->turno_fin = null; 
            if (!isset($item->discreto)) $item->discreto = null; 
            if (!isset($item->hoy)) $item->hoy = null; 
            if (!isset($item->ahora)) $item->ahora = null; 
            if (!isset($item->dia_relativo_anterior)) $item->dia_relativo_anterior = null; 
            if (!isset($item->cant_dias_relativo_anterior)) $item->cant_dias_relativo_anterior = null; 
            if (!isset($item->dia_relativo_posterior)) $item->dia_relativo_posterior = null; 
            if (!isset($item->cant_dias_relativo_posterior)) $item->cant_dias_relativo_posterior = null; 
            if (!isset($item->rango_tiempo_relativo_anterior_ini)) $item->rango_tiempo_relativo_anterior_ini = null; 
            if (!isset($item->rango_tiempo_relativo_anterior_tamano)) $item->rango_tiempo_relativo_anterior_tamano = null; 
            if (!isset($item->turno_relativo_anterior)) $item->turno_relativo_anterior = null; 
            if (!isset($item->cant_turnos_relativo_anterior)) $item->cant_turnos_relativo_anterior = null; 
            if (!isset($item->turno_relativo_posterior)) $item->turno_relativo_posterior = null; 
            if (!isset($item->cant_turnos_relativo_posterior)) $item->cant_turnos_relativo_posterior = null; 
            if (!isset($item->rango_tiempo_relativo_posterior_ini)) $item->rango_tiempo_relativo_posterior_ini = null; 
            if (!isset($item->rango_tiempo_relativo_posterior_tamano)) $item->rango_tiempo_relativo_posterior_tamano = null; 
            if (!isset($item->entre_dias_antes)) $item->entre_dias_antes = null; 
            if (!isset($item->entre_dias_despues)) $item->entre_dias_despues = null; 
            if (!isset($item->entre_turnos_antes)) $item->entre_turnos_antes = null; 
            if (!isset($item->entre_turnos_despues)) $item->entre_turnos_despues = null; 
            if (!isset($item->entre_tiempo_antes)) $item->entre_tiempo_antes = null; 
            if (!isset($item->entre_tiempo_despues)) $item->entre_tiempo_despues = null; 
            if (!isset($item->cant_exa)) $item->cant_exa = null; 
            if (!isset($item->cant_min)) $item->cant_min = null; 
            if (!isset($item->cant_max)) $item->cant_max = null; 
            if (!isset($item->consec_brigada)) $item->consec_brigada = null; 
            if (!isset($item->consec_profesor)) $item->consec_profesor = null; 
            if (!isset($item->consec_lugar)) $item->consec_lugar = null; 
            if (!isset($item->consec_otro_recurso)) $item->consec_otro_recurso = null; 
            if (!isset($item->consec_periodo)) $item->consec_periodo = null; 
            if (!isset($item->consec_dia_ini)) $item->consec_dia_ini = null; 
            if (!isset($item->consec_dia_fin)) $item->consec_dia_fin = null; 
            if (!isset($item->consec_turno_ini)) $item->consec_turno_ini = null; 
            if (!isset($item->consec_turno_fin)) $item->consec_turno_fin = null; 
            if (!isset($item->consec_discreto)) $item->consec_discreto = null; 
            if (!isset($item->siempre)) $item->siempre = null; 
            if (!isset($item->valor)) $item->valor = null; 
       } 
       /** 
         * @name: Load 
         * @desc: Load somes objects to memory collection 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function Load($offset = null, $limit = null, $fields = "*", $where = "" ){ 
             if ($offset==null or $limit=null) {
                 if ($where == "")
                     $r = $this->data->getAll("*",true);
                 else 
                     $r = $this->data->getAllBy($where, "*",true);
             }
             else {
                 if ($where == "")
                  $r = $this->data->getLimit($limit,$offset,$fields,true);
                else 
                  $r = $this->data->getLimitBy($limit,$offset,$where,$fields,true);
             }
             // $r is a collection of value objects (VO)

             foreach ($r as $item){
                 $this->RepairItem($item);
                 $obj = new RestriccionCondicional($item->planificacion,$item->activa,$item->clasif_act,$item->asig,$item->brigada,$item->profesor,$item->lugar,$item->otro_recurso,$item->especialidad,$item->nivel,$item->fecha_ini,$item->fecha_fin,$item->hora_ini,$item->hora_fin,$item->discreto_exacto,$item->dia_ini_planif,$item->dia_fin_planif,$item->hora_ini_planif,$item->hora_fin_planif,$item->discreto_exacto_planif,$item->periodo,$item->dia_ini,$item->dia_fin,$item->turno_ini,$item->turno_fin,$item->discreto,$item->hoy,$item->ahora,$item->dia_relativo_anterior,$item->cant_dias_relativo_anterior,$item->dia_relativo_posterior,$item->cant_dias_relativo_posterior,$item->rango_tiempo_relativo_anterior_ini,$item->rango_tiempo_relativo_anterior_tamano,$item->turno_relativo_anterior,$item->cant_turnos_relativo_anterior,$item->turno_relativo_posterior,$item->cant_turnos_relativo_posterior,$item->rango_tiempo_relativo_posterior_ini,$item->rango_tiempo_relativo_posterior_tamano,$item->entre_dias_antes,$item->entre_dias_despues,$item->entre_turnos_antes,$item->entre_turnos_despues,$item->entre_tiempo_antes,$item->entre_tiempo_despues,$item->cant_exa,$item->cant_min,$item->cant_max,$item->consec_brigada,$item->consec_profesor,$item->consec_lugar,$item->consec_otro_recurso,$item->consec_periodo,$item->consec_dia_ini,$item->consec_dia_fin,$item->consec_turno_ini,$item->consec_turno_fin,$item->consec_discreto,$item->siempre,$item->valor,$item->id,false, $this->data, false);
                 $this->collection[] = $obj;
             } 
       } 
       /** 
         * @name: Reload 
         * @desc: Reload objects to memory collection with last offset and limit 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function Reload(){ 
            $this->Load($last_offset, $last_limit); 
       } 
       /** 
         * @name: getItem 
         * @desc: Load a specific object by primary keys 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function getItem($id){ 
             $r = $this->data->get($id);
             // $r is a value object (VO)
             $obj = false;
             if ($r) $obj = new RestriccionCondicional($r->planificacion,$r->activa,$r->clasif_act,$r->asig,$r->brigada,$r->profesor,$r->lugar,$r->otro_recurso,$r->especialidad,$r->nivel,$r->fecha_ini,$r->fecha_fin,$r->hora_ini,$r->hora_fin,$r->discreto_exacto,$r->dia_ini_planif,$r->dia_fin_planif,$r->hora_ini_planif,$r->hora_fin_planif,$r->discreto_exacto_planif,$r->periodo,$r->dia_ini,$r->dia_fin,$r->turno_ini,$r->turno_fin,$r->discreto,$r->hoy,$r->ahora,$r->dia_relativo_anterior,$r->cant_dias_relativo_anterior,$r->dia_relativo_posterior,$r->cant_dias_relativo_posterior,$r->rango_tiempo_relativo_anterior_ini,$r->rango_tiempo_relativo_anterior_tamano,$r->turno_relativo_anterior,$r->cant_turnos_relativo_anterior,$r->turno_relativo_posterior,$r->cant_turnos_relativo_posterior,$r->rango_tiempo_relativo_posterior_ini,$r->rango_tiempo_relativo_posterior_tamano,$r->entre_dias_antes,$r->entre_dias_despues,$r->entre_turnos_antes,$r->entre_turnos_despues,$r->entre_tiempo_antes,$r->entre_tiempo_despues,$r->cant_exa,$r->cant_min,$r->cant_max,$r->consec_brigada,$r->consec_profesor,$r->consec_lugar,$r->consec_otro_recurso,$r->consec_periodo,$r->consec_dia_ini,$r->consec_dia_fin,$r->consec_turno_ini,$r->consec_turno_fin,$r->consec_discreto,$r->siempre,$r->valor,$r->id,false, $this->data);
              return $obj;
       } 
       /** 
         * @name: get 
         * @desc: Load a specific object in collection by index 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function get($idx){ 
             if (isset($this->collection[$idx]))  
                  return $this->collection[$idx];
             return null; 
       } 
       /** 
         * @name: removeItem 
         * @desc: Remove a specific object by primary key 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function removeItem($id){ 
             $this->data->remove($id);
       } 
       /** 
         * @name: remove 
         * @desc: Remove a specific object in collection by index 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function remove($idx){ 
             if (isset($this->collection[$idx])){  
                  $this->removeItem($this->collection[$idx]->getId());
                  unset($this->collection[$idx]);
                  return true;
              } 
              return false; 
        } 
  } 
?>