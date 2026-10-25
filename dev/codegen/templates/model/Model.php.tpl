`<!--{ Data access class of one table: <Class>Model extends PartModel.
     Every value is a bound parameter ($1, $2, ...) converted by
     PartModel::sqlValue(); $where/$set are trusted SQL fragments whose
     values go in $params (#35). }-->`<?php`{\s}`
   class `{$classname}`Model extends PartModel {`{\s}`
`{\s}`
          public function __construct($db){`{\s}`
                $this->db = $db;`{\s}`
          }`{\s}`
         /**`{\s}`
           * @name: `{$tname}``{\s}`
           * @desc: Work with table "`{$tdesc}`"`{\s}`
           * @updated: `{$updated}``{\s}`
           * @version: 1.0`{\s}`
           */`{\s}`
          /**`{\s}`
            * @name: length`{\s}`
            * @desc: Get count of "`{$tname}`"`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function length($fieldcount="id"){`{\s}`
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM `{$tname}`; ");`{\s}`
              if (isset($r[0])) return $r[0]["c"];
              return 0;`{\s}`
         }`?$hasBool`
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){`[$boolFields]`
                  if (isset($obj->`{$value}`)) {if ($obj->`{$value}` == "f") $obj->`{$value}` = false; else $obj->`{$value}` = true; }`[/$boolFields]`
             } else {`[$boolFields]`
                  if (isset($obj["`{$value}`"])) {if ($obj["`{$value}`"] == "f") $obj["`{$value}`"] = false; else $obj["`{$value}`"] = true; }`[/$boolFields]`
             }
             return $obj;
          }`$hasBool?`
          /**`{\s}`
            * @name: add`{\s}`
            * @desc: Add "`{$tname}`"`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function add(`[$addParams]`$`{$value}``!$_is_last`,`$_is_last!``[/$addParams]`){`{\s}`
             $replicas = array();`[$replicaFields]`
             $`{$value}` = `{$rt.replica_function}`("`{$tname}`");
             $replicas["`{$value}`"] = $`{$value}`;`[/$replicaFields]`
             $r = $this->getAnythingParams("INSERT INTO `{$tname}` (`[$sqlFields]``{$value}``!$_is_last`,`$_is_last!``[/$sqlFields]`) VALUES (`[$sqlFields]`$`{$_order}``!$_is_last`,`$_is_last!``[/$sqlFields]`);", array(`[$insertBinds]``?$md5``{$rt.password_hash}`($`{$name}`)`@else@`PartModel::sqlValue($`{$name}`, "`{$kind}`")`$md5?``!$_is_last`,`{\s}``$_is_last!``[/$insertBinds]`));
             return $replicas;`{\s}`
            }`?$hasPk`
          /**`{\s}`
            * @name: remove`{\s}`
            * @desc: Remove "`{$tname}`"`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function remove(`[$pk]`$`{$value}``!$_is_last`,`$_is_last!``[/$pk]`){`{\s}`
                  return $this->getAnythingParams("DELETE FROM `{$tname}` WHERE `[$pkWhere]``{$col}` = $`{$n}``!$_is_last``{\s}`AND`{\s}``$_is_last!``[/$pkWhere]`;", array(`[$pkBinds]`PartModel::sqlValue($`{$col}`, "`{$kind}`")`!$_is_last`,`{\s}``$_is_last!``[/$pkBinds]`));
            }`$hasPk?`
          /**`{\s}`
            * @name: remove`{\s}`
            * @desc: Remove '`{$tname}`' by ...`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM `{$tname}` WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM `{$tname}` WHERE $where;",$asobjects);
          }`{\s}`
          /**`{\s}`
            * @name: clean`{\s}`
            * @desc: Clean '`{$tname}`'`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function clean($asobjects = false){`{\s}`
               return $this->getAnything("DELETE FROM `{$tname}`", $asobjects);          }`{\s}`
          /**`{\s}`
            * @name: set`{\s}`
            * @desc: Set '`{$tname}`'`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE `{$tname}` SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE `{$tname}` SET $set;",$asobjects);
          }`{\s}`
          /**`{\s}`
            * @name: set`{\s}`
            * @desc: Set '`{$tname}`' by ...`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE `{$tname}` SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE `{$tname}` SET $set WHERE $where;");
          }`{\s}``?$hasPk`
          /**`{\s}`
            * @name: get`{\s}`
            * @desc: Get specified "`{$tname}`"`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function get(`[$pk]`$`{$value}``!$_is_last`,`$_is_last!``[/$pk]`){`{\s}`
                  $r = $this->getAnythingParams("SELECT * FROM `{$tname}` WHERE `[$pkWhere]``{$col}` = $`{$n}``!$_is_last``{\s}`AND`{\s}``$_is_last!``[/$pkWhere]`;", array(`[$pkBinds]`PartModel::sqlValue($`{$col}`, "`{$kind}`")`!$_is_last`,`{\s}``$_is_last!``[/$pkBinds]`), true);`?$hasBool`
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;`@else@`
                   if (isset($r[0])) return $r[0]; else return false;`$hasBool?`
            }`$hasPk?`
          /**`{\s}`
            * @name: getAll`{\s}`
            * @desc: Get all '`{$tname}`'`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM `{$tname}`;",$asobjects);`?$hasBool`
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);`$hasBool?`
               return $r;
          }`{\s}`
          /**`{\s}`
            * @name: getAllBy`{\s}`
            * @desc: Get '`{$tname}`' by ...`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM `{$tname}` WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);`?$hasBool`
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);`$hasBool?`
               return $r;
          }`{\s}`
          /**`{\s}`
            * @name: getLimit`{\s}`
            * @desc: Limited get '`{$tname}`'`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM `{$tname}` LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);`?$hasBool`
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);`$hasBool?`
               return $r;
          }`{\s}`
          /**`{\s}`
            * @name: getLimitBy`{\s}`
            * @desc: Limited get '`{$tname}`' by ...`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM `{$tname}` WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);`?$hasBool`
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);`$hasBool?`
               return $r;
          }`{\s}``[$joins]`
          /**`{\s}`
            * @name: getJoin`{\s}`
            * @desc: Inner join between "`{$tname}`" and "`{$tforeign}`"`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function `{$fname}`($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM `{$tname}` INNER JOIN `{$tforeign}` ON `[$on]``{$tname}`.`{$local}` = `{$tforeign}`.`{$foreign}``!$_is_last``{\s}`AND`{\s}``$_is_last!``[/$on]`;", $asobjects);`?$hasBool`
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);`$hasBool?`
             return $r;`{\s}`
          }`{\s}``[/$joins]``[$joins]`
          /**`{\s}`
            * @name: getJoin`{\s}`
            * @desc: Inner join between "`{$tname}`" and "`{$tforeign}` by ..."`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function `{$fname}`By($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM `{$tname}` INNER JOIN `{$tforeign}` ON `[$on]``{$tname}`.`{$local}` = `{$tforeign}`.`{$foreign}``!$_is_last``{\s}`AND`{\s}``$_is_last!``[/$on]` WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);`?$hasBool`
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);`$hasBool?`
             return $r;`{\s}`
          }`{\s}``[/$joins]``[$joins]`
          /**`{\s}`
            * @name: getJoin`{\s}`
            * @desc: Inner join between "`{$tname}`" and "`{$tforeign}`, by phrase ..."`{\s}`
            * @updated: `{$updated}``{\s}`
            * @version: 1.0`{\s}`
            */`{\s}`
          public function `{$fname}`ByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "`[$phraseColumns]``{$value}``!$_is_last`,`$_is_last!``[/$phraseColumns]`";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM `{$tname}` INNER JOIN `{$tforeign}` ON `[$on]``{$tname}`.`{$local}` = `{$tforeign}`.`{$foreign}``!$_is_last``{\s}`AND`{\s}``$_is_last!``[/$on]` WHERE ($where) AND ($phrasepart);", $params, $asobjects);`?$hasBool`
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);`$hasBool?`
             return $r;`{\s}`
          }`{\s}``[/$joins]`
    }`{\s}`
?>
