ancoragoh.client.browsers.publico = {
	flag_brg : "",
	flag_profe : "",
	flag_lugar : "",
	flag_res : "",
	tipo : 1, // 1-brg, 3-lug, 2-profe, 4-res
	show : function() {
		ancoragoh.client.loadHTMLToWin({
			app_event_get_browser_publico : "yes", access: "public"
		}, "publico", false, true);
	},
	showHorario : function(idbrigada) {
		this.tipo = 1;
		this.flag_brg = idbrigada;
		var periodo = $("#publico_cboPeriodo").val();
		ajax_load_html("index.php?execute", {
			app_event_get_minibrowser_publico : "yes",
			idbrg : idbrigada,
			idperiodo : periodo, access: "public"
		}, "divPublicoDetail");
	},
	showHorarioProfesor : function(idprofesor) {
		this.tipo = 2;
		this.flag_profe = idprofesor;
		var periodo = $("#publico_cboPeriodo").val();
		ajax_load_html("index.php?execute", {
			app_event_get_minibrowser_publico : "yes",
			idprofe : idprofesor,
			idperiodo : periodo, access: "public"
		}, "divPublicoDetail");
	},
	showHorarioLugar : function(idlugar) {
		this.tipo = 3;
		this.flag_lugar = idlugar;
		var periodo = $("#publico_cboPeriodo").val();
		ajax_load_html("index.php?execute", {
			app_event_get_minibrowser_publico : "yes",
			idlug : idlugar,
			idperiodo : periodo, access: "public"
		}, "divPublicoDetail");
	},
	showHorarioOtroRecurso : function(idrecurso) {
		this.tipo = 4;
		this.flag_res = idrecurso;
		var periodo = $("#publico_cboPeriodo").val();
		ajax_load_html("index.php?execute", {
			app_event_get_minibrowser_publico : "yes",
			idres : idrecurso,
			idperiodo : periodo, access: "public"
		}, "divPublicoDetail");
	},
	refrescarHorario : function() {
		if (this.tipo == 1)
			this.showHorario(this.flag_brg);

		if (this.tipo == 2)
			this.showHorarioProfesor(this.flag_profe);

		if (this.tipo == 3)
			this.showHorarioLugar(this.flag_lugar);

		if (this.tipo == 4)
			this.showHorarioOtroRecurso(this.flag_res);

	},
    showTreeBrgs: function(iddiv){
        ajax_load_html("index.php?execute",{app_event_get_public_treeBrigadas:"yes", access: "public"},iddiv);
    },
    showExploradorListProfes: function (iddiv){
        ajax_load_html("index.php?execute",{app_event_get_public_list_profesores:"yes", access: "public"}, iddiv);
    },
    showExploradorListLugares: function (iddiv){
        ajax_load_html("index.php?execute",{app_event_get_public_list_lugares:"yes", access: "public"}, iddiv);
    },
    showExploradorListOtrosRecursos: function (iddiv){
        ajax_load_html("index.php?execute",{app_event_get_public_list_otros_recursos:"yes", access: "public"}, iddiv);
    }
};