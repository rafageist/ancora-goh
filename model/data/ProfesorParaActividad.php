<?php 
   class ProfesorParaActividad{ 
       var $id; 
       var $plantilla; 
       var $profesor; 
       var $prioridad; 
       var $data;  
          /** 
            * @name: ProfesorParaActividad 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function ProfesorParaActividad($plantilla,$profesor,$prioridad,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new ProfesorParaActividadModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($plantilla,$profesor,$prioridad); 
                    $this->id = $replicas["id"]; 
                    $this->plantilla = $plantilla; 
                    $this->profesor = $profesor; 
                    $this->prioridad = $prioridad; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->plantilla = $r->plantilla;
                  $this->profesor = $r->profesor;
                  $this->prioridad = $r->prioridad;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->plantilla = $plantilla;
                  $this->profesor = $profesor;
                  $this->prioridad = $prioridad;
              }
       } 
          /** 
            * @name: getId 
            * @desc: Get value of property "Id" 
            * @updated: 20/06/10 10:40:55 
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
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPlantilla($value){ 
              $this->plantilla = $value; 
              $this->data->setBy("id = '".$this->id."'", "plantilla = '$value'"); 
       } 
          /** 
            * @name: getPlantilla 
            * @desc: Get value of property "Plantilla" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getPlantilla(){ 
              $r = $this->data->get($this->id); 
              $this->plantilla = $r->plantilla; 
              return $this->plantilla;
       } 
          /** 
            * @name: setProfesor 
            * @desc: Set value of property "Profesor" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setProfesor($value){ 
              $this->profesor = $value; 
              $this->data->setBy("id = '".$this->id."'", "profesor = '$value'"); 
       } 
          /** 
            * @name: getProfesor 
            * @desc: Get value of property "Profesor" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getProfesor(){ 
              $r = $this->data->get($this->id); 
              $this->profesor = $r->profesor; 
              return $this->profesor;
       } 
          /** 
            * @name: setPrioridad 
            * @desc: Set value of property "Prioridad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPrioridad($value){ 
              $this->prioridad = $value; 
              $this->data->setBy("id = '".$this->id."'", "prioridad = $value"); 
       } 
          /** 
            * @name: getPrioridad 
            * @desc: Get value of property "Prioridad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getPrioridad(){ 
              $r = $this->data->get($this->id); 
              $this->prioridad = $r->prioridad; 
              return $this->prioridad;
       } 
       public function addProfesorParaGrupo($grupo){ 
              $profesor_para_actividad = $this->id; 
              $obj = new ProfesorParaGrupoClass($profesor_para_actividad,$grupo,"",true,false); 
              return $obj;
       } 
       public function getProfesorParaGrupo(){ 
          $collection = new ProfesorParaGrupoCollection($this->data->db);
          $collection->Load(null,null,"*","profesor_para_actividad = '".$this->id."'");
           return $collection;
       } 
} 
?>