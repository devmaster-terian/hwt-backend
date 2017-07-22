<?php
date_default_timezone_set('America/Mexico_City');

class Logger
{
    private static $standardOutput;
    private static $active;
    private static $processCaller;
    private static $lastTime;

    public static function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public static function disable(){
        self::$active = false;
    }

    public static function enable($activateLogger,$processCaller){
        if(!$activateLogger){
            self::$active = true;
        }
        else{
            self::$active = $activateLogger;
        }

        if(!$processCaller){
            self::$processCaller = '*Process not defined!';
        }
        else{
            self::$processCaller = $processCaller;
        }

    }

    public static function write($message){
        if(self::$active){

            $processLog = self::$processCaller;
            $processLog = str_replace('::','_',$processLog);

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // WINDOWS
                self::$standardOutput = dirname(__FILE__) . "../logs/LoggerProc_"
                    . date('Ymd') . ".log";
            } else {
                // LINUX
                self::$standardOutput = dirname(__FILE__) . "/logs/LoggerProc_"
                    . date('Ymd') . ".log";
            }

            $currentDatetime = date('Y-m-d H:i:s');
            $currentTime = microtime(true);

            $dFormat = "l jS F, Y - H:i:s";
            $time    = microtime(true);
            $mSecs   =  $time - floor($time);
            $mSecs   =  substr($mSecs,1);
            $message = $message . "\n";

            $difTime = $currentTime - self::$lastTime;
            $difTime = round($difTime,4);

            $logString = $currentDatetime . ' T ' . number_format((float)$difTime, 4, '.', ''). ' [' . self::$processCaller . '] ' . $message;

            error_log($logString, 3, self::$standardOutput);
            self::$lastTime = microtime(true);
        }

        return;
    }
}