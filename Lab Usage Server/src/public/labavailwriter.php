<?php

$remote_addr = $_SERVER['REMOTE_ADDR'];

if (!empty($remote_addr)) {
    $ip = explode(".", $remote_addr);
    if (count($ip) == 4 &&
            $ip[0] == '130' &&
            $ip[1] == '68') { // Only allow internal traffic
        
        require_once("../config.php");
        
        $compName = filter_input(INPUT_POST, 'machine', FILTER_SANITIZE_STRING);
        $mstatus = intval(filter_input(INPUT_POST, 'mstatus', FILTER_SANITIZE_NUMBER_INT));
        
        if(!empty($compName) &&
                key_exists($mstatus, $labCompStatusArray)) {
            
            $db = new Db($env[_APP_ENV]['db']);
            $db->connect();
            
            $updateComp = false;
            if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                $query = "UPDATE computer SET mstatus = :mstatus WHERE name = :name";
                $params = array(":mstatus" => $mstatus, ":name" => $compName);
                $updateComp = $db->exec($query, $params);
                $query = null; unset($query);
                $params = null; unset($params);
            } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                $query = "UPDATE computer " . 
                         "SET mstatus = " . $mstatus . " " . 
                         "WHERE name = '" . $db->getMysqliDbObject()->escape_string($compName) ."'";
                $updateComp = $db->exec($query);                
                $query = null; unset($query);
            }
            
            if($updateComp === false) { // FAIL. 
                //PDO Error.
                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                    Log::error("PDO Error while updating computer from labawailwriter.php." . 
                                "\nPDO ErrorInfo Obj: " . var_export($db->getPdoStmtObject->errorInfo(), true) .
                                "\nName: " . $compName . ", mstatus: " . $mstatus);
                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                    Log::error("MySQLi Error while updating computer from labawailwriter.php." . 
                               "\nName: " . $compName . ", mstatus: " . $mstatus);
                }
                
            } else { // Result is true
                // It should affect only one row. So
                // If only one row is affected, then save login/logout time.
                if( intval($db->rowCount()) == 1) {
                    $cur_date = date("Y-m-d"); // DATE Format : YYYY-MM-DD
                    $cur_time = date("H:i"); // TIME Format : HH:MM
                    $cur_ts = time();
                    
                    if ($mstatus == 0) { // Computer is available because user logout. So update the logout timestamp.
                        $results = array();
                        if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                            $query = "SELECT id FROM comptrack WHERE comp_name = :comp_name " .
                                     "AND logout_timestamp IS NULL ORDER BY login_timestamp";
                            $params = array(":comp_name" => $compName);
                            $results = $db->select($query, $params);
                            $query = null; unset($query);
                            $params = null; unset($params);
                        } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                            $query = "SELECT id " . 
                                     "FROM comptrack " . 
                                     "WHERE comp_name = '" . $db->getMysqliDbObject()->escape_string($compName) . "' " .
                                     "AND logout_timestamp IS NULL " . 
                                     "ORDER BY login_timestamp";                            
                            $results = $db->select($query);
                            $query = null; unset($query);                            
                        }
                        
                        // Should receive only one row
                        if(count($results) == 1) {
                            $id = 0;
                            $id = intval($results[0]['id']);
                            if($id !== 0) {
                                $updateCompTrack = false;
                                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                    $query = "UPDATE comptrack " . 
                                             "SET logout_date = :logout_date, " .
                                             "logout_time = :logout_time, " . 
                                             "logout_timestamp = :logout_timestamp " . 
                                             "WHERE id = :id";
                                    $params = array(":logout_date" => $cur_date, 
                                                    ":logout_time" => $cur_time, 
                                                    ":logout_timestamp" => $cur_ts, 
                                                    ":id" => $id);
                                    $updateCompTrack = $db->exec($query, $params);
                                    $params = null; unset($params);
                                    $query = null; unset($query);
                                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                    $query = "UPDATE comptrack " . 
                                             "SET logout_date = '" . $db->getMysqliDbObject()->escape_string($cur_date) . "', " .
                                             "logout_time = '" . $db->getMysqliDbObject()->escape_string($cur_time) . "', " . 
                                             "logout_timestamp = " . intval($cur_ts) . " " . 
                                             "WHERE id = " . intval($id);
                                    $updateCompTrack = $db->exec($query);
                                    $params = null; unset($params);
                                    $query = null; unset($query);
                                }
                                if($updateCompTrack === false) { // FAIL
                                    if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                        Log::error("PDO Error while updating comptrack from labawailwriter.php." . 
                                                    "\nPDO ErrorInfo Obj: " . var_export($db->getPdoStmtObject->errorInfo(), true) .
                                                    "\nName: " . $compName . ", mstatus: " . $mstatus .
                                                    ", logout_date: " . $cur_date . ", logout_time: " . $cur_time . 
                                                    ", logout_timstamp: " . $cur_ts);
                                    } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                        Log::error("MySQLi Error while updating comptrack from labawailwriter.php." .
                                                    "\nName: " . $compName . ", mstatus: " . $mstatus .
                                                    ", logout_date: " . $cur_date . ", logout_time: " . $cur_time . 
                                                    ", logout_timstamp: " . $cur_ts);
                                    }
                                }
                                $updateCompTrack = null; unset($updateCompTrack);
                            }
                            $id = null; unset($id);
                        }                        
                        $results = null; unset($results);
                        
                    } else if($mstatus == 1) { // Computer is not available because of user login. So enter the login timestamp
                        $insertCompTrack = false;                    
                        if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                            $query = "INSERT INTO comptrack(comp_name, login_date, login_time, login_timestamp) " .
                                     "VALUES(:comp_name, :login_date, :login_time, :login_timestamp)";

                            $params = array(":comp_name" => $compName,
                                            ":login_date" => $cur_date, 
                                            ":login_time" => $cur_time, 
                                            ":login_timestamp" => $cur_ts);
                            $insertCompTrack = $db->exec($query, $params);
                            $params = null; unset($params);
                            $query = null; unset($query);
                        } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                            $query = "INSERT INTO comptrack(comp_name, login_date, login_time, login_timestamp) " .
                                     "VALUES('" . $db->getMysqliDbObject()->escape_string($compName) . "', " . 
                                     "'" . $db->getMysqliDbObject()->escape_string($cur_date) . "', " .
                                     "'" . $db->getMysqliDbObject()->escape_string($cur_time) . "', " . 
                                     $cur_ts . " )";

                            $insertCompTrack = $db->exec($query);
                            $query = null; unset($query);
                        }
                        if($insertCompTrack === false) { // FAIL
                            if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                Log::error("PDO Error while inserting into comptrack from labawailwriter.php." . 
                                            "\nPDO ErrorInfo Obj: " . var_export($db->getPdoStmtObject->errorInfo(), true) .
                                            "\nName: " . $compName . ", mstatus: " . $mstatus .
                                            ", login_date: " . $cur_date . ", login_time: " . $cur_time . 
                                            ", login_timestamp: " . $cur_ts);
                            } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                Log::error("MySQLi Error while inserting into comptrack from labawailwriter.php." .
                                            "\nName: " . $compName . ", mstatus: " . $mstatus .
                                            ", login_date: " . $cur_date . ", login_time: " . $cur_time . 
                                            ", login_timestamp: " . $cur_ts);
                            }
                        }
                        
                        $insertCompTrack = null; unset($insertCompTrack);
                        
                    }
                    $cur_date = null; unset($cur_date);
                    $cur_time = null; unset($cur_time);
                    $cur_ts = null; unset($cur_ts);                    
                }
            }
            
            $updateComp = null; unset($updateComp);
            $db->close();
            $db = null; unset($db);
        }
        
        $compName = null; unset($compName);
        $mstatus = null; unset($mstatus);
    }
    $ip = null; unset($ip);
}

$remote_addr = null; unset($remote_addr);
?>
