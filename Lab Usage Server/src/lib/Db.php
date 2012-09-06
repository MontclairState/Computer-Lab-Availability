<?php

class Db {
    
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $adapter;
    
    private $pdoDsn;
    private $pdoDbObject;
    private $pdoStmtObject;
    
    private $mysqliDbObject;
    private $mysqliResultObject;
    
    const DB_ADAPTER_PDO_MYSQL = "PDO_MYSQL";
    const DB_ADAPTER_MYSQLI = "MYSQLI";


    public function __construct($env) {
        if(!is_array($env)) {
            Log::error("env variable must be array from config.php");
        } else {
            $this->host = $env['host'];
            $this->port = $env['port'];
            $this->dbname = $env['dbname'];
            $this->username = $env['username'];
            $this->password = $env['password'];
            if($env['adapter'] === "PDO_MYSQL") {
                $this->adapter = self::DB_ADAPTER_PDO_MYSQL;
            } else if($env['adapter'] === "MYSQLI") {
                $this->adapter = self::DB_ADAPTER_MYSQLI;
            }
            
            
            $this->pdoDsn = 'mysql:dbname=' . $this->dbname . ';host=' . $this->host;
        }        
    }
    
    public function __destruct() {
        $this->pdoStmtObject = null;
        $this->pdoDbObject = null;
    }
    
    public function connect() {
        if( $this->adapter === self::DB_ADAPTER_PDO_MYSQL ) {
            try {
                $this->pdoDbObject = new PDO($this->pdoDsn, $this->username, $this->password);
            } catch(PDOException $e) {
                Log::error("Error connecting to database using PDO_MYSQL. Message: " . $e->getMessage());
                $this->pdoDbObject = null;
            }
        } else if( $this->adapter === self::DB_ADAPTER_MYSQLI ) {
            $this->mysqliDbObject = new mysqli($this->host, $this->username, $this->password, $this->dbname);
            if($this->mysqliDbObject->connect_errno) {
                Log::error("Error connecting to database using MySQLi. [" . 
                                $this->mysqliDbObject->connect_errno . "] : " . 
                                $this->mysqliDbObject->connect_err);
            } else {
                $this->mysqliDbObject->set_charset("utf8");
            }
        } else {
            Log::error("Database adapater not support. Only PDO_MYSQL and MYSQLi are suppored.");
            exit();
        }
    }    
    
    public function close() {
        if( $this->adapter === self::DB_ADAPTER_PDO_MYSQL ) {
            $this->pdoDbObject = null;
        } else if( $this->adapter === self::DB_ADAPTER_MYSQLI ) {
            $this->mysqliDbObject->close();
            $this->mysqliDbObject = null;
        }
    }    
    
    public function select($query, $params = array(), $fetchMode = PDO::FETCH_ASSOC) {
        if($this->adapter === self::DB_ADAPTER_PDO_MYSQL) {
            $this->pdoStmtObject = $this->pdoDbObject->prepare($query);
            $this->pdoStmtObject->execute($params);
            return $this->pdoStmtObject->fetchAll($fetchMode);
        } else if( $this->adapter === self::DB_ADAPTER_MYSQLI ) {
            $resultsArray = null;
            $this->mysqliResultObject = $this->mysqliDbObject->query($query);
            if( !$this->mysqliResultObject ) {
                Log::error("select(...). Query failed. \nQuery: " . $query . "\nError: " . $this->getMySQLiErrorInfo());                
            } else {
                $resultsArray = array();
                while ($row = $this->mysqliResultObject->fetch_assoc()) {
                    $resultsArray[] = $row;
                }
            }
            $this->mysqliResultObject->free();
            return $resultsArray;
        } else {
            Log::error("select(...). Database adapater not support. Only PDO_MYSQL and MYSQLi are suppored.");
            return null;
        }
    }
    
    public function exec($query, $params = array()) {
        if($this->adapter === self::DB_ADAPTER_PDO_MYSQL) {
            $this->pdoStmtObject = $this->pdoDbObject->prepare($query);
            return $this->pdoStmtObject->execute($params);
        } else if( $this->adapter === self::DB_ADAPTER_MYSQLI ) {
            $result = false;
            $result = $this->mysqliDbObject->query($query);
            if($result === false) {
                Log::error("exec(...). Query failed. \nQuery: " . $query . "\nError: " . $this->getMySQLiErrorInfo());
            }
            return $result;
        } else {
            Log::error("exec(...). Database adapater not support. Only PDO_MYSQL and MYSQLi are suppored.");
            return null;
        }
    }
    
    public function lastInsertId() {
        if($this->adapter === self::DB_ADAPTER_PDO_MYSQL) {
            if(isset($this->pdoDbObject)) {
                return $this->pdoDbObject->lastInsertId();
            } else {
                return null;
            }
        } else if($this->adapter === self::DB_ADAPTER_MYSQLI) {
            return $this->mysqliDbObject->insert_id;
        } else {
            return null;
        }
    }
    
    public function rowCount() {
        if($this->adapter === self::DB_ADAPTER_PDO_MYSQL) {
            if(isset($this->pdoStmtObject)) {
                return $this->pdoStmtObject->rowCount(); 
            } else {
                return null;
            }
        } else if($this->adapter === self::DB_ADAPTER_MYSQLI) {
            return $this->mysqliDbObject->affected_rows;
        } else {
            return null;
        }
    }
    
    public function getPdoDbObject() {
        return $this->pdoDbObject;
    }
    
    public function getPdoStmtObject() {
        return $this->pdoStmtObject;
    }
    
    public function getMysqliDbObject() {
        return $this->mysqliDbObject;
    }
    
    public function getDbAdapter() {
        return $this->adapter;
    }
    
    private function getMySQLiErrorInfo() {
        return "[" . $this->mysqliDbObject->connect_errno . "] : " . $this->mysqliDbObject->connect_error;
    }
    
}

?>
