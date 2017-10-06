<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Dataworker.php');
require_once('../recurso/clase/Logger.php');
require_once('../recurso/clase/Mnemea.php');
require_once('../recurso/clase/Reporter.php');
require_once('apiConfigurador.php');
require_once('apiCatalogoGeneral.php');

function formatoPropuestaAdquisicion()
{
    Logger::enable(true, 'imprimiPropuestaAdquisicion');

    $numPropuesta = Receiver::getApiParameter('numPropuesta');

    Emissary::prepareEnvelope();
    Dataworker::openConnection();

    $archivoGenerado = Reporter::openFile('reportePropuestaAdquisicion' . $numPropuesta);
    $nombreReporte = "Propuesta de Adquisición No. " . $numPropuesta;
    Reporter::setMaxColumn('L');
    Reporter::prepareHeader(utf8_encode($nombreReporte));
    Reporter::setCurrenRow(7);

    // ECRC: Localizando la Propuesta de Adquisición
    $objCondicion = new \stdClass();
    $objCondicion->num_propuesta = Dataworker::equalToValue($numPropuesta);
    $hwtPropuestaAdquisicion = Dataworker::findFirst('hwt_propuesta_adquisicion', $objCondicion);

    // ECRC: Desplegando la información de la Propuesta
    $fgColor = 'FFFFFF';
    $bgColor = '054F7D';
    $valorSeparador = 'INFORMACION DE LA PROPUESTA';
    Reporter::printVerticalSeparator($valorSeparador, $fgColor, $bgColor);

    $fgColorTitulo = '054F7D';
    $bgColorTitulo = 'ebf0f4';
    $fgColorTexto = '000000';
    $bgColorText = 'FFFFFF';

    $titulo = 'SOLICITANTE';
    Reporter::printVerticalSeparator($titulo, $fgColorTitulo, $bgColorTitulo);

    $valor = $hwtPropuestaAdquisicion->solicitante . ' '
        . '(' . $hwtPropuestaAdquisicion->solicitante_email . ')'
        . ' con Fecha del ' . $hwtPropuestaAdquisicion->fecha_propuesta;
    Reporter::printVerticalSeparator($valor, $fgColorTexto, $bgColorText, false);

    Reporter::increaseCurrentRow();
    $titulo = 'EMPRESA (Información de la Compañìa que oferta las Unidades)';
    Reporter::printVerticalSeparator($titulo, $fgColorTitulo, $bgColorTitulo);

    $valor = $hwtPropuestaAdquisicion->razon_social . ' ';

    if (intval($hwtPropuestaAdquisicion->codigo_cliente) !== 0) {
        $valor = $valor . ' (Cliente ' . $hwtPropuestaAdquisicion->codigo_cliente . ')';
    }

    Reporter::printVerticalSeparator($valor, $fgColorTexto, $bgColorText, false);

    Reporter::increaseCurrentRow();
    $titulo = 'CONTACTO (Persona para recabar información de las Unidades)';
    Reporter::printVerticalSeparator($titulo, $fgColorTitulo, $bgColorTitulo);

    $valor = $hwtPropuestaAdquisicion->contacto_nombre . ' '
        . ' (' . $hwtPropuestaAdquisicion->contacto_email . ')';

    if ($hwtPropuestaAdquisicion->contacto_cargo !== '') {
        $valor .= ' Cargo de ' . $hwtPropuestaAdquisicion->contacto_cargo;
    }

    if ($hwtPropuestaAdquisicion->contacto_telefono !== '') {
        $valor .= ' Tel. ' . $hwtPropuestaAdquisicion->contacto_telefono;
    }

    if ($hwtPropuestaAdquisicion->contacto_movil !== '') {
        $valor .= ' Cel. ' . $hwtPropuestaAdquisicion->contacto_movil;
    }

    Reporter::printVerticalSeparator($valor, $fgColorTexto, $bgColorText, false);


    Reporter::increaseCurrentRow();
    $titulo = 'PROSPECTO (Persona a la que se dirigirá la Propuesta)';
    Reporter::printVerticalSeparator($titulo, $fgColorTitulo, $bgColorTitulo);

    $valor = $hwtPropuestaAdquisicion->prospecto . ' '
        . ' (' . $hwtPropuestaAdquisicion->prospecto_email . ')';

    if ($hwtPropuestaAdquisicion->prospecto_cargo !== '') {
        $valor .= ' Cargo de ' . $hwtPropuestaAdquisicion->prospecto_cargo;
    }

    if ($hwtPropuestaAdquisicion->prospecto_telefono !== '') {
        $valor .= ' Tel. ' . $hwtPropuestaAdquisicion->prospecto_telefono;
    }

    Reporter::printVerticalSeparator($valor, $fgColorTexto, $bgColorText, false);
    Reporter::increaseCurrentRow();

    $titulo = 'LISTADO DE UNIDADES';
    Reporter::printVerticalSeparator($titulo, $fgColor, $bgColor);
    Reporter::decreaseCurrentRow();

    // ECRC: Desplegando las Unidades de la Propuesta
    // ECRC: Preparando el Titulo de las Columnas
    $arrayTituloColumnas = array(
        "15:TIPO",
        "10:ESTADO",
        "22:VIN",
        "25:MARCA\rMODELO",
        "06:AÑO",
        "20:MOTOR",
        "25:TRANSMISION",
        "15:CAPACIDAD\rEJE TRASERO",
        "15:RELACION\rDIFERENCIAL",
        "15:DISTANCIA\rEJES",
        "15:KILOMETRAJE",
        "20:CABINA"
    );

    Reporter::increaseCurrentRow();

    $pObjConfig = new stdClass();
    $pObjConfig->fgColor = 'FFFFFF';
    $pObjConfig->bgColor = '5083A4';

    Reporter::prepareTitleColumns($arrayTituloColumnas, $pObjConfig);

    $objCondicion = new \stdClass();
    $objCondicion->num_propuesta = Dataworker::equalToValue($numPropuesta);
    $resultHwtPropuestaAdquisicionUnidad = Dataworker::getRecords('hwt_propuesta_adquisicion_unidad', $objCondicion);

    $arrayPropuestaUnidad = array();

    // ECRC: Armando el Objeto para desplegar en el Reporte
    $numRegistros = 0;
    foreach ($resultHwtPropuestaAdquisicionUnidad->data as $recordPropuestaUnidad) {

        $estadoUnidadDescripcion =
            obtieneValorOpcion(
                'combos_propuesta',
                'estado_unidad',
                $recordPropuestaUnidad->estado_unidad);

        $objPropuestaUnidad = new \stdClass();
        $objPropuestaUnidad->tipo = $recordPropuestaUnidad->tipo_unidad;
        $objPropuestaUnidad->estado = $estadoUnidadDescripcion;
        $objPropuestaUnidad->vin = $recordPropuestaUnidad->vin;
        $objPropuestaUnidad->marca_modelo = $recordPropuestaUnidad->marca . ' ' . $recordPropuestaUnidad->modelo;
        $objPropuestaUnidad->ann_unidad = $recordPropuestaUnidad->ann_unidad;
        $objPropuestaUnidad->motor = $recordPropuestaUnidad->motor;
        $objPropuestaUnidad->transmision = $recordPropuestaUnidad->marca_transmision . ' ' . $recordPropuestaUnidad->tipo_transmision;
        $objPropuestaUnidad->num_capacidad_eje_trasero = $recordPropuestaUnidad->capacidad_eje_trasero;
        $objPropuestaUnidad->num_relacion_diferencial = $recordPropuestaUnidad->relacion_diferencial;
        $objPropuestaUnidad->num_distancia_ejes = $recordPropuestaUnidad->distancia_ejes;
        $objPropuestaUnidad->num_kilometraje = $recordPropuestaUnidad->kilometraje;
        $objPropuestaUnidad->cabina = $recordPropuestaUnidad->cabina;

        array_push($arrayPropuestaUnidad, $objPropuestaUnidad);
        $numRegistros++;
    }
    $numRegistros++;

    Reporter::increaseCurrentRow();
    Reporter::writeContent($arrayPropuestaUnidad);

    // ECRC: Agregando las Notas de la Propuesta
    Reporter::increaseCurrentRow();
    $valorSeparador = 'NOTAS';
    Reporter::printVerticalSeparator($valorSeparador, $fgColor, $bgColor);
    $fgColorStd = '000000';
    $bgColorStd = 'FFFFFF';

    $arrayAnotaciones = array();
    $valorSeparador = 'La Fecha de Entrega de Nuevos es la establicida en el Documento.';
    array_push($arrayAnotaciones, $valorSeparador);
    $valorSeparador = 'La Fecha de Entrega de Usados es de 30 dias posteriores a Nuevos.';
    array_push($arrayAnotaciones, $valorSeparador);
    $valorSeparador = 'Se incluyen todas las Unidades involucradas.';
    array_push($arrayAnotaciones, $valorSeparador);

    foreach ($arrayAnotaciones as $anotacion) {
        Reporter::printVerticalSeparator($anotacion, $fgColorStd, $bgColorStd, false);
    }

    // ECRC: Agregando las Observaciones
    if ($hwtPropuestaAdquisicion->observaciones !== '') {
        Reporter::increaseCurrentRow();
        $valorSeparador = 'OBSERVACIONES';
        Reporter::printVerticalSeparator($valorSeparador, $fgColor, $bgColor);
        Reporter::printVerticalSeparator($hwtPropuestaAdquisicion->observaciones, $fgColorStd, $bgColorStd, false);
    }

    // ECRC: Cerrando el Archivo para su Presentación
    $objConfig = new stdClass();
    $objConfig->pageOrientation = 'horizontal';

    $archivoServidor = Reporter::saveFile($objConfig);

    $objArchivoGenerado = new \stdClass();
    $objArchivoGenerado->nombre = $archivoGenerado;
    $objArchivoGenerado->archivoServidor = $archivoServidor;

    $availableInfo = true;
    $apiMessage = 'Archivo generado: ' . $archivoGenerado;
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('archivoGenerado', $objArchivoGenerado);
    Emissary::success($availableInfo);

    Dataworker::closeConnection();
    Emissary::deliverEnvelope();

}

