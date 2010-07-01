<div id = "access_denied" class = "access_denied" >
   <table><tr><td valign="center"><img src="images/cancel.png"> <label style = "color: white"> Acceso denegado </label></td></tr></table>
</div>

<script>
    function hideAccessDenied(){
        document.getElementById('access_denied').style.display = "none";
    }
    
    setTimeout("hideAccessDenied()",2000);
</script>