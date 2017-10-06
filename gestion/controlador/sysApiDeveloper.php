<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Mnemea.php');

function eliminaPHPErrorLog()
{
    Emissary::prepareEnvelope();

    $error_log = ini_get('error_log');

    unlink($error_log);
    $availableInfo = true;
    $apiMessage = 'El Archivo de Log de Errores PHP ha sido eliminado. <br>'
        . 'Archivo: ' . $error_log;
    Emissary::addMessage('info-api', $apiMessage);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function eliminarArchivo()
{
    Emissary::prepareEnvelope();

    $rutaArchivo = Receiver::getApiParameter('ruta_archivo');
    $nombreArchivo = Receiver::getApiParameter('nombre_archivo');

    $archivoEliminar = $rutaArchivo . $nombreArchivo;

    if (file_exists($archivoEliminar)) {
        unlink($archivoEliminar);
        $availableInfo = true;
        $apiMessage = 'El Archivo en el Servidor ha sido eliminado. <br>'
            . 'Archivo: ' . $nombreArchivo;
    } else {
        $availableInfo = false;
        $apiMessage = 'No se encontró el Archivo en el Servidor: ' . $archivoEliminar;
    }

    Emissary::addMessage('info-api', $apiMessage);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);