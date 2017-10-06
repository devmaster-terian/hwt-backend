<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Dataworker.php');
require_once('../recurso/clase/Logger.php');
require_once('../recurso/clase/Mnemea.php');
require_once('../recurso/clase/Reporter.php');
require_once('apiConfigurador.php');

function reporteProveedor(){
    Logger::enable(true, 'reporteProveedor');
    Logger::write('Iniciando');

    Dataworker::openConnection();

    $filtroEstadoProveedor = Receiver::getApiParameter('filtroEstadoProveedor');

    $archivoGenerado = Reporter::openFile('reporteProveedor');
    $nombreReporte = "Proveedores";
    Reporter::prepareHeader($nombreReporte);

    Logger::write('va aqui');


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
        'estado_proveedor',
        'codigo_proveedor',
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

    $objCondicion = new \stdClass();
    $objCondicion->estado_proveedor = Dataworker::equalToString($filtroEstadoProveedor);
    $resultHwtProveedor = Dataworker::getRecords('hwt_proveedor', $objCondicion, $arrayCamposTabla);

    Reporter::writeContent($resultHwtProveedor->data);
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

    $objectOpcionesEstadoProveedor      = listaParametro('combos_proveedor','estado_proveedor');

    Dataworker::closeConnection();

    $availableInfo = true;
    $apiMessage = 'Información para Opciones de Formulario';
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('opcionesEstadoProveedor'              , $objectOpcionesEstadoProveedor);
    Emissary::addData('opcionesGridEstadoProveedor'          , $objectOpcionesEstadoProveedor);

    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function listaProveedor(){

    Logger::enable(true,'listaProveedor');

    Emissary::prepareEnvelope();

    $filtroEstadoProveedor = Receiver::getApiParameter('filtroEstado');
    $paramCodigo         = Receiver::getApiParameter('paramCodigo');
    $paramNombreCorto    = Receiver::getApiParameter('paramNombreCorto');
    $paramRazonSocial    = Receiver::getApiParameter('paramRazonSocial');
    $paramRFC            = Receiver::getApiParameter('paramRFC');

    Dataworker::openConnection();
    $SqlProveedor = "SELECT * FROM hwt_proveedor ";

    if($filtroEstadoProveedor){
        $SqlProveedor = $SqlProveedor
            . " WHERE estado_proveedor = '$filtroEstadoProveedor'";
    }
    else{
        $SqlProveedor = $SqlProveedor
            . " WHERE estado_proveedor = 'ACTIVO'";
    }

    if($paramCodigo){
        $SqlProveedor = $SqlProveedor
            . " AND codigo_proveedor like '%$paramCodigo%'";
    }

    if($paramNombreCorto){
        $SqlProveedor = $SqlProveedor
            . " AND nombre_corto like '%$paramNombreCorto%'";
    }

    if($paramRazonSocial){
        $SqlProveedor = $SqlProveedor
            . " AND razon_social like '%$paramRazonSocial%'";
    }

    if($paramRFC){
        $SqlProveedor = $SqlProveedor
            . " AND rfc like '%$paramRFC%'";
    }

    Logger::write($SqlProveedor);

    $resultHwtProveedor = Dataworker::executeQuery($SqlProveedor);

    if($resultHwtProveedor->numRecords > 0) {
        $availableInfo = true;
        $apiMessage = 'Registros Localizados';
        Emissary::addMessage('info-api' , $apiMessage);
        Emissary::addData('hwtProveedor'  , $resultHwtProveedor->data);
        Emissary::addData('numRecords'  , $resultHwtProveedor->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No hay Registros en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
        Emissary::addMessage('resultHwtProveedor' , json_encode($resultHwtProveedor));
    }

    Dataworker::closeConnection();
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function datosProveedor(){
    $codigoProveedor = Receiver::getApiParameter('codigoProveedor');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();
    $SqlProveedor = "SELECT * FROM hwt_proveedor WHERE codigo_proveedor = '$codigoProveedor'";
    $resultHwtProveedor = Dataworker::executeQuery($SqlProveedor);

    if($resultHwtProveedor->numRecords > 0) {
        $availableInfo = true;
        $apiMessage = 'Registro Localizado';
        Emissary::addMessage('info-api' , $apiMessage);
        Emissary::addData('hwtProveedor' , $resultHwtProveedor->data);
        Emissary::addData('numRecords'     , $resultHwtProveedor->numRecords);
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

function grabaProveedor(){
    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    // ECRC: Usando el Secuenciador de Proveedor
    Receiver::getApiListParameters();
    Receiver::setApiParameterValue('tfCodigoProveedor',Dataworker::getNextSequence('seq_proveedor'));

    $objCamposRegistro = Dataworker::setFieldsTable('hwt_proveedor');
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

function eliminaProveedor(){
    $codigoProveedor = Receiver::getApiParameter('codigoProveedor');
    Emissary::prepareEnvelope();

    $objFieldsRecord = (object) [
        tableName => 'hwt_proveedor',
        keyField  => 'codigo_proveedor',
        keyValue  => $codigoProveedor
    ];

    Dataworker::openConnection();
    $resultadoSql = Dataworker::deleteRecord($objFieldsRecord);
    Dataworker::closeConnection();

    if($resultadoSql->success){
        $apiMessage = 'Se ha eliminado al Proveedor con Código ' . $codigoProveedor;
    }
    else{
        $apiMessage = 'No se logró eliminar al Proveedor con Código ' . $codigoProveedor;
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
