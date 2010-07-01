ancoragoh.client.browsers.planificaciones = {
	show: function(){
		ancoragoh.client.showWin("planificaciones",{app_event_get_browser_Planificaciones: "yes"},false, false);
	},
	fcomplete: function(res, status){
			if ( status == "success" || status == "notmodified"){
				ancoragoh.client.browsers.planificaciones.refrescar();
				ancoragoh.client.showMessage('Planificaci&oacute;n eliminada satisfactoriamente');
			}
	},
	showEliminar: function(id){
		$("#minifield_Planificacion_Id").val(id);
		$("#frame_browser_del_Planificacion").fadeIn("slow");
	},
	eliminar: function(){
            var id = $("#minifield_Planificacion_Id").val();			
            ancoragoh.server.planificaciones.remove(id, this.fcomplete);
    },
	cargaDiagramaGantt: function(){
		ajax_load_html("index.php?execute", {app_event_get_diagram_gantt_planificaciones: "yes"}, "planificaciones_diagrama_gantt");
	},
	cargaMiniBrowser: function(){
		ajax_load_html("index.php?execute", {app_event_get_minibrowser_planificaciones: "yes"}, "browserPlanificaciones");
	},
	refrescar: function(){
		ancoragoh.client.browsers.planificaciones.cargaMiniBrowser();
		ancoragoh.client.browsers.planificaciones.cargaDiagramaGantt();
		$("#browserPlanificaciones").fadeIn("fast");
		$("#diagrama_gantt").fadeOut("fast");
	},
    showPeriodos: function(idplanif){
        ajax_load_html("index.php?execute",{app_event_get_browser_Periodos: "yes", planificacion: idplanif}, "div-periodos");
    }
};