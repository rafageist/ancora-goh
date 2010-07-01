<?php 
 /** 
   * @name: Combobox of ProfesorParaGrupo 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-ProfesorParaGrupo";
 if (!isset($name)) $name = "combo-ProfesorParaGrupo";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-profesor_para_grupo", $default, "Profesor para grupo", true);
?>