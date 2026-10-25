<?php 
   class Usuario{ 
       var $id; 
       var $nombre; 
       var $info; 
       var $correo; 
       var $momento_ingreso; 
       var $nick; 
       var $contrasena; 
       var $data;  
          /** 
            * @name: Usuario 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function __construct($nombre,$info,$correo,$momento_ingreso,$nick,$contrasena,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new UsuarioModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($nombre,$info,$correo,$momento_ingreso,$nick,$contrasena); 
                    $this->id = $replicas["id"]; 
                    $this->nombre = $nombre; 
                    $this->info = $info; 
                    $this->correo = $correo; 
                    $this->momento_ingreso = $momento_ingreso; 
                    $this->nick = $nick; 
                    $this->contrasena = $contrasena; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->nombre = $r->nombre;
                  $this->info = $r->info;
                  $this->correo = $r->correo;
                  $this->momento_ingreso = $r->momento_ingreso;
                  $this->nick = $r->nick;
                  $this->contrasena = $r->contrasena;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->nombre = $nombre;
                  $this->info = $info;
                  $this->correo = $correo;
                  $this->momento_ingreso = $momento_ingreso;
                  $this->nick = $nick;
                  $this->contrasena = $contrasena;
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
            * @name: setNombre 
            * @desc: Set value of property "Nombre" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setNombre($value){ 
              $this->nombre = $value; 
              $this->data->setBy("id = '".$this->id."'", "nombre = '$value'"); 
       } 
          /** 
            * @name: getNombre 
            * @desc: Get value of property "Nombre" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getNombre(){ 
              $r = $this->data->get($this->id); 
              $this->nombre = $r->nombre; 
              return $this->nombre;
       } 
          /** 
            * @name: setInfo 
            * @desc: Set value of property "Info" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setInfo($value){ 
              $this->info = $value; 
              $this->data->setBy("id = '".$this->id."'", "info = '$value'"); 
       } 
          /** 
            * @name: getInfo 
            * @desc: Get value of property "Info" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getInfo(){ 
              $r = $this->data->get($this->id); 
              $this->info = $r->info; 
              return $this->info;
       } 
          /** 
            * @name: setCorreo 
            * @desc: Set value of property "Correo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setCorreo($value){ 
              $this->correo = $value; 
              $this->data->setBy("id = '".$this->id."'", "correo = '$value'"); 
       } 
          /** 
            * @name: getCorreo 
            * @desc: Get value of property "Correo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getCorreo(){ 
              $r = $this->data->get($this->id); 
              $this->correo = $r->correo; 
              return $this->correo;
       } 
          /** 
            * @name: setMomentoIngreso 
            * @desc: Set value of property "MomentoIngreso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setMomentoIngreso($value){ 
              $this->momento_ingreso = $value; 
              $this->data->setBy("id = '".$this->id."'", "momento_ingreso = $value"); 
       } 
          /** 
            * @name: getMomentoIngreso 
            * @desc: Get value of property "MomentoIngreso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getMomentoIngreso(){ 
              $r = $this->data->get($this->id); 
              $this->momento_ingreso = $r->momento_ingreso; 
              return $this->momento_ingreso;
       } 
          /** 
            * @name: setNick 
            * @desc: Set value of property "Nick" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setNick($value){ 
              $this->nick = $value; 
              $this->data->setBy("id = '".$this->id."'", "nick = '$value'"); 
       } 
          /** 
            * @name: getNick 
            * @desc: Get value of property "Nick" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getNick(){ 
              $r = $this->data->get($this->id); 
              $this->nick = $r->nick; 
              return $this->nick;
       } 
          /** 
            * @name: setContrasena 
            * @desc: Set value of property "Contrasena" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function setContrasena($value){ 
              $this->contrasena = $value; 
              $this->data->setBy("id = '".$this->id."'", "contrasena = '$value'"); 
       } 
          /** 
            * @name: getContrasena 
            * @desc: Get value of property "Contrasena" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
       public function getContrasena(){ 
              $r = $this->data->get($this->id); 
              $this->contrasena = $r->contrasena; 
              return $this->contrasena;
       } 
       public function addBrigada($descripcion,$especialidad,$nivel,$matricula){ 
              $creador = $this->id; 
              $obj = new BrigadaClass($descripcion,$especialidad,$nivel,$matricula,$creador,"",true,false); 
              return $obj;
       } 
       public function getBrigada(){ 
          $collection = new BrigadaCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addClasificacionDeActividad($descripcion,$duracion,$prioridad,$gasto){ 
              $creador = $this->id; 
              $obj = new ClasificacionDeActividadClass($descripcion,$duracion,$creador,$prioridad,$gasto,"",true,false); 
              return $obj;
       } 
       public function getClasificacionDeActividad(){ 
          $collection = new ClasificacionDeActividadCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addActividad($plantilla,$grupo,$profesor,$lugar,$dia,$turno,$momento_de_colocacion){ 
              $creador = $this->id; 
              $obj = new ActividadClass($plantilla,$grupo,$profesor,$lugar,$dia,$turno,$momento_de_colocacion,$creador,"",true,false); 
              return $obj;
       } 
       public function getActividad(){ 
          $collection = new ActividadCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addLugar($nombre,$capacidad){ 
              $creador = $this->id; 
              $obj = new LugarClass($nombre,$capacidad,$creador,"",true,false); 
              return $obj;
       } 
       public function getLugar(){ 
          $collection = new LugarCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addOtrosRecursosUsados($otro_recurso,$actividad){ 
              $creador = $this->id; 
              $obj = new OtrosRecursosUsadosClass($otro_recurso,$actividad,$creador,"",true,false); 
              return $obj;
       } 
       public function getOtrosRecursosUsados(){ 
          $collection = new OtrosRecursosUsadosCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addPlanificacion($descripcion,$fecha_inicial,$cant_dias,$activa){ 
              $creador = $this->id; 
              $obj = new PlanificacionClass($descripcion,$fecha_inicial,$cant_dias,$activa,$creador,"",true,false); 
              return $obj;
       } 
       public function getPlanificacion(){ 
          $collection = new PlanificacionCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addNivel($descripcion){ 
              $creador = $this->id; 
              $obj = new NivelClass($descripcion,$creador,"",true,false); 
              return $obj;
       } 
       public function getNivel(){ 
          $collection = new NivelCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addProfesor($nombre){ 
              $creador = $this->id; 
              $obj = new ProfesorClass($nombre,$creador,"",true,false); 
              return $obj;
       } 
       public function getProfesor(){ 
          $collection = new ProfesorCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addOtroRecurso($nombre){ 
              $creador = $this->id; 
              $obj = new OtroRecursoClass($nombre,$creador,"",true,false); 
              return $obj;
       } 
       public function getOtroRecurso(){ 
          $collection = new OtroRecursoCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addAsignatura($descripcion,$especialidad,$nivel){ 
              $creador = $this->id; 
              $obj = new AsignaturaClass($descripcion,$especialidad,$nivel,$creador,"",true,false); 
              return $obj;
       } 
       public function getAsignatura(){ 
          $collection = new AsignaturaCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addEspecialidad($descripcion){ 
              $creador = $this->id; 
              $obj = new EspecialidadClass($descripcion,$creador,"",true,false); 
              return $obj;
       } 
       public function getEspecialidad(){ 
          $collection = new EspecialidadCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addOtroGrupoDeRecursosParaActividad($plantilla,$prioridad,$necesarios,$imprescindibles){ 
              $creador = $this->id; 
              $obj = new OtroGrupoDeRecursosParaActividadClass($plantilla,$prioridad,$necesarios,$imprescindibles,$creador,"",true,false); 
              return $obj;
       } 
       public function getOtroGrupoDeRecursosParaActividad(){ 
          $collection = new OtroGrupoDeRecursosParaActividadCollection($this->data->db);
          $collection->Load(null,null,"*","creador = '".$this->id."'");
           return $collection;
       } 
       public function addSeguridadHost($ip){ 
              $usuario = $this->id; 
              $obj = new SeguridadHostClass($ip,$usuario,"",true,false); 
              return $obj;
       } 
       public function getSeguridadHost(){ 
          $collection = new SeguridadHostCollection($this->data->db);
          $collection->Load(null,null,"*","usuario = '".$this->id."'");
           return $collection;
       } 
       public function addSeguridadRolDeUsuario($rol){ 
              $usuario = $this->id; 
              $obj = new SeguridadRolDeUsuarioClass($usuario,$rol,"",true,false); 
              return $obj;
       } 
       public function getSeguridadRolDeUsuario(){ 
          $collection = new SeguridadRolDeUsuarioCollection($this->data->db);
          $collection->Load(null,null,"*","usuario = '".$this->id."'");
           return $collection;
       } 
       public function addSeguridadTraza($fecha,$accion,$ip){ 
              $usuario = $this->id; 
              $obj = new SeguridadTrazaClass($fecha,$usuario,$accion,$ip,"",true,false); 
              return $obj;
       } 
       public function getSeguridadTraza(){ 
          $collection = new SeguridadTrazaCollection($this->data->db);
          $collection->Load(null,null,"*","usuario = '".$this->id."'");
           return $collection;
       } 
} 
?>