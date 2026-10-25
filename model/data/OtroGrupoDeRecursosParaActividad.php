<?php 
   class OtroGrupoDeRecursosParaActividad{ 
       var $id; 
       var $plantilla; 
       var $prioridad; 
       var $necesarios; 
       var $imprescindibles; 
       var $creador; 
       var $data;  
          /** 
            * @name: OtroGrupoDeRecursosParaActividad 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($plantilla,$prioridad,$necesarios,$imprescindibles,$creador,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new OtroGrupoDeRecursosParaActividadModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($plantilla,$prioridad,$necesarios,$imprescindibles,$creador); 
                    $this->id = $replicas["id"]; 
                    $this->plantilla = $plantilla; 
                    $this->prioridad = $prioridad; 
                    $this->necesarios = $necesarios; 
                    $this->imprescindibles = $imprescindibles; 
                    $this->creador = $creador; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->plantilla = $r->plantilla;
                  $this->prioridad = $r->prioridad;
                  $this->necesarios = $r->necesarios;
                  $this->imprescindibles = $r->imprescindibles;
                  $this->creador = $r->creador;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->plantilla = $plantilla;
                  $this->prioridad = $prioridad;
                  $this->necesarios = $necesarios;
                  $this->imprescindibles = $imprescindibles;
                  $this->creador = $creador;
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
            * @name: setPlantilla 
            * @desc: Set value of property "Plantilla" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setPlantilla($value){ 
              $this->plantilla = $value; 
              $this->data->getAnythingParams("UPDATE otro_grupo_de_recursos_para_actividad SET plantilla = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getPlantilla 
            * @desc: Get value of property "Plantilla" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getPlantilla(){ 
              $r = $this->data->get($this->id); 
              $this->plantilla = $r->plantilla; 
              return $this->plantilla;
       } 
          /** 
            * @name: setPrioridad 
            * @desc: Set value of property "Prioridad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setPrioridad($value){ 
              $this->prioridad = $value; 
              $this->data->getAnythingParams("UPDATE otro_grupo_de_recursos_para_actividad SET prioridad = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getPrioridad 
            * @desc: Get value of property "Prioridad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getPrioridad(){ 
              $r = $this->data->get($this->id); 
              $this->prioridad = $r->prioridad; 
              return $this->prioridad;
       } 
          /** 
            * @name: setNecesarios 
            * @desc: Set value of property "Necesarios" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setNecesarios($value){ 
              $this->necesarios = $value; 
              $this->data->getAnythingParams("UPDATE otro_grupo_de_recursos_para_actividad SET necesarios = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getNecesarios 
            * @desc: Get value of property "Necesarios" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getNecesarios(){ 
              $r = $this->data->get($this->id); 
              $this->necesarios = $r->necesarios; 
              return $this->necesarios;
       } 
          /** 
            * @name: setImprescindibles 
            * @desc: Set value of property "Imprescindibles" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setImprescindibles($value){ 
              $this->imprescindibles = $value; 
              $this->data->getAnythingParams("UPDATE otro_grupo_de_recursos_para_actividad SET imprescindibles = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getImprescindibles 
            * @desc: Get value of property "Imprescindibles" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getImprescindibles(){ 
              $r = $this->data->get($this->id); 
              $this->imprescindibles = $r->imprescindibles; 
              return $this->imprescindibles;
       } 
          /** 
            * @name: setCreador 
            * @desc: Set value of property "Creador" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setCreador($value){ 
              $this->creador = $value; 
              $this->data->getAnythingParams("UPDATE otro_grupo_de_recursos_para_actividad SET creador = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getCreador 
            * @desc: Get value of property "Creador" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getCreador(){ 
              $r = $this->data->get($this->id); 
              $this->creador = $r->creador; 
              return $this->creador;
       } 
       public function addOtroRecursoParaActividad($prioridad,$otro_recurso){ 
              $grupo_de_recursos = $this->id; 
              $obj = new OtroRecursoParaActividadClass($grupo_de_recursos,$prioridad,$otro_recurso,"",true,false); 
              return $obj;
       } 
       public function getOtroRecursoParaActividad(){ 
          $collection = new OtroRecursoParaActividadCollection($this->data->db);
          $collection->Load(null,null,"*","grupo_de_recursos = $1", array(PartModel::sqlValue($this->id, "text")));
           return $collection;
       } 
} 
?>