function totalizaUnidades($pNumPropuesta)
{
    Logger::enable(true, 'totalizaUnidades');
    Logger::write('Inicia el Proceso');

    $objCondicion = new \stdClass();
    $objCondicion->num_propuesta = Dataworker::equalToValue($pNumPropuesta);

    $objTotalUnidades = new \stdClass();
    $objTotalUnidades->nuevas = 0;
    $objTotalUnidades->usadas = 0;

    Logger::write('Extrayendo datos');
    $hwtPropuestaAdquisicionUnidad = Dataworker::getRecords('hwt_propuesta_adquisicion_unidad', $objCondicion);
    Logger::write(json_encode($hwtPropuestaAdquisicionUnidad));
    Logger::write('Fin de Extracción de Datos');

    foreach ($hwtPropuestaAdquisicionUnidad->data as $recordHwtPropuestaAdquisicionUnidad) {
        Logger::write($recordHwtPropuestaAdquisicionUnidad->estado_unidad);
        if (intval($recordHwtPropuestaAdquisicionUnidad->estado_unidad) === 1) { // Unidad Usada
            $objTotalUnidades->nuevas++;
        }

        if (intval($recordHwtPropuestaAdquisicionUnidad->estado_unidad) === 2) { // Unidad Nueva
            $objTotalUnidades->usadas++;
        }
    }

    //ECRC: Grabando el Total de Unidades en la Propuesta
    Receiver::resetApiParameters();
    $hwtPropuestaAdquisicion = new \stdClass();
    $hwtPropuestaAdquisicion->num_propuesta = $pNumPropuesta;
    $hwtPropuestaAdquisicion->cant_unidades_nuevas = $objTotalUnidades->nuevas;
    $hwtPropuestaAdquisicion->cant_unidades_usadas = $objTotalUnidades->usadas;

    $objCamposRegistro = Dataworker::setFieldsTable('hwt_propuesta_adquisicion', $hwtPropuestaAdquisicion);
    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    return $objTotalUnidades;
}

