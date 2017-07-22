<?php
ini_set('display_errors', false);
ini_set('display_startup_errors', false);

class Mnemea
{
    private static $memcache;

    public static function wakeUp()
    {
        if(!isset(self::$memcache)){
            self::$memcache = new Memcache;
            self::$memcache->connect('localhost', 11211) or Logger::write('No se logró habilitar el Servidor');

            if(isset(self::$memcache)){
                $version = self::$memcache->getVersion();
            }
        }
    }

    public static function checkKey($pKey){
        $keyData = self::$memcache->get($pKey);

        if($keyData === false){
            $keyExist = false;
        }else{
            $keyExist = true;

            if(trim($keyData) === ''){
                $keyExist = false;
            }
        }
        return $keyExist;
    }

    public static function setKey($pKey, $pKeyValue){
        $keySet = self::$memcache->set($pKey, $pKeyValue);
        return $keySet;
    }

    public static function getKey($pKey){
        $keyGet = self::$memcache->get($pKey);
        return $keyGet;
    }

    public static function cleanMemory(){
        self::$memcache->flush();
    }
}