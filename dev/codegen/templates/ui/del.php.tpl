`<!--{ DELETE handler of one table (control/data/del/del_<Class>.php). }-->`<?php`{\s}`

/**`{\s}`
  * @name: Receptor of `{$classname}` identification from POST for DELETE`{\s}`
  * @desc: Part Control of MVC Pattern`{\s}`
  * @updated: `{$updated}``{\s}`
  * @version: 1.0`{\s}`
  */`{\s}`
import("`{$package}`.`{$classname}`Model");`{\s}`
import("`{$package}`.`{$classname}`");`{\s}`
import("`{$package}`.`{$classname}`Collection");`{\s}`

// Tomando los valores del POST`{\s}``[$plainFields]`
   if (file_exists("`{$rt.fdm_path}`/fdm_`{$classname}`_`{$value}`.php")) include "`{$rt.fdm_path}`/fdm_`{$classname}`_`{$value}`.php";`{\s}``[/$plainFields]``[$allFields]`
   if (isset($_POST["field_`{$classname}`_`{$value}`"])) $`{$value}` = $_POST["field_`{$classname}`_`{$value}`"]; else $`{$value}` = null;`[/$allFields]`

 // Eliminando el objeto`{\s}`
   $col = new `{$classname}`Collection(`{$rt.db_global}`);
   $col->removeItem(`[$pk]`$`{$value}``!$_is_last`,`$_is_last!``[/$pk]`);
?>
