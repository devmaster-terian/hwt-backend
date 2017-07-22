<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Dataworker.php');
require_once('../recurso/clase/Logger.php');
require_once('../recurso/clase/Mnemea.php');
require_once('../recurso/clase/Reporter.php');
require_once('../recurso/clase/Cabinet.php');
require_once('apiConfigurador.php');

function reporteCondicionUnidad(){
    Logger::enable(true,'reporteCondicionUnidad');

    $rowidCondicionUnidad = Receiver::getApiParameter('rowidCondicionUnidad');
    $vinUnidad            = Receiver::getApiParameter('vinUnidad');
    $numReporte           = Receiver::getApiParameter('numReporte');

    $archivoGenerado = Reporter::openFile('reporteCondicionUnidadVIN' . $vinUnidad);
    $nombreReporte = "Condicion de la Unidad VIN " . $vinUnidad;

    $objCondicion = new \stdClass();
    $objCondicion->vin        = Dataworker::equalToString($vinUnidad);
    $hwtVehiculo = Dataworker::findFirst('hwt_vehiculo',$objCondicion);

    Logger::write('hwtVehiculo');
    Logger::write(json_encode($hwtVehiculo));

    Reporter::setMaxColumn('F');
    Reporter::prepareHeader($nombreReporte);

    //ECRC: Preparando el Titulo de las Columnas
    $arrayTituloColumnas = array(
        "14:ESTADO",
        "45:DESCRIPCION\rCARACTERISTICA",
        "15:VALOR\rREFERENCIA",
        "30:OBSERVACIONES",
        "15:PRECIO\rESTIMADO",
        "13:FOTO"
    );

    Reporter::prepareTitleColumns($arrayTituloColumnas);

    // ECRC: Escribiendo el Contenido del Archivo

    // ECRC: Generando las Secciones de Condición de la Unidad
    $Conexion      = Dataworker::openConnection();
    $objectSeccion = conjuntoParametros('rep_condicion_seccion');

    $unidadNumReparaciones   = 0;
    $unidadValorReparaciones = 0;

    Logger::write('Va Auiq');

    foreach($objectSeccion as $indice=>$seccion){
        // ECRC: Escribiendo las Líneas de Condición de la Unidad
        Receiver::setApiParameterValue('cbxOpcionSeccion',$seccion->codigo);
        Receiver::setApiParameterValue('tfNumReporte'    ,$numReporte);

        $objReporteCondicionLinea = listaReporteCondicionLinea(true);
        $objHwtReporteCondicionLinea = $objReporteCondicionLinea->hwtReporteCondicionLinea;

        Logger::write(json_encode($objHwtReporteCondicionLinea));

        $seccionNumReparaciones   = 0;
        $seccionValorReparaciones = 0;
        $numRegistro              = 0;
        $registroPendiente        = 0;
        $arrayFotografia          = array();

        Reporter::decreaseCurrentRow();
        foreach($objHwtReporteCondicionLinea as $hwtReporteCondicionLinea){
            if($numRegistro === 0){
                $tituloSeccion = $seccion->codigo . ' ' . $seccion->descripcion. ' (' . $vinUnidad . ')';
                Reporter::printVerticalSeparator($tituloSeccion, 'FFFFFF', '6a9bc3');
            }

            Logger::write('>>>hwtReporteCondicionLinea: ' . $hwtReporteCondicionLinea->desc_caracteristica);
            Reporter::writeCell('A' . Reporter::getCurrentRow(),$hwtReporteCondicionLinea->estado);
            Reporter::writeCell('B' . Reporter::getCurrentRow(),$hwtReporteCondicionLinea->desc_caracteristica);
            Reporter::writeCell('C' . Reporter::getCurrentRow(),$hwtReporteCondicionLinea->valor_referencia);
            Reporter::writeCell('D' . Reporter::getCurrentRow(),$hwtReporteCondicionLinea->observaciones);
            Reporter::writeCell('E' . Reporter::getCurrentRow(),number_format(floatval($hwtReporteCondicionLinea->precio_unitario_estimado), 2));

            if($hwtReporteCondicionLinea->fotografia !== ''){
                Reporter::writeCell('F' . Reporter::getCurrentRow(),$hwtReporteCondicionLinea->cod_caracteristica);
            }

            Reporter::increaseCurrentRow();

            // ECRC: Almacenando las Fotografias
            if($hwtReporteCondicionLinea->fotografia !== ''){
                $objFotografia = new \stdClass();
                $objFotografia->caracteristica = $hwtReporteCondicionLinea->cod_caracteristica;
                $objFotografia->observaciones  = $hwtReporteCondicionLinea->observaciones;
                $objFotografia->fotografia     = $hwtReporteCondicionLinea->fotografia;

                array_push($arrayFotografia,$objFotografia);
            }

            if($hwtReporteCondicionLinea->estado === 'PENDIENTE'){
                $registroPendiente = $registroPendiente + 1;
            }

            if($hwtReporteCondicionLinea->estado === 'REPARAR'){
                $seccionNumReparaciones   = $seccionNumReparaciones   + 1;
                $seccionValorReparaciones = $seccionValorReparaciones + floatval($hwtReporteCondicionLinea->precio_unitario_estimado);

                $unidadNumReparaciones   = $unidadNumReparaciones   + 1;
                $unidadValorReparaciones = $unidadValorReparaciones + floatval($hwtReporteCondicionLinea->precio_unitario_estimado);
            }

            $numRegistro = $numRegistro + 1;
        } // foreach objHwtReporteCondicionLinea

        $filaPrincipal = Reporter::getCurrentRow();
        $columnaPrincipal = Reporter::getMaxColumn();

        // ECRC: Generando la Hoja con las Imágenes de las Reparaciones
        $indiceHoja = Reporter::createSheet($seccion->codigo);
        Reporter::setActiveSheet($indiceHoja);
        Reporter::setCurrenRow(1);
        Reporter::setMaxColumn('I');
        Reporter::printVerticalSeparator($tituloSeccion, 'FFFFFF', '6a9bc3');

        $fila      = 2;
        $numImagen = 1;
        foreach($arrayFotografia as $objFotografia){

            $idColumna = 'A';
            if($numImagen % 2 === 0){
                $idColumna = 'F';
            }

            Reporter::writeCell($idColumna . $fila,'Imagen ' . $objFotografia->caracteristica);

            $urlFotografia = $objFotografia->fotografia;

            Logger::write('fotografia...');
            Logger::write($urlFotografia);

            $objImagen = new \stdClass();
            $objImagen->url     = $urlFotografia;
            $objImagen->offsetX = 5;
            $objImagen->offsetY = 5;
            $objImagen->cell    = $idColumna . ($fila + 1);
            $objImagen->width   = 170;
            $objImagen->height  = 170;

            if($numImagen % 2 === 0){
                $fila = $fila + 11;
            }

            if($numImagen % 8 === 0){
                $fila = $fila + 2;
            }

            Reporter::drawImage($objImagen);

            $numImagen = $numImagen + 1;
        }

        // ECRC: Regresando a la Hoja Principal para continuar desplegando la Información
        Reporter::setActiveSheet(0);
        Reporter::setCurrenRow($filaPrincipal);
        Reporter::setMaxColumn($columnaPrincipal);
        if($numRegistro !== 0){
            if($seccionNumReparaciones !== 0){
                $totalSeccion = 'SECCION CON ' . $seccionNumReparaciones . ' '
                    . 'REPARACIONES ($ '
                    . number_format($seccionValorReparaciones) . ')';
                $bgColor = '7f0000';
            }
            else{
                if($registroPendiente > 0){
                    $totalSeccion = 'SECCION CON ' . $registroPendiente . ' CARACTERISTICAS PENDIENTES DE VERIFICAR';
                    $bgColor = 'cc8400';
                }
                else{
                    $totalSeccion = 'SECCION LIBRE DE REPARACIONES';
                    $bgColor = '004c00';
                }
            }
            Reporter::printVerticalSeparator($totalSeccion,'FFFFFF', $bgColor);
            Reporter::increaseCurrentRow();
        }
    }

    if($unidadNumReparaciones !== 0){
        $totalUnidad = 'UNIDAD CON ' . $unidadNumReparaciones . ' '
            . 'REPARACIONES ($ '
            . number_format($unidadValorReparaciones) . ')';
        $bgColor = '660000';
    }
    else{
        $totalUnidad = 'UNIDAD LIBRE DE REPARACIONES';
        $bgColor = '004000';
    }

    Reporter::printVerticalSeparator($totalUnidad,'FFFFFF', $bgColor);


    // ECRC: Generando la Hoja con las Imágenes de la Unidad
    $indiceHoja = Reporter::createSheet('Fotos Unidad');
    Reporter::setActiveSheet($indiceHoja);
    Reporter::setCurrenRow(1);
    Reporter::setMaxColumn('I');
    $tituloSeccion = 'FOTOGRAFIAS DE LA UNIDAD ' . $hwtVehiculo->vin;
    Reporter::printVerticalSeparator($tituloSeccion, 'FFFFFF', '6a9bc3');


    $rutaImagenesUnidad = dirname(__FILE__);
    Logger::write('rutaImagenesUnidad');
    Logger::write($rutaImagenesUnidad);

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // WINDOWS
        $rutaImagenesUnidad = str_replace('gestion\controlador','recursos\imagen',$rutaImagenesUnidad);
    }
    else{
        // LINUX
        $rutaImagenesUnidad = str_replace('gestion/controlador','recursos/imagen', $rutaImagenesUnidad);
    }

    $fila      = 2;
    $numImagen = 1;
    for($iCuentaImagen = 1; $iCuentaImagen <= 20; $iCuentaImagen++){
        $archivoImagen = $rutaImagenesUnidad . DIRECTORY_SEPARATOR
            . $hwtVehiculo->modelo . '_' . $hwtVehiculo->codigo . DIRECTORY_SEPARATOR
            . 'imagen' . str_pad($iCuentaImagen, 2, '0', STR_PAD_LEFT)
            . '-min.jpg';

        Logger::write($archivoImagen);

        if(file_exists($archivoImagen)){
            $idColumna = 'A';
            if($numImagen % 2 === 0){
                $idColumna = 'F';
            }

            $objImagen = new \stdClass();
            $objImagen->url     = $archivoImagen;
            $objImagen->offsetX = 5;
            $objImagen->offsetY = 5;
            $objImagen->cell    = $idColumna . ($fila + 1);
            $objImagen->width   = 280;
            $objImagen->height  = 280;
            $objImagen->absoluto = true;

            if($numImagen % 2 === 0){
                $fila = $fila + 17;
            }

            if($numImagen % 8 === 0){
                $fila = $fila + 2;
            }

            Logger::write('Existe el Archivo ' . $objImagen->url);
            Reporter::drawImage($objImagen);

            $numImagen = $numImagen + 1;
        }

    }

    Reporter::saveFile();

    $objArchivoGenerado = new \stdClass();
    $objArchivoGenerado->nombre   = $archivoGenerado;

    Emissary::prepareEnvelope();

    $availableInfo = true;
    $apiMessage = 'Archivo generado: ' . $archivoGenerado;
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('archivoGenerado' , $objArchivoGenerado);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();

    echo json_encode($objReturn);
}

