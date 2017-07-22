<?php
ini_set('display_errors', false);
ini_set('display_startup_errors', false);
require_once('../recurso/clase/Logger.php');
require_once('../recurso/clase/Mnemea.php');

class Receiver
{
    private static $apiData;
    private static $objApiData;

    public static function resetApiParameters(){
        self::$objApiData = null;
    }

    public static function getApiMethod()
    {
        $apiMethod = $_REQUEST["apiMethod"];
        return $apiMethod;
    }

    public static function getPostParameter($pParameter)
    {
        $postParameter = $_REQUEST[$pParameter];
        return $postParameter;
    }

    public static function getApiListParameters()
    {
        if(!self::$apiData){
            self::$apiData   = $_REQUEST["apiData"];
            self::$objApiData = json_decode(self::$apiData);
        }

        return self::$objApiData;
    }

    public static function setApiParameterValue($parameter,$value){
        self::prepareParameters();
        Logger::enable(true,'Receiver::setApiParameterValue');

        self::$objApiData->{$parameter} = $value;
        return self::$objApiData->{$parameter};
    }

    public static function prepareParameters(){
        if(!self::$apiData){
            self::$apiData    = $_REQUEST["apiData"];
            self::$objApiData = json_decode(self::$apiData);
        }

        return self::$objApiData;
    }

    public static function getApiParameter($parameter)
    {
        Logger::enable(true,'Receiver::getApiParameter');
        if(!self::$apiData){
            self::$apiData    = $_REQUEST["apiData"];
            self::$objApiData = json_decode(self::$apiData);
        }

        // ECRC: Utilizando el Cache para buscar el Nombre del Parámetro
        Mnemea::wakeUp();

        $keyParametro = 'getApiParameter.'.$parameter;
        $keyExist = Mnemea::checkKey($keyParametro);
        if($keyExist){
            $parameter = Mnemea::getKey($keyParametro);
            $parameterValue = self::$objApiData->{$parameter};

            Logger::write('Mnemea encontro: ' . $parameter . ' > '. $parameterValue);
            return $parameterValue;
        }

        $parameterValue = self::$objApiData->{$parameter};

        if($parameterValue === null){
            $prefixList = "tf, dt, df, cbx, chk, fi, ch, cb, ind, ta, rg, rb";
            $arrayPrefix = explode(', ',$prefixList);

            foreach ($arrayPrefix as $prefix){
                $parameterPrefix = $prefix . $parameter;
                if($parameterValue === null){
                    $parameterValue = self::$objApiData->{$parameterPrefix};

                    Logger::write('Buscando: ' . $parameterPrefix);

                    if($parameterValue !== null and $prefix === 'chk'){

                        if($parameterValue){
                            $parameterValue = 1;
                        }
                        else{
                            $parameterValue = 0;
                        }
                    }
                }

                if($parameterValue !== null){
                    Mnemea::setKey($keyParametro,$parameterPrefix);
                    Logger::write('Parametro encontrado: ' . $keyParametro . ' ' . $parameterPrefix . ' ' . $parameterValue);
                    break;
                }

            }//foreach
        } //parameterValue is null

        if($parameterValue === null) {
            $parameterValue = '';
        }

        return $parameterValue;
    }
}