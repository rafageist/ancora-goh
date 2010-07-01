<?php 
 /** 
   * @name: Combobox of RestriccionRealDeClasifAct 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionRealDeClasifAct";
 if (!isset($name)) $name = "combo-RestriccionRealDeClasifAct";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_real_de_clasif_act", $default, "Restricci&oacute;n real de clasif act", true);
?>