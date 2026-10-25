<?php 
   class HigieneParaProfesorCollection{ 
       var $data;  
       var $collection;  
       var $last_offset;  
       var $last_limit;  
       var $last_fields = "*";  
       var $last_where = "";  
       var $last_params;  
       /** 
         * @name: HigieneParaProfesorCollection 
         * @desc: Class constructor 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function __construct($db = false){ 
              if ($db !=false) $this->data = new HigieneParaProfesorModel($db); 
       } 
       /** 
         * @name: HigieneParaProfesorCollection 
         * @desc: Repair item 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function RepairItem(&$item){ 
            if (!isset($item->periodo)) $item->periodo = null; 
            if (!isset($item->profesor)) $item->profesor = null; 
            if (!isset($item->gasto_maximo_diario)) $item->gasto_maximo_diario = null; 
       } 
       /** 
         * @name: Load 
         * @desc: Load somes objects to memory collection 
         * @updated: 20/06/10 10:40:55 
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
                 $obj = new HigieneParaProfesor($item->periodo,$item->profesor,$item->gasto_maximo_diario,false, $this->data, false);
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
            $this->collection = array(); 
            $this->Load($this->last_offset, $this->last_limit, $this->last_fields, $this->last_where, $this->last_params); 
       } 
       /** 
         * @name: getItem 
         * @desc: Load a specific object by primary keys 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function getItem($periodo,$profesor,$gasto_maximo_diario){ 
             $r = $this->data->get($periodo,$profesor,$gasto_maximo_diario);
             // $r is a value object (VO)
             $obj = false;
             if ($r) $obj = new HigieneParaProfesor($r->periodo,$r->profesor,$r->gasto_maximo_diario,false, $this->data);
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
       public function removeItem($periodo,$profesor,$gasto_maximo_diario){ 
             $this->data->remove($periodo,$profesor,$gasto_maximo_diario);
       } 
       /** 
         * @name: remove 
         * @desc: Remove a specific object in collection by index 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function remove($idx){ 
             if (isset($this->collection[$idx])){  
                  $this->removeItem($this->collection[$idx]->getPeriodo(),$this->collection[$idx]->getProfesor(),$this->collection[$idx]->getGastoMaximoDiario());
                  unset($this->collection[$idx]);
                  return true;
              } 
              return false; 
        } 
  } 
?>