<?php 
   class RestriccionDeOtroRecursoCollection{ 
       var $data;  
       var $collection;  
       var $last_offest;  
       var $last_limit;  
       /** 
         * @name: RestriccionDeOtroRecursoCollection 
         * @desc: Class constructor 
         * @updated: 20/06/10 10:40:56 
         * @version: 1.0 
         */ 
       public function RestriccionDeOtroRecursoCollection($db = false){ 
              if ($db !=false) $this->data = new RestriccionDeOtroRecursoModel($db); 
       } 
       /** 
         * @name: RestriccionDeOtroRecursoCollection 
         * @desc: Repair item 
         * @updated: 20/06/10 10:40:56 
         * @version: 1.0 
         */ 
       public function RepairItem(&$item){ 
            if (!isset($item->id)) $item->id = null; 
            if (!isset($item->otro_recurso)) $item->otro_recurso = null; 
            if (!isset($item->periodo)) $item->periodo = null; 
            if (!isset($item->dia)) $item->dia = null; 
            if (!isset($item->turno)) $item->turno = null; 
            if (!isset($item->valor)) $item->valor = null; 
            if (!isset($item->por_actividad)) $item->por_actividad = null; 
            if (!isset($item->actividad)) $item->actividad = null; 
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
                 $obj = new RestriccionDeOtroRecurso($item->otro_recurso,$item->periodo,$item->dia,$item->turno,$item->valor,$item->por_actividad,$item->actividad,$item->id,false, $this->data, false);
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
       public function getItem($id){ 
             $r = $this->data->get($id);
             // $r is a value object (VO)
             $obj = false;
             if ($r) $obj = new RestriccionDeOtroRecurso($r->otro_recurso,$r->periodo,$r->dia,$r->turno,$r->valor,$r->por_actividad,$r->actividad,$r->id,false, $this->data);
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
       public function removeItem($id){ 
             $this->data->remove($id);
       } 
       /** 
         * @name: remove 
         * @desc: Remove a specific object in collection by index 
         * @updated: 20/06/10 10:40:56 
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