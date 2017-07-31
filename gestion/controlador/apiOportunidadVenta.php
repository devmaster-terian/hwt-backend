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

function listaOportunidadVenta()
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
        $objCondicion->situacion_oportunidad = Dataworker::equalToString($filtroEstado);
    }

    ///////////////////////////////////////////////////
    /// ECRC: Preparando la Consulta para Busqueda. ///
    ///////////////////////////////////////////////////
    if ($filtroEstado === 'B') {
        $buscaCodCliente = Receiver::getApiParameter('tfBuscaCodCliente');
        $buscaRazonSocial = Receiver::getApiParameter('tfBuscaRazonSocial');
        $buscaSituacionInicial = Receiver::getApiParameter('cbxBuscaSituacionInicial');
        $buscaSituacionFinal = Receiver::getApiParameter('cbxBuscaSituacionFinal');
        $buscaFechaInicial = Receiver::getApiParameter('dtBuscaFechaInicial');
        $buscaFechaFinal = Receiver::getApiParameter('dtBuscaFechaFinal');
        $buscaMarca = Receiver::getApiParameter('cbxBuscaMarca');
        $buscaModelo = Receiver::getApiParameter('cbxBuscaModelo');
        $buscaConsecionario = Receiver::getApiParameter('cbxBuscaConsecionario');
        $buscaGerenteRegional = Receiver::getApiParameter('cbxBuscaGerenteRegional');
        $buscaVendedor = Receiver::getApiParameter('cbxBuscaVendedor');
    }

    $hwtOportunidadVenta = Dataworker::getRecords('hwt_oportunidad_venta', $objCondicion);

    $objectOpcionesSituacionPedido = listaParametro('combos_oportunidad', 'situacion_oportunidad');
    $objectOpcionesTipoSolicitante = listaParametro('combos_oportunidad', 'tipo_solicitante');

    Logger::write('$objectOpcionesSituacionPedido: ' . json_encode($objectOpcionesSituacionPedido));

    if ($hwtOportunidadVenta->numRecords > 0) {
        foreach ($hwtOportunidadVenta->data as $recordOportunidadVenta) {

            // ECRC: Estableciendo la Descripción de la Situación de la Oportunidad de Venta
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

            // ECRC: Estableciendo la Descripción del Tipo de Solicitante
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


            $recordOportunidadVenta->solicitud_pais_descripcion = obtieneNombrePais(
                $recordOportunidadVenta->solicitud_pais);

            $recordOportunidadVenta->solicitud_estado_descripcion = obtieneNombreEstado(
                $recordOportunidadVenta->solicitud_pais,
                $recordOportunidadVenta->solicitud_estado);

            $recordOportunidadVenta->solicitud_municipio_descripcion = obtieneNombreMunicipio(
                $recordOportunidadVenta->solicitud_pais,
                $recordOportunidadVenta->solicitud_estado,
                $recordOportunidadVenta->solicitud_municipio);
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
    echo json_encode($objReturn);

}

function grabaOportunidadVenta()
{
    Logger::enable(true, 'grabaOportunidadVenta');

    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    if (intval(Receiver::getApiParameter('tfNumPedido')) > 0) {
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

    $registroActualizado = true;
    $apiMessage = 'Registro actualizado en la Base de Datos';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addMessage('sql-ejecutado', $sqlEjecutado);
    Emissary::addData('camposRegistro', $objCamposRegistro);
    Dataworker::closeConnection();

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

    Logger::write(json_encode($objOpcionesSituacionOportunidad));
    $objSituacion = new \stdClass();
    $objSituacion->codigo = '0';
    $objSituacion->descripcion = 'TODAS LAS SITUACIONES';

    $arrayOpcionesSituacion = (array)$objOpcionesSituacionOportunidad;
    array_push($arrayOpcionesSituacion, $objSituacion);
    $arrayOpcionesSituacion = array_values($arrayOpcionesSituacion);
    $objOpcionesSituacionOportunidad = null;
    $objOpcionesSituacionOportunidad = (object)$arrayOpcionesSituacion;

    Logger::write('!!!Termino de Agregar la SItuación');
    Logger::write(json_encode($objOpcionesSituacionOportunidad));

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

    // ECRC: Cerrando la Conexión
    Dataworker::closeConnection();

    Logger::write('Antes de Publicar');
    Logger::write(json_encode($objOpcionesSituacionOportunidad));

    $availableInfo = true;
    $apiMessage = 'Información para Opciones de Formulario';
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('opcionesSituacionOportunidad', $objOpcionesSituacionOportunidad);
    Emissary::addData('opcionesTipoSolicitante', $objOpcionesTipoSolicitante);
    Emissary::addData('opcionesGerenteRegional', listadoGerentesRegionales());
    Emissary::addData('opcionesVendedor', listaVendedores());
    Emissary::addData('opcionesConsecionario', listadoConsecionarios());
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
    $apiMessage = 'Información para Opciones de Formulario';

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

/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);


