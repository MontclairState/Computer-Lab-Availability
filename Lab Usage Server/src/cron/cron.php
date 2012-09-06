<?php

/*
 * This is cron script so it must be called from command line.
 * You can use cron.sh or cron.bat to excute this script. The cron should
 * execute it on hourly basis. 
 * 
 * This script will insert available, occupied or offline computer count
 * into labstats table every hour. (if you set the cron to run every hour)
 * The labstats table will be used for generating reports.
 * 
 */

if (!isset($_SERVER['HTTP_USER_AGENT'])) {
    date_default_timezone_set('America/New_York');    
    $cur_date = date("Y-m-d"); // DATE Format : YYYY-MM-DD
    $cur_time = date("H:i"); // TIME Format : HH:MM
    
    $appDir = dirname(__DIR__);
    require_once($appDir . DIRECTORY_SEPARATOR . 'config.php');
    
    $db = new Db($env[_APP_ENV]['db']);
    $db->connect();
    
    $selectQuery = "SELECT c.lab_id as lab_id, c.mstatus, count(c.mstatus) as cn, l.name as lab_name " . 
                   "FROM computer c " .
                   "LEFT JOIN lab l ON c.lab_id = l.id " . 
                   "GROUP BY c.lab_id, c.mstatus";    
    
    $labData = array();
    $results = $db->select($selectQuery, array());
    
    if(count($results) > 0) {

        foreach($results as $row) {
            if(!isset($labData[$row["lab_id"]])) {
                $labData[$row["lab_id"]] = array();
                $labData[$row["lab_id"]]["lab_id"] = intval($row["lab_id"]);
                $labData[$row["lab_id"]]["lab_name"] = $row["lab_name"];
            }

            if( intval($row['mstatus']) === 0 ) { // Available
                    $labData[$row["lab_id"]]['available'] = intval($row['cn']);
            }
            if( intval($row['mstatus']) === 1 ) { // Occupied
                    $labData[$row["lab_id"]]['occupied'] = intval($row['cn']);
            }
            if( intval($row['mstatus']) === 2 ) { // Offline
                    $labData[$row["lab_id"]]['offline'] = intval($row['cn']);
            }
        }
        
        if(count($labData) > 0) {
            foreach($labData as $id => $row) {
                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                    $insertQuery = "INSERT INTO labstats(lab, lab_id, stat_date, stat_time, available, occupied, offline) " . 
                       "VALUES(:lab, :lab_id, :stat_date, :stat_time, :available, :occupied, :offline)";
                    $params = array();
                    $params[":lab"] = $row["lab_name"];
                    $params[":lab_id"] = $row["lab_id"];
                    $params[":stat_date"] = $cur_date;
                    $params[":stat_time"] = $cur_time;

                    if(array_key_exists("available", $row) && $row["available"] > 0) {
                        $params[":available"] = $row["available"];
                    } else {
                        $params[":available"] = 0;
                    }

                    if(array_key_exists("occupied", $row) && $row["occupied"] > 0) {
                        $params[":occupied"] = $row["occupied"];
                    } else {
                        $params[":occupied"] = 0;
                    }

                    if(array_key_exists("offline", $row) && $row["offline"] > 0) {
                        $params[":offline"] = $row["offline"];
                    } else {
                        $params[":offline"] = 0;
                    }

                    $db->exec($insertQuery, $params);
                    $params = null;
                    
                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                    
                    $params = array();
                    $params[":lab"] = $row["lab_name"];
                    $params[":lab_id"] = $row["lab_id"];
                    $params[":stat_date"] = $cur_date;
                    $params[":stat_time"] = $cur_time;

                    if(array_key_exists("available", $row) && $row["available"] > 0) {
                        $params[":available"] = $row["available"];
                    } else {
                        $params[":available"] = 0;
                    }

                    if(array_key_exists("occupied", $row) && $row["occupied"] > 0) {
                        $params[":occupied"] = $row["occupied"];
                    } else {
                        $params[":occupied"] = 0;
                    }

                    if(array_key_exists("offline", $row) && $row["offline"] > 0) {
                        $params[":offline"] = $row["offline"];
                    } else {
                        $params[":offline"] = 0;
                    }
                    $insertQuery = "INSERT INTO labstats(lab, lab_id, stat_date, stat_time, available, occupied, offline) " .
                                   "VALUES('" . $db->getMysqliDbObject()->escape_string($params[":lab"]) . "', " . 
                                    intval($params[":lab_id"]) . ", " . 
                                    "'" . $db->getMysqliDbObject()->escape_string($params[":stat_date"]) . "', " . 
                                    "'" . $db->getMysqliDbObject()->escape_string($params[":stat_time"]) . "', " . 
                                    intval($params[":available"]) . ", " . 
                                    intval($params[":occupied"]) . ", " . 
                                    intval($params[":offline"]) . ")";
                    
                    $db->exec($insertQuery, $params);
                    $params = null;
                }
            }
        }
    }
    
    $selectQuery = null; unset($selectQuery);
    $insertQuery = null; unset($insertQuery);
    $labData = null; unset($labData);
    $results = null; unset($results);
    
    $db->close();
    $db = null; unset($db);
    $appDir = null; unset($appDir);
}
?>
