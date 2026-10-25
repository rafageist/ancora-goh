<?php 

/** 
  * @name: Receptor of CostoDeTraslado data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.CostoDeTrasladoModel"); 
import("model.data.CostoDeTraslado"); 
import("model.data.CostoDeTrasladoCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_CostoDeTraslado_origen.php")) include "../control/data/fdms/fdm_CostoDeTraslado_origen.php"; 
if (file_exists("../control/data/fdms/fdm_CostoDeTraslado_destino.php")) include "../control/data/fdms/fdm_CostoDeTraslado_destino.php"; 
if (file_exists("../control/data/fdms/fdm_CostoDeTraslado_costo.php")) include "../control/data/fdms/fdm_CostoDeTraslado_costo.php"; 

// Getting data from POST 
   if (isset($_POST["field_CostoDeTraslado_origen"])) $origen = $_POST["field_CostoDeTraslado_origen"]; else $origen = null;
   if (isset($_POST["field_CostoDeTraslado_destino"])) $destino = $_POST["field_CostoDeTraslado_destino"]; else $destino = null;
   if (isset($_POST["field_CostoDeTraslado_costo"])) $costo = $_POST["field_CostoDeTraslado_costo"]; else $costo = null;

 // Actualizando el objeto 
   $col = new CostoDeTrasladoCollection($dbconnection);
   $obj = $col->getItem($origen,$destino);
if ($origen !== null) $obj->setOrigen($origen);
if ($destino !== null) $obj->setDestino($destino);
if ($costo !== null) $obj->setCosto($costo);
?>