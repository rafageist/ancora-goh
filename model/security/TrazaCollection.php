<?php 
   class TrazaCollection{ 
       var $data;  
       var $collection;  
       var $last_offest;  
       var $last_limit;  
       /** 
         * @name: TrazaCollection 
         * @desc: Class constructor 
         * @updated: 20/06/10 10:40:56 
         * @version: 1.0 
         */ 
       public function TrazaCollection($db = false){ 
              if ($db !=false) $this->data = new TrazaModel($db); 
       } 
       /** 
         * @name: TrazaCollection 
         * @desc: Repair item 
         * @updated: 20/06/10 10:40:56 
         * @version: 1.0 
         */ 
       public function RepairItem(&$item){ 
            if (!isset($item->fecha)) $item->fecha = null; 
            if (!isset($item->usuario)) $item->usuario = null; 
            if (!isset($item->accion)) $item->accion = null; 
            if (!isset($item->ip)) $item->ip = null; 
       } 
       /** 
         * @name: Load 
         * @desc: Load somes objects to memory collection 
         * @updated: 20/06/10 10:40:56 
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
                 $obj = new Traza($item->fecha,$item->usuario,$item->accion,$item->ip,false, $this->data, false);
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
  } 
?>