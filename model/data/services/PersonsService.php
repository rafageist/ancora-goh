<?php 
      /** 
        * @name: Service utilies about persons 
        * @desc:  
        * @updated: 20/06/10 10:40:58 
        * @version: 1.0 
        */ 
      class PersonsService extends PartModel{ 
          var $db; 
          /** 
            * @name: Persons 
            * @desc: Constructor 
            * @updated: 20/06/10 10:40:58 
            * @version: 1.0 
            */ 
          public function Persons($db){ 
                $this->db = $db;  
          }
 
          /** 
            * @name: getPersonsByNameAndAge 
            * @desc: Get all persons 
            * @updated: 20/06/10 10:40:58 
            * @version: 1.0 
            */ 
          function getPersonsByNameAndAge($name,$age,$asobjects = false){ 
               $sql = "SELECT * FROM persons WHERE name = '$name' AND age = $age"; 
               if ($asobjects==true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
            } 
    } 
?>