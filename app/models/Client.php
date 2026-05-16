<?php
/**
 * app/models/Client.php
 * Handles database operations for the 'recruiter_clients' table.
 */

class Client {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Add a new client for a recruiter
     */
    public function add($recruiter_id, $employer_id = null, $company_name_override = null) {
        // Validation: must provide either an employer_id or a company name
        if (empty($employer_id) && empty($company_name_override)) {
            return false;
        }

        // Check for duplicates
        if ($employer_id) {
            $checkSql = "SELECT id FROM recruiter_clients WHERE recruiter_id = ? AND employer_id = ?";
            $checkStmt = mysqli_prepare($this->db, $checkSql);
            mysqli_stmt_bind_param($checkStmt, "ii", $recruiter_id, $employer_id);
        } else {
            $checkSql = "SELECT id FROM recruiter_clients WHERE recruiter_id = ? AND company_name_override = ?";
            $checkStmt = mysqli_prepare($this->db, $checkSql);
            mysqli_stmt_bind_param($checkStmt, "is", $recruiter_id, $company_name_override);
        }
        
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            mysqli_stmt_close($checkStmt);
            return false; // Already exists
        }
        mysqli_stmt_close($checkStmt);

        $sql = "INSERT INTO recruiter_clients (recruiter_id, employer_id, company_name_override) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            $employer_id = empty($employer_id) ? null : $employer_id;
            $company_name_override = empty($company_name_override) ? null : $company_name_override;
            
            mysqli_stmt_bind_param($stmt, "iis", $recruiter_id, $employer_id, $company_name_override);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Get all clients for a specific recruiter
     */
    public function getByRecruiter($recruiter_id) {
        $sql = "SELECT rc.*, u.name as employer_name, u.email as employer_email 
                FROM recruiter_clients rc
                LEFT JOIN users u ON rc.employer_id = u.id
                WHERE rc.recruiter_id = ?
                ORDER BY rc.added_at DESC";
                
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $recruiter_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $clients = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $clients;
        }
        return [];
    }

    /**
     * Remove a client relationship
     */
    public function remove($id, $recruiter_id) {
        $sql = "DELETE FROM recruiter_clients WHERE id = ? AND recruiter_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $id, $recruiter_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }
}
?>
