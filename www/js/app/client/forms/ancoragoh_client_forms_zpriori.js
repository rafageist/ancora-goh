ancoragoh.client.forms.zpriori = {
	show : function(idclasif) {
		this.idclasif = idclasif;
		ancoragoh.client.loadHTMLToWin( {
			app_event_get_form_edit_zpriori : "yes",
			clasif : idclasif
		}, "edit_zpriori", false, true);
	},
	event : {
		message: "",
		fcomplete: function(res, status){
					if ( status == "success" || status == "notmodified"){
						ancoragoh.client.showMessage(ancoragoh.client.forms.zpriori.event.message);
					}
		},
		aceptar : function() {
			var cd = $("#data_zpriori_cd").val();
			var ct = $("#data_zpriori_ct").val();
			var idclasif = $("#data_zpriori_idclasif").val();
			var idperiodo = $("#zpriori_cboPeriodo").val();
			
			var zp = '';
			for (i = 1; i <= cd; i++) {
				for (j = 1; j <= ct; j++) {
					var v = $("#zp_" + i + "_" + j).val();
					zp = zp + v;
					if (j < ct)
						zp = zp + ',';
				}
				if (i < cd)
					zp = zp + ';';
			}
			this.message = 'Zonas de prioridad actualizadas';
			ancoragoh.server.clasifact.updateZpriori(idclasif, idperiodo, zp, this.fcomplete);
			ancoragoh.client.hidewin("edit_zpriori");
		},
		cancelar : function() {
			ancoragoh.client.hidewin("edit_zpriori");
		}
	},
	loadzpriori : function() {
		var idperiodo = $("#zpriori_cboPeriodo").val();
		var idclasif = $("#data_zpriori_idclasif").val();
		ajax_load_html("index.php?execute", {
			app_event_get_form_edit_zpriori : "yes",
			clasif : idclasif,
			periodo : idperiodo
		}, "zpriori_detail");
	}

};