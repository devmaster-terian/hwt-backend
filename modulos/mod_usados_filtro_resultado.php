<link rel="stylesheet" type="text/css" href="/hwt-usados/recursos/css/usados_estilo.css">
<?php
    $doc =& JFactory::getDocument();
    $doc->addScript("https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.6/handlebars.js");
?>
<div id="mod_usados_filtro_resultado" class="usados_filtro_resultado"></div>

<script>
    jQuery.noConflict();
    jQuery(document).ready(function ($) {
        $("#btn-nueva-busqueda").hide(500);
        $('#btn-nueva-busqueda').click(function () {
            $(window).scrollTo(0,0);
        });//btn-nueva-busqueda
    });
</script>