function eliminaCondicionUnidad(){
    Logger::enable(true,'eliminaCondicionUnidad');

    $rowidCondicionUnidad             = Receiver::getApiParameter('rowidCondicionUnidad');

    //ECRC: Verificar inicialmente la Existencia del Registro
    Dataworker::openConnection();

    $objDeleteRecord = new \stdClass();
    $objDeleteRecord->tableName = 'hwt_reporte_condicion';
    $objDeleteRecord->keyField  = 'rowid';
    $objDeleteRecord->keyValue  = $rowidCondicionUnidad;

    $objDeletedRecord = Dataworker::deleteRecord($objDeleteRecord);

    Logger::write(json_encode($objDeletedRecord));

    Emissary::prepareEnvelope();

    $availableInfo = true;
    $apiMessage = 'El registro de Condiciones de la Unidad ha sido eliminado.';

    Emissary::success($availableInfo);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('objDeletedRecord' , $objDeletedRecord);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
} // eliminaCondicionUnidad

function eliminaCondicionLinea(){
    Logger::enable(true,'cargaImagenCondicionLinea');

    $rowidCondicionLinea             = Receiver::getApiParameter('rowidLinea');

    //ECRC: Verificar inicialmente la Existencia del Registro
    Dataworker::openConnection();

    $objDeleteRecord = new \stdClass();
    $objDeleteRecord->tableName = 'hwt_reporte_condicion_linea';
    $objDeleteRecord->keyField  = 'rowid';
    $objDeleteRecord->keyValue  = $rowidCondicionLinea;

    $objDeletedRecord = Dataworker::deleteRecord($objDeleteRecord);

    Logger::write(json_encode($objDeletedRecord));

    Emissary::prepareEnvelope();

    $availableInfo = true;
    $apiMessage = 'La Condicion de la Unidad ha sido eliminada.';

    Emissary::success($availableInfo);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('objDeletedRecord' , $objDeletedRecord);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
} // eliminaCondicionLinea

