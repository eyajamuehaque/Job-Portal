<?php
/**
 * app/models/JobAlert.php
 * Handles database operations for the 'job_alerts' table.
 */

class JobAlert {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Create a new job alert
     */
    public function create($seeker_id, $keyword, $category_id, $location, $job_type) {
        $sql = "INSERT INTO job_alerts (seeker_id, keyword, category_id, location, job_type) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            $category_id = empty($category_id) ? null : $category_id;
            $job_type = empty($job_type) ? null : $job_type;
            
            mysqli_stmt_bind_param($stmt, "isiss", $seeker_id, $keyword, $category_id, $location, $job_type);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Delete an alert
     */
    public function delete($id, $seeker_id) {
        $sql = "DELETE FROM job_alerts WHERE id = ? AND seeker_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $id, $seeker_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Get all alerts for a seeker
     */
    public function getBySeeker($seeker_id) {
        $sql = "SELECT a.*, c.name as category_name 
                FROM job_alerts a
                LEFT JOIN categories c ON a.category_id = c.id
                WHERE a.seeker_id = ?
                ORDER BY a.created_at DESC";
                
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $seeker_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $alerts = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $alerts;
        }
        return [];
    }
}
?>
