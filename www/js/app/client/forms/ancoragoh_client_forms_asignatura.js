ancoragoh.client.forms.asignatura = {
		event: {
			message: "",
			fcomplete: function(res, status){
						if ( status == "success" || status == "notmodified"){
							ancoragoh.client.browsers.desgloses.refrescarAsignaturas();
							ancoragoh.client.showMessage(ancoragoh.client.forms.asignatura.event.message);
						}
			},
			agregar: function(){
				var descripcion = $("#field_Asignatura_Descripcion").val();
			    var especialidad  =  $("#field_Asignatura_Especialidad").val();
			    var nivel  =  $("#field_Asignatura_Nivel").val();
			    this.message = "Asignatura insertada satisfactoriamente";
				ancoragoh.server.asignaturas.add(descripcion, especialidad, nivel, this.fcomplete);	
			    ancoragoh.client.hidewin("new_asignatura");
			},
		
			modificar: function(){
				var id = $("#field_Asignatura_Id").val();
				var descripcion = $("#field_Asignatura_Descripcion").val();
			    var especialidad  =  $("#field_Asignatura_Especialidad").val();
			    var nivel  =  $("#field_Asignatura_Nivel").val();
		        
				ancoragoh.server.asignaturas.update(id,descripcion,especialidad,nivel, this.fcomplete);	
				this.message = "Asignatura modificada satisfactoriamente";
				ancoragoh.client.hidewin("edit_asignatura");
			},
		
			eliminar: function(){
				var id = $("#field_Asignatura_Id").val();
				this.message = "Asignatura eliminada satisfactoriamente";
				ancoragoh.server.asignaturas.remove(id, this.fcomplete);
				ancoragoh.client.hidewin("edit_asignatura");
			},
		
			cancelar: function(){
				ancoragoh.client.hidewin("new_asignatura");
				ancoragoh.client.hidewin("edit_asignatura");
			}
		},
		showForNew: function(idesp, idnivel){
		    ajax_load_htmlToWin("index.php?execute", {app_event_get_form_new_asignatura: "yes", especialidad: idesp, nivel: idnivel}, "new_asignatura", false, true);
		},
		showForEdit: function(pid){
			ajax_load_htmlToWin("index.php?execute", {app_event_get_form_edit_asignatura: "yes", id: pid}, "edit_asignatura", false, true);
		}
};