function eliminaImagenCondicionLinea(){
    Logger::enable(true,'eliminaImagenCondicionLinea');

    Dataworker::openConnection();

    $objCondicion = new \stdClass();
    $objCondicion->num_reporte        = Dataworker::equalToString(Receiver::getApiParameter('numReporte'));
    $objCondicion->num_sequencia      = Dataworker::equalToString(Receiver::getApiParameter('numSequencia'));
    $objCondicion->cod_seccion        = Dataworker::equalToString(Receiver::getApiParameter('codSeccion'));
    $objCondicion->cod_caracteristica = Dataworker::equalToString(Receiver::getApiParameter('codCaracteristica'));

    $hwtReporteCondicionLinea = Dataworker::findFirst('hwt_reporte_condicion_linea',$objCondicion);
    if($hwtReporteCondicionLinea->activeRecord === '1'){
        Logger::write($hwtReporteCondicionLinea->fotografia);

        $imagenExistente = $hwtReporteCondicionLinea->fotografia;
        $objArchivoBorrado = Cabinet::deleteFile($imagenExistente);

        Logger::write(json_encode($objArchivoBorrado));

        if($objArchivoBorrado->success){
            $apiMessage = 'Imagen eliminada correctamente';
            //ECRC: Actualizando el Registro con la URL de la Imagen
            $hwtReporteCondicionLinea->fotografia = '';
            $objCamposReporteCondicionLinea = Dataworker::setFieldsTable(
                $Conexion,
                'hwt_reporte_condicion_linea',
                $hwtReporteCondicionLinea);
            Logger::write('Antes de Actualiza el Registro con la Imagen Borrada');
            Dataworker::updateRecord($objCamposReporteCondicionLinea);
        }
        else{
            $apiMessage = 'Fallo el borrado de la Imagen';
        }
    }

    Emissary::prepareEnvelope();

    Emissary::success($objArchivoBorrado->success);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('objImagen' , $objArchivoBorrado);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
} // eliminaImagenCondicionLinea

