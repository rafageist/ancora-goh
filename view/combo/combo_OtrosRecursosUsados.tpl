<?php 
 /** 
   * @name: Combobox of OtrosRecursosUsados 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-OtrosRecursosUsados";
 if (!isset($name)) $name = "combo-OtrosRecursosUsados";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-otros_recursos_usados", $default, "Otros recursos usados", true);
?>