<?php 
 /** 
   * @name: Combobox of RolDeUsuario 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RolDeUsuario";
 if (!isset($name)) $name = "combo-RolDeUsuario";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-rol_de_usuario", $default, "Rol de usuario", true);
?>