function cargaImagenCondicionLinea(){
    Logger::enable(true,'cargaImagenCondicionLinea');

    //ECRC: Verificar inicialmente la Existencia del Registro
    Dataworker::openConnection();

    $objCondicion = new \stdClass();
    $objCondicion->num_reporte        = Dataworker::equalToString(Receiver::getPostParameter('numReporte'));
    $objCondicion->num_sequencia      = Dataworker::equalToString(Receiver::getPostParameter('numSequencia'));
    $objCondicion->cod_seccion        = Dataworker::equalToString(Receiver::getPostParameter('codSeccion'));
    $objCondicion->cod_caracteristica = Dataworker::equalToString(Receiver::getPostParameter('codCaracteristica'));

    $hwtReporteCondicionLinea = Dataworker::findFirst('hwt_reporte_condicion_linea',$objCondicion);

    Logger::write('$hwtReporteCondicionLinea');
    Logger::write(json_encode($hwtReporteCondicionLinea));

    $availableInfo = false;
    $objImagen = null;
    $objResultReparaciones = null;
    if($hwtReporteCondicionLinea->activeRecord === '1' && isset($_FILES)) {
        $dupa = json_encode($_FILES);
        Logger::write($dupa);

        $archivoCarga = Cabinet::getFileString('tfLineaFotografia');
        Logger::write('$archivoCarga - ' . $archivoCarga);

        $objArchivoCarga = new \stdClass();
        $objArchivoCarga->campoArchivoForm    = $archivoCarga;
        $objArchivoCarga->imagenOrigen        = Receiver::getPostParameter('imagenOrigen');
        $objArchivoCarga->imagenDocumento     = Receiver::getPostParameter('imagenDocumento');
        $objArchivoCarga->imagenIdentificador = Receiver::getPostParameter('imagenIdentificador');
        $objArchivoCarga->temp_file_name      = $_FILES[$archivoCarga]['tmp_name'];
        $objArchivoCarga->original_file_name  = $_FILES[$archivoCarga]['name'];

        Logger::write($objArchivoCarga->temp_file_name);
        Logger::write($objArchivoCarga->temp_file_name);
        Logger::write($objArchivoCarga->original_file_name);

        $objImagen = Cabinet::saveFile($objArchivoCarga);

        if($objImagen->success === true){
            $availableInfo = true;

            //ECRC: Actualizando el Registro con la URL de la Imagen
            $hwtReporteCondicionLinea->fotografia = $objImagen->file;

            //ECRC: Actualizando el Resto de la Información de la Línea
            $hwtReporteCondicionLinea->estado                   = Receiver::getPostParameter('estado');
            $hwtReporteCondicionLinea->valor_referencia         = Receiver::getPostParameter('valorReferencia');
            $hwtReporteCondicionLinea->observaciones            = Receiver::getPostParameter('observaciones');
            $hwtReporteCondicionLinea->precio_unitario_estimado = Receiver::getPostParameter('precioEstimado');

            $objCamposReporteCondicionLinea = Dataworker::setFieldsTable(
                'hwt_reporte_condicion_linea',
                $hwtReporteCondicionLinea);
            Dataworker::updateRecord($objCamposReporteCondicionLinea);

            //ECRC: Calculando las Reparaciones
            $objResultReparaciones = calcularReparaciones($hwtReporteCondicionLinea->num_reporte,true);

        }
        else{
            $availableInfo = false;
        }
    }
    else{
        $apiMessage = 'Debe grabar el Registro de la Condición antes de integrar Imágenes';
    }

    if($availableInfo){
        $apiMessage = 'Se ha cargado la Imagen al Repositorio.';
    }
    else{
        if($apiMessage === ''){
            $apiMessage = 'No se logró Procesar la Imagen';
        }
    }

    Emissary::prepareEnvelope();

    Emissary::success($availableInfo);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('objImagen' , $objImagen);

    if($objResultReparaciones !== null){
        Emissary::addData('objResultReparaciones' , $objResultReparaciones);
    }

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
} // cargaImagenCondicionLinea

