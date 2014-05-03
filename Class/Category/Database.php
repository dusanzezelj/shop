<?php
require_once 'config.php';
class Database {
    private $conn;
    private $lastQuery;
    public function __construct($db_server, $db_user, $db_pass, $db_name) {
        $this->connect($db_server, $db_user, $db_pass, $db_name);
       $this->lastQuery="";
    }
    public function connect($db_server, $db_user, $db_pass, $db_name){
         $this->conn= mysqli_connect($db_server, $db_user, $db_pass, $db_name);        
        if (mysqli_connect_errno()){
            die(mysqli_connect_error());
        }
    }
    public function closeConnection() {
           if (isset($this->conn)) {
               mysqli_close($this->conn);
		unset($this->conn);
                unset($this->lastQuery);
		}
    }
    public function query($sql){
       if (!empty($sql)){
           $this->lastQuery=$sql;
           return mysqli_query($this->conn, $sql);
       } 
    }
    public function getNumRows($result){
        $res=$this->query($result);
        return mysqli_num_rows($res);
    }
    public function getAffectedRows(){
        return mysqli_affected_rows($this->conn);
    }
    public function getLastInsertID(){
        return mysqli_insert_id($this->conn);
    }
    public function fetchArray($result){
        return mysqli_fetch_array($result);
    }
    public function escapeString($value){
        return mysqli_real_escape_string($this->conn,$value); 
    }
  
}
$db= new Database($db_server, $db_user, $db_pass, $db_name);

?>
