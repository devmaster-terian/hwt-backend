<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Dataworker.php');

function validarAcceso()
{
    Logger::enable(true,'validarAcceso');
    $hwtUsuario = Receiver::getApiParameter('usuario');
    $hwtAcceso = Receiver::getApiParameter('acceso');

    Emissary::prepareEnvelope();

    Dataworker::openConnection();
    $SqlUsuario = "SELECT * FROM sys_usuario "
        . "WHERE usuario = '$hwtUsuario' "
        . "AND acceso = '$hwtAcceso'";

    Logger::write($SqlUsuario);

    $SqlSistema = "SELECT * FROM sys_sistema";
    $resultHwtSistema = Dataworker::executeQuery($SqlSistema);
    $hwtSistema = $resultHwtSistema->data[0];

    $resultHwtUsuario = Dataworker::executeQuery($SqlUsuario);

    $SqlEmpresa = "SELECT * FROM sys_empresa";
    $resultHwtEmpresa = Dataworker::executeQuery($SqlEmpresa);
    $hwtEmpresa = $resultHwtEmpresa->data[0];


    if ($resultHwtUsuario->numRecords > 0) {
        $availableInfo = true;
        $hwtUsuario = $resultHwtUsuario->data[0];

        // ECRC: Localizando al Perfil Principal del Usuario
        $objCondicion = new \stdClass();
        $objCondicion->usuario = Dataworker::equalToString($hwtUsuario->usuario);
        $objCondicion->principal = Dataworker::equalToValue(1);
        $hwtUsuarioPerfil = Dataworker::findFirst('sys_usuario_perfil', $objCondicion);

        $objCondicion = new \stdClass();
        $objCondicion->codigo_perfil = Dataworker::equalToString($hwtUsuarioPerfil->codigo_perfil);
        $hwtPerfil = Dataworker::findFirst('sys_perfil', $objCondicion);

        $objSessionData = (object)[
            company_code => $hwtEmpresa->codigo_empresa,
            company_name => $hwtEmpresa->nombre_empresa,
            user_id      => $hwtUsuario->usuario,
            user_name => $hwtUsuario->nombre,
            user_email => $hwtUsuario->email,
            user_profile => $hwtPerfil->nombre_perfil,
            profile_code => $hwtPerfil->codigo_perfil,
            system_code => $hwtSistema->codigo_sistema,
            system_name => $hwtSistema->nombre_sistema,
            system_version => $hwtSistema->version_sistema
        ];
        $apiMessage = 'Acceso Permitido';
        Emissary::addMessage('info-api', $apiMessage);

    } else {
        $availableInfo = false;
        $apiMessage = '<span style="font-weight:bold; color: darkred">Acceso No Permitido</b></span>';
        Emissary::addMessage('info-api', $apiMessage);

        $apiMessage = 'Verifique Usuario o Clave de Acceso.';
        Emissary::addMessage('info-api', $apiMessage);
    }

    Dataworker::closeConnection();

    Emissary::success($availableInfo);
    Emissary::addData('sessionData', $objSessionData);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);
