<?php 
 /** 
   * @name: Combobox of CancelarGenerador 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-CancelarGenerador";
 if (!isset($name)) $name = "combo-CancelarGenerador";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-cancelar", $default, "Cancelar", true);
?>