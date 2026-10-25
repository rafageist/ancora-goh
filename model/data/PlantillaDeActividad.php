<?php 
   class PlantillaDeActividad{ 
       var $id; 
       var $desglose; 
       var $orden; 
       var $clasificacion; 
       var $exige_profesor; 
       var $exige_lugar; 
       var $data;  
          /** 
            * @name: PlantillaDeActividad 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($desglose,$orden,$clasificacion,$exige_profesor,$exige_lugar,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new PlantillaDeActividadModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($desglose,$orden,$clasificacion,$exige_profesor,$exige_lugar); 
                    $this->id = $replicas["id"]; 
                    $this->desglose = $desglose; 
                    $this->orden = $orden; 
                    $this->clasificacion = $clasificacion; 
                    $this->exige_profesor = $exige_profesor; 
                    $this->exige_lugar = $exige_lugar; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->desglose = $r->desglose;
                  $this->orden = $r->orden;
                  $this->clasificacion = $r->clasificacion;
                  $this->exige_profesor = $r->exige_profesor;
                  $this->exige_lugar = $r->exige_lugar;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->desglose = $desglose;
                  $this->orden = $orden;
                  $this->clasificacion = $clasificacion;
                  $this->exige_profesor = $exige_profesor;
                  $this->exige_lugar = $exige_lugar;
              }
       } 
          /** 
            * @name: getId 
            * @desc: Get value of property "Id" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getId(){ 
              $r = $this->data->get($this->id); 
              $this->id = $r->id; 
              return $this->id;
       } 
          /** 
            * @name: setDesglose 
            * @desc: Set value of property "Desglose" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setDesglose($value){ 
              $this->desglose = $value; 
              $this->data->setBy("id = '".$this->id."'", "desglose = '$value'"); 
       } 
          /** 
            * @name: getDesglose 
            * @desc: Get value of property "Desglose" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getDesglose(){ 
              $r = $this->data->get($this->id); 
              $this->desglose = $r->desglose; 
              return $this->desglose;
       } 
          /** 
            * @name: setOrden 
            * @desc: Set value of property "Orden" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setOrden($value){ 
              $this->orden = $value; 
              $this->data->setBy("id = '".$this->id."'", "orden = $value"); 
       } 
          /** 
            * @name: getOrden 
            * @desc: Get value of property "Orden" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getOrden(){ 
              $r = $this->data->get($this->id); 
              $this->orden = $r->orden; 
              return $this->orden;
       } 
          /** 
            * @name: setClasificacion 
            * @desc: Set value of property "Clasificacion" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setClasificacion($value){ 
              $this->clasificacion = $value; 
              $this->data->setBy("id = '".$this->id."'", "clasificacion = '$value'"); 
       } 
          /** 
            * @name: getClasificacion 
            * @desc: Get value of property "Clasificacion" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getClasificacion(){ 
              $r = $this->data->get($this->id); 
              $this->clasificacion = $r->clasificacion; 
              return $this->clasificacion;
       } 
          /** 
            * @name: setExigeProfesor 
            * @desc: Set value of property "ExigeProfesor" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setExigeProfesor($value){ 
              $this->exige_profesor = $value; 
               if ($value == true || $value == 1 || $value == "t") $value = "true"; 
               else if ($value == true || $value == 0 || $value == "f") $value = "false"; 
              $this->data->setBy("id = '".$this->id."'", "exige_profesor = $value"); 
       } 
          /** 
            * @name: getExigeProfesor 
            * @desc: Get value of property "ExigeProfesor" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getExigeProfesor(){ 
              $r = $this->data->get($this->id); 
              $this->exige_profesor = $r->exige_profesor; 
              return $this->exige_profesor;
       } 
          /** 
            * @name: setExigeLugar 
            * @desc: Set value of property "ExigeLugar" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setExigeLugar($value){ 
              $this->exige_lugar = $value; 
               if ($value == true || $value == 1 || $value == "t") $value = "true"; 
               else if ($value == true || $value == 0 || $value == "f") $value = "false"; 
              $this->data->setBy("id = '".$this->id."'", "exige_lugar = $value"); 
       } 
          /** 
            * @name: getExigeLugar 
            * @desc: Get value of property "ExigeLugar" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getExigeLugar(){ 
              $r = $this->data->get($this->id); 
              $this->exige_lugar = $r->exige_lugar; 
              return $this->exige_lugar;
       } 
       public function addActividad($grupo,$profesor,$lugar,$dia,$turno,$momento_de_colocacion,$creador){ 
              $plantilla = $this->id; 
              $obj = new ActividadClass($plantilla,$grupo,$profesor,$lugar,$dia,$turno,$momento_de_colocacion,$creador,"",true,false); 
              return $obj;
       } 
       public function getActividad(){ 
          $collection = new ActividadCollection($this->data->db);
          $collection->Load(null,null,"*","plantilla = '".$this->id."'");
           return $collection;
       } 
       public function addProfesorParaActividad($profesor,$prioridad){ 
              $plantilla = $this->id; 
              $obj = new ProfesorParaActividadClass($plantilla,$profesor,$prioridad,"",true,false); 
              return $obj;
       } 
       public function getProfesorParaActividad(){ 
          $collection = new ProfesorParaActividadCollection($this->data->db);
          $collection->Load(null,null,"*","plantilla = '".$this->id."'");
           return $collection;
       } 
       public function addLugarParaActividad($lugar,$prioridad){ 
              $plantilla = $this->id; 
              $obj = new LugarParaActividadClass($plantilla,$lugar,$prioridad,"",true,false); 
              return $obj;
       } 
       public function getLugarParaActividad(){ 
          $collection = new LugarParaActividadCollection($this->data->db);
          $collection->Load(null,null,"*","plantilla = '".$this->id."'");
           return $collection;
       } 
       public function addOtroGrupoDeRecursosParaActividad($prioridad,$necesarios,$imprescindibles,$creador){ 
              $plantilla = $this->id; 
              $obj = new OtroGrupoDeRecursosParaActividadClass($plantilla,$prioridad,$necesarios,$imprescindibles,$creador,"",true,false); 
              return $obj;
       } 
       public function getOtroGrupoDeRecursosParaActividad(){ 
          $collection = new OtroGrupoDeRecursosParaActividadCollection($this->data->db);
          $collection->Load(null,null,"*","plantilla = '".$this->id."'");
           return $collection;
       } 
} 
?>