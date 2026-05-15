<?php
/**
 * app/models/SavedJob.php
 * Handles database operations for the 'saved_jobs' table.
 */

class SavedJob {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Save a job (bookmark)
     */
    public function save($user_id, $job_id) {
        $sql = "INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $job_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Remove a saved job
     */
    public function remove($user_id, $job_id) {
        $sql = "DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $job_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Check if a job is saved by a user
     */
    public function isSaved($user_id, $job_id) {
        $sql = "SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $job_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $count = mysqli_stmt_num_rows($stmt);
            mysqli_stmt_close($stmt);
            return $count > 0;
        }
        return false;
    }

    /**
     * Get all saved jobs for a user
     */
    public function getByUser($user_id) {
        $sql = "SELECT s.*, j.title, j.location, j.job_type, j.salary_min, j.salary_max, u.name as company_name 
                FROM saved_jobs s
                JOIN jobs j ON s.job_id = j.id
                JOIN users u ON j.employer_id = u.id
                WHERE s.user_id = ?
                ORDER BY s.saved_at DESC";
                
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $jobs = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $jobs;
        }
        return [];
    }
}
?>
