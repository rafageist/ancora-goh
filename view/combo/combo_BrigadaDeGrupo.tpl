<?php 
 /** 
   * @name: Combobox of BrigadaDeGrupo 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-BrigadaDeGrupo";
 if (!isset($name)) $name = "combo-BrigadaDeGrupo";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-brigada_de_grupo", $default, "Brigada de grupo", true);
?>