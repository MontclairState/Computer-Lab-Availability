<?php
$lab = filter_input(
            INPUT_GET, 
            'lab', 
            FILTER_VALIDATE_REGEXP, 
            array("options"=>array("regexp" => "/^[a-zA-Z0-9_\-\.]+$/"))
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
        $date = null;
        $type = null;
        $lab = null;
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
            $sql = "SELECT stat_date, HOUR(stat_time) as stat_hour, " . 
                   "sum(available) as available, " . 
                   "sum(occupied) as occupied, sum(offline) as offline " . 
                   "FROM labstats " . 
                   "WHERE stat_date = '" . $db->getMysqliDbObject()->escape_string($date) . "' " . 
                   "GROUP BY stat_date, stat_time " . 
                   "ORDER BY stat_time ASC";
            $results = $db->select($sql);
            $sql = null;
        }
    } else {
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT HOUR(stat_time) as stat_hour, available, occupied, offline FROM labstats WHERE lab = :lab AND stat_date = :stat_date ORDER BY stat_time ASC";    
            $params = array(":lab" => $lab, ":stat_date" => $date);
            $results = $db->select($sql, $params);
            $sql = null;
            $params = null;
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
    
    if(count($results) > 0) {
        
        // Report generation
        require_once(_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Lab Usage Web Application")
                    ->setLastModifiedBy("Lab Usage Web Application")
                    ->setTitle("Daily lab usage report for " . $lab . " on " . $date)
                    ->setSubject("Daily lab usage report for " . $lab . " on " . $date)
                    ->setDescription("Daily lab usage report for " . $lab . " on " . $date);

        // Title
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Lab')
                    ->setCellValue('B1', 'Hour of the day')
                    ->setCellValue('C1', 'Available')
                    ->setCellValue('D1', 'Occupied')
                    ->setCellValue('E1', 'Offine/Maintenance')
                    ->setCellValue('F1', 'Total');
        
        $count = 2;
        foreach($results as $key => $row) {
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $count, $lab)
                        ->setCellValue('B' . $count, $row['stat_hour'])
                        ->setCellValue('C' . $count, $row['available'])
                        ->setCellValue('D' . $count, $row['occupied'])
                        ->setCellValue('E' . $count, $row['offline'])
                        ->setCellValue('F' . $count, intval($row['available']) + intval($row['occupied']) + intval($row['offline']));
            $count++;
        }
        $count = null;
        
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Daily report');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="daily-report.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        $objWriter = null;
    } else {
        header("Content-Type: text/html; charset=UTF-8");
        print("<html><head><title>Lab Usage Administration</title></head><body><h2>No data available.</h2><p>Report cannot be generated for current input. Please change the input and try again.</p><p><a href=\"report.php?lab=" . $lab . "&type=" . $type . "\">Go back</a></p></body></html>");
    }
    
    $results = null;
    $db->close();
    $db = null;
    
} else if($type === 'week') {
    
    $startdate = filter_input(INPUT_GET, 'startdate', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/")));
    $enddate = filter_input(INPUT_GET, 'enddate', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/")));
    require_once("../../config.php");
    
    // Check if date is valid
    if(!Util::isDateValid($startdate) || !Util::isDateValid($enddate)) {
        $startdate = null;
        $enddate = null;
        $type = null;
        $lab = null;        
        echo(""); exit();
    }
    $db = new Db($env[_APP_ENV]['db']);
    $db->connect();
    $results = array();
    if($lab === "all") {
        $lab = "All labs";
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') AS stat_hour, sum(available) as available, sum(occupied) as occupied, sum(offline) as offline FROM labstats WHERE stat_date BETWEEN :startdate AND :enddate GROUP BY stat_date, stat_time ORDER BY stat_date ASC, stat_time ASC";
            $params = array(":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $sql = null;
            $params = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') as stat_hour, " . 
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
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') AS stat_hour, available, occupied, offline FROM labstats WHERE lab = :lab AND stat_date BETWEEN :startdate AND :enddate ORDER BY stat_date ASC, stat_time ASC";
            $params = array(":lab" => $lab, ":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $sql = null;
            $params = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') as stat_hour, " . 
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
    
    if(count($results) > 0) {        
        
        // Report generation
        require_once(_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Lab Usage Web Application")
                    ->setLastModifiedBy("Lab Usage Web Application")
                    ->setTitle("Weekly lab usage report for " . $lab . " from " . $startdate . " to " . $enddate)
                    ->setSubject("Weekly lab usage report for " . $lab . " from " . $startdate . " to " . $enddate)
                    ->setDescription("Weekly lab usage report for " . $lab . " from " . $startdate . " to " . $enddate);

        // Title
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Lab')
                    ->setCellValue('B1', 'Date')
                    ->setCellValue('C1', 'Hour of the day')
                    ->setCellValue('D1', 'Available')
                    ->setCellValue('E1', 'Occupied')
                    ->setCellValue('F1', 'Offine/Maintenance')
                    ->setCellValue('G1', 'Total');
        
        $count = 2;
        foreach($results as $key => $row) {
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $count, $lab)
                        ->setCellValue('B' . $count, $row['stat_date'])
                        ->setCellValue('C' . $count, $row['stat_hour'])
                        ->setCellValue('D' . $count, $row['available'])
                        ->setCellValue('E' . $count, $row['occupied'])
                        ->setCellValue('F' . $count, $row['offline'])
                        ->setCellValue('G' . $count, intval($row['available']) + intval($row['occupied']) + intval($row['offline']));
            $count++;
        }
        $count = null;
        
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Weekly report');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="weekly-report.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        $objWriter = null;        
    } else {
        header("Content-Type: text/html; charset=UTF-8");
        print("<html><head><title>Lab Usage Administration</title></head><body><h2>No data available.</h2><p>Report cannot be generated for current input. Please change the input and try again.</p><p><a href=\"report.php?lab=" . $lab . "&type=" . $type . "\">Go back</a></p></body></html>");
    }
    $results = null;
    $db->close();
    $db = null;
    exit;
    
} else if($type === 'month') {
    
    $month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
    $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
    
    if(!checkdate($month, 1, $year) || $year < 2011) {
        $month = null; 
        $year = null;
        $type = null;
        $lab = null;        
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
        $lab = "All labs";
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') AS stat_hour, sum(available) as available, sum(occupied) as occupied, sum(offline) as offline FROM labstats WHERE stat_date BETWEEN :startdate AND :enddate GROUP BY stat_date, stat_time ORDER BY stat_date ASC, stat_time ASC";
            $params = array(":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $sql = null;
            $params = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') as stat_hour, " . 
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
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') AS stat_hour, available, occupied, offline FROM labstats WHERE lab = :lab AND stat_date BETWEEN :startdate AND :enddate ORDER BY stat_date ASC, stat_time ASC";
            $params = array(":lab" => $lab, ":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $sql = null;
            $params = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') as stat_hour, " . 
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
    if(count($results) > 0) {
        // Report generation
        require_once(_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Lab Usage Web Application")
                    ->setLastModifiedBy("Lab Usage Web Application")
                    ->setTitle("Monthly lab usage report for " . $lab . " for " . date("F", strtotime($startdate)) . " " . $year)
                    ->setSubject("Monthly lab usage report for " . $lab . " for " . date("F", strtotime($startdate)) . " " . $year)
                    ->setDescription("Monthly lab usage report for " . $lab . "  for " . date("F", strtotime($startdate)) . " " . $year);

        // Title
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Lab')
                    ->setCellValue('B1', 'Date')
                    ->setCellValue('C1', 'Hour of the day')
                    ->setCellValue('D1', 'Available')
                    ->setCellValue('E1', 'Occupied')
                    ->setCellValue('F1', 'Offine/Maintenance')
                    ->setCellValue('G1', 'Total');
        
        $count = 2;
        foreach($results as $key => $row) {
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $count, $lab)
                        ->setCellValue('B' . $count, $row['stat_date'])
                        ->setCellValue('C' . $count, $row['stat_hour'])
                        ->setCellValue('D' . $count, $row['available'])
                        ->setCellValue('E' . $count, $row['occupied'])
                        ->setCellValue('F' . $count, $row['offline'])
                        ->setCellValue('G' . $count, intval($row['available']) + intval($row['occupied']) + intval($row['offline']));
            $count++;
        }
        $count = null;
        
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Monthly report');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="monthly-report.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        $objWriter = null;
    } else {
        header("Content-Type: text/html; charset=UTF-8");
        print("<html><head><title>Lab Usage Administration</title></head><body><h2>No data available.</h2><p>Report cannot be generated for current input. Please change the input and try again.</p><p><a href=\"report.php?lab=" . $lab . "&type=" . $type . "\">Go back</a></p></body></html>");
    }
    $startdate = null;
    $enddate = null;
    $month = null; 
    $year = null;
    $results = null;
    $db->close();
    $db = null;    
} else if($type === 'custom') {
    
    $startdate = filter_input(INPUT_GET, 'startdate', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/")));
    $enddate = filter_input(INPUT_GET, 'enddate', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp" => "/^(\d{4})-(\d{2})-(\d{2})$/")));
    
    require_once("../../config.php");
    
    // Check if date is valid
    if(!Util::isDateValid($startdate) || !Util::isDateValid($enddate)) {
        $startdate = null;
        $enddate = null;        
        $type = null;
        $lab = null;
        exit();
    }
    
    $db = new Db($env[_APP_ENV]['db']);
    $db->connect();
    $results = array();
    if($lab === "all") {
        $lab = "All labs";        
        if( $db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') AS stat_hour, sum(available) as available, sum(occupied) as occupied, sum(offline) as offline FROM labstats WHERE stat_date BETWEEN :startdate AND :enddate GROUP BY stat_date, stat_time ORDER BY stat_date ASC, stat_time ASC";
            $params = array(":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $sql = null;
            $params = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') as stat_hour, " . 
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
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') AS stat_hour, available, occupied, offline FROM labstats WHERE lab = :lab AND stat_date BETWEEN :startdate AND :enddate ORDER BY stat_date ASC, stat_time ASC";
            $params = array(":lab" => $lab, ":startdate" => $startdate, ":enddate" => $enddate);
            $results = $db->select($sql, $params);
            $sql = null;
            $params = null;
        } else if( $db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
            $sql = "SELECT stat_date, DATE_FORMAT(stat_time, '%h %p') as stat_hour, " . 
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
    
    if(count($results) > 0) {        
        
        // Report generation
        require_once(_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Lab Usage Web Application")
                    ->setLastModifiedBy("Lab Usage Web Application")
                    ->setTitle("Custom lab usage report for " . $lab . " from " . $startdate . " to " . $enddate)
                    ->setSubject("Custom lab usage report for " . $lab . " from " . $startdate . " to " . $enddate)
                    ->setDescription("Custom lab usage report for " . $lab . " from " . $startdate . " to " . $enddate);

        // Title
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Lab')
                    ->setCellValue('B1', 'Date')
                    ->setCellValue('C1', 'Hour of the day')
                    ->setCellValue('D1', 'Available')
                    ->setCellValue('E1', 'Occupied')
                    ->setCellValue('F1', 'Offine/Maintenance')
                    ->setCellValue('G1', 'Total');
        
        $count = 2;
        foreach($results as $key => $row) {
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $count, $lab)
                        ->setCellValue('B' . $count, $row['stat_date'])
                        ->setCellValue('C' . $count, $row['stat_hour'])
                        ->setCellValue('D' . $count, $row['available'])
                        ->setCellValue('E' . $count, $row['occupied'])
                        ->setCellValue('F' . $count, $row['offline'])
                        ->setCellValue('G' . $count, intval($row['available']) + intval($row['occupied']) + intval($row['offline']));
            $count++;
        }
        $count = null;
        
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Custom report');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="custom-report.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        $objWriter = null;
    } else {
        header("Content-Type: text/html; charset=UTF-8");
        print("<html><head><title>Lab Usage Administration</title></head><body><h2>No data available.</h2><p>Report cannot be generated for current input. Please change the input and try again.</p><p><a href=\"report.php?lab=" . $lab . "&type=" . $type . "\">Go back</a></p></body></html>");
    }
    $startdate = null;
    $enddate = null;
    $results = null;
    $db->close();
    $db = null; 
    
} else if($type === "comp_usage") {
    
    $cuoption = filter_input(INPUT_GET, 'cuoption', FILTER_SANITIZE_STRING);
    if($cuoption !== "day" && $cuoption !== "month" && $cuoption !== "range") {
        $cuoption = null;
        $type = null;
        $lab = null;        
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
        
        if(count($results) > 0) {
            
            // Report generation
            require_once(_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'PHPExcel.php');

            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();

            // Set document properties
            $objPHPExcel->getProperties()->setCreator("Lab Usage Web Application")
                        ->setLastModifiedBy("Lab Usage Web Application")
                        ->setTitle("Computer Usage of " . $compname . " for " . $date)
                        ->setSubject("Computer Usage of " . $compname . " for " . $date)
                        ->setDescription("Computer Usage of " . $compname . " for " . $date);

            // Title
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'Computer name')
                        ->setCellValue('B1', 'Date')
                        ->setCellValue('C1', 'Usage frequency');

            $count = 2;
            $totalUsage = 0;
            foreach($results as $key => $row) {
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $count, $compname)
                            ->setCellValue('B' . $count, $row['login_date'])
                            ->setCellValue('C' . $count, $row['login_count']);
                $totalUsage = $totalUsage + intval($row['login_count']);
                $count++;
            }
            $count++;
            $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $count, "Total # of times used:")
                            ->setCellValue('B' . $count, "")
                            ->setCellValue('C' . $count, $totalUsage);
            
            $totalUsage = null;
            $count = null;

            // Rename worksheet
            $objPHPExcel->getActiveSheet()->setTitle('Computer Usage');

            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);

            // Redirect output to a client’s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="computer-usage-report.xlsx"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            $objWriter = null;
           
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            print("<html><head><title>Lab Usage Administration</title></head><body><h2>No data available.</h2><p>Report cannot be generated for current input. Please change the input and try again.</p><p><a href=\"report.php?lab=" . $lab . "&type=" . $type . "\">Go back</a></p></body></html>");
        }
        
        $compname = null;
        $date = null;
        
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
        
        if(count($results) > 0) {
            
            // Report generation
            require_once(_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'PHPExcel.php');

            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();

            // Set document properties
            $objPHPExcel->getProperties()->setCreator("Lab Usage Web Application")
                        ->setLastModifiedBy("Lab Usage Web Application")
                        ->setTitle("Computer Usage of " . $compname . " for " . $month . "/" . $year)
                        ->setSubject("Computer Usage of " . $compname . " for " . $month . "/" . $year)
                        ->setDescription("Computer Usage of " . $compname . " for " . $month . "/" . $year);

            // Title
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'Computer name')
                        ->setCellValue('B1', 'Date')
                        ->setCellValue('C1', 'Usage frequency');

            $count = 2;
            $totalUsage = 0;
            foreach($results as $key => $row) {
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $count, $compname)
                            ->setCellValue('B' . $count, $row['login_date'])
                            ->setCellValue('C' . $count, $row['login_count']);
                $totalUsage = $totalUsage + intval($row['login_count']);
                $count++;
            }
            $count++;
            $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $count, "Total # of times used:")
                            ->setCellValue('B' . $count, "")
                            ->setCellValue('C' . $count, $totalUsage);
            
            $totalUsage = null;            
            $count = null;

            // Rename worksheet
            $objPHPExcel->getActiveSheet()->setTitle('Computer Usage');

            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);

            // Redirect output to a client’s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="computer-usage-report.xlsx"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            $objWriter = null;
           
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            print("<html><head><title>Lab Usage Administration</title></head><body><h2>No data available.</h2><p>Report cannot be generated for current input. Please change the input and try again.</p><p><a href=\"report.php?lab=" . $lab . "&type=" . $type . "\">Go back</a></p></body></html>");
        }
        
        $compname = null;
        $month = null;
        $year = null;
        
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

        if(count($results) > 0) {
            
            // Report generation
            require_once(_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'PHPExcel.php');

            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();

            // Set document properties
            $objPHPExcel->getProperties()->setCreator("Lab Usage Web Application")
                        ->setLastModifiedBy("Lab Usage Web Application")
                        ->setTitle("Computer Usage of " . $compname . " between " . $from . " and " . $to)
                        ->setSubject("Computer Usage of " . $compname . " between " . $from . " and " . $to)
                        ->setDescription("Computer Usage of " . $compname . " between " . $from . " and " . $to);

            // Title
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'Computer name')
                        ->setCellValue('B1', 'Date')
                        ->setCellValue('C1', 'Usage frequency');

            $count = 2;
            $totalUsage = 0;
            foreach($results as $key => $row) {
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $count, $compname)
                            ->setCellValue('B' . $count, $row['login_date'])
                            ->setCellValue('C' . $count, $row['login_count']);
                $totalUsage = $totalUsage + intval($row['login_count']);
                $count++;
            }
            $count++;
            $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $count, "Total # of times used:")
                            ->setCellValue('B' . $count, "")
                            ->setCellValue('C' . $count, $totalUsage);
            
            $totalUsage = null;
            $count = null;

            // Rename worksheet
            $objPHPExcel->getActiveSheet()->setTitle('Computer Usage');

            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);

            // Redirect output to a client’s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="computer-usage-report.xlsx"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            $objWriter = null;
           
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            print("<html><head><title>Lab Usage Administration</title></head><body><h2>No data available.</h2><p>Report cannot be generated for current input. Please change the input and try again.</p><p><a href=\"report.php?lab=" . $lab . "&type=" . $type . "\">Go back</a></p></body></html>");
        }
        
        $compname = null;
        $from = null;
        $to = null;
    }
    
    $results = null;
    $db->close();
    $db = null;
}

$type = null;
$lab = null;
?>
