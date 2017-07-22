<link rel="stylesheet" type="text/css" href="http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.9.0/css/lightbox.css">
<link rel="stylesheet" type="text/css" href="/hwt-usados/recursos/css/usados_estilo.css">
<!-- <link rel="stylesheet" type="text/css" href="/hwt-usados/recursos/lib/colorbox-master/colorbox.css"> -->

<?php
$joomlaDocument =& JFactory::getDocument();
$joomlaDocument->addScript("https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.6/handlebars.js");
$joomlaDocument->addScript("https://cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.0/jquery.scrollTo.min.js");
$joomlaDocument->addScript("https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.9.0/js/lightbox.js");
$joomlaDocument->addScript("https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.4/jquery.colorbox.js");
$joomlaDocument->addScript("https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.4/i18n/jquery.colorbox-es.js");
?>
<!-- ECRC: Sección de los Filtros de Usados -->
<div id="mod_usados_filtro">
    <button id="btn-filtro-vehiculos" type="button" class="boton-titulo">FILTRAR VEHICULOS</button>

    <!-- DIV donde se realizará el Desplegado de la Tabla  -->
    <select id="seleccionTipoUnidad" class="seleccion-unidad" id="unidad_tipo">
        <option>SELECCIONE EL TIPO DE UNIDAD</option>
    </select>
    <select id="seleccionMarca" class="seleccion-unidad">
        <option>SELECCIONE MARCA</option>
    </select>
    <select id="seleccionModelo" class="seleccion-unidad">
        <option>SELECCIONE MODELO</option>
    </select>

    <!-- Año de la Unidad -->
    <div id="unidad_ann" class="completo_div">
        <div id="div-ann-inicial" class="mitad_izq_div">
            <select id="seleccionAnnInicial" class="seleccion-unidad-mitad">
                <option>AÑO INICIAL</option>
            </select>
        </div>
        <div id="div-ann-final" class="mitad_izq_div">
            <select id="seleccionAnnFinal" class="seleccion-unidad-mitad">
                <option>AÑO FINAL</option>
            </select>
        </div>
    </div>

    <!-- Precio de la Unidad -->
    <div id="unidad_precio" class="completo_div">
        <div id="div-precio-inicial" class="mitad_izq_div">
            <select id="seleccionPrecioInicial" class="seleccion-unidad-mitad">
                <option>PRECIO INICIAL</option>
            </select>
        </div>
        <div id="div-precio-final" class="mitad_izq_div">
            <select id="seleccionPrecioFinal" class="seleccion-unidad-mitad">
                <option>PRECIO FINAL</option>
            </select>
        </div>
    </div>

    <!-- Ubicacion -->
    <select id="seleccionUbicacion" class="seleccion-unidad">
        <option>UBICACION</option>
    </select>

    <button id="btn-unidad_busca_productos" type="button" class="boton-envio">BUSCAR UNIDADES</button>


</div>
<div id="mod_fin_filtro"></div>

<!-- ECRC: Plantilla de las Miniaturas del Producto -->
<script id="plantilla-producto-thumb" type="text/x-handlebars-template">
    {{{scriptProductoThumb}}}

    <div id="producto_resultado" class="new_html_code">
        <div id="producto_resultado_titulo" class="boton-titulo-unidad">{{{unidadTitulo}}}</div>

        <div id="producto_resultado_imagen">
            <object data={{rutaImagen}} type="image/jpg" class="item-unidad-imagen">
                <img id="img_thumb_producto"
                     class="item-unidad-imagen"
                     src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png">
            </object>
        </div>

        <div id="producto_resultado_informacion" class="item-unidad-informacion">
            <table class="tabla-unidad-informacion-celda" align="center">
                <!---
                <tr>
                    <td align="right" class="item-unidad-informacion-celda"><b>FABRICACION </b></td>
                    <td id="celda_ann_fabricacion"><b>{{annFabricacion}}</b></td>
                </tr>
                <tr>
                    <td align="right"><b>KILOMETRAJE </b></td>
                    <td id="celda_kilometraje"><b>{{kilometraje}}</b></td>
                </tr>
                -->

                <tr>
                    <td align="right"><b>UBICADO EN </b></td>
                    <td id="celda_ubicacion"><b>{{ubicacion}}</b></td>
                </tr>
            </table>
        </div>
        <!--
        <div id="producto_resultado_precio">
            <button type="button" class="boton-unidad-precio">{{unidadPrecio}}</button>
        </div>
        -->
        <div id="producto_resultado_01_mas">
            <button type="button" id="{{btnVistaProducto}}" class="boton-unidad-mas">Ver Detalle</button>
        </div>
    </div>
