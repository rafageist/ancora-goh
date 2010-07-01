ancoragoh.client.forms.costotraslado = {
	show : function(idlugar) {
		ancoragoh.client.loadHTMLToWin( {
			app_event_get_form_edit_lugar_costos_de_traslado : "yes",
			lugar : idlugar
		}, "edit_costotraslado", false, true);
	},
	event : {
		message : "",
		fcomplete : function(res, status) {
			if (status == "success" || status == "notmodified") {
				ancoragoh.client
						.showMessage(ancoragoh.client.forms.costotraslado.event.message);
			}
		},
		aceptar : function() {
			this.message = 'Costos de traslado actualizados';
			var cant = $("#data_costotraslado_cantlugs").val();
			var origen = $("#data_costotraslado_idlugar").val();

			for (i = 1; i <= cant; i++) {
				if (i == cant)
					ancoragoh.server.lugares.updateCostosDeTraslado(origen, $(
							"#ct_destino_" + i).val(), $("#ct_" + i).val(),
							this.fcomplete);
				else
					ancoragoh.server.lugares.updateCostosDeTraslado(origen, $(
							"#ct_destino_" + i).val(), $("#ct_" + i).val());
			}

			ancoragoh.client.hidewin("edit_costotraslado");
		},
		cancelar : function() {
			ancoragoh.client.hidewin("edit_costotraslado");
		}
	}
};