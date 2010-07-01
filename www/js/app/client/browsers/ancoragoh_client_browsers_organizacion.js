
ancoragoh.client.browsers.organizacion = {
	// variables banderas
	flag_especialidad: "",
	flag_nivel: "",
	flag_vista: "brigadas",
	flag_planif: "",
	flag_clasif: "",
	// metodos
	refrescarCentro: function(){
		if (this.flag_vista == "brigadas"){
			if (this.flag_especialidad != "" && this.flag_nivel != ""){
				ancoragoh.client.browsers.organizacion.showBrigadasFiltradas();
			}
		}		
		if (this.flag_vista == "grupos"){
			if (this.flag_especialidad != "" && this.flag_nivel != ""){
				ancoragoh.client.browsers.organizacion.showGrupos();
			}
		}
	},
	
	showBrigadasFiltradas: function(){
		var idesp = this.flag_especialidad;
		var idnivel = this.flag_nivel;
		ajax_load_html("index.php?execute", {app_event_get_browser_Brigadas_filtradas: "yes", especialidad: idesp, nivel: idnivel}, 'div-organizacion-center');
	},
	
	showGrupos: function(){
		var idesp = this.flag_especialidad;
		var idnivel = this.flag_nivel;
		ajax_load_html("index.php?execute", {app_event_get_browser_GruposXClasif: "yes", especialidad: idesp, nivel: idnivel}, 'div-organizacion-center');
	},
	
	//eventos
	seleccionaEspecialidad: function(id){
		this.flag_especialidad = id;
		this.refrescarCentro();
	},
	
	seleccionaNivel: function(id){
		this.flag_nivel = id;
		this.refrescarCentro();
	},
	
	seleccionaPlanificacion: function(id){
		this.flag_planif = id;
		this.refrescarCentro();
	},
	
	seleccionarBrigadas: function(){
		this.flag_vista = "brigadas";
		this.refrescarCentro();
	},
	
	seleccionarGrupos: function(){
		this.flag_vista = "grupos";
		this.refrescarCentro();
	},
	
	showGruposFiltrados: function (idclasif){
        this.flag_clasif = idclasif;
        this.refrescaGruposFiltrados();
    },
    
    refrescaGruposFiltrados: function(){
		var idesp = this.flag_especialidad;
		var idnivel = this.flag_nivel;
        var idplanif = this.flag_planif;
        var idclasif = this.flag_clasif;
        ajax_load_html("index.php?execute", {app_event_get_browser_GruposXClasifFiltrados: "yes", especialidad: idesp, nivel: idnivel, clasificacion: idclasif, planificacion: idplanif}, 'div_grupos');
    },
    
	refrescaNiveles: function(){
		ajax_load_html("index.php?execute", {app_event_get_minibrowser_niveles: "yes"}, "div-organizacion-left");
	},
	
	refrescaEspecialidades: function(){
		ajax_load_html("index.php?execute", {app_event_get_minibrowser_especialidades: "yes"}, "div-organizacion-top");	
	},
	
    seleccionaClasificacion: function(id){
		this.flag_clasif = id;
	}
}