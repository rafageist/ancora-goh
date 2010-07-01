ancoragoh.client.forms.plantilla = {
	event: {
		message: "",
		fcomplete: function(res, status){
			if ( status == "success" || status == "notmodified")
				ancoragoh.client.browsers.desgloses.refrescaDesgloseDetalle();
				ancoragoh.client.showMessage(ancoragoh.client.forms.plantilla.event.message);
		},
		agregar: function(){
			var desglose = $("#edtDesglose").val();
			var orden = $("#edtOrden").val();
			var exige_profe = $("#chkExigeProfe").attr("checked");
			var exige_lugar = $("#chkExigeLugar").attr("checked");
			var clasificacion = $("#field_PlantillaDeActividad_clasificacion").val();
			this.message = 'Plantilla agregada satisfactoriamente';
			ancoragoh.server.plantillas.agregar(desglose, orden, clasificacion, exige_profe, exige_lugar, this.fcomplete);                
			ancoragoh.client.hidewin("new_plantilla");
		},
	
	modificar: function(){
		var id = $("#edtId").val();
		var desglose = $("#edtDesglose").val();
		var orden = $("#edtOrden").val();
		var exige_profe = $("#chkExigeProfe").attr("checked");
		var exige_lugar = $("#chkExigeLugar").attr("checked");
		var clasificacion = $("#field_PlantillaDeActividad_clasificacion").val();
		
		this.message = 'Plantilla actualizada satisfactoriamente';
		
		ancoragoh.server.plantillas.update(id, desglose, orden, clasificacion, exige_profe, exige_lugar, this.fcomplete);
		ancoragoh.client.hidewin("edit_plantilla");
	},
	
	eliminar: function(){
		var id = $("#edtId").val();
		ancoragoh.server.plantillas.remove(id, this.fcomplete);
		this.message = 'Plantilla eliminada satisfactoriamente';
		ancoragoh.client.hidewin("edit_plantilla");
	},
	
	cancelar: function(){
		ancoragoh.client.hidewin("new_plantilla");
		ancoragoh.client.hidewin("edit_plantilla");
	}
	},
	showForEdit: function(pid){
		ancoragoh.client.loadHTMLToWin({app_event_get_form_edit_plantilla: "yes", id: pid}, "edit_plantilla", false, true);
	},
	showForNew: function(iddesglose){
		ancoragoh.client.loadHTMLToWin({app_event_get_form_new_plantilla: "yes", desglose: iddesglose}, "new_plantilla", false, true);
	}/*,
	editPlantillaDeActividad: function(idplantilla){
		showDataForm("index.php?execute",{app_event_custom_getui_EditarPlantillaDeActividad: "yes", id: idplantilla});
	},*/
}