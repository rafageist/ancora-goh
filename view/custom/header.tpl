<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>&Aacute;ncora GOH - Generaci&oacute;n y Organizaci&oacute;n de
Horarios</title>  
    
    <?php
				
				//-----------------------------------------------
				// Importing javascript libraries
				//-----------------------------------------------
				

				/* jQuery core */
				importjs ( "jquery/jquery" );
				
				/* jQuery User Interface */
				
				importjs ( "jquery/ui/ui.core" );
				importjs ( "jquery/ui/ui.datepicker" );
				importjs ( "jquery/ui/i18n/ui.datepicker-es" );
				importjs ( "jquery/ui/ui.sortable" );
				importjs ( "jquery/ui/ui.tabs" );
				importjs ( "jquery/ui/ui.draggable" );
				importjs ( "jquery/ui/ui.droppable" );
				importjs ( "jquery/ui/ui.resizable" );
				importjs ( "jquery/ui/ui.dialog" );
				importjs ( "jquery/external/bgiframe/jquery.bgiframe" );
				importjs ( "jquery/ui/effects.core" );
				importjs ( "jquery/ui/effects.blind" );
				importjs ( "jquery/ui/effects.bounce" );
				importjs ( "jquery/ui/effects.clip" );
				importjs ( "jquery/ui/effects.drop" );
				importjs ( "jquery/ui/effects.explode" );
				importjs ( "jquery/ui/effects.fold" );
				importjs ( "jquery/ui/effects.highlight" );
				importjs ( "jquery/ui/effects.pulsate" );
				importjs ( "jquery/ui/effects.scale" );
				importjs ( "jquery/ui/effects.shake" );
				importjs ( "jquery/ui/effects.slide" );
				importjs ( "jquery/ui/effects.transfer" );
				
				/* jQuery Gantt Plugin*/
				importjs ( "jquery/jquery.gantt/jquery.gantt" );
				
				/* jQuery TreeView Plugin*/
				
				/*importjs("jquery/jquery-treeview/lib/jquery.cookie");
		importjs("jquery/jquery-treeview/jquery.treeview");
                */
				//-----------------------------------------------
				// Importing javascript application
				//-----------------------------------------------
				importjs ( "app/ancoragoh" );
				importjs ( "app/lib/ancoragoh_ajax_util" );
				importjs ( "app/lib/ancoragoh_misc" );
				importjs ( "app/server/ancoragoh_server" );
				
				importjs ( "app/server/ancoragoh_server_asignaturas" );
				importjs ( "app/server/ancoragoh_server_brigadas" );
				importjs ( "app/server/ancoragoh_server_clasifact" );
				importjs ( "app/server/ancoragoh_server_desgloses" );
				importjs ( "app/server/ancoragoh_server_especialidades" );
				importjs ( "app/server/ancoragoh_server_grupos" );
				importjs ( "app/server/ancoragoh_server_lugares" );
				importjs ( "app/server/ancoragoh_server_niveles" );
				importjs ( "app/server/ancoragoh_server_otrosrecursos" );
				importjs ( "app/server/ancoragoh_server_periodos" );
				importjs ( "app/server/ancoragoh_server_planificaciones" );
				importjs ( "app/server/ancoragoh_server_plantillas" );
				importjs ( "app/server/ancoragoh_server_profesores" );
				importjs ( "app/server/ancoragoh_server_recursos" );
				importjs ( "app/server/ancoragoh_server_generador" );
				
				importjs ( "app/client/ancoragoh_client" );
				importjs ( "app/client/ancoragoh_client_browsers" );
				importjs ( "app/client/ancoragoh_client_forms" );
				
				importjs ( "app/client/forms/ancoragoh_client_forms" );
				importjs ( "app/client/forms/ancoragoh_client_forms_especialidad" );
				importjs ( "app/client/forms/ancoragoh_client_forms_nivel" );
				importjs ( "app/client/forms/ancoragoh_client_forms_planificacion" );
				importjs ( "app/client/forms/ancoragoh_client_forms_periodo" );
				importjs ( "app/client/forms/ancoragoh_client_forms_brigada" );
				importjs ( "app/client/forms/ancoragoh_client_forms_grupo" );
				importjs ( "app/client/forms/ancoragoh_client_forms_pxact" );
				importjs ( "app/client/forms/ancoragoh_client_forms_lxact" );
				importjs ( "app/client/forms/ancoragoh_client_forms_rxact" );
				importjs ( "app/client/forms/ancoragoh_client_forms_plantilla" );
				importjs ( "app/client/forms/ancoragoh_client_forms_desglose" );
				importjs ( "app/client/forms/ancoragoh_client_forms_profesor" );
				importjs ( "app/client/forms/ancoragoh_client_forms_lugar" );
				importjs ( "app/client/forms/ancoragoh_client_forms_otrorecurso" );
				importjs ( "app/client/forms/ancoragoh_client_forms_asignatura" );
				importjs ( "app/client/forms/ancoragoh_client_forms_clasifact" );
				importjs ( "app/client/forms/ancoragoh_client_forms_zpriori" );
				importjs ( "app/client/forms/ancoragoh_client_forms_costotraslado" );
				
				importjs ( "app/client/wins/ancoragoh_client_wins" );
				importjs ( "app/client/wins/ancoragoh_client_wins_generador" );
				
				importjs ( "app/client/browsers/ancoragoh_client_browsers" );
				importjs ( "app/client/browsers/ancoragoh_client_browsers_planificaciones" );
				importjs ( "app/client/browsers/ancoragoh_client_browsers_brigadas" );
				importjs ( "app/client/browsers/ancoragoh_client_browsers_organizacion" );
				importjs ( "app/client/browsers/ancoragoh_client_browsers_recursos" );
				importjs ( "app/client/browsers/ancoragoh_client_browsers_desgloses" );
				importjs ( "app/client/browsers/ancoragoh_client_browsers_horarios" );
				importjs ( "app/client/browsers/ancoragoh_client_browsers_publico" );
				
				//-----------------------------------------------
				// Importing stylesheets (CSS)
				//-----------------------------------------------
				importcss ( "styles/jquery" );
				
				importcss ( "styles/default" );
				importcss ( "js/jquery/themes/smoothness/ui.all" );
				importcss ( "js/jquery/jquery.gantt/jquery.gantt" );
				/*
        importcss("js/jquery/jquery-treeview/jquery.treeview");
        importcss("js/jquery/jquery-treeview/demo/screen");
        */
				?>
    <?php
				if (isset ( $_SESSION ['user'] ) && ! isset ( $_GET ['logout'] ) || isset($_POST['edtUser'])) {
					?>
	    <style>
body {
	background: white url(images/wallpapers/background3.jpg) no-repeat
		scroll 0 0; /*#13522B none repeat scroll 0 0;*/
}
</style>
	<?php
				}
				?>
</head>