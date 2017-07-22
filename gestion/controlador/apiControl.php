<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Mnemea.php');

function limpiarMemoria(){
    Mnemea::cleanMemory();

    Emissary::prepareEnvelope();

    $apiMessage = 'Memoria MEMCACHE limpiada.';
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::success(true);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);