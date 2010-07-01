ancoragoh.client.forms.grupo = {
		event: {
				message: "",
				fcomplete: function(res, status){
					if ( status == "success" || status == "notmodified"){
						ancoragoh.client.browsers.organizacion.refrescaGruposFiltrados();
						ancoragoh.client.showMessage(ancoragoh.client.forms.grupo.event.message);
					}
				},
				agregar: function(){
					var descrip = $("#field_Grupo_Descripcion").val();
			        var clasif = ancoragoh.client.browsers.organizacion.flag_clasif;
			        var idesp = ancoragoh.client.browsers.organizacion.flag_especialidad;
			        var idnivel = ancoragoh.client.browsers.organizacion.flag_nivel;
			        var idplanif = ancoragoh.client.browsers.organizacion.flag_planif;
			        this.message = "Grupo insertado satisfactoriamente";
			        ancoragoh.server.grupos.agregar(descrip, clasif, idesp, idnivel, idplanif, this.fcomplete);
			        ancoragoh.client.hidewin("new_grupo");
				},
				modificar: function (){
			        var id = $("#field_Grupo_Id").val();
			        var descrip = $("#field_Grupo_Descripcion").val();
			        var clasif = ancoragoh.client.browsers.organizacion.flag_clasif;
			        var idesp = ancoragoh.client.browsers.organizacion.flag_especialidad;
			        var idnivel = ancoragoh.client.browsers.organizacion.flag_nivel;
			        var idplanif = ancoragoh.client.browsers.organizacion.flag_planif;
			        this.message = "Grupo actualizado satisfactoriamente";
			        ancoragoh.server.grupos.modificar(id, descrip, clasif, idesp, idnivel, idplanif, this.fcomplete);
			        ancoragoh.client.browsers.organizacion.refrescarCentro();
			        ancoragoh.client.hidewin("edit_grupo");
				},
		    
			  eliminar: function(){
				    var id = $("#field_Grupo_Id").val();
			        var clasif = ancoragoh.client.browsers.organizacion.flag_clasif;
			        var idesp = ancoragoh.client.browsers.organizacion.flag_especialidad;
			        var idnivel = ancoragoh.client.browsers.organizacion.flag_nivel;
			        var idplanif = ancoragoh.client.browsers.organizacion.flag_planif;
			        this.message = "Grupo eliminado satisfactoriamente";
			        ancoragoh.server.grupos.eliminar(id, this.fcomplete);
			        ancoragoh.client.hidewin("edit_grupo");
				},
			cancelar: function (){
				ancoragoh.client.hidewin("new_grupo");
				ancoragoh.client.hidewin("edit_grupo");
		    }	
		},
		showForNew: function(idclasif, idesp, idnivel){
			var idesp   = ancoragoh.client.browsers.organizacion.flag_especialidad;
            var idclasif = ancoragoh.client.browsers.organizacion.flag_clasif;
            var idnivel  = ancoragoh.client.browsers.organizacion.flag_nivel;
			ajax_load_htmlToWin("index.php?execute",{app_event_get_form_new_grupo:"yes", clasificacion: idclasif, especialidad: idesp, nivel: idnivel}, "new_grupo", false, true); 
	    },
		showForEdit: function(idgrupo){
		    ajax_load_htmlToWin("index.php?execute",{app_event_get_form_edit_grupo:"yes", grupo: idgrupo}, "edit_grupo", false, true); 
		}
}