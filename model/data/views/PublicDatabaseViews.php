<?php 
   class PublicDatabaseViews extends PartModel { 
 
       /** 
         * @name: PublicDatabaseViews 
         * @desc: Constructor 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
          public function PublicDatabaseViews($db){ 
                $this->db = $db; 
          } 
       /** 
         * @name: PublicDatabaseViews 
         * @desc: Constructor 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
           public function getAsignaturasCantAct($asobjects = false){ 
                return $this->getAnything("SELECT * FROM  asignaturas_cant_act;", $asobjects);
           } 
       /** 
         * @name: PublicDatabaseViews 
         * @desc: Constructor 
         * @updated: 20/06/10 10:40:54 
         * @version: 1.0 
         */ 
           public function getUsoDeOtrosRecursos($asobjects = false){ 
                return $this->getAnything("SELECT * FROM  uso_de_otros_recursos;", $asobjects);
           } 
    } 
?>