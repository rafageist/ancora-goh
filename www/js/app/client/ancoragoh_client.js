/**
 * @name main
 */
ancoragoh.client = {
	windows : [],
	active_window : "",
	getWindowIdx : function(pid) {
		var cwins = this.windows.length;
		for ( var i = 0; i < cwins; i++) {
			var id = this.windows[i].id;
			if (id == pid)
				return i;
		}
		return -1;
	},

	showModal : function(id) {

		var pid = "#win_" + id;
		var idx = this.getWindowIdx(id);

		var bgdiv = $('<div>').attr( {
			className : 'bgtransparent',
			id : 'bgtransparent'
		});

		// agregamos nuevo div a la pagina
		$('body').append(bgdiv);

		// obtenemos ancho y alto de la ventana del explorer
		var wscr = $(window).width();
		var hscr = $(window).height();

		// establecemos las dimensiones del fondo
		$('#bgtransparent').css("width", wscr);
		$('#bgtransparent').css("height", hscr);

		$(pid).fadeIn("slow");
		$(pid).css("z-index", 1000);
	},
	showWin : function(idwin, data, modal, fcomplete) {
		ajax_load_htmlToWin("index.php?execute", data, idwin, modal, fcomplete);
	},
	hideModal : function(id) {
		var pid = "#win_" + id;
		$('#bgmodal').remove();
		$('#bgtransparent').remove();
		$(pid).fadeOut("fast");
	},
	showOnTopWindow : function(id) {

		var pid = "#win_" + id;
		var pidt = "#win_title_" + id;
		var cwins = this.windows.length;
		var idx = this.getWindowIdx(id);

		// Cuando dan click en cerrar no debe mostrarse
		if (this.windows[idx].cerrando == false) {

			if (this.windows[idx].showed == false)
				$(pid).fadeIn("slow");

			this.windows[idx].showed == true;

			if (idx != -1) {

				this.active_window = id;

				// Mostrar modal
				if (this.windows[idx].modal == true) {
					this.showModal(id);
					return true;
				}

				if (this.windows[idx].status == "minmax"
						|| this.windows[idx].status == "minnormal") {
					$("#shortcut_" + id).effect("transfer", {
						to : "#win_" + id,
						className : 'ui-effects-transfer'
					}, 500, function() {
					});
				}

				if (this.windows[idx].status == "minmax") {
					this.windows[idx].status = "maximized";
				} else {
					if (this.windows[idx].status == "minnormal")
						this.windows[idx].status = "normal";
				}

				$(pid).css("z-index", 105);
				$(pidt).css("opacity", 1);
				$(pidt).css("filter", "progid:DXImageTransform.Microsoft.Alpha(opacity=100)");

				for ( var i = 0; i < cwins; i++)
					if (idx != i) {
						if ($("#win_" + this.windows[i].id).css("display") != 'none') {
							$("#win_" + this.windows[i].id).css("z-index", 99);
							$("#win_title_" + this.windows[i].id).css(
									"opacity", 0.5);
							$("#win_title_" + this.windows[i].id)
									.css("filter",
											"progid:DXImageTransform.Microsoft.Alpha(opacity=50)");
						}
					}
			}

		} else
			this.windows[idx].cerrando = false;

	},
	maxrestore : function(id) {
		var idx = this.getWindowIdx(id);
		var pid = "#win_" + id;

		if (idx != -1) {
			var w = this.windows[idx];

			if (w.status == "normal") {
				// salvando la configuracion actual
				this.windows[idx].height = $(pid).css("height");
				this.windows[idx].width = $(pid).css("width");
				this.windows[idx].top = $(pid).css("top");
				this.windows[idx].left = $(pid).css("left");
				this.windows[idx].status = "maximized";

				// maximizando
				$(pid).css("top", "0px");
				$(pid).css("left", "0px");
				$(pid).css("height", ($(window).height() - 102) + "px");
				$(pid).css("width", ($(window).width() - 25) + "px");

			} else {
				if (w.status == "maximized") {
					$(pid).css("top", w.top);
					$(pid).css("left", w.left);
					$(pid).css("height", w.height);
					$(pid).css("width", w.width);
					this.windows[idx].status = "normal";
				}
			}
		}

	},
	minimized : function(id) {
		var idx = this.getWindowIdx(id);
		if (idx != -1) {
			this.windows[idx].showed = false;

			if (this.windows[idx].status == "maximized")
				this.windows[idx].status = "minmax";
			if (this.windows[idx].status == "normal")
				this.windows[idx].status = "minnormal";

			$("#win_" + id).effect("transfer", {
				to : "#shortcut_" + id,
				className : 'ui-effects-transfer'
			}, 500, function() {
				$("#win_" + id).fadeOut("fast");
			});

		}
	},
	hidewin : function(id) {
		var pid = "#win_" + id;
		var idx = this.getWindowIdx(id);
		if (idx != -1) {
			if ($(pid).css('display') != 'none') {
				this.windows[idx].showed = false;
				this.windows[idx].cerrando = true;
				$("#form_" + id).html("");
				$("#win_" + id).fadeOut("slow");
				$("#shortcut_" + id).fadeOut("slow");
				if (this.windows[idx].modal == true) {
					this.hideModal(id);
				}
			}
		}

	},
	centerwin : function(id) {
		var w = $("#win_" + id).width();
		var h = $("#win_" + id).height();
		$("#win_" + id).css("left", ($(window).width() - w) / 2);
		$("#win_" + id).css("top", ($(window).height() - h) / 2);
	},
	updateWinProperties : function(id) {
		var idx = this.getWindowIdx(pid);
		var pid = "#win_" + id;
		if (idx != -1) {
			this.windows[idx].height = $(pid).css("height");
			this.windows[idx].width = $(pid).css("width");
			this.windows[idx].top = $(pid).css("top");
			this.windows[idx].left = $(pid).css("left");
		}
	},
	showStatus : function(status) {
		$("#footer").html(status);
	},
	showMessage : function(msg, icon) {
		if (icon == null)
			icon = "aceptar.png";
		$("#imessage")
				.html(
						'<table><tr><td valign="center"><img src="images/'
								+ icon
								+ '"></td><td valign = "center"><label>'
								+ msg
								+ "</label> <input id = 'btnCloseMessage' type='button' value ='x' onclick = '$(\"#imessage\").fadeOut();'></td></tr></table>");
		$("#imessage").fadeIn("slow");
	},
	showhide : function(id) {
		if ($('#' + id).css('display') == 'block')
			$('#' + id).css('display', 'none');
		else
			$('#' + id).css('display', 'block');
	},
	fadeinout : function(id) {
		if ($('#' + id).css('display') == 'block')
			$('#' + id).fadeOut('slow');
		else
			$('#' + id).fadeIn('slow');
	},
	showStatusReady : function() {
		$("#footer")
				.html(
						"<div style='margin:auto;top: 50%';> <label><img src='images/listo.png'> Listo</label></div>");
	},
	showStatusLoading : function() {
		$("#footer")
				.html(
						"<div style='margin:auto;top: 50%';> <label><img src='images/cargando.png'> Cargando...</label></div>");
	},

	loadHTMLToWin : function(pdata, idwin, maximized, modal, fcomplete) {

		if (maximized == null)
			maximized = true;
		if (modal == null)
			modal = false;

		var idx = this.getWindowIdx(idwin);

		if (fcomplete == null)
			fcomplete = function(res, status) {
				if (status == "success" || status == "notmodified") {

					$("#form_" + idwin).html(res.responseText);
					$("#win_" + idwin).fadeIn("slow");

					ancoragoh.client.showStatusReady();

					var idx = ancoragoh.client.getWindowIdx(idwin);
					if (idx != -1) {
						if (ancoragoh.client.windows[idx].status != "maximized") {

							ancoragoh.client.centerwin(idwin);

							if (maximized == true)
								ancoragoh.client.maxrestore(idwin);

							$("#shortcut_" + idwin).fadeIn("slow");

							ancoragoh.client.windows[idx].showed = true;

							if (modal == true)
								ancoragoh.client.showModal(idwin);
							else
								ancoragoh.client.showOnTopWindow(idwin);
						}
					}
				}

			};

		var cuerpoajax = {
			url : "index.php?execute",
			type : "POST",
			dataType : "html",
			data : pdata,
			complete : fcomplete
		};

		jQuery.ajax(cuerpoajax);
	}
};

function begin() {

}