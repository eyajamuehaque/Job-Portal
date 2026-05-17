<?php
/**
 * app/core/Database.php
 * Handles the connection to the MySQL database.
 */

class Database {
    // Database configuration settings
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $dbname = "job_portal";
    
    // Connection property to be used by models
    public $conn;

    /**
     * Constructor
     */
    public function __construct() {

        $this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->dbname);

        
        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
    }


    public function close() {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }
}
?>