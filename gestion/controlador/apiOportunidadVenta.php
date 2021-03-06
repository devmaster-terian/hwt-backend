<?php
//////////////////////////////////////////////////
/// Used Trucks Management System
/// Version 0.7
/// Terian Software Team
//////////////////////////////////////////////////
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Dataworker.php');
require_once('../recurso/clase/Logger.php');
require_once('../recurso/clase/Mnemea.php');
require_once('../recurso/clase/Reporter.php');
require_once('apiCatalogoGeneral.php');
require_once('apiConfigurador.php');

function reporteOportunidadVenta()
{
    Logger::enable(true, 'reporteOportunidadVenta');

    //ECRC: Extrayendo los registros que se est�n presentando en Pantalla
    $objDataset = listaOportunidadVenta('interna');

    $archivoGenerado = Reporter::openFile('reporteOportunidadVenta');
    $nombreReporte = "Oportunidades de Venta";
    Reporter::prepareHeader($nombreReporte);

    //ECRC: Preparando el Titulo de las Columnas
    $arrayTituloColumnas = array(
        "16:NUMERO\rOPORTUNIDAD",
        "10:SEMANA",
        "12:FECHA\rVISITA",
        "12:ESTADO",
        "20:GERENTE\rREGIONAL",
        "40:CLIENTE\rRAZON SOCIAL",
        "20:ESTADO",
        "35:CIUDAD",
        "19:TIPO EMPRESA",
        "35:CONTACTO\rNOMBRE",
        "35:CANTACTO\rCARGO",
        "20:CONTACTO\rTELEFONO",
        "30:CONTACTO\rCORREO",
        "15:CANTIDAD\rSOLICITADA",
        "15:CANTIDAD\rATENDIDA",
        "15:CANTIDAD\rSALDO",
        "20:MARCA",
        "20:MODELO",
        "25:CONSECIONARIO",
        "25:VENDEDOR",
    );
    Reporter::prepareTitleColumns($arrayTituloColumnas);

    $arrayReporteOportunidadVenta = array();
    foreach ($objDataset->hwtOportunidadVenta as $recordOportunidadVenta) {
        $objOportunidadVenta = new \stdClass();
        $objOportunidadVenta->num_oportunidad = $recordOportunidadVenta->num_oportunidad;
        $objOportunidadVenta->semana = $recordOportunidadVenta->visita_semana;
        $objOportunidadVenta->fecha = $recordOportunidadVenta->visita_fecha;
        $objOportunidadVenta->situacion_oportunidad = $recordOportunidadVenta->situacion_oportunidad;
        $objOportunidadVenta->gerente_regional_nombre = $recordOportunidadVenta->gerente_regional_nombre;
        $objOportunidadVenta->razon_social = $recordOportunidadVenta->razon_social;
        $objOportunidadVenta->solicitud_estado = $recordOportunidadVenta->solicitud_estado;
        $objOportunidadVenta->solicitud_ciudad = $recordOportunidadVenta->solicitud_municipio;
        $objOportunidadVenta->tipo_empresa = $recordOportunidadVenta->tipo_empresa;
        $objOportunidadVenta->contacto_nombre = $recordOportunidadVenta->contacto_nombre;
        $objOportunidadVenta->contacto_cargo = $recordOportunidadVenta->contacto_cargo;
        $objOportunidadVenta->contacto_telefono = $recordOportunidadVenta->contacto_telefono;
        $objOportunidadVenta->contacto_email = $recordOportunidadVenta->contacto_email;
        $objOportunidadVenta->cantidad_solicitada = $recordOportunidadVenta->cantidad_solicitada;
        $objOportunidadVenta->cantidad_atendida = $recordOportunidadVenta->cantidad_atendida;
        $objOportunidadVenta->cantidad_saldo = $recordOportunidadVenta->cantidad_saldo;
        $objOportunidadVenta->marca = $recordOportunidadVenta->marca;
        $objOportunidadVenta->modelo = $recordOportunidadVenta->modelo;
        $objOportunidadVenta->consecionario_descripcion = $recordOportunidadVenta->consecionario_descripcion;
        $objOportunidadVenta->vendedor_nombre = $recordOportunidadVenta->vendedor_nombre;

        array_push($arrayReporteOportunidadVenta, $objOportunidadVenta);
    }

    Reporter::writeContent($arrayReporteOportunidadVenta);
    Reporter::saveFile();

    $objArchivoGenerado = new \stdClass();
    $objArchivoGenerado->nombre = $archivoGenerado;

    Emissary::prepareEnvelope();

    $availableInfo = true;
    $apiMessage = 'Archivo generado: ' . $archivoGenerado;
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('archivoGenerado', $objArchivoGenerado);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();

    echo json_encode($objReturn);
}

