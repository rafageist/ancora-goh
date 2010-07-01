ancoragoh.client.forms.desglose = {
	event: {
		message: "",
		fcomplete: function(res, status){
					if ( status == "success" || status == "notmodified")
						ancoragoh.client.browsers.organizacion.refrescaNiveles();
						ancoragoh.client.showMessage(ancoragoh.client.forms.desglose.event.message);
		},
		modificar: function(){
			var mindias = $("#edtMinDias").val();
			var maxdias = $("#edtMaxDias").val();
			var respeta = $("#chkRespetarOrden").attr("checked");
			var mismodia = $("#chkMismoDia").attr("checked");
			var id = $("#edtIdDesglose").val();
			var asignatura = $("#edtAsigDesglose").val();
			var periodo = $("#edtPeriodoDesglose").val();
	        
			if (respeta == true) respeta =1; else respeta = 0;
			if (mismodia == true) mismodia =1; else mismodia = 0;
			
			this.message ='Propiedades del desglose actualizadas';
			ancoragoh.server.desgloses.actualizar(id, asignatura, periodo, mindias, maxdias, respeta, mismodia, this.fcomplete);
			
			ancoragoh.client.hidewin("edit_desglose");
		},
		cancelar: function(){
			ancoragoh.client.hidewin("edit_desglose");
		}
	},
	showForEdit: function(pid){
		ancoragoh.client.loadHTMLToWin({app_event_get_form_edit_desglose: "yes", id: pid}, "edit_desglose", false, true);
	}
}