<?php
/**
 * IDatabase Implementation for MySQL Database Server
 * Common methods for working with database servers
 * by Rafael Rodriguez Ramirez
 * 26/04/2009
 */
 
import("lib.enterprise.gendb.IDatabase");

class MySQLDatabaseImpl implements IDatabase{
    
        var $connection;
        
        /**
          * Connect to database
          */
        public function Connect($dbname,$user,$password, $host='localhost',$port='3306'){
               $this->connection = mysql_connect($host,$user,$password);
               mysql_select_db($dbname,$this->connection);
               if ($this->connection) return true;
               return false;   
        }
        /**
          * Return crudal result
          */
        public function getDefault($sql){
             return mysql_query($sql,$this->connection);
        }
        
        /**
         * Return array of objects. Each row as object.
         */
        public function getAsObjects($sql){
            $sql = str_replace("\'","'",$sql); 
            if ($this->connection){
                 $result = $this->getDefault($sql);
                 $array = array();  
                 while ( $row = mysql_fetch_object($result) ){  
                    $array[] = $row;  
                 }  
                 return $array;             
            }
            return false;
        }
        
        /**
          * Returns array of array. Each row as array with each fieldname as key.
          */
        public function getAsArray($sql){
        	$sql = str_replace("\'","'",$sql); 
            if ($this->connection){
                $result = $this->getDefault($sql); 
                $array = array();
                if (!is_bool($result) && !is_numeric($result)) {
                    if (mysql_num_rows($result)>0)
                        while ($row = mysql_fetch_array($result)){
                            foreach ($row as $k=>$dat) { if (is_numeric($k)) unset ($row[$k]);}
                            //for($i=0;$i<count($row)/2+1;$i++) unset($row[$i]);
                            $array[] = $row;
                        }   
                    return $array;
                } else
                return array('result'=>'ok');
            }
            return false;
        }

       /**
          * Disconnect from the server
          */
        public function Disconnect(){
            if ($this->connection){
                mysql_close($this->connection);
            }
        }
    
    }
?>
