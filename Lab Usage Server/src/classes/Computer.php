<?php

class Computer {
    
    public $id;
    public $name; // Required
    public $mstatus; // Required - Default: 1 (Occupied)
    public $lab; // Required
    public $lab_id; 
    
    public $labObj = null;
 
    public function __construct($name = "", $mstatus = "", $lab = "", $lab_id = "") {
        if(!empty($name)) {
            $this->name = $name;
        }
        if(!empty($mstatus)) {
            $this->mstatus = $mstatus;
        }
        if(!empty($lab)) {
            $this->lab = $lab;
        }
        if(!empty($lab_id)) {
            $this->lab_id = $lab_id;
        }
    }
    
    public function save(Db &$db) {
        if($db instanceof Db) {
            if(empty($this->id)) { // If new computer, then insert                
                if(empty($this->name) || empty($this->lab_id) || !is_int($this->lab_id) ) {
                    Log::error("Error saving computer. Name and LabID cannot be empty.\n" . var_export($this, true));
                    throw new Exception("Error saving computer. Name and LabID cannot be empty.");
                    return null;
                }
                
                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                    $pdoDbObj = $db->getPdoDbObject();
                    // TODO Fix it. Remove "lab" later on.
                    // INSERT INTO computer(name, mstatus, lab, lab_id) VALUES ('test', 1, (SELECT l.name FROM lab l WHERE l.id = 5), 5)
                    $stmt = $pdoDbObj->prepare("INSERT INTO computer(name, mstatus, lab, lab_id) VALUES(:name, :mstatus, (SELECT l.name FROM lab l WHERE l.id = :lab_id), :lab_id)");
                    // TODO correct version
                    //$stmt = $pdoDbObj->prepare("INSERT INTO computer(name, mstatus, lab_id) VALUES(:name, :mstatus, :lab_id)");
                    if(!isset($this->mstatus) || !is_int($this->mstatus)) {
                        $this->mstatus = 1; // Occupied
                    }
                    $params = array(":name" => $this->name, ":mstatus" => intval($this->mstatus), ":lab_id" => intval($this->lab_id));
                    $result = $stmt->execute($params);
                    if($result === false) {
                        $errorMsg = "PDO Error while saving computer." . 
                                    "\nPDO ErrorInfo Obj: " . var_export($stmt->errorInfo(), true) .
                                    "\nComputer Obj: " . var_export($this, true);
                        Log::error($errorMsg);
                        $errorMsg = null;
                        throw new Exception("Error while inserting into Computer.");
                    } else {
                        $this->id = intval($pdoDbObj->lastInsertId());
                    }

                    $params = null;
                    $stmt = null;
                    $pdoDbObj = null;                    
                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                    if(!isset($this->mstatus) || !is_int($this->mstatus)) {
                        $this->mstatus = 1; // Occupied
                    }
                    // TODO Fix it. Remove "lab" later on.
                    $query = "INSERT INTO computer(name, mstatus, lab, lab_id) " . 
                             "VALUES('" . $db->getMysqliDbObject()->escape_string($this->name) . "', " .
                             intval($this->mstatus) . ", " . 
                             "(SELECT l.name FROM lab l WHERE l.id = " . intval($this->lab_id) . "), " .
                             intval($this->lab_id) . ")";
                    // TODO Correct query
                    /*
                    $query = "INSERT INTO computer(name, mstatus, lab_id) " . 
                             "VALUES('" . $db->getMysqliDbObject()->escape_string($this->name) . "', " .
                             intval($this->mstatus) . ", " . intval($this->lab_id) . " )";
                     */
                    $result = $db->exec($query);
                    if($result === false) {
                        $errorMsg = "MySQLi Error while saving computer.";
                        Log::error($errorMsg);
                        $errorMsg = null;
                        throw new Exception("Error while inserting into Computer.");
                    } else {
                        $this->id = intval($db->lastInsertId());
                    }
                    $query = null;
                    $result = null;
                }

                return $this;
                
            } else { // If existing lab, then update it
                if(empty($this->name) || empty($this->lab_id) || !is_int($this->lab_id) ) {
                    Log::error("Error saving computer. Name and Lab cannot be empty.\n" . var_export($this, true));  
                    throw new Exception("Error saving computer. Name and Lab cannot be empty.");
                    return null;
                }
                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                    $pdoDbObj = $db->getPdoDbObject();
                    // TODO Fix it. Remove "lab" later on.
                    $stmt = $pdoDbObj->prepare("UPDATE computer SET name = :name, mstatus = :mstatus, lab = (SELECT l.name FROM lab l WHERE l.id = :lab_id), lab_id = :lab_id WHERE id = :id");
                    // TODO correct version
                    //$stmt = $pdoDbObj->prepare("UPDATE computer SET name = :name, mstatus = :mstatus, lab_id = :lab_id WHERE id = :id");

                    if(!isset($this->mstatus) || !is_int($this->mstatus)) {
                        $this->mstatus = 1; // Occupied
                    }
                    $params = array(":name" => $this->name, ":mstatus" => intval($this->mstatus), ":lab_id" => intval($this->lab_id), ":id" => intval($this->id));
                    $result = $stmt->execute($params);

                    if($result === false) {
                        $errorMsg = "PDO Error while saving computer." . 
                                    "\nPDO ErrorInfo Obj: " . var_export($stmt->errorInfo(), true) .
                                    "\nComputer Obj: " . var_export($this, true);
                        Log::error($errorMsg);
                        $errorMsg = null;
                        throw new Exception("Error while updating into Computer.");
                    } 

                    $params = null;
                    $stmt = null;
                    $pdoDbObj = null;
                    
                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                    if(!isset($this->mstatus) || !is_int($this->mstatus)) {
                        $this->mstatus = 1; // Occupied
                    }
                    
                    // TODO Fix it. Remove "lab" later on.
                    $query = "UPDATE computer " . 
                             "SET name = '" . $db->getMysqliDbObject()->escape_string($this->name) . "', " .
                             "mstatus = " . intval($this->mstatus) . ", " . 
                             "lab = (SELECT l.name FROM lab l WHERE l.id = " . intval($this->lab_id) . "), " .
                             "lab_id = " . intval($this->lab_id) . " " .
                             "WHERE id = " . intval($this->id);
                    // TODO Correct query
                    /*
                    $query = "UPDATE computer " . 
                             "SET name = '" . $db->getMysqliDbObject()->escape_string($this->name) . "', " .
                             "mstatus = " . intval($this->mstatus) . ", " . 
                             "lab_id = " . intval($this->lab_id) . " " .
                             "WHERE id = " . intval($this->id);
                     */
                    $result = $db->exec($query);
                    if($result === false) {
                        $errorMsg = "MySQLi Error while saving computer.";
                        Log::error($errorMsg);
                        $errorMsg = null;
                        throw new Exception("Error while updating Computer.");
                    }
                    
                    $query = null;
                    $result = null;
                }
                return $this;                
            }
        } else {
            throw new Exception("Wrong argument. Please provide Db class instance.");
        }
    }
    
    public function delete(Db &$db) {
        if($db instanceof Db) {
            if(empty($this->id) || empty($this->name) || !is_int($this->id)) {
                Log::error("Error deleting computer. id and name cannot be empty.\n" . var_export($this, true));
                throw new Exception("Error deleting computer. id and name cannot be empty.");
            } else {
                $result = false;
                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                    $pdoDbObj = $db->getPdoDbObject();
                    $stmt = $pdoDbObj->prepare("DELETE FROM computer WHERE id = :id AND name = :name");
                    $params = array(":id" => intval($this->id), ":name" => $this->name);
                    $result = $stmt->execute($params);

                    if(!$result) {
                        $errorMsg = "PDO Error while deleting computer." . 
                                    "\nPDO ErrorInfo Obj: " . var_export($stmt->errorInfo(), true) .
                                    "\nComputer Obj: " . var_export($this, true);
                        Log::error($errorMsg);
                        $errorMsg = null;
                    }
                    $params = null;
                    $stmt = null;
                    $pdoDbObj = null;                    
                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                    $query = "DELETE FROM computer WHERE id = " . 
                                intval($this->id) . " AND name = '" . 
                                $db->getMysqliDbObject()->escape_string($this->name) . "'";
                    $result = $db->exec($query);
                    if(!$result) {
                        $errorMsg = "MySQLi Error while deleting computer.";
                        Log::error($errorMsg);
                        $errorMsg = null;
                    }
                    $query = null;
                }
                return $result;
            }
        } else {
            throw new Exception("Wrong argument. Please provide Db class instance.");
        }
    }
    
    public function find(Db &$db, array $args = array()) {
        if($db instanceof Db) {
            $query = "SELECT id, name, mstatus, lab, lab_id FROM computer";
            $qParams = array();
            
            // Parse Arguments array
            if(count($args) > 0) {
                
                // Condition
                if(array_key_exists("conditions", $args)) {
                    $conditions = $args["conditions"];                    
                    if(is_array($conditions) && count($conditions) > 0) {
                        $bWhereAdded = false;
                        if(array_key_exists("name", $conditions)) {
                            if($conditions["name"] === null) {
                                $query = $query . " WHERE name IS NULL";
                            } else {
                                $qParams[":name"] = $conditions["name"];
                                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                    $query = $query . " WHERE name LIKE :name";
                                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                    $query = $query . " WHERE name LIKE '" . $db->getMysqliDbObject()->escape_string($conditions["name"]) . "'";
                                }
                            }
                            $bWhereAdded = true;
                        } 
                        
                        if(array_key_exists("mstatus", $conditions) && is_int($conditions["mstatus"])) {
                            $qParams[":mstatus"] = intval($conditions["mstatus"]);
                            if($bWhereAdded) {                                
                                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                    $query = $query . " AND mstatus = :mstatus";
                                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                    $query = $query . " AND mstatus = " . intval($conditions["mstatus"]);
                                }                                
                            } else {                                
                                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                    $query = $query . " WHERE mstatus = :mstatus";
                                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                    $query = $query . " WHERE mstatus = " . intval($conditions["mstatus"]);
                                }
                                $bWhereAdded = true;
                            }                            
                        }
                        
                        if(array_key_exists("lab", $conditions)) {
                            $qParams[":lab"] = $conditions["lab"];
                            if($bWhereAdded) {
                                if($conditions["lab"] === null) {
                                    $query = $query . " AND lab IS NULL";
                                } else { 
                                    if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                        $query = $query . " AND lab LIKE :lab";
                                    } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                        $query = $query . " AND lab LIKE '" . $db->getMysqliDbObject()->escape_string($conditions["lab"]) . "'";
                                    }
                                }
                            } else {
                                if($conditions["lab"] === null) {
                                    $query = $query . " WHERE lab IS NULL";
                                } else {
                                    if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                        $query = $query . " WHERE lab LIKE :lab";
                                    } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                        $query = $query . " WHERE lab LIKE '" . $db->getMysqliDbObject()->escape_string($conditions["lab"]) . "'";
                                    }
                                }
                                $bWhereAdded = true;
                            }
                        }
                        
                        if(array_key_exists("lab_id", $conditions) && is_int($conditions["lab_id"])) {
                            $qParams[":lab_id"] = intval($conditions["lab_id"]);
                            if($bWhereAdded) {                                
                                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                    $query = $query . " AND lab_id = :lab_id";
                                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                    $query = $query . " AND lab_id = " . intval($conditions["lab_id"]);
                                }                                
                            } else {
                                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                    $query = $query . " WHERE lab_id = :lab_id";
                                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                    $query = $query . " WHERE lab_id = " . intval($conditions["lab_id"]);
                                }
                                $bWhereAdded = true;
                            }                            
                        }
                        
                        $bWhereAdded = null;
                    }
                    $conditions = null;
                }
                
                // Order
                if(array_key_exists("order", $args)) {
                    $order = $args["order"];
                    if(is_array($order) && count($order) > 0) {
                        $bOrderAdded = false;
                        if(array_key_exists("name", $order)) {
                            if(strcasecmp($order["name"], "asc") == 0) {
                                $query = $query . " ORDER BY name ASC";
                            } else if(strcasecmp($order["name"], "desc") == 0) {
                                $query = $query . " ORDER BY name DESC";
                            }
                            $bOrderAdded = true;
                        } 
                        
                        if(array_key_exists("mstatus", $order)) {                            
                            if($bOrderAdded) {
                                if(strcasecmp($order["mstatus"], "asc") == 0) {
                                    $query = $query . ", mstatus ASC";
                                } else if(strcasecmp($order["mstatus"], "desc") == 0) {
                                    $query = $query . ", mstatus DESC";
                                }
                            } else {
                                if(strcasecmp($order["mstatus"], "asc") == 0) {
                                    $query = $query . " ORDER BY mstatus ASC";
                                } else if(strcasecmp($order["mstatus"], "desc") == 0) {
                                    $query = $query . " ORDER BY mstatus DESC";
                                }
                                $bOrderAdded = true;
                            }
                        } 
                        
                        if(array_key_exists("lab", $order)) {
                            if($bOrderAdded) {
                                if(strcasecmp($order["lab"], "asc") == 0) {
                                    $query = $query . ", lab ASC";
                                } else if(strcasecmp($order["lab"], "desc") == 0) {
                                    $query = $query . ", lab DESC";
                                }
                            } else {
                                if(strcasecmp($order["lab"], "asc") == 0) {
                                    $query = $query . " ORDER BY lab ASC";
                                } else if(strcasecmp($order["lab"], "desc") == 0) {
                                    $query = $query . " ORDER BY lab DESC";
                                }
                                $bOrderAdded = true;
                            }
                        }
                        $bOrderAdded = null;
                    }
                    $order = null;
                }
                
                // Limit
                if(array_key_exists("limit", $args) && is_int($args["limit"]) ) {
                    $query = $query . " LIMIT " . $args["limit"];
                }
                
                // Offset
                if(array_key_exists("offset", $args) && is_int($args["offset"]) ) {
                    $query = $query . " OFFSET " . $args["offset"];
                }
            }
            
            $result = array();
            if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                $pdoDbObj = $db->getPdoDbObject();
                $stmt = $pdoDbObj->prepare($query);
                $stmt->execute($qParams);            
                $result = $stmt->fetchAll(PDO::FETCH_CLASS, "Computer");
            } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                $dbResults = $db->select($query);

                foreach($dbResults as $rowno => $row) {
                    $comp = new Computer();
                    $comp->id = intval($row["id"]);
                    $comp->name = $row["name"];
                    $comp->mstatus = intval($row["mstatus"]);
                    $comp->lab_id = intval($row["lab_id"]);
                    $comp->lab = $row["lab"];
                    $result[] = $comp;

                    $lab = null;
                    $comp = null;
                }
                $dbResults = null;
            }
            
            $query = null;
            $stmt = null;
            $pdoDbObj = null;
            return $result;
        } else {
            throw new Exception("Wrong argument. Please provide Db class instance.");
        }
    }
    
    public function getTotalCount(Db &$db) {
        if($db instanceof Db) {
            $result = 0;
            if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                $pdoDbObj = $db->getPdoDbObject();
                $stmt = $pdoDbObj->query("SELECT count(id) AS comp_count FROM computer");            
                foreach($stmt as $row) {
                    $result = intval($row['comp_count']);
                    $row = null;
                }
                $stmt = null;
                $pdoDbObj = null;
            } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                $records = $db->select("SELECT count(id) AS comp_count FROM computer");
                foreach($records as $row) {
                    $result = intval($row['comp_count']);
                    $row = null;
                }
                $records = null;
            }
            
            return $result;
        } else {
            throw new Exception("Wrong argument. Please provide Db class instance.");
        }
    }
    
    public function findWithLab(Db &$db, array $args = array()) {
        if($db instanceof Db) {
            $query = "SELECT c.id as comp_id, c.name as comp_name, c.mstatus as comp_mstatus, l.id as lab_id, l.name as lab_name, l.title as lab_title, l.description as lab_desc FROM computer c LEFT JOIN lab l ON c.lab_id = l.id";
            $qParams = array();
            
            // Parse Arguments array
            if(count($args) > 0) {
                
                // Condition
                if(array_key_exists("conditions", $args)) {
                    $conditions = $args["conditions"];
                    if(is_array($conditions) && count($conditions) > 0) {
                        $bWhereAdded = false;
                        if(array_key_exists("name", $conditions)) {
                            if($conditions["name"] === null) {
                                $query = $query . " WHERE c.name IS NULL";
                            } else {
                                $qParams[":name"] = $conditions["name"];
                                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                    $query = $query . " WHERE c.name LIKE :name";
                                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                    $query = $query . " WHERE c.name LIKE '" . $db->getMysqliDbObject()->escape_string($conditions["name"]) . "'";
                                }
                            }
                            $bWhereAdded = true;
                        } 
                        
                        if(array_key_exists("mstatus", $conditions) && is_int($conditions["mstatus"])) {
                            $qParams[":mstatus"] = intval($conditions["mstatus"]);
                            if($bWhereAdded) {                                
                                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                    $query = $query . " AND c.mstatus = :mstatus";
                                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                    $query = $query . " AND c.mstatus = " . intval($conditions["mstatus"]);
                                }                                
                            } else {                                
                                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                    $query = $query . " WHERE c.mstatus = :mstatus";
                                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                    $query = $query . " WHERE c.mstatus = " . intval($conditions["mstatus"]);
                                }
                                $bWhereAdded = true;
                            }                            
                        }
                        
                        if(array_key_exists("lab", $conditions)) {
                            $qParams[":lab"] = $conditions["lab"];
                            if($bWhereAdded) {
                                if($conditions["lab"] === null) {
                                    $query = $query . " AND c.lab IS NULL";
                                } else { 
                                    if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                        $query = $query . " AND c.lab LIKE :lab";
                                    } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                        $query = $query . " AND c.lab LIKE '" . $db->getMysqliDbObject()->escape_string($conditions["lab"]) . "'";
                                    }
                                }
                            } else {
                                if($conditions["lab"] === null) {
                                    $query = $query . " WHERE c.lab IS NULL";
                                } else {
                                    $query = $query . " WHERE c.lab LIKE :lab";
                                    if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                        $query = $query . " WHERE c.lab LIKE :lab";
                                    } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                        $query = $query . " WHERE c.lab LIKE '" . $db->getMysqliDbObject()->escape_string($conditions["lab"]) . "'";
                                    }
                                }
                                $bWhereAdded = true;
                            }
                        }
                        
                        if(array_key_exists("lab_id", $conditions) && is_int($conditions["lab_id"])) {
                            $qParams[":lab_id"] = intval($conditions["lab_id"]);
                            if($bWhereAdded) {                                
                                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                    $query = $query . " AND c.lab_id = :lab_id";
                                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                    $query = $query . " AND c.lab_id = " . intval($conditions["lab_id"]);
                                }                                
                            } else {
                                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                    $query = $query . " WHERE c.lab_id = :lab_id";
                                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                    $query = $query . " WHERE c.lab_id = " . intval($conditions["lab_id"]);
                                }
                                $bWhereAdded = true;
                            }                            
                        }
                        
                        $bWhereAdded = null;
                    }
                    $conditions = null;
                }
                
                // Order
                if(array_key_exists("order", $args)) {
                    $order = $args["order"];
                    if(is_array($order) && count($order) > 0) {
                        $bOrderAdded = false;
                        if(array_key_exists("name", $order)) {
                            if(strcasecmp($order["name"], "asc") == 0) {
                                $query = $query . " ORDER BY c.name ASC";
                            } else if(strcasecmp($order["name"], "desc") == 0) {
                                $query = $query . " ORDER BY c.name DESC";
                            }
                            $bOrderAdded = true;
                        } 
                        
                        if(array_key_exists("mstatus", $order)) {                            
                            if($bOrderAdded) {
                                if(strcasecmp($order["mstatus"], "asc") == 0) {
                                    $query = $query . ", c.mstatus ASC";
                                } else if(strcasecmp($order["mstatus"], "desc") == 0) {
                                    $query = $query . ", c.mstatus DESC";
                                }
                            } else {
                                if(strcasecmp($order["mstatus"], "asc") == 0) {
                                    $query = $query . " ORDER BY c.mstatus ASC";
                                } else if(strcasecmp($order["mstatus"], "desc") == 0) {
                                    $query = $query . " ORDER BY c.mstatus DESC";
                                }
                                $bOrderAdded = true;
                            }
                        } 
                        
                        if(array_key_exists("lab", $order)) {
                            if($bOrderAdded) {
                                if(strcasecmp($order["lab"], "asc") == 0) {
                                    $query = $query . ", c.lab ASC";
                                } else if(strcasecmp($order["lab"], "desc") == 0) {
                                    $query = $query . ", c.lab DESC";
                                }
                            } else {
                                if(strcasecmp($order["lab"], "asc") == 0) {
                                    $query = $query . " ORDER BY c.lab ASC";
                                } else if(strcasecmp($order["lab"], "desc") == 0) {
                                    $query = $query . " ORDER BY c.lab DESC";
                                }
                                $bOrderAdded = true;
                            }
                        }
                        $bOrderAdded = null;
                    }
                    $order = null;
                }
                
                // Limit
                if(array_key_exists("limit", $args) && is_int($args["limit"]) ) {
                    $query = $query . " LIMIT " . $args["limit"];
                }
                
                // Offset
                if(array_key_exists("offset", $args) && is_int($args["offset"]) ) {
                    $query = $query . " OFFSET " . $args["offset"];
                }
            }
            $dbResults = array();
            if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                $pdoDbObj = $db->getPdoDbObject();
                $stmt = $pdoDbObj->prepare($query);
                $stmt->execute($qParams);            
                $dbResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                $dbResults = $db->select($query);                
            }
            
            $result = array();
            foreach($dbResults as $rowno => $row) {
                $lab = new Lab();
                $lab->id = intval($row["lab_id"]);
                $lab->name = $row["lab_name"];
                $lab->title = $row["lab_title"];
                $lab->description = $row["lab_desc"];
                
                $comp = new Computer();
                $comp->id = intval($row["comp_id"]);
                $comp->name = $row["comp_name"];
                $comp->mstatus = intval($row["comp_mstatus"]);
                $comp->lab_id = intval($row["lab_id"]);
                $comp->labObj = $lab;
                $result[] = $comp;
                
                $lab = null;
                $comp = null;
            }
            
            $dbResults = null;
            $query = null;
            $stmt = null;
            $pdoDbObj = null;
            return $result;
        } else {
            throw new Exception("Wrong argument. Please provide Db class instance.");
        }
    }
}

?>
