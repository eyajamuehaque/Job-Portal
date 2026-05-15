<?php
/**
 * app/core/Database.php
 * Handles the connection to the MySQL database.
 * Refer to Page 71 of your notes for procedural connection logic.
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
     * Constructor: Initializes the connection when the class is instantiated.
     */
    public function __construct() {
        // Establishing connection using procedural mysqli_connect
        // This style is highlighted in your AIUB notes for simplicity.
        $this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->dbname);

        // Check if the connection was successful
        if (!$this->conn) {
            // Using die() to stop execution on failure (Page 71 of notes)
            die("Connection failed: " . mysqli_connect_error());
        }
    }

    /**
     * Optional: Close the connection manually
     * Refer to Page 74 of your notes
     */
    public function close() {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }
}
?>