<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Dataworker.php');
require_once('../recurso/clase/Logger.php');
require_once('../recurso/clase/Mnemea.php');
require_once('../recurso/clase/Reporter.php');
require_once('apiConfigurador.php');

function reporteCliente(){
    $filtroEstadoCliente = Receiver::getApiParameter('filtroEstadoCliente');

    $archivoGenerado = Reporter::openFile('reporteCliente');
    $nombreReporte = "Clientes";
    Reporter::prepareHeader($nombreReporte);

    //ECRC: Preparando el Titulo de las Columnas
    $arrayTituloColumnas = array(
        "15:ESTATUS\rCLIENTE",
        "12:CODIGO\rCLIENTE",
        "20:NOMBRE\rCORTO",
        "40:RAZON SOCIAL",
        "15:RFC",
        "30:CALLE",
        "10:NO. EXT.",
        "10:NO. INT.",
        "30:COLONIA",
        "20:MUNICIPIO",
        "20:ESTADO",
        "15:PAIS",
        "10:CODIGO\rPOSTAL",
        "20:REPRESENTE\rLEGAL",
        "20:CONTACTO\rNOMBRE",
        "15:CONTACTO\rTELEFONO",
        "15:CONTACTO\rMOVIL",
        "30:CONTACTO\rCORREO",
        "30:FACTURACION\rCORREO",
    );

    Reporter::prepareTitleColumns($arrayTituloColumnas);

    $arrayCamposTabla = array(
        'estado_cliente',
        'codigo_cliente',
        'nombre_corto',
        'razon_social',
        'rfc',
        'dir_calle',
        'dir_num_exterior',
        'dir_num_interior',
        'dir_colonia',
        'dir_municipio',
        'dir_estado',
        'dir_pais',
        'codigo_postal',
        'representante_legal',
        'contacto_nombre',
        'contacto_telefono',
        'contacto_movil',
        'contacto_email',
        'facturacion_email'

    );

    $SqlCliente = "SELECT " . implode(',',$arrayCamposTabla) ." FROM hwt_cliente ";

    if($filtroEstadoCliente){
        $SqlCliente = $SqlCliente
            . " WHERE estado_cliente = '$filtroEstadoCliente'";
    }

    Dataworker::openConnection();
    $resultHwtCliente = Dataworker::executeQuery($SqlCliente);
    Reporter::writeContent($resultHwtCliente->data);
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

function datosOpciones(){
    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    $objectOpcionesEstadoUnidad      = listaParametro('combos_cliente','estado_cliente');

    Dataworker::closeConnection();

    $availableInfo = true;
    $apiMessage = 'Información para Opciones de Formulario';
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('opcionesEstadoCliente'              , $objectOpcionesEstadoUnidad);
    Emissary::addData('opcionesGridEstadoCliente'          , $objectOpcionesEstadoUnidad);

    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function listaCliente(){

    Logger::enable(true,'listaCliente');

    Emissary::prepareEnvelope();

    $filtroEstadoCliente = Receiver::getApiParameter('filtroEstado');
    $paramCodigo         = Receiver::getApiParameter('paramCodigo');
    $paramNombreCorto    = Receiver::getApiParameter('paramNombreCorto');
    $paramRazonSocial    = Receiver::getApiParameter('paramRazonSocial');
    $paramRFC            = Receiver::getApiParameter('paramRFC');

    Dataworker::openConnection();
    $SqlCliente = "SELECT * FROM hwt_cliente ";

    if($filtroEstadoCliente){
        $SqlCliente = $SqlCliente
            . " WHERE estado_cliente = '$filtroEstadoCliente'";
    }
    else{
        $SqlCliente = $SqlCliente
            . " WHERE estado_cliente = 'ACTIVO'";
    }

    if($paramCodigo){
        $SqlCliente = $SqlCliente
            . " AND codigo_cliente like '%$paramCodigo%'";
    }

    if($paramNombreCorto){
        $SqlCliente = $SqlCliente
            . " AND nombre_corto like '%$paramNombreCorto%'";
    }

    if($paramRazonSocial){
        $SqlCliente = $SqlCliente
            . " AND razon_social like '%$paramRazonSocial%'";
    }

    if($paramRFC){
        $SqlCliente = $SqlCliente
            . " AND rfc like '%$paramRFC%'";
    }

    Logger::write($SqlCliente);

    $resultHwtCliente = Dataworker::executeQuery($SqlCliente);

    if($resultHwtCliente->numRecords > 0) {
        $availableInfo = true;
        $apiMessage = 'Registros Localizados';
        Emissary::addMessage('info-api' , $apiMessage);
        Emissary::addData('hwtCliente'  , $resultHwtCliente->data);
        Emissary::addData('numRecords'  , $resultHwtCliente->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No hay Registros en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
        Emissary::addMessage('resultHwtCliente' , json_encode($resultHwtCliente));
    }

    Dataworker::closeConnection();
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function datosCliente(){
    $codigoCliente = Receiver::getApiParameter('codigoCliente');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();
    $SqlCliente = "SELECT * FROM hwt_cliente WHERE codigo_cliente = '$codigoCliente'";
    $resultHwtCliente = Dataworker::executeQuery($SqlCliente);

    if($resultHwtCliente->numRecords > 0) {
        $availableInfo = true;
        $apiMessage = 'Registro Localizado';
        Emissary::addMessage('info-api' , $apiMessage);
        Emissary::addData('hwtCliente' , $resultHwtCliente->data);
        Emissary::addData('numRecords'     , $resultHwtCliente->numRecords);
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
}

function grabaCliente(){
    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    // ECRC: Usando el Secuenciador de Cliente
    Receiver::getApiListParameters();
    Receiver::setApiParameterValue('tfCodigoCliente',Dataworker::getNextSequence('seq_cliente'));

    $objCamposRegistro = Dataworker::setFieldsTable('hwt_cliente');
    $sqlEjecutado      = Dataworker::updateRecord($objCamposRegistro);

    $registroActualizado = true;
    $apiMessage = 'Registro actualizado en la Base de Datos';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addMessage('sql-ejecutado' , $sqlEjecutado);
    Emissary::addData('camposRegistro' , $objCamposRegistro);
    Dataworker::closeConnection();

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function eliminaCliente(){
    $codigoCliente = Receiver::getApiParameter('codigoCliente');
    Emissary::prepareEnvelope();

    $objFieldsRecord = (object) [
        tableName => 'hwt_cliente',
        keyField  => 'codigo_cliente',
        keyValue  => $codigoCliente
    ];

    Dataworker::openConnection();
    $resultadoSql = Dataworker::deleteRecord($objFieldsRecord);
    Dataworker::closeConnection();

    if($resultadoSql->success){
        $apiMessage = 'Se ha eliminado al Cliente con Código ' . $codigoCliente;
    }
    else{
        $apiMessage = 'No se logró eliminar al Cliente con Código ' . $codigoCliente;
    }
    Emissary::success($resultadoSql->success);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('resultadoSQL' , $resultadoSql);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);