function listaOportunidadVenta($pEjecucion = null)
{
    Logger::enable(true, 'listaOportunidadVenta');
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

    ///////////////////////////////////////////////////
    /// ECRC: Preparando la Consulta para Busqueda. ///
    ///////////////////////////////////////////////////
    if ($filtroEstado === 'B') {
        $buscaNumOportunidad = Receiver::getApiParameter('tfBuscaNumOportunidad');
        $buscaCodCliente = Receiver::getApiParameter('tfBuscaCodCliente');
        $buscaSituacionInicial = Receiver::getApiParameter('cbxBuscaSituacionInicial');
        $buscaSituacionFinal = Receiver::getApiParameter('cbxBuscaSituacionFinal');
        $buscaFechaInicial = Receiver::getApiParameter('dtBuscaFechaInicial');
        $buscaFechaFinal = Receiver::getApiParameter('dtBuscaFechaFinal');
        $buscaMarca = Receiver::getApiParameter('cbxBuscaMarca');
        $buscaModelo = Receiver::getApiParameter('cbxBuscaModelo');
        $buscaConsecionario = Receiver::getApiParameter('cbxBuscaConsecionario');
        $buscaGerenteRegional = Receiver::getApiParameter('cbxBuscaGerenteRegional');
        $buscaVendedor = Receiver::getApiParameter('cbxBuscaVendedor');

        if (intval($buscaNumOportunidad) !== 0) {
            $objCondicion->num_oportunidad = Dataworker::equalToValue($buscaNumOportunidad);
        }

        if (intval($buscaCodCliente) !== 0) {
            $objCondicion->codigo_cliente = Dataworker::equalToValue($buscaCodCliente);
        }

        if ($buscaMarca !== 'MULTIMARCA') {
            $objCondicion->marca = Dataworker::equalToString($buscaMarca);
        }

        if ($buscaModelo !== 'CUALQUIER MODELO') {
            $objCondicion->modelo = Dataworker::equalToString($buscaModelo);
        }

        if ($buscaConsecionario !== 'todo') {
            $objCondicion->codigo_consecionario = Dataworker::equalToString($buscaConsecionario);
        }


        if ($buscaGerenteRegional !== 'todo') {
            $objCondicion->codigo_gerente_regional = Dataworker::equalToString($buscaGerenteRegional);
        }

        if ($buscaVendedor !== 'todo') {
            $objCondicion->codigo_vendedor = Dataworker::equalToString($buscaVendedor);
        }


        // ECRC: Filtros de Rango
        $objCondicion->situacion_oportunidad_range_ini = Dataworker::compare('greaterEqualThan', 'string', $buscaSituacionInicial);
        $objCondicion->situacion_oportunidad_range_end = Dataworker::compare('lessEqualThan', 'string', $buscaSituacionFinal);

        $objCondicion->visita_fecha_range_ini = Dataworker::compare('greaterEqualThan', 'date', $buscaFechaInicial);
        $objCondicion->visita_fecha_range_end = Dataworker::compare('lessEqualThan', 'date', $buscaFechaFinal);

    }

    $hwtOportunidadVenta = Dataworker::getRecords('hwt_oportunidad_venta', $objCondicion);

    $objectOpcionesSituacionPedido = listaParametro('combos_oportunidad', 'situacion_oportunidad');
    $objectOpcionesTipoSolicitante = listaParametro('combos_oportunidad', 'tipo_solicitante');

    Logger::write('$objectOpcionesSituacionPedido: ' . json_encode($objectOpcionesSituacionPedido));

    if ($hwtOportunidadVenta->numRecords > 0) {
        foreach ($hwtOportunidadVenta->data as $recordOportunidadVenta) {

            // ECRC: Estableciendo la Descripci�n de la Situaci�n de la Oportunidad de Venta
            $recordOportunidadVenta->situacion_oportunidad_descripcion = '';
            foreach ($objectOpcionesSituacionPedido as $opcionSituacionPedido) {
                if ($recordOportunidadVenta->situacion_oportunidad_descripcion !== '') {
                    break;
                }

                $codigoSituacion = substr($opcionSituacionPedido->codigo, 0, 1);
                $descripcionSituacion = $opcionSituacionPedido->descripcion;

                if ($codigoSituacion === $recordOportunidadVenta->situacion_oportunidad) {
                    $recordOportunidadVenta->situacion_oportunidad_descripcion = $descripcionSituacion;
                }
            }

            // ECRC: Estableciendo la Descripci�n del Tipo de Solicitante
            $recordOportunidadVenta->tipo_solicitante_descripcion = '';
            foreach ($objectOpcionesTipoSolicitante as $opcionTipoSolicitante) {
                if ($recordOportunidadVenta->tipo_solicitante_descripcion !== '') {
                    break;
                }

                $codigoTipo = substr($opcionTipoSolicitante->codigo, 0, 1);
                $descripcionTipo = $opcionTipoSolicitante->descripcion;

                if ($codigoTipo === $recordOportunidadVenta->tipo_solicitante) {
                    $recordOportunidadVenta->tipo_solicitante_descripcion = $descripcionTipo;
                }
            }

            // ECRC: Localizando al Cliente
            $recordOportunidadVenta->cliente_nombre = nombreCliente($recordOportunidadVenta->codigo_cliente);

            // ECRC: Localizando a los Usuarios de Gerente y Vendedor
            $recordOportunidadVenta->gerente_regional_nombre = nombreUsuario($recordOportunidadVenta->codigo_gerente_regional);
            $recordOportunidadVenta->vendedor_nombre = nombreUsuario($recordOportunidadVenta->codigo_vendedor);

            // ECRC: Localizando al Consecionario
            $recordOportunidadVenta->concesionario_descripcion = descripcionConsecionario($recordOportunidadVenta->codigo_consecionario);
        }

        $availableInfo = true;
        $apiMessage = 'Registros Localizados';
        Emissary::addMessage('info-api', $apiMessage);
        Emissary::addData('hwtOportunidadVenta', $hwtOportunidadVenta->data);
        Emissary::addData('numRecords', $hwtOportunidadVenta->numRecords);
    } else {
        $availableInfo = false;
        $apiMessage = 'No hay Registros en la Base';
        Emissary::addMessage('info-api', $apiMessage);
    }

    Dataworker::closeConnection();
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();

    if ($pEjecucion !== null and $pEjecucion === 'interna') {
        return $objReturn;
    } else {
        echo json_encode($objReturn);
    }
}

