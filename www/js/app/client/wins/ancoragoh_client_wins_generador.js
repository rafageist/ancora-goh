ancoragoh.client.wins.generador = {
	generando : false,
	atendiendo_proceso : false,
	flag_detener : false,
	estrategia : {
		compactar : 1, // compactar al inicio del periodo
		priorizar : 1, // 1- orden de las clasif 2- cantidad de actividades de
		// las asignaturas
		agrupar : false, // agrupar actividades por su clasificacion
		maximizar : false, // maximizar combinacion de act por dia
		lugar_cercano : 1, // 1-lugar mas utilizado o 2-lugar mas cercano
		setCompactar : function(valor) {
			this.compactar = valor;
			for (i = 1; i <= 10; i++) {
				if (i <= valor)
					$("#generador_compact_" + i).css("background", "blue");
				else
					$("#generador_compact_" + i).css("background", "gray");
			}

		},
		setPriorizar : function(value) {
			this.priorizar = value;
			$("#generador_select_priorizar1").css("border", "0px");
			$("#generador_select_priorizar2").css("border", "0px");
			$("#generador_select_priorizar" + value).css("border",
					"1px dashed blue");
		},
		setLugar : function(value) {
			this.lugar_cercano = value;
			$("#generador_select_lugar1").css("border", "0px");
			$("#generador_select_lugar2").css("border", "0px");
			$("#generador_select_lugar" + value).css("border",
					"1px dashed blue");
		}
	},
	especialidades : [],
	cantEspecialidades : 0,
	niveles : [],
	cantNiveles : 0,
	planificaciones : [],
	cantPlanificaciones : 0,
	cola : [],
	cantProcesos : 0,
	updateCants : function() {
		if (this.especialidades.length > this.cantEspecialidades)
			this.cantEspecialidades = this.especialidades.length;
		if (this.niveles.length > this.cantNiveles)
			this.cantNiveles = this.niveles.length;
		if (this.planificaciones.length > this.cantPlanificaciones)
			this.cantPlanificaciones = this.planificaciones.length;
		if (this.cola.length > this.cantProcesos)
			this.cantProcesos = this.procesos.length;
	},
	show : function() {

		ancoragoh.client.wins.generador.cola = new Array();
		ancoragoh.client.wins.generador.especialidades = new Array();
		ancoragoh.client.wins.generador.niveles = new Array();
		ancoragoh.client.wins.generador.planificaciones = new Array();
		ancoragoh.client.wins.generador.cantEspecialidades = 0;
		ancoragoh.client.wins.generador.cantNivles = 0;
		ancoragoh.client.wins.generador.cantPlanificaciones = 0;
		ancoragoh.client.wins.generador.cantProcesos = 0;

		ajax_execute_event(
				{
					app_event_get_data_especialidades : "yes"
				},
				function(res, status) {
					if (status == "success" || status == "notmodified") {

						ancoragoh.client.wins.generador.especialidades = eval(res.responseText);

						ajax_execute_event(
								{
									app_event_get_data_niveles : "yes"
								},
								function(res, status) {
									if (status == "success"
											|| status == "notmodified") {

										ancoragoh.client.wins.generador.niveles = eval(res.responseText);
										ajax_execute_event(
												{
													app_event_get_data_planificaciones : "yes"
												},
												function(res, status) {
													if (status == "success"
															|| status == "notmodified") {

														ancoragoh.client.wins.generador.planificaciones = eval(res.responseText);
														ancoragoh.client
																.loadHTMLToWin(
																		{
																			app_event_get_win_generador : "yes"
																		},
																		'generador',
																		false,
																		true);
														ancoragoh.client.wins.generador
																.updateCants();
														ancoragoh.client.wins.generador
																.refrescaEspecialidades();
														ancoragoh.client.wins.generador
																.refrescaNiveles();
														// ancoragoh.client.wins.generador.refrescaPlanificaciones();
														ancoragoh.client.wins.generador
																.refrescaPeriodos();
														// ancoragoh.client.wins.generador
														// .refrescaCola();
													}
												});
									}
								});
					}
				});
	},

	agregaProceso : function(idesp, idnivel, idperiodo) {
		var idxesp = this.getIdxEspecialidad(idesp);
		var idxnivel = this.getIdxNivel(idnivel);
		var idxplanificacion = this.getIdxPlanificacionOfPeriodo(idperiodo);
		var idxperiodo = this.getIdxPeriodo(idxplanificacion, idperiodo);

		this.cantProcesos++;
		this.cola[this.cantProcesos] = {
			esp : idxesp,
			nivel : idxnivel,
			planificacion : idxplanificacion,
			periodo : idxperiodo
		};
		this.refrescaCola();
	},
	eliminaProceso : function(idx) {
		for (i = idx; i < this.cantProcesos; i++)
			this.cola[i] = this.cola[i + 1];
		this.cantProcesos--;
		this.refrescaCola();
	},
	getIdxPlanificacion : function(id) {
		for (i = 0; i < this.cantPlanificaciones; i++) {
			if (this.planificaciones[i].id == id)
				return i;
		}
		return -1;
	},
	getIdxPlanificacionOfPeriodo : function(id) {
		for (i = 0; i < this.cantPlanificaciones; i++) {
			for (j = 0; j < this.planificaciones[i].cantPeriodos; j++) {
				if (this.planificaciones[i].periodos[j].id == id)
					return i;
			}
		}
		return -1;
	},
	getIdxPeriodo : function(idxplanif, id) {
		for (i = 0; i < this.planificaciones[idxplanif].cantPeriodos; i++) {
			if (this.planificaciones[idxplanif].periodos[i].id == id)
				return i;
		}
		return -1;
	},
	getIdxEspecialidad : function(id) {
		for (i = 0; i < this.cantEspecialidades; i++) {
			if (this.especialidades[i].id == id)
				return i;
		}
		return -1;
	},
	getIdxNivel : function(id) {
		for (i = 0; i < this.cantNiveles; i++) {
			if (this.niveles[i].id == id)
				return i;
		}
		return -1;
	},
	refrescaCola : function() {
		h = '<table id = "generador_tabla_procesos" ><thead><th>No.</th><th></th><td>Especialidad</th><th>Nivel</th><td>Per&iacute;odo</th></thead>';

		var desp = '';
		var dnivel = '';
		var dperiodo = '';

		for (i = 1; i <= this.cantProcesos; i++) {
			desp = this.especialidades[this.cola[i].esp].descripcion;
			dnivel = this.niveles[this.cola[i].nivel].descripcion;
			dperiodo = this.planificaciones[this.cola[i].planificacion].periodos[this.cola[i].periodo].descripcion;

			h = h
					+ '<tr><td>'
					+ i
					+ '</td><td><img src = "images/proceso.png"></td><td>'
					+ desp
					+ '</td><td>'
					+ dnivel
					+ '</td><td>'
					+ dperiodo
					+ '</td><td><label class ="delete iconlink" onclick = "ancoragoh.client.wins.generador.eliminaProceso('
					+ i
					+ ');" title = "Eliminar proceso de la cola">x</label></td></tr>';

		}
		h = h + '<tr>';
		h = h + '<td><label class="iconlink opaco" onclick="ancoragoh.client.wins.generador.event.addProceso();" title="Agregar proceso a la cola de generaci&oacite;n"> <img src="images/add_lite.png"> </label></td>';
		h = h + '<td></td><td id="generador_select_esp"></td><td id="generador_select_nivel"></td><td id="generador_select_periodo"></td>';

		h = h + '<td></td></tr>';
		h = h + '</table>';
		$("#cola_generacion").html(h);
		this.refrescaEspecialidades();
		this.refrescaNiveles();
		this.refrescaPeriodos();
	},
	refrescaEspecialidades : function() {
		h = '<select id = "generador_cboEsp">';
		for (i = 0; i < this.cantEspecialidades; i++)
			h = h + '<option value = "' + this.especialidades[i].id + '">'
					+ this.especialidades[i].descripcion + '</option>';
		h = h + '</select>';
		$("#generador_select_esp").html(h);
	},
	refrescaNiveles : function() {
		h = '<select id = "generador_cboNivel">';
		for (i = 0; i < this.niveles.length; i++)
			h = h + '<option value = "' + this.niveles[i].id + '">'
					+ this.niveles[i].descripcion + '</option>';
		h = h + '</select>';
		$("#generador_select_nivel").html(h);
	},
	// refrescaPlanificaciones : function() {
	// h = '<select id = "generador_cboPlanif" onchange =
	// "ancoragoh.client.wins.generador.refrescaPeriodos();" >';
	// for (i = 0; i < this.cantPlanificaciones; i++)
	// h = h + '<option value = "' + this.planificaciones[i].id + '">'
	// + this.planificaciones[i].descripcion + '</option>';
	// h = h + '</select>';
	// $("#generador_select_planif").html(h);
	// },
	refrescaPeriodos : function() {

		// var planif = $("#generador_cboPlanif").val();
		// var idx = this.getIdxPlanificacion(planif);

		// if (idx != -1) {
		h = '<select id = "generador_cboPeriodo">';

		for (j = 0; j < this.cantPlanificaciones; j++) {
			h = h + '<optgroup label ="' + this.planificaciones[j].descripcion
					+ '">';
			var idx = j;
			for (i = 0; i < this.planificaciones[idx].periodos.length; i++)
				h = h + '<option value = "'
						+ this.planificaciones[idx].periodos[i].id + '">'
						+ this.planificaciones[idx].periodos[i].descripcion
						+ '</option>';
			h = h + '</optgroup>';
		}
		h = h + '</select>';

		$("#generador_select_periodo").html(h);
		// }

	},
	event : {
		addProceso : function() {

			var idesp = $("#generador_cboEsp").val();
			var idnivel = $("#generador_cboNivel").val();
			var idperiodo = $("#generador_cboPeriodo").val();

			ancoragoh.client.wins.generador.agregaProceso(idesp, idnivel,
					idperiodo);

			ancoragoh.client.wins.generador.refrescaCola();
		}
	},
	selectCola : function() {
		$("#generador_estrategia").fadeOut("fast");
		$("#generador_cola").fadeIn("fast");
	},
	selectEstrategia : function() {
		$("#generador_cola").fadeOut("fast");
		$("#generador_estrategia").fadeIn("fast");
	},
	generar : function() {

		if (this.flag_detener == true) {
			this.flag_detener = false;
			this.generando = false;
			$("#generador_emo_working").fadeOut("slow");
			ancoragoh.client.showStatusReady();
			this.atendiendo_proceso = false;
			return 0;
		}

		if (this.generando == false)
			$("#generador_emo_working").fadeIn("slow");
		this.generando = true;

		ancoragoh.client.showStatusLoading();

		if (this.atendiendo_proceso == true)
			setTimeout(1000, 'this.generar()');

		if (this.cantProcesos > 0) {
			this.atendiendo_proceso = true;

			var idesp = this.especialidades[this.cola[1].esp].id;
			var idnivel = this.niveles[this.cola[1].nivel].id;
			var idperiodo = this.planificaciones[this.cola[1].planificacion].periodos[this.cola[1].periodo].id;
			var forma = this.estrategia.priorizar;
			var parche = this.estrategia.agrupar == true ? 'true' : 'false';
			var parche1 = this.estrategia.maximizar == true ? 'true' : 'false';
			var compactar = this.estrategia.compactar;
			var buscalugar = this.estrategia.lugar_cercano == 1 ? 'true'
					: 'false';

			ancoragoh.server.generador
					.generar(
							idesp,
							idnivel,
							idperiodo,
							forma,
							parche,
							compactar,
							parche1,
							buscalugar,
							function(res, status) {
								if (status == "success"
										|| status == "notmodified") {
									if (this.flag_detener == false) {
										ancoragoh.client.wins.generador.atendiendo_proceso = false;
										ancoragoh.client.wins.generador
												.eliminaProceso(1);
										ancoragoh.client.wins.generador
												.refrescaCola();
										setTimeout(10,
												ancoragoh.client.wins.generador
														.generar());
									} else {
										ancoragoh.client
												.showMessage("Generaci&oacute;n detenida");
										this.generando = false;
										$("#generador_emo_working").fadeOut(
												"slow");
										ancoragoh.client.showStatusReady();
										this.atendiendo_proceso = false;
									}

								}
							});
		} else {
			this.generando = false;
			this.atendiendo_proceso = false;
			$("#generador_emo_working").fadeOut("slow");
			ancoragoh.client.showStatusReady();
		}

	},
	detener : function() {
		if (this.atendiendo_proceso == true) {
			ancoragoh.client
					.showMessage("Deteniendo la generaci&oacute;n de horarios...");
			this.flag_detener = true;
			var idesp = this.especialidades[this.cola[1].esp].id;
			var idnivel = this.niveles[this.cola[1].nivel].id;
			var idperiodo = this.planificaciones[this.cola[1].planificacion].periodos[this.cola[1].periodo].id;

			ancoragoh.server.generador.cancelar(idesp, idnivel, idperiodo,
					function(res, status) {
						if (status == "success" || status == "notmodified") {
							ancoragoh.client
									.showMessage("Generaci&oacute;n detenida");
							this.generando = false;
							$("#generador_emo_working").fadeOut("slow");
							ancoragoh.client.showStatusReady();
							this.atendiendo_proceso = false;
						}
					});
		}

	}
};