ancoragoh.client.forms.lxact = {
		event: {
			message: "",
			fcomplete: function(res, status){
						if ( status == "success" || status == "notmodified"){
							ancoragoh.client.browsers.desgloses.showLugaresParaActividad();
							ancoragoh.client.showMessage(ancoragoh.client.forms.lxact.event.message);
						}
			},			
			agregar: function (cantdisponibles){
				var cant = cantdisponibles;
				var idplantilla = ancoragoh.client.browsers.desgloses.flag_idplantilla;

				for (var i = 1; i <= cant; i++){
					var chk = $("#chkLugarParaActividad_" + i);
					if (chk.attr("checked")) {
						var id = $("#edtLugarDisponible_" + i).val();
						ancoragoh.server.recursos.agregarLugarParaActividad(idplantilla, id, this.fcomplete);
					}
				}
				ancoragoh.client.browsers.desgloses.showLugaresParaActividad();
			         
		        this.message = "Lugares asignados satisfactoriamente";
		        ancoragoh.client.hidewin("new_lxact");
			},
			eliminar: function(){
				var idplantilla = ancoragoh.client.browsers.desgloses.flag_idplantilla;
				var idlugar = $("#idLxactToDelete").val();
				var tri = $("#idxLxactToDelete").val();
				this.message = 'Lugar para actividad eliminado satisfactoriamente';
				ancoragoh.server.recursos.eliminarLugarParaActividad(idplantilla,idlugar, this.fcomplete);
			},
			cancelar: function(){
				ancoragoh.client.hidewin("new_lxact");
				ancoragoh.client.hidewin("edit_lxact");
			}
		},
		showForNew: function(){
		    ancoragoh.client.loadHTMLToWin({plantilla: ancoragoh.client.browsers.desgloses.flag_idplantilla, app_event_custom_getui_NuevoLugarParaActividad:"yes"}, "new_lxact", false, true); 
		}
        /*showForEdit: function(idpxact){
			ancoragoh.client.loadHTMLToWin({pxact: idpxact, app_event_custom_getui_EditarProfesorParaActividad:"yes"}, "edit_pxact", false, true); 
		}*/
};