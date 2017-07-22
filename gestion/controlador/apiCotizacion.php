<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Dataworker.php');
require_once('../recurso/clase/Logger.php');
require_once('../recurso/clase/Mnemea.php');
require_once('../recurso/clase/Reporter.php');
require_once('../recurso/clase/Mailer.php');
require_once('apiConfigurador.php');

function formatoCotizacion($pObjImpresion){
    Logger::enable(true,'formatoCotizacion');
    Dataworker::openConnection();

    if(isset($pObjImpresion)){
        $numCotizacion = $pObjImpresion->num_cotizacion;
    }
    else{
        $numCotizacion = Receiver::getApiParameter('numCotizacion');
    }

    $archivoGenerado = Reporter::openFile('reporteCotizacion' . $numCotizacion);
    $nombreReporte = "Cotización de Unidades No. " . $numCotizacion;
    Reporter::setMaxColumn('D');
    Reporter::prepareHeader(utf8_encode($nombreReporte));

    //ECRC: Preparando el Titulo de las Columnas
    $arrayTituloColumnas = array(
        "16:",
        "40:",
        "60:",
        "15:",
    );

    Reporter::prepareTitleColumns($arrayTituloColumnas);

    Reporter::decreaseCurrentRow();

    // ECRC: Localizando la Cotizacion
    $objCondicion = new \stdClass();
    $objCondicion->codigo_empresa        = Dataworker::equalToString('HWT');
    $objCondicion->num_cotizacion        = Dataworker::equalToValue($numCotizacion);
    $hwtCotizacion = Dataworker::findFirst('hwt_cotizacion',$objCondicion);

    // ECRC: Localizando al Cliente
    $objCondicion = new \stdClass();
    $objCondicion->codigo_cliente        = Dataworker::equalToValue($hwtCotizacion->codigo_cliente);
    $hwtCliente = Dataworker::findFirst('hwt_cliente',$objCondicion);

    $fgColor = '000000';
    $bgColor = 'FFFFFF';
    Reporter::printVerticalSeparator('',$fgColor,$bgColor);

    $arraySaludo = array(
        'Atención a ' . ucwords(strtolower($hwtCliente->contacto_nombre)),
        'EMPRESA ' . strtoupper($hwtCliente->razon_social),
        '',
        'Enviamos la Cotización de Unidades No. ' . $numCotizacion . ' esperando que sea de su Conformidad.',
        'A continuación el detalle de las Unidades:');

    $numLinea = 1;
    foreach($arraySaludo as $linea){

        if($numLinea <= 2){
            $boldFont = true;
        }
        else{
            $boldFont = false;
        }

        Reporter::printVerticalSeparator(utf8_encode($linea),$fgColor,$bgColor,$boldFont);

        $numLinea = $numLinea + 1;
    }

    Reporter::increaseCurrentRow();

    $bgColor = '054F7D';
    Reporter::printVerticalSeparator('UNIDADES','FFFFFF',$bgColor);

    // ECRC: Presentando las Unidades de la Cotización
    $objCondicion = new \stdClass();
    $objCondicion->codigo_empresa        = Dataworker::equalToString  ('HWT');
    $objCondicion->num_cotizacion        = Dataworker::equalToValue  ($numCotizacion);
    $hwtCotizacionUnidad = Dataworker::getRecords('hwt_cotizacion_unidad',$objCondicion);

    // ECRC: Configuración para las Celdas de Título
    $objConfig = new \stdClass();
    $objConfig->bgColor = '5083a4';
    $objConfig->fgColor = 'FFFFFF';
    $objConfig->align   = 'right';
    $objConfig->bold    = 'true';

    $objConfigBlanco = new \stdClass();
    $objConfigBlanco->align = 'right';
    $objConfigBlanco->bgColor = 'ffffff';
    $objConfigBlanco->fgColor = '000000';

    $objConfigValor = new \stdClass();
    $objConfigValor->align = 'left';
    $objConfigValor->bgColor = 'ffffff';
    $objConfigValor->fgColor = '000000';

    function imprimeDatoUnidad($pTitulo,$pValor, $pObjConfig,$pObjConfigValor){
        Reporter::writeCell('A' . Reporter::getCurrentRow(),utf8_encode($pTitulo),$pObjConfig);
        if($pObjConfigValor !== null){
            Reporter::writeCell('B' . Reporter::getCurrentRow(),utf8_encode($pValor),$pObjConfigValor);
        }
        else{
            Reporter::writeCell('B' . Reporter::getCurrentRow(),utf8_encode($pValor));
        }


        Reporter::increaseCurrentRow();

    } // imprimeDatoUnidad

    $unidadesPagina = 3;
    $totalPaginas = ceil($hwtCotizacionUnidad->numRecords / $unidadesPagina);

    $unidadConteo = 0;
    $unidadTotalConteo = 0;
    $numPagina = 1;
    if($hwtCotizacionUnidad->numRecords > 0) {
        // ECRC: Cargando la Información de la Unidad en el Registro de Unidad de Cotización
        foreach($hwtCotizacionUnidad->data as $recordUnidadCotizacion){

            $objCondicion = new \stdClass();
            $objCondicion->codigo        = Dataworker::equalToString($recordUnidadCotizacion->codigo);
            $hwtVehiculo = Dataworker::findFirst('hwt_vehiculo',$objCondicion);

            $unidadCotizacion = new \stdClass();
            $unidadCotizacion->codigo         = $hwtVehiculo->codigo;
            $unidadCotizacion->vin            = $hwtVehiculo->vin;
            $unidadCotizacion->modelo         = $hwtVehiculo->modelo;
            $unidadCotizacion->marca          = $hwtVehiculo->marca;
            $unidadCotizacion->ann_unidad     = $hwtVehiculo->ann_unidad;
            $unidadCotizacion->precio_sin_iva = '$ ' . number_format($hwtVehiculo->precio_sin_iva,2);

            $celdaInicio = Reporter::getCurrentRow();

            imprimeDatoUnidad('TIPO'          ,$hwtVehiculo->tipo_unidad         ,$objConfig);
            imprimeDatoUnidad('MARCA'         ,$hwtVehiculo->marca               ,$objConfig);
            imprimeDatoUnidad('AÑO'           ,$hwtVehiculo->ann_unidad          ,$objConfig,$objConfigValor);
            imprimeDatoUnidad('SERIE'         ,$hwtVehiculo->vin                 ,$objConfig);
            imprimeDatoUnidad('TRANSMISION'   ,$hwtVehiculo->marca_transmision   ,$objConfig);
            imprimeDatoUnidad('EJE DELANTERO' ,$hwtVehiculo->eje_delantero_marca ,$objConfig);
            imprimeDatoUnidad('EJE TRASERO'   ,$hwtVehiculo->eje_trasero_marca   ,$objConfig);
            imprimeDatoUnidad('SUSPENSIÓN'    ,$hwtVehiculo->tipo_transmision    ,$objConfig);
            imprimeDatoUnidad('QUINTA RUEDA'  ,$hwtVehiculo->quinta_rueda        ,$objConfig);

            $llantasInstaladas = 'Delanteras ' . $hwtVehiculo->llantas_delanteras_medidas . ', '
                               . 'Traseras ' . $hwtVehiculo->llantas_traseras_medidas;

            Logger::write('llantasInstaladas');
            Logger::write($llantasInstaladas);
            imprimeDatoUnidad('LLANTAS'   ,$llantasInstaladas, $objConfig);

            $rinesInstalados = 'Delanteros ' . $hwtVehiculo->rines_delanteros . ', '
                             . 'Traseros ' . $hwtVehiculo->rines_traseros;
            imprimeDatoUnidad('RINES'   ,$rinesInstalados, $objConfig);


            Reporter::writeCell('D' . $celdaInicio,utf8_encode('PRECIO'),$objConfig);
            Reporter::writeCell('D' . ($celdaInicio + 1),'$ ' . number_format($hwtVehiculo->precio_sin_iva,2),$objConfigBlanco);

            // ECRC: Presentando la Miniatura de la Imagen
            //D:\wamp64\www\hwtusados\hwt-usados\gestion\controlador
            ///hwt-usados/recursos/imagen/{{productoFolder}}/imagen01-min.jpg

            $carpetaUnidad = 'recursos/imagen/' . $hwtVehiculo->modelo . '_' . $hwtVehiculo->codigo;
            //$miniaturaUnidad = str_replace('gestion\controlador','recursos/imagen/',dirname(__FILE__));
            $miniaturaUnidad = $carpetaUnidad.'/imagen_thumb.jpg';  //Path to signature .jpg file

            /*
            Logger::write('miniaturaUnidad.................');
            Logger::write($miniaturaUnidad);
            Logger::write(file_exists($miniaturaUnidad));
            */

            $objImagen = new \stdClass();
            $objImagen->url = $miniaturaUnidad;
            $objImagen->offsetX = 90;                            //setOffsetX works properly
            $objImagen->offsetY = 5;                            //setOffsetY works properly
            $objImagen->cell = 'C' . $celdaInicio;
            $objImagen->width = 220;
            $objImagen->height = 220;

            Reporter::drawImage($objImagen);

            Reporter::increaseCurrentRow();
            $unidadConteo = $unidadConteo + 1;
            $unidadTotalConteo = $unidadTotalConteo + 1;

            Logger::write('unidadConteo');
            Logger::write($unidadConteo);

            Logger::write('hwtCotizacionUnidad->numRecords');
            Logger::write($hwtCotizacionUnidad->numRecords);

            if(($unidadConteo === $unidadesPagina
            or $unidadTotalConteo === $hwtCotizacionUnidad->numRecords)
            and $totalPaginas > 1){
                Logger::write('Imprime Banda de LInea');
                Reporter::increaseCurrentRow(7);

                $bgColor = '054F7D';
                $Pagina  = 'Página ' . $numPagina . ' de ' . $totalPaginas;
                Reporter::printVerticalSeparator(utf8_encode($Pagina),'FFFFFF',$bgColor);

                $unidadConteo = 1;
                $numPagina = $numPagina + 1;
            }
        }
    }

    // ECRC: Presentando el Pie de la Cotizacion
    $valSubtotal  = '$ ' . number_format($hwtCotizacion->valor_subtotal,2);
    $valImpuestos = '$ ' . number_format($hwtCotizacion->valor_impuesto,2);
    $valTotal     = '$ ' . number_format($hwtCotizacion->valor_total   ,2);

    Reporter::writeCell('C' . Reporter::getCurrentRow(),utf8_encode('SUBTOTAL'),$objConfig);
    Reporter::writeCell('D' . Reporter::getCurrentRow(),$valSubtotal,$objConfigBlanco);
    Reporter::increaseCurrentRow();

    Reporter::writeCell('C' . Reporter::getCurrentRow(),utf8_encode('IMPUESTOS'),$objConfig);
    Reporter::writeCell('D' . Reporter::getCurrentRow(),$valImpuestos,$objConfigBlanco);
    Reporter::increaseCurrentRow();

    Reporter::writeCell('C' . Reporter::getCurrentRow(),utf8_encode('TOTAL'),$objConfig);
    Reporter::writeCell('D' . Reporter::getCurrentRow(),$valTotal,$objConfigBlanco);
    Reporter::increaseCurrentRow();

    // ECRC: Datos Generales de la Cotizacion
    $arrayDatosGenerales = array(
        'C O N D I C I O N E S   G E N E R A L E S',
        '- Las Unidades se entregan en Condiciones Comerciales, con el uso normal y desgaste del año y modelo',
        '- Cotización válida por 15 días, sujeta a cambios y/o modificaciones sin previo aviso.',
        '- Los Precios indicados en éste documento son de Contado.'
        );

    $numLinea = 1;
    Reporter::increaseCurrentRow();
    foreach($arrayDatosGenerales as $linea){

        if($numLinea <= 1){
            $boldFont = true;
            $bgColor = '054F7D';
            $fgColor = 'ffffff';
        }
        else{
            $boldFont = false;
            $bgColor = 'e6edf2';
            $fgColor = '000000';
        }

        Reporter::printVerticalSeparator(utf8_encode($linea),$fgColor,$bgColor,$boldFont);
        $numLinea = $numLinea + 1;
    }

    Reporter::increaseCurrentRow();
    // ECRC: Localizando a la Empresa
    $objCondicion = new \stdClass();
    $objCondicion->codigo_empresa = Dataworker::equalToString('HWT');
    $hwtEmpresa = Dataworker::findFirst('hwt_empresa',$objCondicion);

    // ECRC: Presentando los Datos Bancarios
    $arrayBancoMoneda = explode(',',$hwtEmpresa->banco_moneda);

    $numBancos = count($arrayBancoMoneda);

    for ($iCiclo = 0; $iCiclo < $numBancos; $iCiclo++) {
        $arrayBancoInstitucion = explode(',',$hwtEmpresa->banco_institucion);
        $arrayBancoRazonSocial = explode(',',$hwtEmpresa->banco_razon_social);
        $arrayBancoCuenta      = explode(',',$hwtEmpresa->banco_cuenta);
        $arrayBancoCuentaClabe = explode(',',$hwtEmpresa->banco_cuenta_clabe);
        $arrayBancoCuentaSwift = explode(',',$hwtEmpresa->banco_cuenta_swift);

        $boldFont = true;
        $bgColor  = 'ffffff';
        $fgColor  = '054F7D';
        $linea = 'CUENTA DE BANCO ' . strtoupper($arrayBancoInstitucion[$iCiclo]) . ' EN ' . $arrayBancoMoneda[$iCiclo];
        Reporter::printVerticalSeparator(utf8_encode($linea),$fgColor,$bgColor,$boldFont);

        Reporter::writeCell('A' . Reporter::getCurrentRow(),utf8_encode('RAZON SOCIAL'),$objConfig);
        Reporter::writeCell('B' . Reporter::getCurrentRow(),$arrayBancoRazonSocial[$iCiclo]);
        Reporter::increaseCurrentRow();

        $objConfig->align = 'left';
        Reporter::writeCell('A' . Reporter::getCurrentRow(),utf8_encode('CUENTA BANCO'),$objConfig,$objConfig);
        Reporter::writeCell('B' . Reporter::getCurrentRow(),' ' . $arrayBancoCuenta[$iCiclo]);
        Reporter::increaseCurrentRow();

        Reporter::writeCell('A' . Reporter::getCurrentRow(),utf8_encode('CUENTA CLABE'),$objConfig,$objConfig);
        Reporter::writeCell('B' . Reporter::getCurrentRow(),' ' . $arrayBancoCuentaClabe[$iCiclo]);
        Reporter::increaseCurrentRow();

        Reporter::writeCell('A' . Reporter::getCurrentRow(),utf8_encode('CUENTA SWIFT'),$objConfig,$objConfig);
        Reporter::writeCell('B' . Reporter::getCurrentRow(),' ' . $arrayBancoCuentaSwift[$iCiclo]);
        Reporter::increaseCurrentRow(2);

    } //iCiclo

    $linea = 'A N O T A C I O N E S';
    Reporter::printVerticalSeparator(utf8_encode($linea),$fgColor,$bgColor,$boldFont);
    Reporter::writeCell('A' . Reporter::getCurrentRow(),$hwtEmpresa->banco_anotaciones);
    Reporter::increaseCurrentRow();

    // ECRC: Agregando al Usuario que generó la Cotización
    $objCondicion = new \stdClass();
    $objCondicion->usuario        = Dataworker::equalToString($hwtCotizacion->usuario);
    $hwtUsuario = Dataworker::findFirst('hwt_usuario',$objCondicion);

    // ECRC: Cierre de Cotizacion
    $arrayCierreCotizacion = array(
        'Quedamos a disposición para cualquier duda o aclaración.',
        'Esperamos contar con la Aprobación de ésta Cotización y poder servirle pronto.',
        '',
        'A T E N T A M E N T E',
        ucwords(strtolower($hwtUsuario->nombre)),
        'Correo '. $hwtUsuario->email ,
        'Teléfono '. $hwtUsuario->telefono,
        'Móvil '. $hwtUsuario->movil
    );
    ;

    $numLinea = 1;
    Reporter::increaseCurrentRow();
    foreach($arrayCierreCotizacion as $linea){

        if($numLinea === 4){
            $boldFont = true;
            $bgColor = 'ffffff';
            $fgColor = '000000';
        }
        else{
            $boldFont = false;
            $bgColor = 'ffffff';
            $fgColor = '000000';
        }

        Reporter::printVerticalSeparator(utf8_encode($linea),$fgColor,$bgColor,$boldFont);
        $numLinea = $numLinea + 1;
    }

    $archivoServidor = Reporter::saveFile();

    $objArchivoGenerado = new \stdClass();
    $objArchivoGenerado->nombre          = $archivoGenerado;
    $objArchivoGenerado->archivoServidor = $archivoServidor;

    Emissary::prepareEnvelope();

    $availableInfo = true;
    $apiMessage = 'Archivo generado: ' . $archivoGenerado;
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('archivoGenerado' , $objArchivoGenerado);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();

    if(isset($pObjImpresion)){
        return $objArchivoGenerado;
    }
    else{
        echo json_encode($objReturn);
    }
}

