`<!--{ Wrappers of the database functions of one schema; the arguments are
     bound parameters (#35). The file has no closing PHP tag. }-->`<?php`{\s}`
    /**`{\s}`
      * @name: `{$schema}`Functions`{\s}`
      * @desc: Functions of schema `{$schema}``{\s}`
      * @updated: `{$updated}``{\s}`
      * @version: 1.0`{\s}`
      */`{\s}`
     class `{$class}` extends PartModel{`{\s}`
        var $db;`{\s}`
          /**`{\s}`
            * @name: `{$schema}`Functions`{\s}`
            * @desc: Constructor`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function __construct($db){`{\s}`
               $this->db = $db;`{\s}``{\s}`
          }`[$functions]`
          /**`{\s}`
            * @name: `{$name}``{\s}`
            * @desc: Function that ...`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function `{$method}`(`[$params]`$`{$name}`,`[/$params]`$asobjects = false){`{\s}`
               $sql = "select `?$rowset`* from`$rowset?``{\s}``{$qname}`(`[$params]`$`{$_order}``!$_is_last`,`$_is_last!``[/$params]`)";`{\s}`
               return $this->getAnythingParams($sql, array(`[$params]`PartModel::sqlValue($`{$name}`, "`{$kind}`")`!$_is_last`,`{\s}``$_is_last!``[/$params]`), $asobjects);
           }`{\s}``[/$functions]`
     }`{\s}`

