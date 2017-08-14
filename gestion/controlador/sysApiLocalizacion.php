<?php
require_once('../recurso/clase/Dataworker.php');
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Mnemea.php');

function zoomPais()
{
    $arrayColumnas = array();
    array_push($arrayColumnas, 'cod_pais as codigo');
    array_push($arrayColumnas, 'pais     as descripcion');

    $tabla = 'sys_localizacion';

    Dataworker::openConnection();
    $hwtPais = Dataworker::getDistinct(
        $arrayColumnas,
        $tabla);
    Dataworker::closeConnection();

    $apiMessage = 'Lista de País';
    $availableInfo = true;

    Emissary::prepareEnvelope();
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('hwtZoomGenerico', $hwtPais);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function zoomEstado()
{
    $pCodPais = utf8_decode(Receiver::getApiParameter('pais'));

    $arrayColumnas = array();
    array_push($arrayColumnas, 'cod_estado as codigo');
    array_push($arrayColumnas, 'estado     as descripcion');

    $tabla = 'sys_localizacion';

    $objConsulta = new \stdClass();
    $objConsulta->pais = Dataworker::equalToString($pCodPais);

    $arrayOrden = array();
    array_push($arrayOrden, 'estado');

    Dataworker::openConnection();
    $hwtEstado = Dataworker::getDistinct(
        $arrayColumnas,
        $tabla,
        $objConsulta,
        $arrayOrden);
    Dataworker::closeConnection();

    $apiMessage = 'Lista de Estado';
    $availableInfo = true;

    Emissary::prepareEnvelope();
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('hwtZoomGenerico', $hwtEstado);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function zoomMunicipio()
{
    $pCodPais = utf8_decode(Receiver::getApiParameter('pais'));
    $pCodEstado = utf8_decode(Receiver::getApiParameter('estado'));

    $arrayColumnas = array();
    array_push($arrayColumnas, 'cod_municipio as codigo');
    array_push($arrayColumnas, 'municipio     as descripcion');

    $tabla = 'sys_localizacion';

    $objConsulta = new \stdClass();
    $objConsulta->pais = Dataworker::equalToString($pCodPais);
    $objConsulta->estado = Dataworker::equalToString($pCodEstado);
    $objConsulta->zona = Dataworker::compare('notEqualThan', 'string', 'Rural');

    $arrayOrden = array();
    array_push($arrayOrden, 'municipio');

    Dataworker::openConnection();
    $hwtMunicipio = Dataworker::getDistinct(
        $arrayColumnas,
        $tabla,
        $objConsulta,
        $arrayOrden);
    Dataworker::closeConnection();

    $apiMessage = 'Lista de Municipios';
    $availableInfo = true;

    Emissary::prepareEnvelope();
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('hwtZoomGenerico', $hwtMunicipio);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function zoomColonia()
{

    $pCodPais = utf8_decode(Receiver::getApiParameter('pais'));
    $pCodEstado = utf8_decode(Receiver::getApiParameter('estado'));
    $codMunicipio = utf8_decode(Receiver::getApiParameter('municipio'));

    $arrayColumnas = array();
    array_push($arrayColumnas, 'asentamiento as descripcion');

    $tabla = 'sys_localizacion';

    $objConsulta = new \stdClass();
    $objConsulta->pais = Dataworker::equalToString($pCodPais);
    $objConsulta->estado = Dataworker::equalToString($pCodEstado);
    $objConsulta->municipio = Dataworker::equalToString($codMunicipio);
    $objConsulta->zona = Dataworker::compare('notEqualThan', 'string', 'Rural');

    $arrayOrden = array();
    array_push($arrayOrden, 'asentamiento');

    Dataworker::openConnection();
    $hwtColonia = Dataworker::getDistinct(
        $arrayColumnas,
        $tabla,
        $objConsulta,
        $arrayOrden);
    Dataworker::closeConnection();

    $apiMessage = 'Lista de Colonias';
    $availableInfo = true;

    Emissary::prepareEnvelope();
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::addData('hwtZoomGenerico', $hwtColonia);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);