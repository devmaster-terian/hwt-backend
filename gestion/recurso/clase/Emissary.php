<?php
class Emissary
{
    private static $dataReturn;

    public static function deliverEnvelope()
    {
        $objReturn = self::$dataReturn;
        header('Content-type:application/json;charset=utf-8');
        echo json_encode($objReturn);
    }

    public static function isReady()
    {
        return 'Emisary is Ready!</br>';
    }

    public static function prepareEnvelope()
    {
        self::$dataReturn = new \stdClass();
        self::$dataReturn->message = array();
        //self::$dataReturn->data = array();
    }

    public static function success($success)
    {
        self::$dataReturn->success = $success;
    }

    public static function addMessage($type , $message)
    {
        $objMessage = new \stdClass();
        $objMessage->type = $type;
        $objMessage->message = utf8_encode($message);

        array_push(self::$dataReturn->message , $objMessage);
    }

    public static function addData($objName , $objData)
    {
        if($objData !== null){
            self::$dataReturn->{$objName} = $objData;
        }
    }

    public static function getEnvelope()
    {
        return self::$dataReturn;
    }
}