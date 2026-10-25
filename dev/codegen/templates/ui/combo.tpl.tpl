`<!--{ Combobox view of one table (view/combo/combo_<Class>.tpl). }-->`<?php`{\s}`
 /**`{\s}`
   * @name: Combobox of `{$classname}``{\s}`
   * @desc: Part View of MVC pattern`{\s}`
   * @updated: `{$updated}``{\s}`
   * @version: 1.0`{\s}`
   */`{\s}`

 if (!isset($id)) $id = "combo-`{$classname}`";
 if (!isset($name)) $name = "combo-`{$classname}`";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-`{$name}`", $default, "`{$title}`", true);
?>
