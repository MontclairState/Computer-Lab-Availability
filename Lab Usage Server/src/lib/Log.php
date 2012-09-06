<?php

class Log {
    /* Logging levels */
    const URGENT    = 0;  // Urgen: system is unusable
    const ALERT     = 1;  // Alert: action must be taken immediately
    const CRITICAL  = 2;  // Critical: critical conditions
    const ERROR     = 3;  // Error: error conditions
    const WARNING   = 4;  // Warning: warning conditions
    const NOTICE    = 5;  // Notice: normal but significant condition
    const INFO      = 6;  // Informational: informational messages
    const DEBUG     = 7;  // Debug: debug messages
    
    private static $dateFormat = "Y-m-d H:i:s";
    
    public static function getLevelName($level) {
        
        switch($level) {
            case self::URGENT:
                return "URGENT";
                break;
            case self::ALERT:
                return "ALERT";
                break;
            case self::CRITICAL:
                return "CRITICAL";
                break;
            case self::ERROR:
                return "ERROR";
                break;
            case self::WARNING:
                return "WARNING";
                break;
            case self::NOTICE:
                return "NOTICE";
                break;
            case self::INFO:
                return "INFO";
                break;
            case self::DEBUG:
                return "DEBUG";
                break;
            default:
                return "";
                break;
        }
        
    }
    
    private static function write($message, $level) {
        
        if(!file_exists(_APP_LOG)) {
            error_log("[" . _APPLICATION_NAME . "] - [" . self::getLevelName($level) . "] : " . $message);
        } else {
            $logFile = @fopen(_APP_LOG, 'a');
            if($logFile === false) {
                error_log("[" . _APPLICATION_NAME . "] - [" . self::getLevelName($level) . "] : " . $message);
            } else {
                $message = "[" . date(self::$dateFormat, time()) . "] - " . 
                        "[" . self::getLevelName($level) . "] : " . 
                        $message . PHP_EOL;
                fwrite($logFile, $message);
                fclose($logFile);
            }
        }
        
    }
    
    public static function debug($message) {
        self::write($message, self::DEBUG);
    }
    
    public static function info($message) {
        self::write($message, self::INFO);
    }
    
    public static function notice($message) {
        self::write($message, self::NOTICE);
    }
    
    public static function warning($message) {
        self::write($message, self::WARNING);
    }
    
    public static function error($message) {
        self::write($message, self::ERROR);
    }
    
    public static function critical($message) {
        self::write($message, self::CRITICAL);
    }
    
    public static function alert($message) {
        self::write($message, self::ALERT);
    }
    
    public static function urgent($message) {
        self::write($message, self::URGENT);
    }
}

?>