function grabaOportunidadVenta()
{
    Logger::enable(true, 'grabaOportunidadVenta');

    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    if (intval(Receiver::getApiParameter('tfNumOportunidad')) > 0) {
        //ECRC: Es un registro que se va a actualizar
    } else {
        //ECRC: Es un registro que se va a crear. Se establecen los Valores por Defecto
        $numOportunidad = Dataworker::getNextSequence('seq_oportunidad_venta');
        Logger::write('Numero generado ' . $numOportunidad);

        Receiver::setApiParameterValue('tfNumOportunidad', $numOportunidad);
        Receiver::setApiParameterValue('tfSituacionOportunidad', '1');
    }

    $objCamposRegistro = Dataworker::setFieldsTable('hwt_oportunidad_venta');
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

function datosOportunidadVenta()
{
    $numOportunidad = Receiver::getApiParameter('numOportunidadVenta');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();
    $SqlCliente = "SELECT * FROM hwt_oportunidad_venta WHERE num_oportunidad = '$numOportunidad'";
    $resultHwtOportunidadVenta = Dataworker::executeQuery($SqlCliente);

    if ($resultHwtOportunidadVenta->numRecords > 0) {
        $availableInfo = true;
        $apiMessage = 'Registro Localizado';
        Emissary::addMessage('info-api', $apiMessage);
        Emissary::addData('hwtOportunidadVenta', $resultHwtOportunidadVenta->data);
        Emissary::addData('numRecords', $resultHwtOportunidadVenta->numRecords);
    } else {
        $availableInfo = false;
        $apiMessage = 'No se encontr� el Registro en la Base';
        Emissary::addMessage('info-api', $apiMessage);
    }

    Dataworker::closeConnection();

    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function datosOpciones()
{
    Logger::enable(true, 'datosOpciones');

    Dataworker::openConnection();

    $objConfig = new \stdClass();
    $objConfig->decode = true;

    $objOpcionesSituacionOportunidad = listaParametro(
        'combos_oportunidad',
        'situacion_oportunidad',
        $objConfig);

    $objSituacion = new \stdClass();
    $objSituacion->codigo = '0';
    $objSituacion->descripcion = 'TODAS LAS SITUACIONES';

    $arrayOpcionesSituacion = (array)$objOpcionesSituacionOportunidad;
    array_push($arrayOpcionesSituacion, $objSituacion);

    $arrayOpcionesSituacion = array_values($arrayOpcionesSituacion); /* Renumeraci�n de Objetos */
    $objOpcionesSituacionOportunidad = null;
    $objOpcionesSituacionOportunidad = (object)$arrayOpcionesSituacion;


    // ECRC: Opciones de Solicitante
    $objOpcionesTipoSolicitante = listaParametro(
        'combos_oportunidad',
        'tipo_solicitante',
        $objConfig);

    // ECRC: Opciones de Marca
    $objOpcionesMarca = listaParametro(
        'combos_unidades',
        'marca');

    $objMarca = new \stdClass();
    $objMarca->codigo = '0';
    $objMarca->descripcion = 'MULTIMARCA';

    // ECRC: Opciones de Modelo
    $arrayOpcionesMarca = (array)$objOpcionesMarca;
    array_push($arrayOpcionesMarca, $objMarca);
    $objOpcionesMarca = (object)$arrayOpcionesMarca;

    $objOpcionesModelo = listaParametro(
        'combos_unidades',
        'modelo');

    $objModelo = new \stdClass();
    $objModelo->codigo = '0';
    $objModelo->descripcion = 'CUALQUIER MODELO';

    $arrayOpcionesModelo = (array)$objOpcionesModelo;
    array_push($arrayOpcionesModelo, $objModelo);
    $objOpcionesModelo = (object)$arrayOpcionesModelo;

    // ECRC: Cerrando la Conexi�n
    Dataworker::closeConnection();

    Logger::write('Antes de Publicar');
    Logger::write(json_encode($objOpcionesSituacionOportunidad));

    $listadoGerentesRegionales = listadoGerentesRegionales();
    Logger::write('listadoGerentesRegionales');
    Logger::write(json_encode($listadoGerentesRegionales));

    $availableInfo = true;
    $apiMessage = 'Informaci�n para Opciones de Formulario';
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('opcionesSituacionOportunidad', $objOpcionesSituacionOportunidad);
    Emissary::addData('opcionesTipoSolicitante', $objOpcionesTipoSolicitante);
    Emissary::addData('opcionesGerenteRegional', listadoGerentesRegionales());
    Emissary::addData('opcionesGerenteRegionalBusca', listadoGerentesRegionales(true));
    Emissary::addData('opcionesVendedor', listaVendedores());
    Emissary::addData('opcionesVendedorBusca', listaVendedores(true));
    Emissary::addData('opcionesConsecionario', listadoConsecionarios());
    Emissary::addData('opcionesConsecionarioBusca', listadoConsecionarios(true));
    Emissary::addData('opcionesMarca', $objOpcionesMarca);
    Emissary::addData('opcionesModelo', $objOpcionesModelo);
    Emissary::addData('opcionesPais', listaPais());

    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function datosLocalizacion()
{
    Logger::enable(true, 'datosCiudadEstado');

    $tipoDato = Receiver::getApiParameter('tipoDato');
    $pais = Receiver::getApiParameter('pais');
    $estado = Receiver::getApiParameter('estado');
    $municipio = Receiver::getApiParameter('municipio');

    $availableInfo = true;
    $apiMessage = 'Informaci�n para Opciones de Formulario';

    Emissary::prepareEnvelope();
    switch ($tipoDato) {
        case 'estado':
            $opcionesEstado = listaEstado($pais);
            Emissary::addData('opcionesEstado', $opcionesEstado);
            break;
        case 'municipio':
            $opcionesMunicipio = listaMunicipio($pais, $estado);
            Emissary::addData('opcionesMunicipio', $opcionesMunicipio);
            break;
        case 'ciudad':
            $opcionesCiudad = listaCiudad($pais, $estado, $municipio);
            Emissary::addData('opcionesCiudad', $opcionesCiudad);
            break;
    }

    Emissary::success($availableInfo);
    Emissary::addMessage('info-api', $apiMessage);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

/* ECRC: Bloque Principal de Ejecuci�n */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);


