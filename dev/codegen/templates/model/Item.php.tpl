`<!--{ Item class of one table (one row as an object, with a setter that
     updates the column and a getter that reloads it). The add<Table>() /
     get<Table>() pairs come from the foreign keys of other tables that
     reference this one. }-->`<?php`{\s}`
   class `{$classname}`{`{\s}``[$fields]`
       var $`{$name}`;`{\s}``[/$fields]`
       var $data;`{\s}``{\s}`
          /**`{\s}`
            * @name: `{$classname}``{\s}`
            * @desc: Class constructor`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
       public function __construct(`[$addParams]`$`{$value}``!$_is_last`,`$_is_last!``[/$addParams]`,`[$replicaFields]`$`{$value}` = '',`[/$replicaFields]`$construct = true, $data = false, $load = true){`{\s}`
              global `{$rt.db_global}`;
              if ($data == false) $this->data = new `{$classname}`Model(`{$rt.db_global}`); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos`{\s}`
              if ($construct == true) {`{\s}`
                  $replicas = $this->data->add(`[$addParams]`$`{$value}``!$_is_last`,`$_is_last!``[/$addParams]`);`{\s}``[$fields]`
                    $this->`{$name}` = `?$replica`$replicas["`{$name}`"]`@else@`$`{$name}``$replica?`;`{\s}``[/$fields]`
              }`{\s}`
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get(`[$pk]`$`{$value}``!$_is_last`,`$_is_last!``[/$pk]`);`{\s}``[$fields]`
                  $this->`{$name}` = $r->`{$name}`;`[/$fields]`
              }`{\s}`
              else { // construir el objeto a partir de los parametros`[$fields]`
                  $this->`{$name}` = $`{$name}`;`[/$fields]`
              }
       }`{\s}``[$fields]``!$replica`
          /**`{\s}`
            * @name: set`{$cap}``{\s}`
            * @desc: Set value of property "`{$cap}`"`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
       public function set`{$cap}`($value){`{\s}``?$md5`
              if (!is_string($value) || $value === '') return false;`{\s}`
              $value = `{$rt.password_hash}`($value);`{\s}``$md5?`
              $this->`{$name}` = $value;`{\s}`
              $this->data->getAnythingParams("UPDATE `{$tname}` SET `{$name}` = $1 WHERE `[$pkSet]``{$col}` = $`{$n}``!$_is_last``{\s}`AND`{\s}``$_is_last!``[/$pkSet]`;", array(`?$md5`$value`@else@`PartModel::sqlValue($value, "`{$kind}`")`$md5?`, `[$pkBindsThis]`PartModel::sqlValue($this->`{$col}`, "`{$kind}`")`!$_is_last`,`{\s}``$_is_last!``[/$pkBindsThis]`));`{\s}`
       }`{\s}``$replica!`
          /**`{\s}`
            * @name: get`{$cap}``{\s}`
            * @desc: Get value of property "`{$cap}`"`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
       public function get`{$cap}`(){`{\s}`
              $r = $this->data->get(`[$pk]`$this->`{$value}``!$_is_last`,`$_is_last!``[/$pk]`);`{\s}`
              $this->`{$name}` = $r->`{$name}`;`{\s}`
              return $this->`{$name}`;
       }`{\s}``[/$fields]``[$referencedBy]`
       public function add`{$suffix}`(`[$params]`$`{$value}``!$_is_last`,`$_is_last!``[/$params]`){`{\s}``[$assigns]`
              $`{$local}` = $this->`{$foreign}`;`{\s}``[/$assigns]`
              $obj = new `{$suffix}`Class(`[$realParams]`$`{$value}``!$_is_last`,`$_is_last!``[/$realParams]`,`[$replicaFields]`"",`[/$replicaFields]`true,false);`{\s}`
              return $obj;
       }`{\s}`
       public function get`{$suffix}`(){`{\s}`
          $collection = new `{$suffix}`Collection($this->data->db);
          $collection->Load(null,null,"*","`[$conds]``{$local}` = $`{$_order}``!$_is_last``{\s}`AND`{\s}``$_is_last!``[/$conds]`", array(`[$conds]`PartModel::sqlValue($this->`{$foreign}`, "`{$kind}`")`!$_is_last`,`{\s}``$_is_last!``[/$conds]`));
           return $collection;
       }`{\s}``[/$referencedBy]`
}`{\s}`
?>
