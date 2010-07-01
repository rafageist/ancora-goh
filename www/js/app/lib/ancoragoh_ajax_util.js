
/**
  * @name ajax_load_htmlToWin
  * @desc Load HTML from URL to a Window
  */  
function ajax_load_htmlToWin(purl, pdata, idwin, maximized, modal, fcomplete){
            
    if (maximized==null) maximized = true;
    if (modal==null) modal=false;
    var idx = ancoragoh.client.getWindowIdx(idwin);
    
    if (fcomplete == null)
	    fcomplete = function(res, status){
	        if ( status == "success" || status == "notmodified" ){	
	            $("#form_" + idwin).html(res.responseText);
	            $("#win_" + idwin).fadeIn("slow");

	            ancoragoh.client.showStatusReady();
	            
	            var idx = ancoragoh.client.getWindowIdx(idwin);
	            if (idx != -1){
	                if (ancoragoh.client.windows[idx].status != "maximized"){
	                    ancoragoh.client.centerwin(idwin);
	                    if (maximized == true) ancoragoh.client.maxrestore(idwin);
	                    $("#shortcut_" + idwin).fadeIn("slow");
	                    ancoragoh.client.windows[idx].showed = true;
	                    if (modal==true) 
	                    	ancoragoh.client.showModal(idwin);
	                    else
	                    	ancoragoh.client.showOnTopWindow(idwin);
	                }
	            }
	         }   
	    };
    
    var cuerpoajax = {
            url: purl,
            type: "POST",
            dataType: "html",
            data: pdata,
            complete: fcomplete 
        };
    
    
    jQuery.ajax(cuerpoajax);
    
}

/**
  * @name ajax_load_html
  * @desc Load HTML from URL to a element
  */

function ajax_load_html(purl, pdata, iddiv, fcomplete){
	ancoragoh.client.showStatusLoading();
    
    if (fcomplete == null)
	    fcomplete = function(res, status){
        if ( status == "success" || status == "notmodified" )	
            $("#" + iddiv).html(res.responseText);
            $("#" + iddiv).fadeIn("slow");
            ancoragoh.client.showStatusReady();
    	};
    
    jQuery.ajax({
        url: purl,
        type: "POST",
        dataType: "html",
        data: pdata,
        complete: fcomplete
    });
}

/**
  * @name executeURL
  * @desc Execute event
  */
function ajax_execute_event(pdata, fcomplete){
	 if (fcomplete == null) fcomplete = function(res, status){};
    jQuery.ajax({
                url: "index.php?execute",
                type: "POST",
                dataType: "html",
                data: pdata,
                complete: fcomplete
            });
}