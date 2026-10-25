<?php 
   class HerenciaDeRestriccionesCollection{ 
       var $data;  
       var $collection;  
       var $last_offset;  
       var $last_limit;  
       var $last_fields = "*";  
       var $last_where = "";  
       var $last_params;  
       /** 
         * @name: HerenciaDeRestriccionesCollection 
         * @desc: Class constructor 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function __construct($db = false){ 
              if ($db !=false) $this->data = new HerenciaDeRestriccionesModel($db); 
       } 
       /** 
         * @name: HerenciaDeRestriccionesCollection 
         * @desc: Repair item 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
       public function RepairItem(&$item){ 
            if (!isset($item->id)) $item->id = null; 
            if (!isset($item->planificacion)) $item->planificacion = null; 
            if (!isset($item->activa)) $item->activa = null; 
            if (!isset($item->hijo_brigada)) $item->hijo_brigada = null; 
            if (!isset($item->hijo_profesor)) $item->hijo_profesor = null; 
            if (!isset($item->hijo_lugar)) $item->hijo_lugar = null; 
            if (!isset($item->hijo_otro_recurso)) $item->hijo_otro_recurso = null; 
            if (!isset($item->padre_brigada)) $item->padre_brigada = null; 
            if (!isset($item->padre_profesor)) $item->padre_profesor = null; 
            if (!isset($item->padre_lugar)) $item->padre_lugar = null; 
            if (!isset($item->padre_otro_recurso)) $item->padre_otro_recurso = null; 
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
                 $obj = new HerenciaDeRestricciones($item->planificacion,$item->activa,$item->hijo_brigada,$item->hijo_profesor,$item->hijo_lugar,$item->hijo_otro_recurso,$item->padre_brigada,$item->padre_profesor,$item->padre_lugar,$item->padre_otro_recurso,$item->id,false, $this->data, false);
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
             if ($r) $obj = new HerenciaDeRestricciones($r->planificacion,$r->activa,$r->hijo_brigada,$r->hijo_profesor,$r->hijo_lugar,$r->hijo_otro_recurso,$r->padre_brigada,$r->padre_profesor,$r->padre_lugar,$r->padre_otro_recurso,$r->id,false, $this->data);
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