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
             // #14: debug mode used to echo the SQL into the HTTP response and
             // append it to query.log in the current directory (the web root),
             // which could publish credentials and data. It now goes only to
             // the PHP error log.
             if ($debug == true) error_log("[SQL] ".$sql);
             return pg_query($this->connection,$sql);
        }

        /**
          * Run a parameterized query ($1, $2, ... placeholders) with
          * pg_query_params. Values are sent separately from the SQL text and
          * are never interpolated. (#14)
          */
        public function queryParams($sql, array $params){
             if (!$this->connection) return false;
             return pg_query_params($this->connection, $sql, array_values($params));
        }

        /**
          * Parameterized variant of getAsObjects(). (#14)
          */
        public function getAsObjectsParams($sql, array $params){
            $result = $this->queryParams($sql, $params);
            if (!$result) return false;
            $array = array();
            while ( $row = pg_fetch_object($result) ){
               $array[] = $row;
            }
            return $array;
        }
        
        /**
          * Parameterized variant of getAsArray(): rows as arrays keyed by
          * column name, an empty array when there are none, false when the
          * query fails. (#35)
          */
        public function getAsArrayParams($sql, array $params){
            $result = $this->queryParams($sql, $params);
            if (!$result) return false;
            return pg_fetch_all($result);
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