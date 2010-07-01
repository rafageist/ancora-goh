function showhide(id){
	if ($('#' + id).css('display')=='block') 
		$('#' + id).css('display','none'); 
	else
		$('#' + id).css('display','block'); 
}
