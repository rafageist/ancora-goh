<?php 
   class RestriccionRealDeProfesorCollection{ 
       var $data;  
       var $collection;  
       var $last_offest;  
       var $last_limit;  
       /** 
         * @name: RestriccionRealDeProfesorCollection 
         * @desc: Class constructor 
         * @updated: 20/06/10 10:40:56 
         * @version: 1.0 
         */ 
       public function __construct($db = false){ 
              if ($db !=false) $this->data = new RestriccionRealDeProfesorModel($db); 
       } 
       /** 
         * @name: RestriccionRealDeProfesorCollection 
         * @desc: Repair item 
         * @updated: 20/06/10 10:40:56 
         * @version: 1.0 
         */ 
       public function RepairItem(&$item){ 
            if (!isset($item->restriccion_nominal)) $item->restriccion_nominal = null; 
            if (!isset($item->periodicidad)) $item->periodicidad = null; 
            if (!isset($item->fecha)) $item->fecha = null; 
            if (!isset($item->hora_inicial)) $item->hora_inicial = null; 
            if (!isset($item->hora_final)) $item->hora_final = null; 
            if (!isset($item->por_actividad)) $item->por_actividad = null; 
            if (!isset($item->actividad)) $item->actividad = null; 
            if (!isset($item->objeto)) $item->objeto = null; 
       } 
       /** 
         * @name: Load 
         * @desc: Load somes objects to memory collection 
         * @updated: 20/06/10 10:40:56 
         * @version: 1.0 
         */ 
       public function Load($offset = null, $limit = null, $fields = "*", $where = "", $params = null ){ 
             if ($offset==null or $limit=null) {
                 if ($where == "")
                     $r = $this->data->getAll("*",true);
                 else 
                     $r = $this->data->getAllBy($where, "*",true, $params);
             }
             else {
                 if ($where == "")
                  $r = $this->data->getLimit($limit,$offset,$fields,true);
                else 
                  $r = $this->data->getLimitBy($limit,$offset,$where,$fields,true, $params);
             }
             // $r is a collection of value objects (VO)

             foreach ($r as $item){
                 $this->RepairItem($item);
                 $obj = new RestriccionRealDeProfesor($item->restriccion_nominal,$item->periodicidad,$item->fecha,$item->hora_inicial,$item->hora_final,$item->por_actividad,$item->actividad,$item->objeto,false, $this->data, false);
                 $this->collection[] = $obj;
             } 
       } 
       /** 
         * @name: Reload 
         * @desc: Reload objects to memory collection with last offset and limit 
         * @updated: 20/06/10 10:40:56 
         * @version: 1.0 
         */ 
       public function Reload(){ 
            $this->Load($last_offset, $last_limit); 
       } 
       /** 
         * @name: getItem 
         * @desc: Load a specific object by primary keys 
         * @updated: 20/06/10 10:40:56 
         * @version: 1.0 
         */ 
       public function getItem($restriccion_nominal,$periodicidad){ 
             $r = $this->data->get($restriccion_nominal,$periodicidad);
             // $r is a value object (VO)
             $obj = false;
             if ($r) $obj = new RestriccionRealDeProfesor($r->restriccion_nominal,$r->periodicidad,$r->fecha,$r->hora_inicial,$r->hora_final,$r->por_actividad,$r->actividad,$r->objeto,false, $this->data);
              return $obj;
       } 
       /** 
         * @name: get 
         * @desc: Load a specific object in collection by index 
         * @updated: 20/06/10 10:40:56 
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
         * @updated: 20/06/10 10:40:56 
         * @version: 1.0 
         */ 
       public function removeItem($restriccion_nominal,$periodicidad){ 
             $this->data->remove($restriccion_nominal,$periodicidad);
       } 
       /** 
         * @name: remove 
         * @desc: Remove a specific object in collection by index 
         * @updated: 20/06/10 10:40:56 
         * @version: 1.0 
         */ 
       public function remove($idx){ 
             if (isset($this->collection[$idx])){  
                  $this->removeItem($this->collection[$idx]->getRestriccionNominal(),$this->collection[$idx]->getPeriodicidad());
                  unset($this->collection[$idx]);
                  return true;
              } 
              return false; 
        } 
  } 
?>