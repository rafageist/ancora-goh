ancoragoh.client.browsers.recursos = {
	showed: "profesores",
	show: function(){
		ancoragoh.client.loadHTMLToWin({app_event_get_browser_recursos: "yes"}, "recursos", true, false);
	},
	refrescar: function(){
		switch(this.showed){
		case "profesores":
			this.showProfesores();
			break;
		case "lugares":
			this.showLugares();
			break
		case "otros":
			this.showOtros()
		}
	},
	showProfesores: function(){
		ajax_load_html("index.php?execute", {app_event_get_minibrowser_profesores: "yes"}, "div-recursos");
		this.showed = "profesores";
	},
	showLugares: function(){
		ajax_load_html("index.php?execute", {app_event_get_minibrowser_lugares: "yes"}, "div-recursos");
		this.showed = "lugares";
	},
	showOtros: function(){
		ajax_load_html("index.php?execute", {app_event_get_minibrowser_otrosrecursos: "yes"}, "div-recursos");
		this.showed = "otros";
	}
};