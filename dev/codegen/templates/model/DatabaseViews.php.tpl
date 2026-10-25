`<!--{ Read access to the database views of one schema. }-->`<?php`{\s}`
   class `{$class}` extends PartModel {`{\s}`
`{\s}`
       /**`{\s}`
         * @name: `{$class}``{\s}`
         * @desc: Constructor`{\s}`
         * @updated: `{$updated}``{\s}`
         * @version: 1.0`{\s}`
         */`{\s}`
          public function __construct($db){`{\s}`
                $this->db = $db;`{\s}`
          }`{\s}``[$views]`
       /**`{\s}`
         * @name: `{$class}``{\s}`
         * @desc: Constructor`{\s}`
         * @updated: `{$updated}``{\s}`
         * @version: 1.0`{\s}`
         */`{\s}`
           public function get`{$method}`($asobjects = false){`{\s}`
                return $this->getAnything("SELECT * FROM  `{$name}`;", $asobjects);
           }`{\s}``[/$views]`
    }`{\s}`
?>
