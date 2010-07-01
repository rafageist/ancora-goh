ancoragoh.client.browsers.horarios = {
	flag_brg : "",
	flag_profe : "",
	flag_lugar : "",
	flag_res : "",
	tipo : 1, // 1-brg, 3-lug, 2-profe, 4-res
	showHorario : function(idbrigada) {
		this.tipo = 1;
		this.flag_brg = idbrigada;
		var periodo = $("#explorador_cboPeriodo").val();
		ajax_load_html("index.php?execute", {
			app_event_get_minibrowser_horarios : "yes",
			idbrg : idbrigada,
			idperiodo : periodo
		}, "divExplorerDetail");
	},
	showHorarioProfesor : function(idprofesor) {
		this.tipo = 2;
		this.flag_profe = idprofesor;
		var periodo = $("#explorador_cboPeriodo").val();
		ajax_load_html("index.php?execute", {
			app_event_get_minibrowser_horarios : "yes",
			idprofe : idprofesor,
			idperiodo : periodo
		}, "divExplorerDetail");
	},
	showHorarioLugar : function(idlugar) {
		this.tipo = 3;
		this.flag_lugar = idlugar;
		var periodo = $("#explorador_cboPeriodo").val();
		ajax_load_html("index.php?execute", {
			app_event_get_minibrowser_horarios : "yes",
			idlug : idlugar,
			idperiodo : periodo
		}, "divExplorerDetail");
	},
	showHorarioOtroRecurso : function(idrecurso) {
		this.tipo = 4;
		this.flag_res = idrecurso;
		var periodo = $("#explorador_cboPeriodo").val();
		ajax_load_html("index.php?execute", {
			app_event_get_minibrowser_horarios : "yes",
			idres : idrecurso,
			idperiodo : periodo
		}, "divExplorerDetail");
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
	setRestriccionBrigada: function(dia, turno){
		var idbrigada = this.flag_brg;
		var idperiodo = $("#explorador_cboPeriodo").val();
		ancoragoh.server.brigadas.setRestriccion(idbrigada, dia, turno, idperiodo);
	}
};