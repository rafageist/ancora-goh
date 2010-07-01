ancoragoh.client.browsers = {
    showExploradorDeHorarios: function(){
        ajax_load_htmlToWin("index.php?execute",{app_event_custom_getui_ExploradorHorarios: "yes"}, "explorador_de_horarios", false, false);
    },
    showPlanificaciones: function(){
		ancoragoh.client.showWin("planificaciones",{app_event_get_browser_Planificaciones: "yes"},false, false);
        //ajax_load_htmlToWin("index.php?execute",{app_event_get_browser_Planificaciones: "yes"}, "planificaciones", false, false);
    },
	showOrganizacion: function(){
        ajax_load_htmlToWin("index.php?execute",{app_event_get_browser_Organizacion: "yes"}, "organizacion", false, false);
    },
    showEspecialidades: function(){
        ajax_load_htmlToWin("index.php?execute",{app_event_get_browser_Especialidades: "yes"}, "especialidades");
    },
    showNiveles: function(){
        ajax_load_htmlToWin("index.php?execute&niveles&listar",{}, "niveles");
    },
    showBrigadas: function(){
        ajax_load_htmlToWin("index.php?execute",{app_event_get_browser_brigadas: "yes"},"brigadas");
    },
    showBrigadasBy: function (esp, nivel, iddiv) {
        ajax_load_html("index.php?execute&brigadas&filtrar&especialidad="+esp+"&nivel="+nivel,{},iddiv);
    },
    showGruposBy: function (idesp, idnivel, iddiv) {
        ajax_load_html("index.php?execute",{app_event_custom_getui_GruposXClasif: "yes", especialidad: idesp, nivel: idnivel},iddiv);
    },
    showGruposFiltrados: function (idesp, idnivel, idclasif, idplanif, iddiv) {
        ajax_load_html("index.php?execute",{app_event_custom_getui_GruposXClasifFiltrados: "yes", especialidad: idesp, nivel: idnivel, clasificacion: idclasif, planificacion: idplanif}, iddiv);
    },
    showClasifAct: function (){
        ajax_load_htmlToWin("index.php?execute&clasif_act&listar",{},"clasifacts");
    },
    showAsignaturas: function(){
        ajax_load_htmlToWin("index.php?execute&asignaturas&listar",{},"asignaturas");
    },
    showAsignaturasBy: function (esp, nivel, iddiv) {
        ajax_load_html("index.php?execute&asignaturas&filtrar&especialidad="+esp+"&nivel="+nivel,{},iddiv);
    },
    showProfesores: function(){
        ajax_load_htmlToWin("index.php?execute&profesores&listar",{},"profesores");
    },
    showLugares: function(){
        ajax_load_htmlToWin("index.php?execute&lugares&listar",{},"lugares");
    },
    showTreeBrgs: function(iddiv){
        ajax_load_html("index.php?execute",{app_event_custom_getui_TreeBrigadas:"yes"},iddiv);
    },
    showExploradorListProfes: function (iddiv){
        ajax_load_html("index.php?execute",{app_event_custom_getui_ExploradorListProfesores:"yes"}, iddiv);
    },
    showExploradorListLugares: function (iddiv){
        ajax_load_html("index.php?execute",{app_event_custom_getui_ExploradorListLugares:"yes"}, iddiv);
    },
    showExploradorListOtrosRecursos: function (iddiv){
        ajax_load_html("index.php?execute",{app_event_custom_getui_ExploradorListOtrosRecursos:"yes"}, iddiv);
    }
};