`<!--{ Controller of the combobox view of one table
     (control/view/combo/get_ui_combo_<Class>.php). The option key is the
     primary key ("{k1,k2}" when it has several columns) or the row number;
     the option text is the "descripcion" column unless the profile gives a
     table description. }-->`<?php`{\s}`
 /**`{\s}`
   * @name: Combobox views of `{$classname}``{\s}`
   * @desc: Part Control of MVC pattern`{\s}`
   * @updated: `{$updated}``{\s}`
   * @version: 1.0`{\s}`
   */`{\s}`

 import ("`{$package}`.`{$classname}`Model");
 import ("`{$package}`.`{$classname}`");
 import ("`{$package}`.`{$classname}`Collection");

 $collection = new `{$classname}`Collection(`{$rt.db_global}`);
 $collection->Load();

 if (isset($_POST["id"])) $indentity = $_POST["id"];
 if (isset($_POST["name"])) $name = $_POST["name"];
 if (isset($_POST["default"])) $default = $_POST["default"];

 $options = array();`!$hasPk`
 $i = 0;`$hasPk!`
 foreach($collection->collection as $item){`!$hasPk`
 $i++;`$hasPk!`
     $options[`!$hasPk`$i`$hasPk!``?$compositePk`'{'.`$compositePk?``[$pk]`$item->`{$value}``!$_is_last`.','.`$_is_last!``[/$pk]``?$compositePk`.'}'`$compositePk?`] = `?$description`'`[$description]``?$field`'.$item->`{$text}`.'`$field?``!$field``{$text}``$field!``[/$description]`'`$description?``!$description`(isset($item->descripcion)?$item->descripcion:"")`$description!`;
 }

 if (isset($_POST["forma"])){`{\s}`
   switch($_POST["forma"]){
     case "combo":
       include "`{$rt.view_path}`/combo/combo_`{$classname}`.tpl";
       break;
   }
 }
?>`{\s}`