function eliminaPropuestaAdquisicionUnidad()
{
    Dataworker::openConnection();
    Emissary::prepareEnvelope();

    $numPropuesta = Receiver::getApiParameter('numPropuesta');
    $rowidPropuestaAdquisicionUnidad = Receiver::getApiParameter('rowidRegistro');

    $objCondicion = new \stdClass();
    $objCondicion->tableName = 'hwt_propuesta_adquisicion_unidad';
    $objCondicion->keyField = 'rowid';
    $objCondicion->keyValue = $rowidPropuestaAdquisicionUnidad;
    $objDeletedRecord = Dataworker::deleteRecord($objCondicion);

    $objTotales = totalizaUnidades($numPropuesta);

    Emissary::prepareEnvelope();
    $availableInfo = true;
    $apiMessage = 'La Unidad ha sido eliminada de la Propuesta de Adquisición.';

    Emissary::success($availableInfo);
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('totalUnidades', $objTotales);
    Emissary::addData('objDeletedRecord', $objDeletedRecord);

    Dataworker::closeConnection();
    Emissary::deliverEnvelope();
}

function datosPropuestaAdquisicionUnidad()
{
    Logger::enable(true, 'datosPropuestaAdquisicionUnidad');

    $numPropuesta = Receiver::getApiParameter('numPropuesta');
    $vin = Receiver::getApiParameter('vin');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();
    $objConstraint = new stdClass();
    $objConstraint->num_propuesta = Dataworker::equalToValue($numPropuesta);
    $objConstraint->vin = Dataworker::equalToString($vin);

    $hwtPropuestaAdquisicionUnidad = Dataworker::findFirst('hwt_propuesta_adquisicion_unidad', $objConstraint);

    if (intval($hwtPropuestaAdquisicionUnidad->activeRecord) === 1) {
        $availableInfo = true;
        $apiMessage = 'Registro Localizado';
        Emissary::addMessage('info-api', $apiMessage);
        Emissary::addData('hwtPropuestaAdquisicionUnidad', $hwtPropuestaAdquisicionUnidad);
    } else {
        $availableInfo = false;
        $apiMessage = 'No se encontró el Registro en la Base';
        Emissary::addMessage('info-api', $apiMessage);
    }

    Dataworker::closeConnection();
    Emissary::success($availableInfo);
    Emissary::deliverEnvelope();
}

