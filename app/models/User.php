<?php
/**
 * app/models/User.php
 * Handles all database operations for the 'users' table.
 */

class User {
    private $db;

    // The constructor receives the database connection from the controller
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Create a new user (Registration)
     * Using Prepared Statements as per Page 80 of your notes
     */
    public function create($name, $email, $password, $phone, $role) {
       
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

       
        $sql = "INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);

        if ($stmt) {
           
            mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $password_hash, $phone, $role);
            
           
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Find a user by email (For Login validation)
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($this->db, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            
            mysqli_stmt_close($stmt);
            return $user; 
        }
        return null;
    }

    /**
     * Check if an email already exists (Validation)
     */
    public function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            $count = mysqli_stmt_num_rows($stmt);
            mysqli_stmt_close($stmt);
            
            return $count > 0;
        }
        return false;
    }
}
?>