ancoragoh.client.forms.planificacion = {
	
    event: {
		fcomplete: function(res, status){
			if ( status == "success" || status == "notmodified")
				ancoragoh.client.browsers.planificaciones.refrescar();
		},
        agregar: function(){
            var descripcion = $("#new_Planificacion_Descripcion").val();
            var fechaini = $("#new_Planificacion_FechaInicial").val();
            var fechafin = $("#new_Planificacion_FechaFinal").val();
            
            ancoragoh.server.planificaciones.add(descripcion, fechaini, fechafin, this.fcomplete);
            ancoragoh.client.showMessage('Planificaci&oacute;n insertada satisfactoriamente');
            ancoragoh.client.hidewin("new_planificacion");
        },

        cancelar: function(){
            ancoragoh.client.hidewin("new_planificacion");
            ancoragoh.client.hidewin("edit_planificacion");
        },

        eliminar: function(){
            var id = $("#field_Planificacion_Id").val();			
            ancoragoh.server.planificaciones.remove(id, this.fcomplete);
            ancoragoh.client.showMessage('Planificaci&oacute;n eliminada satisfactoriamente');
            ancoragoh.client.hidewin("edit_planificacion");
        },
        modificar: function(){
            var id = $("#field_Planificacion_Id").val();        
            var descripcion = $("#edit_Planificacion_Descripcion").val();
            var fechaini = $("#edit_Planificacion_FechaInicial").val();
            var fechafin = $("#edit_Planificacion_FechaFinal").val();
            ancoragoh.server.planificaciones.update(id, descripcion, fechaini, fechafin, this.fcomplete);
            ancoragoh.client.showMessage('Planificaci&oacute;n modificada satisfactoriamente');
	        ancoragoh.client.hidewin("edit_planificacion");
        }
    },
    showForNew: function(){
        ajax_load_htmlToWin("index.php?execute", {app_event_get_form_new_Planificacion: "yes"}, "new_planificacion", false);
    },
    showForEdit: function(pid){
        ajax_load_htmlToWin("index.php?execute", {app_event_get_form_edit_Planificacion: "yes", id: pid}, "edit_planificacion", false, true);
    }
};