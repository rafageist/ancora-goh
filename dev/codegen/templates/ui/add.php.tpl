`<!--{ INSERT handler of one table (control/data/add/add_<Class>.php): runs
     the optional field hooks (fdm_<Class>_<field>.php), reads the posted
     fields and builds the item, which inserts the row. }-->`<?php`{\s}`

/**`{\s}`
  * @name: Receptor of `{$classname}` data from POST for INSERT`{\s}`
  * @desc: Part Control of MVC Pattern`{\s}`
  * @updated: `{$updated}``{\s}`
  * @version: 1.0`{\s}`
  */`{\s}`
import("`{$package}`.`{$classname}`Model");`{\s}`
import("`{$package}`.`{$classname}`");`{\s}`

// Execute fields's modification data`{\s}``[$plainFields]`
   if (file_exists("`{$rt.fdm_path}`/fdm_`{$classname}`_`{$value}`.php")) include "`{$rt.fdm_path}`/fdm_`{$classname}`_`{$value}`.php";`{\s}``[/$plainFields]`

// Getting data from POST`{\s}``[$plainFields]`
   $`{$value}` = $_POST["field_`{$classname}`_`{$value}`"];`[/$plainFields]`

 // Construyendo el objeto`{\s}`
   $obj = new `{$classname}`(`[$plainFields]`$`{$value}`,`[/$plainFields]``[$replicaFields]`"",`[/$replicaFields]`true, null, false);
?>
