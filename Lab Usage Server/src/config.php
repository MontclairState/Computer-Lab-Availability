<?php
/*
 * Path/Resources constants
 */
defined("_APP_NAME") 
    or define("_APP_NAME", "LabUsage v2");

defined("_APP_PATH")
    or define("_APP_PATH", realpath(dirname(__FILE__)));

defined("_WEB_HOST")
    or define("_APP_HOST", "localhost");

defined("_WEB_PATH")
    or define("_WEB_PATH", "/labusage");
    
defined("_LIBRARY_PATH") 
    or define("_LIBRARY_PATH", _APP_PATH . DIRECTORY_SEPARATOR . 'lib');

defined("_CLASS_PATH") 
    or define("_CLASS_PATH", _APP_PATH . DIRECTORY_SEPARATOR . 'classes');

defined("_LOG_PATH") 
    or define("_LOG_PATH", _APP_PATH . DIRECTORY_SEPARATOR . 'logs');

/* Application Environment (case sensitive)
 * Choose 1 from "local", "development", "test" or "production"
 */
defined("_APP_ENV")
    or define("_APP_ENV", "production");
/*
 * PHP Error reporting
 */
ini_set("error_reporting", "true");
error_reporting(E_ALL|E_STRCT);
if(_APP_ENV === "local") {
    ini_set("display_errors", 1);
} else {
    ini_set("display_errors", 0);
}
ini_set("log_errors", 1);
ini_set("error_log", _LOG_PATH . DIRECTORY_SEPARATOR . "php.log");

/*
 * Custom Application logging
 */
defined("_APP_LOG") 
    or define("_APP_LOG", _LOG_PATH . DIRECTORY_SEPARATOR . 'app.log');
include_once(_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'Log.php');

/*
 * Application environments
 */
$env = array(
    "local" => array(
        "db" => array(
            "adapter" => "PDO_MYSQL",
            "host" => "127.0.0.1",
            "port" => "3306",
            "dbname" => "labusage",
            "username" => "labusage",
            "password" => ""
        )
    ), 
    "development" => array(
        "db" => array(
            "adapter" => "PDO_MYSQL",
            "host" => "127.0.0.1",
            "port" => "3306",
            "dbname" => "labusage",
            "username" => "labusage",
            "password" => ""
        )
    ), 
    "test" => array(
        "db" => array(
            "adapter" => "PDO_MYSQL",
            "host" => "127.0.0.1",
            "port" => "3306",
            "dbname" => "labusage",
            "username" => "",
            "password" => ""
        )
    ), 
    "production" => array(
        "db" => array(
            "adapter" => "PDO_MYSQL",
            "host" => "127.0.0.1",
            "port" => "3306",
            "dbname" => "labusage",
            "username" => "",
            "password" => ""
        )
    )
);

$labCompStatusArray = array(0 => "Available", 1 => "Occupied", 2 => "Maintenance");

/*
 * Loading all required classes
 */
require_once(_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'Db.php');
require_once(_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'Util.php');
require_once(_CLASS_PATH . DIRECTORY_SEPARATOR . 'Lab.php');
require_once(_CLASS_PATH . DIRECTORY_SEPARATOR . 'Computer.php');



?>