function listaPropuestaAdquisicionUnidad($pEjecucion = null)
{
    Logger::enable(true, 'listaPropuestaAdquisicionUnidad');
    Mnemea::wakeUp();

    Emissary::prepareEnvelope();
    Dataworker::openConnection();
    $numPropuesta = Receiver::getApiParameter('numPropuesta');

    $objCondicion = new \stdClass();
    $objCondicion->num_propuesta = Dataworker::equalToValue($numPropuesta);

    $hwtPropuestaAdquisicionUnidad = Dataworker::getRecords('hwt_propuesta_adquisicion_unidad', $objCondicion);
    Logger::write('$hwtPropuestaAdquisicionUnidad');
    Logger::write(json_encode($hwtPropuestaAdquisicionUnidad));

    $objOpcionesPropuestaAdquisicion = listaParametro(
        'combos_propuesta',
        'situacion_propuesta');

    Logger::write(json_encode($objOpcionesPropuestaAdquisicion));

    // ECRC: Extrayendo las Opciones para desplegar en el Registro
    /*
    $objConfig = new \stdClass();
    $objConfig->decode = true;
    $objOpcionesEstadoUnidad= listaParametro(
        'combos_propuesta',
        'estado_unidad',
        $objConfig);
    */

    if ($hwtPropuestaAdquisicionUnidad->numRecords > 0) {
        foreach ($hwtPropuestaAdquisicionUnidad->data as $recordPropuestaAdquisicionUnidad) {

            $recordPropuestaAdquisicionUnidad->estado_unidad_descripcion =
                obtieneValorOpcion(
                    'combos_propuesta',
                    'estado_unidad',
                    $recordPropuestaAdquisicionUnidad->estado_unidad);

            /*
            $recordPropuestaAdquisicionUnidad->estado_unidad_descripcion = '';
            foreach ($objOpcionesEstadoUnidad as $opcionEstadoUnidad){
                if($recordPropuestaAdquisicionUnidad->estado_unidad_descripcion !== ''){
                    break;
                }

                if($opcionEstadoUnidad->codigo === $recordPropuestaAdquisicionUnidad->estado_unidad){
                    $recordPropuestaAdquisicionUnidad->estado_unidad_descripcion = $opcionEstadoUnidad->descripcion;
                }
            }
            */

            // ECRC: Estableciendo la Descripción de la Situación de la Oportunidad de Venta
            $recordPropuestaAdquisicionUnidad->situacion_propuesta_descripcion = '';
        }
        $availableInfo = true;
    } else {
        $availableInfo = false;
    }

    Dataworker::closeConnection();
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();

    $apiMessage = 'Registros Localizados';
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('hwtPropuestaAdquisicionUnidad', $hwtPropuestaAdquisicionUnidad->data);
    Emissary::addData('numRecords', $hwtPropuestaAdquisicionUnidad->numRecords);

    if ($pEjecucion !== null and $pEjecucion === 'interna') {
        return $objReturn;
    } else {
        Emissary::deliverEnvelope();
    }
}