function enviarCotizacion(){
    Logger::enable(true,'enviarCotizacion');
    $numCotizacion = Receiver::getApiParameter('tfNoCotizacion');

    Mailer::prepareConection();
    $Conexion   = Dataworker::openConnection();

    $objMensaje = new \stdClass();
    $objMensaje->subject = 'Cotizacion de Unidades No. ' . $numCotizacion;

    // ECRC: Localizando la Cotizacion
    $objCondicion = new \stdClass();
    $objCondicion->codigo_empresa        = Dataworker::equalToString('HWT');
    $objCondicion->num_cotizacion        = Dataworker::equalToValue($numCotizacion);
    $hwtCotizacion = Dataworker::findFirst('hwt_cotizacion',$objCondicion);

    // ECRC: Localizando al Cliente
    $objCondicion = new \stdClass();
    $objCondicion->codigo_cliente        = Dataworker::equalToValue($hwtCotizacion->codigo_cliente);
    $hwtCliente = Dataworker::findFirst('hwt_cliente',$objCondicion);

    Logger::write('Cliente');
    Logger::write(json_encode($hwtCliente));
    Logger::write($hwtCliente->contacto_nombre);

    $cuerpoCorreo = '<b>Atención a ' . ucwords(strtolower($hwtCliente->contacto_nombre)) . '</b><br>'
                  . '<b>EMPRESA ' . strtoupper($hwtCliente->razon_social). '</b><br>'
                  . '<p>Enviamos la Cotización de Unidades No. ' . $numCotizacion . ' esperando que sea de su Conformidad.</p></br></br>'
                  . '<p>A continuación el detalle de las Unidades:</p>';

    // ECRC: Presentando las Unidades de la Cotización
    $objCondicion = new \stdClass();
    $objCondicion->codigo_empresa        = Dataworker::equalToString  ('HWT');
    $objCondicion->num_cotizacion        = Dataworker::equalToValue  ($numCotizacion);
    $hwtCotizacionUnidad = Dataworker::getRecords('hwt_cotizacion_unidad',$objCondicion);

    $arrayDestinatarios    = array();
    $arrayDestinatariosCCO = array();

    if($hwtCotizacionUnidad->numRecords > 0) {
        // ECRC: Preparando el Encabezado de la Tabla de las Unidades
        $arrayHeaders = array(
            'Código',
            'VIN',
            'Modelo',
            'Marca',
            'Año',
            'Precio Unitario'
        );

        // ECRC: Cargando la Información de la Unidad en el Registro de Unidad de Cotización
        $arrayObjData = array();
        foreach($hwtCotizacionUnidad->data as $recordUnidadCotizacion){

            $objCondicion = new \stdClass();
            $objCondicion->codigo        = Dataworker::equalToString($recordUnidadCotizacion->codigo);
            $hwtVehiculo = Dataworker::findFirst('hwt_vehiculo',$objCondicion);

            $unidadCotizacion = new \stdClass();
            $unidadCotizacion->codigo            = $hwtVehiculo->codigo;
            $unidadCotizacion->vin            = $hwtVehiculo->vin;
            $unidadCotizacion->modelo         = $hwtVehiculo->modelo;
            $unidadCotizacion->marca          = $hwtVehiculo->marca;
            $unidadCotizacion->ann_unidad     = $hwtVehiculo->ann_unidad;
            $unidadCotizacion->precio_sin_iva = '$ ' . number_format($hwtVehiculo->precio_sin_iva,2);

            array_push($arrayObjData,$unidadCotizacion);
        }
        $tablaUnidadCotizacion = Mailer::generateTable($arrayHeaders,$arrayObjData,'tabular');

        $totalesCotizacion = new \stdClass();
        $totalesCotizacion->Subtotal  = '$ ' . number_format($hwtCotizacion->valor_subtotal,2);
        $totalesCotizacion->Impuestos = '$ ' . number_format($hwtCotizacion->valor_impuesto,2);
        $totalesCotizacion->Total     = '$ ' . number_format($hwtCotizacion->valor_total,2);
        $arrayObjData = array();
        array_push($arrayObjData,$totalesCotizacion);

        Logger::write('$arrayObjData');
        Logger::write(json_encode($arrayObjData));
        $arrayHeaders = array();
        $tablaTotalCotizacion = Mailer::generateTable($arrayHeaders,$arrayObjData,'list');
        Logger::write($tablaTotalCotizacion);

        // ECRC: Agregando al Contacto del Cliente como destinatario
        $contactoCliente = new \stdClass();
        $contactoCliente->nombre = ucwords(strtolower($hwtCliente->contacto_nombre));
        $contactoCliente->email  = $hwtCliente->contacto_email;
        array_push($arrayDestinatarios,$contactoCliente);
    }

    // ECRC: Agregando al Usuario que generó la Cotización con Copia Oculta
    $objCondicion = new \stdClass();
    $objCondicion->usuario        = Dataworker::equalToString($hwtCotizacion->usuario);
    $hwtUsuario = Dataworker::findFirst('hwt_usuario',$objCondicion);

    $contactoCliente = new \stdClass();
    $contactoCliente->nombre = ucwords(strtolower($hwtUsuario->nombre));
    $contactoCliente->email  = $hwtUsuario->email;
    array_push($arrayDestinatariosCCO,$contactoCliente);

    // ECRC: Datos Generales de la Cotizacion
    $datosGenerales = '<b>C O N D I C I O N E S</b><br>'
                    . '- Las Unidades se entregan en Condiciones Comerciales, con el uso normal y desgaste del año y modelo<br>'
                    . '- Cotización válida por 15 días, sujeta a cambios y/o modificaciones sin previo aviso.<br>'
                    . '- Los Precios indicados en éste documento son de Contado.<br>'
                    ;

    // ECRC: Cierre de Cotizacion
    $cierreCotizacion = '<p>Quedamos a disposición para cualquier duda o aclaración, esperando contar con su Aprobación y poder servirle pronto.</p><br>'
                      . '<b>A T E N T A M E N T E</b><br>'
                      . ucwords(strtolower($hwtUsuario->nombre)).'<br>'
                      . 'Correo  : '. $hwtUsuario->email .'<br>'
                      . 'Teléfono: '. $hwtUsuario->telefono .'<br>'
                      . 'Móvil   : '. $hwtUsuario->movil .'<br>'
                      ;

    // ECRC: Enviando el Correo Electrónico
    $cuerpoCorreo = $cuerpoCorreo
                  . $tablaUnidadCotizacion
                  . '<br>'
                  .'<p align="right">'
                  . $tablaTotalCotizacion
                  . '</p><br>'
                  . $datosGenerales
                  . $cierreCotizacion;

    // ECRC: Localizando a la Empresa
    $objCondicion = new \stdClass();
    $objCondicion->codigo_empresa = Dataworker::equalToString('HWT');
    $hwtEmpresa = Dataworker::findFirst('hwt_empresa',$objCondicion);
    $hwtSistema = Dataworker::findFirst('hwt_sistema');

    Logger::write('Destinararios');
    Logger::write(json_encode($arrayDestinatarios));
    Logger::write(json_encode($arrayDestinatariosCCO));

    $objMensaje->empresa          = $hwtEmpresa->nombre_empresa;
    $objMensaje->tituloCorreo     = $objMensaje->subject;
    $objMensaje->sistema          = $hwtSistema->nombre_sistema;
    $objMensaje->logotipo         = $hwtSistema->url_logotipo;
    $objMensaje->body             = $cuerpoCorreo;
    $objMensaje->destinatarios    = $arrayDestinatarios;
    $objMensaje->destinatariosCCO = $arrayDestinatariosCCO;

    Logger::write('Generando el Formato de Cotizacion');

    $objImpresion = new \stdClass();
    $objImpresion->tipo = 'INTERNA';
    $objImpresion->num_cotizacion = $hwtCotizacion->num_cotizacion;

    $archivoGenerado = formatoCotizacion($objImpresion);

    Logger::write($archivoGenerado->nombre);

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // WINDOWS
        $archivoAnexo = '../reporte/' . $archivoGenerado->nombre . '.xlsx';
    } else {
        // LINUX
        $archivoAnexo = $archivoGenerado->archivoServidor;
    }

    Logger::write('archivoAnexo : ' . $archivoAnexo);

    $nombreArchivo = 'HWT_Cotizacion_' . $hwtCotizacion->num_cotizacion;
    Mailer::attachFile($archivoAnexo,$nombreArchivo);

    $objMailResult = Mailer::sendMail($objMensaje);

    Dataworker::closeConnection();

    Emissary::prepareEnvelope();
    $availableInfo = $objMailResult->success;
    if($availableInfo){
        $apiMessage = 'Cotización enviada al Cliente';
        cambiaEstado($hwtCotizacion->num_cotizacion, 'ENVIADA');
    }
    else{
        $apiMessage = 'No se logró enviar la Cotización al Cliente'
                    . $objMailResult->response;
    }

    Emissary::success($availableInfo);
    Emissary::addMessage('info-api' , $apiMessage);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function eliminarUnidadCotizacion(){
    Logger::enable(true,'eliminarUnidadCotizacion');
    Dataworker::openConnection();

    $numCotizacion = Receiver::getApiParameter('numCotizacion');
    $rowidUnidadCotizacion = Receiver::getApiParameter('rowidUnidadCotizacion');
    $objCondicion = new \stdClass();
    $objCondicion->tableName = 'hwt_cotizacion_unidad';
    $objCondicion->keyField  = 'rowid';
    $objCondicion->keyValue  = $rowidUnidadCotizacion;
    $objDeletedRecord = Dataworker::deleteRecord(,$objCondicion);
    Dataworker::closeConnection();

    Logger::write(json_encode($objDeletedRecord));

    // ECRC: Actualizando la Cotización
    actualizaCotizacion($numCotizacion);

    // ECRC: Localizando la Cotización para retornar los Nuevos Valores
    Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->codigo_empresa        = Dataworker::equalToString  ('HWT');
    $objCondicion->num_cotizacion        = Dataworker::equalToValue  ($numCotizacion);
    $hwtCotizacion = Dataworker::getRecords('hwt_cotizacion',$objCondicion);

    Emissary::prepareEnvelope();
    $availableInfo = true;
    $apiMessage = 'La Unidad de Cotización ha sido eliminada.';

    Emissary::success($availableInfo);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('objDeletedRecord' , $objDeletedRecord);
    Emissary::addData('hwtCotizacion'    , $hwtCotizacion->data);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function grabaUnidadCotizacion(){
    Logger::enable(true,'grabaUnidadCotizacion');

    Emissary::prepareEnvelope();

    Receiver::prepareParameters();
    Dataworker::openConnection();

    $numCotizacion = Receiver::getApiParameter('tfNoCotizacion');
    $codigoUnidad  = Receiver::getApiParameter('tfCodigoUnidad');

    // ECRC: Localizando el Registro de Cotización
    $objCondicion = new \stdClass();
    $objCondicion->num_cotizacion        = Dataworker::equalToValue  ($numCotizacion);
    $hwtCotizacion = Dataworker::findFirst('hwt_cotizacion',$objCondicion);

    // ECRC: Localizando la Información de la Unidad
    $objCondicion = new \stdClass();
    $objCondicion->codigo        = Dataworker::equalToString($codigoUnidad);
    $hwtVehiculo = Dataworker::findFirst('hwt_vehiculo',$objCondicion);

    // ECRC: Calculando el Número de Partida
    $objCondicion = new \stdClass();
    $objCondicion->num_cotizacion        = Dataworker::equalToValue  ($numCotizacion);
    $numPartidaCotizacion = Dataworker::getMaxValue('hwt_cotizacion_unidad','num_partida',$objCondicion);
    Logger::write('Previo $numPartidaCotizacion: ' . json_encode($numPartidaCotizacion));

    $numPartidaCotizacion = intval($numPartidaCotizacion);
    $numPartidaCotizacion = $numPartidaCotizacion + 10;

    Logger::write('Posterior $numPartidaCotizacion: ' . json_encode($numPartidaCotizacion));

    Logger::write('hwtCotizacion...');
    Logger::write(json_encode($hwtCotizacion));

    // ECRC: Estableciendo el Resto de Valores por Defecto
    Receiver::setApiParameterValue('tfCodigoEmpresa'  ,$hwtCotizacion->codigo_empresa);
    Receiver::setApiParameterValue('tfNumCotizacion'  ,$numCotizacion);
    Receiver::setApiParameterValue('tfNumPartida'     ,$numPartidaCotizacion);
    Receiver::setApiParameterValue('tfCodigo'         ,$codigoUnidad);
    Receiver::setApiParameterValue('tfPrecioUnitario' ,$hwtVehiculo->precio_sin_iva);

    $objCamposRegistro   = Dataworker::setFieldsTable('hwt_cotizacion_unidad');

    Logger::write(json_encode($objCamposRegistro));

    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    // ECRC: Actualizando la Cotización
    actualizaCotizacion($numCotizacion);

    // ECRC: Localizando la Cotización para retornar los Nuevos Valores
    Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->codigo_empresa        = Dataworker::equalToString  ('HWT');
    $objCondicion->num_cotizacion        = Dataworker::equalToValue  ($numCotizacion);
    $hwtCotizacion = Dataworker::getRecords('hwt_cotizacion',$objCondicion);

    $registroActualizado = true;
    $apiMessage = 'Registro almacenado en la Base de Datos';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api'        , $apiMessage);
    Emissary::addMessage('sql-ejecutado'   , $sqlEjecutado);
    Emissary::addData('camposRegistro' , $objCamposRegistro);
    Emissary::addData('hwtCotizacion'  , $hwtCotizacion->data);
    Dataworker::closeConnection();

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function datosCotizacion(){
    Logger::enable(true,'datosCotizacion');

    Dataworker::openConnection();
    $numCotizacion = Receiver::getApiParameter('numCotizacion');
    Emissary::prepareEnvelope();

    Logger::write('iniciando la busqueda');

    // ECRC: Localizando la Cotizacion
    $objCondicion = new \stdClass();
    $objCondicion->num_cotizacion        = Dataworker::equalToValue($numCotizacion);
    $hwtCotizacion = Dataworker::findFirst('hwt_cotizacion',$objCondicion);

    // ECRC: Localizando al Cliente
    $objCondicion = new \stdClass();
    $objCondicion->codigo_cliente        = Dataworker::equalToValue($hwtCotizacion->codigo_cliente);
    $hwtCliente = Dataworker::findFirst('hwt_cliente',$objCondicion);
    $hwtCotizacion->nombre_cliente = $hwtCliente->razon_social;

    Logger::write(json_encode($hwtCotizacion));

    $availableInfo = true;
    $apiMessage = 'Registros Localizados';
    Emissary::addMessage('info-api'       , $apiMessage);
    Emissary::addData('hwtCotizacion' , $hwtCotizacion);

    Dataworker::closeConnection();
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function listaCotizacion(){
    Logger::enable(true,'listaCotizacion');

    Emissary::prepareEnvelope();
    Dataworker::openConnection();

    $objCondicion = new \stdClass();
    $objCondicion->codigo_empresa        = Dataworker::equalToString  ('HWT');
    $hwtCotizacion = Dataworker::getRecords('hwt_cotizacion',$objCondicion);

    Logger::write('va por auqi');
    Logger::write(json_encode($hwtCotizacion));

    if($hwtCotizacion->numRecords > 0) {
        Logger::write(json_encode($hwtCotizacion->data));

        foreach($hwtCotizacion->data as $recordCotizacion){
            // ECRC: Localizando al Cliente
            $objCondicion = new \stdClass();
            $objCondicion->codigo_cliente        = Dataworker::equalToValue($recordCotizacion->codigo_cliente);
            $hwtCliente = Dataworker::findFirst('hwt_cliente',$objCondicion);

            $recordCotizacion->nombre_cliente = $hwtCliente->razon_social;

            // ECRC: Localizando al Usuario
            $objCondicion = new \stdClass();
            $objCondicion->usuario        = Dataworker::equalToString($recordCotizacion->usuario);
            $hwtUsuario = Dataworker::findFirst('hwt_usuario',$objCondicion);

            $recordCotizacion->nombre_usuario = $hwtUsuario->nombre;
        }

        $availableInfo = true;
        $apiMessage = 'Registros Localizados';
        Emissary::addMessage('info-api'       , $apiMessage);
        Emissary::addData('hwtCotizacion' , $hwtCotizacion->data);
        Emissary::addData('numRecords'    , $hwtCotizacion->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No hay Registros en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
    }

    Dataworker::closeConnection();
    Emissary::success(true);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function listaUnidadCotizacion(){
    Emissary::prepareEnvelope();
    Dataworker::openConnection();

    $objCondicion = new \stdClass();
    $objCondicion->codigo_empresa        = Dataworker::equalToString  ('HWT');
    $objCondicion->num_cotizacion        = Dataworker::equalToValue  (Receiver::getApiParameter('tfNumCotizacion'));
    $hwtCotizacionUnidad = Dataworker::getRecords('hwt_cotizacion_unidad',$objCondicion);

    if($hwtCotizacionUnidad->numRecords > 0) {

        // ECRC: Cargando la Información de la Unidad en el Registro de Unidad de Cotización
        foreach($hwtCotizacionUnidad->data as $recordUnidadCotizacion){

            $objCondicion = new \stdClass();
            $objCondicion->codigo        = Dataworker::equalToString($recordUnidadCotizacion->codigo);
            $hwtVehiculo = Dataworker::findFirst('hwt_vehiculo',$objCondicion);

            $recordUnidadCotizacion->vin            = $hwtVehiculo->vin;
            $recordUnidadCotizacion->modelo         = $hwtVehiculo->modelo;
            $recordUnidadCotizacion->marca          = $hwtVehiculo->marca;
            $recordUnidadCotizacion->ann_unidad     = $hwtVehiculo->ann_unidad;
            $recordUnidadCotizacion->precio_sin_iva = $hwtVehiculo->precio_sin_iva;
            $recordUnidadCotizacion->precio_con_iva = $hwtVehiculo->precio_con_iva;
        }

        $availableInfo = true;
        $apiMessage = 'Registros Localizados';
        Emissary::addMessage('info-api'             , $apiMessage);
        Emissary::addData('hwtCotizacionUnidad' , $hwtCotizacionUnidad->data);
        Emissary::addData('numRecords'          , $hwtCotizacionUnidad->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No hay Registros en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
    }

    Dataworker::closeConnection();
    Emissary::success(true);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function actualizaCotizacion($pNumCotizacion){
    Logger::enable(true,actualizaCotizacion);

    Logger::write('Iniciando actualizaCotizacion');

    Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->codigo_empresa        = Dataworker::equalToString  ('HWT');
    $objCondicion->num_cotizacion        = Dataworker::equalToValue  ($pNumCotizacion);
    $hwtCotizacionUnidad = Dataworker::getRecords('hwt_cotizacion_unidad',$objCondicion);

    $pctImpuesto   = .16;
    $valorSubtotal = 0;
    $valorImpuesto = 0;
    $valorTotal    = 0;
    foreach($hwtCotizacionUnidad->data as $recordUnidadCotizacion){
        $precioUnitario = floatval($recordUnidadCotizacion->precio_unitario);

        Logger::write('precioUnitario: ' . $precioUnitario);
        $valorSubtotal = $valorSubtotal + $precioUnitario;

        Logger::write('valorSubtotal: ' . $valorSubtotal);
    }

    $valorImpuesto = round($valorSubtotal * $pctImpuesto,0);
    $valorTotal    = $valorSubtotal + $valorImpuesto;

    Receiver::resetApiParameters();
    /*
    Receiver::setApiParameterValue('tfCodigoEmpresa' ,'HWT');
    Receiver::setApiParameterValue('tfNumCotizacion' ,$pNumCotizacion);
    Receiver::setApiParameterValue('tfValorSubtotal' ,$valorSubtotal);
    Receiver::setApiParameterValue('tfValorImpuesto' ,$valorImpuesto);
    Receiver::setApiParameterValue('tfValorTotal'    ,$valorTotal);
    */

    $hwtCotizacion = new \stdClass();
    $hwtCotizacion->codigo_empresa = 'HWT';
    $hwtCotizacion->num_cotizacion = $pNumCotizacion;
    $hwtCotizacion->valor_subtotal = $valorSubtotal;
    $hwtCotizacion->valor_impuesto = $valorImpuesto;
    $hwtCotizacion->valor_total    = $valorTotal;

    $objCamposRegistro   = Dataworker::setFieldsTable('hwt_cotizacion',$hwtCotizacion);
    Logger::write(json_encode($objCamposRegistro));

    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    return $sqlEjecutado;
}

function cambiaEstado($pNumCotizacion,$pEstado){
    Logger::enable(true,cambiaEstado);
    Dataworker::openConnection();

    Logger::write('Iniciando cambiaEstado');

    Receiver::resetApiParameters();
    /*
    Receiver::setApiParameterValue('tfCodigoEmpresa' ,'HWT');
    Receiver::setApiParameterValue('tfNumCotizacion' ,$pNumCotizacion);
    Receiver::setApiParameterValue('tfEstado'        ,$pEstado);
    */

    $hwtCotizacion = new \stdClass();
    $hwtCotizacion->codigo_empresa = 'HWT';
    $hwtCotizacion->num_cotizacion = $pNumCotizacion;
    $hwtCotizacion->estado         = $pEstado;

    $objCamposRegistro   = Dataworker::setFieldsTable('hwt_cotizacion',$hwtCotizacion);
    Logger::write(json_encode($objCamposRegistro));

    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    return $sqlEjecutado;
}

function eliminarCotizacion(){
    Logger::enable(true,'eliminarCotizacion');
    Dataworker::openConnection();

    $numCotizacion = Receiver::getApiParameter('numCotizacion');
    $rowidCotizacion = Receiver::getApiParameter('rowidCotizacion');

    $objCondicion = new \stdClass();
    $objCondicion->tableName = 'hwt_cotizacion';
    $objCondicion->keyField  = 'rowid';
    $objCondicion->keyValue  = $rowidCotizacion;
    $objDeletedRecord = Dataworker::deleteRecord(,$objCondicion);
    Dataworker::closeConnection();

    Logger::write(json_encode($objDeletedRecord));

    Emissary::prepareEnvelope();
    $availableInfo = true;
    $apiMessage = 'La Unidad de Cotización ha sido eliminada.';

    Emissary::success($availableInfo);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('objDeletedRecord' , $objDeletedRecord);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function grabaCotizacion(){
    Logger::enable(true,'grabaCotizacion');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    $numCliente    = Receiver::getApiParameter('tfCodigoCliente');
    $numCotizacion = Dataworker::getNextSequence('seq_cotizacion');
    $estado        = Receiver::getApiParameter('tfEstado');

    Logger::write('numCotizacion: ' . $numCotizacion);
    Receiver::setApiParameterValue('tfCodigoEmpresa'      ,'HWT');
    Receiver::setApiParameterValue('tfNumCotizacion'      ,$numCotizacion);

    if($estado === ''){
        Receiver::setApiParameterValue('tfEstado' ,'PENDIENTE');
    }

    $objCamposRegistro   = Dataworker::setFieldsTable('hwt_cotizacion');

    Logger::write(json_encode($objCamposRegistro));

    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    $registroActualizado = true;
    $apiMessage = 'Registro almacenado en la Base de Datos';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('camposRegistro' , $objCamposRegistro);
    Dataworker::closeConnection();

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);



