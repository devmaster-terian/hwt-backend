<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Dataworker.php');
require_once('../recurso/clase/Logger.php');

function contieneValor($variable){
    return (!(!isset($variable) || trim($variable)===''));
}

function conjuntoParametros($pCodeConfig){
    Logger::enable(true,'apiConfigurador::conjuntoParametros');

    Mnemea::wakeUp();
    $keyParametro = 'param'.$pCodeConfig;
    $keyExist = Mnemea::checkKey($keyParametro);

    Logger::write('Extrayendo el Grupo de Parametros: ' . $keyParametro);

    $objReturnValue = null;
    if(!$keyExist){
        Logger::write('-----------------------------------------------------');
        Logger::write('Llamada para ' . $pCodeConfig);

        $SqlParametro = "SELECT * FROM work_config_param "
            . "WHERE code_config = '$pCodeConfig' ORDER BY code_param";

        Logger::write('Ejecuta el Query');
        $resultWorkConfigParam = Dataworker::executeQuery($SqlParametro);

        Logger::write('Descompone el Registro');
        $numLoop = 0;
        $arrayValue = array();

        if($resultWorkConfigParam->numRecords > 0){
            foreach ($resultWorkConfigParam->data as $configParameter){
                $recordString = json_encode($configParameter);
                Logger::write($recordString);

                $numLoop = $numLoop + 1;

                $objValue = (object) [
                    codigo      => $configParameter->code_param,
                    descripcion => $configParameter->description,
                    valor       => $configParameter->value
                ];

                array_push($arrayValue,$objValue);
            }
        }

        Logger::write('Descompone Parametros');

        $objReturnValue = (object) $arrayValue;
        Logger::write('Finalizo el Proceso');

        Logger::write('Grabando en Memoria el Resultado');
        Logger::write(json_encode($objReturnValue));
        Mnemea::setKey($keyParametro,$objReturnValue);
    }
    else{
        $objReturnValue = Mnemea::getKey($keyParametro);
        Logger::write('Valor anteriormente guardado');
        Logger::write(json_encode($objReturnValue));
    }

    return $objReturnValue;
}

function listaParametro($pCodeConfig, $pCodeParam){
    Mnemea::wakeUp();
    $keyParametro = $pCodeConfig.'.'.$pCodeParam;
    $keyExist = Mnemea::checkKey($keyParametro);

    Logger::write('Buscando Parametro: ' . $keyParametro);

    $objReturnValue = null;
    if(!$keyExist){
        $SqlParametro = "SELECT * FROM work_config_param "
            . "WHERE code_config = '$pCodeConfig' "
            . "AND code_param = '$pCodeParam'";

        $resultWorkConfigParam = Dataworker::executeQuery($SqlParametro);

        if($resultWorkConfigParam->numRecords > 0){
            foreach ($resultWorkConfigParam->data as $configParameter){
                $recordString = json_encode($configParameter);
                $arrayListValue = explode('|' , $configParameter->value);
            }
        }

        $numLoop = 0;
        $arrayValue = array();
        foreach ($arrayListValue as $comboValue){
            $numLoop = $numLoop + 1;
            $objValue = (object) [
                codigo      => $numLoop,
                descripcion => strtoupper($comboValue),
            ];

            array_push($arrayValue,$objValue);
        }

        $objReturnValue = (object) $arrayValue;
        Mnemea::setKey($keyParametro,$objReturnValue);
    }
    else{
        $objReturnValue = Mnemea::getKey($keyParametro);
    }

    return $objReturnValue;
}

function valorParametro($pCodeConfig, $pCodeParam){
    $objReturnValue = null;

    $SqlParametro = "SELECT * FROM work_config_param "
        . "WHERE code_config = '$pCodeConfig' "
        . "AND code_param = '$pCodeParam'";

    $resultWorkConfigParam = Dataworker::executeQuery($SqlParametro);
    if($resultWorkConfigParam->numRecords > 0){
        $objReturnValue = $resultWorkConfigParam->data;
    }

    return $objReturnValue;
}