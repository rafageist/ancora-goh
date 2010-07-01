<?php
/**
 * Interface Database
 * Common methods for working with database servers
 * by Rafael Rodriguez Ramirez
 * 26/04/2009
 */
 
 
 
interface IDatabase{
	    
		/**
		  * Connect to database
		  */
		public function Connect($dbname,$user,$password, $host='localhost',$port='5432');

		/**
		  * Return crudal result
		  */
		public function getDefault($sql);
		
		/**
		  * Return array of objects. Each row as object.
		  */
		public function getAsObjects($sql);

	   /**
		 * Returns array of array. Each row as array with each fieldname as key.
		 */
		public function getAsArray($sql);
		
		/**
	      * Disconnect from the server
		  */
		public function Disconnect();

}

?>