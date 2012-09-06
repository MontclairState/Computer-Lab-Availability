<?php

require_once("../config.php");

$db = new Db($env[_APP_ENV]['db']);
$db->connect();
$comp = new Computer();
$compList = $comp->findWithLab($db);

$labs = array();

foreach($compList as $comp) {
    
    if(!key_exists($comp->labObj->name, $labs)) {
        $labs[$comp->labObj->name] = array(
            "name" => $comp->labObj->name,
            "title" => $comp->labObj->title,
            "label" => $comp->labObj->title,
            "description" => $comp->labObj->description,
            "available" => 0,
            "occupied" => 0,
            "maintenance" => 0,
            "total" => 0,
            "data" => array("available" => "", 
                            "occupied" => "",
                            "maintenance" => "")
        );
    }
    
    if($comp->mstatus == 0) { // Available
        $labs[$comp->labObj->name]["available"] = 
                $labs[$comp->labObj->name]["available"] + 1;
        $labs[$comp->labObj->name]["data"]["available"] = 
                $labs[$comp->labObj->name]["data"]["available"] .
                $comp->name . ", ";
    }
    
    if($comp->mstatus == 1) { // Occupied
        $labs[$comp->labObj->name]["occupied"] = 
                $labs[$comp->labObj->name]["occupied"] + 1;
        $labs[$comp->labObj->name]["data"]["occupied"] = 
                $labs[$comp->labObj->name]["data"]["occupied"] .
                $comp->name . ", ";        
    }
    
    if($comp->mstatus == 2) { // Maintenance
        $labs[$comp->labObj->name]["maintenance"] = 
                $labs[$comp->labObj->name]["maintenance"] + 1;
        $labs[$comp->labObj->name]["data"]["maintenance"] = 
                $labs[$comp->labObj->name]["data"]["maintenance"] .
                $comp->name . ", ";        
    }
    
     $labs[$comp->labObj->name]["total"] = 
             $labs[$comp->labObj->name]["total"] + 1;
     
    
     $comp = null;
}

// Encoding to JSON
$callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_STRING);
if( !empty($callback)) {
    print($callback."(".json_encode($labs).");");
} else {
    print(json_encode($labs));
}

$labs = null;
$compList = null;
$comp = null;
$db->close();
$db = null;
