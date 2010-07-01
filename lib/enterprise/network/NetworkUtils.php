<?php

function getClientIPAddress(){
   		if(!isset($_SERVER)) $_SERVER = $HTTP_SERVER_VARS;
		$ip = '127.0.01';
        if (isset($_SERVER['REMOTE_ADDR'])) $ip = $_SERVER['REMOTE_ADDR'];
		if (isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) $ip = $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'];
        return $ip;
    }
?>