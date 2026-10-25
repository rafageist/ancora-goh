<?php 
   class ActividadCollection{ 
       var $data;  
       var $collection;  
       var $last_offset;  
       var $last_limit;  
       var $last_fields = "*";  
       var $last_where = "";  
       var $last_params;  
       /** 
         * @name: ActividadCollection 
         * @desc: Class constructor 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function __construct($db = false){ 
              if ($db !=false) $this->data = new ActividadModel($db); 
       } 
       /** 
         * @name: ActividadCollection 
         * @desc: Repair item 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function RepairItem(&$item){ 
            if (!isset($item->id)) $item->id = null; 
            if (!isset($item->plantilla)) $item->plantilla = null; 
            if (!isset($item->grupo)) $item->grupo = null; 
            if (!isset($item->profesor)) $item->profesor = null; 
            if (!isset($item->lugar)) $item->lugar = null; 
            if (!isset($item->dia)) $item->dia = null; 
            if (!isset($item->turno)) $item->turno = null; 
            if (!isset($item->momento_de_colocacion)) $item->momento_de_colocacion = null; 
            if (!isset($item->creador)) $item->creador = null; 
       } 
       /** 
         * @name: Load 
         * @desc: Load somes objects to memory collection 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function Load($offset = null, $limit = null, $fields = "*", $where = "", $params = null ){ 
             $this->last_offset = $offset; $this->last_limit = $limit;
             $this->last_fields = $fields; $this->last_where = $where; $this->last_params = $params;
             if ($offset === null || $limit === null) {
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
                 $obj = new Actividad($item->plantilla,$item->grupo,$item->profesor,$item->lugar,$item->dia,$item->turno,$item->momento_de_colocacion,$item->creador,$item->id,false, $this->data, false);
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
            $this->collection = array(); 
            $this->Load($this->last_offset, $this->last_limit, $this->last_fields, $this->last_where, $this->last_params); 
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
             if ($r) $obj = new Actividad($r->plantilla,$r->grupo,$r->profesor,$r->lugar,$r->dia,$r->turno,$r->momento_de_colocacion,$r->creador,$r->id,false, $this->data);
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