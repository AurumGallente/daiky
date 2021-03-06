<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
set_time_limit(10 * 60);
class Database {
    private static $_instance = null;
    private $servername = "localhost";
    private $username = "";
    private $password = "";
    private $db = "daiky";
    private $conn;
    public $result;
    private function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password);
        $this->conn->set_charset("utf8");
        $this->conn->select_db($this->db);
    }
    static public function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function query($query) {
        $result = $this->conn->query($query);
        $results = array();        
        if($result && !is_bool($result)){
            while ($row = mysqli_fetch_assoc($result)) {
                $results[] = ($row);
            }
            $this->result = $results;            
        }
        return self::$_instance;
    }
    public function close(){
        $this->conn->close();
    }
    public function fetchSingleRow(){
        return $this->result[0];
    }
    public function fetchAll(){
        return $this->result;
    }
    public function quote($arg){
        return $this->conn->real_escape_string($arg);
    }
}
