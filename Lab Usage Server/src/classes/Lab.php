<?php
class Lab {
    
    public $id;
    public $name;
    public $title;
    public $description;
    
    public function __construct($name = "", $title = "", $description = "") {
        if(!empty($name)) {
            $this->name = $name;
        }
        if(!empty($title)) {
            $this->title = $title;
        }
        if(!empty($description)) {
            $this->description = $description;
        }
    }
    
    public function save(Db &$db) {
        if($db instanceof Db) {
            if(empty($this->id)) { // If new lab, then insert                
                if(empty($this->name)) {
                    throw new Exception("Error saving lab. Name cannot be empty.");
                }
                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                    $pdoDbObj = $db->getPdoDbObject();
                    $stmt = $pdoDbObj->prepare("INSERT INTO lab(name, title, description) VALUES(:name, :title, :description)");
                    $params = array(":name" => $this->name, ":title" => $this->title, ":description" => $this->description);
                    $result = $stmt->execute($params);
                    if($result === false) {
                        throw new Exception("Error while inserting into lab.");
                    } else {
                        $this->id = intval($pdoDbObj->lastInsertId());
                    }

                    $params = null;
                    $stmt = null;
                    $pdoDbObj = null;
                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {                    
                    $query = "INSERT INTO lab(name, title, description) " . 
                             "VALUES('" . $db->getMysqliDbObject()->escape_string($this->name) . "', " .
                             "'" . $db->getMysqliDbObject()->escape_string($this->title) . "', " . 
                             "'" . $db->getMysqliDbObject()->escape_string($this->description) . "')";

                    $result = $db->exec($query);
                    if($result === false) {
                        $errorMsg = "MySQLi Error while saving lab.";
                        Log::error($errorMsg);
                        $errorMsg = null;
                        throw new Exception("Error while inserting into lab.");
                    } else {
                        $this->id = intval($db->lastInsertId());
                    }
                    $query = null;
                    $result = null;
                }
                return $this;
            } else { // If existing lab, then update it
                if(empty($this->name)) {
                    throw new Exception("Error updaing lab. Name cannot be empty.");
                }
                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                    $pdoDbObj = $db->getPdoDbObject();
                    $stmt = $pdoDbObj->prepare("UPDATE lab SET name = :name, title = :title, description = :description WHERE id = :id");
                    $params = array(":name" => $this->name, ":title" => $this->title, ":description" => $this->description, ":id" => intval($this->id));
                    $result = $stmt->execute($params);
                    if($result === false) {
                        $errorMsg = "PDO Error while saving lab." . 
                                    "\nPDO ErrorInfo Obj: " . var_export($stmt->errorInfo(), true) .
                                    "\nLab Obj: " . var_export($this, true);
                        Log::error($errorMsg);
                        $errorMsg = null;
                        throw new Exception("Error while updating Lab.");
                    }
                    $params = null;
                    $stmt = null;
                    $pdoDbObj = null;
                    $result = null;
                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                    $query = "UPDATE lab SET " . 
                             "name = '" . $db->getMysqliDbObject()->escape_string($this->name) . "', " .
                             "title = '" . $db->getMysqliDbObject()->escape_string($this->title) . "', " . 
                             "description = '" . $db->getMysqliDbObject()->escape_string($this->description) . "' " .
                             "WHERE id = " . intval($this->id);

                    $result = $db->exec($query);
                    if($result === false) {
                        $errorMsg = "MySQLi Error while saving lab.";
                        Log::error($errorMsg);
                        $errorMsg = null;
                        throw new Exception("Error while updating into lab.");
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
            if(empty($this->id) || empty($this->name)) {              
                throw new Exception("Error deleting lab. id and name cannot be empty.");
                return null;
            } else {                
                $result = false;
                if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                    $pdoDbObj = $db->getPdoDbObject();
                    $stmt = $pdoDbObj->prepare("DELETE FROM lab WHERE id = :id AND name = :name");
                    $params = array(":id" => intval($this->id), ":name" => $this->name);
                    $result = $stmt->execute($params);
                    if(!$result) {
                        $errorMsg = "PDO Error while deleting lab." . 
                                    "\nPDO ErrorInfo Obj: " . var_export($stmt->errorInfo(), true) .
                                    "\nLab Obj: " . var_export($this, true);
                        Log::error($errorMsg);
                        $errorMsg = null;
                    }
                    $params = null;
                    $stmt = null;
                    $pdoDbObj = null;                    
                } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                    $query = "DELETE FROM lab WHERE id = " . 
                                intval($this->id) . " AND name = '" . 
                                $db->getMysqliDbObject()->escape_string($this->name) . "'";
                    $result = $db->exec($query);
                    if(!$result) {
                        $errorMsg = "MySQLi Error while deleting lab.";
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
            $query = "SELECT id, name, title, description FROM lab";
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
                        
                        if(array_key_exists("title", $conditions)) {
                            $qParams[":title"] = $conditions["title"];
                            if($bWhereAdded) {
                                if($conditions["title"] === null) {
                                    $query = $query . " AND title IS NULL";
                                } else {                                    
                                    if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                        $query = $query . " AND title LIKE :title";
                                    } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                        $query = $query . " AND title LIKE '" . $db->getMysqliDbObject()->escape_string($conditions["title"]) . "'";
                                    }
                                }
                            } else {
                                if($conditions["title"] === null) {
                                    $query = $query . " WHERE title IS NULL";
                                } else {                                    
                                    if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                        $query = $query . " WHERE title LIKE :title";
                                    } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                        $query = $query . " WHERE title LIKE '" . $db->getMysqliDbObject()->escape_string($conditions["title"]) . "'";
                                    }
                                }
                                $bWhereAdded = true;
                            }                            
                        } 
                        
                        if(array_key_exists("description", $conditions)) {
                            $qParams[":description"] = $conditions["description"];
                            if($bWhereAdded) {
                                if($conditions["description"] === null) {
                                    $query = $query . " AND description IS NULL";
                                } else {                                    
                                    if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                        $query = $query . " AND description LIKE :description";
                                    } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                        $query = $query . " AND description LIKE '" . $db->getMysqliDbObject()->escape_string($conditions["description"]) . "'";
                                    }
                                }
                            } else {
                                if($conditions["description"] === null) {
                                    $query = $query . " WHERE description IS NULL";
                                } else {
                                    if($db->getDbAdapter() === Db::DB_ADAPTER_PDO_MYSQL) {
                                        $query = $query . " WHERE description LIKE :description";
                                    } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                                        $query = $query . " WHERE description LIKE '" . $db->getMysqliDbObject()->escape_string($conditions["description"]) . "'";
                                    }
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
                        
                        if(array_key_exists("title", $order)) {                            
                            if($bOrderAdded) {
                                if(strcasecmp($order["title"], "asc") == 0) {
                                    $query = $query . ", title ASC";
                                } else if(strcasecmp($order["title"], "desc") == 0) {
                                    $query = $query . ", title DESC";
                                }
                            } else {
                                if(strcasecmp($order["title"], "asc") == 0) {
                                    $query = $query . " ORDER BY title ASC";
                                } else if(strcasecmp($order["title"], "desc") == 0) {
                                    $query = $query . " ORDER BY title DESC";
                                }
                                $bOrderAdded = true;
                            }
                        } 
                        
                        if(array_key_exists("description", $order)) {
                            if($bOrderAdded) {
                                if(strcasecmp($order["description"], "asc") == 0) {
                                    $query = $query . ", description ASC";
                                } else if(strcasecmp($order["description"], "desc") == 0) {
                                    $query = $query . ", description DESC";
                                }
                            } else {
                                if(strcasecmp($order["description"], "asc") == 0) {
                                    $query = $query . " ORDER BY description ASC";
                                } else if(strcasecmp($order["description"], "desc") == 0) {
                                    $query = $query . " ORDER BY description DESC";
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
                $result = $stmt->fetchAll(PDO::FETCH_CLASS, "Lab");
                $stmt = null;
                $pdoDbObj = null;
            } else if($db->getDbAdapter() === Db::DB_ADAPTER_MYSQLI) {
                $dbResults = $db->select($query);
                foreach($dbResults as $rowno => $row) {
                    $lab = new Lab();
                    $lab->id = intval($row["id"]);
                    $lab->name = $row["name"];
                    $lab->title = $row["title"];
                    $lab->description = $row["description"];
                    $result[] = $lab;

                    $lab = null;
                }
                $dbResults = null;
            }
            
            $query = null;

            return $result;
        } else {
            throw new Exception("Wrong argument. Please provide Db class instance.");
        }
    }
}

/*
 * Unwanted code
 * 
    public function getAll(Db &$db) {
        if($db instanceof Db) {
            $pdoDbObj = $db->getPdoDbObject();
            $stmt = $pdoDbObj->prepare("SELECT id, name, title, description FROM lab");
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_CLASS, "Lab");
            $stmt = null;
            $pdoDbObj = null;
            return $result;
        } else {
            throw new Exception("Wrong argument. Please provide Db class instance.");
        }
    }
 * 
 * 
 public function getAllOrderBy(&$db, $orderBy, $orderType) {
        if($db instanceof Db &&
           ($orderBy === "name" || $orderBy === "title" || $orderBy === "description")) {
            
            $query = "SELECT id, name, title, description FROM lab ORDER BY " . $orderBy;
            if($orderType === "asc") {
                $query = $query . " ASC";
            } else if($orderType === "desc") {
                $query = $query . " DESC";
            }
            $pdoDbObj = $db->getPdoDbObject();
            $stmt = $pdoDbObj->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_CLASS, "Lab");
            $query = null;
            $stmt = null;
            $pdoDbObj = null;
            return $result;
        } else {
            throw new Exception("Wrong argument. Please provide Db class instance.");
        }
    }
 *  */
?>
