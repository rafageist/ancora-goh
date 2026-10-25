`<!--{ UPDATE handler of one table (control/data/set/set_<Class>.php). A
     field that was not posted is null and left unchanged (#35); a password
     (md5 field) changes only when a new one was posted (#14). }-->`<?php`{\s}`

/**`{\s}`
  * @name: Receptor of `{$classname}` data from POST for UPDATE`{\s}`
  * @desc: Part Control of MVC Pattern`{\s}`
  * @updated: `{$updated}``{\s}`
  * @version: 1.0`{\s}`
  */`{\s}`

import("`{$package}`.`{$classname}`Model");`{\s}`
import("`{$package}`.`{$classname}`");`{\s}`
import("`{$package}`.`{$classname}`Collection");`{\s}`

// Execute fields's modification data`{\s}``[$plainFields]`
if (file_exists("`{$rt.fdm_path}`/fdm_`{$classname}`_`{$value}`.php")) include "`{$rt.fdm_path}`/fdm_`{$classname}`_`{$value}`.php";`{\s}``[/$plainFields]`

// Getting data from POST`{\s}``[$allFields]`
   if (isset($_POST["field_`{$classname}`_`{$value}`"])) $`{$value}` = $_POST["field_`{$classname}`_`{$value}`"]; else $`{$value}` = null;`[/$allFields]`

 // Actualizando el objeto`{\s}`
   $col = new `{$classname}`Collection(`{$rt.db_global}`);
   $obj = $col->getItem(`[$pk]`$`{$value}``!$_is_last`,`$_is_last!``[/$pk]`);`[$setters]``?$md5`
// #14: only change the password when a new one was posted; the "null"
// placeholder above must not become a password (it is hashed now)
if (isset($_POST["field_`{$classname}`_`{$name}`"]) && is_string($_POST["field_`{$classname}`_`{$name}`"]) && $_POST["field_`{$classname}`_`{$name}`"] !== '') $obj->set`{$cap}`($`{$name}`);`$md5?``!$md5`
if ($`{$name}` !== null) $obj->set`{$cap}`($`{$name}`);`$md5!``[/$setters]`
?>
