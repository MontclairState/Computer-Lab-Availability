<?php
$lab = filter_input(
            INPUT_GET, 
            'lab', 
            FILTER_VALIDATE_REGEXP, 
            array("options"=>array("regexp" => "/^[A-Za-z0-9_\-]+$/"))
        );

$type = filter_input(
            INPUT_GET, 
            'type', 
            FILTER_VALIDATE_REGEXP, 
            array("options"=>array("regexp" => "/^[A-Za-z_]+$/"))
        );
 
// Check for type in array
if(!in_array($type, array("day", "week", "month", "custom", "comp_usage"))) {
    $type = null;
    $lab = null;    
    exit();
}

if($type === 'day') {
    
    $date = filter_input(
                INPUT_GET, 
                'date', 
                FILTER_VALIDATE_REGEXP, 
                array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/"))
            );
    
    require_once("../../config.php");
    
    // Check if date is valid
    if(!Util::isDateValid($date)) {
        echo(""); exit();
    }
    $db = new Db($env[_APP_ENV]['db']);
    $db->connect();
    $results = array();
    if($lab === "all") {
        $lab = "All labs";
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, sum(available) as available, sum(occupied) as occupied, sum(offline) as offline FROM labstats WHERE stat_date = :stat_date GROUP BY stat_date, stat_time ORDER BY stat_time ASC";            
            $params = array(":stat_date" => $date);
            $results = $db->select($sql, $params);
            $sql = null;
            $params = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, sum(available) as available, sum(occupied) as occupied, sum(offline) as offline FROM labstats WHERE stat_date = '" . $db->getMysqliDbObject()->escape_string($date) . "' GROUP BY stat_date, stat_time ORDER BY stat_time ASC";
            $results = $db->select($sql);
            $sql = null;
        }
    } else {
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT HOUR(stat_time) as stat_hour, available, occupied, offline FROM labstats WHERE lab = :lab AND stat_date = :stat_date ORDER BY stat_time ASC";    
            $params = array(":lab" => $lab, ":stat_date" => $date);
            $results = $db->select($sql, $params);
            $params = null;
            $sql = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT HOUR(stat_time) as stat_hour, available, occupied, offline " . 
                   "FROM labstats " . 
                   "WHERE lab = '" . $db->getMysqliDbObject()->escape_string($lab) . "' " . 
                   "AND stat_date = '" . $db->getMysqliDbObject()->escape_string($date) . "' " . 
                   "ORDER BY stat_time ASC";
            $results = $db->select($sql);
            $sql = null;
        }
    }
    
    $db->close();
    $db = null;    
    
    if(count($results) > 0) {
        $available_arr = array_fill(0, 24, 0);
        $occupied_arr = array_fill(0, 24, 0);
        $offline_arr = array_fill(0, 24, 0);

        foreach($results as $key => $row) {
            $available_arr[$row['stat_hour']] = $row['available'];
            $occupied_arr[$row['stat_hour']] = $row['occupied'];
            $offline_arr[$row['stat_hour']] = $row['offline'];
        }
        
        $output = new stdClass();
        $output->title = "Daily lab usage report for " . $lab . " on " . $date;        
        
        $series1 = new stdClass();
        $series1->name = "Available";
        $series1->data = $available_arr;
        
        $series2 = new stdClass();
        $series2->name = "Occupied";
        $series2->data = $occupied_arr;
        
        $series3 = new stdClass();
        $series3->name = "Offline";
        $series3->data = $offline_arr;
        
        $output->series = array($series1, $series2, $series3);
        
        $jsonOut = json_encode($output, JSON_NUMERIC_CHECK);
        
        $series3 = null;
        $series2 = null;
        $series1 = null;
        $output = null;
        $offline_arr = null;
        $occupied_arr = null;
        $available_arr = null;
        $results = null;
        
        echo($jsonOut);
    }
} else if($type === 'week') {
    
    $startdate = filter_input(
                    INPUT_GET, 
                    'startdate', 
                    FILTER_VALIDATE_REGEXP, 
                    array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/"))
                 );
    $enddate = filter_input(
                    INPUT_GET, 
                    'enddate', 
                    FILTER_VALIDATE_REGEXP, 
                    array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/"))
                 );
    require_once("../../config.php");
    
    // Check if date is valid
    if(!Util::isDateValid($startdate) || !Util::isDateValid($enddate)) {
        echo(""); exit();
    }
    $db = new Db($env[_APP_ENV]['db']);
    $db->connect();
    $results = array();
    if($lab === "all") {
        $lab = "All labs";
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, sum(available) as available, sum(occupied) as occupied, sum(offline) as offline FROM labstats WHERE stat_date BETWEEN :startdate AND :enddate GROUP BY stat_date, stat_time ORDER BY stat_date ASC, stat_time ASC";            
            $params = array(":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $params = null;
            $sql = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, " . 
                   "sum(available) as available, sum(occupied) as occupied, " .
                   "sum(offline) as offline " . 
                   "FROM labstats " . 
                   "WHERE stat_date BETWEEN '" . $db->getMysqliDbObject()->escape_string($startdate) . "' " . 
                   "AND '" . $db->getMysqliDbObject()->escape_string($enddate) . "' " . 
                   "GROUP BY stat_date, stat_time " . 
                   "ORDER BY stat_date ASC, stat_time ASC";
            $results = $db->select($sql);
            $sql = null;
        }
    } else {
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, available, occupied, offline FROM labstats WHERE lab = :lab AND stat_date BETWEEN :startdate AND :enddate ORDER BY stat_date ASC, stat_time ASC";
            $params = array(":lab" => $lab, ":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $params = null;
            $sql = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, " . 
                   "sum(available) as available, sum(occupied) as occupied, " .
                   "sum(offline) as offline " . 
                   "FROM labstats " . 
                   "WHERE lab = '" . $db->getMysqliDbObject()->escape_string($lab) . "' " .
                   "AND stat_date BETWEEN '" . $db->getMysqliDbObject()->escape_string($startdate) . "' " . 
                   "AND '" . $db->getMysqliDbObject()->escape_string($enddate) . "' " . 
                   "GROUP BY stat_date, stat_time " . 
                   "ORDER BY stat_date ASC, stat_time ASC";            
            $results = $db->select($sql);
            $sql = null;
        }
    }
    
    $db->close();
    $db = null;    
    
    if(count($results) > 0) {
        $occupied_arr = array();
        $available_arr = array();
        $offline_arr = array();

        foreach($results as $key => $row) {
            $occupied_arr[] = $row['occupied'];
            $available_arr[] = $row['available'];
            $offline_arr[] = $row['offline'];            
        }        
        
        $output = new stdClass();
        $output->title = "Weekly lab usage report for " . $lab . " from " . $startdate . " to " . $enddate;        
        
        $series1 = new stdClass();
        $series1->name = "Available";
        $series1->data = $available_arr;
        
        $series2 = new stdClass();
        $series2->name = "Occupied";
        $series2->data = $occupied_arr;
        
        $output->series = array($series1, $series2);
        
        $jsonOut = json_encode($output, JSON_NUMERIC_CHECK);
        
        $series2 = null;
        $series1 = null;
        $output = null;
        $offline_arr = null;
        $occupied_arr = null;
        $available_arr = null;
        $results = null;
        
        echo($jsonOut);
    }
} else if($type === 'month') {
    
    $month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
    $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
    
    if(!checkdate($month, 1, $year) || $year < 2011) {
        $month = null; $year = null;
        echo("");
        exit();
    }
    if($month < 10) {
        $month = "0" . $month;
    }    
    
    $startdate = $year . "-" . $month . "-01"; 
    $enddate = $year . "-" . $month . "-" . cal_days_in_month(CAL_GREGORIAN, intval($month), $year);
    
    require_once("../../config.php");
    
    $db = new Db($env[_APP_ENV]['db']);
    $db->connect();
    $results = array();
    if($lab === "all") {
        $lab = "All Labs";
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, sum(available) as available, sum(occupied) as occupied, sum(offline) as offline FROM labstats WHERE stat_date BETWEEN :startdate AND :enddate GROUP BY stat_date, stat_time ORDER BY stat_date ASC, stat_time ASC";
            $params = array(":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $params = null;
            $sql = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, " . 
                   "sum(available) as available, sum(occupied) as occupied, " .
                   "sum(offline) as offline " . 
                   "FROM labstats " . 
                   "WHERE stat_date BETWEEN '" . $db->getMysqliDbObject()->escape_string($startdate) . "' " . 
                   "AND '" . $db->getMysqliDbObject()->escape_string($enddate) . "' " . 
                   "GROUP BY stat_date, stat_time " . 
                   "ORDER BY stat_date ASC, stat_time ASC";
            $results = $db->select($sql);
            $sql = null;
        }
    } else {
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, available, occupied, offline FROM labstats WHERE lab = :lab AND stat_date BETWEEN :startdate AND :enddate ORDER BY stat_date ASC, stat_time ASC";
            $params = array(":lab" => $lab, ":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $params = null;
            $sql = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, " . 
                   "sum(available) as available, sum(occupied) as occupied, " .
                   "sum(offline) as offline " . 
                   "FROM labstats " . 
                   "WHERE lab = '" . $db->getMysqliDbObject()->escape_string($lab) . "' " . 
                   "AND stat_date BETWEEN '" . $db->getMysqliDbObject()->escape_string($startdate) . "' " . 
                   "AND '" . $db->getMysqliDbObject()->escape_string($enddate) . "' " . 
                   "GROUP BY stat_date, stat_time " . 
                   "ORDER BY stat_date ASC, stat_time ASC";
            $results = $db->select($sql);
            $sql = null;
        }
    }
    
    $db->close();
    $db = null;    
    
    if(count($results) > 0) {
        $occupied_arr = array();
        $available_arr = array();
        $offline_arr = array();

        foreach($results as $key => $row) {
            $occupied_arr[] = $row['occupied'];
            $available_arr[] = $row['available'];
            $offline_arr[] = $row['offline'];            
        }        
        
        $output = new stdClass();
        $output->title = "Monthly lab usage report for " . $lab . " for " . date("F", strtotime($startdate)) . " " . $year;
        
        $series1 = new stdClass();
        $series1->name = "Available";
        $series1->data = $available_arr;
        
        $series2 = new stdClass();
        $series2->name = "Occupied";
        $series2->data = $occupied_arr;
        
        $output->series = array($series1, $series2);
        
        $jsonOut = json_encode($output, JSON_NUMERIC_CHECK);
        
        $series2 = null;
        $series1 = null;
        $output = null;
        $offline_arr = null;
        $occupied_arr = null;
        $available_arr = null;
        $results = null;
        
        echo($jsonOut);
    }
} else if($type === 'custom') {
    
    $startdate = filter_input(INPUT_GET, 'startdate', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/")));
    $enddate = filter_input(INPUT_GET, 'enddate', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/")));
    
    require_once("../../config.php");
    
    // Check if date is valid
    if(!Util::isDateValid($startdate) || !Util::isDateValid($enddate)) {
        echo(""); exit();
    }
    
    $db = new Db($env[_APP_ENV]['db']);
    $db->connect();
    $results = array();
    if($lab === "all") {
        $lab = "All Labs";
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, sum(available) as available, sum(occupied) as occupied, sum(offline) as offline FROM labstats WHERE stat_date BETWEEN :startdate AND :enddate GROUP BY stat_date, stat_time ORDER BY stat_date ASC, stat_time ASC";
            $params = array(":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $params = null;
            $sql = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, " . 
                   "sum(available) as available, sum(occupied) as occupied, " .
                   "sum(offline) as offline " . 
                   "FROM labstats " . 
                   "WHERE stat_date BETWEEN '" . $db->getMysqliDbObject()->escape_string($startdate) . "' " . 
                   "AND '" . $db->getMysqliDbObject()->escape_string($enddate) . "' " . 
                   "GROUP BY stat_date, stat_time " . 
                   "ORDER BY stat_date ASC, stat_time ASC";
            $results = $db->select($sql);
            $sql = null;
        }
    } else {
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, available, occupied, offline FROM labstats WHERE lab = :lab AND stat_date BETWEEN :startdate AND :enddate ORDER BY stat_date ASC, stat_time ASC";
            $params = array(":lab" => $lab, ":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $params = null;
            $sql = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, " . 
                   "sum(available) as available, sum(occupied) as occupied, " .
                   "sum(offline) as offline " . 
                   "FROM labstats " . 
                   "WHERE lab = '" . $db->getMysqliDbObject()->escape_string($lab) . "' " . 
                   "AND stat_date BETWEEN '" . $db->getMysqliDbObject()->escape_string($startdate) . "' " . 
                   "AND '" . $db->getMysqliDbObject()->escape_string($enddate) . "' " . 
                   "GROUP BY stat_date, stat_time " . 
                   "ORDER BY stat_date ASC, stat_time ASC";
            $results = $db->select($sql);
            $sql = null;
        }
    }
    
    $db->close();
    $db = null;    
    
    if(count($results) > 0) {
        $occupied_arr = array();
        $available_arr = array();
        $offline_arr = array();

        foreach($results as $key => $row) {
            $occupied_arr[] = $row['occupied'];
            $available_arr[] = $row['available'];
            $offline_arr[] = $row['offline'];            
        }        
        
        $output = new stdClass();
        $output->title = "Custom lab usage report for " . $lab . " from " . $startdate . " to " . $enddate;  
        
        $series1 = new stdClass();
        $series1->name = "Available";
        $series1->data = $available_arr;
        
        $series2 = new stdClass();
        $series2->name = "Occupied";
        $series2->data = $occupied_arr;
        
        $output->series = array($series1, $series2);
        
        $jsonOut = json_encode($output, JSON_NUMERIC_CHECK);
        
        $series2 = null;
        $series1 = null;
        $output = null;
        $offline_arr = null;
        $occupied_arr = null;
        $available_arr = null;
        $results = null;
        
        echo($jsonOut);
    } else {
        echo("");
    }
} else if($type === "comp_usage") {
    
    $cuoption = filter_input(INPUT_GET, 'cuoption', FILTER_SANITIZE_STRING);
    if($cuoption !== "day" && $cuoption !== "month" && $cuoption !== "range") {
        echo("");
        exit;
    }
    require_once("../../config.php");
    $db = new Db($env[_APP_ENV]['db']);
    $db->connect();
    $results = array();

    if($cuoption === "day") {
        $compname = filter_input(INPUT_GET, 'compname', FILTER_SANITIZE_STRING);
        $date = filter_input(INPUT_GET, 'date', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/")));
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT comp_name, login_date, count(login_time) AS login_count FROM comptrack WHERE comp_name = :comp_name AND login_date = :logindate GROUP BY login_date ORDER BY login_date ASC";
            $params = array(":comp_name" => $compname, ":logindate" => $date);
            $results = $db->select($sql, $params);
            $sql = null;
            $params = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT comp_name, login_date, " . 
                   "count(login_time) AS login_count " . 
                   "FROM comptrack " . 
                   "WHERE comp_name = '" . $db->getMysqliDbObject()->escape_string($compname) . "' " . 
                   "AND login_date = '" . $db->getMysqliDbObject()->escape_string($date) . "' " . 
                   "GROUP BY login_date " . 
                   "ORDER BY login_date ASC";            
            $results = $db->select($sql);
            $sql = null;
        }

        if(count($results) == 0) {
            echo("");
        } else {
            $data_array = array();
            $axis_array = array();
            foreach($results as $key => $row) {
                $data_array[] = $row['login_count'];
                $axis_array[] = substr($row['login_date'], 8);
            }

            $output = new stdClass();
            $output->title = "Computer Usage of " . $compname . " for " . $date;
            $output->xaxis = $axis_array;
            $output->data = $data_array;

            $jsonOut = json_encode($output, JSON_NUMERIC_CHECK);

            $output = null;
            $data_array = null;
            $axis_array = null;
            $results = null;
            echo($jsonOut);            
        }
    } else if($cuoption === "month") {
        $compname = filter_input(INPUT_GET, 'compname', FILTER_SANITIZE_STRING);
        $month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
        $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);

        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT comp_name, login_date, count(login_time) AS login_count FROM comptrack WHERE comp_name = :comp_name AND MONTH(login_date) = :month AND YEAR(login_date) = :year GROUP BY login_date ORDER BY login_date ASC";
            $params = array(":comp_name" => $compname, ":month" => $month, ":year" => $year);
            $results = $db->select($sql, $params);
            $sql = null;
            $params = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT comp_name, login_date, " . 
                   "count(login_time) AS login_count " . 
                   "FROM comptrack " . 
                   "WHERE comp_name = '" . $db->getMysqliDbObject()->escape_string($compname) . "' " . 
                   "AND MONTH(login_date) = " . $month . " " . 
                   "AND YEAR(login_date) = " . $year . " " . 
                   "GROUP BY login_date " . 
                   "ORDER BY login_date ASC";
            $results = $db->select($sql);
            $sql = null;
        }

        if(count($results) == 0) {
            echo("");
        } else {
            $data_array = array();
            $axis_array = array();
            foreach($results as $key => $row) {
                $data_array[] = $row['login_count'];
                $axis_array[] = substr($row['login_date'], 8);
            }

            $output = new stdClass();
            $output->title = "Computer Usage of " . $compname . " for " . $month . "/" . $year;
            $output->xaxis = $axis_array;
            $output->data = $data_array;

            $jsonOut = json_encode($output, JSON_NUMERIC_CHECK);

            $output = null;
            $data_array = null;
            $axis_array = null;
            $results = null;
            echo($jsonOut);            
        }
    } else if($cuoption === "range") {
        $compname = filter_input(INPUT_GET, 'compname', FILTER_SANITIZE_STRING);
        $from = filter_input(INPUT_GET, 'from', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/")));
        $to = filter_input(INPUT_GET, 'to', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/")));

        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT comp_name, login_date, count(login_time) AS login_count FROM comptrack WHERE comp_name = :comp_name AND login_date BETWEEN :from AND :to GROUP BY login_date ORDER BY login_date ASC";
            $params = array(":comp_name" => $compname, ":from" => $from, ":to" => $to);
            $results = $db->select($sql, $params);
            $sql = null;
            $params = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT comp_name, login_date, " . 
                   "count(login_time) AS login_count " . 
                   "FROM comptrack " . 
                   "WHERE comp_name = '" . $db->getMysqliDbObject()->escape_string($compname) . "' " . 
                   "AND login_date " . 
                   "BETWEEN '" . $db->getMysqliDbObject()->escape_string($from) . "' " . 
                   "AND '" . $db->getMysqliDbObject()->escape_string($to) . "' " . 
                   "GROUP BY login_date " . 
                   "ORDER BY login_date ASC";
            $results = $db->select($sql);
            $sql = null;
        }

        if(count($results) == 0) {
            echo("");
        } else {
            $data_array = array();
            $axis_array = array();
            foreach($results as $key => $row) {
                $data_array[] = $row['login_count'];
                $axis_array[] = substr($row['login_date'], 5);
            }

            $output = new stdClass();
            $output->title = "Computer Usage of " . $compname . " between " . $from . " and " . $to;
            $output->xaxis = $axis_array;
            $output->data = $data_array;

            $jsonOut = json_encode($output, JSON_NUMERIC_CHECK);

            $output = null;
            $data_array = null;
            $axis_array = null;
            $results = null;
            echo($jsonOut);
        }
    }
    
    $results = null;
    $db->close();
    $db = null;    
    
}


?>
