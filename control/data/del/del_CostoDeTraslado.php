<?php 

/** 
  * @name: Receptor of CostoDeTraslado identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.CostoDeTrasladoModel"); 
import("model.data.CostoDeTraslado"); 
import("model.data.CostoDeTrasladoCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_CostoDeTraslado_origen.php")) include "../control/data/fdms/fdm_CostoDeTraslado_origen.php"; 
   if (file_exists("../control/data/fdms/fdm_CostoDeTraslado_destino.php")) include "../control/data/fdms/fdm_CostoDeTraslado_destino.php"; 
   if (file_exists("../control/data/fdms/fdm_CostoDeTraslado_costo.php")) include "../control/data/fdms/fdm_CostoDeTraslado_costo.php"; 
   if (isset($_POST["field_CostoDeTraslado_origen"])) $origen = $_POST["field_CostoDeTraslado_origen"]; else $origen = null;
   if (isset($_POST["field_CostoDeTraslado_destino"])) $destino = $_POST["field_CostoDeTraslado_destino"]; else $destino = null;
   if (isset($_POST["field_CostoDeTraslado_costo"])) $costo = $_POST["field_CostoDeTraslado_costo"]; else $costo = null;

 // Eliminando el objeto 
   $col = new CostoDeTrasladoCollection($dbconnection);
   $col->removeItem($origen,$destino);
?>