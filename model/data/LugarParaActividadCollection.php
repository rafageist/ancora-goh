<?php 
   class LugarParaActividadCollection{ 
       var $data;  
       var $collection;  
       var $last_offest;  
       var $last_limit;  
       /** 
         * @name: LugarParaActividadCollection 
         * @desc: Class constructor 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function __construct($db = false){ 
              if ($db !=false) $this->data = new LugarParaActividadModel($db); 
       } 
       /** 
         * @name: LugarParaActividadCollection 
         * @desc: Repair item 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function RepairItem(&$item){ 
            if (!isset($item->plantilla)) $item->plantilla = null; 
            if (!isset($item->lugar)) $item->lugar = null; 
            if (!isset($item->prioridad)) $item->prioridad = null; 
       } 
       /** 
         * @name: Load 
         * @desc: Load somes objects to memory collection 
         * @updated: 20/06/10 10:40:55 
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
                 $obj = new LugarParaActividad($item->plantilla,$item->lugar,$item->prioridad,false, $this->data, false);
                 $this->collection[] = $obj;
             } 
       } 
       /** 
         * @name: Reload 
         * @desc: Reload objects to memory collection with last offset and limit 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function Reload(){ 
            $this->Load($last_offset, $last_limit); 
       } 
       /** 
         * @name: getItem 
         * @desc: Load a specific object by primary keys 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function getItem($plantilla,$lugar){ 
             $r = $this->data->get($plantilla,$lugar);
             // $r is a value object (VO)
             $obj = false;
             if ($r) $obj = new LugarParaActividad($r->plantilla,$r->lugar,$r->prioridad,false, $this->data);
              return $obj;
       } 
       /** 
         * @name: get 
         * @desc: Load a specific object in collection by index 
         * @updated: 20/06/10 10:40:55 
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
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function removeItem($plantilla,$lugar){ 
             $this->data->remove($plantilla,$lugar);
       } 
       /** 
         * @name: remove 
         * @desc: Remove a specific object in collection by index 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function remove($idx){ 
             if (isset($this->collection[$idx])){  
                  $this->removeItem($this->collection[$idx]->getPlantilla(),$this->collection[$idx]->getLugar());
                  unset($this->collection[$idx]);
                  return true;
              } 
              return false; 
        } 
  } 
?>