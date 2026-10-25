`<!--{ Service class (dev/services/*.json): one method per named SQL query.
     The "$name" references became bound placeholders (#35). }-->`<?php`{\s}`
      /**`{\s}`
        * @name: `{$description}``{\s}`
        * @desc:`{\s}``{\s}`
        * @updated: `{$updated}``{\s}`
        * @version: 1.0`{\s}`
        */`{\s}`
      class `{$name}`Service extends PartModel{`{\s}`
          var $db;`{\s}`
          /**`{\s}`
            * @name: `{$name}``{\s}`
            * @desc: Constructor`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function `{$name}`($db){`{\s}`
                $this->db = $db;`{\s}``{\s}`
          }
`{\s}``[$methods]`
          /**`{\s}`
            * @name: `{$method}``{\s}`
            * @desc: `{$descrip}``{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          function `{$method}`(`[$params]`$`{$value}`,`[/$params]`$asobjects = false){`{\s}`
               $sql = "`{$sqlLiteral}`";`{\s}`
               return $this->getAnythingParams($sql, array(`[$params]`PartModel::sqlValue($`{$value}`, "text")`!$_is_last`,`{\s}``$_is_last!``[/$params]`), $asobjects);
            }`{\s}``[/$methods]`
    }`{\s}`
?>