function cambiaEstadoLinea(){
    Logger::enable(true,'cambiaEstadoLinea');

    $lineaNumReporte   = Receiver::getApiParameter('lineaNumReporte');
    $lineaCodSequencia = Receiver::getApiParameter('lineaCodSequencia');
    $lineaEstado       = Receiver::getApiParameter('lineaEstado');

    Emissary::prepareEnvelope();

    Dataworker::openConnection();
    $objectOpcionesEstadoCaracteristica  = listaParametro('combos_rep_condicion','estado_caracteristica');


    $numMaximoEstado = count((array)$objectOpcionesEstadoCaracteristica);
    Logger::write('Iniciando la descompisicion de Opciones');
    Logger::write($numMaximoEstado);

    $estadoActual = 0;
    foreach($objectOpcionesEstadoCaracteristica as $estadoCaracteristica){
        if($lineaEstado === $estadoCaracteristica->descripcion){
            $estadoActual = intval($estadoCaracteristica->codigo);
        }

        Logger::write('$estadoActual:' .  $estadoActual);
        Logger::write('$lineaEstado:'.$lineaEstado);
        Logger::write($estadoCaracteristica->codigo);
        Logger::write($estadoCaracteristica->descripcion);
    }

    Logger::write('reicibo $estadoActual:' . $estadoActual);

    $estadoActual = $estadoActual + 1;

    if($estadoActual > $numMaximoEstado){
        $estadoActual = 1;
    }

    $descEstadoAsignado = '';
    foreach($objectOpcionesEstadoCaracteristica as $estadoCaracteristica){
        if($estadoActual === intval($estadoCaracteristica->codigo)){
            $descEstadoAsignado = $estadoCaracteristica->descripcion;
        }

        Logger::write($estadoCaracteristica->codigo);
        Logger::write($estadoCaracteristica->descripcion);
    }

    Logger::write('$estadoActual:' . $estadoActual);
    Logger::write('$descEstadoAsignado: ' . $descEstadoAsignado);

    $SqlActualiza = "UPDATE hwt_reporte_condicion_linea "
                  . "SET estado = '$descEstadoAsignado'"
                  . " WHERE num_reporte = $lineaNumReporte "
                  . "   AND num_sequencia = $lineaCodSequencia";
    $resultHwtCondicionUnidad = Dataworker::executeQuery($SqlActualiza);
    Dataworker::closeConnection();

    $registroActualizado = true;
    $apiMessage = 'Registro actualizado en la Base de Datos';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api' , $apiMessage);
    /*Emissary::addMessage('sql-ejecutado' , $sqlEjecutado);*/
    /*Emissary::addData('camposRegistro' , $resultHwtCondicionUnidad);*/

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
} // cambiaEstadoLinea