function grabaPropuestaAdquisicionUnidad()
{
    Logger::enable(true, 'grabaPropuestaAdquisicionUnidad');

    Emissary::prepareEnvelope();
    Dataworker::openConnection();

    $numPropuesta = Receiver::getApiParameter('tfUnidadNumPropuesta');
    $fechaEntrega = Receiver::getApiParameter('dfUnidadFechaEntrega');

    Receiver::setApiParameterValue('tfNumPropuesta', $numPropuesta);
    Receiver::setApiParameterValue('dfFechaEntrega', $fechaEntrega);

    if (intval($numPropuesta) > 0) {
        //ECRC: Es un registro que se va a actualizar
    } else {
        //ECRC: Es un registro que se va a crear. Se establecen los Valores por Defecto
    }

    $objCamposRegistro = Dataworker::setFieldsTable('hwt_propuesta_adquisicion_unidad');
    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    $objTotales = totalizaUnidades($numPropuesta);

    Dataworker::closeConnection();

    Logger::write('Actualizando el Registro');

    $registroActualizado = true;
    $apiMessage = 'Unidad registrada para la Propuesta de Adquisición.';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('totalUnidades', $objTotales);

    Emissary::deliverEnvelope();
}

function datosPropuestaAdquisicion()
{
    Logger::enable(true, 'datosPropuestaAdquisicon');

    $numPropuesta = Receiver::getApiParameter('numPropuesta');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();
    $objConstraint = new stdClass();
    $objConstraint->num_propuesta = Dataworker::equalToValue($numPropuesta);

    $hwtPropuestaAdquisicion = Dataworker::findFirst('hwt_propuesta_adquisicion', $objConstraint);

    if (intval($hwtPropuestaAdquisicion->activeRecord) === 1) {
        $availableInfo = true;
        $apiMessage = 'Registro Localizado';
        Emissary::addMessage('info-api', $apiMessage);
        Emissary::addData('hwtPropuestaAdquisicion', $hwtPropuestaAdquisicion);
    } else {
        $availableInfo = false;
        $apiMessage = 'No se encontró el Registro en la Base';
        Emissary::addMessage('info-api', $apiMessage);
    }

    Dataworker::closeConnection();
    Emissary::success($availableInfo);
    Emissary::deliverEnvelope();
}

