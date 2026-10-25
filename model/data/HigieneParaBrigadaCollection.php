<?php 
   class HigieneParaBrigadaCollection{ 
       var $data;  
       var $collection;  
       var $last_offest;  
       var $last_limit;  
       /** 
         * @name: HigieneParaBrigadaCollection 
         * @desc: Class constructor 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function __construct($db = false){ 
              if ($db !=false) $this->data = new HigieneParaBrigadaModel($db); 
       } 
       /** 
         * @name: HigieneParaBrigadaCollection 
         * @desc: Repair item 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function RepairItem(&$item){ 
            if (!isset($item->periodo)) $item->periodo = null; 
            if (!isset($item->brigada)) $item->brigada = null; 
            if (!isset($item->gasto_maximo_diario)) $item->gasto_maximo_diario = null; 
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
                 $obj = new HigieneParaBrigada($item->periodo,$item->brigada,$item->gasto_maximo_diario,false, $this->data, false);
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
       public function getItem($periodo,$brigada,$gasto_maximo_diario){ 
             $r = $this->data->get($periodo,$brigada,$gasto_maximo_diario);
             // $r is a value object (VO)
             $obj = false;
             if ($r) $obj = new HigieneParaBrigada($r->periodo,$r->brigada,$r->gasto_maximo_diario,false, $this->data);
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
       public function removeItem($periodo,$brigada,$gasto_maximo_diario){ 
             $this->data->remove($periodo,$brigada,$gasto_maximo_diario);
       } 
       /** 
         * @name: remove 
         * @desc: Remove a specific object in collection by index 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function remove($idx){ 
             if (isset($this->collection[$idx])){  
                  $this->removeItem($this->collection[$idx]->getPeriodo(),$this->collection[$idx]->getBrigada(),$this->collection[$idx]->getGastoMaximoDiario());
                  unset($this->collection[$idx]);
                  return true;
              } 
              return false; 
        } 
  } 
?>