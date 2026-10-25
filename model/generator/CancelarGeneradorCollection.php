<?php 
   class CancelarGeneradorCollection{ 
       var $data;  
       var $collection;  
       var $last_offest;  
       var $last_limit;  
       /** 
         * @name: CancelarGeneradorCollection 
         * @desc: Class constructor 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function __construct($db = false){ 
              if ($db !=false) $this->data = new CancelarGeneradorModel($db); 
       } 
       /** 
         * @name: CancelarGeneradorCollection 
         * @desc: Repair item 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function RepairItem(&$item){ 
            if (!isset($item->especialidad)) $item->especialidad = null; 
            if (!isset($item->nivel)) $item->nivel = null; 
            if (!isset($item->periodo)) $item->periodo = null; 
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
                 $obj = new CancelarGenerador($item->especialidad,$item->nivel,$item->periodo,false, $this->data, false);
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
       public function getItem($especialidad,$nivel,$periodo){ 
             $r = $this->data->get($especialidad,$nivel,$periodo);
             // $r is a value object (VO)
             $obj = false;
             if ($r) $obj = new CancelarGenerador($r->especialidad,$r->nivel,$r->periodo,false, $this->data);
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
       public function removeItem($especialidad,$nivel,$periodo){ 
             $this->data->remove($especialidad,$nivel,$periodo);
       } 
       /** 
         * @name: remove 
         * @desc: Remove a specific object in collection by index 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function remove($idx){ 
             if (isset($this->collection[$idx])){  
                  $this->removeItem($this->collection[$idx]->getEspecialidad(),$this->collection[$idx]->getNivel(),$this->collection[$idx]->getPeriodo());
                  unset($this->collection[$idx]);
                  return true;
              } 
              return false; 
        } 
  } 
?>