function listaPropuestaAdquisicion($pEjecucion = null)
{
    Logger::enable(true, 'listaPropuestaAdquisicion');
    Mnemea::wakeUp();

    Emissary::prepareEnvelope();
    Dataworker::openConnection();
    $filtroEstado = substr(Receiver::getApiParameter('filtroEstado'), 0, 1);

    if ($filtroEstado === 'A') {
        $objCondicion = null;
    } else {
        $objCondicion = new \stdClass();
        //$objCondicion->situacion_oportunidad = Dataworker::equalToString($filtroEstado);
    }

    $hwtPropuestaAdquisicion = Dataworker::getRecords('hwt_propuesta_adquisicion', $objCondicion);


    $objOpcionesPropuestaAdquisicion = listaParametro(
        'combos_propuesta',
        'situacion_propuesta');

    Logger::write(json_encode($objOpcionesPropuestaAdquisicion));

    if ($hwtPropuestaAdquisicion->numRecords > 0) {
        foreach ($hwtPropuestaAdquisicion->data as $recordPropuestaAdquisicion) {

            // ECRC: Estableciendo la Descripción de la Situación de la Oportunidad de Venta
            $recordPropuestaAdquisicion->situacion_propuesta_descripcion = '';
            foreach ($objOpcionesPropuestaAdquisicion as $opcionSituacionPropuesta) {
                if ($recordPropuestaAdquisicion->situacion_propuesta_descripcion !== '') {
                    break;
                }

                $codigoSituacion = substr($opcionSituacionPropuesta->codigo, 0, 1);
                $descripcionSituacion = $opcionSituacionPropuesta->descripcion;

                if ($codigoSituacion === $recordPropuestaAdquisicion->situacion_propuesta) {
                    $recordPropuestaAdquisicion->situacion_propuesta_descripcion = $descripcionSituacion;
                }
            }
        }
        $availableInfo = true;
    } else {
        $availableInfo = false;
    }

    Dataworker::closeConnection();
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();

    $apiMessage = 'Registros Localizados';
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('hwtPropuestaAdquisicion', $hwtPropuestaAdquisicion->data);
    Emissary::addData('numRecords', $hwtPropuestaAdquisicion->numRecords);

    if ($pEjecucion !== null and $pEjecucion === 'interna') {
        return $objReturn;
    } else {
        echo json_encode($objReturn);
    }
}

function grabaPropuestaAdquisicion()
{
    Logger::enable(true, 'grabaPropuestaAdquisicion');

    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    if (intval(Receiver::getApiParameter('tfNumPropuesta')) > 0) {
        //ECRC: Es un registro que se va a actualizar
    } else {
        //ECRC: Es un registro que se va a crear. Se establecen los Valores por Defecto
        $numPropuesta = Dataworker::getNextSequence('seq_propuesta_adquisicion');
        Logger::write('Numero generado ' . $numPropuesta);

        Receiver::setApiParameterValue('tfNumPropuesta', $numPropuesta);
        Receiver::setApiParameterValue('tfSituacionPropuesta', '1');
    }

    $objCamposRegistro = Dataworker::setFieldsTable('hwt_propuesta_adquisicion');
    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    Logger::write('Va por aqui');

    $registroActualizado = true;
    $apiMessage = 'Registro actualizado en la Base de Datos';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addMessage('sql-ejecutado', $sqlEjecutado);
    //Emissary::addData('camposRegistro', $objCamposRegistro);
    Dataworker::closeConnection();

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function datosOpcionesPropuesta()
{
    Logger::enable(true, 'datosOpciones');

    Dataworker::openConnection();

    $objConfig = new \stdClass();
    $objConfig->decode = true;

    $objOpcionesPropuestaAdquisicion = listaParametro(
        'combos_propuesta',
        'situacion_propuesta',
        $objConfig);

    $objSituacion = new \stdClass();
    $objSituacion->codigo = '0';
    $objSituacion->descripcion = 'TODAS LAS SITUACIONES';

    $arrayOpcionesSituacion = (array)$objOpcionesPropuestaAdquisicion;
    array_push($arrayOpcionesSituacion, $objSituacion);

    $arrayOpcionesSituacion = array_values($arrayOpcionesSituacion); /* Renumeración de Objetos */
    $objOpcionesPropuestaAdquisicion = null;
    $objOpcionesPropuestaAdquisicion = (object)$arrayOpcionesSituacion;


    $objOpcionesEstadoUnidad = listaParametro(
        'combos_propuesta',
        'estado_unidad',
        $objConfig);

    // ECRC: Cerrando la Conexión
    Dataworker::closeConnection();

    $availableInfo = true;
    $apiMessage = 'Información para Opciones de Formulario';
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('opcionesPropuestaAdquisicion', $objOpcionesPropuestaAdquisicion);
    Emissary::addData('opcionesSituacionPropuesta', $objOpcionesPropuestaAdquisicion);
    Emissary::addData('opcionesEstadoUnidad', $objOpcionesEstadoUnidad);

    Emissary::success($availableInfo);

    Emissary::deliverEnvelope();

}

/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);
