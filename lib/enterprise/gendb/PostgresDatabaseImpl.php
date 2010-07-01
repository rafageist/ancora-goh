<?php
/**
 * IDatabase Implementation for PostgreSQL Database Server
 * Common methods for working with database servers
 * by Rafael Rodriguez Ramirez
 * 26/04/2009
 */

import("lib.enterprise.gendb.IDatabase");

class PostgresDatabaseImpl implements IDatabase{
    
        var $connection;
        
        /**
          * Connect to database
          */
        public function Connect($dbname,$user,$password, $host='localhost',$port='5432'){
               $this->connection = pg_connect("host = $host port=$port dbname=$dbname user =$user password = $password");
               if ($this->connection) return true;
               return false;   
        }
        
        /**
          * Return crudal result
          */
        public function getDefault($sql, $debug = false){
             if ($debug == true) {
                 echo "[SQL] ".$sql."\n";
                 $f = fopen("query.log","a");
                 fputs($f,$sql."\n");
                 fclose($f);
            }
             return pg_query($this->connection,$sql);
        }
        
        /**
         * Return array of objects. Each row as object.
         */
        public function getAsObjects($sql){
            
            if ($this->connection){
                 $result = $this->getDefault($sql);
                 $array = array();  
                 while ( $row = pg_fetch_object($result) ){  
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
            if ($this->connection){
                $result = $this->getDefault($sql);
                return pg_fetch_all($result);
            }
        }

       /**
          * Disconnect from the server
          */
        public function Disconnect(){
            if ($this->connection){
                pg_close($this->connection);
            }
        }
    }
?>