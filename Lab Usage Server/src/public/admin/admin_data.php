<?php
/*
 * type = lab or comp
 * op = list, add, edit, delete
 *  - list : sort by,  (GET)
 *  - add : info (POST)
 *  - edit : info (POST)
 *  - del : info (POST)
 */

$type = filter_input(
            INPUT_GET, 
            'type',  
            FILTER_VALIDATE_REGEXP, 
            array("options"=>array("regexp" => "/^[a-z]+$/"))
        );

// For labs
if($type === "lab") {
    
    $op = filter_input(
            INPUT_GET, 
            'op',  
            FILTER_VALIDATE_REGEXP, 
            array("options"=>array("regexp" => "/^[a-z]+$/"))
        );
    
    if($op === "list") {
        require_once("../../config.php");
        $db = new Db($env[_APP_ENV]['db']);
        $db->connect();

        $lab = new Lab();
        $labs = $lab->find($db, 
                           array("order" => array("name" => "asc")));
        $output = "";
        if(count($labs) > 0) {
            $count = 1;
            foreach($labs as $l) {
                $output .= "<tr id=\"rl_" . $l->id . "\">\n";
                $output .= "<td>" . $count . "</td>\n";
                $output .= "<td>" . $l->name . "</td>\n";
                $output .= "<td>" . $l->title . "</td>\n";
                $output .= "<td>" . $l->description . "</td>\n";
                $output .= "<td style=\"white-space:nowrap\"><button class=\"btn\" style=\"padding: 3px 4px; margin-right: 5px;\" title=\"Edit lab information\" onclick=\"editLab('" . $l->id . "');\"><i class=\"icon-pencil\"></i></button><button class=\"btn\" style=\"padding: 3px 4px;\" title=\"Delete this lab\" onclick=\"confirmDeleteLab('" . $l->id . "');\"><i class=\"icon-trash\"></i></button></td>\n";
                $output .= "</tr>\n";
                $count++;
            }
            $count = null;   
        }
        $labs = null;
        $lab = null;
        $db->close();
        $db = null;
        
        print($output);
        $output = null;
        
    } else if($op === "save") {
        
        $labid = filter_input(
                    INPUT_POST, 
                    'labid',
                    FILTER_VALIDATE_REGEXP, 
                    array("options"=>array("regexp" => "/^[0-9]+$/"))
                );
        
        $name = filter_input(
                    INPUT_POST, 
                    'name',
                    FILTER_VALIDATE_REGEXP, 
                    array("options"=>array("regexp" => "/^[a-zA-Z0-9_\-\.]+$/"))
                );
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $desc = filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_STRING);
        
        if(!empty($name)) {
            require_once("../../config.php");
            $db = new Db($env[_APP_ENV]['db']);
            $jsonOut = "";
            $db->connect();

            $lab = new Lab();
            $lab->name = $name;
            $lab->title = $title;
            $lab->description = $desc;
            if(!empty($labid)) {
                $lab->id = intval($labid);
            }
            try {
                $lab = $lab->save($db);
            } catch(Exception $e) {
                $output = new stdClass();
                $output->msgtype = "error";
                $output->message = "Error occured while saving Lab '" . $name . "'. Please try again.";
                $jsonOut = json_encode($output);
                $output = null;
            }
            
            $db->close();
            $db = null;
            
            if(!empty($jsonOut)) {
                print($jsonOut);
                $jsonOut = null;
            } else {
                $output = new stdClass();
                $output->msgtype = "success";
                $output->message = "Lab '" . $name . "' saved successfully.";
                $output->labid = $lab->id;
                $jsonOut = json_encode($output, JSON_NUMERIC_CHECK);
                print($jsonOut);
                $jsonOut = null;
                $output = null;
            }
            
            $lab = null;
        } else {
            $output = new stdClass();
            $output->msgtype = "error";
            $output->message = "Error occured while saving Lab. Name can not be empty. Please try again.";
            $jsonOut = json_encode($output);
            print($jsonOut);
            $jsonOut = null;
            $output = null;
        }
    } else if($op === "del") {
 
        $labid = filter_input(
                    INPUT_POST, 
                    'labid',
                    FILTER_VALIDATE_REGEXP, 
                    array("options"=>array("regexp" => "/^[0-9]+$/"))
                );
        
        $name = filter_input(
                    INPUT_POST, 
                    'name',
                    FILTER_VALIDATE_REGEXP, 
                    array("options"=>array("regexp" => "/^[a-zA-Z0-9_\-\.\s]+$/"))
                );        
        
        if(!empty($labid) && !empty($name)) {
            require_once("../../config.php");
            $db = new Db($env[_APP_ENV]['db']);
            $jsonOut = "";
            $db->connect();

            $lab = new Lab();
            $lab->id = $labid;
            $lab->name = $name;
            $result = false;
            
            try {
                $result = $lab->delete($db);
            } catch(Exception $e) {
                $output = new stdClass();
                $output->msgtype = "error";
                $output->message = "Error occured while deleting Lab " . $name . ". Please try again.";
                $jsonOut = json_encode($output);
                $output = null;
            }
            
            $db->close();
            $db = null;
            
            if( $result === true) {
                $output = new stdClass();
                $output->msgtype = "success";
                $output->message = "Lab " . $name . " deleted successfully.";
                $jsonOut = json_encode($output, JSON_NUMERIC_CHECK);
                print($jsonOut);
                $jsonOut = null;
                $output = null;
            } else {
                $output = new stdClass();
                $output->msgtype = "error";
                $output->message = "Error occured while deleting Lab " . $name . ". Contact your system administrator.";
                $jsonOut = json_encode($output);
                print($jsonOut);
                $output = null;
            }
            
            $lab = null;
        } else {
            $output = new stdClass();
            $output->msgtype = "error";
            $output->message = "Error occured while deleting Lab. Name can not be empty. Please try again.";
            $jsonOut = json_encode($output);
            print($jsonOut);
            $jsonOut = null;
            $output = null;
        }
    }
    
    
    
} else if($type === "comp") {
    $op = filter_input(
            INPUT_GET, 
            'op',  
            FILTER_VALIDATE_REGEXP, 
            array("options"=>array("regexp" => "/^[a-z]+$/"))
        );
    
    if($op === "list") {        
        $sort_by = filter_input(
                        INPUT_GET, 
                        'sort_by',  
                        FILTER_VALIDATE_REGEXP, 
                        array("options"=>array("regexp" => "/^[a-z]+$/"))
                    );
        $sort_type = filter_input(
                        INPUT_GET, 
                        'sort_type',  
                        FILTER_VALIDATE_REGEXP, 
                        array("options"=>array("regexp" => "/^[a-z]+$/"))
                    );
        $limit = intval(filter_input(
                        INPUT_GET, 
                        'limit',  
                        FILTER_VALIDATE_REGEXP, 
                        array("options"=>array("regexp" => "/^[0-9]+$/"))
                    ));        
        $offset = intval(filter_input(
                        INPUT_GET, 
                        'offset',  
                        FILTER_VALIDATE_REGEXP, 
                        array("options"=>array("regexp" => "/^[0-9]+$/"))
                    ));
        $filter = intval(filter_input(
                        INPUT_GET, 
                        'filter',  
                        FILTER_VALIDATE_REGEXP, 
                        array("options"=>array("regexp" => "/^[0-9]+$/"))
                    ));
        
        if($sort_by !== "name" && $sort_by !== "mstatus" && $sort_by !== "lab") {
            $op = null;
            $sort_by = null;
            $sort_type = null;
            $limit = null;
            $offset = null;
            print("");
            return;
        }
        
        if($sort_type !== "asc" && $sort_type !== "desc") {
            $op = null;
            $sort_by = null;
            $sort_type = null;
            $limit = null;
            $offset = null;
            print("");
            return;
        }
        
        require_once("../../config.php");
        $db = new Db($env[_APP_ENV]['db']);
        $db->connect();

        $computer = new Computer();
        $lab = new Lab();
        $allLabs = $lab->find($db);
        
        $total = 0;
                
        if($filter != 0) {
            $computers = $computer->find($db, array("conditions" => array("lab_id" => $filter),
                                                    "order" => array($sort_by => $sort_type), 
                                                    "limit" => $limit,
                                                    "offset" => $offset));
            $total = 0;
            if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                $records = $db->select("SELECT count(id) AS comp_count FROM computer WHERE lab_id = :lab_id", array(":lab_id" => $filter));
                foreach($records as $row) {
                    $total = intval($row['comp_count']);
                    $row = null;
                }
                $records = null;
            } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                $records = $db->select("SELECT count(id) AS comp_count FROM computer WHERE lab_id = " . intval($filter));
                foreach($records as $row) {
                    $total = intval($row['comp_count']);
                    $row = null;
                }
                $records = null;
            }
            
        } else {
            $computers = $computer->find($db, array("order" => array($sort_by => $sort_type), 
                                                    "limit" => $limit,
                                                    "offset" => $offset));
            $total = $computer->getTotalCount($db);
        }
        $html = "";
        $count = $offset + 1;
        if(count($computers) > 0) {
            foreach($computers as $c) {
                $html .= "<tr id=\"c_" . $c->id . "\">\n";
                $html .= "<td>" . $count . "</td>\n";
                $html .= "<td>" . $c->name . "</td>\n";
                $html .= "<td>" . $labCompStatusArray[$c->mstatus] . "</td>\n";
                foreach($allLabs as $l) {
                    if($l->id == $c->lab_id) {
                        if(empty($l->title)) {
                            $html .= "<td>" . $l->name . "</td>\n";
                        } else {
                            $html .= "<td>" . $l->title . "</td>\n";
                        }
                    }
                    $l = null;
                }
                $html .= "<td style=\"white-space:nowrap\"><button class=\"btn\" style=\"padding: 3px 4px; margin-right: 5px;\" title=\"Edit lab information\" onclick=\"editComp('" . $c->id . "');\"><i class=\"icon-pencil\"></i></button><button class=\"btn\" style=\"padding: 3px 4px;\" title=\"Delete this computer\" onclick=\"confirmDeleteComp('" . $c->id . "');\"><i class=\"icon-trash\"></i></button></td>\n";
                $html .= "</tr>\n";
                $count++;
                $c = null;
            }   
        }
        $count--;
        $allLabs = null;
        $lab = null;
        $computers = null;
        $computer = null;
        $db->close();
        $db = null;
        
        $output = new stdClass();
        $output->html = $html;
        $output->total = $total;
        $output->table_text = "Showing " . ($offset + 1) . " to " . $count . " of " . $total . " entries";
        
        $jsonOut = json_encode($output);        
        
        print($jsonOut);
        $output = null;
        $count = null;
        
    } else if($op === "save") {
                
        $compid = intval(filter_input(
                        INPUT_POST, 
                        'compid',  
                        FILTER_VALIDATE_REGEXP, 
                        array("options"=>array("regexp" => "/^[0-9]+$/"))
                    ));        
        $labid = intval(filter_input(
                        INPUT_POST, 
                        'labid',  
                        FILTER_VALIDATE_REGEXP, 
                        array("options"=>array("regexp" => "/^[0-9]+$/"))
                    ));
        $name = filter_input(INPUT_POST, 
                    'name',
                    FILTER_VALIDATE_REGEXP, 
                    array("options"=>array("regexp" => "/^[a-zA-Z0-9_\-\.\s]+$/"))
                );
        $mstatus = intval(filter_input(
                        INPUT_POST, 
                        'mstatus',  
                        FILTER_VALIDATE_REGEXP, 
                        array("options"=>array("regexp" => "/^[0-9]+$/"))
                    ));
        
        if(!empty($name)) {
            require_once("../../config.php");
            $db = new Db($env[_APP_ENV]['db']);
            $jsonOut = "";
            $db->connect();

            $comp = new Computer();
            
            $comp->name = $name;
            $comp->mstatus = $mstatus;
            $comp->lab_id = $labid;            
            
            if(!empty($compid)) {
                $comp->id = $compid;
            }
            
            try {
                $comp = $comp->save($db);
            } catch(Exception $e) {
                $output = new stdClass();
                $output->msgtype = "error";
                $output->message = "Error occured while saving computer '" . $name . "'. Please try again.";
                $jsonOut = json_encode($output);
                $output = null;
            }
            
            $db->close();
            $db = null;
            
            if(!empty($jsonOut)) {
                print($jsonOut);
                $jsonOut = null;
            } else {
                $output = new stdClass();
                $output->msgtype = "success";
                $output->message = "Computer '" . $name . "' saved successfully.";
                $output->compid = intval($comp->id);
                $jsonOut = json_encode($output, JSON_NUMERIC_CHECK);
                print($jsonOut);
                $jsonOut = null;
                $output = null;
            }
            
            $comp = null;
        } else {
            $output = new stdClass();
            $output->msgtype = "error";
            $output->message = "Error occured while saving computer. The name is incorrect. Please try again.";
            $jsonOut = json_encode($output);
            $output = null;
            $jsonOut = null;
        }
    } else if($op === "del") {
        
        $compid = intval(filter_input(
                        INPUT_POST, 
                        'compid',  
                        FILTER_VALIDATE_REGEXP, 
                        array("options"=>array("regexp" => "/^[0-9]+$/"))
                    ));
        
        $name = filter_input(INPUT_POST, 
                    'name',
                    FILTER_VALIDATE_REGEXP, 
                    array("options"=>array("regexp" => "/^[a-zA-Z0-9_\-\.\s]+$/"))
                );
        
        if(!empty($compid) && !empty($name)) {
            require_once("../../config.php");
            $db = new Db($env[_APP_ENV]['db']);
            $jsonOut = "";
            $db->connect();

            $computer = new Computer();
            $computer->id = $compid;
            $computer->name = $name;
            
            $result = false;
            
            try {
                $result = $computer->delete($db);
            } catch(Exception $e) {
                $output = new stdClass();
                $output->msgtype = "error";
                $output->message = "Error occured while deleting Computer " . $name . ". Please try again.";
                $jsonOut = json_encode($output);
                $output = null;
            }
            
            $db->close();
            $db = null;
            
            if( $result === true) {
                $output = new stdClass();
                $output->msgtype = "success";
                $output->message = "Computer " . $name . " deleted successfully.";
                $jsonOut = json_encode($output, JSON_NUMERIC_CHECK);
                print($jsonOut);
                $jsonOut = null;
                $output = null;
            } else {
                $output = new stdClass();
                $output->msgtype = "error";
                $output->message = "Error occured while deleting Computer " . $name . ". Contact your system administrator.";
                $jsonOut = json_encode($output);
                print($jsonOut);
                $output = null;
            }
            
            $lab = null;
        } else {
            $output = new stdClass();
            $output->msgtype = "error";
            $output->message = "Error occured while saving computer. The name is incorrect. Please try again.";
            $jsonOut = json_encode($output);
            $output = null;
            $jsonOut = null;
        }
    }
}
$type = null;
?>
