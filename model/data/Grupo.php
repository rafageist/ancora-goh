<?php 
   class Grupo{ 
       var $id; 
       var $clasificacion; 
       var $descripcion; 
       var $especialidad; 
       var $nivel; 
       var $planificacion; 
       var $data;  
          /** 
            * @name: Grupo 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function Grupo($clasificacion,$descripcion,$especialidad,$nivel,$planificacion,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new GrupoModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($clasificacion,$descripcion,$especialidad,$nivel,$planificacion); 
                    $this->id = $replicas["id"]; 
                    $this->clasificacion = $clasificacion; 
                    $this->descripcion = $descripcion; 
                    $this->especialidad = $especialidad; 
                    $this->nivel = $nivel; 
                    $this->planificacion = $planificacion; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->clasificacion = $r->clasificacion;
                  $this->descripcion = $r->descripcion;
                  $this->especialidad = $r->especialidad;
                  $this->nivel = $r->nivel;
                  $this->planificacion = $r->planificacion;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->clasificacion = $clasificacion;
                  $this->descripcion = $descripcion;
                  $this->especialidad = $especialidad;
                  $this->nivel = $nivel;
                  $this->planificacion = $planificacion;
              }
       } 
          /** 
            * @name: getId 
            * @desc: Get value of property "Id" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getId(){ 
              $r = $this->data->get($this->id); 
              $this->id = $r->id; 
              return $this->id;
       } 
          /** 
            * @name: setClasificacion 
            * @desc: Set value of property "Clasificacion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setClasificacion($value){ 
              $this->clasificacion = $value; 
              $this->data->setBy("id = '".$this->id."'", "clasificacion = '$value'"); 
       } 
          /** 
            * @name: getClasificacion 
            * @desc: Get value of property "Clasificacion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getClasificacion(){ 
              $r = $this->data->get($this->id); 
              $this->clasificacion = $r->clasificacion; 
              return $this->clasificacion;
       } 
          /** 
            * @name: setDescripcion 
            * @desc: Set value of property "Descripcion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDescripcion($value){ 
              $this->descripcion = $value; 
              $this->data->setBy("id = '".$this->id."'", "descripcion = '$value'"); 
       } 
          /** 
            * @name: getDescripcion 
            * @desc: Get value of property "Descripcion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDescripcion(){ 
              $r = $this->data->get($this->id); 
              $this->descripcion = $r->descripcion; 
              return $this->descripcion;
       } 
          /** 
            * @name: setEspecialidad 
            * @desc: Set value of property "Especialidad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setEspecialidad($value){ 
              $this->especialidad = $value; 
              $this->data->setBy("id = '".$this->id."'", "especialidad = '$value'"); 
       } 
          /** 
            * @name: getEspecialidad 
            * @desc: Get value of property "Especialidad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getEspecialidad(){ 
              $r = $this->data->get($this->id); 
              $this->especialidad = $r->especialidad; 
              return $this->especialidad;
       } 
          /** 
            * @name: setNivel 
            * @desc: Set value of property "Nivel" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setNivel($value){ 
              $this->nivel = $value; 
              $this->data->setBy("id = '".$this->id."'", "nivel = '$value'"); 
       } 
          /** 
            * @name: getNivel 
            * @desc: Get value of property "Nivel" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getNivel(){ 
              $r = $this->data->get($this->id); 
              $this->nivel = $r->nivel; 
              return $this->nivel;
       } 
          /** 
            * @name: setPlanificacion 
            * @desc: Set value of property "Planificacion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPlanificacion($value){ 
              $this->planificacion = $value; 
              $this->data->setBy("id = '".$this->id."'", "planificacion = '$value'"); 
       } 
          /** 
            * @name: getPlanificacion 
            * @desc: Get value of property "Planificacion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPlanificacion(){ 
              $r = $this->data->get($this->id); 
              $this->planificacion = $r->planificacion; 
              return $this->planificacion;
       } 
       public function addActividad($plantilla,$profesor,$lugar,$dia,$turno,$momento_de_colocacion,$creador){ 
              $grupo = $this->id; 
              $obj = new ActividadClass($plantilla,$grupo,$profesor,$lugar,$dia,$turno,$momento_de_colocacion,$creador,"",true,false); 
              return $obj;
       } 
       public function getActividad(){ 
          $collection = new ActividadCollection($this->data->db);
          $collection->Load(null,null,"*","grupo = '".$this->id."'");
           return $collection;
       } 
       public function addBrigadaDeGrupo($brigada){ 
              $grupo = $this->id; 
              $obj = new BrigadaDeGrupoClass($brigada,$grupo,"",true,false); 
              return $obj;
       } 
       public function getBrigadaDeGrupo(){ 
          $collection = new BrigadaDeGrupoCollection($this->data->db);
          $collection->Load(null,null,"*","grupo = '".$this->id."'");
           return $collection;
       } 
       public function addLugarFijo($lugar){ 
              $grupo = $this->id; 
              $obj = new LugarFijoClass($grupo,$lugar,"",true,false); 
              return $obj;
       } 
       public function getLugarFijo(){ 
          $collection = new LugarFijoCollection($this->data->db);
          $collection->Load(null,null,"*","grupo = '".$this->id."'");
           return $collection;
       } 
       public function addProfesorParaGrupo($profesor_para_actividad){ 
              $grupo = $this->id; 
              $obj = new ProfesorParaGrupoClass($profesor_para_actividad,$grupo,"",true,false); 
              return $obj;
       } 
       public function getProfesorParaGrupo(){ 
          $collection = new ProfesorParaGrupoCollection($this->data->db);
          $collection->Load(null,null,"*","grupo = '".$this->id."'");
           return $collection;
       } 
       public function addAccesoProhibido($lugar){ 
              $grupo = $this->id; 
              $obj = new AccesoProhibidoClass($grupo,$lugar,"",true,false); 
              return $obj;
       } 
       public function getAccesoProhibido(){ 
          $collection = new AccesoProhibidoCollection($this->data->db);
          $collection->Load(null,null,"*","grupo = '".$this->id."'");
           return $collection;
       } 
} 
?>