function grabaCondicionLinea(){
    Logger::enable(true,'grabaCondicionLinea');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    // ECRC: Usando el Secuenciador de CondicionUnidadLinea
    Receiver::getApiListParameters();

    $tfLineaNumReporte             = Receiver::getApiParameter('tfLineaNumReporte');
    $tfLineaNumSequencia           = Receiver::getApiParameter('tfLineaNumSequencia');
    $tfLineaCodSeccion             = Receiver::getApiParameter('tfLineaCodSeccion');
    $tfLineaCodCaracteristica      = Receiver::getApiParameter('tfLineaCodCaracteristica');
    $tfLineaDescCaracteristica     = Receiver::getApiParameter('tfLineaDescCaracteristica');
    $cbxLineaEstado                = Receiver::getApiParameter('cbxLineaEstado');
    $tfLineaValorReferencia        = Receiver::getApiParameter('tfLineaValorReferencia');
    $tfLineaObservaciones          = Receiver::getApiParameter('tfLineaObservaciones');
    $tflineaPrecioUnitarioEstimado = Receiver::getApiParameter('tflineaPrecioUnitarioEstimado');

    // ECRC: Actualizando la Secuencia y la Caracteristica cuando es una Línea de Suplemento
    Logger::write('Antes de Actualizacion tfLineaNumSequencia > ' . $tfLineaNumSequencia);
    if(trim($tfLineaNumSequencia) === ''){
        Logger::write('Secuencia vacia');
        $fechaActual = new DateTime();

        $tfLineaNumSequencia = $fechaActual->getTimestamp();
        $tfLineaCodCaracteristica = $tfLineaCodSeccion . $fechaActual->getTimestamp();

        Logger::write('tfLineaNumSequencia ' . $tfLineaNumSequencia);
        Logger::write('tfLineaCodCaracteristica ' . $tfLineaCodCaracteristica);
    }

    $objHwtReporteCondicionLinea = (object) [
        num_reporte              => $tfLineaNumReporte,
        num_sequencia            => $tfLineaNumSequencia,
        cod_seccion              => $tfLineaCodSeccion,
        cod_caracteristica       => $tfLineaCodCaracteristica,
        desc_caracteristica      => utf8_decode($tfLineaDescCaracteristica),
        estado                   => $cbxLineaEstado,
        observaciones            => $tfLineaObservaciones,
        valor_referencia         => $tfLineaValorReferencia,
        precio_unitario_estimado => $tflineaPrecioUnitarioEstimado
    ];

    $objCamposReporteCondicionLinea = Dataworker::setFieldsTable(
        'hwt_reporte_condicion_linea',
        $objHwtReporteCondicionLinea);
    $sqlEjecutado      = Dataworker::updateRecord($objCamposReporteCondicionLinea);
    Dataworker::closeConnection();

    //ECRC: Calculando las Reparaciones
    $objResultReparaciones = calcularReparaciones($tfLineaNumReporte,true);
    $registroActualizado = true;
    $apiMessage = 'Registro actualizado en la Base de Datos';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('totalReparaciones' , $objResultReparaciones->totalReparaciones);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
} // grabaCondicionLinea

function calcularReparaciones($pNumReporte, $pOperacionInterna){
    Emissary::prepareEnvelope();
    Dataworker::openConnection();

    if($pNumReporte !== null){
        $numReporte = $pNumReporte;
    }
    else{
        $numReporte = Receiver::getApiParameter('tfNumReporte');
    }

    $objSqlReporteCondicion = (object) [
        num_reporte => ' = '.$numReporte
    ];
    $objResultQuery = Dataworker::getRecords('hwt_reporte_condicion_linea',$objSqlReporteCondicion);

    //ECRC: Calculando el Valor de las Reparaciones y almacenando en el Reporte de Condicion
    $precioTotalEstimado  = 0;
    $numeroReparaciones = 0;

    $objRecord = $objResultQuery->data;
    foreach($objRecord as $position => $record){
        if($record->estado === 'REPARAR'){
            $numeroReparaciones  = $numeroReparaciones + 1;
            $precioTotalEstimado = $precioTotalEstimado + floatval($record->precio_unitario_estimado);
        }
    }

    Logger::write('Reseteando los Parametros');
    Receiver::resetApiParameters();

    // ECRC: Actualizando los Totales en el Registro
    $objSqlReporteCondicion = (object) [
        num_reporte              => ' = "'.$numReporte.'"',
    ];
    $recordHwtReporteCondicion = Dataworker::findFirst('hwt_reporte_condicion',$objSqlReporteCondicion);

    $objHwtReporteCondicion = (object) [
        num_reporte              => $recordHwtReporteCondicion->num_reporte,
        vin                      => $recordHwtReporteCondicion->vin,
        usuario                  => $recordHwtReporteCondicion->usuario,
        precio_total_estimado    => $precioTotalEstimado,
        num_reparaciones         => $numeroReparaciones
    ];

    $objCamposReporteCondicion = Dataworker::setFieldsTable(
        'hwt_reporte_condicion',
        $objHwtReporteCondicion);
    Dataworker::updateRecord($objCamposReporteCondicion);

    Dataworker::closeConnection();

    if($objResultQuery->numRecords > 0){
        $availableInfo = true;
    }
    else{
        $availableInfo = false;
    }

    $objTotalReparaciones = new \stdClass;
    $objTotalReparaciones->num_reparaciones      = $numeroReparaciones;
    $objTotalReparaciones->precio_total_estimado = $precioTotalEstimado;

    Emissary::success($availableInfo);
    Emissary::addData('hwtCalculoReparaciones' , $objResultQuery->data);
    Emissary::addData('totalReparaciones'   , $objTotalReparaciones);
    $objReturn = Emissary::getEnvelope();

    if($pOperacionInterna === true){
        return $objReturn;
    }
    else{

        echo json_encode($objReturn);
    }
} // calcularReparaciones

