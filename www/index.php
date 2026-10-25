<?php
   // #14: strict mode, HttpOnly/SameSite cookies (see SessionUtil)
   include_once "../lib/basic/security/SessionUtil.php";
   SessionUtil::start();
   
   if (file_exists("path.php")) include "path.php";
   
   include "../lib/import.php";
   
   
   //----------------- Header ----------------------//
   if (!isset($_GET['execute'])) import("view.custom.header");	
   //-----------------------------------------------//
   
   // Main imports
   import("lib.basic.file.PropertiesFile");
   import("lib.basic.security.PasswordUtil");
   import("lib.enterprise.gendb.PostgresDatabaseImpl");
   import("lib.basic.string.StringUtils");
   import("lib.basic.php.PHPUtils");
   import("lib.basic.html.HTMLUtil");
   import("model.data.PartModel");
   import("control.Application");
   import("view.windows"); 
     
    //Connecting to database....
   $dbcfg = new PropertiesFile("../bin/ancoragoh.conf");
   $dbconnection = new PostgresDatabaseImpl();
   $dbconnection->Connect($dbcfg->dbname, $dbcfg->user, $dbcfg->password, $dbcfg->host, $dbcfg->port);
   $dberror = false;
   $noscript = false;
   
?>

<?php if (!isset($_GET['execute'])) {?>
	<nosccript>	<?php /*$noscript = true;*/ ?> </nosccript>
<?php }?>
 
<?php 
	if ($noscript == true || $dberror == true){
	echo '
			<style>
				body{ 
					background: black;
					color: white;
					font-size: 14px;
				}
			</style>
    		<p align="center">
     			<img src = "images/ancoragoh.gif"><br>
     			&Aacute;ncora GOH - Generaci&oacute;n y Organizaci&oacute;n de Horarios <br>
				<div style = "background: red; color: yellow; padding: 10px; width: 50%; margin: auto; -moz-border-radius: 10px;">
					<img src="images/cancel.png"><b>Se detectaron algunos problemas antes de iniciar la aplicaci&oacute;n</b><br>
					<ul>
						<li>El navegador no tiene habilitado el soporte JavaScript. <br>
							Por favor habilite JavaScript para poder utilizar la aplicaci&oacute;n
						</li>
						'.(($dberror == true)?'':'<li> No se puede establecer la conexi&oacute;n con la base de datos: '.$emsg.'</li>').' 
				
					</ul><br>
					Contacte con su administrador si el problema persiste.
				</div>
			</p>';
	exit();
	}
   
   //Launch the application...
   $app = new Application();
   $app->Run();

   //----------------- Footer ----------------------//
   if (!isset($_GET['execute'])) import("view.custom.footer");
   //-----------------------------------------------//
?>
   