</script>

<!-- ECRC: Plantilla para la Imagen de Vista del Producto -->
<script id="plantilla-imagen-vista-producto" type="text/x-handlebars-template">
    <object data={{productoImagen}}
            type="image/jpg"
            class="producto-imagen">
        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png">
    </object>
</script>


<!-- ECRC: Plantilla para la Vista del Producto -->
<script id="plantilla-vista-producto" type="text/x-handlebars-template">
    <div id="producto-titulo-ventana" class="producto-titulo-ventana">
        <div id="producto-titulo-detalle" class="producto-titulo-detalle">{{productoFolder}}</div>
        <div id="{{btnProductoCierre}}" class="producto-titulo-cierre ion-close"></div>
    </div>
    <div id="producto-encabezado" class="producto-encabezado">
        <div id="producto-marca-modelo" class="producto-marca-modelo">
            {{productoMarcaModelo}}
            <div id="producto-precio" class="producto-precio">
                {{productoPrecio}}
            </div>
        </div>
        <div id="produto-datos" class="producto-datos">
            {{productoDatosUbicacion}}
        </div>
    </div>
    <div id="producto-informacion" class="producto-informacion">
        <div id="producto-imagen-producto" class="producto-imagen-producto">
            <object data={{productoImagen}}
                    type="image/jpg"
                    class="producto-imagen">
                <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png">
            </object>
        </div> <!--/producto-imagen-->
        <div id="producto-carousel" class="producto-carousel">
            <div class="well">
                <div id="myCarousel" class="carousel slide">

                    <ol class="carousel-indicators">
                        <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                        <li data-target="#myCarousel" data-slide-to="1"></li>
                        <li data-target="#myCarousel" data-slide-to="2"></li>
                    </ol>

                    <!-- Carousel items -->
                    <div class="carousel-inner">
                        <!-- ------------------------------------------------ >
                        <!-- INICIA PAGINA UNO DE CARRUSEL DE MINIATURAS      >
                        <!-- ---------------------------------------------- -->
                        <div class="item active">
                            <div class="img-thumb-fila"> <!-- Fila 2 de Miniaturas -->
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen01-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen01"
                                            type="image/jpg" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen02-min.jpg"
                                            id="thumb-{{productoFolder}}-imagen02"
                                            type="image/jpg" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen03-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen03"
                                            type="image/jpg" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen04-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen04"
                                            type="image/jpg" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen05-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen05"
                                            type="image/jpg" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen06-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen06"
                                            type="image/jpg" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                            </div> <!--/ Fila 1 de Miniaturas -->
                        </div><!--/item-->
                        <!-- ------------------------------------------------ >
                        <!-- INICIA PAGINA UNO DE CARRUSEL DE MINIATURAS      >
                        <!-- ---------------------------------------------- -->

                        <!-- ------------------------------------------------ >
                        <!-- INICIA PAGINA DOS DE CARRUSEL DE MINIATURAS      >
                        <!-- ---------------------------------------------- -->
                        <div class="item">
                            <div class="img-thumb-fila"> <!-- Fila 1 de Miniaturas -->
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen07-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen07"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen08-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen08"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen09-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen09"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen10-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen10"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen11-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen11"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen12-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen12"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                            </div>  <!--/ Fila 2 de Miniaturas -->
                        </div><!--/item-->
                        <!-- ------------------------------------------------ >
                        <!-- INICIA PAGINA DOS DE CARRUSEL DE MINIATURAS      >
                        <!-- ---------------------------------------------- -->

                        <!-- ------------------------------------------------ >
                        <!-- INICIA PAGINA TRES DE CARRUSEL DE MINIATURAS      >
                        <!-- ---------------------------------------------- -->
                        <div class="item">
                            <div class="img-thumb-fila"> <!-- Fila 1 de Miniaturas -->
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen13-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen13"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen14-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen14"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen15-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen15"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait_mini.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen16-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen16"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen17-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen17"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                                <div class="img-thumb">
                                    <object data="/hwt-usados/recursos/imagen/{{productoFolder}}/imagen18-min.jpg"
                                              id="thumb-{{productoFolder}}-imagen18"
                                            type="image/png" style="max-width:100%;">
                                        <img src="/hwt-usados/recursos/imagen/comun/hwt_placeholder_portrait.png"
                                             alt="Image"
                                             style="max-width:100%;"/>
                                    </object>
                                </div>
                            </div>  <!--/ Fila 2 de Miniaturas -->
                        </div><!--/item-->
                        <!-- ------------------------------------------------ >
                        <!-- INICIA PAGINA TRES DE CARRUSEL DE MINIATURAS     >
                        <!-- ---------------------------------------------- -->

                    </div><!--/carousel-inner-->
                    <!--
                    <a class="left carousel-control"  href="#myCarousel" data-slide="Anterior">‹</a>
                    <a class="right carousel-control" href="#myCarousel" data-slide="Siguiente">›</a>
                    -->
                </div><!--/myCarousel-->
            </div><!--/well-->
        </div> <!--/producto-carousel-->
        <div id="producto-especificaciones" class="producto-especificaciones">
            <table>
                <tr>
                    <td colspan="2" class="celda-titulo">ESPECIFICACIONES</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>TIPO</b></td>
                    <td class="celda-valor">{{productoVistaTipo}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>COLOR</b></td>
                    <td class="celda-valor">{{productoVistaColor}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>MOTOR</b></td>
                    <td class="celda-valor">{{productoVistaMotor}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>MODELO MOTOR</b>
                    </td>
                    <td class="celda-valor">{{productoVistaModeloMotor}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>KILOMETRAJE</b></td>
                    <td class="celda-valor">{{productoVistaKilometraje}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>POTENCIA</b></td>
                    <td class="celda-valor">{{productoVistaHP}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>TRANSMISION</b></td>
                    <td class="celda-valor">{{productoVistaTransmision}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>MODELO TRANSMISION</b></td>
                    <td class="celda-valor">{{productoVistaModeloTransmision}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>VELOCIDADES</b></td>
                    <td class="celda-valor">{{productoVistaVelocidades}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>RELACION DIFERENCIAL</b></td>
                    <td class="celda-valor">{{productoVistaRelacionDiferencial}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>EJE DELANTERO</b></td>
                    <td class="celda-valor">{{productoVistaEjeDelantero}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>EJE TRASERO</b></td>
                    <td class="celda-valor">{{productoVistaEjeTrasero}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>DISTANCIA EJES</b></td>
                    <td class="celda-valor">{{productoVistaDistanciaEjes}}</td>
                </tr>
                <tr>
                    <td class="celda-propiedad"><b>CABINA</b></td>
                    <td class="celda-valor">{{productoVistaCabina}}</td>
                </tr>
            </table>
        </div> <!--/producto-especificaciones-->
    </div> <!--/producto-informacion-->

    <div id="producto-titulo-ventana-Inferior" class="producto-titulo-ventana hidden-desktop">
        <div id="producto-titulo-detalle-Inferior" class="producto-titulo-detalle">{{productoVIN}}</div>
        <div id="{{btnProductoCierreInferior}}"    class="producto-titulo-cierre ion-close"></div>
    </div>
</script>

<script>
    jQuery.noConflict();
    jQuery(document).ready(function ($) {

        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        var precioInicialUnidad = 0;
        var precioFinalUnidad   = 0;
        var annInicialUnidad    = 0;
        var annFinalUnidad      = 0;

        console.log('Jquery ready');

        ///////////////////////////////////////
        // F   U   N   C   I   O   N   E   S //
        ///////////////////////////////////////
        function mensajeUsuario(pMensaje) {
            $.colorbox({
                html  : pMensaje,
                width : "640px",
                close : "<b>Cerrar</b>"
            });
        }


        function presentaImagen(pImagen) {
            $.colorbox({
                href:$(pImagen).attr("src")});
        }

        function existeArchivoServer(image_url) {
            var http = new XMLHttpRequest();
            http.open('HEAD', image_url, false);
            http.send();
            return http.status != 404;
        }

        function buscarUnidades(OnLoadscrollTo, XPosition, YPosition){
            // ECRC: Ocultando la Vista del Producto
            $("#mod_producto_vista").hide(500);
            $("#btn-nueva-busqueda").show(500);
            $('#mod_usados_filtro_resultado').show();
            $('#mod_usados_filtro_resultado').empty();

            console.log('buscarUnidades');

            var consulta      = "listaProductos";
            var tipoUnidad    = $('#seleccionTipoUnidad').find('option:selected').val();
            var marca         = $('#seleccionMarca').find('option:selected').val();
            var modelo        = $('#seleccionModelo').find('option:selected').val();
            var annInicial    = $('#seleccionAnnInicial').find('option:selected').val();
            var annFinal      = $('#seleccionAnnFinal').find('option:selected').val();
            var precioInicial = $('#seleccionPrecioInicial').find('option:selected').val();
            var precioFinal   = $('#seleccionPrecioFinal').find('option:selected').val();
            var ubicacion     = $('#seleccionUbicacion').find('option:selected').val();

            var objDatosConsulta = {
                hwtConsulta      : consulta,
                hwtTipoUnidad    : tipoUnidad,
                hwtMarca         : marca,
                hwtModelo        : modelo,
                hwtAnnInicial    : annInicial,
                hwtAnnFinal      : annFinal,
                hwtPrecioInicial : precioInicial,
                hwtPrecioFinal   : precioFinal,
                hwtUbicacion     : ubicacion
            };

            $.ajax({
                type       : "GET",
                contentType: "application/json; charset=utf-8",
                url        : "/hwt-usados/modulos/mod_usados_filtro_query.php",
                data       : objDatosConsulta,
                success: function (resultadoPhp) {
                    jsonDatos = JSON.parse(resultadoPhp);

                    for (var iCiclo = 0; iCiclo < jsonDatos.length; iCiclo++) {

                        var datoUnidadTitulo   = '';
                        var datoRutaImagen     = '';
                        var datoAnnFabricacion = '';
                        var datoKilometraje    = '';
                        var datoUbicacion      = '';
                        var datoUnidadPrecio   = '';
                        var datoVistaProducto  = '';

                        var hwt_unidad = jsonDatos[iCiclo];
                        console.warn('hwt_unidad');
                        console.warn(hwt_unidad.codigo);

                        var tituloProducto = '<b>' + hwt_unidad.marca + ' ' + hwt_unidad.ann_unidad + '</b>'
                            + '</br>' + hwt_unidad.modelo + ' (' + hwt_unidad.codigo + ')';

                        var precioUnidad = parseFloat(hwt_unidad.precio_con_iva).toFixed(2).replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,");

                        datoUnidadTitulo   = tituloProducto;
                        datoAnnFabricacion = hwt_unidad.ann_unidad;
                        datoKilometraje    = hwt_unidad.kilometraje;
                        datoUbicacion      = hwt_unidad.ubicacion;
                        datoUnidadPrecio   = '$ ' + precioUnidad;
                        datoVistaProducto  = 'btn-vista-' + hwt_unidad.vin;

                        var folderUnidad = hwt_unidad.modelo + '_' + hwt_unidad.codigo;
                        var timestamp = Math.floor(Date.now() / 1000);
                        var imagenUnidad = "/hwt-usados/recursos/imagen/" + folderUnidad + "/imagen_thumb.jpg";
                        imagenUnidad = imagenUnidad + '?dc=' + timestamp;
                        datoRutaImagen = imagenUnidad;

                        var objHwtUnidad = {
                            unidadTitulo     : datoUnidadTitulo,
                            rutaImagen       : datoRutaImagen,
                            annFabricacion   : datoAnnFabricacion,
                            kilometraje      : datoKilometraje,
                            ubicacion        : datoUbicacion,
                            unidadPrecio     : datoUnidadPrecio,
                            btnVistaProducto : datoVistaProducto
                        }

                        var jsonStringHwtUnidad = JSON.stringify(hwt_unidad);
                        sessionStorage.setItem(hwt_unidad.vin, jsonStringHwtUnidad);

                        var source = $("#plantilla-producto-thumb").html();
                        var template = Handlebars.compile(source);
                        var html = template(objHwtUnidad);
                        $('#mod_usados_filtro_resultado').append(html);

                        var objBotonVistaProducto = hwt_unidad.vin;

                        // ECRC: Agregando un Listener Clic al Botón creado
                        document.getElementById(datoVistaProducto).addEventListener('click', function (objBotonVistaProducto) {

                            console.log('objBotonVistaProducto');
                            console.log(objBotonVistaProducto);
                            console.log(objBotonVistaProducto.target.id);

                            var vinUnidad = objBotonVistaProducto.target.id;
                            vinUnidad = vinUnidad.replace("btn-vista-", "");


                            var jsonStringUnidad = sessionStorage.getItem(vinUnidad);
                            var objHwtUnidad = JSON.parse(jsonStringUnidad);

                            // ECRC: Generando el Boton para el Cierre
                            var btnProductoCierreDefault = "producto-titulo-cierre-" + objBotonVistaProducto;
                            var btnProductoCierreDefaultInferior = "producto-titulo-cierre-Inferior" + objBotonVistaProducto;

                            $('#mod_usados_filtro_resultado').hide(500);
                            $('#mod_producto_vista').empty();

                            var gaUnidadUsada = '/unidad-usada/'
                                + objHwtUnidad.marca
                                + '-' + objHwtUnidad.ann_unidad
                                + '-' + objHwtUnidad.modelo
                                + '-' + objHwtUnidad.codigo;
                            console.log('Google Analytics: ' + gaUnidadUsada);
                            ga('create', 'UA-90533207-1', gaUnidadUsada);
                            ga('set', 'page', gaUnidadUsada);
                            ga('send', 'pageview');

                            var folderUnidad = objHwtUnidad.modelo + '_' + objHwtUnidad.codigo;

                            // ECRC: Cargando los datos que se presentarán en la Vista del Producto
                            var objHwtVistaProducto = {
                                productoImagen                   : '/hwt-usados/recursos/imagen/' + folderUnidad + '/imagen_thumb.jpg',
                                productoVistaTipo                : objHwtUnidad.tipo_unidad,
                                productoVIN                      : objHwtUnidad.vin,
                                productoFolder                   : objHwtUnidad.modelo + "_" + objHwtUnidad.codigo,
                                productoDatosUbicacion           : objHwtUnidad.ubicacion,
                                productoMarcaModelo              : objHwtUnidad.marca + ' ' + objHwtUnidad.ann_unidad + ' - ' + objHwtUnidad.modelo,
                                productoPrecio                   : '$ ' + parseFloat(objHwtUnidad.precio_con_iva).toFixed(2).replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,"),
                                productoVistaColor               : objHwtUnidad.color,
                                productoVistaMotor               : objHwtUnidad.motor,
                                productoVistaModeloMotor         : objHwtUnidad.modelo_motor,
                                productoVistaHP                  : objHwtUnidad.potencia_motor + ' hp' ,
                                productoVistaTransmision         : objHwtUnidad.marca_transmision,
                                productoVistaModeloTransmision   : objHwtUnidad.modelo_transmision,
                                productoVistaVelocidades         : objHwtUnidad.velocidades,
                                productoVistaRelacionDiferencial : objHwtUnidad.relacion_dif,
                                productoVistaEjeDelantero        : objHwtUnidad.eje_delantero_capacidad,
                                productoVistaEjeTrasero          : objHwtUnidad.eje_trasero_capacidad,
                                productoVistaKilometraje         : parseFloat(objHwtUnidad.kilometraje).toFixed(0).replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,") + ' km',
                                productoVistaDistanciaEjes       : objHwtUnidad.distancia_ejes,
                                productoVistaCabina              : objHwtUnidad.tipo_cabina,
                                btnProductoCierre                : btnProductoCierreDefault,
                                btnProductoCierreInferior        : btnProductoCierreDefaultInferior
                            }

                            var source = $("#plantilla-vista-producto").html();
                            var template = Handlebars.compile(source);
                            var html = template(objHwtVistaProducto);
                            $('#mod_producto_vista').append(html);
                            $('#mod_producto_vista').show(500);

                            $('#myCarousel').carousel({
                                interval: 7000
                            })

                            $(window).scrollTo(document.getElementById("mod_fin_filtro"));

                            document.getElementById(btnProductoCierreDefault).addEventListener('click', function () {
                                $('#mod_producto_vista').empty();
                                $('#mod_producto_vista').hide();
                                $('#mod_usados_filtro_resultado').show();
                            }, false);

                            document.getElementById(btnProductoCierreDefaultInferior).addEventListener('click', function () {
                                $('#mod_producto_vista').empty();
                                $('#mod_producto_vista').hide();
                                $('#mod_usados_filtro_resultado').show();
                            }, false);

                            // ECRC: Generando el Evento para los Botones
                            for(var iCiclo = 1; iCiclo <= 18; iCiclo++){
                                var numeroImagen = '';
                                if(iCiclo < 10){
                                    numeroImagen = '0' + iCiclo.toString();
                                }
                                else{
                                    numeroImagen = iCiclo.toString();
                                }

                                var btnThumbImagen = "thumb-" + objHwtUnidad.modelo + "_" + objHwtUnidad.codigo + "-imagen" + numeroImagen;
                                var objBotonThumbProducto = btnThumbImagen;
                                document.getElementById(btnThumbImagen).addEventListener('click', function (objBotonThumbProducto) {
                                    var vinUnidad = objBotonThumbProducto.target.id;
                                    var aObjetoImagen = vinUnidad.split('-');

                                    $('#producto-imagen-producto').empty();

                                    var objHwtVistaProducto = {
                                        productoImagen: '/hwt-usados/recursos/imagen/' + folderUnidad + '/' + aObjetoImagen[2] + '-min.jpg',
                                    }

                                    var source = $("#plantilla-imagen-vista-producto").html();
                                    var template = Handlebars.compile(source);
                                    var html = template(objHwtVistaProducto);
                                    $('#producto-imagen-producto').append(html);
                                    $('#producto-imagen-producto').show(500);

                                    $('.producto-imagen-producto object').each(function(){
                                        var anchor = $('<a/>').attr(
                                            {'href': this.data}).colorbox(
                                            {
                                                maxHeight: "700px",
                                                transition : "elastic",
                                                speed : 250,
                                                close : "Cerrar",
                                                title: "Highway Trucks - Used Units"

                                            });

                                        anchor.class = "group1";
                                        $(this).wrap(anchor); });

                                }, false);
                            } // for thumb imagenes
                        }, false);
                    }

                    if(OnLoadscrollTo !== undefined){
                        $(window).scrollTo(document.getElementById(OnLoadscrollTo));
                    }

                    if(XPosition !== undefined){
                        console.log('Por posicion' + YPosition);
                        //$(window).scrollTo(XPosition, YPosition);

                        $("html, body").animate({ scrollTop: YPosition }, "slow");
                    }
                } //success
            });
        } //buscarUnidades


        ///////////////////////////////////////
        //       E  V  E  N  T  O  S         //
        ///////////////////////////////////////
        $('#seleccionTipoUnidad').change(function(){

            var tipoUnidad = this.value;
            var datosJsonSesion = sessionStorage.getItem("cargaSelectores");
            jsonDatos = JSON.parse(datosJsonSesion);



            // ECRC: Poblando el Selector de Marca
            var seleccionMarca = $('#seleccionMarca');
            var aMarca = new Array();
            for(var iUnidad = 0; iUnidad < jsonDatos.length; iUnidad++){
                if(jsonDatos[iUnidad].tipo_unidad !== tipoUnidad){
                    continue;
                }

                var datoMarca = jsonDatos[iUnidad].marca;
                var index = $.inArray(datoMarca, aMarca);
                if(index === -1){
                    aMarca.push(datoMarca);
                    seleccionMarca.append($("<option />",{
                        value: datoMarca,
                        text: datoMarca
                    }));
                }
            }
            seleccionMarca.val('TODO');

            var seleccionModelo = $('#seleccionModelo');
            seleccionModelo
                .empty()
                .append('<option selected="selected" value="TODO">TODOS LOS MODELOS</option>');
            seleccionModelo.val('TODO');

            $('#seleccionPrecioInicial').val(precioInicialUnidad);
            $('#seleccionPrecioFinal').val(precioFinalUnidad);
            $('#seleccionAnnInicial').val(annInicialUnidad);
            $('#seleccionAnnFinal').val(annFinalUnidad);

            // ECRC: Poblando el Selector de Ubicaciones
            var seleccionUbicacion = $('#seleccionUbicacion');
            var aUbicacion = new Array();
            for(var iUnidad = 0; iUnidad < jsonDatos.length; iUnidad++){
                if(jsonDatos[iUnidad].tipo_unidad !== tipoUnidad){
                    continue;
                }

                var datoUbicacion = jsonDatos[iUnidad].ubicacion;
                var index = $.inArray(datoUbicacion, aUbicacion);
                if(index === -1){
                    aUbicacion.push(datoUbicacion);
                    seleccionUbicacion.append($("<option />",{
                        value : datoUbicacion,
                        text  : datoUbicacion
                    }));
                }
            }

            seleccionUbicacion.val('TODO');
        });

        $('#seleccionMarca').change(function(){

            var tipoUnidad = $('#seleccionTipoUnidad option:selected').text();
            var Marca = this.value;

            var datosJsonSesion = sessionStorage.getItem("cargaSelectores");
            jsonDatos = JSON.parse(datosJsonSesion);

            // ECRC: Limpiando el Selector de Modelo
            $('#seleccionModelo')
                .empty()
                .append('<option selected="selected" value="TODO">SELECCIONE UN MODELO</option>');

            // ECRC: Poblando el Selector de Modelo
            var seleccionModelo = $('#seleccionModelo');
            var aModelo = new Array();

            for(var iUnidad = 0; iUnidad < jsonDatos.length; iUnidad++){
                if(jsonDatos[iUnidad].tipo_unidad !== tipoUnidad){
                    continue;
                }

                if(Marca !== "TODO"){
                    if(jsonDatos[iUnidad].marca !== Marca){
                        continue;
                    }
                }

                var datoModelo = jsonDatos[iUnidad].modelo;
                var index = $.inArray(datoModelo, aModelo);
                if(index === -1){
                    aModelo.push(datoModelo);
                    seleccionModelo.append($("<option />",{
                        value: datoModelo,
                        text: datoModelo
                    }));
                }
            }

            seleccionModelo.append($("<option />",{
                value : 'TODO',
                text  : 'TODOS LOS MODELOS'
            }));
        });

        $('#btn-lightbox').click(function () {
            $.colorbox({
                html: "funcion",
                width: "640px",
                close: "<b>Cerrar</b>"
            });
        });

        $('#btn-unidad_busca_productos').click(function () {
            buscarUnidades('mod_usados_filtro_resultado');
        });

        function cargaSelectores(){
            var consulta      = "cargaSelectores";

            var objDatosConsulta = {
                hwtConsulta      : consulta
            };

            $.ajax({
                type: "GET",
                contentType: "application/json; charset=utf-8",
                url: "/hwt-usados/modulos/mod_usados_filtro_query.php",
                data: objDatosConsulta,
                success: function (resultadoPhp) {

                    sessionStorage.setItem(consulta,resultadoPhp);
                    var datosJsonSesion = sessionStorage.getItem(consulta);
                    jsonDatos = JSON.parse(datosJsonSesion);

                    console.warn('Regreso del Datos Selectores:')

                    var annInicial    = 0;
                    var annFinal      = 0;
                    var precioInicial = 0;
                    var precioFinal   = 0;


                    $('#seleccionPrecioInicial').val(precioInicialUnidad);

                    // ECRC: Poblando el Selector de Tipo de Unidad
                    var seleccionTipoUnidad = $('#seleccionTipoUnidad');
                    var aTipoUnidad = new Array();
                    for(var iUnidad = 0; iUnidad < jsonDatos.length; iUnidad++){

                        // Cargando el Selector de Tipo de Unidad
                        var datoTipoUnidad = jsonDatos[iUnidad].tipo_unidad;
                        var index = $.inArray(datoTipoUnidad, aTipoUnidad);
                        if(index === -1){
                            aTipoUnidad.push(datoTipoUnidad);
                            seleccionTipoUnidad.append($("<option />",{
                                value : datoTipoUnidad,
                                text  : datoTipoUnidad
                            }));
                        }
                    }

                    seleccionTipoUnidad.append($("<option />",{
                        value : "TODO",
                        text  : "TODOS LOS TIPOS DE UNIDAD"
                    }));
                    seleccionTipoUnidad.val('TODO');

                    $('#seleccionMarca')
                        .empty()
                        .append('<option selected="selected" value="TODO">TODAS LAS MARCAS</option>');
                    $('#seleccionMarca').val('TODO');

                    $('#seleccionModelo')
                        .empty()
                        .append('<option selected="selected" value="TODO">TODOS LOS MODELOS</option>');
                    $('#seleccionModelo').val('TODO');
                }
            });

            // Determinando el Año Inicial y Final
            var fechaActual = new Date();
            var annActual = fechaActual.getFullYear();

            $('#seleccionAnnInicial')
                .empty()
                .append('<option selected="selected" value="0">AÑO INICIAL</option>');

            $('#seleccionAnnFinal')
                .empty()
                .append('<option selected="selected" value="9999">AÑO FINAL</option>');

            annInicialUnidad    = 2000;
            annFinalUnidad      = annActual;

            for(var iAnnProceso = annInicialUnidad; iAnnProceso <=  annActual; iAnnProceso++){
                $('#seleccionAnnInicial').append($("<option />",{
                    value : iAnnProceso,
                    text  : iAnnProceso
                }));

                $('#seleccionAnnFinal').append($("<option />",{
                    value : iAnnProceso,
                    text  : iAnnProceso
                }));
            }

            // Determinando el Precio Inicial y FInal
            $('#seleccionPrecioInicial')
                .empty()
                .append('<option selected="selected" value="0">PRECIO INICIAL</option>');

            $('#seleccionPrecioFinal')
                .empty()
                .append('<option selected="selected" value="9999999">PRECIO FINAL</option>');

            precioInicialUnidad = 200000;
            precioFinalUnidad   = 1800000;
            for(var iPrecioProceso = precioInicialUnidad; iPrecioProceso <= precioFinalUnidad; iPrecioProceso += 200000){
                var valorPrecioInicial = parseFloat(iPrecioProceso).toFixed(2).replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,");
                $('#seleccionPrecioInicial').append($("<option />",{
                    value: iPrecioProceso,
                    text: '$ ' + valorPrecioInicial
                }));

                var valorPrecioFinal = parseFloat(iPrecioProceso).toFixed(2).replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,");
                $('#seleccionPrecioFinal').append($("<option />",{
                    value: iPrecioProceso,
                    text: '$ ' + valorPrecioFinal
                }));
            }

            $('#seleccionPrecioInicial').val(precioInicialUnidad);
            $('#seleccionPrecioFinal').val(precioFinalUnidad);
            $('#seleccionAnnInicial').val(annInicialUnidad);
            $('#seleccionAnnFinal').val(annFinalUnidad);

            $('#seleccionUbicacion').append($("<option />",{
                value: 'TODO',
                text: 'TODAS LAS UBICACIONES'
            }));

            $('#seleccionUbicacion').val('TODO');

            console.log('Va a dar el clic');

            setTimeout(function(){
                buscarUnidades('mod_usados_filtro',0,250);
            }, 1700);

        } // Carga Selectores
        ////////////////////////////////////////////////////////////////
        //  B L O Q U E   P R I N C I P A L   D E   E J E C U C I O N //
        ////////////////////////////////////////////////////////////////
        cargaSelectores();
    });
</script>


