`<!--{ Collection class of one table: loads rows as item objects. Load()
     keeps its arguments so that Reload() repeats the same query (#44). }-->`<?php`{\s}`
   class `{$collectionname}`{`{\s}`
       var $data;`{\s}``{\s}`
       var $collection;`{\s}``{\s}`
       var $last_offset;`{\s}``{\s}`
       var $last_limit;`{\s}``{\s}`
       var $last_fields = "*";`{\s}``{\s}`
       var $last_where = "";`{\s}``{\s}`
       var $last_params;`{\s}``{\s}`
       /**`{\s}`
         * @name: `{$collectionname}``{\s}`
         * @desc: Class constructor`{\s}`
         * @updated: `{$updated}``{\s}`
         * @version: 1.0`{\s}`
         */`{\s}`
       public function __construct($db = false){`{\s}`
              if ($db !=false) $this->data = new `{$classname}`Model($db);`{\s}`
       }`{\s}`
       /**`{\s}`
         * @name: `{$collectionname}``{\s}`
         * @desc: Repair item`{\s}`
         * @updated: `{$updated}``{\s}`
         * @version: 1.0`{\s}`
         */`{\s}`
       public function RepairItem(&$item){`{\s}``[$sqlFields]`
            if (!isset($item->`{$value}`)) $item->`{$value}` = null;`{\s}``[/$sqlFields]`
       }`{\s}`
       /**`{\s}`
         * @name: Load`{\s}`
         * @desc: Load somes objects to memory collection`{\s}`
         * @updated: `{$updated}``{\s}`
         * @version: 1.0`{\s}`
         */`{\s}`
       public function Load($offset = null, $limit = null, $fields = "*", $where = "", $params = null ){`{\s}`
             $this->last_offset = $offset; $this->last_limit = $limit;
             $this->last_fields = $fields; $this->last_where = $where; $this->last_params = $params;
             if ($offset === null || $limit === null) {
                 if ($where == "")
                     $r = $this->data->getAll("*",true);
                 else`{\s}`
                     $r = $this->data->getAllBy($where, "*",true, $params);
             }
             else {
                 if ($where == "")
                  $r = $this->data->getLimit($limit,$offset,$fields,true);
                else`{\s}`
                  $r = $this->data->getLimitBy($limit,$offset,$where,$fields,true, $params);
             }
             // $r is a collection of value objects (VO)

             foreach ($r as $item){
                 $this->RepairItem($item);
                 $obj = new `{$classname}`(`[$ctorFields]`$item->`{$value}`,`[/$ctorFields]`false, $this->data, false);
                 $this->collection[] = $obj;
             }`{\s}`
       }`{\s}`
       /**`{\s}`
         * @name: Reload`{\s}`
         * @desc: Reload objects to memory collection with last offset and limit`{\s}`
         * @updated: `{$updated}``{\s}`
         * @version: 1.0`{\s}`
         */`{\s}`
       public function Reload(){`{\s}`
            $this->collection = array();`{\s}`
            $this->Load($this->last_offset, $this->last_limit, $this->last_fields, $this->last_where, $this->last_params);`{\s}`
       }`{\s}``?$hasPk`
       /**`{\s}`
         * @name: getItem`{\s}`
         * @desc: Load a specific object by primary keys`{\s}`
         * @updated: `{$updated}``{\s}`
         * @version: 1.0`{\s}`
         */`{\s}`
       public function getItem(`[$pk]`$`{$value}``!$_is_last`,`$_is_last!``[/$pk]`){`{\s}`
             $r = $this->data->get(`[$pk]`$`{$value}``!$_is_last`,`$_is_last!``[/$pk]`);
             // $r is a value object (VO)
             $obj = false;
             if ($r) $obj = new `{$classname}`(`[$ctorFields]`$r->`{$value}`,`[/$ctorFields]`false, $this->data);
              return $obj;
       }`{\s}``$hasPk?`
       /**`{\s}`
         * @name: get`{\s}`
         * @desc: Load a specific object in collection by index`{\s}`
         * @updated: `{$updated}``{\s}`
         * @version: 1.0`{\s}`
         */`{\s}`
       public function get($idx){`{\s}`
             if (isset($this->collection[$idx]))`{\s}``{\s}`
                  return $this->collection[$idx];
             return null;`{\s}`
       }`{\s}``?$hasPk`
       /**`{\s}`
         * @name: removeItem`{\s}`
         * @desc: Remove a specific object by primary key`{\s}`
         * @updated: `{$updated}``{\s}`
         * @version: 1.0`{\s}`
         */`{\s}`
       public function removeItem(`[$pk]`$`{$value}``!$_is_last`,`$_is_last!``[/$pk]`){`{\s}`
             $this->data->remove(`[$pk]`$`{$value}``!$_is_last`,`$_is_last!``[/$pk]`);
       }`{\s}`
       /**`{\s}`
         * @name: remove`{\s}`
         * @desc: Remove a specific object in collection by index`{\s}`
         * @updated: `{$updated}``{\s}`
         * @version: 1.0`{\s}`
         */`{\s}`
       public function remove($idx){`{\s}`
             if (isset($this->collection[$idx])){`{\s}``{\s}`
                  $this->removeItem(`[$pkCaps]`$this->collection[$idx]->get`{$value}`()`!$_is_last`,`$_is_last!``[/$pkCaps]`);
                  unset($this->collection[$idx]);
                  return true;
              }`{\s}`
              return false;`{\s}`
        }`{\s}``$hasPk?`
  }`{\s}`
?>