function datosReporteCondicion(){
    $numReporte = Receiver::getApiParameter('numReporte');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();
    $SqlCliente = "SELECT * FROM hwt_reporte_condicion WHERE num_reporte = '$numReporte'";
    $resultHwtReporteCondicion = Dataworker::executeQuery($SqlCliente);

    if($resultHwtReporteCondicion->numRecords > 0) {
        $availableInfo = true;
        $apiMessage = 'Registro Localizado';

        $objHwtReporteCondicion = $resultHwtReporteCondicion->data;

        //ECRC: Calculando el Número y valor de las Reparaciones
        $objReparaciones = calcularReparaciones($numReporte,true);

        $totalReparaciones = $objReparaciones->totalReparaciones;

        $objHwtReporteCondicion[0]->{num_reparaciones}      = $totalReparaciones->num_reparaciones;
        $objHwtReporteCondicion[0]->{precio_total_estimado} = $totalReparaciones->precio_total_estimado;

        Emissary::addMessage('info-api'             , $apiMessage);
        Emissary::addData('hwtReporteCondicion' , $objHwtReporteCondicion);
        Emissary::addData('numRecords'          , $resultHwtReporteCondicion->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No se encontró el Registro en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
    }

    Dataworker::closeConnection();

    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
} // datosReporteCondicion

function listaReporteCondicion(){
    Logger::enable(true,'apiCondicionUnidad::listaReporteCondicion');

    Emissary::prepareEnvelope();

    $filtroOpcionSeccion = Receiver::getApiParameter('cbxOpcionSeccion');

    Dataworker::openConnection();
    $SqlConsulta = "SELECT * FROM hwt_reporte_condicion ";

    if($filtroOpcionSeccion){
        if($filtroOpcionSeccion !== 'ALL'){
            $SqlConsulta = $SqlConsulta
                . " AND cod_seccion = '$filtroOpcionSeccion'";
        }
    }

    Logger::write($SqlConsulta);

    $resultHwtReporteCondicion = Dataworker::executeQuery($SqlConsulta);

    if($resultHwtReporteCondicion->numRecords > 0) {
        $availableInfo = true;
        $apiMessage = 'Registros Localizados';
        Emissary::addMessage('info-api'            , $apiMessage);
        Emissary::addData('hwtReporteCondicion' , $resultHwtReporteCondicion->data);
        Emissary::addData('numRecords'          , $resultHwtReporteCondicion->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No hay Registros en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
    }

    Dataworker::closeConnection();
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
} // listaReporteCondicion

function listaReporteCondicionLinea($pInternalProcedure){
    Logger::enable(true,'apiCondicionUnidad::generaSecciones');

    Emissary::prepareEnvelope();

    $filtroOpcionSeccion = Receiver::getApiParameter('cbxOpcionSeccion');
    $numReporte          = Receiver::getApiParameter('tfNumReporte');

    Dataworker::openConnection();
    $SqlConsulta = "SELECT * FROM hwt_reporte_condicion_linea "
                 . "WHERE num_reporte = " . $numReporte;

    if($filtroOpcionSeccion){
        if($filtroOpcionSeccion !== 'ALL'){
            $SqlConsulta = $SqlConsulta
                . " AND cod_seccion = '$filtroOpcionSeccion'";
        }
    }

    Logger::write($SqlConsulta);

    $resultHwtReporteCondicionLinea = Dataworker::executeQuery($SqlConsulta);

    if($resultHwtReporteCondicionLinea->numRecords > 0) {
        $availableInfo = true;
        $apiMessage = 'Registros Localizados';
        Emissary::addMessage('info-api'                  , $apiMessage);

        Emissary::addData('hwtReporteCondicionLinea' , $resultHwtReporteCondicionLinea->data);
        Emissary::addData('numRecords'               , $resultHwtReporteCondicionLinea->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No hay Registros en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
    }

    Dataworker::closeConnection();
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();

    if($pInternalProcedure === null){
        echo json_encode($objReturn);
    }
    else{
        return $objReturn;
    }

} // listaReporteCondicionLinea

function generaSecciones(){
    // ECRC: Cuando se generan las Secciones, se genera inicialmente el Encabezado del Reporte y se le asigna un Número
    Logger::enable(true,'generaSecciones');

    Emissary::prepareEnvelope();
    Dataworker::openConnection();

    // ECRC: Validando la existencia del Reporte para el VIN
    $objSqlReporteCondicion = (object) [
        vin => ' = "'.Receiver::getApiParameter('tfVin').'"'
        ];
    $recordHwtReporteCondicion = Dataworker::findFirst('hwt_reporte_condicion',$objSqlReporteCondicion);

    if($recordHwtReporteCondicion->vin !== null){
        $availableInfo = false;
        $apiMessage = 'El VIN ' . $recordHwtReporteCondicion->vin
                    . ' ya tiene el Reporte de Condicion generado '
                    . $recordHwtReporteCondicion->num_reporte;
        Emissary::addMessage('error-api' , $apiMessage);
    }
    else{
        $dtFechaActual = date('Y-m-d');
        Logger::write('Fecha Actiual: ' .$dtFechaActual);

        // ECRC: Usando el Secuenciador de Cliente y Valores Iniciales del Registro
        Receiver::getApiListParameters();

        $numReporteCondicion = Dataworker::getNextSequence('seq_rep_condicion');
        Receiver::setApiParameterValue('tfNumReporte'   ,$numReporteCondicion);
        Receiver::setApiParameterValue('dfFechaReporte' ,$dtFechaActual);
        Receiver::setApiParameterValue('tfNumReparaciones',0);
        Receiver::setApiParameterValue('tfPrecioTotalEstimado',0);

        $objCamposRegistro = Dataworker::setFieldsTable('hwt_reporte_condicion');

        Logger::write('Antes de grabar el Encabezado de Condcion');
        Logger::write(json_encode($objCamposRegistro));
        $sqlEjecutado      = Dataworker::updateRecord($objCamposRegistro);

        // ECRC: Generando las Secciones de Condición de la Unidad
        $objectCaracteristicas = conjuntoParametros('rep_condicion_caracteristica');
        $numSequencia = 10;
        foreach($objectCaracteristicas as $indice=>$caracteristica){
            Logger::write('caractersitica ' . $caracteristica->descripcion);

            $objHwtReporteCondicionLinea = (object) [
                num_reporte              => $numReporteCondicion,
                num_sequencia            => $numSequencia,
                cod_seccion              => substr($caracteristica->codigo,0,2),
                cod_caracteristica       => $caracteristica->codigo,
                desc_caracteristica      => utf8_decode($caracteristica->descripcion),
                estado                   => $caracteristica->valor,
                observaciones            => '',
                fotografia               => '',
                valor_referencia         => '',
                precio_unitario_estimado => '0'
            ];

            $objCamposReporteCondicionLinea = Dataworker::setFieldsTable(
                                                                          'hwt_reporte_condicion_linea',
                                                                          $objHwtReporteCondicionLinea);
            $sqlEjecutado      = Dataworker::updateRecord($objCamposReporteCondicionLinea);
            $numSequencia = $numSequencia + 10;
        }

        Dataworker::closeConnection();

        $objHwtReporteCondicion = (object)[
            num_reporte   => $numReporteCondicion,
            fecha_reporte => $dtFechaActual
        ];

        $availableInfo = true;
        $apiMessage = 'Registro grabado correctamente';
        Emissary::addMessage('info-api' , $apiMessage);
        Emissary::addMessage('sql-query' , $sqlEjecutado);
        Emissary::addData('hwtReporteCondicion' , $objHwtReporteCondicion);
    }

    Emissary::success($availableInfo);
    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
} // generaSecciones

function datosOpcionesCondicion(){
    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    $objectOpcionesEstadoCaracteristica  = listaParametro('combos_rep_condicion','estado_caracteristica');
    $objectOpcionesSecciones             = conjuntoParametros('rep_condicion_seccion');

    $arrayOpcionesSecciones = (array) $objectOpcionesSecciones;
    $objValue = (object) [
        codigo      => 'ALL',
        descripcion => 'TODAS LAS SECCIONES',
        valor       => 'TODAS'
    ];

    array_push($arrayOpcionesSecciones,$objValue);

    // ECRC: Reoridenando el Arreglo cuando se adicionan nuevos Objetos
    $arrayOpcionesSecciones  = array_values($arrayOpcionesSecciones);
    $objectOpcionesSecciones = (object) $arrayOpcionesSecciones;

    Dataworker::closeConnection();

    $availableInfo = true;
    $apiMessage = 'Información para Opciones de Formulario';
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('opcionesEstadoCaracteristica' , $objectOpcionesEstadoCaracteristica);
    Emissary::addData('opcionesLineaEstado'          , $objectOpcionesEstadoCaracteristica);
    Emissary::addData('opcionesOpcionSeccion'        , $objectOpcionesSecciones);

    Emissary::success($availableInfo);
    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
} // datosOpcionesCondicion

/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);
