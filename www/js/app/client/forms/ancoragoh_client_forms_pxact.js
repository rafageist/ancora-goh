ancoragoh.client.forms.pxact = {
	event : {
		flag_idplantilla : "",
		flag_id : "",
		flag_idx : 0,
		message : "",
		fcomplete : function(res, status) {
			if (status == "success" || status == "notmodified") {
				ancoragoh.client.browsers.desgloses
						.showProfesoresParaActividad(
								ancoragoh.client.browsers.desgloses.flag_idplantilla);
				ancoragoh.client
						.showMessage(ancoragoh.client.forms.pxact.event.message);
			}
		},
		agregar : function(cantdisponibles, idplantilla) {

			this.flag_idplantilla = idplantilla;

			for ( var i = 1; i <= cantdisponibles; i++) {
				var chk = $("#chkProfeParaActividad_" + i);
				if (chk.attr("checked")) {
					var idprofesor = $("#edtProfesorDisponible_" + i).val();

					if (i == cantdisponibles)
						ancoragoh.server.recursos.agregarProfesorParaActividad(
								idplantilla, idprofesor);
					else
						ancoragoh.server.recursos.agregarProfesorParaActividad(
								idplantilla, idprofesor, this.fcomplete);
				}
			}
			this.message = "Profesores asignados satisfactoriamente";
			ancoragoh.client.browsers.desgloses.showProfesoresParaActividad(
					idplantilla, 'div_asignacion_recursos');
			ancoragoh.client.hidewin("new_pxact");
		},
		modificar : function() {

			var cant = $("#flag_PxAct_cantGrupos").val();
			var idpxact = $("#flag_PxAct_idPxact").val();
			var idprofesor = $("#flag_PxAct_profesor").val();
			var prioridad = $("#field_PxAct_prioridad").val();

			ancoragoh.server.recursos.eliminarGruposDeProfesor(idpxact);

			for ( var i = 1; i <= cant; i++) {
				var chk = $("#chkGrupoDeProfesor_" + i);
				if (chk.attr("checked") == true) {
					var idgrupo = $("#edtIdGrupoDeProfesor_" + i).val();
					ancoragoh.server.recursos.actualizarProfesorParaGrupo(
							idpxact, idgrupo, this.fcomplete);
				}
			}
			this.message = "Asignaci&oacute;n de profesor actualizada satisfactoriamente";
			ancoragoh.server.recursos.actualizarProfesorParaActividad(idpxact,
					ancoragoh.client.browsers.desgloses.flag_idplantilla,
					idprofesor, prioridad, this.fcomplete);
			ancoragoh.client.hidewin("edit_pxact");
		},
		eliminar : function() {
			this.message = "Asignaci&oacute;n de profesor eliminada satisfactoriamente";
			ancoragoh.server.recursos.eliminarProfesorParaActividad(
					this.flag_id, this.fcomplete);

			$("#frameEliminar-pxact").fadeOut("slow");
		},
		cancelar : function() {
			ancoragoh.client.hidewin("new_pxact");
			ancoragoh.client.hidewin("edit_pxact");
		}
	},
	showForNew : function(idplantilla) {
		this.flag_idplantilla = idplantilla;
		ancoragoh.client.loadHTMLToWin( {
			plantilla : idplantilla,
			app_event_custom_getui_NuevoProfesorParaActividad : "yes"
		}, "new_pxact", false, true);
	},
	showForEdit : function(idpxact) {
		ancoragoh.client.loadHTMLToWin( {
			pxact : idpxact,
			app_event_custom_getui_EditarProfesorParaActividad : "yes"
		}, "edit_pxact", false, true);
	},
	showFrameEliminarPxact : function(idpxact, ixpxact) {
		this.event.flag_id = idpxact;
		this.event.flag_idx = ixpxact;
		$("#frameEliminar-pxact").fadeIn("slow");
	},
	editarPrioridadPxact : function(idprofe, idpxact, idx) {
		var idplantilla = ancoragoh.client.browsers.desgloses.flag_idplantilla;
		$('#tr_pxact_' + idx).css("background", "#AAAAAA");
		$('#tr_pxact_' + idx).css("color", "white");
		
		ajax_load_html("index.php?execute", {app_event_get_form_edit_pxact_prioridad: "yes", plantilla: idplantilla, profe: idprofe, pxact: idpxact}, "pxact_prioridad");
	},
	actualizaPrioridad: function(prioridad, idpxact){
		this.event.message = 'Prioridad actualizada';
		ancoragoh.server.recursos.actualizaPrioridadPxact(idpxact,prioridad, this.event.fcomplete);
		$("#pxact_prioridad").fadeOut("slow");
	},
	cancelarPrioridad: function(){
		$("#pxact_prioridad").fadeOut("slow");
	}
};