<?php 
 /** 
   * @name: Combobox of RestriccionDeClasifAct 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionDeClasifAct";
 if (!isset($name)) $name = "combo-RestriccionDeClasifAct";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_de_clasif_act", $default, "Restricci&oacute;n de clasif act", true);
?>