<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Mnemea.php');

function limpiaCache(){
    Mnemea::wakeUp();
    Mnemea::cleanMemory();

    Emissary::prepareEnvelope();

    $availableInfo = true;
    $apiMessage = 'La Memoria Cache ha sido limpiada.';
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);

