ancoragoh.client.forms.rxact = {
		event: {
			flag_id: "",
			flag_idgr: "",
			message: "",
			fcomplete: function(res, status){
						if ( status == "success" || status == "notmodified"){
							ancoragoh.client.browsers.desgloses.showOtrosRecursosParaActividad(ancoragoh.client.browsers.desgloses.flag_idplantilla);
							ancoragoh.client.showMessage(ancoragoh.client.forms.rxact.event.message);
						}
			},
			agregar: function (cantdisponibles){
				var prioridad = $("#field_RecursoParaActividad_max_prioridad").val();
				var grupo = this.flag_idgr;
				this.message = "Recursos asignados satisfactoriamente";
		        for (var i = 1; i <= cantdisponibles; i++){
		            var chk = $("#chkRecursoParaActividad_"+i);
		            if (chk.attr("checked")) {
		                var id = $("#edtRecursoDisponible_" + i).val();
	                	ancoragoh.server.recursos.agregarOtroRecursoParaActividad(id, prioridad, grupo, this.fcomplete);
		            }
		        }
		        
		        ancoragoh.client.hidewin("new_rxact");
			},
			eliminar: function(){
				this.message = "Asignaci&oacute;n de profesor eliminada satisfactoriamente";
				ancoragoh.server.recursos.eliminarProfesorParaActividad(this.flag_id, this.fcomplete);
				
				$("#frameEliminar-pxact").fadeOut("slow");
			},
			cancelar: function(){
				ancoragoh.client.hidewin("new_rxact");
			}
		},
		showFrameEliminarPxact: function(idpxact, ixpxact) {
			this.event.flag_id = idpxact;
			this.event.flag_idx = ixpxact;
			$("#frameEliminar-pxact").fadeIn("slow");
		},
		editarPrioridadesRxAct: function(idplantilla, idrxact, iddiv){
			this.event.flag_idgr = idrxact;
			ajax_load_html("index.php?execute",{plantilla: idplantilla, rxact: idrxact, app_event_custom_getui_EditarPrioridadRxact:"yes"}, iddiv);
		},
        editGrupoRecursos: function(idrxact, iddiv){
			ajax_load_html("index.php?execute",{rxact: idrxact, app_event_custom_getui_OtrosRecursosAsignadosDetalles:"yes"}, iddiv);
		},
		showAddRecursos: function(idgr, idplantilla){
			this.event.flag_idgr = idgr;
			ancoragoh.client.loadHTMLToWin({app_event_get_form_add_rxact: "yes", grupo: idgr},"new_rxact", false, true);
			
		},
		actualizaPrioridadGRxAct: function(p){
			var idplantilla = ancoragoh.client.browsers.desgloses.flag_idplantilla;
			var idrxact = this.event.flag_idgr;
			this.event.message = "Prioridad del grupo de recursos actualizada";
			ancoragoh.server.recursos.actualizaPrioridadRxact(idrxact, p, this.event.fcomplete);
	        $("#rxact_prioridad").fadeOut("slow");
		},
		cancelarPrioridadGRxAct: function(){
	        $("#rxact_prioridad").fadeOut("slow");
	        $('.tr_rxact').css("background","white");
	        $('.tr_rxact').css("color","black");
	    },
	    setImprescindibles: function(idgr, idchk){
	    	if ($("#" + idchk).attr("checked")==true)
	    		ancoragoh.server.recursos.actualizarImprescindibles(idgr, 1);
	    	else
	    		ancoragoh.server.recursos.actualizarImprescindibles(idgr, 0);
	    },
	    setNecesariosGRxAct: function(idgr, nec){
	    	this.event.message = "Grupo de recursos actualizado satisfactoriamente";
	    	ancoragoh.server.recursos.actualizarNecesarios(idgr, nec, this.event.fcomplete);
	    },
	    eliminarGRxAct: function (){		
			var idrxact = $("#idGRxactToDelete").val();
			this.event.message = 'Grupo de otros recursos para actividad eliminado satisfactoriamente';
			ancoragoh.server.recursos.eliminarGrupoDeRecursosParaActividad(idrxact, this.event.fcomplete);
			$('#frame_del_GRxAct').fadeOut('slow');
		},
	    eliminarRxAct: function (){		
			var idgr = $("#idGRxactToDelete").val();
			var idrxact = $("#idRxactToDelete").val();
			this.event.message = 'Recurso para actividad eliminado satisfactoriamente';
			ancoragoh.server.recursos.eliminarRecursoParaActividad(idgr, idrxact, this.event.fcomplete);
			$('#frame_del_RxAct').fadeOut('slow');
		},
		agregarGrupoRecursos: function(){
			this.event.message = 'Grupo de otros recursos para actividad agregado satisfactoriamente';
	        ancoragoh.server.recursos.nuevoGrupoDeRecursosParaActividad(ancoragoh.client.browsers.desgloses.flag_idplantilla, this.event.fcomplete);
	    },
	    showFrameEliminarGRxAct: function(idgr){        
			$("#idGRxactToDelete").val(idgr);
			$("#frame_del_GRxAct").fadeIn("slow");
		},
	    showFrameEliminarRxAct: function(idgr, idr){ 
			$("#idGRxactToDelete").val(idgr);
			$("#idRxactToDelete").val(idr);
			$("#frame_del_RxAct").fadeIn("slow");
		}
		
};