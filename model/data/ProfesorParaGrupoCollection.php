<?php 
   class ProfesorParaGrupoCollection{ 
       var $data;  
       var $collection;  
       var $last_offest;  
       var $last_limit;  
       /** 
         * @name: ProfesorParaGrupoCollection 
         * @desc: Class constructor 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function ProfesorParaGrupoCollection($db = false){ 
              if ($db !=false) $this->data = new ProfesorParaGrupoModel($db); 
       } 
       /** 
         * @name: ProfesorParaGrupoCollection 
         * @desc: Repair item 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function RepairItem(&$item){ 
            if (!isset($item->profesor_para_actividad)) $item->profesor_para_actividad = null; 
            if (!isset($item->grupo)) $item->grupo = null; 
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
                 $obj = new ProfesorParaGrupo($item->profesor_para_actividad,$item->grupo,false, $this->data, false);
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
       public function getItem($profesor_para_actividad,$grupo){ 
             $r = $this->data->get($profesor_para_actividad,$grupo);
             // $r is a value object (VO)
             $obj = false;
             if ($r) $obj = new ProfesorParaGrupo($r->profesor_para_actividad,$r->grupo,false, $this->data);
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
       public function removeItem($profesor_para_actividad,$grupo){ 
             $this->data->remove($profesor_para_actividad,$grupo);
       } 
       /** 
         * @name: remove 
         * @desc: Remove a specific object in collection by index 
         * @updated: 20/06/10 10:40:55 
         * @version: 1.0 
         */ 
       public function remove($idx){ 
             if (isset($this->collection[$idx])){  
                  $this->removeItem($this->collection[$idx]->getProfesorParaActividad(),$this->collection[$idx]->getGrupo());
                  unset($this->collection[$idx]);
                  return true;
              } 
              return false; 
        } 
  } 
?>